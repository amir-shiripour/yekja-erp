@extends('layouts.user')

@section('title', 'دریافتی‌ها و پرداخت‌های کاربران (درآمدها)')

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
        }
     }">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('smsreseller::partials.toast')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-indigo-600 text-white shadow-lg shadow-emerald-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </span>
                <span>پرداخت‌ها و دریافتی‌های کاربران (درآمدها)</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                گزارش تمام شارژ‌ها و پرداخت‌های اینترنتی کاربران همراه با امکان ثبت خودکار سند درآمد در حسابداری
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.smsreseller.user_credits') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>گردش اعتباری کاربران</span>
            </a>

            <a href="{{ route('user.smsreseller.panel_payments') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-95">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>شارژهای پنل اصلی (هزینه‌ها)</span>
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

    @if(session('error'))
        <div
            class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">جمع کل واریزی (اصل + مالیات)</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $faNum(number_format($sumAmount + $sumTax)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>اصل مبالغ:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($sumAmount)) }} ریال</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>جریان نقدی ورودی:</span>
                <span class="font-black text-emerald-600 dark:text-emerald-400">شارژ آنلاین کاربران</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">مالیات بر ارزش افزوده دریافتی</p>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ $faNum(number_format($sumTax)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>درصد قانونی:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">سند بدهی مالیاتی</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تراز مالیاتی:</span>
                <span class="font-black text-amber-600 dark:text-amber-400">قابل کسر در ترازنامه</span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">تراکنش‌های واریز موفق</p>
                    <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ $faNum(number_format($depositedCount)) }} <span
                            class="text-xs font-bold text-gray-400">مورد</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>وضعیت:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">واریز شده به حساب</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>نرخ موفقیت:</span>
                <span class="font-black text-indigo-600 dark:text-indigo-400">
                    {{ $payments->total() > 0 ? $faNum(number_format(($depositedCount / $payments->total()) * 100, 1)) . '%' : '—' }}
                </span>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">تعداد کل فاکتورها / رکوردها</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $faNum(number_format($payments->total())) }} <span class="text-xs font-bold text-gray-400">ردیف</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>صفحه‌بندی:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">صفحه {{ $faNum($payments->currentPage()) }} از {{ $faNum($payments->lastPage()) }}</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>نمایش:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum($payments->count()) }} رکورد در این صفحه</span>
            </div>
        </div>
    </div>
    <form action="{{ route('user.smsreseller.user_payments') }}" method="GET"
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
                <label class="{{ $labelClass }}">وضعیت پرداخت</label>
                <select name="state"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="all">همه وضعیت‌ها</option>
                    @foreach($availableStates as $st)
                        <option value="{{ $st }}" @selected(request('state') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">نوع پرداخت</label>
                <select name="type"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="all">همه انواع</option>
                    @foreach($availableTypes as $tp)
                        <option value="{{ $tp }}" @selected(request('type') === $tp)>{{ $tp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 cursor-pointer">
                    اعمال فیلتر
                </button>
                @if(request()->anyFilled(['username', 'from_date', 'to_date', 'state', 'type']))
                    <a href="{{ route('user.smsreseller.user_payments') }}"
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
        @if($payments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">کاربر</th>
                        <th class="px-6 py-4 text-start">درگاه / بانک</th>
                        <th class="px-6 py-4 text-start">نوع پرداخت</th>
                        <th class="px-6 py-4 text-center">وضعیت</th>
                        <th class="px-6 py-4 text-start">مبلغ شارژ</th>
                        <th class="px-6 py-4 text-start">مالیات</th>
                        <th class="px-6 py-4 text-start">مبلغ کل واریزی</th>
                        <th class="px-6 py-4 text-start">تاریخ و زمان</th>
                        <th class="px-6 py-4 text-center">سند حسابداری</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($payments as $pay)
                        @php
                            $amountNum = (float)$pay->amount;
                            $taxNum = (float)$pay->tax;
                            $totalNum = (float)$pay->total_amount;
                            $docSource = $sourceDocs[$pay->id] ?? null;
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
                                            {{ $pay->user_name ?? ($pay->user_id ?? '—') }}
                                        </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-bold">
                                {{ $pay->bank ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400 font-bold">
                                {{ $pay->type ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($pay->state === 'واریز شده')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            واریز شده
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $pay->state ?? 'نامشخص' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900 dark:text-white">
                                    {{ $faNum(number_format($amountNum)) }} <span
                                        class="text-xs font-normal text-gray-400">ریال</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-600 dark:text-amber-400 font-black">
                                    {{ $faNum(number_format($taxNum)) }} <span
                                        class="text-xs font-normal text-gray-400">ریال</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                        {{ $faNum(number_format($totalNum)) }} <span
                                            class="text-xs font-normal text-gray-400">ریال</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500 dark:text-gray-400">
                                    <span class="dir-ltr inline-block" dir="ltr">
                                        {{ $faNum($pay->jalali_date ?? '—') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($docSource)
                                        @php $snap = json_decode($docSource->snapshot, true); @endphp
                                        <a href="{{ route('admin.accounting.documents.show', $docSource->document_id) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800 hover:bg-indigo-100 transition-colors shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>سند #{{ $faNum($snap['document_number'] ?? $docSource->document_id) }}</span>
                                        </a>
                                    @else
                                        <button type="button"
                                                @click="openIncomeModal({{ json_encode($pay) }})"
                                                style="background-color: #059669 !important; color: #ffffff !important;"
                                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-black bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-500/20 active:scale-95 transition-all cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <span>ثبت سند درآمد</span>
                                        </button>
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
                        <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($payments->total()) }}</strong>
                        پرداخت
                    </span>
                </div>

                @if($payments->hasPages())
                    <div>
                        {{ $payments->withQueryString()->links('smsreseller::partials.pagination') }}
                    </div>
                @endif
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ پرداختی یافت نشد</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        با توجه به فیلترهای اعمال شده، پرداختی برای نمایش وجود ندارد. می‌توانید فیلترها را تغییر داده یا
                        پاک‌سازی کنید.
                    </p>
                </div>
                @if(request()->anyFilled(['username', 'from_date', 'to_date', 'state', 'type']))
                    <a href="{{ route('user.smsreseller.user_payments') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        پاک‌سازی تمام فیلترها
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('smsreseller::partials.income-document-modal')
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
