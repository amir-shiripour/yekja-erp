@extends('layouts.user')

@section('title', 'پیشخوان و سود مالی ریسیلر پیامک')

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
        syncing: false,
        toast: { show: false, message: '', type: 'success', timer: null },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 4000);
        }
     }">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('smsreseller::partials.toast')


    {{-- هدر صفحه و دکمه‌های عملیاتی --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                <span>پیشخوان مالی و سود ریسیلر پیامک</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                گزارش تجمعی درآمدهای دریافتی، هزینه‌های شارژ پنل و سود خالص نمایندگی
                @if($lastSync)
                    <span class="inline-flex items-center gap-1.5 ms-2 px-2.5 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-[11px] font-bold text-gray-600 dark:text-gray-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        آخرین همگام‌سازی: {{ $faNum(\Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($lastSync))->format('Y/m/d H:i')) }}
                    </span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('user.smsreseller.user_profitability') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>تحلیل مشتریان برتر</span>
            </a>

            <form action="{{ route('user.smsreseller.sync') }}" method="POST" @submit="syncing = true" class="inline">
                @csrf
                <button type="submit"
                        :disabled="syncing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-2xs active:scale-95 cursor-pointer disabled:opacity-60">
                    <template x-if="!syncing">
                        <svg class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </template>
                    <template x-if="syncing">
                        <svg class="animate-spin w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <span x-text="syncing ? 'در حال دریافت اطلاعات...' : 'دریافت دیتای جدید از سرور'"></span>
                </button>
            </form>

            <a href="{{ route('user.smsreseller.margin_calculator') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-95">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>محاسبه‌گر سود خطوط</span>
            </a>
        </div>
    </div>

    {{-- پیام‌های نشست فلش --}}
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ۴ کارت اصلی شاخص‌های آماری و مالی --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        {{-- ۱. دریافتی از کاربران --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">دریافتی از کاربران (فروش کل)</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $faNum(number_format($summaryStats->total_user_total ?? 0)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>اصل شارژ:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($summaryStats->total_user_amount ?? 0)) }} ریال</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تعداد دفعات واریز:</span>
                <span class="font-black text-gray-700 dark:text-gray-300">{{ $faNum(number_format($summaryStats->total_user_payments_count ?? 0)) }} تراکنش</span>
            </div>
        </div>

        {{-- ۲. شارژ پنل پیامک --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">شارژ پنل پیامک (هزینه‌ها)</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ $faNum(number_format($summaryStats->total_panel_total ?? 0)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>اصل پرداختی:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($summaryStats->total_panel_amount ?? 0)) }} ریال</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تعداد دفعات شارژ پنل:</span>
                <span class="font-black text-gray-700 dark:text-gray-300">{{ $faNum(number_format($summaryStats->total_panel_charges_count ?? 0)) }} بار</span>
            </div>
        </div>

        {{-- ۳. گردش اعتباری کاربران --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">تراکنش‌های اعتباری کاربران</p>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ $faNum(number_format($summaryStats->total_credit_tx_count ?? 0)) }} <span class="text-xs font-bold text-gray-400">مورد</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>مجموع افزایش:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($summaryStats->total_credit_increase_sum ?? 0)) }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>مجموع کاهش:</span>
                <span class="font-black text-gray-700 dark:text-gray-300">{{ $faNum(number_format($summaryStats->total_credit_decrease_sum ?? 0)) }}</span>
            </div>
        </div>

        {{-- ۴. سود خالص دوره --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">سود خالص (دوره انتخابی)</p>
                    <p class="text-2xl font-black @if(($summaryStats->total_profit ?? 0) >= 0) text-indigo-600 dark:text-indigo-400 @else text-rose-600 dark:text-rose-400 @endif mt-1">
                        {{ $faNum(number_format($summaryStats->total_profit ?? 0)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>فرمول:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">دریافتی منهای شارژ</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>وضعیت:</span>
                @if(($summaryStats->total_profit ?? 0) >= 0)
                    <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        سودآور
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-bold text-rose-600 dark:text-rose-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        زیان‌ده
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- کارت فیلترهای پیشرفته پیشخوان --}}
    <form action="{{ route('user.smsreseller.index') }}" method="GET"
          class="bg-white dark:bg-gray-800/60 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            {{-- دوره زمانی گزارش --}}
            <div class="lg:col-span-4">
                <label class="{{ $labelClass }}">دوره زمانی گزارش</label>
                <select name="period"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="month" @selected($period === 'month')>این ماه (ماه جاری)</option>
                    <option value="day" @selected($period === 'day')>امروز</option>
                    <option value="week" @selected($period === 'week')>۷ روز اخیر</option>
                    <option value="year" @selected($period === 'year')>امسال</option>
                    <option value="all" @selected($period === 'all')>کل تاریخچه</option>
                    <option value="custom" @selected($period === 'custom')>بازه انتخابی (از / تا تاریخ)</option>
                </select>
            </div>

            {{-- از تاریخ --}}
            <div class="lg:col-span-3">
                <label class="{{ $labelClass }}">از تاریخ</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="text" name="from_date" data-jdp data-jdp-only-date placeholder="انتخاب تاریخ" value="{{ $customFromDate }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 pr-11 pl-4 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-start placeholder:text-gray-400 cursor-pointer" autocomplete="off">
                </div>
            </div>

            {{-- تا تاریخ --}}
            <div class="lg:col-span-3">
                <label class="{{ $labelClass }}">تا تاریخ</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="text" name="to_date" data-jdp data-jdp-only-date placeholder="انتخاب تاریخ" value="{{ $customToDate }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 pr-11 pl-4 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-start placeholder:text-gray-400 cursor-pointer" autocomplete="off">
                </div>
            </div>

            {{-- دکمه‌ها --}}
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 cursor-pointer">
                    اعمال فیلتر
                </button>
                @if(($period && $period !== 'all' && $period !== 'month') || $customFromDate || $customToDate)
                    <a href="{{ route('user.smsreseller.index') }}"
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

    {{-- جدول تفکیکی عملکرد ماه‌ها --}}
    <div class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>گزارش تفکیکی عملکرد ماه‌ها</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده خلاصه واریزی‌های کاربران، هزینه‌های شارژ پنل و سود نهایی به تفکیک دوره</p>
            </div>
        </div>

        @if(count($monthlyReports))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                        <tr>
                            <th class="px-6 py-4 text-start">ماه / دوره</th>
                            <th class="px-6 py-4 text-start">دریافتی از کاربران (کل)</th>
                            <th class="px-6 py-4 text-start">شارژ پنل پیامک (کل)</th>
                            <th class="px-6 py-4 text-center">تراکنش‌ها</th>
                            <th class="px-6 py-4 text-start">سود خالص دوره</th>
                            <th class="px-6 py-4 text-end">مشاهده جزئیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach($monthlyReports as $item)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                                {{-- ماه --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs shrink-0">
                                            {{ $faNum(substr($item['month_key'], 5, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white block text-sm">
                                                 {{ $item['month_title'] }}
                                            </span>
                                            @if($item['is_current_month'] ?? false)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    ماه جاری
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- دریافتی از کاربران --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 block">
                                        {{ $faNum(number_format($item['user_total'])) }} ریال
                                    </span>
                                    <span class="text-[11px] text-gray-400 block mt-0.5">
                                        اصل: {{ $faNum(number_format($item['user_amount'])) }} | {{ $faNum($item['user_count']) }} واریزی
                                    </span>
                                </td>

                                {{-- شارژ پنل --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-rose-600 dark:text-rose-400 block">
                                        {{ $faNum(number_format($item['panel_total'])) }} ریال
                                    </span>
                                    <span class="text-[11px] text-gray-400 block mt-0.5">
                                        اصل: {{ $faNum(number_format($item['panel_amount'])) }} | {{ $faNum($item['panel_count']) }} بار شارژ
                                    </span>
                                </td>

                                {{-- تراکنش‌ها --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-lg border border-gray-200/60 dark:border-gray-600/50">
                                        {{ $faNum(number_format($item['credit_tx_count'])) }} مورد
                                    </span>
                                </td>

                                {{-- سود خالص --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item['net_profit'] >= 0)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            + {{ $faNum(number_format($item['net_profit'])) }} ریال
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            - {{ $faNum(number_format(abs($item['net_profit']))) }} ریال
                                        </span>
                                    @endif
                                </td>

                                {{-- عملیات و لینک‌ها --}}
                                <td class="px-6 py-4 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('user.smsreseller.user_payments', ['from_date' => $item['month_key'] . '/01', 'to_date' => $item['month_key'] . '/31', 'state' => 'واریز شده']) }}"
                                           class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 transition-colors"
                                           title="مشاهده واریزی‌های کاربران این ماه">
                                            واریزی‌ها
                                        </a>
                                        <a href="{{ route('user.smsreseller.panel_payments', ['from_date' => $item['month_key'] . '/01', 'to_date' => $item['month_key'] . '/31', 'state' => 'واریز شده']) }}"
                                           class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 transition-colors"
                                           title="مشاهده شارژهای پنل اصلی این ماه">
                                            شارژ‌ها
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- فوتر صفحه‌بندی --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        نمایش
                        <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($monthlyReports->firstItem() ?? 0) }}</strong>
                        تا
                        <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($monthlyReports->lastItem() ?? 0) }}</strong>
                        از
                        <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($monthlyReports->total()) }}</strong>
                        ماه / دوره
                    </span>
                </div>

                @if($monthlyReports->hasPages())
                    <div>
                        {{ $monthlyReports->withQueryString()->links('smsreseller::partials.pagination') }}
                    </div>
                @endif
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">اطلاعاتی برای دوره زمانی انتخابی یافت نشد</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        می‌توانید بازه تاریخ یا دوره زمانی را تغییر داده یا از دکمه «دریافت دیتای جدید از سرور» استفاده کنید.
                    </p>
                </div>
                @if($period === 'custom')
                    <a href="{{ route('user.smsreseller.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        بازگشت به پیش‌فرض
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@include('smsreseller::partials.confirm-modal')
@include('partials.jalali-date-picker')
<script>
    (function() {
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
