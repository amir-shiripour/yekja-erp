<?php

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;


class BackfillLegacyPaymentsCommand extends Command
{
    protected $signature = 'reseller:backfill-payments
                            {--apply : Actually create the missing payment rows}
                            {--id= : Backfill a single invoice id (for targeted repairs)}';

    protected $description = 'Create missing settled payment rows for invoices with paid_amount > 0 (legacy data repair)';

    public function handle(): int
    {
        $apply = (bool)$this->option('apply');
        $singleId = $this->option('id');

        $query = ResellerInvoice::query()
            ->where('paid_amount', '>', 0)
            ->whereNotIn('status', [ResellerInvoice::STATUS_CANCELLED])
            ->whereDoesntHave('payments', fn($q) => $q->whereIn('status', ResellerInvoicePayment::SETTLED_STATUSES));

        if ($singleId !== null) {
            $query->whereKey((int)$singleId);
        }

        $invoices = $query->orderBy('id')->get();

        if ($invoices->isEmpty()) {
            $this->info('No legacy invoices need backfill. Invariant intact.');

            return self::SUCCESS;
        }

        $this->warn(sprintf('%d invoice(s) have paid_amount > 0 without settled payment rows (apply: %s)', $invoices->count(), $apply ? 'YES' : 'NO'));

        $repaired = 0;
        $overpaid = 0;

        foreach ($invoices as $invoice) {
            $paid = (int)$invoice->paid_amount;
            $total = (int)$invoice->total;
            $isOverpaid = $total > 0 && $paid > $total;

            $this->line(sprintf(
                '  #%d %s | paid=%s total=%s%s',
                $invoice->id,
                $invoice->invoice_number,
                number_format($paid),
                number_format($total),
                $isOverpaid ? ' [OVERPAID — recording actual paid amount]' : ''
            ));

            if ($isOverpaid) {
                $overpaid++;
            }

            if (!$apply) {
                continue;
            }

            DB::transaction(function () use ($invoice, $paid): void {
                $invoice->payments()->create([
                    'user_id' => $invoice->created_by,
                    'amount' => $paid,
                    'method' => 'legacy_backfill',
                    'gateway' => null,
                    'paid_at' => $invoice->paid_at ?? $invoice->updated_at ?? now(),
                    'transaction_id' => 'LEGACY-BACKFILL-' . $invoice->id,
                    'notes' => 'Backfill: legacy paid_amount without payment row (reseller:backfill-payments)',
                    'status' => ResellerInvoicePayment::STATUS_PAID,
                ]);
            });

            $repaired++;
        }

        if ($apply) {
            $this->info(sprintf('Done. %d payment row(s) created, %d overpaid flagged.', $repaired, $overpaid));
        } else {
            $this->comment('Dry run only — re-run with --apply to write changes.');
        }

        return self::SUCCESS;
    }
}
