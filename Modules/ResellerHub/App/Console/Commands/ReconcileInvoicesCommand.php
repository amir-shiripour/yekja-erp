<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerInvoicePayment;

class ReconcileInvoicesCommand extends Command
{
    protected $signature = 'reseller:reconcile {--fix : Automatically correct divergences and resync status}';

    protected $description = 'Reconcile invoice paid amounts with ledger payments to guarantee single source of truth';

    public function handle(): int
    {
        $fix = (bool)$this->option('fix');
        $this->info('Starting ResellerHub financial reconciliation audit...');

        $invoices = ResellerInvoice::with(['payments'])->get();
        $discrepancies = 0;
        $fixed = 0;

        $tableRows = [];

        foreach ($invoices as $invoice) {
            $settledSum = (int)$invoice->payments
                ->whereIn('status', ResellerInvoicePayment::SETTLED_STATUSES)
                ->sum('amount');

            $currentPaidAmount = (int)$invoice->paid_amount;
            $total = (int)$invoice->total;
            $currentStatus = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : (string)$invoice->status;

            $hasPaidDiff = ($settledSum !== $currentPaidAmount);
            $hasStatusDiff = false;

            if ($total > 0) {
                if ($settledSum >= $total && $currentStatus !== InvoiceStatus::PAID->value && $currentStatus !== InvoiceStatus::CANCELLED->value) {
                    $hasStatusDiff = true;
                } elseif ($settledSum < $total && $currentStatus === InvoiceStatus::PAID->value) {
                    $hasStatusDiff = true;
                }
            }

            if ($hasPaidDiff || $hasStatusDiff) {
                $discrepancies++;

                $targetStatus = $currentStatus;
                if ($currentStatus !== InvoiceStatus::CANCELLED->value) {
                    if ($total > 0 && $settledSum >= $total) {
                        $targetStatus = InvoiceStatus::PAID->value;
                    } elseif ($settledSum > 0) {
                        $targetStatus = InvoiceStatus::PARTIALLY_PAID->value;
                    } else {
                        $targetStatus = InvoiceStatus::PENDING->value;
                    }
                }

                $tableRows[] = [
                    'ID' => $invoice->id,
                    'Number' => $invoice->invoice_number,
                    'Total' => number_format($total),
                    'Recorded Paid' => number_format($currentPaidAmount),
                    'Actual Payments' => number_format($settledSum),
                    'Diff' => number_format($settledSum - $currentPaidAmount),
                    'Status' => "{$currentStatus} -> {$targetStatus}",
                ];

                if ($fix) {
                    $invoice->paid_amount = $settledSum;
                    if ($currentStatus !== InvoiceStatus::CANCELLED->value) {
                        $invoice->status = $targetStatus;
                        if ($targetStatus === InvoiceStatus::PAID->value && !$invoice->paid_at) {
                            $invoice->paid_at = now();
                        }
                    }
                    $invoice->saveQuietly();
                    $fixed++;
                }
            }
        }

        if ($discrepancies > 0) {
            $this->table(
                ['ID', 'Number', 'Total', 'Recorded Paid', 'Actual Payments', 'Diff', 'Status Transition'],
                $tableRows
            );

            if ($fix) {
                $this->info("Successfully reconciled and fixed {$fixed} divergent invoice(s).");
            } else {
                $this->warn("Found {$discrepancies} divergent invoice(s). Run with --fix to reconcile.");
            }

            return self::FAILURE;
        }

        $this->info("100% reconciled: all {$invoices->count()} invoice(s) match payment ledger perfectly.");
        return self::SUCCESS;
    }
}
