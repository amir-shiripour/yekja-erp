<?php

namespace Modules\ResellerHub\App\Livewire\Portal;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Modules\ResellerHub\App\Jobs\ProvisionTenantJob;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Models\ResellerSetting;
use Modules\ResellerHub\App\Services\Portal\CheckoutPricingService;
use Modules\ResellerHub\App\Services\Pricing\PriceRoundingService;
use Modules\ResellerHub\App\Services\Payment\GatewayAmountResolver;
use Modules\ResellerHub\App\Services\ResellerBillingService;
use Modules\ResellerHub\App\Services\DomainVerificationService;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;
use App\Tenancy\TenantManager;
use App\Services\PaymentService;
use Modules\ResellerHub\App\Enums\InvoiceType;
use Modules\ResellerHub\App\Enums\InvoiceStatus;
use Modules\ResellerHub\App\Models\ResellerSubAccount;
use Modules\ResellerHub\App\Services\Onboarding\OnboardingStateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\ResellerHub\App\Http\Requests\PortalCheckoutRequest;
use Modules\ResellerHub\App\Services\Checkout\PendingCheckoutService;
use Throwable;

#[Layout('layouts.guest')]
class Checkout extends Component
{
    use WithFileUploads;

    public $step = 1;

    // Step 1: Package Selection
    #[Url(as: 'reseller')]
    public $resellerId = null;

    #[Url(as: 'package')]
    public $selectedPackageId = null;

    #[Url(as: 'period')]
    public $billingPeriod = 'monthly_price';

    public bool $showPackageSelector = false;

    // Step 1: Domain Selection
    public string $domainType = 'subdomain'; // 'subdomain' or 'custom_domain'
    public string $subdomain = '';
    public string $customDomain = '';
    public string $domainError = '';
    public string $domainSuccess = '';
    public string $customDomainError = '';
    public string $customDomainSuccess = '';

    // Step 1: White-Label Branding
    public string $whiteLabelType = 'default'; // 'default' or 'custom'
    public string $brandTitle = '';
    public string $brandPrimaryColor = '#4f46e5';
    public string $brandFooterText = '';
    public string $supportPhone = '';
    public string $supportEmail = '';
    public $brandLogo = null;
    public $brandFavicon = null;

    // Step 1: Payment Gateway Selection
    public string $selectedGateway = '';

    public function mount()
    {
        $this->selectedGateway = ResellerHelper::getDefaultGateway();
        $user = Auth::user();
        if ($user && $user->tenant_id) {
            return redirect()->route('user.dashboard');
        }

        if (request()->has('reseller')) {
            $this->resellerId = (int)request()->query('reseller');
        }

        if (request()->has('package')) {
            $this->selectedPackageId = (int)request()->query('package');
        }

        if (request()->has('period')) {
            $this->billingPeriod = request()->query('period');
        }

        $reseller = $this->resolveReseller();
        if ($reseller) {
            $defaultColor = ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5');
            $this->brandPrimaryColor = $defaultColor ?: '#4f46e5';
        }

        // If package is already specified and exists for this reseller, keep selector collapsed
        $packageExistsQuery = ResellerPackage::active();
        if ($reseller) {
            $packageExistsQuery->where('reseller_id', $reseller->id);
        }
        $existingPkg = $this->selectedPackageId ? (clone $packageExistsQuery)->find($this->selectedPackageId) : null;

        if ($existingPkg) {
            $this->showPackageSelector = false;
            if (!$existingPkg->isFree() && (empty($this->billingPeriod) || !$existingPkg->isPeriodActive($this->billingPeriod))) {
                $active = $existingPkg->getActivePeriods();
                if (!empty($active)) {
                    $this->billingPeriod = $active[0];
                }
            }
        } else {
            // Check if reseller configured an active default package for direct registration
            $defaultPkg = $reseller ? ResellerSetting::getDefaultRegistrationPackage($reseller->id) : null;
            if ($defaultPkg) {
                $this->selectedPackageId = $defaultPkg->id;
                $this->showPackageSelector = false;
            } else {
                // Fallback: pre-select first package of this reseller and open the selector
                $firstQuery = ResellerPackage::active();
                if ($reseller) {
                    $firstQuery->where('reseller_id', $reseller->id);
                }
                $first = $firstQuery->orderBy('monthly_price')->first();
                if ($first) {
                    $this->selectedPackageId = $first->id;
                }
                $this->showPackageSelector = true;
            }
        }
    }

    public function setWhiteLabelType(string $type): void
    {
        $this->whiteLabelType = in_array($type, ['default', 'custom'], true) ? $type : 'default';
    }

    public function setPrimaryColor(string $color): void
    {
        $color = trim($color);
        if (preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $color)) {
            $this->brandPrimaryColor = $color;
        }
    }

    public function removeBrandLogo(): void
    {
        $this->brandLogo = null;
        $this->resetErrorBag('brandLogo');
    }

    public function removeBrandFavicon(): void
    {
        $this->brandFavicon = null;
        $this->resetErrorBag('brandFavicon');
    }

    public function resolveReseller(): ?Reseller
    {
        if ($this->resellerId) {
            $r = Reseller::find($this->resellerId);
            if ($r) return $r;
        }

        if ($this->selectedPackageId) {
            $pkg = ResellerPackage::find($this->selectedPackageId);
            if ($pkg?->reseller_id) {
                $r = Reseller::find($pkg->reseller_id);
                if ($r) {
                    $this->resellerId = $r->id;
                    return $r;
                }
            }
        }

        return null;
    }

    public function getWhiteLabelFee(int $months): array
    {
        return app(CheckoutPricingService::class)
            ->getWhiteLabelFee($this->resolveReseller(), $this->whiteLabelType, $months);
    }

    public function isRegistrationWithoutPaymentAllowed(): bool
    {
        if (!$this->selectedPackageId) {
            return false;
        }

        $package = ResellerPackage::find($this->selectedPackageId);
        if (!$package || !$package->isFree()) {
            return false;
        }

        // "Free" must mean the whole checkout is free, not just the base price.
        // Setup fees and white-label fees are part of what the customer owes.
        return $this->calculatePayableTotal($package, $this->resolveMonths()) <= 0;
    }

    /**
     * Server-side mirror of the values shown in the UI. Livewire state is
     * client-controlled, so nothing may be trusted at submit time.
     */
    protected function assertCheckoutStateIsValid(): bool
    {
        $this->resetErrorBag();

        $reseller = $this->resolveReseller();

        $packageValidator = Validator::make(
            ['selectedPackageId' => $this->selectedPackageId],
            PortalCheckoutRequest::packageRules($reseller?->id),
            PortalCheckoutRequest::packageMessages()
        );

        if ($packageValidator->fails()) {
            $this->addError('payment', $packageValidator->errors()->first('selectedPackageId'));
            return false;
        }

        if ($this->domainType === 'custom_domain') {
            if (!$this->validateCustomDomain()) {
                $this->addError('payment', $this->customDomainError);
                return false;
            }

            if (!$this->validateSubdomain()) {
                $this->addError('payment', $this->domainError);
                return false;
            }
        } elseif (!$this->validateSubdomain()) {
            $this->addError('payment', $this->domainError);
            return false;
        }

        if ($this->subdomainIsTaken()) {
            $this->domainError = __('resellerhub::messages.errors.subdomain_taken', ['subdomain' => $this->subdomain]);
            $this->addError('payment', $this->domainError);
            return false;
        }

        if ($this->whiteLabelType === 'custom') {
            $brandingValidator = Validator::make([
                'brandTitle' => $this->brandTitle,
                'brandPrimaryColor' => $this->brandPrimaryColor,
                'brandFooterText' => $this->brandFooterText,
                'supportPhone' => $this->supportPhone,
                'supportEmail' => $this->supportEmail,
                'brandLogo' => $this->brandLogo,
                'brandFavicon' => $this->brandFavicon,
            ], PortalCheckoutRequest::brandingRules(), PortalCheckoutRequest::brandingMessages());

            if ($brandingValidator->fails()) {
                $this->addError('payment', $brandingValidator->errors()->first());
                return false;
            }
        }

        return true;
    }

    /**
     * Total the customer would actually be charged for this checkout.
     */
    protected function calculatePayableTotal(ResellerPackage $package, int $months): int
    {
        $unitPrice = $package->getFinalPriceForMonths($months);
        $items = $this->resolveInvoiceItems($package, $months, $unitPrice);

        $total = 0;

        foreach ($items as $item) {
            $total += max(0, (int)($item['unit_price'] ?? 0) - (int)($item['discount_amount'] ?? 0));
        }

        $reseller = $this->resolveReseller();
        if ($total > 0 && $reseller) {
            return app(PriceRoundingService::class)->round($total, $reseller->id);
        }

        return $total;
    }

    public function getTotalPrice(ResellerPackage $package, int $months): int
    {
        if ($this->isRegistrationWithoutPaymentAllowed() && (int)$this->selectedPackageId === (int)$package->id) {
            return 0;
        }

        return app(CheckoutPricingService::class)
            ->getTotalPrice($package, $months, $this->resolveReseller(), $this->whiteLabelType);
    }

    public function togglePackageSelector()
    {
        $this->showPackageSelector = !$this->showPackageSelector;
    }

    public function selectPackage($id)
    {
        $this->selectedPackageId = $id;

        $package = ResellerPackage::find($id);
        if ($package && !$package->isFree() && (empty($this->billingPeriod) || !$package->isPeriodActive($this->billingPeriod))) {
            $active = $package->getActivePeriods();
            if (!empty($active)) {
                $this->billingPeriod = $active[0];
            }
        }
        $this->showPackageSelector = false;
    }

    public function setDomainType(string $type)
    {
        $this->domainType = in_array($type, ['subdomain', 'custom_domain'], true) ? $type : 'subdomain';
        $this->domainError = '';
        $this->customDomainError = '';
    }

    public function updatedSubdomain()
    {
        $this->validateSubdomain();
    }

    public function validateSubdomain(): bool
    {
        $this->subdomain = strtolower(trim($this->subdomain));
        $this->domainError = '';
        $this->domainSuccess = '';

        $reusableId = null;
        $user = Auth::user();
        if ($user) {
            $reusable = app(PendingCheckoutService::class)
                ->findReusable($user, (int)$this->resolveReseller()?->id);
            $reusableId = $reusable?->id;
        }

        $validator = Validator::make(
            ['subdomain' => $this->subdomain],
            PortalCheckoutRequest::subdomainRules($reusableId),
            PortalCheckoutRequest::subdomainMessages()
        );

        if ($validator->fails()) {
            $this->domainError = $validator->errors()->first('subdomain');
            return false;
        }

        $this->domainSuccess = 'زیردامنه آزاد و آماده استفاده است.';
        return true;
    }

    protected function subdomainIsTaken(): bool
    {
        $query = Tenant::query()->where('subdomain', $this->subdomain);

        $user = Auth::user();
        if ($user) {
            $reusable = app(PendingCheckoutService::class)
                ->findReusable($user, (int)$this->resolveReseller()?->id);

            if ($reusable) {
                $query->whereKeyNot($reusable->id);
            }
        }

        return $query->exists();
    }

    public function updatedCustomDomain()
    {
        $this->validateCustomDomain();
    }

    public function validateCustomDomain(): bool
    {
        $domainService = app(DomainVerificationService::class);
        $cleaned = $domainService->sanitizeDomain($this->customDomain);
        $this->customDomain = $cleaned ?? trim($this->customDomain);
        $this->customDomainError = '';
        $this->customDomainSuccess = '';

        $validator = Validator::make(
            ['custom_domain' => $this->customDomain],
            PortalCheckoutRequest::customDomainRules(),
            PortalCheckoutRequest::customDomainMessages()
        );

        if ($validator->fails()) {
            $this->customDomainError = $validator->errors()->first('custom_domain');
            return false;
        }

        if (empty($this->subdomain)) {
            $this->subdomain = $this->generateSubdomainFromCustomDomain($this->customDomain);
        }

        $this->customDomainSuccess = 'دامنه معتبر و قابل اتصال است.';
        return true;
    }

    protected function generateSubdomainFromCustomDomain(string $customDomain): string
    {
        $base = Str::slug(Str::before($customDomain, '.'));
        $base = preg_replace('/[^a-z0-9\-]/', '', $base);
        if (strlen($base) < 3) {
            $base = 'crm-' . ($base ?: 'app');
        }
        $candidate = $base;
        $counter = 1;
        $reserved = app(TenantManager::class)->getReservedSubdomains();
        while (Tenant::where('subdomain', $candidate)->exists() || in_array($candidate, $reserved, true)) {
            $candidate = $base . $counter++;
        }
        return $candidate;
    }

    public function nextStep()
    {
        $this->resetErrorBag();

        $reseller = $this->resolveReseller();

        $packageValidator = Validator::make(
            ['selectedPackageId' => $this->selectedPackageId],
            PortalCheckoutRequest::packageRules($reseller?->id),
            PortalCheckoutRequest::packageMessages()
        );

        if ($packageValidator->fails()) {
            $this->addError('package', $packageValidator->errors()->first('selectedPackageId'));
            return;
        }

        if ($this->domainType === 'custom_domain') {
            if (!$this->validateCustomDomain()) {
                return;
            }
            if (!$this->validateSubdomain()) {
                return;
            }
        } else {
            if (!$this->validateSubdomain()) {
                return;
            }
        }

        if ($this->whiteLabelType === 'custom') {
            $this->validate(
                PortalCheckoutRequest::brandingRules(),
                PortalCheckoutRequest::brandingMessages()
            );
        }

        $this->step = 2;
    }

    protected function resolveMonths(): int
    {
        return ResellerPackage::resolveMonthFromPeriodKey($this->billingPeriod);
    }

    protected function resolveBranding($reseller, $user): array
    {
        $isCustom = ($this->whiteLabelType === 'custom');

        $uploadedLogo = null;
        if ($isCustom && $this->brandLogo && method_exists($this->brandLogo, 'store')) {
            $uploadedLogo = $this->brandLogo->store('branding/tenants/logos', 'public');
        }

        $uploadedFavicon = null;
        if ($isCustom && $this->brandFavicon && method_exists($this->brandFavicon, 'store')) {
            $uploadedFavicon = $this->brandFavicon->store('branding/tenants/favicons', 'public');
        }

        $defaultColor = $reseller ? ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5') : '#4f46e5';

        return [
            'is_custom' => $isCustom,
            'brand_title' => ($isCustom && !empty($this->brandTitle))
                ? trim($this->brandTitle)
                : ($reseller?->brand_title ?: ($user->name ?: $this->subdomain)),
            'brand_primary_color' => ($isCustom && !empty($this->brandPrimaryColor))
                ? trim($this->brandPrimaryColor)
                : $defaultColor,
            'brand_logo' => ($isCustom && $uploadedLogo)
                ? $uploadedLogo
                : ($reseller?->brand_logo),
            'brand_favicon' => ($isCustom && $uploadedFavicon)
                ? $uploadedFavicon
                : ($reseller?->brand_favicon),
            'brand_footer_text' => ($isCustom && !empty($this->brandFooterText))
                ? trim($this->brandFooterText)
                : ($reseller?->brand_footer_text),
            'support_phone' => ($isCustom && !empty($this->supportPhone))
                ? trim($this->supportPhone)
                : ($reseller?->support_phone),
            'support_email' => ($isCustom && !empty($this->supportEmail))
                ? trim($this->supportEmail)
                : ($reseller?->support_email),
        ];
    }

    protected function resolveInvoiceItems($package, int $months, int $unitPrice): array
    {
        $rawPrice = $package->getPriceForMonths($months);
        $finalPrice = $package->getFinalPriceForMonths($months);
        $hasDiscount = ($package->isSubscription() ? $package->hasActiveDiscountForMonths($months) : $package->hasActiveDiscount()) && $finalPrice < $rawPrice;
        $discountAmount = $hasDiscount ? max(0, $rawPrice - $finalPrice) : 0;
        $itemUnitPrice = $hasDiscount ? $rawPrice : $unitPrice;

        $items = [
            [
                'item_type' => 'package',
                'item_id' => 'package_' . $package->id,
                'title' => "خرید پکیج {$package->name} - {$months} ماهه" . ($hasDiscount ? ' (با تخفیف ویژه)' : ''),
                'description' => "راه‌اندازی و تخصیص سامانه مدیریت مشتریان",
                'duration_months' => $months,
                'quantity' => 1,
                'unit_price' => $itemUnitPrice,
                'discount_amount' => $discountAmount,
                'tax_rate' => 0,
            ]
        ];

        $packageSetupFee = $package->getFinalSetupFeeAttribute();
        if ($packageSetupFee > 0) {
            $items[] = [
                'item_type' => 'setup_fee',
                'item_id' => 'package_' . $package->id . '_setup',
                'title' => "هزینه راه‌اندازی اولیه پکیج «{$package->name}»",
                'description' => 'هزینه راه‌اندازی و کانفیگ اولیه سامانه',
                'duration_months' => 0,
                'quantity' => 1,
                'unit_price' => $packageSetupFee,
                'discount_amount' => 0,
                'tax_rate' => 0,
            ];
        }

        $wlFee = $this->getWhiteLabelFee($months);
        if ($wlFee['recurring'] > 0) {
            $brandLabel = !empty($this->brandTitle) ? $this->brandTitle : 'سفارشی';
            $items[] = [
                'item_type' => 'white_label',
                'item_id' => 'white_label',
                'title' => "برندینگ اختصاصی وایت‌لیبل ({$brandLabel})",
                'description' => "لایسنس و پیکربندی برندینگ و تم سازمانی اختصاصی برای دوره {$months} ماهه",
                'duration_months' => $months,
                'quantity' => 1,
                'unit_price' => $wlFee['recurring'],
                'discount_amount' => 0,
                'tax_rate' => 0,
            ];
        }

        if ($wlFee['setup'] > 0) {
            $items[] = [
                'item_type' => 'setup_fee',
                'item_id' => 'white_label_setup',
                'title' => "هزینه راه‌اندازی و پیکربندی وایت‌لیبل",
                'description' => "هزینه اولیه پیکربندی هویت بصری، لوگو و تم اختصاصی",
                'duration_months' => 0,
                'quantity' => 1,
                'unit_price' => $wlFee['setup'],
                'discount_amount' => 0,
                'tax_rate' => 0,
            ];
        }

        return $items;
    }

    public function pay()
    {
        $user = Auth::user();

        if (!$user) {
            $this->addError('payment', __('resellerhub::messages.portal.login_required'));
            return;
        }

        if (!$this->assertCheckoutStateIsValid()) {
            return;
        }

        $reseller = $this->resolveReseller();

        if (!$reseller || !$reseller->isActive()) {
            $this->addError('payment', __('resellerhub::messages.portal.reseller_unavailable'));
            return;
        }

        $package = ResellerPackage::where('reseller_id', $reseller->id)
            ->where('is_active', true)
            ->find($this->selectedPackageId);

        if (!$package) {
            $this->addError('payment', __('resellerhub::messages.portal.invalid_package'));
            return;
        }

        // If package is free or eligible for default package free registration, proceed directly
        if ($this->isRegistrationWithoutPaymentAllowed()) {
            return $this->registerFree();
        }

        $months = $this->resolveMonths();

        // Gateway determination
        $gateway = $this->selectedGateway ?: ResellerHelper::getDefaultGateway();
        if (!ResellerHelper::isGatewayActive($gateway)) {
            $gateway = ResellerHelper::getDefaultGateway();
        }

        if (!$gateway || !ResellerHelper::isGatewayActive($gateway)) {
            $this->addError('payment', __('resellerhub::messages.portal.no_gateway_available'));
            return;
        }

        $this->selectedGateway = $gateway;

        $invoice = null;

        try {
            $invoice = DB::transaction(function () use ($user, $reseller, $package, $months, $gateway) {
                // 1. Find or Create SubAccount for Reseller
                $subAccount = ResellerSubAccount::firstOrCreate(
                    [
                        'reseller_id' => $reseller->id,
                        'mobile' => $user->mobile,
                    ],
                    [
                        'name' => $user->name ?: $user->mobile,
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'company_name' => $user->name ?: $user->mobile,
                        'status' => ResellerSubAccount::STATUS_ACTIVE,
                        'limits' => $package->limits ?? null,
                        'settings' => [
                            'source' => 'portal_purchase',
                            'source_label' => 'خرید آنلاین از سایت',
                            'package_id' => $package->id,
                            'package_name' => $package->name,
                            'billing_period' => $this->billingPeriod,
                            'acquired_at' => now()->toIso8601String(),
                        ],
                    ]
                );

                // 2. Resolve Branding & Reusable Pending Tenant
                $pending = app(PendingCheckoutService::class);
                $tenant = $pending->findReusable($user, (int)$reseller->id);

                $branding = $this->resolveBranding($reseller, $user);
                $tenantName = ($this->whiteLabelType === 'custom' && !empty($this->brandTitle))
                    ? trim($this->brandTitle)
                    : ($user->name ?: $user->mobile);

                $attributes = [
                    'reseller_id' => $reseller->id,
                    'package_id' => $package->id,
                    'sub_account_id' => $subAccount->id,
                    'name' => $tenantName,
                    'subdomain' => $this->subdomain,
                    'custom_domain' => ($this->domainType === 'custom_domain' && !empty($this->customDomain)) ? $this->customDomain : null,
                    'branding' => $branding,
                    'settings' => array_merge(
                        is_array($tenant?->settings) ? $tenant->settings : [],
                        ['source' => 'online_purchase']
                    ),
                ];

                if ($tenant) {
                    $pending->releaseOpenInvoices($tenant);          // I6: یک فاکتور باز در هر لحظه
                    $tenant = $pending->markPending($tenant, $attributes);
                } else {
                    $tenant = $pending->markPending(new Tenant(), $attributes);
                }

                // لینک sub-account: فقط اگر به تننت مرده/درانتظار اشاره میکند
                $current = $subAccount->tenant_id ? Tenant::withTrashed()->find($subAccount->tenant_id) : null;
                if (!$current || $current->trashed() || $current->isPendingPayment()) {
                    $subAccount->update(['tenant_id' => $tenant->id]);
                }

                // Auto-complete initial profile step since user registered with name and verified mobile
                try {
                    app(OnboardingStateService::class)->markCompleted($tenant->id, 'complete_profile', [
                        'source' => 'online_registration',
                        'user_id' => $user->id,
                    ]);
                } catch (Throwable) {
                }

                // 3. Generate Invoice
                $unitPrice = $package->getFinalPriceForMonths($months);
                $items = $this->resolveInvoiceItems($package, $months, $unitPrice);

                $billingService = app(ResellerBillingService::class);
                return $billingService->createInvoice($reseller, [
                    'tenant_id' => $tenant->id,
                    'package_id' => $package->id,
                    'type' => InvoiceType::OFFICIAL->value,
                    'status' => InvoiceStatus::UNPAID->value,
                    'sub_account_name' => $user->name ?: $user->mobile,
                    'sub_account_mobile' => $user->mobile,
                    'sub_account_email' => $user->email ?? '',
                    'meta' => [
                        'source' => 'portal_purchase',
                        'source_label' => 'خرید آنلاین از سایت',
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'billing_period' => $this->billingPeriod,
                        'domain_type' => $this->domainType,
                        'subdomain' => $this->subdomain,
                        'white_label_type' => $this->whiteLabelType,
                        'brand_title' => $branding['brand_title'] ?? null,
                        'gateway' => $gateway,
                        'gateway_name' => ResellerHelper::getGatewayName($gateway),
                    ],
                    'items' => $items,
                ], $user);
            }, 3);
        } catch (Throwable $e) {
            Log::error('[Checkout] Failed to create checkout order: ' . $e->getMessage(), [
                'reseller_id' => $reseller->id,
                'package_id' => $package->id,
                'user_id' => $user->id,
            ]);

            $this->addError('payment', __('resellerhub::messages.portal.unexpected_error'));
            return;
        }

        // 4. Connect to Selected Payment Gateway
        try {
            $paymentService = new PaymentService($gateway);
            $verifyUrl = route('portal.checkout.verify', [
                'invoice' => $invoice->id,
                'gateway' => $gateway,
            ]);

            $result = $paymentService->requestPayment(
                (float)app(GatewayAmountResolver::class)->forInvoice($invoice)['amount'],
                "سفارش شماره {$invoice->invoice_number}",
                $user->email ?? '',
                $user->mobile,
                $verifyUrl
            );
        } catch (Throwable $e) {
            Log::error('[Checkout] Gateway initiation failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'gateway' => $gateway,
            ]);

            $this->addError('payment', __('resellerhub::messages.portal.gateway_server_error'));
            return;
        }

        if (!empty($result['success']) && !empty($result['payment_url'])) {
            Log::info(json_encode([
                'checkout_id' => $invoice->meta['checkout_id'] ?? $invoice->invoice_number,
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'stage' => 'pay_initiated',
            ], JSON_UNESCAPED_UNICODE));

            return redirect()->away($result['payment_url']);
        }

        $gatewayLabel = ResellerHelper::getGatewayName($gateway);
        $this->addError('payment', 'خطا در ارتباط با درگاه پرداخت (' . $gatewayLabel . '): ' . ($result['message'] ?? 'ناشناخته'));
    }

    public function selectGateway(string $gateway): void
    {
        if (ResellerHelper::isGatewayActive($gateway)) {
            $this->selectedGateway = $gateway;
        }
    }

    public function registerFree()
    {
        $user = Auth::user();

        if (!$user) {
            $this->addError('payment', __('resellerhub::messages.portal.login_required'));
            return;
        }

        if (!$this->assertCheckoutStateIsValid()) {
            return;
        }

        $reseller = $this->resolveReseller();

        if (!$reseller || !$reseller->isActive()) {
            $this->addError('payment', __('resellerhub::messages.portal.reseller_unavailable'));
            return;
        }

        $package = ResellerPackage::where('reseller_id', $reseller->id)
            ->where('is_active', true)
            ->find($this->selectedPackageId);

        if (!$package) {
            $this->addError('payment', __('resellerhub::messages.portal.invalid_package'));
            return;
        }

        // Enterprise Security Guard: Strictly verify registration without payment is allowed!
        if (!$this->isRegistrationWithoutPaymentAllowed()) {
            $this->addError('payment', __('resellerhub::messages.portal.payment_required'));
            return;
        }

        $months = $this->resolveMonths();

        $tenant = null;

        try {
            $tenant = DB::transaction(function () use ($user, $reseller, $package, $months) {
                $subAccount = ResellerSubAccount::firstOrCreate(
                    [
                        'reseller_id' => $reseller->id,
                        'mobile' => $user->mobile,
                    ],
                    [
                        'name' => $user->name ?: $user->mobile,
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'company_name' => $user->name ?: $user->mobile,
                        'status' => ResellerSubAccount::STATUS_ACTIVE,
                        'limits' => $package->limits ?? null,
                        'settings' => [
                            'source' => 'portal_free_registration',
                            'source_label' => 'ثبت‌نام مستقیم و رایگان از سایت',
                            'package_id' => $package->id,
                            'package_name' => $package->name,
                            'billing_period' => $this->billingPeriod,
                            'acquired_at' => now()->toIso8601String(),
                        ],
                    ]
                );

                $pending = app(PendingCheckoutService::class);
                $tenant = $pending->findReusable($user, (int)$reseller->id);

                $branding = $this->resolveBranding($reseller, $user);
                $tenantName = ($this->whiteLabelType === 'custom' && !empty($this->brandTitle))
                    ? trim($this->brandTitle)
                    : ($user->name ?: $user->mobile);

                $attributes = [
                    'reseller_id' => $reseller->id,
                    'package_id' => $package->id,
                    'sub_account_id' => $subAccount->id,
                    'name' => $tenantName,
                    'subdomain' => $this->subdomain,
                    'custom_domain' => ($this->domainType === 'custom_domain' && !empty($this->customDomain)) ? $this->customDomain : null,
                    'branding' => $branding,
                    'settings' => array_merge(
                        is_array($tenant?->settings) ? $tenant->settings : [],
                        ['source' => 'online_free_registration']
                    ),
                ];

                if ($tenant) {
                    $pending->releaseOpenInvoices($tenant);
                    $t = $pending->markPending($tenant, $attributes);
                } else {
                    $t = $pending->markPending(new Tenant(), $attributes);
                }

                $current = $subAccount->tenant_id ? Tenant::withTrashed()->find($subAccount->tenant_id) : null;
                if (!$current || $current->trashed() || $current->isPendingPayment()) {
                    $subAccount->update(['tenant_id' => $t->id]);
                }

                // Auto-complete initial profile step
                try {
                    app(OnboardingStateService::class)->markCompleted($t->id, 'complete_profile', [
                        'source' => 'online_registration',
                        'user_id' => $user->id,
                    ]);
                } catch (Throwable) {
                }

                $items = $this->resolveInvoiceItems(
                    $package,
                    $months,
                    $package->getFinalPriceForMonths($months)
                );

                $payable = collect($items)->sum(
                    fn(array $item): int => max(0, (int)($item['unit_price'] ?? 0) - (int)($item['discount_amount'] ?? 0))
                );

                if ($payable > 0) {
                    throw new \RuntimeException('Free registration is not allowed for a payable checkout.');
                }

                $billingService = app(ResellerBillingService::class);
                $invoice = $billingService->createInvoice($reseller, [
                    'tenant_id' => $t->id,
                    'package_id' => $package->id,
                    'type' => InvoiceType::OFFICIAL->value,
                    'status' => InvoiceStatus::PAID->value,
                    'paid_at' => now(),
                    'paid_amount' => 0,
                    'payment_method' => 'free_grant',
                    'sub_account_name' => $user->name ?: $user->mobile,
                    'sub_account_mobile' => $user->mobile,
                    'sub_account_email' => $user->email ?? '',
                    'meta' => [
                        'source' => 'portal_free_registration',
                        'source_label' => 'ثبت‌نام مجاز بدون پرداخت',
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'billing_period' => $this->billingPeriod,
                        'domain_type' => $this->domainType,
                        'subdomain' => $this->subdomain,
                        'white_label_type' => $this->whiteLabelType,
                        'brand_title' => $branding['brand_title'] ?? null,
                    ],
                    'items' => $items,
                ], $user);

                $billingService->convertInvoiceToOrder($invoice);

                return $t;
            }, 3);
        } catch (Throwable $e) {
            Log::error('[Checkout] Free registration failed: ' . $e->getMessage(), [
                'reseller_id' => $reseller->id,
                'package_id' => $package->id,
                'user_id' => $user->id,
            ]);

            $this->addError('payment', __('resellerhub::messages.portal.unexpected_error'));
            return;
        }

        // 4. Dispatch provisioning job and redirect to loading screen
        ProvisionTenantJob::dispatch($tenant->id);

        return redirect()->route('portal.onboarding.loading', ['tenant' => $tenant->id]);
    }

    /**
     * Backward compatibility alias for registerFree with strict payment enforcement.
     */
    public function payLater()
    {
        return $this->registerFree();
    }

    public function resolveBaseDomain(): string
    {
        return ResellerHelper::getBaseDomain();
    }

    public static function getColorPresets(): array
    {
        return ResellerHelper::getColorPresets();
    }

    public function render()
    {
        $packages = [];
        $availablePeriods = ResellerHelper::getAllAvailablePeriods();
        $defaultPeriod = 'monthly_price';

        $reseller = $this->resolveReseller();

        if (class_exists(ResellerPackage::class)) {
            $packagesQuery = ResellerPackage::active();
            if ($reseller) {
                $packagesQuery->where('reseller_id', $reseller->id);
            }
            $packages = $packagesQuery->orderBy('monthly_price')->get();
        }

        $baseDomain = $this->resolveBaseDomain();

        $domainService = app(DomainVerificationService::class);
        $serverIp = $domainService->getTargetServerIp() ?? (request()?->server('SERVER_ADDR') ?: '127.0.0.1');
        $targetCname = $domainService->getTargetCname();

        $availableGateways = ResellerHelper::getAvailableGateways();
        if (empty($this->selectedGateway) || !isset($availableGateways[$this->selectedGateway])) {
            $this->selectedGateway = ResellerHelper::getDefaultGateway();
        }

        $selectedPackage = $this->selectedPackageId ? collect($packages)->firstWhere('id', $this->selectedPackageId) : null;

        // Dynamic package periods specifically for selected package
        $packagePeriods = ($selectedPackage && method_exists($selectedPackage, 'getPackagePeriods'))
            ? $selectedPackage->getPackagePeriods()
            : $availablePeriods;

        // Ensure currently selected billing period is active for this package
        if ($selectedPackage && !$selectedPackage->isFree() && (empty($this->billingPeriod) || !$selectedPackage->isPeriodActive($this->billingPeriod))) {
            $active = $selectedPackage->getActivePeriods();
            if (!empty($active)) {
                $this->billingPeriod = $active[0];
            }
        }

        $resellerSettings = $reseller ? ResellerSetting::allForReseller($reseller->id) : [];
        $currency = $resellerSettings['currency'] ?? 'toman';
        $currencyLabel = reseller_currency_label($currency);
        $urlScheme = (request()?->getScheme() ? request()->getScheme() . '://' : 'https://');

        $defaultBrandTitle = $reseller?->brand_title ?: config('app.name', 'CRM');
        $defaultBrandColor = $reseller ? ResellerSetting::get($reseller->id, 'brand_primary_color', '#4f46e5') : '#4f46e5';
        $defaultBrandLogo = $reseller?->brand_logo;
        $defaultBrandFavicon = $reseller?->brand_favicon;
        $whiteLabelFee = $this->getWhiteLabelFee($this->resolveMonths());

        return view('resellerhub::livewire.portal.checkout', [
            'packages' => $packages,
            'availablePeriods' => $availablePeriods,
            'packagePeriods' => $packagePeriods,
            'defaultPeriod' => $defaultPeriod,
            'availableGateways' => $availableGateways,
            'selectedGateway' => $this->selectedGateway,
            'baseDomain' => $baseDomain,
            'serverIp' => $serverIp,
            'targetCname' => $targetCname,
            'selectedPackage' => $selectedPackage,
            'reseller' => $reseller,
            'currency' => $currency,
            'currencyLabel' => $currencyLabel,
            'urlScheme' => $urlScheme,
            'colorPresets' => self::getColorPresets(),
            'defaultBrandTitle' => $defaultBrandTitle,
            'defaultBrandColor' => $defaultBrandColor,
            'defaultBrandLogo' => $defaultBrandLogo,
            'defaultBrandFavicon' => $defaultBrandFavicon,
            'whiteLabelFee' => $whiteLabelFee,
            'isRegistrationWithoutPaymentAllowed' => $this->isRegistrationWithoutPaymentAllowed(),
        ]);
    }
}
