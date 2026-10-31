<?php

namespace App\Providers;

use App\Tenancy\TenancyModelRegistry;
use App\Tenancy\TenantManager;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // رجیستر کردن سینگلتون مدیریت مستأجرین
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });

        // هلپر دسترسی سریع در کانتینر: app('currentTenant')
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
    }
}
