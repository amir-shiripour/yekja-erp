<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services\Invoices;

use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Support\CurrencyContext;


final class InvoiceSettlementService
{
    public function calculatePaidAmount(ResellerInvoice $invoice): int
    {
        if ($invoice->relationLoaded('validPayments')) {
            return $this->sumSettled($invoice->getRelation('validPayments'));
        }

        if ($invoice->relationLoaded('payments')) {
            return $this->sumSettled($invoice->getRelation('payments'));
        }

        return (int)$invoice->validPayments()->sum('amount');
    }

    public function isPaid(ResellerInvoice $invoice): bool
    {
        if ($invoice->isCancelled()) {
            return false;
        }

        if ($invoice->status_enum === InvoiceStatus::PAID) {
            return true;
        }

        if ((int)$invoice->total === 0) {
            return $invoice->status_enum !== InvoiceStatus::DRAFT;
        }

        if ((int)$invoice->total < 0) {
            return false;
        }

        return max(0, (int)$invoice->total - $this->calculatePaidAmount($invoice)) <= 0;
    }

    public function remainingAmount(ResellerInvoice $invoice): int
    {
        if ($this->isPaid($invoice) || $invoice->isCancelled() || (int)$invoice->total <= 0) {
            return 0;
        }

        $paid = array_key_exists('paid_amount', $invoice->getAttributes())
        && $invoice->getAttributes()['paid_amount'] !== null
            ? (int)$invoice->getAttributes()['paid_amount']
            : $this->calculatePaidAmount($invoice);

        return max(0, (int)$invoice->total - $paid);
    }

    public function updatePaymentStatus(ResellerInvoice $invoice, bool $save = true): ResellerInvoice
    {
        $invoice->paid_amount = $this->calculatePaidAmount($invoice);

        if ($invoice->isCancelled()) {
            $invoice->status = ResellerInvoice::STATUS_CANCELLED;
        } elseif (($invoice->total > 0 && $invoice->paid_amount >= $invoice->total)
            || ((int)$invoice->total === 0 && $this->isPaid($invoice))) {
            $invoice->status = ResellerInvoice::STATUS_PAID;

            if (!$invoice->paid_at) {
                $invoice->paid_at = now();
            }
        } elseif ($invoice->isOverdue()) {
            $invoice->status = ResellerInvoice::STATUS_OVERDUE;
        } else {
            $invoice->status = ResellerInvoice::STATUS_UNPAID;
        }

        if ($save) {
            $invoice->save();
        }

        return $invoice;
    }

    public function recordPayment(
        ResellerInvoice $invoice,
        int             $amount,
        string          $method = 'direct',
        ?string         $transactionId = null,
        ?string         $notes = null,
        ?int            $userId = null,
        ?string         $idempotencyKey = null,
    ): ResellerInvoicePayment
    {
        $attributes = [
            'user_id' => $userId ?? auth()->id() ?? $invoice->created_by,
            'amount' => $amount,
            'method' => $method,
            'gateway' => null,
            'paid_at' => now(),
            'transaction_id' => $transactionId ?? ('PAY-' . time() . '-' . random_int(100, 999)),
            'notes' => $notes ?? ('ثبت پرداخت برای فاکتور #' . $invoice->invoice_number),
            'status' => ResellerInvoicePayment::STATUS_PAID,
        ];

        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            // Idempotent replay: return the already-recorded payment instead of
            // creating a duplicate settled row (P1-2).
            $existing = $invoice->payments()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $attributes['idempotency_key'] = $idempotencyKey;
        }

        if ($this->hasCurrencyColumn($invoice)) {
            $attributes['currency'] = $invoice->getAttribute('currency')
                ?: CurrencyContext::forReseller((int)$invoice->reseller_id);
        }

        $payment = $invoice->payments()->create($attributes);

        $this->updatePaymentStatus($invoice, true);

        return $payment;
    }

    /**
     * @param iterable<int, ResellerInvoicePayment> $payments
     */
    private function sumSettled(iterable $payments): int
    {
        $total = 0;

        foreach ($payments as $payment) {
            if (in_array($payment->status, ResellerInvoicePayment::SETTLED_STATUSES, true)) {
                $total += (int)$payment->amount;
            }
        }

        return $total;
    }

    private function hasCurrencyColumn(ResellerInvoice $invoice): bool
    {
        return CurrencyContext::supportsColumn('reseller_invoice_payments');
    }
}
