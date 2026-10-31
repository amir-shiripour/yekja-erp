<?php

namespace Modules\ResellerHub\App\Providers;

use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Modules\ResellerHub\App\Enums\OnboardingJourney;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingRegistry;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Livewire\Livewire;
use Modules\ResellerHub\App\Livewire\Dashboard\OnboardingWidget;
use Throwable;

class OnboardingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OnboardingRegistry::class, function () {
            return new OnboardingRegistry();
        });

        $this->app->singleton(OnboardingStateService::class, function ($app) {
            return new OnboardingStateService($app->make(OnboardingRegistry::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('resellerhub.onboarding-widget', OnboardingWidget::class);

        $this->registerDefaultJourneys();
        $this->bindModelCompletionTriggers();
    }

    /**
     * Register standard system journeys and steps into the registry.
     */
    protected function registerDefaultJourneys(): void
    {
        /** @var OnboardingRegistry $registry */
        $registry = $this->app->make(OnboardingRegistry::class);

        $registry->registerJourney(OnboardingJourney::CRM_SETUP->toJourneyData());
    }

    /**
     * Dynamically hook Eloquent model created events for automated step completions.
     * ZERO code changes required inside external module controllers or models.
     */
    protected function bindModelCompletionTriggers(): void
    {
        /** @var OnboardingRegistry $registry */
        $registry = $this->app->make(OnboardingRegistry::class);
        $completionModels = $registry->getAllCompletionModels();

        foreach ($completionModels as $modelClass => $stepSlugs) {
            if (!class_exists($modelClass)) {
                continue;
            }

            $modelClass::created(function ($model) use ($stepSlugs, $modelClass) {
                try {
                    $tenantId = $model->tenant_id
                        ?? (app()->bound(TenantManager::class) ? app(TenantManager::class)->getTenantId() : null)
                        ?? auth()->user()?->tenant_id;

                    if ($tenantId) {
                        /** @var OnboardingStateService $stateService */
                        $stateService = app(OnboardingStateService::class);
                        foreach ($stepSlugs as $slug) {
                            $stateService->markCompleted((int)$tenantId, $slug, [
                                'auto_triggered_by' => get_class($model),
                                'model_id' => $model->getKey(),
                            ]);
                        }
                    }
                } catch (Throwable $e) {
                    Log::warning("Onboarding model trigger failed for {$modelClass}: " . $e->getMessage());
                }
            });
        }
    }
}
