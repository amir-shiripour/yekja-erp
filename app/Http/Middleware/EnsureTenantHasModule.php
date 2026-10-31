<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\ResellerHub\App\Services\TenantQuotaService;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasModule
{

    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        if ($tenantManager->hasTenant()) {
            $tenant = $tenantManager->getTenant();

            if (!$tenant->hasModule($moduleSlug)) {
                $quotaService = app(TenantQuotaService::class);
                $payload = $quotaService->getPendingOrderPayload($tenant, $moduleSlug);

                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json([
                        'success' => false,
                        'quota_exceeded' => true,
                        'is_order_pending' => $payload['is_order_pending'] ?? true,
                        'message' => $payload['message'] ?? "دسترسی به ماژول {$moduleSlug} در اشتراک سازمان شما فعال نمی‌باشد.",
                        'payload' => $payload,
                    ], 403);
                }

                if (Route::has('user.dashboard')) {
                    return redirect()->route('user.dashboard')
                        ->with('reseller_quota_exceeded', $payload);
                }

                return response()->view('errors.module-not-subscribed', [
                    'moduleSlug' => $moduleSlug,
                    'tenant' => $tenant,
                    'payload' => $payload,
                ], 403);
            }
        }
        return $next($request);
    }
}
