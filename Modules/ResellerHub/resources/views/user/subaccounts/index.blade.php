@extends('layouts.user')

@section('title', 'مدیریت زیرمجموعه‌ها و خریداران CRM')

@php
    $centralDomain = reseller_clean_domain(config('tenancy.central_domains')[0] ?? request()->getHost());
@endphp


@section('content')
    <div x-data="subAccountsManager()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5">
                            <span>مدیریت زیرمجموعه‌ها و خریداران CRM</span>
                            <span
                                class="text-xs font-black px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                            Sub-Accounts
                        </span>
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                            مشاهده، جستجو و مدیریت حساب‌های خریداران سامانه‌ها، پزشکان و مدیران کلینیک‌های زیرمجموعه
                        </p>
                    </div>
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

                <a href="{{ route('reseller.sub-accounts.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black shadow-md shadow-emerald-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ایجاد زیرمجموعه جدید</span>
                </a>

                <a href="{{ route('reseller.tenants.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ساخت CRM جدید برای زیرمجموعه</span>
                </a>
            </div>
        </div>

        {{-- Quick Stat Counters --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Card 1: Total Sub-Accounts --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                <span
                    class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل حساب‌های زیرمجموعه</span>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ reseller_fa_num($totalSubAccounts) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 2: Active Sub-Accounts --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                <span
                    class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">زیرمجموعه‌های فعال</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ reseller_fa_num($activeSubAccounts) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 3: Suspended Sub-Accounts --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">حساب‌های معلق</span>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5">{{ reseller_fa_num($suspendedSubAccounts) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 4: Connected CRM Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                <span
                    class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">سامانه‌های CRM متصل</span>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1.5">{{ reseller_fa_num($connectedTenants) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- Filter and Search Card --}}
        <form method="GET" action="{{ route('reseller.sub-accounts.index') }}"
              class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl transition-all duration-300 p-5 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                {{-- Search input --}}
                <div
                    class="relative {{ ($isSuperAdmin && count($resellersList) > 0) ? 'lg:col-span-2' : 'lg:col-span-4' }}">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <x-resellerhub::input class="ps-10" type="text"
                                          name="search"
                                          value="{{ request('search') }}"
                                          placeholder="جستجو در نام کسب‌وکار، ایمیل، موبایل..."
                    />
                </div>

                {{-- Status Filter --}}
                <div class="lg:col-span-2">
                    <x-resellerhub::select name="status">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فقط فعال‌ها
                        </option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>فقط معلق‌ها
                        </option>
                    </x-resellerhub::select>
                </div>

                {{-- Source Filter --}}
                <div class="lg:col-span-2">
                    <x-resellerhub::select name="source">
                        <option value="">همه منابع ثبت</option>
                        <option value="online" {{ request('source') === 'online' ? 'selected' : '' }}> خرید آنلاین از
                            سایت
                        </option>
                        <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}> ثبت دستی نماینده
                        </option>
                    </x-resellerhub::select>
                </div>

                {{-- Sort Filter --}}
                <div class="lg:col-span-2">
                    <x-resellerhub::select name="sort_by">
                        <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>
                            جدیدترین
                            (پیش‌فرض)
                        </option>
                        <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                        <option value="name_asc" @selected(request('sort_by') === 'name_asc')>نام خریدار (الفبا صعودی)
                        </option>
                        <option value="name_desc" @selected(request('sort_by') === 'name_desc')>نام خریدار (الفبا نزولی)
                        </option>
                        <option value="created_at_desc" @selected(request('sort_by') === 'created_at_desc')>تاریخ ثبت
                            (جدیدترین)
                        </option>
                        <option value="created_at_asc" @selected(request('sort_by') === 'created_at_asc')>تاریخ ثبت
                            (قدیمی‌ترین)
                        </option>
                    </x-resellerhub::select>
                </div>

                @if(request('sort_order'))
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif

                {{-- Super Admin Reseller Filter --}}
                @if($isSuperAdmin && count($resellersList) > 0)
                    <div class="lg:col-span-2">
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
                    class="flex items-center gap-1.5 lg:col-span-2 justify-end">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->hasAny(['search', 'status', 'reseller_id', 'sort_by', 'sort_order']))
                        <a href="{{ route('reseller.sub-accounts.index') }}"
                           class="inline-flex items-center justify-center p-2.5 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors shrink-0"
                           title="پاکسازی فیلترها">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>

                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Sub-Accounts Data Table --}}
        <x-resellerhub::card class="overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">فهرست حساب‌های
                        زیرمجموعه</h3>
                    <span
                        class="text-xs px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700/70 text-gray-600 dark:text-gray-300 font-bold">
                    {{ reseller_fa_num($subAccounts->total()) }} حساب یافت شد
                </span>
                </div>
            </div>

            @if($subAccounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-xs sm:text-sm">
                        <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500 font-black">
                            <th scope="col" class="py-3.5 px-4 text-start">
                                <a href="{{ reseller_sort_url('company') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>کسب‌وکار / زیرمجموعه</span>
                                    {!! reseller_sort_icon('company') !!}
                                </a>
                            </th>
                            <th scope="col" class="py-3.5 px-4 text-start">
                                <a href="{{ reseller_sort_url('tenant') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>سامانه CRM متصل</span>
                                    {!! reseller_sort_icon('tenant') !!}
                                </a>
                            </th>
                            @if($isSuperAdmin)
                                <th scope="col" class="py-3.5 px-4 text-start">
                                    <a href="{{ reseller_sort_url('reseller') }}"
                                       class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <span>نماینده مجری</span>
                                        {!! reseller_sort_icon('reseller') !!}
                                    </a>
                                </th>
                            @endif
                            <th scope="col" class="py-3.5 px-4 text-start">
                                <a href="{{ reseller_sort_url('date') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>تاریخ ثبت</span>
                                    {!! reseller_sort_icon('date') !!}
                                </a>
                            </th>
                            <th scope="col" class="py-3.5 px-4 text-center">
                                <a href="{{ reseller_sort_url('status') }}"
                                   class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>وضعیت حساب</span>
                                    {!! reseller_sort_icon('status') !!}
                                </a>
                            </th>
                            <th scope="col" class="py-3.5 px-4 text-end">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                        @foreach($subAccounts as $sub)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/60 transition-colors">
                                <td class="py-4 px-4">
                                    @php
                                        $subDisplayName = $sub->company_name ?: ($sub->tenant?->name ?: $sub->name);
                                        $subInitial = mb_substr($subDisplayName ?: '—', 0, 1, 'UTF-8');
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-xs shrink-0">
                                            <span>{{ $subInitial }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('reseller.sub-accounts.show', $sub->id) }}"
                                               class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate block">
                                                {{ $subDisplayName }}
                                            </a>
                                            <div
                                                class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold border {{ $sub->source_badge }}"
                                                    title="{{ $sub->source_label }}">
                                                    @if($sub->isOnlinePurchase())
                                                        <svg class="w-3 h-3" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-3 h-3" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                    @endif
                                                    <span>{{ $sub->source_label }}</span>
                                                </span>
                                                @if($sub->email)
                                                    <span
                                                        class="dir-ltr text-start truncate max-w-[160px]">{{ $sub->email }}</span>
                                                @endif
                                                @if($sub->mobile)
                                                    <span
                                                        class="dir-ltr text-start">{{ reseller_fa_num($sub->mobile) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($sub->tenant)
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('reseller.tenants.show', $sub->tenant->id) }}"
                                                   class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    {{ $sub->tenant->name }}
                                                </a>
                                                <span
                                                    class="px-1.5 py-0.5 text-xs font-bold rounded-md {{ $sub->tenant->status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                                    {{ $sub->tenant->status === 'active' ? 'سامانه فعال' : 'سامانه معلق' }}
                                                </span>
                                            </div>
                                            <a href="https://{{ $sub->tenant->subdomain }}.{{ $centralDomain }}"
                                               target="_blank"
                                               class="text-xs text-gray-400 hover:text-indigo-500 dir-ltr text-start flex items-center gap-1">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                <span>{{ $sub->tenant->subdomain }}.{{ $centralDomain }}</span>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">فاقد سامانه متصل</span>
                                    @endif
                                </td>

                                @if($isSuperAdmin)
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-gray-700 dark:text-gray-300">
                                            {{ $sub->reseller?->company_name ?: ($sub->reseller?->user?->name ?? 'نماینده #' . reseller_fa_num($sub->reseller_id)) }}
                                        </span>
                                    </td>
                                @endif

                                <td class="py-4 px-4 text-gray-500 dark:text-gray-400">
                                    <div
                                        class="font-bold text-gray-700 dark:text-gray-300">{{ reseller_format_jalali($sub->created_at, 'Y/m/d H:i') }}</div>
                                    <div class="text-xs text-gray-400">{{ $sub->time_ago }}</div>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $sub->status_badge }}"
                                        id="badge-sub-{{ $sub->id }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $sub->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span>{{ $sub->status_label }}</span>
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-end">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('reseller.sub-accounts.show', $sub->id) }}"
                                           class="p-2 rounded-xl bg-gray-100 hover:bg-indigo-50 dark:bg-gray-700 dark:hover:bg-indigo-950/60 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition cursor-pointer"
                                           title="مشاهده جزئیات کامل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('reseller.sub-accounts.edit', $sub->id) }}"
                                           class="p-2 rounded-xl bg-gray-100 hover:bg-amber-50 dark:bg-gray-700 dark:hover:bg-amber-950/60 text-gray-600 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition cursor-pointer"
                                           title="ویرایش حساب زیرمجموعه">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button"
                                                @click="promptToggleStatus({{ $sub->id }}, '{{ addslashes($sub->name) }}', '{{ $sub->status }}')"
                                                class="p-2 rounded-xl transition cursor-pointer {{ $sub->status === 'active' ? 'bg-amber-50 hover:bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 dark:text-amber-300' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-300' }}"
                                                title="{{ $sub->status === 'active' ? 'تعلیق حساب زیرمجموعه' : 'فعال‌سازی مجدد حساب' }}">
                                            @if($sub->status === 'active')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                        @if($sub->tenant)
                                            <a href="{{ route('reseller.tenants.show', $sub->tenant->id) }}"
                                               class="p-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 transition cursor-pointer"
                                               title="مشاهده صفحه CRM">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Rich Pagination Footer --}}
                @if($subAccounts->total() > 0)
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40 rounded-b-3xl">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <div>
                                نمایش
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ reseller_fa_num($subAccounts->firstItem() ?? 0) }}</span>
                                تا
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ reseller_fa_num($subAccounts->lastItem() ?? 0) }}</span>
                                از مجموع
                                <span
                                    class="font-black text-indigo-600 dark:text-indigo-400">{{ reseller_fa_num($subAccounts->total()) }}</span>
                                حساب زیرمجموعه ثبت‌شده
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
                                            {{ (int)$subAccounts->perPage() === $size ? 'selected' : '' }}>
                                            {{ reseller_fa_num($size) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            @if($subAccounts->hasPages())
                                {{ $subAccounts->appends(request()->query())->links('resellerhub::partials.pagination') }}
                            @else
                                <nav role="navigation" aria-label="Pagination Navigation"
                                     class="inline-flex items-center gap-1.5">
                                <span
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none"
                                    title="صفحه قبل">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs shadow-md shadow-indigo-500/20 select-none">
                                    {{ reseller_fa_num(1) }}
                                </span>
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none"
                                        title="صفحه بعد">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </span>
                                </nav>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="py-16 text-center px-4">
                    <div
                        class="w-16 h-16 rounded-3xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-500 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">هیچ زیرمجموعه‌ای یافت نشد</h4>
                    <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-1 max-w-sm mx-auto">
                        @if(request()->hasAny(['search', 'status', 'reseller_id']))
                            هیچ حسابی با مشخصات و فیلترهای جستجوی شما مطابقت ندارد.
                        @else
                            هنوز هیچ حساب خریدار یا زیرمجموعه‌ای در این بخش ثبت نشده است. با ساخت نخستین CRM، مشخصات
                            خریدار
                            در این بخش قرار می‌گیرد.
                        @endif
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-3">
                        @if(request()->hasAny(['search', 'status', 'reseller_id']))
                            <a href="{{ route('reseller.sub-accounts.index') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition">
                                پاکسازی فیلترها
                            </a>
                        @else
                            <a href="{{ route('reseller.tenants.create') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-500/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ساخت اولین CRM و خریدار</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </x-resellerhub::card>


        {{-- Toggle Status Confirmation Modal (Styled with High-impact Animations) --}}
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

                    {{-- Dynamic 64px Icon Badge with Soft Glow --}}
                    <div
                        class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center shadow-xl transition-all duration-300"
                        :class="toggleTarget?.current_status === 'active' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 shadow-rose-500/20' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-emerald-500/20'">
                        <template x-if="toggleTarget?.current_status === 'active'">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                            </svg>
                        </template>
                        <template x-if="toggleTarget?.current_status !== 'active'">
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
                            x-text="toggleTarget?.current_status === 'active' ? ('تعلیق دسترسی «' + (toggleTarget?.name || '') + '»') : ('فعال‌سازی مجدد حساب «' + (toggleTarget?.name || '') + '»')"></h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed"
                           x-text="toggleTarget?.current_status === 'active' ? 'با تعلیق حساب، دسترسی اولیه ورود خریدار و مدیر این زیرمجموعه موقتاً مسدود شده و امکان ورود به CRM غیرفعال خواهد شد.' : 'با رفع تعلیق، دسترسی کامل ورود خریدار به سامانه CRM اختصاصی بلافاصله فعال می‌گردد.'"></p>
                    </div>

                    {{-- Summary Preview Card --}}
                    <div
                        class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs space-y-2.5 text-start">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">نام خریدار / زیرمجموعه:</span>
                            <span class="font-black text-gray-800 dark:text-gray-200"
                                  x-text="toggleTarget?.name || '—'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">وضعیت کنونی:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                  :class="toggleTarget?.current_status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400'"
                                  x-text="toggleTarget?.current_status === 'active' ? 'حساب فعال' : 'حساب معلق'"></span>
                        </div>
                    </div>

                    {{-- Modal Action Buttons --}}
                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button type="button" @click="showToggleModal = false" :disabled="isToggling"
                                class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-bold transition-all w-1/2 flex items-center justify-center cursor-pointer disabled:opacity-50">
                            انصراف
                        </button>
                        <button type="button" @click="executeToggleStatus()" :disabled="isToggling"
                                :class="toggleTarget?.current_status === 'active' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/25' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/25'"
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
                                x-text="isToggling ? 'در حال پردازش...' : (toggleTarget?.current_status === 'active' ? 'تایید و تعلیق حساب' : 'تایید و فعال‌سازی')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div x-show="toast.show" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 end-6 z-[100] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl text-xs sm:text-sm font-bold border backdrop-blur-md"
             :class="toast.type === 'success' ? 'bg-emerald-600/95 border-emerald-500 text-white' : 'bg-rose-600/95 border-rose-500 text-white'">
            <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="toast.type !== 'success'" class="w-5 h-5" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span x-text="toast.message"></span>
        </div>

    </div>

    <script>
        function subAccountsManager() {
            return {

                showToggleModal: false,
                toggleTarget: null,
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

                promptToggleStatus(id, name, currentStatus) {
                    this.toggleTarget = {id, name, current_status: currentStatus};
                    this.showToggleModal = true;
                },

                async executeToggleStatus() {
                    if (!this.toggleTarget || this.isToggling) return;
                    this.isToggling = true;

                    try {
                        const res = await fetch(`{{ url('/reseller/sub-accounts') }}/${this.toggleTarget.id}/toggle-status`, {
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
                }
            };
        }
    </script>
    @include('resellerhub::partials.prevent-double-submit')
@endsection
