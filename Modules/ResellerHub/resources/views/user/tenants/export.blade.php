@extends('layouts.user')

@section('title', 'استخراج و پشتیبان‌گیری دیتابیس مستاجران (SQL Dump)')

@php
    $faNum = function ($str) {
        if ($str === null || $str === '') return '';
        return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], (string)$str);
    };

    $statusLabels = [
        'active' => ['label' => 'فعال', 'class' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800'],
        'suspended' => ['label' => 'معلق', 'class' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800'],
        'expired' => ['label' => 'منقضی‌شده', 'class' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800'],
    ];

    if (!function_exists('resellerExportSortUrl')) {
        function resellerExportSortUrl($col) {
            $currentCol = request('sort_by', 'latest');
            $currentOrder = strtolower(request('sort_order', 'desc'));

            if ($currentCol === $col . '_asc') {
                $currentCol = $col;
                $currentOrder = 'asc';
            } elseif ($currentCol === $col . '_desc') {
                $currentCol = $col;
                $currentOrder = 'desc';
            }

            $newOrder = ($currentCol === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort_by' => $col, 'sort_order' => $newOrder]);
        }
    }

    if (!function_exists('resellerExportSortIcon')) {
        function resellerExportSortIcon($col) {
            $currentCol = request('sort_by', 'latest');
            $currentOrder = strtolower(request('sort_order', 'desc'));

            if ($currentCol === $col . '_asc') {
                $currentCol = $col;
                $currentOrder = 'asc';
            } elseif ($currentCol === $col . '_desc') {
                $currentCol = $col;
                $currentOrder = 'desc';
            }

            if ($currentCol === $col) {
                if ($currentOrder === 'asc') {
                    return '<svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>';
                } else {
                    return '<svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>';
                }
            }

            return '<svg class="w-3.5 h-3.5 opacity-30 group-hover:opacity-100 transition-opacity inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>';
        }
    }
@endphp

@section('content')
    <div x-data="tenantExportPage()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-center gap-3">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-500/20 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m0 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/>
                </svg>
            </span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span>استخراج دیتابیس مستاجران</span>
                        <span
                            class="text-xs font-black px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                        SQL Dump & Data Export
                    </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        دریافت فایل خروجی دیتابیس مستقل (شامل جداول و داده‌ها) برای انتقال، پشتیبان‌گیری یا جداسازی سرور
                        هر
                        زیرمجموعه
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('reseller.dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>پیشخوان نمایندگی</span>
                </a>

                <a href="{{ route('reseller.sub-accounts.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>مدیریت زیرمجموعه‌ها</span>
                </a>
            </div>
        </div>

        {{-- Quick Stat Counters --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Card 1: Total Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                <span
                    class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل سامانه‌های در دسترس</span>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ $faNum($totalTenants) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 2: Active Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">سامانه‌های فعال</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $faNum($activeTenants) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 3: Custom Domain Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                <span
                    class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">دارای دامنه اختصاصی</span>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1.5">{{ $faNum($customDomainCount) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 4: Info Guarantee --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block truncate">امنیت و ایزولاسیون کامل</span>
                    <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mt-1.5 leading-relaxed">
                        تفکیک ۱۰۰٪ داده‌ها در سطح SQL Dump مستقل
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- Filter and Search Card (Responsive 12-col Grid, Zero Overflow, Zero Empty Gap) --}}
        <form method="GET" action="{{ route('reseller.tenants.export') }}"
              class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl transition-all duration-300 p-5 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                {{-- Search input --}}
                <div
                    class="relative col-span-1 sm:col-span-2 {{ ($isSuperAdmin && count($resellersList) > 0) ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <x-resellerhub::input class="ps-10" type="text"
                                          name="search"
                                          value="{{ request('search') }}"
                                          placeholder="جستجو در نام سامانه، ساب‌دامین، دامنه یا خریدار..."
                    />
                </div>

                {{-- Status Filter --}}
                <div
                    class="col-span-1 sm:col-span-1 lg:col-span-2">
                    <x-resellerhub::select name="status">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فقط فعال‌ها
                        </option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>فقط معلق‌ها
                        </option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>فقط منقضی‌شده‌ها
                        </option>
                    </x-resellerhub::select>
                </div>

                {{-- Sort Filter --}}
                <div
                    class="col-span-1 sm:col-span-1 {{ ($isSuperAdmin && count($resellersList) > 0) ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                    <x-resellerhub::select name="sort_by">
                        <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>
                            جدیدترین (پیش‌فرض)
                        </option>
                        <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                        <option value="name_asc" @selected(request('sort_by') === 'name_asc')>نام سامانه (الفبا صعودی)
                        </option>
                        <option value="name_desc" @selected(request('sort_by') === 'name_desc')>نام سامانه (الفبا
                            نزولی)
                        </option>
                        <option value="created_at_desc" @selected(request('sort_by') === 'created_at_desc')>تاریخ ساخت
                            (جدیدترین)
                        </option>
                        <option value="created_at_asc" @selected(request('sort_by') === 'created_at_asc')>تاریخ ساخت
                            (قدیمی‌ترین)
                        </option>
                    </x-resellerhub::select>
                </div>

                @if(request('sort_order'))
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif

                {{-- Super Admin Reseller Filter --}}
                @if($isSuperAdmin && count($resellersList) > 0)
                    <div class="col-span-1 sm:col-span-1 lg:col-span-2">
                        <x-resellerhub::select name="reseller_id">
                            <option value="all">همه نمایندگان</option>
                            @foreach($resellersList as $r)
                                <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->company_name ?: ($r->user?->name ?? 'نماینده #' . $r->id) }}
                                </option>
                            @endforeach
                        </x-resellerhub::select>
                    </div>
                @endif

                {{-- Submit and Reset Buttons --}}
                <div
                    class="col-span-1 {{ ($isSuperAdmin && count($resellersList) > 0) ? 'sm:col-span-2 lg:col-span-3' : 'sm:col-span-1 lg:col-span-3' }} flex items-center gap-2 justify-end">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->hasAny(['search', 'status', 'reseller_id', 'sort_by', 'sort_order']))
                        <a href="{{ route('reseller.tenants.export') }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all cursor-pointer shrink-0"
                           title="پاکسازی فیلترها">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>حذف</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Tenants Data Table --}}
        <x-resellerhub::card class="overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">سامانه‌های آماده استخراج
                        دیتابیس</h3>
                    <span
                        class="text-xs px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700/70 text-gray-600 dark:text-gray-300 font-bold">
                    {{ $faNum($tenants->total()) }} سامانه یافت شد
                </span>
                </div>
            </div>

            @if($tenants->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-xs sm:text-sm">
                        <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500 font-black">
                            <th scope="col" class="py-3.5 px-4 text-start">
                                <a href="{{ resellerExportSortUrl('name') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>شناسه و نام سامانه</span>
                                    {!! resellerExportSortIcon('name') !!}
                                </a>
                            </th>
                            <th scope="col" class="py-3.5 px-4 text-start">
                                <a href="{{ resellerExportSortUrl('subdomain') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>آدرس دسترسی (Subdomain)</span>
                                    {!! resellerExportSortIcon('subdomain') !!}
                                </a>
                            </th>
                            <th scope="col" class="py-3.5 px-4 text-start">مالک / زیرمجموعه</th>
                            @if($isSuperAdmin)
                                <th scope="col" class="py-3.5 px-4 text-start">
                                    <a href="{{ resellerExportSortUrl('reseller') }}"
                                       class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <span>نماینده</span>
                                        {!! resellerExportSortIcon('reseller') !!}
                                    </a>
                                </th>
                            @endif
                            <th scope="col" class="py-3.5 px-4 text-center">
                                <a href="{{ resellerExportSortUrl('status') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>وضعیت سامانه</span>
                                    {!! resellerExportSortIcon('status') !!}
                                </a>
                            </th>
                            <th scope="col" class="py-3.5 px-4 text-center">حجم داده‌های ثبت‌شده</th>
                            <th scope="col" class="py-3.5 px-4 text-end">عملیات استخراج</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                        @foreach($tenants as $tenant)
                            @php
                                $tStats = $tenantsData[$tenant->id] ?? ['total_records' => 0, 'modules' => []];
                                $totalRec = $tStats['total_records'] ?? 0;
                                $st = $statusLabels[$tenant->status] ?? ['label' => $tenant->status, 'class' => 'bg-gray-100 text-gray-700'];

                                $tenantModalPayload = [
                                    'id' => $tenant->id,
                                    'name' => $tenant->name,
                                    'subdomain' => $tenant->subdomain,
                                    'custom_domain' => $tenant->custom_domain,
                                    'status' => $tenant->status,
                                    'status_label' => $st['label'],
                                    'created_at_fa' => $tenant->jalali_created_at ?: '-',
                                    'owner_name' => $tenant->subAccount ? ($tenant->subAccount->company_name ?: $tenant->subAccount->name) : 'نمایندگی اصلی',
                                    'owner_phone' => $tenant->subAccount ? ($tenant->subAccount->mobile ?: ($tenant->subAccount->phone ?? '-')) : '-',
                                    'export_sql_url' => route('reseller.tenants.export-sql', $tenant),
                                    'export_json_url' => route('reseller.tenants.export-json', $tenant),
                                    'show_url' => route('reseller.tenants.show', $tenant),
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/60 transition-colors">
                                {{-- Tenant Title --}}
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-emerald-600 text-white font-black text-sm flex items-center justify-center shadow-xs shrink-0">
                                            <span>#{{ $tenant->id }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="font-bold text-gray-900 dark:text-white truncate max-w-[200px]">{{ $tenant->name }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                ثبت: {{ $faNum($tenant->jalali_created_at ?: '-') }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Subdomain / Custom Domain --}}
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg font-mono text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60 dir-ltr">
                                        /{{ $tenant->subdomain }}
                                    </span>
                                        @if($tenant->custom_domain)
                                            <div
                                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 dir-ltr">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                </svg>
                                                <span class="truncate">{{ $tenant->custom_domain }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Owner / SubAccount --}}
                                <td class="py-4 px-4">
                                    @if($tenant->subAccount)
                                        <div
                                            class="font-bold text-gray-800 dark:text-gray-200">{{ $tenant->subAccount->company_name ?: $tenant->subAccount->name }}</div>
                                        <div
                                            class="text-xs text-gray-400 mt-0.5">{{ $tenant->subAccount->mobile ?: ($tenant->subAccount->phone ?? '-') }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 font-bold">سامانه اختصاصی نماینده</span>
                                    @endif
                                </td>

                                {{-- Super Admin: Reseller --}}
                                @if($isSuperAdmin)
                                    <td class="py-4 px-4">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                        {{ $tenant->reseller?->company_name ?: ($tenant->reseller?->user?->name ?? 'نماینده #' . $tenant->reseller_id) }}
                                    </span>
                                    </td>
                                @endif

                                {{-- Status --}}
                                <td class="py-4 px-4 text-center">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $st['class'] }}">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full {{ $tenant->status === 'active' ? 'bg-emerald-500' : ($tenant->status === 'suspended' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                    <span>{{ $st['label'] }}</span>
                                </span>
                                </td>

                                {{-- Record Count --}}
                                <td class="py-4 px-4 text-center">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black {{ $totalRec > 0 ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                                    {{ $faNum($totalRec) }} رکورد داده
                                </span>
                                </td>

                                {{-- Action: Open Modal --}}
                                <td class="py-4 px-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                @click="openModal({{ json_encode($tenantModalPayload) }}, {{ json_encode($tStats) }})"
                                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all cursor-pointer active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>عملیات استخراج</span>
                                        </button>

                                        <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                                           class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition cursor-pointer"
                                           title="مشاهده صفحه جزئیات CRM">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Rich Pagination Footer --}}
                @if($tenants->total() > 0)
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40 rounded-b-3xl">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <div>
                                نمایش
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ $faNum($tenants->firstItem() ?? 0) }}</span>
                                تا
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ $faNum($tenants->lastItem() ?? 0) }}</span>
                                از مجموع
                                <span
                                    class="font-black text-indigo-600 dark:text-indigo-400">{{ $faNum($tenants->total()) }}</span>
                                سامانه زیرمجموعه
                            </div>

                            <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>

                            {{-- Per Page Dropdown --}}
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400 text-xs">تعداد در هر صفحه:</span>
                                <select name="per_page" aria-label="تعداد در هر صفحه"
                                        onchange="window.location.href = this.value"
                                        class="py-1 px-2.5 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer shadow-2xs">
                                    @foreach([5, 10, 15, 25, 50, 100] as $size)
                                        <option
                                            value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                                            {{ (int)$tenants->perPage() === $size ? 'selected' : '' }}>
                                            {{ $faNum($size) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            @if($tenants->hasPages())
                                {{ $tenants->appends(request()->query())->links('resellerhub::partials.pagination') }}
                            @endif
                        </div>
                    </div>
                @endif

            @else
                <div class="py-16 text-center text-gray-400">
                    <div
                        class="w-16 h-16 rounded-3xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto mb-4 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h4 class="font-black text-gray-700 dark:text-gray-300 text-base">هیچ سامانه‌ای برای استخراج دیتابیس
                        یافت نشد</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">برای این نماینده هیچ سامانه‌ای ثبت نشده است
                        یا
                        فیلترهای اعمال‌شده نتیجه‌ای در بر نداشتند.</p>
                </div>
            @endif
        </x-resellerhub::card>

        {{-- Export Operations Modal --}}
        <div x-show="modalOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">

            <div x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 @click.outside="closeModal()"
                 @keydown.escape.window="closeModal()"
                 class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">

                {{-- Modal Header --}}
                <div
                    class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/40">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m0 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span>استخراج دیتابیس اختصاصی</span>
                                <span
                                    class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800">
                                #<span x-text="currentTenant?.id"></span>
                            </span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                سامانه: <span class="font-bold text-gray-800 dark:text-gray-200"
                                              x-text="currentTenant?.name"></span>
                            </p>
                        </div>
                    </div>

                    <button type="button"
                            @click="closeModal()"
                            class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 flex items-center justify-center transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs sm:text-sm">

                    {{-- Tenant Info Summary Bar --}}
                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <span class="text-xs text-gray-400 block font-bold">زیرمجموعه / خریدار:</span>
                            <span class="font-black text-gray-800 dark:text-gray-200 text-xs mt-0.5 block truncate"
                                  x-text="currentTenant?.owner_name"></span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-bold">آدرس ساب‌دامین:</span>
                            <span
                                class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400 mt-0.5 block truncate dir-ltr">
                            /<span x-text="currentTenant?.subdomain"></span>
                        </span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-bold">کل رکوردهای دیتابیس:</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs mt-0.5 block">
                            <span x-text="currentStats?.total_records ?? 0"></span> رکورد مجزا
                        </span>
                        </div>
                    </div>

                    {{-- Primary Action Cards --}}
                    <div class="space-y-3">
                        <div
                            class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                            <span>انتخاب نوع فایل خروجی:</span>
                            <span class="text-xs text-gray-400">دانلود بدون محدودیت و به صورت آنی</span>
                        </div>

                        {{-- Option 1: Standalone SQL Dump (Recommended) --}}
                        <a :href="currentTenant?.export_sql_url"
                           class="group p-4 sm:p-5 rounded-2xl bg-emerald-50/60 hover:bg-emerald-50 dark:bg-emerald-950/20 dark:hover:bg-emerald-950/30 border-2 border-emerald-500/40 hover:border-emerald-500 flex items-center justify-between gap-4 transition-all duration-200 shadow-sm cursor-pointer">
                            <div class="flex items-center gap-3.5">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                                    SQL
                                </div>
                                <div class="space-y-1">
                                    <div
                                        class="font-black text-gray-900 dark:text-white text-sm flex items-center gap-2">
                                        <span>دانلود فایل کامل دیتابیس (SQL Dump)</span>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full bg-emerald-600 text-white font-bold">پیشنهادی</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                        شامل کدهای ایجاد جداول (<code
                                            class="font-mono text-xs text-emerald-600 font-bold">CREATE TABLE</code>) و
                                        درج کلیه داده‌ها (<code class="font-mono text-xs text-emerald-600 font-bold">INSERT
                                            INTO</code>) با رعایت روابط خارجی
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0">
                            <span
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-black text-xs shadow-md shadow-emerald-500/20 group-hover:bg-emerald-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>دانلود .sql</span>
                            </span>
                            </div>
                        </a>

                        {{-- Option 2: JSON Export --}}
                        <a :href="currentTenant?.export_json_url"
                           class="group p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800/80 hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4 transition-all duration-200 shadow-xs cursor-pointer">
                            <div class="flex items-center gap-3.5">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                                    JSON
                                </div>
                                <div class="space-y-1">
                                    <div class="font-black text-gray-900 dark:text-white text-sm">
                                        دانلود آرشیو ساختاریافته JSON
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                        مناسب برای پردازش‌های برنامه‌نویسی، انتقال به سامانه‌های ثالث و یکپارچه‌سازی API
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0">
                            <span
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs transition">
                                <span>دانلود .json</span>
                            </span>
                            </div>
                        </a>
                    </div>

                    {{-- Table Breakdown Details --}}
                    <div class="space-y-3 pt-2">
                        <div
                            class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                            <span>خلاصه تفکیک جداول دیتابیس در فایل خروجی:</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto p-1">
                            <template x-for="(modInfo, modKey) in (currentStats?.modules || {})" :key="modKey">
                                <template x-for="tbl in (modInfo?.tables || [])" :key="tbl.table">
                                    <div x-show="tbl.count > 0"
                                         class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-gray-800 dark:text-gray-200 truncate"
                                                 x-text="tbl.label"></div>
                                            <div class="text-xs text-gray-400 font-mono" x-text="tbl.table"></div>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-xs border border-emerald-200/60 dark:border-emerald-800/50 shrink-0">
                                        <span x-text="tbl.count"></span> رکورد
                                    </span>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>

                    {{-- Security & Isolation Notice --}}
                    <div
                        class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 flex items-start gap-2.5 text-xs text-blue-900 dark:text-blue-200">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <p class="leading-relaxed">
                            داده‌های استخراج‌شده در این بسته به صورت کاملاً ایزوله و محافظت‌شده فیلتر شده‌اند و به هیچ
                            عنوان
                            اطلاعات سایر مستاجران یا داده‌های مدیریتی پلتفرم در این فایل قرار ندارد.
                        </p>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/40 flex items-center justify-between">
                    <a :href="currentTenant?.show_url"
                       class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <span>مشاهده پنل و تنظیمات این CRM</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>

                    <button type="button"
                            @click="closeModal()"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition cursor-pointer">
                        بستن
                    </button>
                </div>

            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function tenantExportPage() {
                return {
                    modalOpen: false,
                    currentTenant: null,
                    currentStats: null,
                    openModal(tenant, stats) {
                        this.currentTenant = tenant;
                        this.currentStats = stats;
                        this.modalOpen = true;
                        document.body.classList.add('overflow-hidden');
                    },
                    closeModal() {
                        this.modalOpen = false;
                        this.currentTenant = null;
                        this.currentStats = null;
                        document.body.classList.remove('overflow-hidden');
                    }
                }
            }
        </script>
    @endpush
@endsection
