<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Throwable;

class TenantSsoController extends Controller
{
    /**
     * SSO token lifetime in seconds, from config.
     *
     * The cache entry expires on its own, but we also verify the embedded
     * `created_at` so the window holds even when the configured cache store
     * ignores TTLs.
     */
    protected function ssoTtl(): int
    {
        return max(30, (int)config('resellerhub.sso.token_ttl_seconds', 120));
    }

    /**
     * Handle one-time SSO login to a tenant CRM as its admin.
     */
    public function handle(Request $request): RedirectResponse
    {
        $token = (string)$request->query('token');

        if (empty($token)) {
            return redirect()->route('login')->withErrors([
                'email' => __('resellerhub::messages.sso.missing_token'),
            ]);
        }

        // Pull the SSO payload (one-time usage only)
        $ssoData = Cache::pull('tenant_sso_' . $token);

        if (!$ssoData || empty($ssoData['tenant_id']) || empty($ssoData['user_id'])) {
            return redirect()->route('login')->withErrors([
                'email' => __('resellerhub::messages.sso.expired_token'),
            ]);
        }

        // Defence in depth: reject a token that is older than the configured TTL even
        // if the cache store handed it back anyway.
        $issuedAt = (int)($ssoData['created_at'] ?? 0);
        if ($issuedAt <= 0 || (now()->timestamp - $issuedAt) > $this->ssoTtl()) {
            return redirect()->route('login')->withErrors([
                'email' => __('resellerhub::messages.sso.expired_token'),
            ]);
        }

        $tenantId = (int)$ssoData['tenant_id'];
        $userId = (int)$ssoData['user_id'];
        $impersonatorId = $ssoData['impersonator_id'] ?? null;

        // Security: Validate IP address binding if present in SSO payload
        if (!empty($ssoData['ip_address']) && $ssoData['ip_address'] !== $request->ip()) {
            $isLocal = in_array($request->ip(), ['127.0.0.1', '::1'], true) && in_array($ssoData['ip_address'], ['127.0.0.1', '::1'], true);
            if (!$isLocal) {
                return redirect()->route('login')->withErrors([
                    'email' => __('resellerhub::messages.sso.ip_mismatch'),
                ]);
            }
        }

        if (!$this->userAgentMatches($ssoData, $request)) {
            return redirect()->route('login')->withErrors([
                'email' => __('resellerhub::messages.sso.invalid_context'),
            ]);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant || !$tenant->isActive()) {
            return redirect()->route('login')->withErrors([
                'email' => __('resellerhub::messages.sso.tenant_not_found'),
            ]);
        }

        $user = User::where('tenant_id', $tenantId)->find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => __('resellerhub::messages.sso.admin_not_found'),
            ]);
        }

        // Ensure current tenant context is bound
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenantManager->setTenant($tenant);
        app()->instance('currentTenant', $tenant);
        view()->share('currentTenant', $tenant);

        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        if ($impersonatorId) {
            session([
                'impersonated_by' => $impersonatorId,
                'impersonated_tenant_id' => $tenantId,
                'impersonated_at' => now()->timestamp,
            ]);
            try {
                app(ResellerAuditService::class)->logImpersonationStart($tenant, (int)$impersonatorId, $request->ip(), $request->userAgent());
            } catch (Throwable $e) {
                Log::warning('[TenantSso] Failed to record audit: ' . $e->getMessage());
            }
        }

        $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
        return redirect()->away($targetUrl)->with('success', __('resellerhub::messages.sso.login_success', ['name' => $tenant->name]));
    }

    /**
     * Exit impersonation and return back to the reseller / super-admin dashboard.
     */
    public function exitImpersonation(Request $request): RedirectResponse
    {
        $impersonatorId = session('impersonated_by');
        $tenantId = session('impersonated_tenant_id');
        if ($impersonatorId && $tenantId) {
            $t = Tenant::find($tenantId);
            if ($t) {
                try {
                    app(ResellerAuditService::class)->logImpersonationEnd($t, (int)$impersonatorId);
                } catch (Throwable $e) {
                    Log::warning('[TenantSso] Failed to record exit audit: ' . $e->getMessage());
                }
            }
        }

        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenantManager->setTenant(null);

        $protocol = $request->isSecure() ? 'https://' : 'http://';

        $isTenantHost = false;
        $subdomain = $tenantManager->extractSubdomain($request->getHost());
        if ($subdomain) {
            $isTenantHost = true;
        } else {
            $tenantWithDomain = Tenant::where('custom_domain', $request->getHost())->first();
            if ($tenantWithDomain) {
                $isTenantHost = true;
            }
        }

        if (!$isTenantHost) {
            $centralBase = $request->getSchemeAndHttpHost();
        } else {
            $centralDomains = $tenantManager->getCentralDomains();
            $targetCentral = $centralDomains[0] ?? $request->getHost();
            $centralBase = $protocol . $targetCentral;
        }

        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($impersonatorId) {
            $returnToken = Str::random(64);
            Cache::put('sso_return_' . $returnToken, [
                'user_id' => $impersonatorId,
                'ip_address' => $request->ip(),
                'user_agent_hash' => md5((string)$request->userAgent()),
                'created_at' => now()->timestamp,
            ], now()->addSeconds($this->ssoTtl()));
            return redirect()->away($centralBase . '/reseller/sso-return?token=' . $returnToken);
        }

        return redirect()->away($centralBase . '/login');
    }

    public function handleReturn(Request $request): RedirectResponse
    {
        $token = (string)$request->query('token');
        if (empty($token)) {
            return redirect()->route('login')->withErrors(['email' => __('resellerhub::messages.sso.missing_token')]);
        }

        $returnData = Cache::pull('sso_return_' . $token);
        if (!$returnData) {
            return redirect()->route('login')->withErrors(['email' => __('resellerhub::messages.sso.expired_token')]);
        }

        $userId = is_array($returnData) ? ($returnData['user_id'] ?? null) : $returnData;
        if (is_array($returnData) && !empty($returnData['ip_address']) && $returnData['ip_address'] !== $request->ip()) {
            $isLocal = in_array($request->ip(), ['127.0.0.1', '::1'], true) && in_array($returnData['ip_address'], ['127.0.0.1', '::1'], true);
            if (!$isLocal) {
                return redirect()->route('login')->withErrors(['email' => __('resellerhub::messages.sso.ip_mismatch')]);
            }
        }

        if (!is_array($returnData) || !$this->userAgentMatches($returnData, $request)) {
            return redirect()->route('login')->withErrors(['email' => __('resellerhub::messages.sso.invalid_context')]);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        Auth::guard('web')->login($user, true);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $isPrivileged = method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin'));
        $targetPath = ($isPrivileged && Route::has('reseller.dashboard')) ? '/reseller/dashboard' : '/user/dashboard';

        return redirect($targetPath)->with('success', __('resellerhub::messages.sso.return_success'));
    }

    /**
     * Bind an SSO token to the browser that requested it. Fails closed: a
     * payload without a user-agent hash is rejected.
     */
    protected function userAgentMatches(array $payload, Request $request): bool
    {
        $expected = $payload['user_agent_hash'] ?? null;

        if (!is_string($expected) || $expected === '') {
            return false;
        }

        return hash_equals($expected, md5((string)$request->userAgent()));
    }
}
