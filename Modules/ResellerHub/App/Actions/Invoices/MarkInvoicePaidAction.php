<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Actions\Invoices;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\DTOs\PaymentRecordDTO;
use Modules\ResellerHub\App\Events\InvoicePaid;
use Modules\ResellerHub\App\Events\PaymentRecorded;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use RuntimeException;
use Throwable;

class MarkInvoicePaidAction
{
    /**
     * Mark an invoice as paid, record payment entries, and sync/convert to order.
     * @param ResellerInvoice $invoice
     * @param PaymentRecordDTO|array $paymentData
     * @return ResellerOrder
     * @throws Throwable
     */
    public function execute(ResellerInvoice $invoice, PaymentRecordDTO|array $paymentData = []): ResellerOrder
    {
        $dto = $paymentData instanceof PaymentRecordDTO ? $paymentData : PaymentRecordDTO::fromArray($paymentData);

        /** @var array<int, ResellerInvoicePayment> $recordedPayments */
        $recordedPayments = [];

        /** @var ResellerOrder $order */
        $order = DB::transaction(static function () use ($invoice, $dto, &$recordedPayments): ResellerOrder {
            /** @var ResellerInvoice $lockedInvoice */
            $lockedInvoice = ResellerInvoice::query()
                ->whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvoice->isCancelled()) {
                throw new RuntimeException('Cancelled invoices cannot be marked as paid.');
            }

            $total = (int)$lockedInvoice->total;
            if ($total < 0) {
                throw new RuntimeException('Invoices with a negative total cannot be marked as paid.');
            }

            $alreadyPaid = $lockedInvoice->calculatePaidAmount();
            $due = max(0, $total - $alreadyPaid);

            // 1) Accept what the customer already claimed, oldest first.
            $due = self::settlePendingClaims($lockedInvoice, $due, $recordedPayments);

            // 2) Record whatever is still outstanding as a manual payment.
            if ($due > 0) {
                $method = $dto->paymentMethod ?? $lockedInvoice->payment_method ?? 'direct';
                $txId = $dto->transactionRef
                    ?? $lockedInvoice->transaction_ref
                    ?? ('SYS-' . $lockedInvoice->id . '-' . now()->format('YmdHis'));
                $invNumFa = ResellerHelper::toPersianDigits((string)$lockedInvoice->invoice_number);
                $paymentNote = $dto->notes
                    ?: __('resellerhub::messages.invoices.full_settlement_note', ['invoice' => $invNumFa]);

                $recordedPayments[] = ResellerInvoicePayment::create([
                    'invoice_id' => $lockedInvoice->id,
                    'user_id' => auth()->id() ?? $lockedInvoice->created_by,
                    'amount' => $due,
                    'method' => $method,
                    'gateway' => $dto->gateway,
                    'paid_at' => now(),
                    'transaction_id' => $txId,
                    'notes' => $paymentNote,
                    'status' => ResellerInvoicePayment::STATUS_PAID,
                ]);
            }

            $lockedInvoice->paid_amount = $lockedInvoice->calculatePaidAmount();
            $lockedInvoice->updatePaymentStatus(false);

            if ($lockedInvoice->status !== ResellerInvoice::STATUS_PAID) {
                $lockedInvoice->paid_at = null;
            } elseif (!$lockedInvoice->paid_at) {
                $lockedInvoice->paid_at = Carbon::now();
            }

            if (!empty($dto->paymentMethod)) {
                $lockedInvoice->payment_method = $dto->paymentMethod;
            }
            if (!empty($dto->transactionRef)) {
                $lockedInvoice->transaction_ref = $dto->transactionRef;
            }
            $lockedInvoice->save();

            return app(ResellerBillingService::class)->convertInvoiceToOrder($lockedInvoice);
        }, 3);

        $invoice->refresh();

        foreach ($recordedPayments as $payment) {
            event(new PaymentRecorded($payment));
        }
        if ($invoice->isPaid()) {
            event(new InvoicePaid($invoice, $recordedPayments === [] ? null : end($recordedPayments)));
        }

        return $order;
    }

    /**
     * Turn pending manual claims into settled payments, capped by the amount due.
     *
     * @param array<int, ResellerInvoicePayment> $recorded
     * @return int The remaining amount still due after the claims were accepted.
     */
    private static function settlePendingClaims(
        ResellerInvoice $invoice,
        int             $due,
        array           &$recorded
    ): int
    {
        if ($due <= 0) {
            return $due;
        }

        $claims = $invoice->payments()
            ->where('status', ResellerInvoicePayment::STATUS_PENDING)
            ->where('method', '!=', 'online')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($claims as $claim) {
            if ($due <= 0) {
                break;
            }

            $accepted = min((int)$claim->amount, $due);

            $claim->forceFill([
                'amount' => $accepted,
                'status' => ResellerInvoicePayment::STATUS_PAID,
                'paid_at' => $claim->paid_at ?? now(),
                'notes' => trim(
                    (string)$claim->notes . ' | ' . __('resellerhub::messages.invoices.claim_confirmed_note')
                ),
            ])->save();

            $recorded[] = $claim;
            $due -= $accepted;
        }

        return $due;
    }
}
