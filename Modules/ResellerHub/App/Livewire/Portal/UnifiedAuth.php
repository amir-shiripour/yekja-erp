<?php

namespace Modules\ResellerHub\App\Livewire\Portal;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Url;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Jobs\ProvisionTenantJob;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Services\SmsManager;
use Throwable;

class UnifiedAuth extends Component
{
    #[Url]
    public $package = null;

    #[Url]
    public $period = null;

    public $step = 'mobile'; // mobile, otp_login, name_otp_register
    public $mobile = '';
    public $name = '';
    public $otp = '';
    public $errorMessage = '';
    public $devOtpHint = '';
    public bool $isExistingUser = false;
    public string $existingUserName = '';
    public string $authNotice = '';

    public function mount($package = null, $period = null)
    {
        $this->package = $package ?: request()->query('package');
        $this->period = $period ?: request()->query('period');
    }

    protected $rules = [
        'mobile' => 'required|regex:/^09\d{9}$/',
    ];

    public function updatedMobile()
    {
        $this->errorMessage = '';
    }

    public function resetToMobile()
    {
        $this->step = 'mobile';
        $this->isExistingUser = false;
        $this->existingUserName = '';
        $this->authNotice = '';
        $this->errorMessage = '';
        $this->otp = '';
    }

    public function submitMobile(SmsManager $smsManager)
    {
        $this->validateOnly('mobile');
        $this->errorMessage = '';

        // Anti-Escalation Fencing: Check if user exists and has admin roles
        $user = User::where('mobile', $this->mobile)->first();

        if ($user) {
            $this->isExistingUser = true;
            $this->existingUserName = '';
            $this->step = 'otp_login';
            $this->authNotice = 'کد تایید ورود برای شما ارسال شد.';
        } else {
            $this->isExistingUser = false;
            $this->existingUserName = '';
            $this->step = 'name_otp_register';
            $this->authNotice = 'کد تایید پیامکی جهت ثبت‌نام و فعال‌سازی حساب کاربری برای شما ارسال شد.';
        }

        // Generate and Persist OTP
        $code = (string)random_int(10000, 99999);

        SmsOtp::create([
            'phone' => $this->mobile,
            'code' => $code,
            'context' => 'portal_auth',
            'expires_at' => now()->addMinutes(5),
        ]);

        if ($this->isLocalDev()) {
            $this->devOtpHint = $code;
            Log::info("Portal OTP for {$this->mobile}: {$code}");
        }

        // Attempt SMS Delivery via SmsManager
        try {
            $smsManager->sendText($this->mobile, "کد تایید ورود به سامانه: {$code}");
        } catch (Throwable $e) {
            if (!$this->isLocalDev()) {
                $this->errorMessage = 'خطا در ارسال پیامک: ' . $e->getMessage();
                return;
            }
            Log::warning("Local SMS Gateway offline: " . $e->getMessage());
        }

        $this->dispatch('otp-timer-reset', duration: 120);
    }

    public function resendOtp(SmsManager $smsManager)
    {
        $this->errorMessage = '';

        if (empty($this->mobile) || !in_array($this->step, ['otp_login', 'name_otp_register'], true)) {
            $this->errorMessage = 'درخواست نامعتبر است.';
            return;
        }

        // Anti-flood throttle: at least 60 seconds cooldown between resends
        $lastOtp = SmsOtp::where('phone', $this->mobile)
            ->where('context', 'portal_auth')
            ->latest('id')
            ->first();

        if ($lastOtp && $lastOtp->created_at && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            $remaining = 60 - (int)$lastOtp->created_at->diffInSeconds(now());
            $this->errorMessage = "لطفاً {$remaining} ثانیه دیگر جهت ارسال مجدد کد صبر کنید.";
            return;
        }

        $code = (string)random_int(10000, 99999);

        SmsOtp::create([
            'phone' => $this->mobile,
            'code' => $code,
            'context' => 'portal_auth',
            'expires_at' => now()->addMinutes(5),
        ]);

        if ($this->isLocalDev()) {
            $this->devOtpHint = $code;
            Log::info("Portal OTP Resent for {$this->mobile}: {$code}");
        }

        try {
            $smsManager->sendText($this->mobile, "کد تایید ورود به سامانه: {$code}");
        } catch (Throwable $e) {
            if (!$this->isLocalDev()) {
                $this->errorMessage = 'خطا در ارسال پیامک: ' . $e->getMessage();
                return;
            }
            Log::warning("Local SMS Gateway offline: " . $e->getMessage());
        }

        $this->dispatch('otp-timer-reset', duration: 120);
    }

    public function getFormattedMobile(): string
    {
        if (strlen($this->mobile) === 11) {
            $formatted = substr($this->mobile, 0, 4) . ' ' . substr($this->mobile, 4, 3) . ' ' . substr($this->mobile, 7);
            return ResellerHelper::toPersianDigits($formatted);
        }
        return ResellerHelper::toPersianDigits($this->mobile);
    }

    public function getMaskedMobile(): string
    {
        return $this->getFormattedMobile();
    }

    public function verifyOtp()
    {
        $this->errorMessage = '';
        $this->validate([
            'otp' => 'required|numeric|digits:5',
        ]);

        if ($this->step === 'name_otp_register') {
            $this->validate(['name' => 'required|string|min:3|max:100']);
        }

        // In local development, support test bypass codes (11111 or 12345)
        $isDevMasterCode = $this->isLocalDev() && in_array((string)$this->otp, ['11111', '12345'], true);

        // Verify with SmsOtp model
        $otpRecord = SmsOtp::query()
            ->where('phone', $this->mobile)
            ->where('code', $this->otp)
            ->where('context', 'portal_auth')
            ->latest()
            ->first();

        if (!$isDevMasterCode && (!$otpRecord || $otpRecord->isExpired() || $otpRecord->isUsed())) {
            $this->errorMessage = 'کد تایید نامعتبر است یا منقضی شده است.';
            return;
        }

        // Mark as used
        if ($otpRecord) {
            $otpRecord->update(['used_at' => now()]);
        }

        // Process User
        $user = User::where('mobile', $this->mobile)->first();

        if (!$user) {
            // Register new user
            $user = User::create([
                'name' => $this->name,
                'mobile' => $this->mobile,
                'email' => $this->mobile . '@crm.local', // Placeholder to satisfy DB constraints
                'password' => Hash::make(Str::random(16)), // Secure random password
            ]);
        }

        Auth::login($user);
        session()->regenerate();
        session([
            'login_method' => 'mobile',
            'auth_via_mobile' => true,
            'auth_login_method' => 'mobile',
        ]);

        if ($this->isExistingUser) {
            if (!empty($this->package)) {
                return redirect()->route('portal.checkout.index', array_filter([
                    'package' => $this->package,
                    'period' => $this->period,
                ]));
            }

            //Super-admin role
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return redirect()->route(Route::has('admin.dashboard') ? 'admin.dashboard' : 'user.dashboard');
            }

            //Reseller role
            if (method_exists($user, 'hasRole') && $user->hasRole('reseller')) {
                return redirect()->route('reseller.dashboard');
            }

            // User with direct tenant association
            if (!empty($user->tenant_id)) {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant && method_exists($tenant, 'isActive') && $tenant->isActive()) {
                    return redirect()->away(app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard'));
                }
            }

            // User associated with Reseller SubAccount
            $subAccount = ResellerSubAccount::where('user_id', $user->id)
                ->orWhere('mobile', $user->mobile)
                ->first();

            if ($subAccount && !empty($subAccount->tenant_id)) {
                $tenant = Tenant::find($subAccount->tenant_id);
                if ($tenant && method_exists($tenant, 'isActive') && $tenant->isActive()) {
                    return redirect()->away(app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard'));
                }
            }

            // If no package was explicitly requested in the URL, send straight to default user dashboard
            return redirect()->route('user.dashboard');
        }

        // --- NEW USER REGISTRATION FLOW ---
        // 1. If user explicitly selected a package from the pricing table:
        if (!empty($this->package)) {
            $targetPackage = is_numeric($this->package)
                ? ResellerPackage::where('is_active', true)->find($this->package)
                : ResellerPackage::where('is_active', true)->where('slug', $this->package)->first();

            if ($targetPackage) {
                return redirect()->route('portal.checkout.index', array_filter([
                    'package' => $targetPackage->id,
                    'period' => $this->period,
                ]));
            }
        }

        // Regular Registration (User did NOT come from pricing table / no package selected):
        // Check if reseller has configured and enabled a default registration package in settings:
        $reseller = Reseller::first();
        $targetPackage = null;

        if ($reseller) {
            $targetPackage = ResellerSetting::getDefaultRegistrationPackage($reseller->id);
        }

        try {
            $months = 12;
            if (!empty($this->period)) {
                $months = ResellerPackage::resolveMonthFromPeriodKey($this->period);
            }

            $subdomain = ResellerHelper::generateTenantSubdomain($reseller?->id, $user->mobile, $user->name);

            $tenant = DB::transaction(function () use ($user, $reseller, $targetPackage, $months, $subdomain) {
                $subAccount = ResellerSubAccount::firstOrCreate(
                    [
                        'reseller_id' => $reseller?->id,
                        'mobile' => $user->mobile,
                    ],
                    [
                        'name' => $user->name ?: $user->mobile,
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'company_name' => $user->name ?: $user->mobile,
                        'status' => ResellerSubAccount::STATUS_ACTIVE,
                        'limits' => $targetPackage?->limits ?? null,
                        'settings' => [
                            'source' => 'portal_direct_registration',
                            'source_label' => 'ثبت‌نام مستقیم و آنی از پرتال',
                            'package_id' => $targetPackage?->id,
                            'package_name' => $targetPackage?->name ?? 'بدون پکیج اولیه',
                            'billing_period' => $this->period ?: 'yearly_price',
                            'acquired_at' => now()->toIso8601String(),
                        ],
                    ]
                );

                $branding = [
                    'is_custom' => false,
                    'brand_title' => $reseller?->brand_title ?: ($user->name ?: $subdomain),
                    'brand_primary_color' => $reseller ? ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5') : '#4f46e5',
                    'brand_logo' => $reseller?->brand_logo,
                    'brand_favicon' => $reseller?->brand_favicon,
                    'brand_footer_text' => $reseller?->brand_footer_text,
                    'support_phone' => $reseller?->support_phone,
                    'support_email' => $reseller?->support_email,
                ];

                $hasDefaultPackage = ($targetPackage !== null);

                $t = Tenant::create([
                    'reseller_id' => $reseller?->id,
                    'package_id' => $targetPackage?->id,
                    'sub_account_id' => $subAccount->id,
                    'name' => $user->name ?: $user->mobile,
                    'subdomain' => $subdomain,
                    'custom_domain' => null,
                    'status' => Tenant::STATUS_ACTIVE,
                    'plan_expires_at' => $hasDefaultPackage ? now()->addMonths($months) : null,
                    'branding' => $branding,
                    'settings' => [
                        'source' => 'online_direct_registration',
                    ],
                ]);

                if (!$subAccount->tenant_id) {
                    $subAccount->update(['tenant_id' => $t->id]);
                }

                if (!$user->tenant_id) {
                    $user->update(['tenant_id' => $t->id]);
                }

                // If Spatie permissions are active, ensure user has admin role in tenant
                if (method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('admin');
                    } catch (Throwable) {
                    }
                }

                try {
                    app(OnboardingStateService::class)->markCompleted($t->id, 'complete_profile', [
                        'source' => 'online_registration',
                        'user_id' => $user->id,
                    ]);
                } catch (Throwable) {
                }

                // If a default package was configured and assigned, create grant invoice & order:
                if ($reseller && $targetPackage) {
                    $billingService = app(ResellerBillingService::class);
                    $isFree = $targetPackage->isFree();
                    $items = [
                        [
                            'item_type' => 'package',
                            'item_id' => 'package_' . $targetPackage->id,
                            'title' => "پکیج {$targetPackage->name}" . ($isFree ? ' (پلن رایگان)' : ' (پکیج پیش‌فرض ثبت‌نام)'),
                            'description' => "راه‌اندازی و تخصیص مستقیم سامانه به عنوان پکیج پیش‌فرض",
                            'duration_months' => $months,
                            'quantity' => 1,
                            'unit_price' => 0,
                            'discount_amount' => 0,
                            'tax_rate' => 0,
                        ]
                    ];

                    $invoice = $billingService->createInvoice($reseller, [
                        'tenant_id' => $t->id,
                        'package_id' => $targetPackage->id,
                        'type' => InvoiceType::OFFICIAL->value,
                        'status' => InvoiceStatus::PAID->value,
                        'paid_at' => now(),
                        'paid_amount' => 0,
                        'payment_method' => 'default_registration_grant',
                        'client_name' => $user->name ?: $user->mobile,
                        'client_mobile' => $user->mobile,
                        'client_email' => $user->email ?? '',
                        'meta' => [
                            'source' => 'portal_direct_registration',
                            'source_label' => 'ثبت‌نام مستقیم با پکیج پیش‌فرض',
                            'package_id' => $targetPackage->id,
                            'package_name' => $targetPackage->name,
                            'billing_period' => $this->period ?: 'yearly_price',
                            'subdomain' => $subdomain,
                        ],
                        'items' => $items,
                    ], $user);

                    $billingService->convertInvoiceToOrder($invoice);
                }

                return $t;
            }, 3);

            ProvisionTenantJob::dispatch($tenant->id);

            return redirect()->route('portal.onboarding.loading', ['tenant' => $tenant->id]);
        } catch (Throwable $e) {
            Log::error('[UnifiedAuth] Auto-provisioning failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->route('user.dashboard');
        }
    }

    public function isLocalDev(): bool
    {
        if (app()->environment('local', 'testing')) {
            return true;
        }

        return false;
    }

    public function render()
    {
        return view('resellerhub::livewire.portal.unified-auth');
    }
}
