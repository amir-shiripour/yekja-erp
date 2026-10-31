<?php

namespace Modules\ResellerHub\App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingRegistry;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Symfony\Component\HttpFoundation\Response;

class EnforceOnboardingDependency
{
    public function __construct(
        protected OnboardingRegistry     $registry,
        protected OnboardingStateService $stateService
    )
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $requiredStepSlug): Response
    {
        $tenantId = null;
        if (app()->bound(TenantManager::class)) {
            $tenantId = app(TenantManager::class)->getTenantId();
        }
        if (!$tenantId && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
        }

        // If not in a tenant context, proceed normally
        if (!$tenantId) {
            return $next($request);
        }

        $tenant = app()->bound(TenantManager::class) ? app(TenantManager::class)->getTenant() : Tenant::find($tenantId);
        if ($tenant && $tenant->reseller_id) {
            $tourConfig = ResellerSetting::getOnboardingTourConfig($tenant->reseller_id);

            // If tour is disabled, do not block
            if (empty($tourConfig['enabled'])) {
                return $next($request);
            }

            // If enforcement policy is soft, do not block
            if (($tourConfig['enforcement'] ?? 'soft') === 'soft') {
                return $next($request);
            }

            // If grace period, check if still within grace period
            if (($tourConfig['enforcement'] ?? 'soft') === 'grace_period') {
                $graceDays = (int)($tourConfig['grace_period_days'] ?? 3);
                if ($tenant->created_at && $tenant->created_at->clone()->addDays($graceDays)->isFuture()) {
                    return $next($request);
                }
            }
        }

        // Check if required step is completed or skipped (sub-millisecond cache lookup)
        if (!$this->stateService->isStepCompletedOrSkipped((int)$tenantId, $requiredStepSlug)) {
            $step = $this->registry->getStep($requiredStepSlug);
            $targetRoute = $step?->routeAction;

            if ($targetRoute && Route::has($targetRoute)) {
                $targetUrl = route($targetRoute);
                // Prevent redirect loop if already on target route
                if ($request->url() !== $targetUrl) {
                    return redirect()->route($targetRoute)->with(
                        'warning',
                        __('resellerhub::messages.onboarding.dependency_warning', ['step' => $step->title])
                    );
                }
            }
        }

        return $next($request);
    }
}
