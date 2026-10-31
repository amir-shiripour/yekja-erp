<?php

namespace Modules\ResellerHub\App\Services\Tenant;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Tenancy\TenantModuleActivator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Throwable;

class TenantStatusSyncService
{
    /**
     * Synchronize the underlying Tenant status based on ResellerOrder status.
     */
    public function syncTenantStatus(ResellerOrder $order): void
    {
        if (!$order->tenant_id) {
            return;
        }

        try {
            $tenant = Tenant::find($order->tenant_id);
            if (!$tenant) {
                return;
            }

            $orderStatus = strtolower(trim((string)$order->status));

            // Active condition: activate tenant and modules, sync plan expiration
            if ($orderStatus === ResellerOrder::STATUS_ACTIVE || $orderStatus === 'completed') {
                $tenant->status = Tenant::STATUS_ACTIVE;

                if ($order->end_date) {
                    $orderEnd = Carbon::parse($order->end_date);
                    if (!$tenant->plan_expires_at || $orderEnd->greaterThan($tenant->plan_expires_at)) {
                        $tenant->plan_expires_at = $orderEnd;
                    }
                }

                $tenant->save();

                // Reactivate tenant modules
                $slugsToActivate = collect($order->modules_snapshot ?? [])->pluck('slug')->toArray();
                if (!empty($slugsToActivate)) {
                    TenantModule::where('tenant_id', $tenant->id)
                        ->whereIn('module_slug', $slugsToActivate)
                        ->update(['is_active' => true]);
                } else {
                    TenantModule::where('tenant_id', $tenant->id)->update(['is_active' => true]);
                }

                TenantModuleActivator::flushTenantCache($tenant->id);

                Log::info("[TenantStatusSyncService] Tenant #{$tenant->id} activated via Order #{$order->id} status: {$orderStatus}");
            } elseif (in_array($orderStatus, [ResellerOrder::STATUS_INACTIVE, ResellerOrder::STATUS_CANCELLED, 'suspended'], true)) {
                // If the tenant still has a valid active plan expiring in the future, do not clear plan or deactivate modules
                if ($tenant->plan_expires_at && Carbon::parse($tenant->plan_expires_at)->isFuture()) {
                    Log::info("[TenantStatusSyncService] Tenant #{$tenant->id} has plan_expires_at in the future ({$tenant->plan_expires_at}). Preserving active modules.");
                    return;
                }

                $hasOtherActiveOrder = ResellerOrder::where('tenant_id', $tenant->id)
                    ->where('id', '!=', $order->id)
                    ->whereIn('status', [ResellerOrder::STATUS_ACTIVE, 'completed'])
                    ->exists();

                if (!$hasOtherActiveOrder) {
                    // Do NOT suspend the tenant itself.
                    // Keep tenant status ACTIVE so the user can still log in to their panel and access billing/portal,
                    // but clear plan_expires_at and deactivate all feature modules,
                    // exactly like a freshly registered tenant without an active package.
                    $tenant->status = Tenant::STATUS_ACTIVE;
                    $tenant->plan_expires_at = null;
                    $tenant->save();

                    // Deactivate all tenant modules (close all business features)
                    TenantModule::where('tenant_id', $tenant->id)->update(['is_active' => false]);
                    if (class_exists(TenantModuleActivator::class)) {
                        TenantModuleActivator::flushTenantCache($tenant->id);
                    }
                } else {
                    // Collect slugs from all other active orders
                    $otherActiveOrders = ResellerOrder::where('tenant_id', $tenant->id)
                        ->where('id', '!=', $order->id)
                        ->whereIn('status', [ResellerOrder::STATUS_ACTIVE, 'completed'])
                        ->get();

                    $otherActiveSlugs = [];
                    foreach ($otherActiveOrders as $oao) {
                        foreach ($oao->modules_snapshot ?? [] as $snap) {
                            if (($snap['type'] ?? '') === 'module' && !empty($snap['slug'])) {
                                $otherActiveSlugs[] = strtolower(trim($snap['slug']));
                            }
                        }
                    }

                    $thisOrderSlugs = collect($order->modules_snapshot ?? [])
                        ->where('type', 'module')
                        ->pluck('slug')
                        ->filter()
                        ->map(fn($s) => strtolower(trim($s)))
                        ->toArray();

                    $slugsToDeactivate = array_diff($thisOrderSlugs, $otherActiveSlugs);
                    if (!empty($slugsToDeactivate)) {
                        TenantModule::where('tenant_id', $tenant->id)
                            ->whereIn('module_slug', $slugsToDeactivate)
                            ->update(['is_active' => false]);
                    }
                }

                Log::info("[TenantStatusSyncService] Tenant #{$tenant->id} suspended/deactivated via Order #{$order->id} status: {$orderStatus}");
            }
        } catch (Throwable $e) {
            Log::error("[TenantStatusSyncService] Failed to sync tenant status for Order #{$order->id}: " . $e->getMessage());
        }
    }
}
