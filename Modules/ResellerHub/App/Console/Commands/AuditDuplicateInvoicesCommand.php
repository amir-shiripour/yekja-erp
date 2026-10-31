<?php

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\ResellerHub\App\Models\ResellerInvoice;


class AuditDuplicateInvoicesCommand extends Command
{
    protected $signature = 'reseller:audit-duplicates
                            {--cancel : Cancel all but the newest duplicate pending invoice per group}';

    protected $description = 'Detect (and optionally cancel) duplicate pending invoices per tenant + package';

    public function handle(): int
    {
        $cancel = (bool)$this->option('cancel');

        $groups = ResellerInvoice::query()
            ->selectRaw('reseller_id, tenant_id, package_id, COUNT(*) as cnt')
            ->whereNull('parent_invoice_id')
            ->whereIn('status', [ResellerInvoice::STATUS_UNPAID, ResellerInvoice::STATUS_OVERDUE])
            ->whereNotNull('tenant_id')
            ->whereNotNull('package_id')
            ->groupBy('reseller_id', 'tenant_id', 'package_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate pending invoices found.');

            return self::SUCCESS;
        }

        $this->warn(sprintf('%d duplicate group(s) found (cancel: %s)', $groups->count(), $cancel ? 'YES' : 'NO'));

        foreach ($groups as $group) {
            $invoices = ResellerInvoice::query()
                ->where('reseller_id', $group->reseller_id)
                ->where('tenant_id', $group->tenant_id)
                ->where('package_id', $group->package_id)
                ->whereNull('parent_invoice_id')
                ->whereIn('status', [ResellerInvoice::STATUS_UNPAID, ResellerInvoice::STATUS_OVERDUE])
                ->orderByDesc('id')
                ->get();

            $this->line(sprintf(
                '  reseller=%d tenant=%d package=%d — %d invoice(s)',
                $group->reseller_id,
                $group->tenant_id,
                $group->package_id,
                $invoices->count()
            ));

            // Newest invoice is kept; the rest are duplicates.
            $invoices->skip(1)->each(function (ResellerInvoice $invoice) use ($cancel): void {
                $this->line(sprintf('    duplicate #%d %s (%s)', $invoice->id, $invoice->invoice_number, $invoice->status));

                if (!$cancel) {
                    return;
                }

                $invoice->status = ResellerInvoice::STATUS_CANCELLED;
                $invoice->notes = trim(($invoice->notes ?? '') . "\n[auto] Cancelled by reseller:audit-duplicates (duplicate pending invoice)");
                $invoice->save();
                $this->info(sprintf('    -> cancelled #%d', $invoice->id));
            });
        }

        if (!$cancel) {
            $this->comment('Report only — re-run with --cancel to cancel duplicates (newest kept).');
        }

        return self::SUCCESS;
    }
}
