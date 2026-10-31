<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantPath
{
    /**
     * Handle an incoming request before routing.
     * Rewrites baseUrl and pathInfo if the first URL segment matches a registered tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        $rawPath = trim($request->getPathInfo(), '/');
        if (empty($rawPath)) {
            return $next($request);
        }

        $segments = explode('/', $rawPath);
        $firstSegment = strtolower(trim($segments[0]));

        if (empty($firstSegment) || $tenantManager->isReservedSubdomain($firstSegment)) {
            if ($firstSegment === 'livewire') {
                $referer = $request->header('referer');
                if ($referer) {
                    $refererPath = parse_url($referer, PHP_URL_PATH);
                    $refererSlug = $tenantManager->extractTenantFromPath($refererPath ?? '');
                    if ($refererSlug) {
                        $refererTenant = Tenant::where('subdomain', $refererSlug)->first();
                        if ($refererTenant) {
                            $tenantManager->setTenant($refererTenant);
                            app()->instance('currentTenant', $refererTenant);
                            view()->share('currentTenant', $refererTenant);
                            $this->configureTenantSession($refererTenant);
                        }
                    }
                }
            }
            return $next($request);
        }

        $tenant = Tenant::where('subdomain', $firstSegment)->first();
        if (!$tenant) {
            return $next($request);
        }

        $tenantManager->setTenant($tenant);
        app()->instance('currentTenant', $tenant);
        view()->share('currentTenant', $tenant);
        $this->configureTenantSession($tenant);

        $remainingSegments = array_slice($segments, 1);
        $nextSegment = strtolower(trim($remainingSegments[0] ?? ''));

        if ($nextSegment === 'admin' || in_array($nextSegment, ['get-started', 'portal'], true)) {
            $centralPath = '/' . implode('/', $remainingSegments);
            $query = $request->getQueryString();
            $statusCode = ($request->isMethod('GET') || $request->isMethod('HEAD')) ? 302 : 307;
            return redirect()->away($request->getSchemeAndHttpHost() . $centralPath . ($query ? ('?' . $query) : ''), $statusCode);
        }
        if ($nextSegment === 'reseller' && !in_array($remainingSegments[1] ?? '', ['sso-login', 'exit-impersonation'], true)) {
            $centralPath = '/' . implode('/', $remainingSegments);
            $query = $request->getQueryString();
            return redirect()->away($request->getSchemeAndHttpHost() . $centralPath . ($query ? ('?' . $query) : ''));
        }

        $remainingPath = '/' . implode('/', $remainingSegments);

        if ($remainingPath === '/' || empty($remainingSegments)) {
            $remainingPath = '/login';
        }

        $ref = new ReflectionClass($request);

        $baseUrlProp = $ref->getProperty('baseUrl');
        $baseUrlProp->setAccessible(true);
        $baseUrlProp->setValue($request, '/' . $firstSegment);

        $pathInfoProp = $ref->getProperty('pathInfo');
        $pathInfoProp->setAccessible(true);
        $pathInfoProp->setValue($request, $remainingPath);

        $urlRef = new ReflectionClass(app('url'));
        if ($urlRef->hasProperty('assetRoot')) {
            $assetRootProp = $urlRef->getProperty('assetRoot');
            $assetRootProp->setAccessible(true);
            $assetRootProp->setValue(app('url'), $request->getSchemeAndHttpHost());
        }

        return $next($request);
    }

    /**
     * Scope the session cookie name to the tenant to isolate sessions across browser tabs.
     */
    protected function configureTenantSession(Tenant $tenant): void
    {
        $tenantCookie = 'crm_sess_' . substr(md5('tenant_' . $tenant->id), 0, 10);
        config(['session.cookie' => $tenantCookie]);
    }
}
