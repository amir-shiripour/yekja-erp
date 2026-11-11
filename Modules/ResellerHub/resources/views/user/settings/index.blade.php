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
        'tour'          => ['label' => 'تور راه‌اندازی', 'icon' => 'flag'],
        'branding'      => ['label' => 'برند و ظاهر', 'icon' => 'sparkles'],
        'finance'       => ['label' => 'مالی و فاکتور', 'icon' => 'coin'],
        'kyc'           => ['label' => 'احراز هویت', 'icon' => 'identification'],
        'automation'    => ['label' => 'اتوماسیون', 'icon' => 'bolt'],
        'tenancy'       => ['label' => 'چندمستاجری', 'icon' => 'shield-check'],
    ];

    $isSuperAdmin = $isSuperAdmin ?? (auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super-admin'));
    if ($isSuperAdmin) {
        $tabs['database'] = ['label' => 'استخراج دیتابیس', 'icon' => 'database'];
    }

    $initialTab = request('tab', session('active_tab', 'general'));
    if ($initialTab === 'pricing') {
        $initialTab = 'finance';
    }
    if ($initialTab === 'database' && !$isSuperAdmin) {
        $initialTab = 'general';
    }
@endphp

@section('content')
    <div x-data="resellerSettingsManager(window.resellerSettingsConfig)"
         class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 relative w-full max-w-full min-w-0"
         style="overflow-x: clip;">

        @include('resellerhub::partials.toast')

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs sm:text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ \Illuminate\Support\Facades\Route::has('reseller.dashboard') ? route('reseller.dashboard') : url('/') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                داشبورد نمایندگی
            </a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 rtl:rotate-180" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 dark:text-white font-bold truncate">تنظیمات سامانه نمایندگی</span>
        </nav>

        {{-- Hero Header --}}
        <x-resellerhub::card>
            <div class="p-3.5 sm:p-6 flex items-center justify-between gap-3 sm:gap-5">
                <div class="flex items-center gap-2.5 sm:gap-4 min-w-0 flex-1">
                    <div
                        class="shrink-0 flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-600 text-white shadow-xs">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-sm sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                            تنظیمات سامانه نمایندگی
                        </h1>
                    </div>
                </div>

                <a href="{{ \Illuminate\Support\Facades\Route::has('reseller.dashboard') ? route('reseller.dashboard') : url('/') }}"
                   class="shrink-0 inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-4 h-8 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-gray-200 border border-slate-200/80 dark:border-gray-600 text-[11px] sm:text-xs font-bold transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="sm:hidden">بازگشت</span>
                    <span class="hidden sm:inline">بازگشت به داشبورد</span>
                </a>
            </div>
        </x-resellerhub::card>

        {{-- Main Settings Form with Tabs --}}
        <form @submit.prevent="saveSettings($el)"
              method="POST"
              action="{{ route('reseller.settings.update') }}"
              enctype="multipart/form-data"
              data-no-spinner="true"
              class="space-y-4 sm:space-y-6 w-full max-w-full min-w-0">
            @csrf

            {{-- Sticky Tab Navigation Bar --}}
            <input type="hidden" name="active_tab" x-model="activeTab">
            <input type="hidden" name="allow_custom_domains" :value="allowCustomDomains ? '1' : '0'">
            @php
                $mobileMoreTabs = array_diff_key($tabs, array_flip(['general', 'finance']));
                $mobileMoreLabels = collect($mobileMoreTabs)->map(fn($t) => $t['label'])->toArray();
            @endphp
            <div class="sticky top-3 sm:top-4 z-30"
                 x-data="{
                    mobileMoreOpen: false,
                    moreLabels: {{ \Illuminate\Support\Js::from($mobileMoreLabels) }},
                    isMoreActive() { return Object.prototype.hasOwnProperty.call(this.moreLabels, activeTab); },
                    moreButtonLabel() { return this.isMoreActive() ? this.moreLabels[activeTab] : 'سایر بخش‌ها'; }
                 }"
                 @click.outside="mobileMoreOpen = false"
                 @keydown.escape.window="mobileMoreOpen = false">

                {{-- Mobile: 3-Segment Compact Bar + More Tabs Dropdown (Matching Orders Show Style) --}}
                <div class="lg:hidden relative">
                    <div
                        class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-1.5 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs grid grid-cols-3 gap-1">
                        <button type="button" id="mobile-tab-general"
                                @click="activeTab = 'general'; mobileMoreOpen = false"
                                :class="activeTab === 'general' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50'"
                                class="flex items-center justify-center gap-1.5 px-2 py-2.5 rounded-xl text-[11px] font-bold transition-colors cursor-pointer whitespace-nowrap truncate">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">عمومی</span>
                        </button>

                        <button type="button" id="mobile-tab-finance"
                                @click="activeTab = 'finance'; mobileMoreOpen = false"
                                :class="activeTab === 'finance' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50'"
                                class="flex items-center justify-center gap-1.5 px-2 py-2.5 rounded-xl text-[11px] font-bold transition-colors cursor-pointer whitespace-nowrap truncate">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">مالی و فاکتور</span>
                        </button>

                        <button type="button" id="mobile-tabs-more-btn"
                                @click="mobileMoreOpen = !mobileMoreOpen"
                                :class="isMoreActive() ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50'"
                                class="flex items-center justify-center gap-1 px-2 py-2.5 rounded-xl text-[11px] font-bold transition-colors cursor-pointer whitespace-nowrap truncate">
                            <span class="truncate" x-text="moreButtonLabel()"></span>
                            <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                                 :class="mobileMoreOpen ? 'rotate-180' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Dropdown Panel for Remaining Tabs --}}
                    <div x-show="mobileMoreOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150 transform"
                         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100 transform"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                         class="absolute inset-x-0 top-full mt-2 z-40 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-lg p-1.5 grid grid-cols-2 gap-1">
                        @foreach($mobileMoreTabs as $tabKey => $tab)
                            <button type="button" id="mobile-tab-{{ $tabKey }}"
                                    @click="activeTab = '{{ $tabKey }}'; mobileMoreOpen = false"
                                    :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200/70 dark:border-indigo-800/60' : 'text-slate-600 dark:text-gray-300 border-transparent hover:bg-slate-50 dark:hover:bg-gray-700/50'"
                                    class="flex items-center gap-2 px-3 py-2.5 rounded-xl border text-xs font-bold transition-colors cursor-pointer text-right">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-600 dark:bg-indigo-400' : 'bg-slate-300 dark:bg-gray-600'"></span>
                                <span class="truncate">{{ $tab['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Desktop: Full Tab Strip --}}
                <div
                    role="tablist" aria-label="تب‌های تنظیمات نمایندگی"
                    class="hidden lg:flex bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-1.5 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs items-center gap-1 overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] w-full max-w-full">
                    @foreach($tabs as $tabKey => $tab)
                        <button type="button" role="tab" id="tab-{{ $tabKey }}" aria-controls="tabpanel-{{ $tabKey }}"
                                :aria-selected="activeTab === '{{ $tabKey }}' ? 'true' : 'false'"
                                @click="activeTab = '{{ $tabKey }}'"
                                :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-white'"
                                class="shrink-0 flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors duration-150 cursor-pointer whitespace-nowrap">
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
                                @case('database')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m0 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/>
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
            <div class="sticky bottom-3 sm:bottom-4 mt-6 max-w-full min-w-0 transition-all duration-200"
                 :class="{ 'z-10': defaultPackageDropdownOpen, 'z-30': !defaultPackageDropdownOpen }"
                 x-show="activeTab !== 'automation' && activeTab !== 'kyc' && activeTab !== 'tenancy' && activeTab !== 'database'">
                <div
                    class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-2.5 sm:p-3.5 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-lg shadow-gray-200/40 dark:shadow-black/20 flex flex-row-reverse items-center justify-between gap-2.5 sm:gap-4 max-w-full min-w-0">
                    <button type="submit"
                            :disabled="saving"
                            data-no-spinner="true"
                            class="flex-1 sm:flex-none h-10 sm:h-11 px-5 sm:px-7 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-xs transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-40 whitespace-nowrap">
                        <template x-if="saving">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>در حال ذخیره‌سازی...</span>
                            </span>
                        </template>
                        <template x-if="!saving">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>ذخیره تنظیمات</span>
                            </span>
                        </template>
                    </button>

                    <a href="{{ \Illuminate\Support\Facades\Route::has('reseller.dashboard') ? route('reseller.dashboard') : url('/') }}"
                       class="h-10 sm:h-11 px-3.5 sm:px-5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-gray-700/80 dark:hover:bg-gray-700 text-xs sm:text-sm font-bold text-slate-600 dark:text-gray-300 inline-flex items-center justify-center transition-colors whitespace-nowrap shrink-0">
                        <span class="sm:hidden">بازگشت</span>
                        <span class="hidden sm:inline">بازگشت به پیشخوان</span>
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

        {{--
            Tab 7: Tenant Database & SQL Data Export (Super Admin Only)
            این تب منحصراً برای سوپرادمین سیستم فعال است و امکان جستجو، فیلتر و دریافت خروجی دیتابیس (SQL Dump و JSON) را می‌دهد.
            جهت جلوگیری از تداخل فرم جستجو با فرم ذخیره تنظیمات، بیرون از <form> رندر می‌شود.
        --}}
        @if($isSuperAdmin)
            @include('resellerhub::user.settings.tabs.tab-database')
        @endif

        {{-- Seed Workflows Confirmation Modal --}}
        @include('resellerhub::user.settings.modals.confirm-seed-modal')
    </div>

    {{-- Shared Helpers & UI Presets --}}
    @include('resellerhub::user.packages.partials.icons-script')

    {{-- Alpine Component Logic --}}
    @include('resellerhub::user.settings.partials.settings-script')
@endsection
