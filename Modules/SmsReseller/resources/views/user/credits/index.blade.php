@extends('layouts.user')

@section('title', 'تراکنش‌های اعتباری کاربران زیرمجموعه')

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
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
                <span>تراکنش‌های اعتباری کاربران زیرمجموعه</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                ریز لاگ گردش، مصرف و شارژ اعتبار تمام کاربران نمایندگی پیامک
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
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>بروزرسانی لیست</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div
            class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($response['success']) && $response['success'] === false)
        <div
            class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <span>خطا در دریافت اطلاعات: {{ $response['message'] ?? 'نامشخص' }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">جمع کل گردش اعتبار</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $faNum(number_format($totalIncreaseAmount + $totalDecreaseAmount)) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>مجموع تراز:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">افزایش + کاهش</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تغییرات مثبت و منفی:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">مجموع تراکنش‌های جاری</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">مجموع افزایش اعتبار</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $faNum(number_format($totalIncreaseAmount)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>ورودی:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">واریز و شارژ کاربران</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>روند شارژها:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">مثبت (+)</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">مجموع کاهش اعتبار (مصرف)</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ $faNum(number_format($totalDecreaseAmount)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>خروجی:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400">ارسال پیامک و تعرفه</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تراز مصرف:</span>
                <span class="font-bold text-rose-600 dark:text-rose-400">کسر شده (-)</span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">تعداد کل رکوردها</p>
                    <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ $faNum(number_format($transactions->total())) }} <span
                            class="text-xs font-bold text-gray-400">ردیف</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>صفحه‌بندی:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">صفحه {{ $faNum($transactions->currentPage()) }} از {{ $faNum($transactions->lastPage()) }}</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>نمایش:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum($transactions->count()) }} رکورد در این صفحه</span>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('user.smsreseller.user_credits') }}"
          class="bg-white dark:bg-gray-800/60 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="{{ $labelClass }}">نام کاربری / شماره موبایل</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input type="text" name="username" placeholder="0912..." value="{{ request('username') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 ps-10 pe-3 py-2 text-xs sm:text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-end">
                </div>
            </div>

            <div>
                <label class="{{ $labelClass }}">از تاریخ</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="text" name="from_date" data-jdp data-jdp-only-date placeholder="انتخاب تاریخ"
                           value="{{ request('from_date') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 pr-11 pl-4 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-start placeholder:text-gray-400 cursor-pointer"
                           autocomplete="off">
                </div>
            </div>

            <div>
                <label class="{{ $labelClass }}">تا تاریخ</label>
                <div class="relative">
                    <div
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="text" name="to_date" data-jdp data-jdp-only-date placeholder="انتخاب تاریخ"
                           value="{{ request('to_date') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 pr-11 pl-4 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-start placeholder:text-gray-400 cursor-pointer"
                           autocomplete="off">
                </div>
            </div>

            <div>
                <label class="{{ $labelClass }}">نوع تراکنش</label>
                <select name="type"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="all" @selected(request('type') === 'all' || !request('type'))>همه انواع</option>
                    @foreach($availableTypes as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="{{ $labelClass }}">تغییر اعتبار</label>
                <select name="increase"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="all" @selected(request('increase') === 'all' || !request('increase'))>همه موارد
                    </option>
                    <option value="1" @selected(request('increase') === '1' || request('increase') === 'افزایش')>افزایش
                        (+)
                    </option>
                    <option value="0" @selected(request('increase') === '0' || request('increase') === 'کاهش')>کاهش
                        (-)
                    </option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 cursor-pointer">
                    اعمال فیلتر
                </button>
                @if(request()->anyFilled(['from_date', 'to_date', 'username', 'type', 'increase']) && (request('from_date') || request('to_date') || request('username') || (request('type') && request('type') !== 'all') || (request('increase') && request('increase') !== 'all')))
                    <a href="{{ route('user.smsreseller.user_credits') }}"
                       class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all shrink-0 cursor-pointer"
                       title="پاک‌سازی فیلترها">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
        @if($transactions->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">کاربر</th>
                        <th class="px-6 py-4 text-start">نوع تراکنش</th>
                        <th class="px-6 py-4 text-center">تغییر اعتبار</th>
                        <th class="px-6 py-4 text-start">مبلغ تراکنش</th>
                        <th class="px-6 py-4 text-start">مانده اعتبار پس از تراکنش</th>
                        <th class="px-6 py-4 text-start">تاریخ و زمان</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($transactions as $trx)
                        @php
                            $trxUserName = $trx->user_name ?? ($trx['userName'] ?? ($trx->user_id ?? ($trx['userId'] ?? '—')));
                            $trxType = $trx->type ?? ($trx['type'] ?? '—');
                            $trxDate = $trx->jalali_date ?? ($trx['createDate'] ?? '—');
                            $isIncrease = !empty($trx->increase) || !empty($trx['increase']);

                            $rawAmount = $trx->amount ?? ($trx['amount'] ?? 0);
                            $rawCredit = $trx->current_credit ?? ($trx['currentCredit'] ?? 0);
                            $amountClean = is_numeric($rawAmount) ? (float)$rawAmount : (float)str_replace(',', '', (string)$rawAmount);
                            $creditClean = is_numeric($rawCredit) ? (float)$rawCredit : (float)str_replace(',', '', (string)$rawCredit);
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span
                                        class="font-mono font-bold text-gray-900 dark:text-white text-xs select-all dir-ltr"
                                        dir="ltr">
                                            {{ $trxUserName }}
                                        </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-300">
                                {{ $trxType }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($isIncrease)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            افزایش اعتبار
                                        </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            کاهش اعتبار
                                        </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="text-sm font-black {{ $isIncrease ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $isIncrease ? '+' : '-' }} {{ $faNum(number_format($amountClean)) }} <span
                                            class="text-xs font-normal text-gray-400">ریال</span>
                                    </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">
                                        {{ $faNum(number_format($creditClean)) }} <span
                                            class="text-xs font-normal text-gray-400">ریال</span>
                                    </span>
                            </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500 dark:text-gray-400">
                                    <span class="dir-ltr inline-block" dir="ltr">
                                        {{ $faNum($trxDate) }}
                                    </span>
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
                            class="text-gray-800 dark:text-gray-200">{{ $faNum($transactions->firstItem() ?? 0) }}</strong>
                        تا
                        <strong
                            class="text-gray-800 dark:text-gray-200">{{ $faNum($transactions->lastItem() ?? 0) }}</strong>
                        از
                        <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($transactions->total()) }}</strong>
                        تراکنش
                    </span>
                </div>

                @if($transactions->hasPages())
                    <div>
                        {{ $transactions->withQueryString()->links('smsreseller::partials.pagination') }}
                    </div>
                @endif
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ تراکنشی یافت نشد</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        با توجه به فیلترهای اعمال شده، موردی برای نمایش وجود ندارد. می‌توانید فیلترها را تغییر داده یا
                        پاک‌سازی کنید.
                    </p>
                </div>
                @if(request()->anyFilled(['from_date', 'to_date', 'username', 'type', 'increase']))
                    <a href="{{ route('user.smsreseller.user_credits') }}"
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
