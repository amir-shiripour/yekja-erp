<?php

namespace Modules\ResellerHub\App\Livewire\Dashboard;

use App\Models\CustomUserField;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserCustomValue;
use App\Tenancy\TenantManager;
use Carbon\Carbon;
use Illuminate\Support\ViewErrorBag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\ResellerHub\App\Enums\BrandColor;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Enums\OnboardingStep;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Http\Requests\SaveTenantBrandingRequest;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerInvoice;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\DomainVerificationService;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingRegistry;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\TenantQuotaService;

class OnboardingWidget extends Component
{
    public bool $isCollapsed = false;

    public bool $isDismissed = false;

    // Modals
    public bool $showKycModal = false;

    public bool $showDomainModal = false;

    public bool $showBrandingModal = false;

    public bool $showPackageModal = false;

    // Toast/Feedback
    public string $successMessage = '';

    // Step 1: KYC Form
    public array $kycForm = [];

    // Step 2: Domain Setup Form
    public string $domainType = 'subdomain'; // 'subdomain' or 'custom_domain'

    public string $subdomainInput = '';

    public string $customDomainInput = '';

    // Step 3: White-label Branding Form
    public string $brandTitle = '';

    public string $brandPrimaryColor = '#4f46e5';

    public string $brandFooterText = '';

    public string $supportPhone = '';

    public string $supportEmail = '';

    // Step 4: Package selection
    public ?int $selectedPackageId = null;

    public string $selectedPeriod = 'yearly_price';

    public function mount(): void
    {
        $this->isCollapsed = (bool)session('onboarding_widget_collapsed', false);
        $this->isDismissed = (bool)session('onboarding_widget_dismissed', false);

        $tenant = $this->getTenant();
        $reseller = $this->getReseller();
        $allowCustomDomains = $reseller ? $reseller->allowsCustomDomains() : true;

        if ($tenant) {
            $this->subdomainInput = '';
            $this->customDomainInput = (string)($tenant->custom_domain ?? '');
            if ($allowCustomDomains && !empty($tenant->custom_domain)) {
                $this->domainType = 'custom_domain';
            } else {
                $this->domainType = 'subdomain';
            }

            $b = $tenant->branding ?? [];
            $this->brandTitle = (string)($b['brand_title'] ?? $tenant->name);
            $this->brandPrimaryColor = (string)($b['brand_primary_color'] ?? '#4f46e5');
            $this->brandFooterText = (string)($b['brand_footer_text'] ?? '');
            $this->supportPhone = reseller_fa_num((string)($b['support_phone'] ?? ''));
            $this->supportEmail = (string)($b['support_email'] ?? '');
            $this->selectedPackageId = $tenant->package_id;
        }

        $this->loadKycValues();
    }

    public function toggleCollapse(): void
    {
        $this->isCollapsed = !$this->isCollapsed;
        session(['onboarding_widget_collapsed' => $this->isCollapsed]);
    }

    public function dismiss(): void
    {
        $this->isDismissed = true;
        session(['onboarding_widget_dismissed' => true]);
    }

    public function reopen(): void
    {
        $this->isDismissed = false;
        $this->isCollapsed = false;
        session(['onboarding_widget_dismissed' => false, 'onboarding_widget_collapsed' => false]);

        $tenant = $this->getTenant();
        if ($tenant) {
            $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
            $this->redirect($targetUrl, navigate: false);
        }
    }

    public function loadKycValues(): void
    {
        $tenantUser = $this->getTenantUser();
        if ($tenantUser) {
            $existing = UserCustomValue::where('user_id', $tenantUser->id)->get()->keyBy('field_name');
            $subAccountFields = CustomUserField::forSubAccounts()->get();
            foreach ($subAccountFields as $field) {
                $val = $existing->get($field->field_name)?->value ?? '';
                if ($field->field_type === 'checkbox') {
                    $this->kycForm[$field->field_name] = json_decode($val, true) ?: [];
                } else {
                    $this->kycForm[$field->field_name] = $val;
                }
            }
        }
    }

    public function openKycModal(): void
    {
        $this->isCollapsed = false;
        session(['onboarding_widget_collapsed' => false]);
        $this->loadKycValues();
        $this->showKycModal = true;
    }

    public function closeKycModal(): void
    {
        $this->showKycModal = false;
        $this->resetErrorBag();
    }

    public function saveKyc(): void
    {
        $tenantUser = $this->getTenantUser();
        if (!$tenantUser) {
            $this->addError('kyc', __('resellerhub::messages.onboarding.errors.kyc_user_not_found'));

            return;
        }

        $fields = CustomUserField::forSubAccounts()->get();
        $rules = [];
        $attributes = [];

        foreach ($fields as $field) {
            $key = 'kycForm.' . $field->field_name;
            $type = strtolower($field->field_type ?? 'text');
            $base = ($field->is_required ?? false) ? ['required'] : ['nullable'];

            switch ($type) {
                case 'number':
                    $base[] = 'numeric';
                    break;
                case 'date':
                    $base[] = 'date';
                    break;
                case 'email':
                    $base[] = 'email';
                    break;
                default:
                    $base[] = 'string';
            }

            $rules[$key] = $base;
            $attributes[$key] = $field->label ?? $field->field_name;
        }

        $this->validate($rules, [], $attributes);

        foreach ($fields as $field) {
            if (array_key_exists($field->field_name, $this->kycForm)) {
                $raw = $this->kycForm[$field->field_name];
                $val = is_array($raw) ? json_encode(array_values($raw), JSON_UNESCAPED_UNICODE) : (string)$raw;

                UserCustomValue::updateOrCreate(
                    ['user_id' => $tenantUser->id, 'field_name' => $field->field_name],
                    ['value' => $val]
                );
            }
        }

        $this->completeStep(OnboardingStep::COMPLETE_KYC->value);
        $this->showKycModal = false;
        $this->successMessage = __('resellerhub::messages.onboarding.kyc_saved');
    }

    public function openDomainModal(): void
    {
        $this->isCollapsed = false;
        session(['onboarding_widget_collapsed' => false]);
        $tenant = $this->getTenant();
        $reseller = $this->getReseller();
        $allowCustomDomains = $reseller ? $reseller->allowsCustomDomains() : true;

        if ($tenant) {
            $this->subdomainInput = '';
            $this->customDomainInput = (string)($tenant->custom_domain ?? '');
            if ($allowCustomDomains && !empty($tenant->custom_domain)) {
                $this->domainType = 'custom_domain';
            } else {
                $this->domainType = 'subdomain';
            }
        }
        $this->showDomainModal = true;
    }

    public function closeDomainModal(): void
    {
        $this->showDomainModal = false;
        $this->subdomainInput = '';
        $this->resetErrorBag();
    }

    public function saveDomain()
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return null;
        }

        $reseller = $this->getReseller();
        $allowCustomDomains = $reseller ? $reseller->allowsCustomDomains() : true;

        if ($this->domainType === 'custom_domain' && !$allowCustomDomains) {
            $this->addError('customDomainInput', __('resellerhub::messages.onboarding.errors.custom_domain_disabled'));

            return null;
        }

        if ($this->domainType === 'subdomain') {
            $this->validate([
                'subdomainInput' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9-]+$/'],
            ], [
                'subdomainInput.required' => __('resellerhub::messages.onboarding.errors.subdomain_required'),
                'subdomainInput.regex' => __('resellerhub::messages.onboarding.errors.subdomain_regex'),
            ]);

            $candidate = strtolower(trim($this->subdomainInput));
            $reserved = app(TenantManager::class)->getReservedSubdomains();

            if (in_array($candidate, $reserved, true)) {
                $this->addError('subdomainInput', __('resellerhub::messages.onboarding.errors.subdomain_reserved'));

                return null;
            }

            $alreadyTaken = Tenant::where('subdomain', $candidate)->where('id', '!=', $tenant->id)->exists();
            if ($alreadyTaken) {
                $this->addError('subdomainInput', __('resellerhub::messages.onboarding.errors.subdomain_taken'));

                return null;
            }

            $settings = $tenant->settings ?: [];
            $settings['domain_configured'] = true;

            $tenant->update([
                'subdomain' => $candidate,
                'settings' => $settings,
            ]);
        } else {
            $this->validate([
                'customDomainInput' => ['required', 'string', 'min:4', 'max:100', 'regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)+[a-zA-Z]{2,}$/'],
            ], [
                'customDomainInput.required' => __('resellerhub::messages.onboarding.errors.custom_domain_required'),
                'customDomainInput.regex' => __('resellerhub::messages.onboarding.errors.custom_domain_regex'),
            ]);

            $customCandidate = strtolower(trim(preg_replace('#^https?://#', '', $this->customDomainInput)));
            $alreadyTaken = Tenant::where('custom_domain', $customCandidate)->where('id', '!=', $tenant->id)->exists();
            if ($alreadyTaken) {
                $this->addError('customDomainInput', __('resellerhub::messages.onboarding.errors.custom_domain_taken'));

                return null;
            }

            $settings = $tenant->settings ?: [];
            $settings['domain_configured'] = true;

            $tenant->update([
                'custom_domain' => $customCandidate,
                'settings' => $settings,
            ]);
        }

        $tenant->refresh();
        $this->completeStep(OnboardingStep::CONFIGURE_DOMAIN->value);
        $this->showDomainModal = false;
        $this->successMessage = __('resellerhub::messages.onboarding.domain_saved');

        $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
        session()->flash('success', $this->successMessage);

        return $this->redirect($targetUrl, navigate: false);
    }

    public function openBrandingModal(): void
    {
        $this->isCollapsed = false;
        session(['onboarding_widget_collapsed' => false]);
        $tenant = $this->getTenant();
        if ($tenant) {
            $b = $tenant->branding ?: [];
            $this->brandTitle = (string)($b['brand_title'] ?? $tenant->name);
            $this->brandPrimaryColor = (string)($b['brand_primary_color'] ?? '#4f46e5');
            $this->brandFooterText = (string)($b['brand_footer_text'] ?? '');
            $this->supportPhone = reseller_fa_num((string)($b['support_phone'] ?? ''));
            $this->supportEmail = (string)($b['support_email'] ?? '');
        }
        $this->showBrandingModal = true;
    }

    /**
     * Apply one of the ready-made brand color presets.
     */
    public function selectBrandPrimaryColor(string $hex): void
    {
        $normalized = strtolower(trim($hex));

        if (preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/', $normalized) !== 1) {
            return;
        }

        $this->brandPrimaryColor = $normalized;
        $this->resetErrorBag('brandPrimaryColor');
    }

    public function closeBrandingModal(): void
    {
        $this->showBrandingModal = false;
        $this->resetErrorBag();
    }

    public function saveBranding()
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return null;
        }

        $this->validate(
            SaveTenantBrandingRequest::brandingRules(),
            SaveTenantBrandingRequest::brandingMessages()
        );

        $branding = $tenant->branding ?: [];
        $branding['is_custom'] = true;
        $branding['brand_title'] = trim($this->brandTitle);
        $branding['brand_primary_color'] = trim($this->brandPrimaryColor);
        $branding['brand_footer_text'] = trim($this->brandFooterText);
        $branding['support_phone'] = trim($this->supportPhone);
        $branding['support_email'] = trim($this->supportEmail);

        $settings = $tenant->settings ?: [];
        $settings['branding_configured'] = true;

        $tenant->update([
            'branding' => $branding,
            'settings' => $settings,
        ]);
        $tenant->refresh();

        $this->completeStep(OnboardingStep::CONFIGURE_BRANDING->value);
        $this->showBrandingModal = false;
        $this->successMessage = __('resellerhub::messages.onboarding.branding_saved');

        $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
        session()->flash('success', $this->successMessage);

        return $this->redirect($targetUrl, navigate: false);
    }

    public function openPackageModal(): void
    {
        $this->isCollapsed = false;
        session(['onboarding_widget_collapsed' => false]);
        $tenant = $this->getTenant();
        if ($tenant) {
            $this->selectedPackageId = $tenant->package_id;
        }
        $this->showPackageModal = true;
    }

    public function closePackageModal(): void
    {
        $this->showPackageModal = false;
        $this->resetErrorBag();
    }

    public function selectPackage(int $packageId, ?string $period = null)
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return null;
        }

        $pkg = ResellerPackage::find($packageId);
        if (!$pkg) {
            $this->addError('package', __('resellerhub::messages.onboarding.errors.package_invalid'));

            return null;
        }

        $reseller = $tenant->reseller ?: ($pkg->reseller_id ? Reseller::find($pkg->reseller_id) : null);
        if (!$reseller) {
            $this->addError('package', __('resellerhub::messages.onboarding.errors.reseller_not_found'));

            return;
        }

        $periodKey = $period ?: $this->selectedPeriod;
        $months = ResellerPackage::resolveMonthFromPeriodKey($periodKey);

        $user = $this->getTenantUser();
        $billingService = app(ResellerBillingService::class);

        // Enforce advance renewal limit if tenant already has an active plan
        if ($tenant->package_id && $tenant->plan_expires_at && Carbon::parse($tenant->plan_expires_at)->isFuture()) {
            $limitCheck = $billingService->validateAdvanceRenewalLimit($tenant, $months, $reseller->id);
            if (!$limitCheck['allowed']) {
                $this->addError('package', $limitCheck['message']);
                $this->showPackageModal = false;

                $expiresAtJalali = !empty($limitCheck['current_expires_at'])
                    ? reseller_format_jalali($limitCheck['current_expires_at'])
                    : '';

                $maxCycles = $limitCheck['max_cycles'] ?? 1;
                $maxCyclesLabel = __('resellerhub::messages.onboarding.future_cycles', ['count' => reseller_fa_num($maxCycles)]);

                $this->dispatch('open-renewal-limit-modal', [
                    'tenant_name' => $tenant->name,
                    'package_name' => $pkg->name,
                    'max_cycles' => $maxCycles,
                    'max_cycles_label' => $maxCyclesLabel,
                    'expires_at_jalali' => reseller_fa_num($expiresAtJalali),
                    'message' => $limitCheck['message'],
                ]);

                return;
            }
        }

        if ($pkg->isFree()) {
            // Free package: Activate immediately
            $tenant->update([
                'package_id' => $pkg->id,
                'plan_expires_at' => now()->addMonths($months),
            ]);

            $invoice = $billingService->createInvoice($reseller, [
                'tenant_id' => $tenant->id,
                'package_id' => $pkg->id,
                'type' => InvoiceType::OFFICIAL->value,
                'status' => InvoiceStatus::PAID->value,
                'paid_at' => now(),
                'paid_amount' => 0,
                'payment_method' => 'free_grant',
                'sub_account_name' => $user->name ?: $user->mobile,
                'sub_account_mobile' => $user->mobile,
                'sub_account_email' => $user->email ?? '',
                'meta' => [
                    'source' => 'dashboard_onboarding_selection',
                    'source_label' => __('resellerhub::messages.onboarding.source_online_purchase'),
                    'package_id' => $pkg->id,
                    'package_name' => $pkg->name,
                ],
                'items' => [
                    [
                        'item_type' => 'package',
                        'item_id' => 'package_' . $pkg->id,
                        'title' => __('resellerhub::messages.onboarding.free_package_title', ['name' => $pkg->name]),
                        'duration_months' => $months,
                        'quantity' => 1,
                        'unit_price' => 0,
                        'tax_rate' => 0,
                    ],
                ],
            ], $user);

            $billingService->convertInvoiceToOrder($invoice);
            $this->completeStep(OnboardingStep::SELECT_PACKAGE->value);
            $this->showPackageModal = false;
            $this->successMessage = __('resellerhub::messages.onboarding.free_plan_activated', ['name' => $pkg->name]);
            $this->dispatch('notify', ['type' => 'success', 'text' => $this->successMessage]);

            $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
            session()->flash('success', $this->successMessage);

            return $this->redirect($targetUrl, navigate: false);
        }

        // Check if tenant already has an unpaid/pending invoice for this package to prevent duplicates
        $existingInvoice = ResellerInvoice::where('tenant_id', $tenant->id)
            ->whereIn('status', [
                ResellerInvoice::STATUS_UNPAID,
                ResellerInvoice::STATUS_OVERDUE,
                ResellerInvoice::STATUS_DRAFT,
                'pending',
                'unpaid',
            ])
            ->where(function ($q) use ($pkg) {
                $q->where('package_id', $pkg->id)
                    ->orWhere('meta->package_id', $pkg->id)
                    ->orWhere('meta->to_package_id', $pkg->id);
            })
            ->latest('id')
            ->first();

        if ($existingInvoice) {
            $invoice = $existingInvoice;
        } else {
            // Paid package: Generate invoice and redirect to pay
            $rawPrice = $pkg->getPriceForMonths($months);
            $finalPrice = $pkg->getFinalPriceForMonths($months);
            $hasDiscount = ($pkg->isSubscription() ? $pkg->hasActiveDiscountForMonths($months) : $pkg->hasActiveDiscount()) && $finalPrice < $rawPrice;
            $discountAmount = $hasDiscount ? max(0, $rawPrice - $finalPrice) : 0;

            $invoice = $billingService->createInvoice($reseller, [
                'tenant_id' => $tenant->id,
                'package_id' => $pkg->id,
                'type' => InvoiceType::OFFICIAL->value,
                'status' => InvoiceStatus::UNPAID->value,
                'sub_account_name' => $user->name ?: $user->mobile,
                'sub_account_mobile' => $user->mobile,
                'sub_account_email' => $user->email ?? '',
                'meta' => [
                    'source' => 'dashboard_onboarding_selection',
                    'source_label' => __('resellerhub::messages.onboarding.source_online_purchase'),
                    'package_id' => $pkg->id,
                    'package_name' => $pkg->name,
                    'billing_period' => $periodKey,
                    'has_package_discount' => $hasDiscount,
                ],
                'items' => [
                    [
                        'item_type' => 'package',
                        'item_id' => 'package_' . $pkg->id,
                        'title' => $hasDiscount
                            ? __('resellerhub::messages.onboarding.package_with_discount_title', ['name' => $pkg->name])
                            : __('resellerhub::messages.onboarding.package_standard_title', ['name' => $pkg->name]),
                        'duration_months' => $months,
                        'quantity' => 1,
                        'unit_price' => $rawPrice,
                        'discount_amount' => $discountAmount,
                        'tax_rate' => 0,
                    ],
                ],
            ], $user);
        }

        $currency = ResellerSetting::getCurrencyForReseller($reseller?->id);
        $currencyLabel = ResellerSetting::getCurrencyLabelForReseller($reseller?->id);
        $formattedPrice = reseller_price($invoice->total, $currency);

        $this->showPackageModal = false;

        $this->dispatch('open-invoice-payment-modal', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_number_formatted' => reseller_fa_num($invoice->invoice_number),
            'package_name' => $pkg->name,
            'invoice_due' => $invoice->total,
            'invoice_due_formatted' => $formattedPrice,
            'amountFormatted' => $formattedPrice,
            'currencyLabel' => $currencyLabel,
        ]);
    }

    #[On('refresh-onboarding')]
    #[On('onboarding-tour-updated')]
    #[On('onboarding-step-updated')]
    public function refreshWidget(): void
    {
        ResellerSetting::clearRuntimeCache();
        $tenant = $this->getTenant();
        if ($tenant) {
            /** @var OnboardingStateService $stateService */
            $stateService = app(OnboardingStateService::class);
            $stateService->flushCache($tenant->id);
            $progress = $stateService->getTenantProgress($tenant);
            if (!empty($progress['is_finished'])) {
                $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
                $this->redirect($targetUrl, navigate: false);
            }
        }
    }

    public function completeStep(string $stepSlug): void
    {
        $tenant = $this->getTenant();
        if ($tenant) {
            /** @var OnboardingStateService $stateService */
            $stateService = app(OnboardingStateService::class);
            $stateService->markCompleted($tenant->id, $stepSlug, [
                'source' => 'user_action',
                'user_id' => auth()->id(),
            ]);
            $this->dispatch('onboarding-step-updated', step: $stepSlug);

            $progress = $stateService->getTenantProgress($tenant);
            if (!empty($progress['is_finished'])) {
                session()->flash('success', __('resellerhub::messages.onboarding.tour_completed_welcome', [
                    'defaultValue' => 'تبریک! تمامی مراحل راه‌اندازی با موفقیت انجام شد و سامانه به حالت پیشخوان منتقل شد.',
                ]));
                $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
                $this->redirect($targetUrl, navigate: false);
            }
        }
    }

    public function getStepActionUrl($step): ?string
    {
        $stepData = is_array($step) ? ($step['step'] ?? null) : $step;
        if (!$stepData) {
            return null;
        }

        $actionUrl = is_object($stepData) ? ($stepData->actionUrl ?? null) : ($stepData['action_url'] ?? null);
        $actionType = strtolower((string)(is_object($stepData) ? ($stepData->actionType ?? '') : ($stepData['action_type'] ?? '')));

        if (empty($actionUrl)) {
            return null;
        }

        if (in_array($actionType, ['external', 'link'], true)) {
            return $actionUrl;
        }

        if ($actionType === 'internal') {
            $tenant = $this->getTenant();
            $path = ltrim($actionUrl, '/');
            if ($tenant) {
                return app(TenantManager::class)->getTenantUrl($tenant, $path);
            }

            return url($path);
        }

        return $actionUrl;
    }

    public function completeCustomStep(string $stepSlug): void
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return;
        }

        $registry = app(OnboardingRegistry::class);
        $step = $registry->getStep($stepSlug);

        // If step requires quota and tenant has no quota / active plan, redirect to package modal
        if ($step && $step->requiredQuota && $tenant->reseller_id && !$tenant->hasActivePaidPlan()) {
            $quotaService = app(TenantQuotaService::class);
            if (!$quotaService->hasRemainingQuota($tenant, $step->requiredQuota)) {
                $this->handleQuotaLockedStep($stepSlug);

                return;
            }
        }

        // Strict enterprise validation: if manual completion is not allowed, check entity
        if ($step && !$step->canManualComplete) {
            $stateService = app(OnboardingStateService::class);
            if ($stateService->isStepAutoVerifiable($step)) {
                $user = $this->getTenantUser();
                $exists = $stateService->verifyStepEntityExistence($tenant, $step, $user);

                if (!$exists) {
                    $this->addError('step', __('resellerhub::messages.onboarding.step_real_data_error'));
                    $this->dispatch('notify', [
                        'type' => 'warning',
                        'text' => __('resellerhub::messages.onboarding.step_real_data_required'),
                    ]);

                    return;
                }
            }
        }

        $this->completeStep($stepSlug);
        $this->successMessage = __('resellerhub::messages.onboarding.step_completed');
        $this->dispatch('notify', ['type' => 'success', 'text' => $this->successMessage]);
    }

    public function handleQuotaLockedStep(string $stepSlug): void
    {
        $registry = app(OnboardingRegistry::class);
        $step = $registry->getStep($stepSlug);
        $stepTitle = $step?->title ?? $stepSlug;

        $this->showPackageModal = true;
        $this->dispatch('notify', [
            'type' => 'info',
            'text' => __('resellerhub::messages.onboarding.step_quota_locked_notice', ['step' => $stepTitle]),
        ]);
    }

    public function skipStep(string $stepSlug): void
    {
        $tenant = $this->getTenant();
        if ($tenant) {
            $step = app(OnboardingRegistry::class)->getStep($stepSlug);
            if ($step && $step->isCore) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'text' => __('resellerhub::messages.onboarding.core_step_cannot_be_skipped'),
                ]);
                return;
            }

            $stateService = app(OnboardingStateService::class);
            $skipped = $stateService->markSkipped($tenant->id, $stepSlug);
            if ($skipped) {
                $this->successMessage = __('resellerhub::messages.onboarding.step_skipped');
                $this->dispatch('notify', ['type' => 'info', 'text' => $this->successMessage]);
                $this->dispatch('onboarding-step-updated', step: $stepSlug);

                $progress = $stateService->getTenantProgress($tenant);
                if (!empty($progress['is_finished'])) {
                    session()->flash('success', __('resellerhub::messages.onboarding.tour_completed_welcome', [
                        'defaultValue' => 'تبریک! تمامی مراحل راه‌اندازی با موفقیت انجام شد و سامانه به حالت پیشخوان منتقل شد.',
                    ]));
                    $targetUrl = app(TenantManager::class)->getTenantUrl($tenant, 'user/dashboard');
                    $this->redirect($targetUrl, navigate: false);
                }
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'text' => __('resellerhub::messages.onboarding.core_step_cannot_be_skipped'),
                ]);
            }
        }
    }

    protected function getTenantUser(): ?User
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return auth()->user();
        }

        $tenantUser = User::where('tenant_id', $tenant->id)->first();
        if (!$tenantUser && $tenant->sub_account_id && $tenant->subAccount) {
            $tenantUser = $tenant->subAccount->user;
        }

        return $tenantUser ?: auth()->user();
    }

    protected function getTenant(): ?Tenant
    {
        $tenant = null;
        if (app()->bound(TenantManager::class)) {
            $tenant = app(TenantManager::class)->getTenant();
        }

        if (!$tenant && app()->bound('tenant')) {
            $tenant = app('tenant');
        }

        if (!$tenant && auth()->check()) {
            $tenant = auth()->user()->tenant;
            if (!$tenant && auth()->user()->tenant_id) {
                $tenant = Tenant::find(auth()->user()->tenant_id);
            }
        }

        return $tenant;
    }

    #[Computed]
    public function reseller(): ?Reseller
    {
        return $this->getReseller();
    }

    public function getResellerProperty(): ?Reseller
    {
        return $this->getReseller();
    }

    protected function getReseller(): ?Reseller
    {
        $tenant = $this->getTenant();

        return $tenant?->reseller ?: ($tenant?->reseller_id ? Reseller::find($tenant->reseller_id) : null);
    }

    public function render()
    {
        try {
            $tenant = $this->getTenant();

            if (!$tenant) {
                return view('resellerhub::livewire.dashboard.onboarding-empty');
            }

            /** @var OnboardingStateService $stateService */
            $stateService = app(OnboardingStateService::class);
            $progress = $stateService->getTenantProgress($tenant);

            $tourConfig = ResellerSetting::getOnboardingTourConfig($tenant->reseller_id);

            if (!$tourConfig['enabled'] || empty($progress['journeys']) || empty($progress['total']) || !empty($progress['is_finished'])) {
                return view('resellerhub::livewire.dashboard.onboarding-empty');
            }

            $viewErrorBag = new ViewErrorBag;
            $viewErrorBag->put('default', $this->getErrorBag());

            $subAccountFields = CustomUserField::forSubAccounts()->get();
            $packages = ResellerPackage::where('reseller_id', $tenant->reseller_id ?: 1)
                ->active()
                ->orderBy('sort_order')
                ->get();

            if ($packages->isEmpty()) {
                $packages = ResellerPackage::active()->orderBy('sort_order')->get();
            }

            $cnameTarget = app(DomainVerificationService::class)->getTargetCname();

            $availablePeriods = ResellerHelper::getAllAvailablePeriods();
            if (empty($this->selectedPeriod) || !isset($availablePeriods[$this->selectedPeriod])) {
                $firstKey = !empty($availablePeriods) ? array_key_first($availablePeriods) : 'yearly_price';
                $this->selectedPeriod = isset($availablePeriods['yearly_price']) ? 'yearly_price' : $firstKey;
            }

            $reseller = $this->getReseller();
            $allowCustomDomains = $reseller ? $reseller->allowsCustomDomains() : true;

            return view('resellerhub::livewire.dashboard.onboarding-widget', [
                'tenant' => $tenant,
                'tourConfig' => $tourConfig,
                'progress' => $progress,
                'isFinished' => $progress['is_finished'],
                'isDismissed' => $this->isDismissed,
                'isCollapsed' => $this->isCollapsed,
                'showKycModal' => $this->showKycModal,
                'showDomainModal' => $this->showDomainModal,
                'showBrandingModal' => $this->showBrandingModal,
                'showPackageModal' => $this->showPackageModal,
                'subAccountFields' => $subAccountFields,
                'packages' => $packages,
                'availablePeriods' => $availablePeriods,
                'cnameTarget' => $cnameTarget,
                'successMessage' => $this->successMessage,
                'selectedPeriod' => $this->selectedPeriod,
                'selectedPackageId' => $this->selectedPackageId,
                'allowCustomDomains' => $allowCustomDomains,
                'domainType' => $this->domainType,
                'subdomainInput' => $this->subdomainInput,
                'customDomainInput' => $this->customDomainInput,
                'brandTitle' => $this->brandTitle,
                'brandPrimaryColor' => $this->brandPrimaryColor,
                'colorPresets' => BrandColor::presets(),
                'brandFooterText' => $this->brandFooterText,
                'supportPhone' => $this->supportPhone,
                'supportEmail' => $this->supportEmail,
                'errors' => $viewErrorBag,
            ]);
        } catch (\Throwable $e) {
            return view('resellerhub::livewire.dashboard.onboarding-empty');
        }
    }
}
