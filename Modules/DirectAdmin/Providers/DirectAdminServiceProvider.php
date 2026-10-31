<?php

namespace Modules\DirectAdmin\Providers;

use App\Models\CronTask;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\DirectAdmin\Console\Commands\DirectAdminInstall;
use Modules\DirectAdmin\Console\Commands\DirectAdminUpdatePermissions;
use Modules\DirectAdmin\Console\SyncDirectAdminCommand;
use Throwable;

class DirectAdminServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'DirectAdmin';
    protected string $moduleNameLower = 'directadmin';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/directadmin.php', 'directadmin');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'permissions.directadmin');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'directadmin');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'DirectAdmin');
        $this->registerTranslations();
        $this->mapRoutes();
        $this->registerCommands();
        $this->registerCronTasks();
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            SyncDirectAdminCommand::class,
            DirectAdminInstall::class,
            DirectAdminUpdatePermissions::class,
        ]);
    }

    /**
     * Auto-register or normalize cron task in central cron system.
     */
    protected function registerCronTasks(): void
    {
        try {
            if (Schema::hasTable('cron_tasks')) {
                // Auto-fix any misspelled or prefixed commands (e.g. 'artisan directadmin:sync')
                CronTask::where(function ($q) {
                    $q->where('command', 'like', '%directadmin:sync%')
                      ->orWhere('command', 'like', '%artisan%directadmin%')
                      ->orWhere(function ($sub) {
                          $sub->where('module', 'DirectAdmin')->where('command', 'like', 'artisan%');
                      });
                })->where('command', '!=', 'directadmin:sync')
                  ->update(['command' => 'directadmin:sync']);

                CronTask::firstOrCreate(
                    ['command' => 'directadmin:sync'],
                    [
                        'module' => 'DirectAdmin',
                        'name' => 'همگام‌سازی اکانت‌ها و سرورهای دایرکت‌ادمین',
                        'description' => 'همگام‌سازی خودکار و روزانه اکانت‌ها، مصرف فضا، پهنای باند و وضعیت اتصال سرورهای دایرکت‌ادمین',
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
        if (!$this->app->routesAreCached()) {
            Route::middleware(['web', 'auth'])
                ->prefix('user')
                ->name('user.')
                ->group(__DIR__ . '/../Routes/user.php');
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
