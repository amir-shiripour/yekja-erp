<?php

namespace Modules\DomainManager\Providers;

use App\Models\CronTask;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;
use Modules\DomainManager\Console\Commands\DomainManagerInstall;
use Modules\DomainManager\Console\Commands\DomainManagerUpdatePermissions;
use Modules\DomainManager\Console\SyncDomainsCommand;
use Throwable;

class DomainManagerServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'DomainManager';
    protected string $moduleNameLower = 'domainmanager';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/domainmanager.php', 'domainmanager');
        $this->mergeConfigFrom(__DIR__.'/../Config/permissions.php', 'permissions.domainmanager');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'domainmanager');
        $this->registerTranslations();
        $this->mapRoutes();
        $this->registerCommandsAndSchedule();
        $this->registerCronTasks();
    }

    /**
     * Register console commands and automated cron schedule.
     */
    protected function registerCommandsAndSchedule(): void
    {
        $this->commands([
            SyncDomainsCommand::class,
            DomainManagerInstall::class,
            DomainManagerUpdatePermissions::class,
        ]);
    }

    /**
     * Auto-register or normalize cron task in central cron system.
     */
    protected function registerCronTasks(): void
    {
        try {
            if (Schema::hasTable('cron_tasks')) {
                // Auto-fix any misspelled or prefixed commands (e.g. 'artisan domainmanager:sync')
                CronTask::where(function ($q) {
                    $q->where('command', 'like', '%domainmanager:sync%')
                      ->orWhere('command', 'like', '%artisan%domain%')
                      ->orWhere(function ($sub) {
                          $sub->where('module', 'DomainManager')->where('command', 'like', 'artisan%');
                      });
                })->where('command', '!=', 'domainmanager:sync')
                  ->update(['command' => 'domainmanager:sync']);

                CronTask::firstOrCreate(
                    ['command' => 'domainmanager:sync'],
                    [
                        'module' => 'DomainManager',
                        'name' => 'همگام‌سازی دامنه‌ها و کیف‌پول کارگزاری‌ها',
                        'description' => 'همگام‌سازی خودکار و روزانه اطلاعات دامنه‌ها، تاریخ انقضا و موجودی کیف پول از کارگزاری‌های فعال',
                        'expression' => 'daily',
                        'prevent_overlap' => true,
                        'is_active' => true,
                        'is_system' => true,
                        'next_run_at' => now()->addDay(),
                    ]
                );
            }
        } catch (Throwable $e) {
            // Fail silently
        }
    }

    protected function mapRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth'])
                ->prefix('user')
                ->name('user.')
                ->group(__DIR__.'/../Routes/user.php');
        }
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../lang', $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
        }
    }
}
