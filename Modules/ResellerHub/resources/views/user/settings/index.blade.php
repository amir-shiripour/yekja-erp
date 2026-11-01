@php use Modules\ResellerHub\App\Services\DomainVerificationService; @endphp
@extends('layouts.user')

@section('title', 'تنظیمات سامانه نمایندگی')

@php
    $defaultDuration = $settings['default_plan_duration_months'] ?? 12;
    $autoSuspend = !empty($settings['auto_suspend_expired']);
    $gracePeriod = $settings['grace_period_days'] ?? 7;
    $notifyDays = $settings['notify_days_before_expiry'] ?? 14;
    $notifyEmail = $settings['notification_email'] ?? ($reseller->support_email ?? '');
    $notifyMobile = $settings['notification_mobile'] ?? ($reseller->support_phone ?? '');
    $allowCustomDomain = isset($settings['allow_custom_domains']) ? (bool)$settings['allow_custom_domains'] : true;
    $enableDefaultPackageRegistration = !empty($settings['enable_default_package_registration']);
    $defaultPackageId = $settings['default_package_id'] ?? null;
    $resellerPackages = $resellerPackages ?? collect([]);
    $customDomainYearlyPrice = (int)($settings['custom_domain_yearly_price'] ?? 0);
    $customDomainMonthlyPrice = (int)($settings['custom_domain_monthly_price'] ?? 0);
    $customDomainSetupFee = (int)($settings['custom_domain_setup_fee'] ?? 0);
    $brandPrimaryColor = $settings['brand_primary_color'] ?? '#4f46e5';
    $whiteLabelYearlyPrice = (int)($settings['white_label_yearly_price'] ?? 0);
    $whiteLabelMonthlyPrice = (int)($settings['white_label_monthly_price'] ?? 0);
    $whiteLabelSetupFee = (int)($settings['white_label_setup_fee'] ?? 0);

    $domainService = app(DomainVerificationService::class);
    $cnameTarget = $domainService->getTargetCname();

    $currency = $currency ?? ($settings['currency'] ?? 'toman');
    $currencyLabel = $currencyLabel ?? reseller_currency_label($currency);
    $catalog = $catalog ?? collect([]);

    $tabs = [
        'general'       => ['label' => 'عمومی', 'icon' => 'clock'],
        'tour'          => ['label' => 'تور و راه‌اندازی', 'icon' => 'flag'],
        'branding'      => ['label' => 'برند و وایت‌ لیبل', 'icon' => 'sparkles'],
        'finance'       => ['label' => 'مالی و فاکتور', 'icon' => 'coin'],
        'kyc'           => ['label' => 'احراز هویت زیرمجموعه‌ها', 'icon' => 'identification'],
        'automation'    => ['label' => 'اتوماسیون و گردش کار', 'icon' => 'bolt'],
        'tenancy'       => ['label' => 'چندمستاجری و مدل‌ها', 'icon' => 'shield-check'],
    ];

    $initialTab = request('tab', session('active_tab', 'general'));
    if ($initialTab === 'pricing') {
        $initialTab = 'finance';
    }
@endphp

@section('content')
    <div x-data="resellerSettingsManager(window.resellerSettingsConfig)"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative w-full max-w-full min-w-0"
         style="overflow-x: clip;">

        @include('resellerhub::partials.toast')

        {{-- Hero Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                    </span>
                    <span>تنظیمات سامانه نمایندگی فروش</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    پیکربندی پیش‌فرض‌های لایسنس، برند و وایت‌لیبل اختصاصی، هشدارهای خودکار و ماژول‌های فعال
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ \Illuminate\Support\Facades\Route::has('reseller.dashboard') ? route('reseller.dashboard') : url('/') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>بازگشت به داشبورد</span>
                </a>
            </div>
        </div>

        {{-- Main Settings Form with Tabs --}}
        <form @submit.prevent="saveSettings($el)"
              method="POST"
              action="{{ route('reseller.settings.update') }}"
              enctype="multipart/form-data"
              data-no-spinner="true"
              class="space-y-6 w-full max-w-full min-w-0">
            @csrf

            {{-- Sticky Tab Navigation Bar (Matching DirectAdmin / Projects Style) --}}
            <input type="hidden" name="active_tab" x-model="activeTab">
            <input type="hidden" name="allow_custom_domains" :value="allowCustomDomains ? '1' : '0'">
            <div class="sticky top-4 z-30 mb-6">
                <div
                    role="tablist" aria-label="تب‌های تنظیمات نمایندگی"
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-2 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm flex flex-wrap items-center gap-1.5 w-full max-w-full">
                    @foreach($tabs as $tabKey => $tab)
                        <button type="button" role="tab" id="tab-{{ $tabKey }}" aria-controls="tabpanel-{{ $tabKey }}"
                                :aria-selected="activeTab === '{{ $tabKey }}' ? 'true' : 'false'"
                                @click="activeTab = '{{ $tabKey }}'"
                                :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="flex-1 min-w-[120px] sm:min-w-36 flex items-center justify-center gap-2 px-3 sm:px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer">
                            @switch($tab['icon'])
                                @case('coin')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @break
                                @case('clock')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @break
                                @case('sparkles')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    @break
                                @case('bell')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    @break
                                @case('grid')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    @break
                                @case('identification')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                    </svg>
                                    @break
                                @case('flag')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                    </svg>
                                    @break
                                @case('bolt')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    @break
                                @case('shield-check')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    @break
                            @endswitch
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab 1: General --}}
            @include('resellerhub::user.settings.tabs.tab-general')

            {{-- Tab: Onboarding Tour --}}
            @include('resellerhub::user.settings.tabs.tab-tour')

            {{-- Tab 2: Branding & White-label --}}
            @include('resellerhub::user.settings.tabs.tab-branding')

            {{-- Tab 3: Finance & Invoicing --}}
            @include('resellerhub::user.settings.tabs.tab-finance')

            {{-- Tab 4: KYC & Sub-Account Custom Fields --}}
            @include('resellerhub::user.settings.tabs.tab-kyc')

            {{-- Tab 5: Automation & Workflows --}}
            @include('resellerhub::user.settings.tabs.tab-automation')

            {{-- Sticky Save Action Bar --}}
            <div class="sticky bottom-4 mt-8 max-w-full min-w-0 transition-all duration-200"
                 :class="{ 'z-10': defaultPackageDropdownOpen, 'z-40': !defaultPackageDropdownOpen }"
                 x-show="activeTab !== 'automation' && activeTab !== 'kyc' && activeTab !== 'tenancy'">
                <div
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4 max-w-full min-w-0">
                    <button type="submit"
                            :disabled="saving"
                            data-no-spinner="true"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black shadow-lg shadow-indigo-500/30 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <template x-if="saving">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>در حال ذخیره‌سازی...</span>
                            </span>
                        </template>
                        <template x-if="!saving">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>ذخیره تنظیمات</span>
                            </span>
                        </template>
                    </button>

                    <a href="{{ \Illuminate\Support\Facades\Route::has('reseller.dashboard') ? route('reseller.dashboard') : url('/') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        بازگشت به پیشخوان
                    </a>
                </div>
            </div>
        </form>

        {{--
            Tab 6: Tenancy Dynamic Models & Diagnostics
            این تب شامل فرم‌های مستقل خودش است (افزودن/حذف مدل و همگام‌سازی).
            قرار گرفتن آن داخل فرم اصلی باعث می‌شد مرورگر فرم اصلی را پیش از نوار ذخیره ببندد
            و دکمه «ذخیره تنظیمات» عملاً هیچ درخواستی ارسال نکند؛ بنابراین بیرون از فرم رندر می‌شود.
        --}}
        @include('resellerhub::user.settings.tabs.tab-tenancy')

        {{-- Seed Workflows Confirmation Modal --}}
        @include('resellerhub::user.settings.modals.confirm-seed-modal')
    </div>

    {{-- Shared Helpers & UI Presets --}}
    @include('resellerhub::user.packages.partials.icons-script')

    {{-- Alpine Component Logic --}}
    @include('resellerhub::user.settings.partials.settings-script')
@endsection
