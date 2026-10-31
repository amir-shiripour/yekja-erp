@extends('layouts.user')

@section('title', 'پرونده سودآوری کاربر - ' . ($client ? $client->full_name : $username))

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
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
        activeTab: '{{ request('credits_page') ? 'credits' : 'payments' }}',
        showIncomeModal: false,
        paymentId: null,
        userName: '',
        amount: 0,
        tax: 0,
        totalAmount: 0,
        docDate: '',
        description: '',
        openIncomeModal(pay) {
            this.paymentId = pay.id;
            this.userName = pay.user_name || 'کاربر';
            this.amount = Number(pay.amount);
            this.tax = Number(pay.tax || 0);
            this.totalAmount = this.amount + this.tax;
            this.docDate = pay.jalali_date ? pay.jalali_date.split(' ')[0] : '{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}';
            this.description = 'سند درآمد حاصل از شارژ پیامک کاربر ' + this.userName + ' به مبلغ ' + this.totalAmount.toLocaleString('fa-IR') + ' ریال';
            this.showIncomeModal = true;
        },
        copied: false,
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
     }">

    @include('smsreseller::partials.toast')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('user.dashboard') }}" class="hover:text-indigo-600 transition-colors">پیشخوان</a>
                <span>/</span>
                <a href="{{ route('user.smsreseller.index') }}" class="hover:text-indigo-600 transition-colors">ریسیلر
                    پیامک</a>
                <span>/</span>
                <a href="{{ route('user.smsreseller.user_profitability') }}"
                   class="hover:text-indigo-600 transition-colors">سودآوری مشتریان</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-200 font-bold">جزئیات عملکرد {{ $username }}</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <span>پرونده عملکرد و سودآوری: {{ $client ? $client->full_name : $username }}</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('user.smsreseller.user_profitability') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-xs sm:text-sm font-bold transition-all shadow-2xs active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                <span>بازگشت به سودآوری مشتریان</span>
            </a>

            @if($client)
                <a href="{{ route('user.clients.show', $client->id) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-950/40 dark:text-purple-400 dark:hover:bg-purple-900/50 text-xs sm:text-sm font-bold transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>پرونده مشتری در CRM</span>
                </a>
            @endif
        </div>
    </div>
    <div class="{{ $cardClass }} p-6 sm:p-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div
                    class="w-16 h-16 rounded-3xl @if($client) bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/20 @else bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 @endif flex items-center justify-center shrink-0 font-black text-2xl shadow-sm">
                    @if($client)
                        {{ mb_substr($client->full_name, 0, 1) }}
                    @else
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>
                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">
                            {{ $client ? $client->full_name : 'کاربر مهمان پیامک' }}
                        </h2>
                        @if($client)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                مشتری متصل به CRM
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                کاربر مستقل پیامک
                            </span>
                        @endif

                        @if($userRank)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-black @if($userRank === 1) bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-300/40 @elseif($userRank <= 3) bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 @else bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 @endif">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.496m5.007 0a7.5 7.5 0 00-5.007 0m5.007 0V8.25a2.25 2.25 0 00-2.25-2.25h-0.514a2.25 2.25 0 00-2.25 2.25v2.25m-3.75-2.25h-.75a2.25 2.25 0 00-2.25 2.25v.75a4.5 4.5 0 004.5 4.5m10.5-7.5h.75a2.25 2.25 0 012.25 2.25v.75a4.5 4.5 0 01-4.5 4.5"/>
                                </svg>
                                <span>رتبه {{ $faNum($userRank) }} سودآوری از {{ $faNum($totalRankedUsers) }} مشتری</span>
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <button type="button"
                                @click="copyToClipboard('{{ $username }}')"
                                class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer"
                                title="کلیک برای کپی نام کاربری">
                            <span class="dir-ltr font-black select-all">{{ $username }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="copied" x-cloak class="text-[10px] text-emerald-600 font-bold">کپی شد!</span>
                        </button>

                        @if($client && $client->phone && $client->phone !== $username)
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <span>تلفن تماس: <strong
                                    class="dir-ltr text-gray-700 dark:text-gray-300 font-bold">{{ $client->phone }}</strong></span>
                        @endif

                        @if($client && $client->email)
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <span>ایمیل: <strong
                                    class="dir-ltr text-gray-700 dark:text-gray-300 font-bold">{{ $client->email }}</strong></span>
                        @endif

                        @if($lastPayment)
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <span>آخرین پرداخت: <strong
                                    class="text-gray-700 dark:text-gray-300">{{ $faNum($lastPayment->jalali_date) }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 self-end lg:self-center">
                <a href="{{ route('user.smsreseller.user_payments', ['username' => $username, 'state' => 'all']) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-50 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-gray-700/50 dark:text-gray-300 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-400 text-xs font-bold transition-all shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>فیلتر واریزی‌ها</span>
                </a>

                <a href="{{ route('user.smsreseller.user_credits', ['username' => $username]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-50 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:bg-gray-700/50 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 text-xs font-bold transition-all shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>فیلتر گردش اعتبار</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="{{ $cardClass }} p-5 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">کل خرید شارژ (فروش ما)</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $faNum(number_format($totalIncome)) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تراکنش‌های واریز:</span>
                <span
                    class="font-black text-emerald-600 dark:text-emerald-400">{{ $faNum($paymentsCount) }} واریز موفق</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-5 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">هزینه خرید پیامک (سرور)</p>
                    <p class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ $faNum(number_format($estimatedCost)) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>سهم هزینه از فروش:</span>
                <span class="font-bold text-rose-600 dark:text-rose-400">{{ $faNum(number_format(100 - $netMarginPercent, 1)) }}%</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-5 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">سود خالص کسب‌شده</p>
                    <p class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        + {{ $faNum(number_format($netProfit)) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>حاشیه سود خالص:</span>
                <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $faNum(number_format($netMarginPercent, 1)) }}%</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-5 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">خالص مصرف پنل پیامک</p>
                    <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ $faNum(number_format($netConsumed)) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>برگشتی از بلک‌لیست:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">+{{ $faNum(number_format($totalRefunds)) }} ریال</span>
            </div>
        </div>

        <div class="{{ $cardClass }} p-5 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">آخرین مانده اعتبار پنل</p>
                    <p class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1">
                        {{ $faNum(number_format($currentCredit)) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>هدایا و شارژ دستی:</span>
                <span class="font-bold text-purple-600 dark:text-purple-400">{{ $faNum(number_format($manualGifts)) }} ریال</span>
            </div>
        </div>
    </div>

    <div class="{{ $cardClass }} p-6 sm:p-7">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50 pb-4 mb-6">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">شفافیت گردش پنل، ارسال‌ها و مبالغ
                        بازگشتی</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تفکیک ارسال ناخالص، برگشتی‌های خودکار
                        بلک‌لیست مخابرات و مصرف قطعی</p>
                </div>
            </div>
            <span class="text-xs font-mono text-gray-400">{{ $username }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">کل ارسال‌های اولیه (ناخالص):</span>
                <p class="text-base font-black text-gray-900 dark:text-white">
                    {{ $faNum(number_format($grossSends)) }} <span class="text-xs font-normal text-gray-400">ریال</span>
                </p>
                <p class="text-[11px] text-gray-400">در قالب {{ $faNum($sendCount) }} بار ارسال درخواست پیامک</p>
            </div>

            <div
                class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 space-y-1">
                <span
                    class="text-xs font-bold text-emerald-700 dark:text-emerald-400">↩ برگشتی بلک‌لیست و نرسیده:</span>
                <p class="text-base font-black text-emerald-600 dark:text-emerald-400">
                    + {{ $faNum(number_format($totalRefunds)) }} <span class="text-xs font-normal text-emerald-600/70">ریال</span>
                </p>
                <p class="text-[11px] text-emerald-600/70">{{ $faNum($refundCount) }} بار بازگشت اعتبار به کیف پول
                    کاربر</p>
            </div>

            <div
                class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 space-y-1">
                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400">= خالص مصرف واقعی:</span>
                <p class="text-base font-black text-indigo-600 dark:text-indigo-400">
                    {{ $faNum(number_format($netConsumed)) }} <span
                        class="text-xs font-normal text-indigo-600/70">ریال</span>
                </p>
                <p class="text-[11px] text-indigo-600/70">ارسال ناخالص منهای برگشتی‌های ناموفق</p>
            </div>

            <div
                class="p-4 rounded-2xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/30 space-y-1">
                <span class="text-xs font-bold text-purple-700 dark:text-purple-400"> شارژ دستی / هدایا:</span>
                <p class="text-base font-black text-purple-600 dark:text-purple-400">
                    {{ $faNum(number_format($manualGifts)) }} <span
                        class="text-xs font-normal text-purple-600/70">ریال</span>
                </p>
                <p class="text-[11px] text-purple-600/70">{{ $faNum($giftCount) }} تراکنش شارژ دستی یا انتقال پنل</p>
            </div>
        </div>

        @php
            $refundRatio = $grossSends > 0 ? ($totalRefunds / $grossSends) * 100 : 0;
            $netConsumedRatio = $grossSends > 0 ? ($netConsumed / $grossSends) * 100 : 100;
        @endphp
        @if($grossSends > 0)
            <div class="mt-6 space-y-2">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>نسبت موفقیت ارسال‌ها: <strong>{{ $faNum(number_format($netConsumedRatio, 1)) }}%</strong> تحویل قطعی</span>
                    <span>نرخ برگشت بلک‌لیست: <strong class="text-emerald-600 dark:text-emerald-400">{{ $faNum(number_format($refundRatio, 1)) }}%</strong></span>
                </div>
                <div class="w-full h-3 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden flex">
                    <div class="bg-indigo-600 h-full transition-all duration-500"
                         style="width: {{ $netConsumedRatio }}%"
                         title="مصرف خالص: {{ number_format($netConsumedRatio, 1) }}%"></div>
                    <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $refundRatio }}%"
                         title="برگشتی بلک‌لیست: {{ number_format($refundRatio, 1) }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700/80 pb-3">
            <button type="button"
                    @click="activeTab = 'payments'"
                    :class="activeTab === 'payments' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>سوابق پرداخت‌ها و شارژهای اینترنتی</span>
                <span
                    :class="activeTab === 'payments' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold">
                    {{ $faNum($payments->total()) }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'credits'"
                    :class="activeTab === 'credits' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>لاگ گردش اعتبار و تراکنش‌های پنل</span>
                <span
                    :class="activeTab === 'credits' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold">
                    {{ $faNum($credits->total()) }}
                </span>
            </button>
        </div>
        <div x-show="activeTab === 'payments'" x-cloak>
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">جدول سوابق شارژ اینترنتی و
                            پرداخت‌های کاربر</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشاهده جزئیات مبالغ، مالیات، درگاه و
                            ثبت سند حسابداری درآمد</p>
                    </div>
                </div>

                @if($payments->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                            <thead
                                class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                            <tr>
                                <th class="px-6 py-4 text-start">شناسه / درگاه</th>
                                <th class="px-6 py-4 text-start">مبلغ پایه</th>
                                <th class="px-6 py-4 text-start">مالیات</th>
                                <th class="px-6 py-4 text-start">مبلغ کل واریزی</th>
                                <th class="px-6 py-4 text-start">وضعیت پرداخت</th>
                                <th class="px-6 py-4 text-start">تاریخ و زمان</th>
                                <th class="px-6 py-4 text-start">سند حسابداری</th>
                                <th class="px-6 py-4 text-end">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($payments as $p)
                                @php
                                    $sourceDoc = $sourceDocs[$p->id] ?? null;
                                    $snapshot = $sourceDoc ? json_decode($sourceDoc->snapshot, true) : null;
                                @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-xs font-mono text-gray-500 dark:text-gray-400 block dir-ltr text-start select-all">#{{ $p->id }}</span>
                                        <span
                                            class="text-xs font-bold text-gray-800 dark:text-gray-200 block mt-0.5">{{ $p->bank ?: 'درگاه آنلاین' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                        {{ $faNum(number_format($p->amount)) }} <span
                                            class="text-xs font-normal text-gray-400">ریال</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        @if($p->tax > 0)
                                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ $faNum(number_format($p->tax)) }} ریال</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-black text-emerald-600 dark:text-emerald-400">
                                        {{ $faNum(number_format($p->total_amount)) }} <span
                                            class="text-xs font-normal text-gray-400">ریال</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($p->state === 'واریز شده')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>واریز شده</span>
                                                </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    <span>{{ $p->state }}</span>
                                                </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500 dark:text-gray-400">
                                        <span class="dir-ltr inline-block"
                                              dir="ltr">{{ $faNum($p->jalali_date ?? '—') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sourceDoc)
                                            <a href="{{ route('user.accounting.documents.show', $sourceDoc->document_id) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-black transition-colors"
                                               title="مشاهده سند حسابداری">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span>سند {{ $faNum($snapshot['document_number'] ?? $sourceDoc->document_id) }}</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">ثبت نشده</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end">
                                        @if(!$sourceDoc && $p->state === 'واریز شده')
                                            <button type="button"
                                                    @click="openIncomeModal({{ json_encode($p) }})"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white text-xs font-black transition-all shadow-2xs active:scale-95 cursor-pointer"
                                                    title="ثبت خودکار سند درآمد در حسابداری">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 4v16m8-8H4"/>
                                                </svg>
                                                <span>ثبت سند درآمد</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
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
                                    class="text-gray-800 dark:text-gray-200">{{ $faNum($payments->firstItem() ?? 0) }}</strong>
                                تا
                                <strong
                                    class="text-gray-800 dark:text-gray-200">{{ $faNum($payments->lastItem() ?? 0) }}</strong>
                                از
                                <strong
                                    class="text-gray-800 dark:text-gray-200">{{ $faNum($payments->total()) }}</strong>
                                پرداخت
                            </span>
                        </div>

                        @if($payments->hasPages())
                            <div class="flex items-center justify-end sm:ms-auto">
                                {{ $payments->appends(request()->except('payments_page'))->links('smsreseller::partials.pagination') }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-12 text-center text-gray-400 space-y-2">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <p class="text-sm font-bold text-gray-600 dark:text-gray-300">هیچ سابقه پرداختی برای این کاربر
                            یافت نشد</p>
                    </div>
                @endif
            </div>
        </div>
        <div x-show="activeTab === 'credits'" x-cloak>
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">ریز تراکنش‌های اعتبار، مصرف و
                            برگشتی‌های پنل</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ثبت تاریخچه کامل افزایش و کاهش اعتبار
                            در سیستم</p>
                    </div>
                </div>

                @if($credits->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                            <thead
                                class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                            <tr>
                                <th class="px-6 py-4 text-start">شناسه</th>
                                <th class="px-6 py-4 text-start">نوع عملیات / شرح</th>
                                <th class="px-6 py-4 text-start">جهت گردش</th>
                                <th class="px-6 py-4 text-start">مبلغ تراکنش</th>
                                <th class="px-6 py-4 text-start">مانده پس از تراکنش</th>
                                <th class="px-6 py-4 text-start">تاریخ و زمان</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($credits as $c)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-400">
                                        #{{ $c->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-xs font-bold text-gray-800 dark:text-gray-200 block">{{ $c->type ?: 'تراکنش پنل' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($c->increase)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <span>افزایش اعتبار</span>
                                                </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M20 12H4"/>
                                                    </svg>
                                                    <span>کسر اعتبار (ارسال)</span>
                                                </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-black @if($c->increase) text-emerald-600 dark:text-emerald-400 @else text-rose-600 dark:text-rose-400 @endif">
                                        @if($c->increase)
                                            +@else-
                                        @endif{{ $faNum(number_format($c->amount)) }} <span
                                            class="text-xs font-normal text-gray-400">ریال</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700 dark:text-gray-300">
                                        {{ $faNum(number_format($c->current_credit)) }} ریال
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500 dark:text-gray-400">
                                        <span class="dir-ltr inline-block"
                                              dir="ltr">{{ $faNum($c->jalali_date ?? '—') }}</span>
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
                                    class="text-gray-800 dark:text-gray-200">{{ $faNum($credits->firstItem() ?? 0) }}</strong>
                                تا
                                <strong
                                    class="text-gray-800 dark:text-gray-200">{{ $faNum($credits->lastItem() ?? 0) }}</strong>
                                از
                                <strong
                                    class="text-gray-800 dark:text-gray-200">{{ $faNum($credits->total()) }}</strong>
                                تراکنش اعتباری
                            </span>
                        </div>

                        @if($credits->hasPages())
                            <div class="flex items-center justify-end sm:ms-auto">
                                {{ $credits->appends(request()->except('credits_page'))->links('smsreseller::partials.pagination') }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-12 text-center text-gray-400 space-y-2">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm font-bold text-gray-600 dark:text-gray-300">هیچ تراکنش اعتباری برای این کاربر
                            یافت نشد</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('smsreseller::partials.income-document-modal')
</div>

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
                        hasSecond: false,
                        zIndex: 999999
                    });
                }
            }
        }, true);
        document.addEventListener('click', function (e) {
            if (e.target && (e.target.hasAttribute('data-jdp') || e.target.hasAttribute('data-jdp-only-date'))) {
                if (window.jalaliDatepicker) {
                    window.jalaliDatepicker.updateOptions({
                        date: true,
                        time: false,
                        hasSecond: false,
                        zIndex: 999999
                    });
                    window.jalaliDatepicker.show(e.target);
                }
            }
        }, true);
    })();
</script>
@endsection
