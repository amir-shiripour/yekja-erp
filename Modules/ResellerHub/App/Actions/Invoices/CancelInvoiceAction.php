<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Actions\Invoices;

use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Events\InvoiceCancelled;
use Modules\ResellerHub\App\Events\InvoiceStatusChanged;
use Modules\ResellerHub\App\Events\PaymentCancelled;
use Modules\ResellerHub\App\Exceptions\ResellerHubException;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Services\Adapters\WalletAdapterInterface;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use RuntimeException;
use Throwable;

/**
 * Single implementation of invoice cancellation.
 */
class CancelInvoiceAction
{
    public function __construct(
        protected ResellerBillingService $billingService,
        protected WalletAdapterInterface $walletAdapter,
    )
    {
    }

    /**
     * @throws RuntimeException when the invoice can no longer be cancelled.
     */
    public function execute(ResellerInvoice $invoice): ResellerInvoice
    {
        if ($invoice->isCancelled()) {
            return $invoice;
        }

        if ($invoice->isPaid()) {
            throw new RuntimeException(__('resellerhub::messages.invoices.cannot_cancel_paid'));
        }

        $oldStatus = $invoice->status;

        DB::transaction(function () use ($invoice) {
            /** @var ResellerInvoice $locked */
            $locked = ResellerInvoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isCancelled()) {
                return;
            }

            $gatewaySettlements = $locked->payments()
                ->whereIn('status', ResellerInvoicePayment::SETTLED_STATUSES)
                ->where(static function ($query): void {
                    $query->where('method', 'online')->orWhereNotNull('gateway');
                })
                ->get();

            if ($gatewaySettlements->isNotEmpty()) {
                throw new ResellerHubException(__('resellerhub::messages.invoices.cannot_cancel_gateway_settled', [
                    'count' => ResellerHelper::toPersianDigits($gatewaySettlements->count()),
                ]));
            }

            $payments = $locked->payments()
                ->where('status', '!=', ResellerInvoicePayment::STATUS_CANCELED)
                ->lockForUpdate()
                ->get();

            foreach ($payments as $payment) {
                $this->reversePayment($locked, $payment);

                $payment->status = ResellerInvoicePayment::STATUS_CANCELED;
                $payment->save();

                event(new PaymentCancelled($payment));
            }

            $locked->status = InvoiceStatus::CANCELLED->value;
            $locked->paid_amount = 0;
            $locked->paid_at = null;
            $locked->save();

            $this->billingService->syncOrdersForInvoice($locked);
        }, 3);

        $invoice->refresh();

        event(new InvoiceCancelled($invoice));
        event(new InvoiceStatusChanged(
            $invoice,
            InvoiceStatus::CANCELLED->value,
            $oldStatus instanceof InvoiceStatus ? $oldStatus->value : (string)$oldStatus
        ));

        return $invoice;
    }

    /**
     * Reverse the financial effect of a single payment.
     * use the exact same refund semantics.
     */
    public function reversePayment(ResellerInvoice $invoice, ResellerInvoicePayment $payment): void
    {
        if ($payment->status === ResellerInvoicePayment::STATUS_CANCELED) {
            return;
        }

        // FIN-04: Generate reversal credit document audit for non-wallet payments
        if ($payment->method !== 'wallet' || (int)$payment->amount <= 0) {
            try {
                app(ResellerAuditService::class)->logPaymentReversedManual($invoice, $payment);
            } catch (Throwable $e) {
                // Non-blocking
            }
            return;
        }

        $customerUser = $invoice->customerUser();

        if (!$customerUser) {
            return;
        }

        // FIN-02: Prevent duplicate refund via status or notes marker
        if ($payment->status === ResellerInvoicePayment::STATUS_CANCELED || str_contains($payment->notes ?? '', '[wallet_refunded:')) {
            return;
        }

        $this->walletAdapter->refund(
            $customerUser,
            (int)$payment->amount,
            $invoice,
            __('resellerhub::messages.invoices.wallet_refund_description', ['invoice' => $invoice->invoice_number]),
            ['invoice_id' => $invoice->id, 'payment_id' => $payment->id]
        );

        $payment->status = ResellerInvoicePayment::STATUS_CANCELED;
        $payment->notes = trim(($payment->notes ?? '') . ' [wallet_refunded:' . now()->toIso8601String() . ']');
        $payment->save();
    }
}
