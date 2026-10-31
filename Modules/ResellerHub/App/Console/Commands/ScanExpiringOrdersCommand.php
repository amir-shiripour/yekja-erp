<?php

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Enums\OrderStatus;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\ResellerWorkflowService;
use Modules\ResellerHub\App\Services\Tenant\TenantStatusSyncService;
use Throwable;

class ScanExpiringOrdersCommand extends Command
{
    protected $signature = 'reseller:scan-expiry
                            {--dry-run : Run check without modifying database records}
                            {--grace-days=1 : Days of grace after expiry before deactivating order}';

    protected $description = 'Scan reseller orders nearing expiration or expired, trigger reminders, and suspend overdue expired orders';

    public function handle(
        ResellerWorkflowService $workflowService,
        TenantStatusSyncService $syncService
    ): int
    {
        $isDryRun = (bool)$this->option('dry-run');
        $graceDays = (int)$this->option('grace-days');

        $today = Carbon::today();
        $suspensionCutoffDate = $today->copy()->subDays($graceDays)->toDateString();

        $this->info("Scanning expired orders with expiration <= {$suspensionCutoffDate} (Dry run: " . ($isDryRun ? 'YES' : 'NO') . ")");

        // 1. Suspend orders past their expiration + grace days
        $expiredOrders = ResellerOrder::whereIn('status', [
            OrderStatus::ACTIVE->value,
            'active',
            'completed',
        ])
            ->where(function ($query) use ($suspensionCutoffDate) {
                $query->whereDate('renewal_date', '<=', $suspensionCutoffDate)
                    ->orWhere(function ($q) use ($suspensionCutoffDate) {
                        $q->whereNull('renewal_date')
                            ->whereDate('end_date', '<=', $suspensionCutoffDate);
                    });
            })
            ->get();

        $suspendedCount = 0;
        foreach ($expiredOrders as $order) {
            $oldStatus = (string)$order->status;
            $expDate = $order->renewal_date?->format('Y-m-d') ?: $order->end_date?->format('Y-m-d') ?: 'unknown';

            $this->line("Expired Order #{$order->order_number} (ID: {$order->id}) - Expired on: {$expDate}");

            if ($isDryRun) {
                $suspendedCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($order, $oldStatus, $workflowService, $syncService) {
                    if ($order->canTransitionTo(OrderStatus::INACTIVE)) {
                        $order->transitionTo(OrderStatus::INACTIVE);
                    } else {
                        $order->status = OrderStatus::INACTIVE->value;
                        $order->save();
                    }

                    // Synchronize tenant and module statuses
                    $syncService->syncTenantStatus($order);

                    // Trigger workflow events
                    $workflowService->onOrderStatusChanged($order, OrderStatus::INACTIVE->value, $oldStatus);
                });

                $suspendedCount++;
                Log::info("[ScanExpiringOrders] Order #{$order->id} deactivated due to expiration on {$expDate}.");
            } catch (Throwable $e) {
                $this->error("Failed to deactivate order #{$order->id}: " . $e->getMessage());
                Log::error("[ScanExpiringOrders] Error on order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Deactivated {$suspendedCount} expired orders (tenant features closed).");

        return 0;
    }
}
