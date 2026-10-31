<?php

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\OrderStatus;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Throwable;

class ScanOverdueInvoicesCommand extends Command
{
    protected $signature = 'reseller:scan-overdue
                            {--dry-run : Run check without modifying database records}
                            {--grace-days=0 : Days of grace after due date before marking overdue}
                            {--cancel-after-days=7 : Days overdue after which unpaid orders are cancelled}';

    protected $description = 'Scan unpaid reseller invoices past their due date, transition them to overdue, and apply dunning policies';

    public function handle(
        ResellerWorkflowService $workflowService,
        ResellerAuditService    $auditService
    ): int
    {
        $isDryRun = (bool)$this->option('dry-run');
        $graceDays = (int)$this->option('grace-days');
        $cancelAfterDays = (int)$this->option('cancel-after-days');

        $cutoffDate = Carbon::today()->subDays($graceDays)->toDateString();
        $this->info("Scanning invoices with due_date <= {$cutoffDate} (Dry run: " . ($isDryRun ? 'YES' : 'NO') . ")");

        // 1. Mark unpaid invoices as Overdue
        $unpaidInvoices = ResellerInvoice::whereIn('status', [
            InvoiceStatus::UNPAID->value,
            'unpaid',
            'pending',
        ])
            ->whereDate('due_date', '<=', $cutoffDate)
            ->get();

        $markedOverdueCount = 0;
        foreach ($unpaidInvoices as $invoice) {
            $oldStatus = (string)$invoice->status;

            $this->line("Invoice #{$invoice->invoice_number} (ID: {$invoice->id}) - Due: {$invoice->due_date?->format('Y-m-d')}, Status: {$oldStatus}");

            if ($isDryRun) {
                $markedOverdueCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($invoice, $oldStatus, $workflowService, $auditService) {
                    if ($invoice->canTransitionTo(InvoiceStatus::OVERDUE)) {
                        $invoice->transitionTo(InvoiceStatus::OVERDUE);
                    } else {
                        $invoice->status = InvoiceStatus::OVERDUE->value;
                        $invoice->save();
                    }

                    $workflowService->onInvoiceStatusChanged($invoice, InvoiceStatus::OVERDUE->value, $oldStatus);
                });

                $markedOverdueCount++;
                Log::info("[ScanOverdueInvoices] Invoice #{$invoice->id} transitioned from {$oldStatus} to overdue.");
            } catch (Throwable $e) {
                $this->error("Failed to update invoice #{$invoice->id}: " . $e->getMessage());
                Log::error("[ScanOverdueInvoices] Error on invoice #{$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("Marked {$markedOverdueCount} invoices as overdue.");

        // 2. Dunning Enforcement: Cancel orders tied to severely overdue invoices
        if ($cancelAfterDays > 0) {
            $cancelCutoffDate = Carbon::today()->subDays($cancelAfterDays)->toDateString();
            $severelyOverdueInvoices = ResellerInvoice::where('status', InvoiceStatus::OVERDUE->value)
                ->whereDate('due_date', '<=', $cancelCutoffDate)
                ->with(['sourceOrder', 'orders'])
                ->get();

            $cancelledOrdersCount = 0;
            foreach ($severelyOverdueInvoices as $invoice) {
                // If invoice is a renewal invoice, DO NOT cancel ongoing active subscriptions!
                // An unpaid renewal invoice should remain overdue, but not cancel the tenant's current active order.
                if ($invoice->isRenewal()) {
                    Log::info("[ScanOverdueInvoices] Invoice #{$invoice->invoice_number} is a renewal invoice. Skipping Dunning cancellation of active source orders.");
                    continue;
                }

                $relatedOrders = collect([$invoice->sourceOrder])
                    ->filter()
                    ->concat($invoice->orders)
                    ->unique('id');

                foreach ($relatedOrders as $order) {
                    if ($order->status === OrderStatus::CANCELLED->value) {
                        continue;
                    }

                    // Preserve active orders whose subscription period is still in the future
                    if ($order->status === OrderStatus::ACTIVE->value && $order->end_date && Carbon::parse($order->end_date)->isFuture()) {
                        Log::info("[ScanOverdueInvoices] Order #{$order->order_number} is currently active until {$order->end_date}. Skipping Dunning cancellation.");
                        continue;
                    }

                    $this->warn("Dunning cancellation: Order #{$order->order_number} (Invoice #{$invoice->invoice_number} overdue for >= {$cancelAfterDays} days)");

                    if ($isDryRun) {
                        $cancelledOrdersCount++;
                        continue;
                    }

                    try {
                        if ($order->canTransitionTo(OrderStatus::CANCELLED)) {
                            $order->transitionTo(OrderStatus::CANCELLED);
                        } else {
                            $order->status = OrderStatus::CANCELLED->value;
                            $order->save();
                        }

                        $workflowService->onOrderStatusChanged($order, OrderStatus::CANCELLED->value, (string)$order->getOriginal('status'));
                        $cancelledOrdersCount++;
                        Log::info("[ScanOverdueInvoices] Dunning cancelled Order #{$order->id} for overdue Invoice #{$invoice->id}");
                    } catch (Throwable $e) {
                        Log::error("[ScanOverdueInvoices] Failed to cancel Order #{$order->id}: " . $e->getMessage());
                    }
                }
            }

            $this->info("Cancelled {$cancelledOrdersCount} orders under Dunning policy ({$cancelAfterDays}+ days overdue).");
        }

        return 0;
    }
}
