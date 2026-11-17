<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Tenancy\TenancyModelRegistry;
use App\Tenancy\TenantCache;
use App\Tenancy\TenantManager;
use App\Tenancy\TenantStorage;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Throwable;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Helpers/tenancy_helpers.php'))) {
            require_once app_path('Helpers/tenancy_helpers.php');
        }

        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });

        $this->app->singleton(TenantStorage::class);
        $this->app->singleton(TenantCache::class);
        $this->app->alias(TenantStorage::class, 'tenant.storage');
        $this->app->alias(TenantCache::class, 'tenant.cache');
        $this->app->bind('currentTenant', function () {
            return app(TenantManager::class)->getTenant();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Reset bound cache for clean lifecycle in tests and long-running workers
        TenancyModelRegistry::flushBound();

        // Zero-Touch Tenancy: Dynamic scoping of all module models
        TenancyModelRegistry::bootAll();

        // Zero-Touch Queue: Propagate tenant context to queued jobs
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $tenantId = app(TenantManager::class)->getTenantId();
            return $tenantId !== null ? ['tenant_id' => $tenantId] : [];
        });

        Queue::before(function (JobProcessing $event) {
            try {
                $payload = method_exists($event->job, 'payload') ? $event->job->payload() : [];
                if (!empty($payload['tenant_id'])) {
                    $tenant = Tenant::find($payload['tenant_id']);
                    if ($tenant) {
                        app(TenantManager::class)->setTenant($tenant);
                    }
                }
            } catch (Throwable) {
                // Silently ignore queue payload parsing issues
            }
        });

        Queue::after(function (JobProcessed $event) {
            app(TenantManager::class)->setTenant(null);
        });

        Queue::failing(function (JobFailed $event) {
            app(TenantManager::class)->setTenant(null);
        });
    }
}
