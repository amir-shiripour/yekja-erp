<?php

namespace Modules\ResellerHub\App\Jobs;

use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\ResellerHub\App\Events\TenantProvisioningProgress;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Spatie\Permission\Models\Role;
use Throwable;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public int $tenantId;

    public function __construct(int|Tenant $tenant)
    {
        $this->tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
    }

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) {
            return;
        }

        Log::info(json_encode([
            'checkout_id' => $tenant->subdomain,
            'invoice_id' => $tenant->invoices()->latest('id')->value('id'),
            'tenant_id' => $tenant->id,
            'stage' => 'provision_started',
        ], JSON_UNESCAPED_UNICODE));

        $reportProgress = function (int $progress, string $step, string $message, ?string $redirectUrl = null) use ($tenant) {
            Cache::put("tenant_provisioning_progress_{$tenant->id}", [
                'tenant_id' => $tenant->id,
                'progress' => $progress,
                'step' => $step,
                'message' => $message,
                'redirect_url' => $redirectUrl,
                'completed' => ($progress >= 100),
            ], now()->addMinutes(30));

            try {
                event(new TenantProvisioningProgress(
                    tenantId: $tenant->id,
                    step: $step,
                    progress: $progress,
                    message: $message,
                    redirectUrl: $redirectUrl
                ));
            } catch (Throwable $e) {
                // If broadcasting fails (e.g. reverb offline), cache and polling take care of progress seamlessly
            }
        };

        $reportProgress(15, 'domain', 'در حال تخصیص زیردامنه و فضای اختصاصی سامانه...');
        $reportProgress(40, 'database', 'پیکربندی ساختار پایگاه داده و تنظیمات امنیتی...');

        $user = User::where('tenant_id', $tenant->id)->first()
            ?? $tenant->subAccount?->user;

        if ($user) {
            if (!$user->tenant_id) {
                $user->update(['tenant_id' => $tenant->id]);
            }
            $role = Role::firstOrCreate(
                ['name' => 'admin', 'guard_name' => 'web'],
                ['display_name' => 'مدیر سیستم']
            );
            if (!$user->hasRole('admin')) {
                $user->assignRole($role);
            }

            // Ensure SubAccount is created and linked for reseller tracking
            if (!$tenant->sub_account_id && $tenant->reseller_id) {
                $subAccount = ResellerSubAccount::firstOrCreate(
                    [
                        'reseller_id' => $tenant->reseller_id,
                        'mobile' => $user->mobile,
                    ],
                    [
                        'name' => $user->name ?: $user->mobile,
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'tenant_id' => $tenant->id,
                        'company_name' => $user->name ?: $user->mobile,
                        'status' => ResellerSubAccount::STATUS_ACTIVE,
                        'limits' => $tenant->package?->limits ?? null,
                        'settings' => [
                            'source' => 'portal_purchase',
                            'source_label' => 'خرید آنلاین از سایت',
                            'package_id' => $tenant->package_id,
                            'package_name' => $tenant->package?->name,
                            'acquired_at' => now()->toIso8601String(),
                        ],
                    ]
                );
                if (!$subAccount->tenant_id) {
                    $subAccount->update(['tenant_id' => $tenant->id]);
                }
                $tenant->update(['sub_account_id' => $subAccount->id]);
            }
        }

        // Step 3: Activating Package Modules & Quotas (70%)
        $reportProgress(70, 'modules', 'فعال‌سازی ماژول‌ها، سهمیه‌ها و مجوزهای پکیج...');
        $package = $tenant->package;

        $isSettled = $this->isSettledForActivation($tenant);
        $isActive = $tenant->status === Tenant::STATUS_ACTIVE;
        $shouldActivate = $isSettled || $isActive;

        if ($package) {
            $modules = is_array($package->modules) ? $package->modules : [];
            foreach ($modules as $slug) {
                TenantModule::updateOrCreate([
                    'tenant_id' => $tenant->id,
                    'module_slug' => strtolower(trim($slug)),
                ], [
                    'is_active' => $shouldActivate,
                    'expires_at' => $tenant->plan_expires_at,
                ]);
            }

            $currentSettings = is_array($tenant->settings) ? $tenant->settings : [];
            $currentSettings['features'] = $package->features ?? [];
            $currentSettings['limits'] = $package->limits ?? [];
            $tenant->settings = $currentSettings;
            $tenant->save();
        }

        $reportProgress(90, 'workspace', 'در حال نهایی‌سازی تنظیمات و آماده‌سازی میزکار...');

        if ($tenant->status === Tenant::STATUS_SUSPENDED) {
            $reportProgress(70, 'suspended', 'این سامانه توسط مدیر تعلیق شده است.');
            return;   // I2: suspended represents admin decision; no automated job reactivates it
        }

        if (!$shouldActivate) {
            $reportProgress(70, 'payment', 'در انتظار تأیید پرداخت و فعال‌سازی اشتراک...');
            return;
        }

        $tenant->update(['status' => Tenant::STATUS_ACTIVE]);

        app(OnboardingStateService::class)->markCompleted(
            $tenant->id,
            'complete_profile',
            ['source' => 'online_registration', 'user_id' => $user?->id]
        );

        $tenantModel = $tenant->fresh();
        $tenantManager = app(TenantManager::class);
        $dashboardUrl = ($tenantModel && $tenantModel->isActive())
            ? $tenantManager->getTenantUrl($tenantModel, 'user/dashboard')
            : route('user.dashboard');

        $targetUrl = route('splash.registered', ['redirect_url' => $dashboardUrl]);
        $reportProgress(100, 'completed', 'سامانه شما با موفقیت آماده شد! در حال انتقال به صفحه تبریک و داشبورد...', $targetUrl);
    }

    /**
     * Determine whether the tenant has a settled payment or zero-value invoice for activation.
     */
    public function isSettledForActivation(Tenant $tenant): bool
    {
        $invoice = $tenant->invoices()
            ->whereNotIn('status', [InvoiceStatus::CANCELLED->value, InvoiceStatus::DRAFT->value])
            ->latest('id')
            ->first();

        if ($invoice) {
            if ((int)$invoice->total <= 0) {
                return $invoice->isPaid();
            }

            if ($invoice->isPaid() && !$invoice->validPayments()->exists()) {
                Log::warning('[ProvisionTenantJob] paid invoice without settled payment', [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenant->id,
                ]);
            }

            return $invoice->validPayments()->exists();
        }

        $order = $tenant->orders()->latest('id')->first();

        return !$order || (int)$order->total_amount <= 0;
    }

    /**
     * Handle a job failure gracefully and update cache to release frontend loader.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[ProvisionTenantJob] Provisioning failed for tenant {$this->tenantId}: " . $exception->getMessage(), [
            'tenant_id' => $this->tenantId,
            'exception' => $exception,
        ]);

        Cache::put("tenant_provisioning_progress_{$this->tenantId}", [
            'tenant_id' => $this->tenantId,
            'progress' => 0,
            'step' => 'error',
            'message' => 'خطایی در راه‌اندازی خودکار سامانه رخ داد. لطفاً با پشتیبانی تماس بگیرید.',
            'redirect_url' => null,
            'completed' => true,
            'error' => true,
        ], now()->addMinutes(30));
    }
}
