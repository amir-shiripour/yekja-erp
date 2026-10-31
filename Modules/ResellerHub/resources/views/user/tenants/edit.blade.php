@php
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Modules\ResellerHub\App\Models\ResellerSubAccount;
    use Morilog\Jalali\Jalalian;
    @endphp

@extends('layouts.user')

@section('title', 'ویرایش سامانه CRM: ' . $tenant->name)

@php
    $subAccount = $tenant->subAccount ?: ResellerSubAccount::where('tenant_id', $tenant->id)->first();
    $initialActiveModules = $tenant->modules->pluck('module_slug')->toArray();

    $tenantBranding = is_array($tenant->branding) ? $tenant->branding : [];
    $isCustom = !empty($tenantBranding['is_custom']);
    $brandTitle = $tenantBranding['brand_title'] ?? '';
    $brandPrimaryColor = $tenantBranding['brand_primary_color'] ?? ($resellerPrimaryColor ?? '#4f46e5');
    $brandLogoUrl = !empty($tenantBranding['brand_logo']) ? \Illuminate\Support\Facades\Storage::url($tenantBranding['brand_logo']) : null;
    $brandFaviconUrl = !empty($tenantBranding['brand_favicon']) ? \Illuminate\Support\Facades\Storage::url($tenantBranding['brand_favicon']) : null;
    $brandFooterText = $tenantBranding['brand_footer_text'] ?? '';
    $supportPhone = $tenantBranding['support_phone'] ?? '';
    $supportEmail = $tenantBranding['support_email'] ?? '';
    $resellerSettings = ResellerSetting::allForReseller($reseller->id);
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
    $currencyLabel = reseller_currency_label($resellerSettings['currency'] ?? 'toman');

    $initialSelectedFeatures = [];
    foreach ($catalog as $cMod) {
        $cSlug = strtolower(trim($cMod['slug']));
        $cFeatKeys = collect($cMod['features'] ?? [])->pluck('key')->toArray();
        if (!empty($tenantFeatures)) {
            $initialSelectedFeatures[$cSlug] = array_values(array_intersect($cFeatKeys, $tenantFeatures));
        } else {
            $initialSelectedFeatures[$cSlug] = in_array($cSlug, $initialActiveModules, true) ? $cFeatKeys : [];
        }
    }
@endphp

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')
    @include('resellerhub::user.tenants.partials.edit-wizard.edit-script')

    <div x-data="tenantEditor(window.tenantEditorConfig)"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        @include('resellerhub::partials.toast')

        {{-- Header & Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    <span>ویزارد ویرایش سامانه CRM</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    اطلاعات کلینیک یا سازمان «<span
                        class="font-bold text-gray-700 dark:text-gray-200">{{ $tenant->name }}</span>» را ویرایش و به
                    صورت آنی ذخیره کنید.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>مشاهده جزییات سامانه</span>
                </a>

                <a href="{{ route('reseller.dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>داشبورد</span>
                </a>
            </div>
        </div>

        {{-- Stepper Progress Bar --}}
        @include('resellerhub::user.tenants.partials.edit-wizard.stepper')

        {{-- Success State Screen --}}
        @include('resellerhub::user.tenants.partials.edit-wizard.success-screen')

        {{-- Main 3-Step Edit Form --}}
        <form x-show="updatedTenant === null"
              @submit.prevent="submitForm($el)"
              method="POST"
              action="{{ route('reseller.tenants.update', $tenant->id) }}"
              class="bg-white dark:bg-gray-800/80 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 divide-y divide-gray-100 dark:divide-gray-700/60 overflow-hidden backdrop-blur-xl">
            @csrf
            @method('PUT')

            {{-- Marker: the modules step was explicitly submitted, even if all switches are off. --}}
            <input type="hidden" name="modules_present" value="1">

            {{-- General Error Alert --}}
            @include('resellerhub::user.tenants.partials.edit-wizard.error-alert')

            {{-- Step 1: Business Identity & Domains --}}
            @include('resellerhub::user.tenants.partials.edit-wizard.step-1-identity')

            {{-- Step 2: Manager & SubAccount Selection --}}
            @include('resellerhub::user.tenants.partials.edit-wizard.step-2-admin')

            {{-- Step 3: Modules & Final Submission --}}
            @include('resellerhub::user.tenants.partials.edit-wizard.step-3-modules')
        </form>

        {{-- Quick Create Sub-Account Modal --}}
        @include('resellerhub::user.tenants.partials.edit-wizard.modals.quick-subaccount-modal')
    </div>

    @include('resellerhub::partials.prevent-double-submit')
@endsection
