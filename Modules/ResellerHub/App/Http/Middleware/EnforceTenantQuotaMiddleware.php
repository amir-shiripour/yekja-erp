<?php

namespace Modules\ResellerHub\App\Http\Middleware;

use App\Models\Module;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Services\TenantQuotaService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnforceTenantQuotaMiddleware
{
    /** Request-scoped memo for the guarded route table. */
    protected ?array $guardedRoutes = null;

    /**
     * Modern 2026 Constructor Dependency Injection
     */
    public function __construct(
        protected TenantManager      $tenantManager,
        protected TenantQuotaService $quotaService,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->tenantManager->hasTenant() || $this->tenantManager->isBypassed()) {
            return $next($request);
        }

        $tenant = $this->tenantManager->getTenant();
        if (!$tenant) {
            return $next($request);
        }

        $targetModule = $this->resolveTargetModule($request);
        if ($targetModule && !$tenant->hasModule($targetModule)) {
            $payload = $this->quotaService->getPendingOrderPayload($tenant, $targetModule);

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => false,
                    'quota_exceeded' => true,
                    'is_order_pending' => true,
                    'message' => $payload['message'],
                    'payload' => $payload,
                ], 403);
            }

            return redirect()->route('user.dashboard')
                ->with('reseller_quota_exceeded', $payload);
        }

        $quotaKey = $this->resolveQuotaKey($request);
        if (!$quotaKey) {
            return $next($request);
        }

        if ($this->quotaService->hasRemainingQuota($tenant, $quotaKey->value)) {
            return $next($request);
        }

        // Quota is exceeded! Prepare detailed payload
        $payload = $this->quotaService->getExceededQuotaPayload($tenant, $quotaKey->value);

        // 1. AJAX / API Requests
        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => false,
                'quota_exceeded' => true,
                'message' => $payload['message'],
                'quota' => $payload,
            ], 422);
        }

        // 2. Standard Web Requests: Redirect with session flash payload
        $targetUrl = $this->determineRedirectUrl($request, $quotaKey->fallbackRouteName());

        return redirect()->to($targetUrl)->with('reseller_quota_exceeded', $payload);
    }

    /**
     * Resolve the targeted QuotaKey enum from the current request route or path.
     */
    protected function resolveQuotaKey(Request $request): ?QuotaKey
    {
        $routeName = $request->route()?->getName();
        if ($routeName) {
            $quotaKey = QuotaKey::fromRoute($routeName);
            if ($quotaKey) {
                return $quotaKey;
            }
        }

        // Fallback for un-named routes matching dynamic guarded paths
        $currentPath = '/' . trim($request->path(), '/');
        foreach ($this->guardedRoutes() as $guarded) {
            if ($currentPath === $guarded['path']) {
                return QuotaKey::tryFrom($guarded['key']);
            }
        }

        return null;
    }

    /**
     * Guarded route table, resolved at most once per request.
     */
    protected function guardedRoutes(): array
    {
        return $this->guardedRoutes ??= $this->quotaService->getGuardedRoutesForFrontend();
    }


    protected function moduleSlugExists(string $slug): bool
    {
        $slugs = Cache::remember('resellerhub:active_module_slugs', 300, static function (): array {
            return Module::query()
                ->pluck('slug')
                ->map(static fn($s) => strtolower((string)$s))
                ->all();
        });

        return in_array(strtolower($slug), $slugs, true);
    }

    protected function determineRedirectUrl(Request $request, ?string $fallbackRoute): string
    {
        $previousUrl = url()->previous();
        $currentUrl = $request->fullUrl();

        // Avoid infinite loop if previous URL is the same as current creation page
        if ($previousUrl && $previousUrl !== $currentUrl && !str_contains($previousUrl, '/create')) {
            return $previousUrl;
        }

        if ($fallbackRoute && Route::has($fallbackRoute)) {
            try {
                return route($fallbackRoute);
            } catch (Throwable $e) {
            }
        }

        return url('/user/dashboard');
    }

    /**
     * Resolve the target module slug from the current request if it belongs to a module.
     */
    protected function resolveTargetModule(Request $request): ?string
    {
        $action = $request->route()?->getActionName();
        if ($action && preg_match('/^Modules\\\\([A-Za-z0-9]+)\\\\/i', $action, $m)) {
            $slug = strtolower($m[1]);
            if (!in_array($slug, ['core', 'settings', 'usermanagement', 'resellerhub', 'admin', 'auth'], true)) {
                return $slug;
            }
        }

        $routeName = $request->route()?->getName();
        if ($routeName) {
            $parts = explode('.', $routeName);
            $first = strtolower($parts[0] ?? '');
            $second = strtolower($parts[1] ?? '');
            $candidate = ($first === 'user') ? $second : $first;
            if ($candidate && !in_array($candidate, ['core', 'settings', 'usermanagement', 'resellerhub', 'admin', 'auth', 'dashboard', 'profile', 'notifications', 'two-factor'], true)) {
                if ($this->moduleSlugExists($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
