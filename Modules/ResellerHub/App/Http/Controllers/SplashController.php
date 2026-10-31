<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Tenancy\TenantManager;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Throwable;

class SplashController extends Controller
{

    public function registered(Request $request)
    {
        $redirectUrl = $this->safeRedirectUrl($request->query('redirect_url'))
            ?? $this->safeRedirectUrl(session('resellerhub_splash_redirect_url'))
            ?? '/user/dashboard';

        $user = Auth::user();
        if ($user && $user->tenant_id && $redirectUrl === '/user/dashboard') {
            try {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant && $tenant->isActive()) {
                    $redirectUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
                }
            } catch (Throwable) {
            }
        }

        $userName = $user?->name ?? '';
        $brandTitle = config('app.name', __('resellerhub::messages.common.crm_system'));
        $brandLogo = null;

        try {
            if (function_exists('currentReseller')) {
                $reseller = currentReseller();
                if ($reseller) {
                    $brandTitle = $reseller->brand_title ?: $brandTitle;
                    $brandLogo = $reseller->brand_logo ?: null;
                }
            }
        } catch (Throwable) {
        }

        return view('resellerhub::portal.splash.registered',
            compact('redirectUrl', 'userName', 'brandTitle', 'brandLogo')
        );
    }


    public function welcomeBack(Request $request)
    {
        $user = Auth::user();
        $userName = $user?->name ?? '';
        $userMobile = $user?->mobile ? ResellerHelper::toPersianDigits(preg_replace('/(\d{4})(\d{3})(\d{4})/', '$1 $2 $3', $user->mobile)) : null;

        $redirectUrl = $this->safeRedirectUrl(session('resellerhub_splash_intended_url'));

        if ($redirectUrl && (str_contains($redirectUrl, 'loading') || str_contains($redirectUrl, 'splash') || str_contains($redirectUrl, 'get-started') || str_contains($redirectUrl, 'checkout'))) {
            $redirectUrl = null;
        }

        if (!$redirectUrl) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                $redirectUrl = url('/admin/dashboard');
            } elseif ($user && method_exists($user, 'hasRole') && $user->hasRole('reseller')) {
                $redirectUrl = url('/reseller/dashboard');
            } elseif ($user && $user->tenant_id) {
                try {
                    $tenant = Tenant::find($user->tenant_id);
                    if ($tenant && $tenant->isActive()) {
                        $redirectUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
                    }
                } catch (Throwable) {
                }
            }
        }

        $redirectUrl = $redirectUrl ?: url('/user/dashboard');

        // صفحه خوش برگشتید منحصراً برای کاربرانی نمایش داده می‌شود که با شماره موبایل وارد شده‌اند
        $isMobile = session('login_method') === 'mobile' || session('auth_via_mobile') || session('auth_login_method') === 'mobile';
        if (!$isMobile) {
            return redirect()->to($redirectUrl);
        }

        $brandTitle = config('app.name', __('resellerhub::messages.common.crm_system'));
        $brandLogo = null;

        try {
            if (function_exists('currentReseller')) {
                $reseller = currentReseller();
                if ($reseller) {
                    $brandTitle = $reseller->brand_title ?: $brandTitle;
                    $brandLogo = $reseller->brand_logo ?: null;
                }
            }
        } catch (Throwable) {
        }

        return view('resellerhub::portal.splash.welcome-back',
            compact('redirectUrl', 'userName', 'userMobile', 'brandTitle', 'brandLogo')
        );
    }

    /**
     * Allow only relative paths or absolute URLs on the application's own
     * hosts. Blocks javascript:, protocol-relative and foreign redirects.
     */
    protected function safeRedirectUrl(mixed $candidate): ?string
    {
        if (!is_string($candidate)) {
            return null;
        }

        $url = trim($candidate);

        if ($url === '' || str_starts_with($url, '//') || str_starts_with($url, '/\\')) {
            return null;
        }

        // Relative path: must stay inside the application.
        if (str_starts_with($url, '/')) {
            return $url;
        }

        $parts = parse_url($url);

        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host']);
        $requestHost = strtolower(request()->getHost());

        if ($host === $requestHost) {
            return $url;
        }

        $baseHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($baseHost !== '' && ($host === $baseHost || str_ends_with($host, '.' . $baseHost))) {
            return $url;
        }

        return null;
    }
}
