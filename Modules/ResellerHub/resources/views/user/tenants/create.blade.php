@php
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Modules\ResellerHub\App\Services\DomainVerificationService;
    use Morilog\Jalali\Jalalian;
    @endphp

@extends('layouts.user')

@section('title', 'ساخت CRM جدید')

@php
    $resellersListForJs = collect($resellersList ?? [])->values();
    $modulesListForJs = collect($allModulesJson ?? [])->values();
    $defaultDuration = $defaultDuration ?? 12;
    $isSuperAdmin = $isSuperAdmin ?? false;

    $resellerSettings = ResellerSetting::allForReseller($reseller->id);
    $financialSettings = $financialSettings ?? $resellerSettings;
    $currency = $financialSettings['currency'] ?? ($resellerSettings['currency'] ?? ($currency ?? 'toman'));
    $currencyLabel = reseller_currency_label($currency);
    $defaultTaxRate = $defaultTaxRate ?? ($resellerSettings['default_tax_rate'] ?? 10);
    $defaultTaxMode = $defaultTaxMode ?? ($resellerSettings['tax_mode'] ?? 'invoice');
    $modulePricings = $modulePricings ?? [];
    $catalog = $catalog ?? collect([]);
    $invoiceNumbering = [
        'prefix'   => (string)($resellerSettings['invoice_prefix'] ?? 'INV-'),
        'middle'   => (string)($resellerSettings['invoice_middle_prefix'] ?? Jalalian::now()->getYear()),
        'suffix'   => (string)($resellerSettings['invoice_suffix'] ?? ''),
        'padding'  => (int)($resellerSettings['invoice_padding'] ?? 4),
        'auto'     => !empty($resellerSettings['invoice_auto']),
        'tax_mode' => (string)($resellerSettings['tax_mode'] ?? 'invoice'),
        'tax_rate' => (float)($resellerSettings['default_tax_rate'] ?? 10),
        'terms'    => (string)($resellerSettings['invoice_terms'] ?? ''),
        'footer'   => (string)($resellerSettings['invoice_footer_note'] ?? ''),
    ];

    $currentResellerData = [
        'id' => $reseller->id,
        'name' => $reseller->company_name ?: ($reseller->user?->name ?? 'نماینده #' . $reseller->id),
        'brand_title' => $reseller->brand_title ?: ($reseller->user?->name ?? ''),
        'user_email' => $reseller->user?->email ?? '',
        'user_mobile' => $reseller->user?->mobile ?? '',
        'allowed_slugs' => $reseller->allowedModuleSlugs(),
    ];

    $tenantCreatorConfigPayload = [
        'currentReseller' => $currentResellerData,
        'allModules' => $modulesListForJs,
        'defaultDuration' => $defaultDuration,
        'isSuperAdmin' => $isSuperAdmin,
        'packages' => $packages ?? [],
        'subAccounts' => $subAccounts ?? [],
        'packageStoreUrl' => route('reseller.packages.store'),
        'selectedPackageId' => request('package') ? (int)request('package') : null,
        'oldLimits' => old('limits', []),
        'catalog' => $catalog ?? [],
        'modulePricings' => $modulePricings ?? [],
        'financialSettings' => $financialSettings ?? [],
        'enableModuleItemizedPricing' => !empty($financialSettings['enable_module_itemized_pricing']),
        'invoiceNumbering' => $invoiceNumbering ?? [],
        'currencyLabel' => $currencyLabel,
        'defaultTaxRate' => $defaultTaxRate ?? 10,
        'defaultTaxMode' => $defaultTaxMode ?? "invoice",
        'allowsCustomDomains' => ($reseller && method_exists($reseller, 'allowsCustomDomains') && $reseller->allowsCustomDomains()),
        'cnameTarget' => app(DomainVerificationService::class)->getTargetCname(),
        'durationOptions' => \Modules\ResellerHub\App\Helpers\ResellerHelper::durationOptions(),
        'colorPresets' => \Modules\ResellerHub\App\Helpers\ResellerHelper::whiteLabelColorPresets(),
    ];
@endphp

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')
    @include('partials.jalali-date-picker')

    <div x-data="tenantCreator(window.tenantCreatorConfig)"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        @include('resellerhub::partials.toast')

        {{-- Header & Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    <span>ساخت و راه‌اندازی CRM جدید</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    اطلاعات کلینیک یا سازمان خریدار را وارد کنید تا سامانه به صورت آنی و بدون کدنویسی راه‌اندازی شود.
                </p>
            </div>

            <a href="{{ route('reseller.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>بازگشت به داشبورد</span>
            </a>
        </div>

        {{-- Provisioning Info Banner --}}
        <div
            class="bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-4 flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-3 text-sm text-indigo-900 dark:text-indigo-200">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>ظرفیت ساخت سامانه CRM: <strong
                        class="text-base text-indigo-700 dark:text-indigo-300">نامحدود (بدون سقف)</strong></span>
            </div>
            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hidden sm:inline">راه‌اندازی فوری و خودکار بدون نیاز به تایید دستی</span>
        </div>

        {{-- Stepper Progress Indicator --}}
        @include('resellerhub::user.tenants.partials.create-wizard.stepper')

        {{-- Success Screen --}}
        @include('resellerhub::user.tenants.partials.create-wizard.success-screen')

        {{-- Main Wizard Form --}}
        <form x-show="createdTenant === null"
              @submit.prevent="submitForm($el)"
              class="bg-white dark:bg-gray-800/80 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 divide-y divide-gray-100 dark:divide-gray-700/60 backdrop-blur-xl">
            @csrf

            @include('resellerhub::user.tenants.partials.create-wizard.error-alert')
            @include('resellerhub::user.tenants.partials.create-wizard.step-1-identity')
            @include('resellerhub::user.tenants.partials.create-wizard.step-2-packages')
            @include('resellerhub::user.tenants.partials.create-wizard.step-3-financial')
        </form>

        {{-- Modals --}}
        @include('resellerhub::user.tenants.partials.create-wizard.modals.save-package-modal')
        @include('resellerhub::user.tenants.partials.create-wizard.modals.quick-subaccount-modal')
    </div>

    {{-- Alpine Component Logic --}}
    @include('resellerhub::user.tenants.partials.create-wizard.wizard-script')

    @include('partials.jalali-date-picker')
    @include('resellerhub::partials.prevent-double-submit')
@endsection
