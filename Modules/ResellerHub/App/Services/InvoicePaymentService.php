<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Services\PaymentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Events\InvoicePaid;
use Modules\ResellerHub\App\Events\PaymentRecorded;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\Payment\GatewayAmountResolver;
use Throwable;

class InvoicePaymentService
{
    protected ResellerWorkflowService $workflowService;

    public function __construct(
        ResellerWorkflowService         $workflowService,
        protected GatewayAmountResolver $amounts = new GatewayAmountResolver(),
    )
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Verify online payment callback for an invoice with concurrency locking.
     *
     * @param ResellerInvoice $invoice
     * @param array $callbackData
     * @param string|null $gateway
     * @return array{
     *     success: bool,
     *     message: string,
     *     ref_id: string|null,
     *     already_paid: bool,
     *     payment: ResellerInvoicePayment|null
     * }
     */
    public function verifyOnlinePayment(ResellerInvoice $invoice, array $callbackData, ?string $gateway = null): array
    {
        $lockKey = "rh_inv_verify_lock_{$invoice->id}";
        $lock = Cache::lock($lockKey, 30);

        try {
            $lock->block(10);

            // Re-fetch fresh invoice status
            $invoice->refresh();

            $incomingAuthority = (string)(
                $callbackData['Authority']
                ?? $callbackData['authority']
                ?? $callbackData['TrackId']
                ?? ''
            );

            //Check if this authority was already processed and verified
            if (!empty($incomingAuthority)) {
                $alreadyPaid = $invoice->payments()
                    ->where('status', 'paid')
                    ->where(function ($q) use ($incomingAuthority) {
                        $q->where('transaction_id', $incomingAuthority)
                            ->orWhere('notes', 'like', "%{$incomingAuthority}%");
                    })
                    ->first();

                if ($alreadyPaid) {
                    return [
                        'success' => true,
                        'message' => __('resellerhub::messages.invoices.online_payment_verified', ['ref' => $alreadyPaid->transaction_id]),
                        'ref_id' => $alreadyPaid->transaction_id,
                        'already_paid' => true,
                        'payment' => $alreadyPaid,
                    ];
                }
            }

            $gateway = $gateway ?: ($callbackData['gateway'] ?? ResellerHelper::getDefaultGateway());

            //Find pending payment specifically tied to this invoice and authority
            $pendingPayment = null;
            if (!empty($incomingAuthority)) {
                $pendingPayment = $invoice->payments()
                    ->where('status', 'pending')
                    ->where('method', 'online')
                    ->where('transaction_id', $incomingAuthority)
                    ->first();
            }

            if (!$pendingPayment) {
                $candidate = $invoice->payments()->where('status', 'pending')->where('method', 'online')->latest()->first();
                if ($candidate && (empty($incomingAuthority) || str_starts_with($candidate->transaction_id, 'ONLINE-') || $candidate->transaction_id === $incomingAuthority)) {
                    $pendingPayment = $candidate;
                }
            }

            if (!$pendingPayment) {
                Log::warning("[InvoicePaymentService] verifyOnlinePayment: No valid pending payment for invoice {$invoice->id} with authority '{$incomingAuthority}'.");
                return [
                    'success' => false,
                    'message' => __('resellerhub::messages.invoices.no_pending_payment'),
                    'ref_id' => null,
                    'already_paid' => false,
                    'payment' => null,
                ];
            }

            if ($pendingPayment->status === 'paid') {
                return [
                    'success' => true,
                    'message' => __('resellerhub::messages.invoices.already_paid'),
                    'ref_id' => $pendingPayment->transaction_id,
                    'already_paid' => true,
                    'payment' => $pendingPayment,
                ];
            }

            //Currency conversion — the pending payment is denominated in
            // the invoice currency, the gateway expects the payment currency.
            $amountInPaymentCurrency = (float)$this->amounts->convert(
                (int)$pendingPayment->amount,
                $this->amounts->sourceCurrency($invoice)
            );

            $dataToVerify = array_merge($callbackData, [
                'Amount' => $amountInPaymentCurrency,
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->id,
            ]);

            $paymentService = new PaymentService($gateway);
            $result = $paymentService->verifyPayment($dataToVerify);

            $isSuccess = !empty($result['success']) || (isset($result['status']) && $result['status'] === 'success');

            if ($isSuccess) {
                $refId = (string)($result['ref_id'] ?? $result['reference_id'] ?? $result['authority'] ?? $incomingAuthority ?: '');
                if ($refId === '') {
                    Log::error("[InvoicePaymentService] Gateway {$gateway} reported success without a transaction reference for invoice #{$invoice->id}.");
                    return [
                        'success' => false,
                        'message' => __('resellerhub::messages.invoices.payment_verify_error'),
                        'ref_id' => null,
                        'already_paid' => false,
                        'payment' => $pendingPayment,
                    ];
                }

                if (ResellerInvoicePayment::isTransactionSettledElsewhere($gateway, $refId, $invoice->id)) {
                    Log::critical("[InvoicePaymentService] Security Alert: Transaction {$refId} on gateway {$gateway} is already settled on another invoice. Blocked replay attempt on invoice #{$invoice->id}.");
                    return [
                        'success' => false,
                        'message' => __('resellerhub::messages.invoices.payment_verify_error'),
                        'ref_id' => null,
                        'already_paid' => false,
                        'payment' => $pendingPayment,
                    ];
                }

                // Security Check 2: Gateway Duplicate Flag Guard
                if (!empty($result['is_duplicate']) && $pendingPayment->transaction_id !== $refId && $pendingPayment->status !== 'paid') {
                    Log::warning("[InvoicePaymentService] Gateway reported transaction {$refId} as duplicate/already verified elsewhere. Refused on invoice #{$invoice->id}.");
                    return [
                        'success' => false,
                        'message' => __('resellerhub::messages.invoices.payment_verify_error'),
                        'ref_id' => null,
                        'already_paid' => false,
                        'payment' => $pendingPayment,
                    ];
                }

                $reportedAmount = $result['amount'] ?? $result['Amount'] ?? null;
                if (!is_numeric($reportedAmount)) {
                    Log::critical("[InvoicePaymentService] Security Alert: Gateway {$gateway} did not report verified amount for invoice #{$invoice->id}. Verification rejected.");
                    return [
                        'success' => false,
                        'message' => __('resellerhub::messages.invoices.payment_verify_error'),
                        'ref_id' => null,
                        'already_paid' => false,
                        'payment' => $pendingPayment,
                    ];
                }

                $reported = (int)round((float)$reportedAmount);
                $expected = (int)round((float)$amountInPaymentCurrency);

                if (abs($reported - $expected) > 1) {
                    Log::critical("[InvoicePaymentService] Security Alert: Amount mismatch on invoice #{$invoice->id}: gateway={$reported}, expected={$expected}.");
                    return [
                        'success' => false,
                        'message' => __('resellerhub::messages.invoices.payment_verify_error'),
                        'ref_id' => null,
                        'already_paid' => false,
                        'payment' => $pendingPayment,
                    ];
                }

                $gatewayLabel = ResellerHelper::getGatewayName($gateway);

                DB::transaction(function () use ($pendingPayment, $refId, $gatewayLabel, $invoice) {
                    $pendingPayment->update([
                        'status' => 'paid',
                        'transaction_id' => $refId,
                        'paid_at' => now(),
                        'notes' => __('resellerhub::messages.invoices.online_payment_note', ['gateway' => $gatewayLabel, 'ref' => $refId]),
                    ]);

                    $invoice->updatePaymentStatus(false);
                    if ($invoice->isPaid() && !$invoice->paid_at) {
                        $invoice->paid_at = now();
                    }
                    $invoice->save();

                    $this->syncOrdersForInvoice($invoice);
                });

                // Trigger Domain Events
                try {
                    event(new PaymentRecorded($pendingPayment));
                    if ($invoice->isPaid()) {
                        event(new InvoicePaid($invoice, $pendingPayment));
                    }
                } catch (Throwable $we) {
                    Log::warning('[InvoicePaymentService] event trigger warning: ' . $we->getMessage());
                }

                return [
                    'success' => true,
                    'message' => __('resellerhub::messages.invoices.online_payment_verified', ['ref' => $refId]),
                    'ref_id' => $refId,
                    'already_paid' => false,
                    'payment' => $pendingPayment,
                ];
            }

            // Failed payment branch
            $pendingPayment->update(['status' => 'failed']);
            $errorMessage = $result['message'] ?? __('resellerhub::messages.invoices.payment_failed');

            try {
                $this->workflowService->onPaymentFailed($invoice, $gateway, $errorMessage);
            } catch (Throwable $we) {
                Log::warning('[InvoicePaymentService] payment failed workflow warning: ' . $we->getMessage());
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'ref_id' => null,
                'already_paid' => false,
                'payment' => $pendingPayment,
            ];
        } catch (Throwable $e) {
            Log::error('[InvoicePaymentService] verifyOnlinePayment exception: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [
                'success' => false,
                'message' => __('resellerhub::messages.invoices.payment_verify_error'),
                'ref_id' => null,
                'already_paid' => false,
                'payment' => null,
            ];
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Synchronize orders associated with this invoice upon payment completion.
     */
    public function syncOrdersForInvoice(ResellerInvoice $invoice): void
    {
        $orders = ResellerOrder::where('invoice_id', $invoice->id)->get();

        foreach ($orders as $order) {
            if ($invoice->isPaid() && $order->status === ResellerOrder::STATUS_PENDING) {
                $order->update([
                    'status' => ResellerOrder::STATUS_ACTIVE,
                ]);
            }
        }
    }
}
