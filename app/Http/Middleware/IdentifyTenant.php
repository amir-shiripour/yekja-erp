<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{

    public function handle(Request $request, Closure $next): Response
    {
        // اگر برنامه هنوز نصب نشده است یا مسیر مربوط به نصب است، نیازی به اعتبارسنجی مستأجر نیست
        if ($request->is('install') || $request->is('install/*') || !File::exists(storage_path('app/installed.flag'))) {
            return $next($request);
        }

        try {
            if (!Schema::hasTable('tenants')) {
                return $next($request);
            }
        } catch (\Throwable $e) {
            return $next($request);
        }

        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        $tenant = $tenantManager->getTenant();

        if (!$tenant) {
            try {
                $host = $request->getHost();
                $subdomain = $tenantManager->extractSubdomain($host);

                if (!empty($subdomain)) {
                    $tenant = Tenant::where('subdomain', $subdomain)->first();
                } else {
                    $cleanHost = preg_replace('/^www\./i', '', $host);
                    $tenant = Tenant::where('custom_domain', $host)
                        ->orWhere('custom_domain', $cleanHost)
                        ->orWhere('custom_domain', 'www.' . $cleanHost)
                        ->first();

                    if ($tenant && !empty($tenant->custom_domain) && (int)$tenant->reseller_id > 0) {
                        $tenant->loadMissing('reseller');
                        if ($tenant->reseller && method_exists($tenant->reseller, 'allowsCustomDomains') && !$tenant->reseller->allowsCustomDomains()) {
                            return response()->view('errors.tenant-status', [
                                'tenant' => $tenant,
                                'type' => 'domain_disabled',
                                'title' => 'اتصال دامنه اختصاصی غیرفعال است',
                                'message' => 'قابلیت اتصال دامنه اختصاصی برای این سامانه توسط نماینده غیرفعال گردیده است. لطفاً از طریق آدرس اختصاصی سابدامین وارد شوید.',
                            ], 403);
                        }
                    }
                }

                if (!empty($subdomain) && !$tenant) {
                    return response()->view('errors.tenant-not-found', [
                        'subdomain' => $subdomain,
                        'host' => $host,
                    ], 404);
                }
            } catch (\Throwable $e) {
                Log::warning('[IdentifyTenant] Failed to query tenants: ' . $e->getMessage());
                $tenant = null;
            }
        }

        if ($tenant) {
            if ($tenant->isPendingPayment()) {
                return response()->view('errors.tenant-status', [
                    'tenant' => $tenant,
                    'type' => 'pending',
                    'title' => 'در انتظار تکمیل پرداخت',
                    'message' => 'این سامانه هنوز فعال نشده است. برای ادامه، فرآیند خرید را کامل کنید.',
                ], 402);
            }

            if ($tenant->isSuspended()) {
                return response()->view('errors.tenant-status', [
                    'tenant' => $tenant,
                    'type' => 'suspended',
                    'title' => 'سرویس موقتاً مسدود شده است',
                    'message' => 'دسترسی به این پنل بنا به درخواست مدیر سیستم یا به دلایل اداری موقتاً مسدود گردیده است.',
                ], 403);
            }

            if ($tenant->isExpired()) {
                return response()->view('errors.tenant-status', [
                    'tenant' => $tenant,
                    'type' => 'expired',
                    'title' => 'اعتبار اشتراک به پایان رسیده است',
                    'message' => 'مدت زمان اشتراک این نرم‌افزار منقضی شده است. لطفاً جهت تمدید اشتراک با پشتیبانی تماس حاصل فرمایید.',
                ], 402);
            }

            $tenantManager->setTenant($tenant);
            app()->instance('currentTenant', $tenant);
            view()->share('currentTenant', $tenant);

            if (Auth::check()) {
                $user = Auth::user();

                $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

                if (!$isSuperAdmin && !empty($user->tenant_id) && (int)$user->tenant_id !== (int)$tenant->id) {
                    Auth::guard('web')->logout();

                    return redirect()->route('login')->withErrors([
                        'email' => 'حساب کاربری شما متعلق به این سازمان یا کلینیک نمی‌باشد.',
                    ]);
                }
            }
        } else {
            $tenantManager->setTenant(null);
            app()->instance('currentTenant', null);
            view()->share('currentTenant', null);

            if (Auth::check()) {
                $user = Auth::user();
                $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
                $isReseller = method_exists($user, 'hasRole') && $user->hasRole('reseller');

                if (!$isSuperAdmin && !$isReseller && !empty($user->tenant_id)) {
                    try {
                        $userTenant = Tenant::find($user->tenant_id);
                        if ($userTenant) {
                            if ($request->is('livewire/*') || $request->is('get-started/*') || $request->is('get-started') || $request->is('reseller/invoices/*') || $request->is('reseller/invoices') || $request->is('portal/*') || $request->is('portal')) {
                                $tenantManager->setTenant($userTenant);
                                app()->instance('currentTenant', $userTenant);
                                view()->share('currentTenant', $userTenant);
                            } else {
                                $targetUrl = $tenantManager->getTenantUrl($userTenant, 'user/dashboard');
                                return redirect()->away($targetUrl);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('[IdentifyTenant] Failed to load user tenant: ' . $e->getMessage());
                    }
                }
            }
        }

        return $next($request);
    }
}
