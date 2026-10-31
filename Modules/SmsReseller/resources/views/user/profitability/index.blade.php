@extends('layouts.user')

@section('title', 'تحلیل سود و عملکرد مشتریان پیامک')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white ps-4 pe-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('smsreseller::partials.toast')


    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <span>تحلیل سودآوری و رتبه‌بندی مشتریان پیامک</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                شناسایی مشتریان برتر (VIP)، بیشترین واریزی‌ها، حجم مصرف و تطبیق هوشمند با پرونده‌های مشتریان CRM
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.smsreseller.user_payments') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>واریزی‌های کاربران</span>
            </a>

            <a href="{{ route('user.smsreseller.user_credits') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-2xs active:scale-95 cursor-pointer">
                <svg class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>لاگ اعتبارات</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">تعداد کل مشتریان شارژکننده</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $faNum(number_format($kpis['total_customers_count'])) }} <span
                            class="text-xs font-bold text-gray-400">کاربر</span>
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تطبیق با پرونده CRM:</span>
                <strong
                    class="text-indigo-600 dark:text-indigo-400 font-black">{{ $faNum(number_format($kpis['matched_crm_count'])) }}
                    مشتری شناسایی‌شده</strong>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">کل دریافتی از مشتریان (فروش)</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $faNum(number_format($kpis['total_income_sum'])) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>مجموع واریزها:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($kpis['total_payments_count'])) }} تراکنش موفق</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">کل سود خالص تاریخی</p>
                    <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ $faNum(number_format($kpis['total_net_profit_sum'])) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>میانگین مارجین واقعی:</span>
                <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $faNum(number_format($kpis['overall_profit_ratio'], 1)) }}%</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs font-bold text-gray-400 dark:text-gray-400">مشتری شماره ۱ (Top VIP)</p>
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.496m5.007 0a7.5 7.5 0 00-5.007 0m5.007 0V8.25a2.25 2.25 0 00-2.25-2.25h-0.514a2.25 2.25 0 00-2.25 2.25v2.25m-3.75-2.25h-.75a2.25 2.25 0 00-2.25 2.25v.75a4.5 4.5 0 004.5 4.5m10.5-7.5h.75a2.25 2.25 0 012.25 2.25v.75a4.5 4.5 0 01-4.5 4.5"/>
                            </svg>
                        </span>
                    </div>
                    <p class="text-lg font-black text-amber-600 dark:text-amber-400 mt-1 truncate"
                       title="{{ $kpis['top_customer_name'] }}">
                        {{ $kpis['top_customer_name'] }}
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>سود خالص کسب‌شده:</span>
                <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $faNum(number_format($kpis['top_customer_profit'])) }} ریال</span>
            </div>
        </div>
    </div>
    <form action="{{ route('user.smsreseller.user_profitability') }}" method="GET"
          class="bg-white dark:bg-gray-800/60 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="{{ $labelClass }}">جستجوی شماره یا نام مشتری</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="0912... یا نام مشتری"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 pr-11 pl-4 py-2 text-xs sm:text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
            </div>
            <div>
                <label class="{{ $labelClass }}">وضعیت پرونده در CRM</label>
                <select name="crm_status"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="all">همه کاربران پیامک</option>
                    <option value="matched" @selected(request('crm_status') === 'matched')>فقط مشتریان متصل به CRM
                    </option>
                    <option value="unmatched" @selected(request('crm_status') === 'unmatched')>فقط کاربران بدون پرونده
                        CRM
                    </option>
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">مرتب‌سازی نتایج بر اساس</label>
                <select name="sort_by"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="income_desc" @selected(request('sort_by') === 'income_desc' || !request('sort_by'))>
                        بیشترین واریزی و فروش کل (پیش‌فرض)
                    </option>
                    <option value="net_profit_desc" @selected(request('sort_by') === 'net_profit_desc')>بیشترین سود
                        خالص
                    </option>
                    <option value="payments_desc" @selected(request('sort_by') === 'payments_desc')>بیشترین تعداد دفعات
                        واریز
                    </option>
                    <option value="usage_desc" @selected(request('sort_by') === 'usage_desc')>بیشترین حجم مصرف پیامک
                    </option>
                    <option value="recent" @selected(request('sort_by') === 'recent')>جدیدترین فعالیت و پرداخت</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 cursor-pointer">
                    اعمال فیلتر
                </button>
                @if(request()->hasAny(['search', 'crm_status', 'sort_by']))
                    <a href="{{ route('user.smsreseller.user_profitability') }}"
                       class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all shrink-0 cursor-pointer"
                       title="پاک‌سازی فیلتر">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 overflow-hidden shadow-sm backdrop-blur-xl">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span>جدول رتبه‌بندی سودآوری مشتریان</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">خلاصه شارژ کل، هزینه خرید پیامک از سرور و سود
                    نهایی به ازای هر مشتری</p>
            </div>
        </div>

        @if($paginatedUsers->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-center">رتبه</th>
                        <th class="px-6 py-4 text-start">مشخصات مشتری</th>
                        <th class="px-6 py-4 text-start">شارژ کل کاربر (فروش)</th>
                        <th class="px-6 py-4 text-start">پرداختی به سرور (هزینه)</th>
                        <th class="px-6 py-4 text-start">سود خالص ما</th>
                        <th class="px-6 py-4 text-start">آخرین پرداخت</th>
                        <th class="px-6 py-4 text-end">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($paginatedUsers as $index => $u)
                        @php
                            $rank = ($paginatedUsers->currentPage() - 1) * $paginatedUsers->perPage() + $loop->iteration;
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($rank === 1)
                                    <span
                                        class="inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shadow-2xs font-black text-xs"
                                        title="رتبه ۱ سودآوری">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.496m5.007 0a7.5 7.5 0 00-5.007 0m5.007 0V8.25a2.25 2.25 0 00-2.25-2.25h-0.514a2.25 2.25 0 00-2.25 2.25v2.25m-3.75-2.25h-.75a2.25 2.25 0 00-2.25 2.25v.75a4.5 4.5 0 004.5 4.5m10.5-7.5h.75a2.25 2.25 0 012.25 2.25v.75a4.5 4.5 0 01-4.5 4.5"/>
                                        </svg>
                                        <span>{{ $faNum(1) }}</span>
                                    </span>
                                @elseif($rank === 2)
                                    <span
                                        class="inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-xl bg-slate-500/10 text-slate-600 dark:text-slate-300 border border-slate-400/20 shadow-2xs font-black text-xs"
                                        title="رتبه ۲ سودآوری">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.496m5.007 0a7.5 7.5 0 00-5.007 0m5.007 0V8.25a2.25 2.25 0 00-2.25-2.25h-0.514a2.25 2.25 0 00-2.25 2.25v2.25m-3.75-2.25h-.75a2.25 2.25 0 00-2.25 2.25v.75a4.5 4.5 0 004.5 4.5m10.5-7.5h.75a2.25 2.25 0 012.25 2.25v.75a4.5 4.5 0 01-4.5 4.5"/>
                                        </svg>
                                        <span>{{ $faNum(2) }}</span>
                                    </span>
                                @elseif($rank === 3)
                                    <span
                                        class="inline-flex items-center justify-center gap-1 px-2.5 py-1 rounded-xl bg-amber-700/10 text-amber-700 dark:text-amber-500 border border-amber-700/20 shadow-2xs font-black text-xs"
                                        title="رتبه ۳ سودآوری">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-500" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.496m5.007 0a7.5 7.5 0 00-5.007 0m5.007 0V8.25a2.25 2.25 0 00-2.25-2.25h-0.514a2.25 2.25 0 00-2.25 2.25v2.25m-3.75-2.25h-.75a2.25 2.25 0 00-2.25 2.25v.75a4.5 4.5 0 004.5 4.5m10.5-7.5h.75a2.25 2.25 0 012.25 2.25v.75a4.5 4.5 0 01-4.5 4.5"/>
                                        </svg>
                                        <span>{{ $faNum(3) }}</span>
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 font-bold text-xs">
                                        {{ $faNum($rank) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 @if($u['client']) bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 @else bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 @endif font-bold text-xs shadow-2xs">
                                        @if($u['client'])
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        @if($u['client'])
                                            <a href="{{ route('user.clients.show', $u['client']->id) }}" target="_blank"
                                               class="font-black text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-2 group">
                                                <span>{{ $u['client']->full_name }}</span>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                                    مشتری CRM
                                                </span>
                                            </a>
                                        @else
                                            <span class="font-bold text-sm text-gray-800 dark:text-gray-200 block">کاربر مهمان پیامک</span>
                                        @endif
                                        <span
                                            class="text-xs font-mono text-gray-400 dir-ltr block text-start mt-0.5 select-all">{{ $u['user_name'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-black text-gray-900 dark:text-white block">
                                    {{ $faNum(number_format($u['total_income'])) }} <span
                                        class="text-xs font-normal text-gray-400">ریال</span>
                                </span>
                                <span class="text-[11px] text-gray-400 block mt-0.5">
                                    {{ $faNum($u['payments_count']) }} بار واریز موفق
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-black text-rose-600 dark:text-rose-400 block">
                                    {{ $faNum(number_format($u['estimated_cost'])) }} <span
                                        class="text-xs font-normal text-gray-400">ریال</span>
                                </span>
                                <span class="text-[11px] text-gray-400 block mt-0.5">
                                    هزینه خرید پیامک
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 block">
                                    + {{ $faNum(number_format($u['net_profit'])) }} <span
                                        class="text-xs font-normal text-gray-400">ریال</span>
                                </span>
                                <span
                                    class="text-[11px] text-emerald-600 dark:text-emerald-400 font-black block mt-0.5">
                                    مارجین {{ $faNum(number_format($u['net_margin_percent'], 1)) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500 dark:text-gray-400">
                                <span class="dir-ltr inline-block">
                                    {{ $u['last_payment_date'] ? $faNum($u['last_payment_date']) : '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- دکمه مشاهده جزئیات عملکرد (آیکون چشم) --}}
                                    <a href="{{ route('user.smsreseller.user_profitability.show', $u['user_name']) }}"
                                       class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-blue-600 hover:bg-blue-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400 transition-colors shadow-2xs"
                                       title="مشاهده جزئیات و پرونده سودآوری">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <a href="{{ route('user.smsreseller.user_payments', ['username' => $u['user_name'], 'state' => 'واریز شده']) }}"
                                       class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-400 transition-colors shadow-2xs"
                                       title="مشاهده تمام واریزی‌های این کاربر">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </a>

                                    <a href="{{ route('user.smsreseller.user_credits', ['username' => $u['user_name']]) }}"
                                       class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors shadow-2xs"
                                       title="مشاهده لاگ تراکنش‌های این کاربر">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </a>

                                    @if($u['client'])
                                        <a href="{{ route('user.clients.show', $u['client']->id) }}"
                                           target="_blank"
                                           class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-purple-600 hover:bg-purple-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-purple-950/40 dark:hover:text-purple-400 transition-colors shadow-2xs"
                                           title="مشاهده پرونده کامل مشتری در CRM">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
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
            <div
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        نمایش
                        <strong
                            class="text-gray-800 dark:text-gray-200">{{ $faNum($paginatedUsers->firstItem() ?? 0) }}</strong>
                        تا
                        <strong
                            class="text-gray-800 dark:text-gray-200">{{ $faNum($paginatedUsers->lastItem() ?? 0) }}</strong>
                        از
                        <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($paginatedUsers->total()) }}</strong>
                        مشتری
                    </span>
                </div>

                @if($paginatedUsers->hasPages())
                    <div>
                        {{ $paginatedUsers->withQueryString()->links('smsreseller::partials.pagination') }}
                    </div>
                @endif
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">اطلاعاتی مطابق با فیلترهای جستجو یافت
                        نشد</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        می‌توانید عبارت جستجو را تغییر داده یا تمام فیلترها را پاک‌سازی کنید.
                    </p>
                </div>
                @if(request()->hasAny(['search', 'crm_status', 'sort_by']))
                    <a href="{{ route('user.smsreseller.user_profitability') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        پاک‌سازی تمام فیلترها
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@include('smsreseller::partials.confirm-modal')
@include('partials.jalali-date-picker')
<script>
    (function () {
        function initSmsResellerJdp() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp], [data-jdp-only-date]',
                    date: true,
                    time: false,
                    hasSecond: false
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSmsResellerJdp);
        } else {
            initSmsResellerJdp();
        }
        document.addEventListener('focusin', function (e) {
            if (e.target && (e.target.hasAttribute('data-jdp') || e.target.hasAttribute('data-jdp-only-date'))) {
                if (window.jalaliDatepicker) {
                    window.jalaliDatepicker.updateOptions({
                        date: true,
                        time: false,
                        hasSecond: false
                    });
                }
            }
        }, true);
    })();
</script>
@endsection
