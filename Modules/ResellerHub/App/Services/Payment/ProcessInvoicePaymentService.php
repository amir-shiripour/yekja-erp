<?php

namespace Modules\ResellerHub\App\Services\Payment;

use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Exception;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Events\InvoicePaid;
use Modules\ResellerHub\App\Events\PaymentRecorded;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Services\WalletService;
use Nwidart\Modules\Facades\Module;
use Throwable;

class ProcessInvoicePaymentService
{
    public function __construct(
        protected ResellerBillingService $billingService,
        protected GatewayAmountResolver  $amounts,
    )
    {
    }

    /**
     * Process invoice payment via chosen strategies (Online Gateway, Wallet, Direct).
     *
     * @return array{type: 'redirect'|'success', url?: string, payments?: array, invoice?: ResellerInvoice}
     * @throws Exception
     */
    public function process(ResellerInvoice $invoice, array $data, ?User $actor = null): array
    {
        $invoice->refresh();

        $reseller = $invoice->reseller ?: Reseller::find($invoice->reseller_id);
        if (!$reseller) {
            throw new ResellerHubException(__('resellerhub::messages.invoices.reseller_not_found'));
        }

        $currency = $this->amounts->sourceCurrency($invoice);
        $paymentCurrency = $this->amounts->paymentCurrency();
        $conversionFactor = $this->amounts->forInvoice($invoice)['factor'];
        $paymentCurrencyLabel = ResellerHelper::currencyLabel($paymentCurrency);

        $useWallet = !empty($data['use_wallet']);

        // Money that arrives outside a gateway (cash, POS, card-to-card) may
        // only be *settled* by the reseller side. A tenant user submitting the
        // same form records a claim that stays pending until the reseller
        // confirms it — otherwise a customer could mark their own invoice paid.
        $settleOffline = $this->actorCanSettleOffline($actor, $invoice);

        $paymentItems = $this->normalizePaymentItems(
            rawItems: (array)($data['payment_items'] ?? []),
            singleMethod: $data['payment_method'] ?? null,
            singleAmount: $data['amount'] ?? 0,
            singleTxId: $data['transaction_id'] ?? ($data['transaction_ref'] ?? null),
            singleGateway: $data['gateway'] ?? null,
            conversionFactor: $conversionFactor
        );

        $hasMethodPayments = !empty($paymentItems);

        if (!$useWallet && !$hasMethodPayments) {
            throw new ResellerHubException(__('resellerhub::messages.invoices.select_payment_method'));
        }

        $currentDue = (int)$invoice->remainingAmount();
        if ($currentDue <= 0) {
            throw new ResellerHubException(__('resellerhub::messages.invoices.already_settled'));
        }

        // Calculate requested wallet amount
        $reqWalletAmt = 0;
        if ($useWallet) {
            if (!empty($data['wallet_amount'])) {
                $rawWalletAmt = (float)str_replace(',', '', (string)$data['wallet_amount']);
                if ($rawWalletAmt <= 0) {
                    throw new ResellerHubException(__('resellerhub::messages.invoices.wallet_amount_positive'));
                }
                $reqWalletAmt = (int)round($rawWalletAmt / $conversionFactor);
            } else {
                $reqWalletAmt = $currentDue;
            }
        }

        // Calculate direct items total
        $directTotal = 0;
        foreach ($paymentItems as $item) {
            $directTotal += (int)($item['amount'] ?? 0);
        }

        $totalSubmittedInRequest = $reqWalletAmt + $directTotal;

        // Exact comparison without float tolerance hack
        if ($totalSubmittedInRequest > $currentDue) {
            $submittedDisplay = (int)round($totalSubmittedInRequest * $conversionFactor);
            $dueDisplay = (int)round($currentDue * $conversionFactor);
            throw new ResellerHubException(__('resellerhub::messages.invoices.payment_exceeds_due', [
                'submitted' => number_format($submittedDisplay),
                'currency' => $paymentCurrencyLabel,
                'due' => number_format($dueDisplay),
            ]));
        }

        // Strategy 1: Online Gateway Redirect (live payment initiation)
        if (!$useWallet && count($paymentItems) === 1) {
            $firstItem = $paymentItems[0];
            $onlineRedirect = $this->tryProcessOnlineGateway(
                invoice: $invoice,
                item: $firstItem,
                currentDue: $currentDue,
                conversionFactor: $conversionFactor,
                actor: $actor
            );
            if ($onlineRedirect !== null) {
                return $onlineRedirect;
            }
        }

        $paidAt = !empty($data['paid_at'])
            ? (reseller_to_carbon($data['paid_at']) ?? now())
            : now();
        if ($paidAt->isFuture() && $paidAt->diffInMinutes(now()) > 5) {
            $paidAt = now();
        }
        if ($invoice->issue_date && $paidAt->lt($invoice->issue_date->copy()->startOfDay())) {
            $paidAt = $invoice->issue_date->copy()->startOfDay();
        }

        $createdPayments = [];

        DB::transaction(function () use (
            $invoice,
            $useWallet,
            $reqWalletAmt,
            $paymentItems,
            $paidAt,
            $actor,
            $settleOffline,
            &$createdPayments
        ) {
            // FIN-01: Lock invoice row with lockForUpdate to prevent race conditions & double settlement
            $lockedInvoice = ResellerInvoice::whereKey($invoice->getKey())->lockForUpdate()->firstOrFail();
            $remainingDue = (int)$lockedInvoice->remainingAmount();

            if ($remainingDue <= 0 && $lockedInvoice->isPaid()) {
                return;
            }

            // Strategy 2: Wallet Payment
            if ($useWallet && $remainingDue > 0) {
                $walletPayment = $this->processWalletPayment(
                    invoice: $invoice,
                    reqWalletAmt: $reqWalletAmt,
                    remainingDue: $remainingDue,
                    paidAt: $paidAt,
                    actor: $actor
                );
                if ($walletPayment) {
                    $createdPayments[] = $walletPayment;
                    $remainingDue -= (int)$walletPayment->amount;
                }
            }

            // Strategy 3: Direct / Manual Payment
            if (!empty($paymentItems) && $remainingDue > 0) {
                $directPayments = $this->processDirectPayments(
                    invoice: $invoice,
                    paymentItems: $paymentItems,
                    remainingDue: $remainingDue,
                    paidAt: $paidAt,
                    actor: $actor,
                    settle: $settleOffline
                );
                foreach ($directPayments as $dp) {
                    $createdPayments[] = $dp;

                    if (in_array($dp->status, ResellerInvoicePayment::SETTLED_STATUSES, true)) {
                        $remainingDue -= (int)$dp->amount;
                    }
                }
            }

            // Update Invoice Status and Totals
            $invoice->updatePaymentStatus(false);
            if ($invoice->isPaid() && !$invoice->paid_at) {
                $invoice->paid_at = $paidAt;
            }
            $invoice->save();

            // Synchronize connected order & tenant
            $this->billingService->syncOrdersForInvoice($invoice);
        }, 3);

        // Trigger Domain Events (Workflows, Accounting Sync & Audit Trail)
        foreach ($createdPayments as $p) {
            if (in_array($p->status, ResellerInvoicePayment::SETTLED_STATUSES, true)) {
                event(new PaymentRecorded($p));
            }
        }
        if ($invoice->isPaid()) {
            event(new InvoicePaid($invoice, !empty($createdPayments) ? end($createdPayments) : null));
        }

        $settled = collect($createdPayments)->contains(
            static fn(ResellerInvoicePayment $payment): bool => in_array($payment->status, ResellerInvoicePayment::SETTLED_STATUSES, true)
        );

        return [
            'type' => $settled ? 'success' : 'pending',
            'payments' => $createdPayments,
            'invoice' => $invoice,
        ];
    }

    /**
     * Try processing as online gateway request. Returns redirect array if successful, null if not applicable.
     */
    protected function tryProcessOnlineGateway(
        ResellerInvoice $invoice,
        array           $item,
        int             $currentDue,
        float           $conversionFactor,
        ?User           $actor
    ): ?array
    {
        $methodKey = $item['method'] ?? '';
        $itemGateway = $item['gateway'] ?: $methodKey;
        $activeGateways = ResellerHelper::getAvailableGateways();

        if (!isset($activeGateways[$itemGateway]) || !empty($item['transaction_id'])) {
            return null;
        }

        $amountToPay = min((int)$item['amount'], $currentDue);
        $paymentService = new PaymentService($itemGateway);
        $verifyUrl = route('reseller.invoices.verify', [
            'invoice' => $invoice->id,
            'gateway' => $itemGateway,
        ]);

        $email = $invoice->sub_account_email ?? $actor?->email ?? '';
        $mobile = $invoice->sub_account_mobile ?? $actor?->mobile ?? '';
        $amountInPaymentCurrency = (float)$this->amounts->convert(
            $amountToPay,
            $this->amounts->sourceCurrency($invoice)
        );

        $result = $paymentService->requestPayment(
            $amountInPaymentCurrency,
            __('resellerhub::messages.invoices.paid_via_method', ['invoice' => $invoice->invoice_number]),
            $email,
            $mobile,
            $verifyUrl
        );

        if (!empty($result['success']) && !empty($result['payment_url'])) {
            $invoice->payments()->create([
                'user_id' => $actor?->id,
                'amount' => $amountToPay,
                'method' => 'online',
                'gateway' => $itemGateway,
                'paid_at' => now(),
                'transaction_id' => $result['authority'] ?? ('ONLINE-' . time()),
                'status' => 'pending',
                'notes' => __('resellerhub::messages.invoices.pending_online_notes', ['gateway' => ResellerHelper::getGatewayName($itemGateway)]),
            ]);

            return [
                'type' => 'redirect',
                'url' => $result['payment_url'],
            ];
        }

        $gatewayLabel = ResellerHelper::getGatewayName($itemGateway);
        throw new ResellerHubException(__('resellerhub::messages.invoices.gateway_error', [
            'gateway' => $gatewayLabel,
            'error' => ($result['message'] ?? __('resellerhub::messages.invoices.unknown')),
        ]));
    }

    /**
     * Process wallet payment strategy.
     */
    protected function processWalletPayment(
        ResellerInvoice $invoice,
        int             $reqWalletAmt,
        int             $remainingDue,
        Carbon          $paidAt,
        ?User           $actor
    ): ?ResellerInvoicePayment
    {
        $customerWallet = null;
        $customerUser = null;
        if (Module::has('Wallet') && Module::isEnabled('Wallet') && class_exists(Wallet::class)) {
            $customerUser = $invoice->customerUser();

            if ($customerUser) {
                $customerWallet = Wallet::where('holder_type', get_class($customerUser))
                    ->where('holder_id', $customerUser->id)
                    ->first();
            }
        }

        if (!$customerWallet || (float)$customerWallet->balance <= 0) {
            throw new ResellerHubException(__('resellerhub::messages.invoices.wallet_insufficient'));
        }

        $walletBalance = (int)$customerWallet->balance;
        $walletReq = ($reqWalletAmt > 0) ? $reqWalletAmt : $remainingDue;
        $walletAmountToUse = min($walletReq, $walletBalance, $remainingDue);

        if ($walletAmountToUse <= 0) {
            return null;
        }

        $walletHolder = $customerWallet->holder ?? $customerUser;
        if ($walletHolder && class_exists(WalletService::class)) {
            try {
                $transType = class_exists(TransactionType::class) ? TransactionType::PAYMENT : 'payment';
                app(WalletService::class)->withdraw(
                    $walletHolder,
                    $walletAmountToUse,
                    $transType,
                    $invoice,
                    __('resellerhub::messages.invoices.wallet_withdraw_desc', ['invoice' => $invoice->invoice_number]),
                    ['invoice_id' => $invoice->id]
                );
            } catch (Throwable $we) {
                Log::error('[ProcessInvoicePaymentService] Wallet withdraw failed: ' . $we->getMessage());
                throw new ResellerHubException(__('resellerhub::messages.invoices.wallet_withdraw_error', ['error' => $we->getMessage()]));
            }
        } else {
            if ($customerWallet->balance < $walletAmountToUse) {
                throw new ResellerHubException(__('resellerhub::messages.invoices.wallet_insufficient'));
            }
            $customerWallet->decrement('balance', $walletAmountToUse);
        }

        return $invoice->payments()->create([
            'user_id' => $actor?->id,
            'amount' => $walletAmountToUse,
            'method' => 'wallet',
            'gateway' => null,
            'paid_at' => $paidAt,
            'transaction_id' => 'WALLET-' . time(),
            'notes' => __('resellerhub::messages.invoices.paid_via_wallet', ['invoice' => $invoice->invoice_number]),
            'status' => 'paid',
        ]);
    }

    /**
     * Process direct / manual payment items strategy.
     *
     * When `$settle` is false the rows are recorded as *claims*: they show up
     * on the invoice for the reseller to review, but they do not count towards
     * the paid amount and never unlock modules on their own.
     *
     * @return ResellerInvoicePayment[]
     */
    protected function processDirectPayments(
        ResellerInvoice $invoice,
        array           $paymentItems,
        int             $remainingDue,
        Carbon          $paidAt,
        ?User           $actor,
        bool            $settle = true
    ): array
    {
        $payments = [];

        foreach ($paymentItems as $item) {
            if ($settle && $remainingDue <= 0) {
                break;
            }

            $method = $item['method'] ?? null;
            $itemAmt = (int)($item['amount'] ?? 0);

            if (empty($method) || $itemAmt <= 0 || $method === 'wallet') {
                continue;
            }

            $amountToPay = $settle ? min($itemAmt, $remainingDue) : $itemAmt;

            $payment = $invoice->payments()->create([
                'user_id' => $actor?->id,
                'amount' => $amountToPay,
                'method' => $method,
                'gateway' => $item['gateway'] ?? null,
                'paid_at' => $paidAt,
                'transaction_id' => $item['transaction_id'] ?? null,
                'notes' => $settle
                    ? __('resellerhub::messages.invoices.paid_via_method', ['invoice' => $invoice->invoice_number])
                    : __('resellerhub::messages.invoices.manual_claim_awaiting_confirmation', ['invoice' => $invoice->invoice_number]),
                'status' => $settle ? ResellerInvoicePayment::STATUS_PAID : ResellerInvoicePayment::STATUS_PENDING,
            ]);

            $payments[] = $payment;

            if ($settle) {
                $remainingDue -= $amountToPay;
            }
        }

        return $payments;
    }

    /**
     * Whether the acting user is allowed to record money as received.
     *
     * Reuses the invoice policy so the rule lives in one place: only a user who
     * may change the invoice status (its reseller, or a super-admin) can settle
     * an offline payment.
     */
    protected function actorCanSettleOffline(?User $actor, ResellerInvoice $invoice): bool
    {
        if (!$actor) {
            return false;
        }

        try {
            return (bool)$actor->can('updateStatus', $invoice);
        } catch (Throwable $e) {
            Log::warning('[ProcessInvoicePaymentService] Falling back to "pending" for offline payment: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Normalize payment items from array or legacy single method fields.
     */
    protected function normalizePaymentItems(
        array   $rawItems,
        ?string $singleMethod,
                $singleAmount,
        ?string $singleTxId,
        ?string $singleGateway,
        float   $conversionFactor
    ): array
    {
        $items = [];

        foreach ($rawItems as $item) {
            if (is_array($item) && !empty($item['method']) && !str_starts_with((string)$item['method'], 'cheque-')) {
                $submittedAmt = (float)str_replace(',', '', (string)($item['amount'] ?? 0));
                if ($submittedAmt > 0) {
                    $amtInResellerCurrency = (int)round($submittedAmt / $conversionFactor);
                    if ($amtInResellerCurrency > 0) {
                        $items[] = [
                            'method' => $item['method'],
                            'amount' => $amtInResellerCurrency,
                            'transaction_id' => $item['transaction_id'] ?? null,
                            'gateway' => $item['gateway'] ?? null,
                        ];
                    }
                }
            }
        }

        $singleAmount = (float)str_replace(',', '', (string)$singleAmount);
        if (empty($items) && !empty($singleMethod) && $singleMethod !== 'wallet' && !str_starts_with($singleMethod, 'cheque-') && $singleAmount > 0) {
            $amtInResellerCurrency = (int)round($singleAmount / $conversionFactor);
            if ($amtInResellerCurrency > 0) {
                $items[] = [
                    'method' => $singleMethod,
                    'amount' => $amtInResellerCurrency,
                    'transaction_id' => $singleTxId,
                    'gateway' => $singleGateway,
                ];
            }
        }

        return $items;
    }
}
