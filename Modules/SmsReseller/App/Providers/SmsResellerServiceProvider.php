<?php

namespace Modules\SmsReseller\App\Providers;

use App\Models\CronTask;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\SmsReseller\App\Console\Commands\SmsResellerInstall;
use Modules\SmsReseller\App\Console\Commands\SmsResellerUpdatePermissions;
use Modules\SmsReseller\App\Console\SyncSmsResellerDataCommand;
use Throwable;

class SmsResellerServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'SmsReseller';

    protected string $moduleNameLower = 'smsreseller';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerCronTasks();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            SyncSmsResellerDataCommand::class,
            SmsResellerInstall::class,
            SmsResellerUpdatePermissions::class,
        ]);
    }

    /**
     * Register command Schedules.
     * Note: Scheduled centrally and dynamically via CronService.
     */
    protected function registerCommandSchedules(): void
    {
        // Managed via central CronService
    }

    /**
     * Auto-register or normalize cron task in central cron system.
     */
    protected function registerCronTasks(): void
    {
        try {
            if (Schema::hasTable('cron_tasks')) {
                // Auto-fix any misspelled or prefixed commands (e.g. 'artisan smsreseller:sync')
                CronTask::where(function ($q) {
                    $q->where('command', 'like', '%smsreseller:sync%')
                      ->orWhere('command', 'like', '%artisan%smsreseller%')
                      ->orWhere(function ($sub) {
                          $sub->where('module', 'SmsReseller')->where('command', 'like', 'artisan%');
                      });
                })->where('command', '!=', 'smsreseller:sync')
                  ->update(['command' => 'smsreseller:sync']);

                CronTask::firstOrCreate(
                    ['command' => 'smsreseller:sync'],
                    [
                        'module' => 'SmsReseller',
                        'name' => 'همگام‌سازی تراکنش‌ها و سود ریسیلر پیامک',
                        'description' => 'همگام‌سازی خودکار و روزانه اطلاعات تراکنش‌ها، پرداخت‌ها، اعتبارات و خلاصه‌های مالی ریسیلر پیامک',
                        'expression' => 'everyFiveMinutes',
                        'prevent_overlap' => true,
                        'is_active' => true,
                        'is_system' => true,
                        'next_run_at' => now()->addMinutes(5),
                    ]
                );
            }
        } catch (Throwable $e) {
            // Fail silently
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder','')));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
