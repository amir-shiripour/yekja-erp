@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;use Modules\ResellerHub\App\Models\ResellerSetting;
    use Modules\ResellerHub\App\Services\DomainVerificationService;
    use Morilog\Jalali\Jalalian;
@endphp

@extends('layouts.user')

@section('title', 'ساخت سامانه جدید')

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
        'selectedSubAccountId' => request('sub_account_id') ? (int)request('sub_account_id') : (old('sub_account_id') ? (int)old('sub_account_id') : null),
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
        'durationOptions' => ResellerHelper::durationOptions(),
        'colorPresets' => ResellerHelper::whiteLabelColorPresets(),
    ];
@endphp

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')
    @include('partials.jalali-date-picker')

    <div x-data="tenantCreator(window.tenantCreatorConfig)"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5 sm:space-y-8 relative">

        @include('resellerhub::partials.toast')

        {{-- Header & Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-5">
            <div class="flex items-start justify-between gap-3 w-full sm:w-auto">
                <div class="min-w-0">
                    <h1 class="text-base sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2.5 sm:gap-4 tracking-tight">
                        <span
                            class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white shadow-sm shrink-0">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <span>ساخت و راه‌اندازی سامانه جدید</span>
                    </h1>
                </div>

                {{-- Mobile Back to Dashboard Button (Top-Left) --}}
                <a href="{{ route('reseller.dashboard') }}"
                   class="sm:hidden inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden xs:inline">بازگشت به </span>
                    <span>داشبورد</span>
                </a>
            </div>

            {{-- Desktop Back to Dashboard Button --}}
            <a href="{{ route('reseller.dashboard') }}"
               class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>بازگشت به داشبورد</span>
            </a>
        </div>

        {{-- Stepper Progress Indicator --}}
        @include('resellerhub::user.tenants.partials.create-wizard.stepper')

        {{-- Success Screen --}}
        @include('resellerhub::user.tenants.partials.create-wizard.success-screen')

        {{-- Main Wizard Form --}}
        <form x-show="createdTenant === null"
              @submit.prevent="submitForm($el)"
              class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700/60 divide-y divide-gray-100 dark:divide-gray-700/60">
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
