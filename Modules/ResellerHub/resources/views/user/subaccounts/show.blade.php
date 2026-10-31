@php use App\Tenancy\TenantManager; @endphp
@extends('layouts.user')

@section('title', 'جزئیات حساب خریدار: ' . $subAccount->name)

@php
    $modulesMap = ($availableModules ?? collect())->keyBy('slug');
    $packagesJson = ($packages ?? collect())->map(function ($p) use ($modulesMap) {
        $pMods = $p->modules ?? [];
        $pFeats = $p->features ?? [];
        $activePeriods = (is_array($p->active_periods) && !empty($p->active_periods))
            ? $p->active_periods
            : ['monthly_price', 'quarterly_price', 'semi_annual_price', 'yearly_price'];

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug ?? null,
            'description' => $p->description ?? '',
            'icon' => $p->icon ?: 'cube',
            'color' => $p->color ?: 'indigo',
            'color_hex' => $p->color_hex,
            'modules' => $pMods,
            'modules_count' => count($pMods),
            'features' => $pFeats,
            'features_count' => count($pFeats),
            'limits' => $p->limits ?? [],

            'price_type' => $p->price_type ?? 'subscription',
            'active_periods' => $activePeriods,

            'monthly_price' => (int)($p->monthly_price ?? 0),
            'quarterly_price' => (int)($p->quarterly_price ?? 0),
            'semi_annual_price' => (int)($p->semi_annual_price ?? 0),
            'yearly_price' => (int)($p->yearly_price ?? 0),
            'one_time_price' => (int)($p->one_time_price ?? 0),
            'setup_fee' => (int)($p->setup_fee ?? 0),

            'final_monthly_price' => (int)($p->final_monthly_price ?? 0),
            'final_quarterly_price' => (int)($p->final_quarterly_price ?? 0),
            'final_semi_annual_price' => (int)($p->final_semi_annual_price ?? 0),
            'final_yearly_price' => (int)($p->final_yearly_price ?? 0),
            'final_one_time_price' => (int)($p->final_one_time_price ?? 0),
            'final_setup_fee' => (int)($p->final_setup_fee ?? 0),

            'discount_type' => $p->discount_type ?? 'percent',
            'discount_value' => (float)($p->discount_value ?? 0),

            'modules_titles' => collect($pMods)->map(fn($s) => $modulesMap[$s]->title ?? $s)->values(),
        ];
    })->values();

    $modulesListForJs = ($availableModules ?? collect())->map(function ($m) {
        return [
            'slug' => $m->slug,
            'name' => $m->name,
            'title' => $m->title,
            'is_allowed' => $m->is_allowed,
        ];
    })->values();

    $resellerData = [
        'id' => $reseller->id,
    ];
@endphp

@section('content')
    @include('partials.jalali-date-picker')
    @include('resellerhub::user.packages.partials.icons-script')

    <script>
        window.subAccountDetailConfig = {
            id: {{ $subAccount->id }},
            name: {!! json_encode($subAccount->name, JSON_UNESCAPED_UNICODE) !!},
            mobile: {!! json_encode($subAccount->mobile ?? '', JSON_UNESCAPED_UNICODE) !!},
            email: {!! json_encode($subAccount->email ?? '', JSON_UNESCAPED_UNICODE) !!},
            company_name: {!! json_encode($subAccount->company_name ?? '', JSON_UNESCAPED_UNICODE) !!},
            status: {!! json_encode($subAccount->status, JSON_UNESCAPED_UNICODE) !!},
            packages: {!! json_encode($packagesJson, JSON_UNESCAPED_UNICODE) !!},
            modules: {!! json_encode($modulesListForJs ?? [], JSON_UNESCAPED_UNICODE) !!},
            catalog: {!! json_encode($catalog ?? [], JSON_UNESCAPED_UNICODE) !!},
            reseller: {!! json_encode($resellerData) !!},
            financialSettings: {!! json_encode($financialSettings ?? []) !!},
            invoiceNumbering: {!! json_encode($invoiceNumbering ?? []) !!},
            currency: {!! json_encode($currency ?? 'toman') !!},
            currencyLabel: {!! json_encode($currencyLabel ?? 'تومان', JSON_UNESCAPED_UNICODE) !!},
            baseDomain: {!! json_encode($baseDomain ?? request()->getHost(), JSON_UNESCAPED_UNICODE) !!},
            defaultDuration: '{{ $defaultDuration ?? 12 }}',
            storeTenantUrl: "{{ route('reseller.tenants.store') }}",
            checkSubdomainUrl: "{{ route('reseller.tenants.check-subdomain') }}",
            csrfToken: '{{ csrf_token() }}'
        };
    </script>

    <div x-data="subAccountDetailManager()"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        @include('resellerhub::partials.toast')

        {{-- Top Navigation & Breadcrumbs --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                <a href="{{ route('reseller.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>پیشخوان نمایندگی</span>
                </a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('reseller.sub-accounts.index') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    مدیریت زیرمجموعه‌ها
                </a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-800 dark:text-gray-200">{{ $subAccount->name }}</span>
            </nav>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('reseller.sub-accounts.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>بازگشت به لیست</span>
                </a>

                <a href="{{ route('reseller.sub-accounts.edit', $subAccount->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 text-amber-600 dark:text-amber-400 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>ویرایش مشخصات</span>
                </a>

                @if($subAccount->tenant)
                    <a href="{{ route('reseller.tenants.show', $subAccount->tenant->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>مدیریت در پنل CRM</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Main Header Card --}}
        <x-resellerhub::card class="p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-indigo-600 text-white font-black text-2xl sm:text-3xl flex items-center justify-center shadow-lg shadow-indigo-600/25 shrink-0">
                        <span>{{ mb_substr($subAccount->name, 0, 1, 'UTF-8') }}</span>
                    </div>
                    <div class="space-y-1.5 min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                                {{ $subAccount->name }}
                            </h1>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $subAccount->status_badge }}">
                                <span
                                    class="w-2 h-2 rounded-full {{ $subAccount->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                <span>{{ $subAccount->status_label }}</span>
                            </span>
                            @if($subAccount->isOnlinePurchase())
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/80 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span>خرید آنلاین از سایت</span>
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>ثبت دستی نماینده</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">
                            {{ $subAccount->company_name ? ('کسب‌وکار: ' . $subAccount->company_name) : 'حساب کاربری خریدار سامانه CRM اختصاصی' }}
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button"
                            @click="showToggleModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer border {{ $subAccount->status === 'active' ? 'bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-400 border-amber-200/60 dark:border-amber-800/50' : 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800/50' }}">
                        @if($subAccount->status === 'active')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>تعلیق موقت حساب</span>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>فعال‌سازی مجدد حساب</span>
                        @endif
                    </button>

                    @if($tenantLoginUrl)
                        <a href="{{ $tenantLoginUrl }}" target="_blank"
                           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                            <span>ورود به سامانه خریدار</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </x-resellerhub::card>

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Metric 1: Account Status --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">وضعیت حساب</span>
                    <p class="text-lg sm:text-xl font-black mt-1.5 {{ $subAccount->status === 'active' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $subAccount->status_label }}
                    </p>
                    <span
                        class="text-xs text-gray-400 block mt-0.5">{{ reseller_fa_num($subAccount->jalali_created_at) }}</span>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl {{ $subAccount->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }} flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Metric 2: CRM Tenant --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">سامانه ابری CRM</span>
                    <p class="text-lg sm:text-xl font-black text-gray-900 dark:text-white mt-1.5 truncate">
                        {{ $subAccount->tenant ? $subAccount->tenant->name : 'فاقد سامانه' }}
                    </p>
                    <span class="text-xs text-gray-400 block mt-0.5 dir-ltr text-start truncate">
                        {{ $subAccount->tenant ? ($subAccount->tenant->custom_domain ?: (request()->getHttpHost() . '/' . $subAccount->tenant->subdomain)) : 'ثبت نشده' }}
                    </span>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Metric 3: License Expiry --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">انقضای لایسنس سامانه</span>
                    <p class="text-lg sm:text-xl font-black text-gray-900 dark:text-white mt-1.5 truncate">
                        {{ $planExpiresAtJalali ? reseller_fa_num($planExpiresAtJalali) : 'نامحدود' }}
                    </p>
                    <span
                        class="text-xs block mt-0.5 {{ !is_null($planDaysRemaining) && $planDaysRemaining <= 7 ? 'text-rose-500 font-bold' : 'text-gray-400' }}">
                        @if(!is_null($planDaysRemaining))
                            {{ $planDaysRemaining > 0 ? (reseller_fa_num($planDaysRemaining) . ' روز باقیمانده') : 'منقضی شده' }}
                        @else
                            اشتراک بدون محدودیت
                        @endif
                    </span>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Metric 4: Active Modules --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">ماژول‌های فعال</span>
                    <p class="text-lg sm:text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1.5">
                        {{ reseller_fa_num(count($tenantModules)) }} ماژول
                    </p>
                    <span class="text-xs text-gray-400 block mt-0.5">سرویس‌های در دسترس</span>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- 2-Column Main Detail Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <div class="space-y-8">
                <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">
                                اطلاعات هویتی و راه‌های ارتباطی خریدار
                            </h2>
                        </div>
                        <span
                            class="text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                            شناسه: #{{ reseller_fa_num($subAccount->id) }}
                        </span>
                    </div>

                    <div class="space-y-4 text-xs sm:text-sm">
                        <div
                            class="flex justify-between items-center p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500 dark:text-gray-400 font-medium">نام و نام خانوادگی خریدار:</span>
                            <span class="font-black text-gray-900 dark:text-white">{{ $subAccount->name }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500 dark:text-gray-400 font-medium">نام کسب‌وکار / کلینیک:</span>
                            <span
                                class="font-bold text-gray-800 dark:text-gray-200">{{ $subAccount->company_name ?: '—' }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 font-medium block">نشانی ایمیل (نام کاربری ورود):</span>
                                <span
                                    class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-start block mt-0.5 truncate max-w-[220px] sm:max-w-xs">
                                    {{ $subAccount->email ?: '—' }}
                                </span>
                            </div>
                            @if($subAccount->email)
                                <button type="button"
                                        @click="copyText('{{ $subAccount->email }}', 'نشانی ایمیل خریدار', 'email')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs"
                                        :class="copiedKey === 'email' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-700'">
                                    <template x-if="copiedKey === 'email'">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="copiedKey !== 'email'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                    <span x-text="copiedKey === 'email' ? 'کپی شد!' : 'کپی ایمیل'"></span>
                                </button>
                            @endif
                        </div>
                        <div
                            class="flex justify-between items-center p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                            <div>
                                <span
                                    class="text-gray-500 dark:text-gray-400 font-medium block">شماره تلفن همراه:</span>
                                <span
                                    class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-start block mt-0.5">
                                    {{ $subAccount->mobile ? reseller_fa_num($subAccount->mobile) : '—' }}
                                </span>
                            </div>
                            @if($subAccount->mobile)
                                <button type="button"
                                        @click="copyText('{{ $subAccount->mobile }}', 'شماره تماس خریدار', 'mobile')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs"
                                        :class="copiedKey === 'mobile' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-700'">
                                    <template x-if="copiedKey === 'mobile'">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="copiedKey !== 'mobile'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                    <span x-text="copiedKey === 'mobile' ? 'کپی شد!' : 'کپی تماس'"></span>
                                </button>
                            @endif
                        </div>

                        @php
                            $initialPassword = $subAccount->settings['initial_password'] ?? null;
                        @endphp
                        @if(false)
                            <div
                                class="flex justify-between items-center p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-900/50">
                                <div>
                                    <span class="text-amber-800 dark:text-amber-300 font-bold text-xs block">رمز عبور پیش‌فرض تولید شده:</span>
                                    <span
                                        class="font-black text-gray-900 dark:text-white dir-ltr text-start block mt-0.5 tracking-wider">{{ $initialPassword }}</span>
                                </div>
                                <button type="button"
                                        @click="copyText('{{ $initialPassword }}', 'رمز عبور خریدار', 'pwd')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs"
                                        :class="copiedKey === 'pwd' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700'">
                                    <template x-if="copiedKey === 'pwd'">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="copiedKey !== 'pwd'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                    <span x-text="copiedKey === 'pwd' ? 'کپی شد!' : 'کپی رمز'"></span>
                                </button>
                            </div>
                        @endif

                        <div
                            class="flex justify-between items-center p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500 dark:text-gray-400 font-medium">تاریخ ثبت حساب:</span>
                            <div class="flex items-center gap-1.5 font-bold text-gray-800 dark:text-gray-200">
                                <span>{{ reseller_fa_num($subAccount->jalali_created_at) }}</span>
                                <span class="text-xs text-gray-400 font-normal">({{ $subAccount->time_ago }})</span>
                            </div>
                        </div>
                        @if($subAccount->reseller)
                            <div
                                class="flex justify-between items-center p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40">
                                <span
                                    class="text-gray-500 dark:text-gray-400 font-medium">نماینده ارائه‌دهنده سامانه:</span>
                                <span class="font-black text-indigo-600 dark:text-indigo-400">
                                    {{ $subAccount->reseller->company_name ?: ($subAccount->reseller->user?->name ?? 'نماینده #' . $subAccount->reseller->id) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </x-resellerhub::card>

                {{-- LIMITS & QUOTAS SUMMARY CARD --}}
                <x-resellerhub::card class="p-6 sm:p-8 space-y-4">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">سقف مصرف و
                                    سهمیه‌های ماژول‌ها</h3>
                                <p class="text-xs text-gray-400">محدودیت‌های تعریف‌شده برای این زیرمجموعه و وضعیت
                                    مصرف جاری</p>
                            </div>
                        </div>

                        <a href="{{ route('reseller.sub-accounts.edit', $subAccount->id) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>ویرایش سهمیه‌ها</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        {{-- Invoices Quota --}}
                        @php
                            $invLimit = $subAccount->getLimit('invoices');
                            $invUsed = $subAccount->invoices()->count();
                            $invRemaining = $subAccount->remainingInvoiceQuota();
                        @endphp
                        <div
                            class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-gray-700 dark:text-gray-300">سقف فاکتورها:</span>
                                @if(!is_null($invLimit))
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-xs font-black {{ $invRemaining > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                        {{ $invRemaining > 0 ? reseller_fa_num($invRemaining) . ' باقیمانده' : 'تکمیل شده' }}
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400">نامحدود</span>
                                @endif
                            </div>
                            <div class="flex items-baseline justify-between text-xs">
                                <span class="text-gray-400 text-xs">مصرف شده:</span>
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ reseller_fa_num($invUsed) }} {{ !is_null($invLimit) ? 'از ' . reseller_fa_num($invLimit) : 'فاکتور' }}</span>
                            </div>
                        </div>

                        {{-- Tenants Quota --}}
                        {{--
                            سقف تعداد سامانه‌های CRM (لیمیت فروش) برای زیرمجموعه حذف شده است؛
                            این کارت دیگر نمایش داده نمی‌شود.
                        --}}

                        {{-- Other Module Limits if set --}}
                        @if(!empty($subAccount->limits))
                            @foreach($subAccount->limits as $mLimitKey => $mLimitVal)
                                @if(!in_array($mLimitKey, ['invoices', 'tenants']) && !empty($mLimitVal))
                                    <div
                                        class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold text-gray-700 dark:text-gray-300 truncate">ماژول {{ $mLimitKey }}:</span>
                                            <span
                                                class="px-2 py-0.5 rounded-lg text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400">
                                                حداکثر {{ reseller_fa_num($mLimitVal) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-400 truncate">
                                            سقف مصرف تعیین‌شده
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </x-resellerhub::card>

                @php
                    $tenantList = $allTenants ?? collect($subAccount->tenant ? [$subAccount->tenant] : []);
                @endphp
                <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">
                                سامانه‌های CRM متصل به این زیرمجموعه
                            </h2>
                            <span
                                class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                {{ reseller_fa_num($tenantList->count()) }} سامانه
                            </span>
                        </div>

                        <button type="button" @click="openQuickProvisionForSub()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-sm transition cursor-pointer active:scale-95">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>افزودن CRM جدید</span>
                        </button>
                    </div>

                    @if($tenantList->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($tenantList as $t)
                                @php
                                    $tLoginUrl = app(TenantManager::class)->getTenantUrl($t, 'login');
                                    $tDisplayUrl = app(TenantManager::class)->getTenantUrl($t);
                                    $tExpiresJalali = $t->plan_expires_at ? \Morilog\Jalali\Jalalian::fromDateTime($t->plan_expires_at)->format('Y/m/d') : null;
                                    $tDaysRemaining = $t->plan_expires_at ? now()->diffInDays($t->plan_expires_at, false) : null;
                                @endphp
                                <div
                                    class="p-4 sm:p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 space-y-3.5">
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-200/60 dark:border-gray-700/60">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm shrink-0">
                                                <span>{{ mb_substr($t->name, 0, 1, 'UTF-8') }}</span>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ $t->name }}</h3>
                                                <span
                                                    class="text-xs text-indigo-600 dark:text-indigo-400 dir-ltr text-start block mt-0.5">{{ $t->subdomain }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs font-bold px-2.5 py-1 rounded-full {{ $t->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/60' }}">
                                                {{ $t->status === 'active' ? 'فعال و برخط' : ($t->status === 'suspended' ? 'معلق' : 'منقضی') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-400">اعتبار لایسنس:</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr">
                                                {{ $tExpiresJalali ? reseller_fa_num($tExpiresJalali) : 'بدون انقضا' }}
                                            </span>
                                            @if(!is_null($tDaysRemaining))
                                                <span
                                                    class="text-xs px-2 py-0.5 rounded font-bold {{ $tDaysRemaining > 7 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                                    {{ $tDaysRemaining > 0 ? (reseller_fa_num($tDaysRemaining) . ' روز مانده') : 'منقضی شده' }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button"
                                                    @click="copyText('{{ $tLoginUrl }}', 'لینک ورود خریدار به CRM', 'tlogin_{{ $t->id }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold transition cursor-pointer shadow-2xs"
                                                    :class="copiedKey === 'tlogin_{{ $t->id }}' ? 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-300' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 text-gray-700 dark:text-gray-200'">
                                                <template x-if="copiedKey === 'tlogin_{{ $t->id }}'">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </template>
                                                <template x-if="copiedKey !== 'tlogin_{{ $t->id }}'">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </template>
                                                <span
                                                    x-text="copiedKey === 'tlogin_{{ $t->id }}' ? 'کپی شد!' : 'کپی لینک ورود'"></span>
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('reseller.tenants.login-as', $t->id) }}"
                                                  target="_blank" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 text-xs font-bold transition">
                                                    <span>ورود مستقیم</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            <a href="{{ route('reseller.tenants.show', $t->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition">
                                                <span>مدیریت در پنل</span>
                                                <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-10 text-center space-y-3">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 mx-auto flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-gray-800 dark:text-gray-200">فاقد سامانه CRM فعال</h3>
                            <p class="text-xs text-gray-400 max-w-xs mx-auto">
                                برای این زیرمجموعه هنوز سامانه CRM متصلی ساخته نشده است.
                            </p>
                            <div class="pt-3">
                                <button type="button" @click="openQuickProvisionForSub()"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 transition cursor-pointer active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>ساخت اولین سامانه CRM برای این خریدار</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </x-resellerhub::card>
            </div>

            <div class="space-y-8">
                <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">
                                ماژول‌ها و امکانات فعال در سامانه
                            </h2>
                        </div>
                        <span
                            class="text-xs font-black px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                            {{ reseller_fa_num(count($tenantModules)) }} قابلیت فعال
                        </span>
                    </div>

                    @if(!empty($tenantModules))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($tenantModules as $mod)
                                <div
                                    class="p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <span
                                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block truncate">
                                            {{ $mod['title'] }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-400 block dir-ltr text-start truncate mt-0.5">
                                            {{ $mod['slug'] }}
                                        </span>
                                    </div>
                                    <span
                                        class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-gray-400 text-xs">
                            <p>هیچ ماژول یا قابلیت جداگانه‌ای برای این سامانه ثبت نشده است.</p>
                        </div>
                    @endif
                </x-resellerhub::card>
                <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">
                                فاکتورهای ثبت‌شده خریدار
                            </h2>
                        </div>
                        <a href="{{ route('reseller.invoices.index', ['search' => $subAccount->email ?: $subAccount->name]) }}"
                           class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            مشاهده تمام فاکتورها
                        </a>
                    </div>

                    @if($invoices->count() > 0)
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full text-xs text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                                <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                                <tr>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-start">شماره
                                        فاکتور
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-center">
                                        مبلغ
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-center">
                                        وضعیت
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-center">
                                        تاریخ
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-end">عملیات
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($invoices as $invoice)
                                    @php
                                        $badge = $invoice->status_badge;
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 px-3">
                                            <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                                               class="font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ reseller_fa_num($invoice->invoice_number) }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-gray-800 dark:text-gray-200">
                                            {{ reseller_fa_num(number_format($invoice->total)) }}
                                            <span class="text-xs text-gray-400">{{ $currencyLabel }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badge['bg'] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] ?? 'bg-gray-400' }}"></span>
                                                <span>{{ $invoice->status_label }}</span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center text-gray-500 dark:text-gray-400">
                                            {{ reseller_fa_num($invoice->jalali_issue_date ?? $invoice->issue_date?->format('Y/m/d')) }}
                                        </td>
                                        <td class="py-3 px-3 text-end">
                                            <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                                               class="p-1.5 rounded-lg bg-gray-100 hover:bg-indigo-50 dark:bg-gray-700 dark:hover:bg-indigo-950/60 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center transition"
                                               title="مشاهده فاکتور">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-6 text-center text-gray-400 text-xs">
                            <p>هنوز فاکتوری برای این خریدار ثبت نشده است.</p>
                        </div>
                    @endif
                </x-resellerhub::card>
                <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">
                                سفارشات ثبت‌شده خریدار
                            </h2>
                        </div>
                        <a href="{{ route('reseller.orders.index', ['search' => $subAccount->email ?: $subAccount->name]) }}"
                           class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            مشاهده تمام سفارشات
                        </a>
                    </div>

                    @if($orders->count() > 0)
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full text-xs text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                                <thead class="bg-gray-50/70 dark:bg-gray-900/40">
                                <tr>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-start">
                                        سفارش
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-center">
                                        دوره
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-center">
                                        مبلغ
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-center">
                                        وضعیت
                                    </th>
                                    <th scope="col" class="py-2.5 px-3 font-bold text-gray-500 dark:text-gray-400 text-end">عملیات
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($orders as $order)
                                    @php
                                        $oBadge = $order->status_badge;
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 px-3">
                                            <a href="{{ route('reseller.orders.show', $order->id) }}"
                                               class="font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                                #{{ reseller_fa_num($order->order_number ?: $order->id) }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-3 text-center text-gray-600 dark:text-gray-300 font-medium">
                                            {{ reseller_duration_label($order->billing_cycle) }}
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-gray-800 dark:text-gray-200">
                                            {{ reseller_fa_num(number_format($order->total_amount)) }}
                                            <span class="text-xs text-gray-400">{{ $currencyLabel }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $oBadge['bg'] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full {{ $oBadge['dot'] ?? 'bg-gray-400' }}"></span>
                                                <span>{{ $order->status_label }}</span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-end">
                                            <a href="{{ route('reseller.orders.show', $order->id) }}"
                                               class="p-1.5 rounded-lg bg-gray-100 hover:bg-indigo-50 dark:bg-gray-700 dark:hover:bg-indigo-950/60 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center transition"
                                               title="مشاهده سفارش">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-6 text-center text-gray-400 text-xs">
                            <p>هنوز سفارشی برای این خریدار ثبت نشده است.</p>
                        </div>
                    @endif
                </x-resellerhub::card>
            </div>
        </div>

        {{-- Toggle Status Confirmation Modal --}}
        <div x-show="showToggleModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             @keydown.escape.window="!isToggling && (showToggleModal = false)">

            {{-- Backdrop --}}
            <div x-show="showToggleModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-950/80 backdrop-blur-md transition-opacity"
                 @click="!isToggling && (showToggleModal = false)"></div>

            {{-- Modal Dialog Box --}}
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div x-show="showToggleModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                     @click.stop="$event.stopPropagation()"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 shadow-2xl transition-all max-w-lg w-full border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-center">

                    {{-- Dynamic Icon Badge --}}
                    <div
                        class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center shadow-xl transition-all duration-300"
                        :class="status === 'active' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 shadow-rose-500/20' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-emerald-500/20'">
                        <template x-if="status === 'active'">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                            </svg>
                        </template>
                        <template x-if="status !== 'active'">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </template>
                    </div>

                    {{-- Title & Body Description --}}
                    <div class="space-y-2">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white"
                            x-text="status === 'active' ? ('تعلیق دسترسی «' + name + '»') : ('فعال‌سازی مجدد حساب «' + name + '»')"></h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed"
                           x-text="status === 'active' ? 'با تعلیق حساب، دسترسی اولیه ورود خریدار و مدیر این زیرمجموعه موقتاً مسدود شده و امکان ورود به CRM غیرفعال خواهد شد.' : 'با رفع تعلیق، دسترسی کامل ورود خریدار به سامانه CRM اختصاصی بلافاصله فعال می‌گردد.'"></p>
                    </div>

                    {{-- Summary Preview Card --}}
                    <div
                        class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs space-y-2.5 text-start">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">نام خریدار / زیرمجموعه:</span>
                            <span class="font-black text-gray-800 dark:text-gray-200" x-text="name"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">وضعیت کنونی:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                  :class="status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400'"
                                  x-text="status === 'active' ? 'حساب فعال' : 'حساب معلق'"></span>
                        </div>
                    </div>

                    {{-- Modal Action Buttons --}}
                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button type="button" @click="showToggleModal = false" :disabled="isToggling"
                                class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-bold transition-all w-1/2 flex items-center justify-center cursor-pointer disabled:opacity-50">
                            انصراف
                        </button>
                        <button type="button" @click="executeToggleStatus()" :disabled="isToggling"
                                :class="status === 'active' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/25' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/25'"
                                class="px-5 py-3 rounded-2xl text-white text-sm font-black transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 w-1/2 disabled:opacity-50 cursor-pointer">
                            <template x-if="isToggling">
                                <svg class="animate-spin w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span
                                x-text="isToggling ? 'در حال پردازش...' : (status === 'active' ? 'تایید و تعلیق حساب' : 'تایید و فعال‌سازی')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Provision CRM Modal (inside x-data root) --}}
        @include('resellerhub::user.subaccounts.partials.quick-provision-modal')

    </div>

    <script>
        function subAccountDetailManager() {
            const config = window.subAccountDetailConfig || {};
            return {
                id: config.id,
                name: config.name,
                mobile: config.mobile || '',
                email: config.email || '',
                status: config.status,

                showToggleModal: false,
                isToggling: false,
                copiedKey: null,

                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                copyText(text, label = 'متن', key = null) {
                    if (!text) {
                        this.showToast('مقداری جهت کپی وجود ندارد.', 'error');
                        return;
                    }

                    const successMsg = label + ' با موفقیت کپی شد.';

                    const onCopySuccess = () => {
                        if (key) {
                            this.copiedKey = key;
                            setTimeout(() => {
                                if (this.copiedKey === key) {
                                    this.copiedKey = null;
                                }
                            }, 2200);
                        }
                        this.showToast(successMsg, 'success');
                    };

                    const fallbackCopy = (val) => {
                        try {
                            const textArea = document.createElement('textarea');
                            textArea.value = val;
                            textArea.style.position = 'fixed';
                            textArea.style.left = '-999999px';
                            textArea.style.top = '-999999px';
                            textArea.style.opacity = '0';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            const successful = document.execCommand('copy');
                            document.body.removeChild(textArea);
                            if (successful) {
                                onCopySuccess();
                            } else {
                                this.showToast('کپی با خطا مواجه شد.', 'error');
                            }
                        } catch (err) {
                            this.showToast('دسترسی به کلیپ‌بورد مقدور نیست.', 'error');
                        }
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(() => {
                            onCopySuccess();
                        }).catch(() => {
                            fallbackCopy(text);
                        });
                    } else {
                        fallbackCopy(text);
                    }
                },

                async executeToggleStatus() {
                    if (this.isToggling) return;
                    this.isToggling = true;

                    try {
                        const res = await fetch(`{{ url('/reseller/sub-accounts') }}/${this.id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.status = data.new_status;
                            this.showToggleModal = false;
                            setTimeout(() => {
                                window.location.reload();
                            }, 600);
                        } else {
                            this.showToast(data.message || 'خطا در تغییر وضعیت.', 'error');
                        }
                    } catch (e) {
                        this.showToast('خطای ارتباط با سرور هنگام ثبت تغییرات.', 'error');
                    } finally {
                        this.isToggling = false;
                    }
                },

                // ==================== Quick Provision Modal State & Methods ====================
                quickProvisionModalOpen: false,
                packagesList: config.packages || [],
                selectedPackage: null,
                resellerData: config.reseller || {},
                modules: config.modules || [],
                catalog: config.catalog || [],
                baseDomain: config.baseDomain || window.location.hostname,
                defaultDuration: config.defaultDuration || '12',
                storeTenantUrl: config.storeTenantUrl || '',
                checkSubdomainUrl: config.checkSubdomainUrl || '',
                csrfToken: config.csrfToken || '',

                cleanBaseDomain() {
                    let d = this.baseDomain || (typeof window !== 'undefined' ? window.location.hostname : '');
                    return d ? d.replace(/:\d+$/, '') : '';
                },

                durationList: window.resellerStandardDurations || [],

                get durationOptions() {
                    const fn = this.resolvePackageDurationOptions || window.resolvePackageDurationOptions;
                    if (fn) {
                        return fn(this.selectedPackage, {
                            fallback: this.durationList,
                            oneTimeValue: '1200',
                            oneTimeLabel: 'مادام‌العمر (یک‌باره)',
                        });
                    }
                    return this.durationList;
                },

                get durationLabel() {
                    const opt = this.durationOptions.find(o => String(o.value) === String(this.form.expires_in_months));
                    return opt ? opt.label : (this.faNum(this.form.expires_in_months) + ' ماهه');
                },

                getDurationLabel(months) {
                    const opt = this.durationOptions.find(o => String(o.value) === String(months));
                    return opt ? opt.label : (this.faNum(months) + ' ماهه');
                },

                ...(window.resellerUiHelpers ? window.resellerUiHelpers() : {}),

                formatPrice(num) {
                    if (!num && num !== 0) return '۰';
                    return this.formatAmount ? this.formatAmount(num) : num;
                },

                selectPackageDuration(val) {
                    this.form.expires_in_months = String(val);
                    this.syncQuickInvoiceItems();
                },

                form: {
                    name: '',
                    subdomain: '',
                    package_id: null,
                    reseller_id: (config.reseller && config.reseller.id) ? config.reseller.id : '',
                    sub_account_id: config.id,
                    admin_name: config.name || '',
                    admin_mobile: config.mobile || '',
                    admin_email: config.email || '',
                    expires_in_months: '12',
                    modules: [],
                    issue_invoice: true,
                    invoice_status: 'unpaid'
                },

                subdomainChecking: false,
                subdomainStatus: 'idle',
                subdomainMsg: '',
                subdomainTimer: null,
                subdomainManuallyEdited: false,

                // Invoice Management State
                invoiceNumbering: config.invoiceNumbering || {},
                currencyLabel: config.currencyLabel || 'تومان',
                invoiceNumberAuto: Boolean(config.invoiceNumbering && config.invoiceNumbering.auto),
                invoiceNumberOverride: '',
                taxRateLocked: true,
                invoiceTaxRate: parseFloat((config.invoiceNumbering && config.invoiceNumbering.tax_rate) || 0),
                invoiceIssueDate: '',
                invoiceDueDate: '',
                invoiceTerms: (config.invoiceNumbering && config.invoiceNumbering.terms) || '',
                invoiceTermsLocked: true,
                invoiceNotes: (config.invoiceNumbering && config.invoiceNumbering.footer) || '',
                invoiceDiscountAmount: 0,
                invoiceDiscountDisplay: '',
                invoiceItems: [],
                quickInvoiceItems: [],

                submitting: false,
                generalError: '',
                formErrors: {},
                createdTenant: null,
                createdInvoice: null,
                createdOrder: null,
                copiedUrl: false,
                copiedCreds: false,

                openQuickProvisionForSub() {
                    this.formErrors = {};
                    this.generalError = '';
                    this.createdTenant = null;
                    this.createdInvoice = null;
                    this.createdOrder = null;
                    this.copiedUrl = false;
                    this.copiedCreds = false;
                    this.subdomainStatus = 'idle';
                    this.subdomainMsg = '';
                    this.subdomainManuallyEdited = false;

                    this.form.admin_name = config.name || '';
                    this.form.admin_mobile = config.mobile || '';
                    this.form.admin_email = config.email || '';
                    this.form.sub_account_id = config.id;
                    this.form.reseller_id = (config.reseller && config.reseller.id) ? config.reseller.id : '';
                    this.form.name = config.name ? (config.name + ' CRM') : '';
                    this.form.subdomain = '';
                    this.form.issue_invoice = true;
                    this.form.invoice_status = 'unpaid';

                    // Select first package by default if available
                    if (this.packagesList.length > 0) {
                        this.selectPackage(this.packagesList[0]);
                    }

                    // Reset Invoice fields
                    this.invoiceNumberAuto = Boolean(this.invoiceNumbering && this.invoiceNumbering.auto);
                    this.invoiceNumberOverride = '';
                    this.taxRateLocked = true;
                    this.invoiceTaxRate = parseFloat((this.invoiceNumbering && this.invoiceNumbering.tax_rate) || 0);
                    this.invoiceTermsLocked = true;
                    this.invoiceTerms = (this.invoiceNumbering && this.invoiceNumbering.terms) || '';
                    this.invoiceNotes = (this.invoiceNumbering && this.invoiceNumbering.footer) || '';
                    this.invoiceDiscountAmount = 0;
                    this.invoiceDiscountDisplay = '';
                    this.invoiceItems = [];

                    this.setTodayIssueDate();
                    this.invoiceDueDate = this.addDaysToJalaliToday(30);

                    this.syncQuickInvoiceItems();
                    this.autoSuggestSubdomain();

                    this.quickProvisionModalOpen = true;

                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            window.jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                            });
                        }
                    });
                },

                closeQuickProvision() {
                    if (this.submitting) return;
                    this.quickProvisionModalOpen = false;
                    this.createdTenant = null;
                    this.createdInvoice = null;
                    this.createdOrder = null;
                },

                selectPackage(pkg) {
                    this.selectedPackage = pkg;
                    this.form.package_id = pkg.id;
                    this.form.modules = Array.isArray(pkg.modules) ? [...pkg.modules] : [];

                    const priceType = pkg.price_type || 'subscription';
                    if (priceType === 'free') {
                        this.form.issue_invoice = false;
                        this.form.expires_in_months = '12';
                    } else if (priceType === 'one_time') {
                        this.form.issue_invoice = true;
                        this.form.expires_in_months = '1200';
                    } else {
                        this.form.issue_invoice = true;
                        const availableDurations = this.durationOptions;
                        const preferred = availableDurations.find(d => String(d.value) === String(this.defaultDuration));
                        this.form.expires_in_months = preferred
                            ? preferred.value
                            : (availableDurations[0] ? availableDurations[0].value : '12');
                    }

                    this.syncQuickInvoiceItems();
                },

                toggleInvoiceNumberAuto() {
                    this.invoiceNumberAuto = !this.invoiceNumberAuto;
                },

                get invoiceNumberPreview() {
                    const cfg = this.invoiceNumbering || {};
                    const pad = parseInt(cfg.padding) || 4;
                    const num = String(1).padStart(pad, '0');
                    const mid = cfg.middle ? (cfg.middle + '-') : '';
                    return (cfg.prefix || '') + mid + num + (cfg.suffix || '');
                },

                toggleTaxRateLock() {
                    this.taxRateLocked = !this.taxRateLocked;
                    if (this.taxRateLocked) {
                        this.invoiceTaxRate = parseFloat((this.invoiceNumbering && this.invoiceNumbering.tax_rate) || 0);
                    }
                },

                setTodayIssueDate() {
                    const formatter = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                    const parts = formatter.format(new Date()).split('/');
                    if (parts.length === 3) {
                        this.invoiceIssueDate = parts[0] + '/' + parts[1] + '/' + parts[2];
                    }
                },

                addDaysToJalaliToday(days) {
                    const d = new Date();
                    d.setDate(d.getDate() + days);
                    const formatter = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                    const parts = formatter.format(d).split('/');
                    return (parts.length === 3) ? (parts[0] + '/' + parts[1] + '/' + parts[2]) : '';
                },

                autoSuggestSubdomain() {
                    if (this.createdTenant || this.subdomainManuallyEdited) return;
                    const name = (this.form.name || '').trim();
                    if (!name) return;
                    const fn = this.suggestSubdomain || window.suggestSubdomain;
                    this.form.subdomain = fn ? fn(name) : '';
                    this.onSubdomainInput();
                },

                onSubdomainInput() {
                    this.subdomainManuallyEdited = true;
                    let val = (this.form.subdomain || '').toLowerCase().trim();
                    val = val.replace(/[^a-z0-9-]/g, '');
                    this.form.subdomain = val;

                    clearTimeout(this.subdomainTimer);
                    if (!val) {
                        this.subdomainStatus = 'idle';
                        this.subdomainMsg = '';
                        return;
                    }
                    if (val.length < 3) {
                        this.subdomainStatus = 'unavailable';
                        this.subdomainMsg = 'حداقل ۳ کاراکتر الزامی است.';
                        return;
                    }

                    this.subdomainChecking = true;
                    this.subdomainTimer = setTimeout(() => this.checkSubdomainAvailability(val), 400);
                },

                async checkSubdomainAvailability(subdomain) {
                    try {
                        const res = await fetch(`${this.checkSubdomainUrl}?subdomain=${encodeURIComponent(subdomain)}`, {
                            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                        });
                        const data = await res.json();
                        this.subdomainChecking = false;
                        if (data.available) {
                            this.subdomainStatus = 'available';
                            this.subdomainMsg = 'زیردامنه آزاد و قابل استفاده است.';
                        } else {
                            this.subdomainStatus = 'unavailable';
                            this.subdomainMsg = data.message || 'این زیردامنه قبلاً ثبت شده است.';
                        }
                    } catch (e) {
                        this.subdomainChecking = false;
                        this.subdomainStatus = 'idle';
                    }
                },

                getPackageDurationPrice(pkg, months) {
                    if (!pkg) return 0;
                    const priceType = pkg.price_type || 'subscription';
                    if (priceType === 'free') return 0;
                    if (priceType === 'one_time') return parseInt(pkg.one_time_price) || 0;

                    months = parseInt(months, 10) || 12;

                    // Dynamic period_prices support
                    if (pkg.period_prices && typeof pkg.period_prices === 'object') {
                        const directPrice = parseInt(pkg.period_prices[months] || pkg.period_prices[String(months)]) || 0;
                        if (directPrice > 0) {
                            return directPrice;
                        }
                    }

                    const mPrice = parseInt(pkg.monthly_price) || 0;
                    const qPrice = parseInt(pkg.quarterly_price) || 0;
                    const sPrice = parseInt(pkg.semi_annual_price) || 0;
                    const yPrice = parseInt(pkg.yearly_price) || 0;
                    const yearlyBase = yPrice > 0 ? yPrice : (mPrice * 12);

                    if (months === 1) return mPrice;
                    if (months === 3) return qPrice > 0 ? qPrice : Math.round((yearlyBase / 12) * 3);
                    if (months === 6) return sPrice > 0 ? sPrice : Math.round((yearlyBase / 12) * 6);
                    if (months === 12) return yearlyBase;
                    if (months === 24) return yearlyBase * 2;
                    if (months === 36) return yearlyBase * 3;
                    return Math.round((yearlyBase / 12) * months);
                },

                getPackagePriceForDuration(durationMonths = null) {
                    const pkg = this.selectedPackage;
                    const months = (durationMonths !== null && durationMonths !== undefined)
                        ? durationMonths : this.form.expires_in_months;
                    return this.getPackageDurationPrice(pkg, months);
                },

                getPackageSetupFee() {
                    const pkg = this.selectedPackage;
                    if (pkg && pkg.price_type === 'free') return 0;
                    return parseInt(pkg && pkg.setup_fee) || 0;
                },

                getActivePackageDiscount() {
                    const pkg = this.selectedPackage;
                    if (!pkg || pkg.price_type === 'free') return 0;
                    const basePrice = this.getPackagePriceForDuration();
                    const type = pkg.discount_type || 'percent';
                    const value = parseFloat(pkg.discount_value) || 0;
                    if (value <= 0 || basePrice <= 0) return 0;
                    if (type === 'amount') return Math.min(basePrice, Math.round(value));
                    return Math.round(basePrice * (Math.min(value, 100) / 100));
                },

                getPackageTotalAmount() {
                    const base = this.getPackagePriceForDuration() + this.getPackageSetupFee();
                    const discount = this.getActivePackageDiscount();
                    return Math.max(0, base - discount);
                },

                addCustomInvoiceItem() {
                    this.invoiceItems.push({
                        description: '',
                        item_type: 'custom',
                        item_id: 'custom_' + Date.now(),
                        duration_months: 0,
                        unit_price: 0,
                        quantity: 1,
                        discount_amount: 0,
                        tax_rate: 0
                    });
                },

                removeInvoiceItem(idx) {
                    if (this.invoiceItems[idx] && this.invoiceItems[idx].item_type === 'custom') {
                        this.invoiceItems.splice(idx, 1);
                    }
                },

                formatPriceInput(val) {
                    if (val === null || val === undefined || val === '') return '';
                    const n = parseInt(String(val).replace(/[^0-9]/g, '')) || 0;
                    return n.toLocaleString('en-US');
                },

                parsePriceInput(val) {
                    return window.parsePriceInput ? window.parsePriceInput(val) : (parseInt(String(val).replace(/[^0-9]/g, '')) || 0);
                },

                syncQuickInvoiceItems() {
                    const pkg = this.selectedPackage;
                    if (!pkg) return;

                    const customItems = (this.invoiceItems || []).filter(it => it.item_type === 'custom');

                    const items = [];
                    const isOneTime = pkg.price_type === 'one_time';
                    const months = isOneTime ? 0 : (parseInt(this.form.expires_in_months, 10) || 12);
                    const basePrice = this.getPackagePriceForDuration(months);
                    const discountAmount = this.getActivePackageDiscount();
                    const durationText = isOneTime ? 'مادام‌العمر' : this.getDurationLabel(months);
                    const pkgMods = pkg.modules || [];
                    const pkgFeats = pkg.features || [];

                    const desc = isOneTime
                        ? `خرید مادام‌العمر پکیج «${pkg.name || ''}» (شامل ${this.faNum(pkgMods.length)} ماژول و ${this.faNum(pkgFeats.length)} قابلیت)`
                        : `اشتراک ${durationText} پکیج «${pkg.name || ''}» (شامل ${this.faNum(pkgMods.length)} ماژول و ${this.faNum(pkgFeats.length)} قابلیت)`;

                    items.push({
                        description: desc,
                        item_type: 'package',
                        item_id: 'package_' + pkg.id,
                        duration_months: months,
                        unit_price: basePrice,
                        quantity: 1,
                        discount_amount: discountAmount,
                        tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                        is_bundled: true,
                        is_extra: false,
                    });

                    const setupFee = this.getPackageSetupFee();
                    if (setupFee > 0) {
                        items.push({
                            description: `هزینه راه‌اندازی و کانفیگ اولیه پکیج «${pkg.name || ''}»`,
                            item_type: 'setup_fee',
                            item_id: 'package_' + pkg.id + '_setup',
                            duration_months: 0,
                            unit_price: setupFee,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                            is_bundled: true,
                            is_extra: false,
                        });
                    }
                    this.invoiceItems = [...items, ...customItems];
                },

                getItemRowTotal(item) {
                    const qty = Math.max(1, parseFloat(item.quantity) || 1);
                    const price = Math.max(0, parseInt(item.unit_price) || 0);
                    const disc = Math.max(0, parseInt(item.discount_amount) || 0);
                    return Math.max(0, (qty * price) - disc);
                },

                getInvoiceSubtotal() {
                    return (this.invoiceItems || []).reduce((sum, it) => sum + this.getItemRowTotal(it), 0);
                },

                getInvoiceTax() {
                    const subtotal = this.getInvoiceSubtotal();
                    const rate = parseFloat(this.invoiceTaxRate) || 0;
                    return Math.round((subtotal * rate) / 100);
                },

                getInvoiceDiscount() {
                    return Math.max(0, parseInt(this.invoiceDiscountAmount) || 0);
                },

                getInvoiceTotal() {
                    const sub = this.getInvoiceSubtotal();
                    const tax = this.getInvoiceTax();
                    const disc = this.getInvoiceDiscount();
                    return Math.max(0, (sub + tax) - disc);
                },

                handleInvoiceDiscountInput(e) {
                    const parsed = this.parsePriceInput(e.target.value);
                    this.invoiceDiscountAmount = parsed;
                    this.invoiceDiscountDisplay = parsed > 0 ? this.formatPriceInput(parsed) : '';
                },

                updateInvoiceDiscount() {
                    const maxAllowed = this.getInvoiceSubtotal() + this.getInvoiceTax();
                    this.invoiceDiscountAmount = Math.min(Math.max(0, this.invoiceDiscountAmount), maxAllowed);
                    this.invoiceDiscountDisplay = this.invoiceDiscountAmount > 0 ? this.formatPriceInput(this.invoiceDiscountAmount) : '';
                },

                getModuleTitle(slug) {
                    const found = (this.modules || []).find(m => m.slug === slug);
                    return found ? (found.title || found.name || slug) : slug;
                },

                getModuleFeatureCount(slug) {
                    const s = String(slug).toLowerCase();
                    const cat = (this.catalog || []).find(c => (c.slug || '').toLowerCase() === s);
                    if (!cat || !Array.isArray(cat.features)) return 0;
                    const pkgFeatures = (this.selectedPackage && this.selectedPackage.features) || [];
                    return cat.features.filter(f => pkgFeatures.includes(f.key)).length;
                },


                buildInvoicePayload() {
                    if (!this.form.issue_invoice) {
                        return {issue_invoice: false};
                    }
                    return {
                        issue_invoice: true,
                        invoice_auto: this.invoiceNumberAuto,
                        invoice_number: this.invoiceNumberAuto ? '' : (this.invoiceNumberOverride || '').trim(),
                        tax_from_settings: Boolean(this.taxRateLocked),
                        invoice_tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                        invoice_tax_mode: 'invoice',
                        invoice_issue_date: this.invoiceIssueDate,
                        invoice_due_date: this.invoiceDueDate,
                        invoice_status: 'unpaid',
                        invoice_discount_amount: this.invoiceDiscountAmount,
                        invoice_terms: this.invoiceTerms,
                        invoice_notes: this.invoiceNotes,
                        invoice_items: (this.invoiceItems || [])
                            .filter(it => (it.item_type === 'custom') ? ((it.description || '').trim() !== '') : true)
                            .map(it => ({
                                description: (it.description || '').trim(),
                                item_type: it.item_type,
                                item_id: it.item_id || '',
                                duration_months: parseInt(it.duration_months) || 0,
                                unit_price: parseInt(it.unit_price) || 0,
                                quantity: Math.max(1, parseInt(it.quantity) || 1),
                                discount_amount: Math.max(0, parseInt(it.discount_amount) || 0),
                                tax_rate: parseFloat(it.tax_rate) || 0,
                            })),
                    };
                },

                async submitQuickProvision() {
                    if (this.submitting) return;
                    if (!this.selectedPackage) {
                        this.generalError = 'لطفاً یک پکیج را انتخاب فرمایید.';
                        return;
                    }
                    if (this.subdomainStatus === 'unavailable' || this.subdomainChecking) {
                        this.generalError = 'لطفاً یک سابدامین معتبر و آزاد انتخاب فرمایید.';
                        return;
                    }

                    this.submitting = true;
                    this.generalError = '';
                    this.formErrors = {};

                    try {
                        const pkg = this.selectedPackage;
                        const invoicePayload = this.buildInvoicePayload();

                        const payload = {
                            name: (this.form.name || '').trim(),
                            subdomain: (this.form.subdomain || '').trim(),
                            sub_account_id: this.form.sub_account_id,
                            reseller_id: this.form.reseller_id,
                            admin_name: (this.form.admin_name || '').trim(),
                            admin_mobile: (this.form.admin_mobile || '').trim(),
                            admin_email: this.form.admin_email ? this.form.admin_email.trim() : null,
                            expires_in_months: this.form.expires_in_months,
                            package_id: pkg.id,
                            modules: (pkg && pkg.modules) ? pkg.modules : [],
                            features: (pkg && pkg.features) ? pkg.features : [],
                            ...invoicePayload,
                            _token: this.csrfToken
                        };

                        const res = await fetch(this.storeTenantUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            this.submitting = false;
                            if (data.errors) {
                                this.formErrors = data.errors;
                                this.generalError = 'لطفاً خطاهای مشخص‌شده در فرم را اصلاح نمایید.';
                            } else {
                                this.generalError = data.message || 'خطا در ثبت سامانه. لطفاً فیلدها را بررسی فرمایید.';
                            }
                            return;
                        }

                        this.submitting = false;
                        this.createdTenant = data.tenant || null;
                        if (this.createdTenant) {
                            this.createdTenant.url = (data.tenant && data.tenant.url)
                                ? data.tenant.url
                                : ('https://' + this.cleanBaseDomain() + '/' + this.form.subdomain);
                            this.createdTenant.redirect_url = data.redirect_url || '';
                        }
                        this.createdInvoice = data.invoice || null;
                        this.createdOrder = data.order || null;

                        this.showToast('سامانه CRM خریدار با موفقیت راه‌اندازی شد.', 'success');
                    } catch (err) {
                        this.submitting = false;
                        this.generalError = 'خطای ارتباط با سرور هنگام ایجاد سامانه CRM.';
                    }
                },

                copyToClipboard(text, type) {
                    if (!text) return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text);
                    } else {
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.style.position = 'fixed';
                        ta.style.left = '-999999px';
                        ta.style.top = '-999999px';
                        ta.style.opacity = '0';
                        document.body.appendChild(ta);
                        ta.focus();
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                    }
                    if (type === 'url') {
                        this.copiedUrl = true;
                        setTimeout(() => this.copiedUrl = false, 2500);
                    } else if (type === 'creds') {
                        this.copiedCreds = true;
                        setTimeout(() => this.copiedCreds = false, 2500);
                    }
                }
            };
        }
    </script>
@endsection
