<?php

namespace Modules\ResellerHub\App\Observers;

use App\Models\Tenant;
use App\Models\TenantModule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Services\Tenant\TenantStatusSyncService;
use Throwable;

class ResellerOrderObserver
{
    /**
     * Handle the ResellerOrder "updated" event.
     */
    public function updated(ResellerOrder $order): void
    {
        if (!$order->wasChanged('status') && !$order->wasChanged('end_date')) {
            return;
        }

        $this->syncTenantStatus($order);
    }

    /**
     * Handle the ResellerOrder "created" event.
     */
    public function created(ResellerOrder $order): void
    {
        $this->syncTenantStatus($order);
    }

    /**
     * Synchronize the underlying Tenant status based on ResellerOrder status.
     */
    public function syncTenantStatus(ResellerOrder $order): void
    {
        app(TenantStatusSyncService::class)->syncTenantStatus($order);
    }
}

