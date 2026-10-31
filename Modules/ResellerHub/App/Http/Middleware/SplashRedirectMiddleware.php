<?php

namespace Modules\ResellerHub\App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SplashRedirectMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->isMethod('GET')
            && !$request->expectsJson()
            && !$request->isXmlHttpRequest()
            && !$request->hasHeader('X-Livewire')
            && !$request->is('*livewire*')
        ) {
            $path = trim($request->path(), '/');

            $isExcludedPath = $request->is('*splash*')
                || $request->is('*login*')
                || $request->is('*logout*')
                || $request->is('*register*')
                || $request->is('*password*')
                || $request->is('*sanctum*')
                || $request->is('*get-started*')
                || $request->is('*loading*')
                || $request->is('*checkout*')
                || $request->is('*onboarding*')
                || str_starts_with($path, '_')
                || str_contains($path, '.');

            if (
                $request->is('*get-started*')
                || $request->is('*loading*')
                || $request->is('*checkout*')
                || $request->is('*onboarding*')
                || $request->is('*register*')
            ) {
                session()->forget('resellerhub_show_welcome_splash');
            }

            if (!$isExcludedPath && auth()->check()) {
                $user = auth()->user();
                $tenantManager = app(TenantManager::class);
                $currentTenant = $tenantManager->hasTenant()
                    ? $tenantManager->getTenant()
                    : ($user?->tenant_id ? Tenant::find($user->tenant_id) : null);

                if (session('resellerhub_show_registered_splash')) {
                    session()->forget('resellerhub_show_registered_splash');
                    session(['resellerhub_splash_redirect_url' => $request->fullUrl()]);

                    if ($currentTenant && $currentTenant->isActive()) {
                        return redirect()->away($tenantManager->getTenantUrl($currentTenant, 'resellerhub/splash/registered'));
                    }

                    return redirect()->route('splash.registered');
                }

                if (session('resellerhub_show_welcome_splash')) {
                    session()->forget('resellerhub_show_welcome_splash');
                    $isMobile = session('login_method') === 'mobile' || session('auth_via_mobile') || session('auth_login_method') === 'mobile';

                    if ($isMobile) {
                        session(['resellerhub_splash_intended_url' => $request->fullUrl()]);

                        if ($currentTenant && $currentTenant->isActive()) {
                            return redirect()->away($tenantManager->getTenantUrl($currentTenant, 'resellerhub/splash/welcome-back'));
                        }

                        return redirect()->route('splash.welcome-back');
                    }
                }
            }
        }

        return $next($request);
    }
}
