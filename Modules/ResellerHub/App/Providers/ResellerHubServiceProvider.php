<?php

namespace Modules\ResellerHub\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Middleware\EnforceOnboardingDependency;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerOrder;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Illuminate\Console\Scheduling\Schedule;
use Modules\ResellerHub\App\Console\Commands\AuditDuplicateInvoicesCommand;
use Modules\ResellerHub\App\Console\Commands\BackfillLegacyPaymentsCommand;
use Modules\ResellerHub\App\Console\Commands\ExpirePendingCheckoutsCommand;
use Modules\ResellerHub\App\Console\Commands\ScanExpiringOrdersCommand;
use Modules\ResellerHub\App\Console\Commands\ScanOverdueInvoicesCommand;
use Modules\ResellerHub\App\Console\Commands\SeedResellerWorkflows;
use Modules\ResellerHub\App\Observers\ResellerOrderObserver;
use Modules\ResellerHub\App\Policies\ResellerInvoicePolicy;
use Modules\ResellerHub\App\Policies\ResellerOrderPolicy;
use Modules\ResellerHub\App\Listeners\TriggerResellerWorkflowListener;
use Livewire\Livewire;
use Modules\ResellerHub\App\Livewire\Portal\Checkout;
use Modules\ResellerHub\App\Livewire\Portal\TenantProvisioningLoader;
use Modules\ResellerHub\App\Livewire\Portal\UnifiedAuth;
use Modules\ResellerHub\App\Services\Adapters\WalletAdapter;
use Modules\ResellerHub\App\Services\Adapters\WalletAdapterInterface;
use Modules\ResellerHub\App\Services\Support\ModuleTitleResolver;
use Modules\ResellerHub\App\Services\Support\MoneyFormatter;
use Modules\ResellerHub\App\Services\Tenant\TenantStatusSyncService;
use Nwidart\Modules\Facades\Module;
use Throwable;

class ResellerHubServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ResellerHub';

    protected string $moduleNameLower = 'resellerhub';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        ResellerOrder::observe(ResellerOrderObserver::class);
        Event::subscribe(TriggerResellerWorkflowListener::class);


        $this->app['router']->aliasMiddleware(
            'onboarding.required',
            EnforceOnboardingDependency::class
        );

        Gate::policy(ResellerInvoice::class, ResellerInvoicePolicy::class);
        Gate::policy(ResellerOrder::class, ResellerOrderPolicy::class);

        View::composer('resellerhub::*', function ($view) {
            $view->with([
                'faNum' => fn($str) => ResellerHelper::toPersianDigits($str),
                'toJalali' => fn($date) => ResellerHelper::toJalali($date),
                'formatJalali' => fn($date, $format = 'Y/m/d', $toPersian = true) => ResellerHelper::formatJalali($date, $format, $toPersian),
                'formatPrice' => fn($amount, $curr = null, $toPersian = true) => ResellerHelper::formatPrice($amount, $curr, $toPersian),
            ]);

            if (class_exists(Module::class) && !Module::isEnabled('ResellerHub')) {
                $view->with([
                    'currency' => 'toman',
                    'currencyLabel' => 'تومان',
                ]);
                return;
            }

            $viewData = $view->getData();
            if (!isset($viewData['currency']) || !isset($viewData['currencyLabel'])) {
                try {
                    $resellerId = null;
                    if (isset($viewData['reseller']) && is_object($viewData['reseller']) && isset($viewData['reseller']->id)) {
                        $resellerId = (int)$viewData['reseller']->id;
                    } elseif (isset($viewData['tenant']) && is_object($viewData['tenant']) && isset($viewData['tenant']->reseller_id)) {
                        $resellerId = (int)$viewData['tenant']->reseller_id;
                    } elseif (isset($viewData['order']) && is_object($viewData['order']) && isset($viewData['order']->reseller_id)) {
                        $resellerId = (int)$viewData['order']->reseller_id;
                    } elseif (isset($viewData['package']) && is_object($viewData['package']) && isset($viewData['package']->reseller_id)) {
                        $resellerId = (int)$viewData['package']->reseller_id;
                    } elseif (isset($viewData['invoice']) && is_object($viewData['invoice']) && isset($viewData['invoice']->reseller_id)) {
                        $resellerId = (int)$viewData['invoice']->reseller_id;
                    } elseif (auth()->check()) {
                        $resellerId = auth()->user()?->reseller?->id ?? null;
                    }

                    $currency = $viewData['currency'] ?? ResellerSetting::getCurrencyForReseller($resellerId);
                    $currencyLabel = $viewData['currencyLabel'] ?? ResellerHelper::currencyLabel($currency);
                } catch (Throwable $e) {
                    // Safe zero-crash fallback for unmigrated fresh installs or transient DB errors
                    $currency = 'toman';
                    $currencyLabel = 'تومان';
                }

                $view->with([
                    'currency' => $currency,
                    'currencyLabel' => $currencyLabel,
                ]);
            }
        });

        Blade::directive('fa', function ($expression) {
            return "<?php echo \Modules\ResellerHub\App\Helpers\ResellerHelper::toPersianDigits((string)({$expression})); ?>";
        });

        if (class_exists(Livewire::class)) {
            Livewire::component('modules.reseller-hub.app.livewire.portal.checkout', Checkout::class);
            Livewire::component('resellerhub.portal.checkout', Checkout::class);
            Livewire::component('modules.reseller-hub.app.livewire.portal.tenant-provisioning-loader', TenantProvisioningLoader::class);
            Livewire::component('resellerhub.portal.tenant-provisioning-loader', TenantProvisioningLoader::class);
            Livewire::component('portal.unified-auth', UnifiedAuth::class);
            Livewire::component('resellerhub.portal.unified-auth', UnifiedAuth::class);
            Livewire::component('modules.reseller-hub.app.livewire.portal.unified-auth', UnifiedAuth::class);
        }

        $this->app->terminating(static function (): void {
            ResellerSetting::clearRuntimeCache();
        });
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerTranslations();

        $helpersPath = __DIR__ . '/../Helpers/helpers.php';
        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ResellerQuotaServiceProvider::class);
        $this->app->register(OnboardingServiceProvider::class);
        $this->app->singleton(WalletAdapterInterface::class, WalletAdapter::class);
        $this->app->singleton(ModuleTitleResolver::class);
        $this->app->singleton(MoneyFormatter::class);
        $this->app->singleton(TenantStatusSyncService::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            SeedResellerWorkflows::class,
            ScanOverdueInvoicesCommand::class,
            ScanExpiringOrdersCommand::class,
            BackfillLegacyPaymentsCommand::class,
            AuditDuplicateInvoicesCommand::class,
            ExpirePendingCheckoutsCommand::class,
            \Modules\ResellerHub\App\Console\Commands\ReconcileInvoicesCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('reseller:scan-overdue')->dailyAt('08:00');
            $schedule->command('reseller:scan-expiry')->dailyAt('08:30');
            $schedule->command('resellerhub:expire-pending-checkouts')
                ->everyTenMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

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
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace') . '\\' . $this->moduleName . '\\' . ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder', '')));
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
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}
