@extends('layouts.user')

@section('title', 'محاسبه لحظه‌ای حاشیه سود اپراتورهای پیامک')

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
        showEditPricesModal: false,
        showSimulatorModal: false,
        operators: {{ json_encode($margins) }},
        activeOp: '{{ array_key_first($margins) ?? 'op_1000' }}',
        calcCount: 50000,
        editPrices: {
            @foreach($margins as $opKey => $m)
                '{{ $opKey }}': {{ (int)$m['selling_price'] }},
            @endforeach
        },
        editDisplayPrices: {
            @foreach($margins as $opKey => $m)
                '{{ $opKey }}': '{{ number_format($m['selling_price']) }}',
            @endforeach
        },
        formatPriceInput(event, opKey) {
            let input = event.target;
            let oldVal = input.value;
            let cursor = input.selectionStart;
            let digitsBeforeCursor = oldVal.slice(0, cursor).replace(/\D/g, '').length;

            // Convert Persian/Arabic digits and strip non-digits
            let raw = oldVal
                .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                .replace(/[^0-9]/g, '');

            if (raw === '') {
                this.editPrices[opKey] = 0;
                this.editDisplayPrices[opKey] = '';
                input.value = '';
                return;
            }

            let num = parseInt(raw, 10);
            this.editPrices[opKey] = num;
            let formatted = num.toLocaleString('en-US');
            this.editDisplayPrices[opKey] = formatted;
            input.value = formatted;

            // Maintain smart cursor position
            let newCursor = 0;
            let digitCount = 0;
            for (let i = 0; i < formatted.length; i++) {
                if (/\d/.test(formatted[i])) {
                    digitCount++;
                }
                if (digitCount === digitsBeforeCursor) {
                    newCursor = i + 1;
                    break;
                }
            }
            if (digitCount < digitsBeforeCursor) {
                newCursor = formatted.length;
            }
            input.setSelectionRange(newCursor, newCursor);
        },
        formatCalcCount(event) {
            let input = event.target;
            let oldVal = input.value;
            let cursor = input.selectionStart;
            let digitsBeforeCursor = oldVal.slice(0, cursor).replace(/\D/g, '').length;

            let raw = oldVal
                .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                .replace(/[^0-9]/g, '');

            if (raw === '') {
                this.calcCount = 0;
                input.value = '';
                return;
            }

            let num = parseInt(raw, 10);
            this.calcCount = num;
            let formatted = num.toLocaleString('en-US');
            input.value = formatted;

            let newCursor = 0;
            let digitCount = 0;
            for (let i = 0; i < formatted.length; i++) {
                if (/\d/.test(formatted[i])) {
                    digitCount++;
                }
                if (digitCount === digitsBeforeCursor) {
                    newCursor = i + 1;
                    break;
                }
            }
            if (digitCount < digitsBeforeCursor) {
                newCursor = formatted.length;
            }
            input.setSelectionRange(newCursor, newCursor);
        }
     }">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('smsreseller::partials.toast')

    {{-- هدر صفحه --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
                <span>محاسبه‌گر زنده و تحلیلی حاشیه سود اپراتورها</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                آنالیز نرخ خرید زنده سرور، قیمت مصوب فروش و حاشیه سود هر خط پیامکی
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>نرخ‌های خرید: آنلاین از سرور</span>
            </span>

            {{-- دکمه باز کردن شبیه‌ساز در مودال --}}
            <button type="button"
                    @click="showSimulatorModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-xs sm:text-sm font-bold transition-all shadow-2xs active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>شبیه‌ساز سود تیراژ</span>
            </button>

            {{-- دکمه ویرایش نرخ‌های فروش --}}
            <button type="button"
                    @click="showEditPricesModal = true"
                    style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important; color: #ffffff !important;"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 active:scale-95 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>تنظیم نرخ‌های فروش</span>
            </button>
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

    {{-- ۴ کارت آماری وضعیت مارجین کل --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        {{-- میانگین درصد مارجین --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">میانگین درصد حاشیه سود</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $faNum(number_format($kpis['avg_margin_percent'], 1)) }}%
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>تراز مارجین:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">میانگین تمام خطوط فعال</span>
            </div>
        </div>

        {{-- پرسودترین خط --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">پرسودترین خط / اپراتور</p>
                    <p class="text-lg font-black text-amber-600 dark:text-amber-400 mt-1 truncate" title="{{ $kpis['most_profitable_op_title'] }}">
                        {{ $kpis['most_profitable_op_title'] }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>بیشترین مارجین:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $faNum(number_format($kpis['most_profitable_op_margin'], 1)) }}%</span>
            </div>
        </div>

        {{-- محبوب‌ترین خطوط --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">خطوط پرکاربرد پیشنهادی</p>
                    <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        اپراتور ۱۰۰۰ و سیم‌کارت
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>دامنه نرخ خرید:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum('1,350') }} تا {{ $faNum('1,573') }} ریال</span>
            </div>
        </div>

        {{-- موجودی زنده کیف پول --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">موجودی زنده کیف پول شما</p>
                    <p class="text-2xl font-black text-violet-600 dark:text-violet-400 mt-1">
                        {{ $faNum(number_format($onlineBalance)) }} <span class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>توان ارسال تخمینی:</span>
                <span class="font-black text-violet-600 dark:text-violet-400">~{{ $faNum(number_format($smsCount)) }} پیامک</span>
            </div>
        </div>
    </div>

    {{-- جدول اصلی و تحلیلی نرخ‌ها (نمای تمیز بدون اینپوت در ردیف‌ها) --}}
    <div class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 overflow-hidden shadow-sm backdrop-blur-xl">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>جدول تحلیلی نرخ خرید، قیمت فروش و مارجین سود هر خط</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده نرخ زنده خرید سرور، قیمت مصوب فروش و محاسبه درصد سود خالص به ازای هر پیامک</p>
            </div>

            <button type="button"
                    @click="showEditPricesModal = true"
                    style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important; color: #ffffff !important;"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 active:scale-95 transition-all shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>ویرایش نرخ‌های فروش</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                <thead class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">نام اپراتور / نوع خط</th>
                        <th class="px-6 py-4 text-start">نرخ خرید شما (سرور)</th>
                        <th class="px-6 py-4 text-start">قیمت مصوب فروش</th>
                        <th class="px-6 py-4 text-start">سود هر پیامک</th>
                        <th class="px-6 py-4 text-start">درصد مارجین سود</th>
                        <th class="px-6 py-4 text-start">سود در ۱۰۰ هزار پیامک</th>
                        <th class="px-6 py-4 text-start">وضعیت سوددهی</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($margins as $opKey => $m)
                        @php
                            $marginVal = $m['margin_percent'];
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            {{-- نام اپراتور --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white block text-sm">{{ $m['title'] }}</span>
                                        <span class="text-[11px] font-mono text-gray-400 dir-ltr block text-start">{{ $opKey }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- نرخ خرید --}}
                            <td class="px-6 py-4 whitespace-nowrap font-black text-rose-600 dark:text-rose-400">
                                {{ $faNum(number_format($m['buy_price'])) }} <span class="text-xs font-normal text-gray-400">ریال</span>
                            </td>

                            {{-- قیمت فروش (نمایش تمیز به جای اینپوت خام) --}}
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 dark:text-white">
                                <span class="inline-flex items-center gap-1">
                                    <span>{{ $faNum(number_format($m['selling_price'])) }}</span>
                                    <span class="text-xs font-normal text-gray-400">ریال</span>
                                </span>
                            </td>

                            {{-- سود هر پیامک --}}
                            <td class="px-6 py-4 whitespace-nowrap font-black text-emerald-600 dark:text-emerald-400">
                                + {{ $faNum(number_format($m['profit_per_sms'])) }} <span class="text-xs font-normal text-gray-400">ریال</span>
                            </td>

                            {{-- درصد مارجین با نوار پیشرفت شکیل --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-20 bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-300"
                                             style="width: {{ min(100, max(0, $marginVal)) }}%"></div>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                        {{ $faNum(number_format($marginVal, 1)) }}%
                                    </span>
                                </div>
                            </td>

                            {{-- سود در ۱۰۰ هزار --}}
                            <td class="px-6 py-4 whitespace-nowrap font-black text-indigo-600 dark:text-indigo-400">
                                {{ $faNum(number_format(100000 * $m['profit_per_sms'])) }} <span class="text-xs font-normal text-gray-400">ریال</span>
                            </td>

                            {{-- وضعیت سوددهی --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($marginVal >= 30)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>مارجین عالی</span>
                                    </span>
                                @elseif($marginVal >= 15)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        <span>سود استاندارد</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>حاشیه سود کم</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 sm:p-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
            <span>نرخ‌های فروش برای کلیه کاربران و پنل‌های زیرمجموعه اعمال می‌شود. برای اصلاح هر نرخ، روی دکمه «ویرایش نرخ‌های فروش» کلیک کنید.</span>
            <button type="button" @click="showEditPricesModal = true" class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 cursor-pointer self-start sm:self-auto">
                ویرایش نرخ‌ها &larr;
            </button>
        </div>
    </div>

    {{-- مودال ۱: تنظیم و ویرایش نرخ‌های فروش (تک دکمه ذخیره استاندارد و انیمیشن روان) --}}
    <div x-show="showEditPricesModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="edit-prices-modal-title" role="dialog" aria-modal="true"
         @keydown.escape.window="showEditPricesModal = false">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            {{-- بک‌دراپ تیره با ترنزیشن فید نرم --}}
            <div x-show="showEditPricesModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                 @click="showEditPricesModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- پنل اصلی مودال با ترنزیشن مقیاس و حرکت عمودی --}}
            <div x-show="showEditPricesModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom sm:align-middle bg-white dark:bg-gray-800 rounded-3xl text-start overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl w-full p-6 sm:p-8 border border-gray-100 dark:border-gray-700/80 space-y-6">

                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="edit-prices-modal-title" class="text-lg font-black text-gray-900 dark:text-white">تنظیم نرخ‌های فروش اپراتورهای پیامک</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">قیمت فروش هر پیامک به مشتری را مشخص کنید تا سود و مارجین به‌روزرسانی شود</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditPricesModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-xl transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('user.smsreseller.save_margin_prices') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="max-h-[55vh] overflow-y-auto pe-1.5 space-y-3.5 divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach($margins as $opKey => $m)
                            <div class="pt-3.5 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white block">{{ $m['title'] }}</span>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>خرید سرور:</span>
                                        <strong class="text-rose-600 dark:text-rose-400 font-bold">{{ $faNum(number_format($m['buy_price'])) }} ریال</strong>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3.5 self-end sm:self-center">
                                    {{-- هیدن اینپوت برای ارسال عدد خالص به سرور --}}
                                    <input type="hidden"
                                           name="selling_prices[{{ $opKey }}]"
                                           :value="editPrices['{{ $opKey }}']">

                                    {{-- اینپوت گروپ شکیل با تفکیک ۳ رقمی ارقام و واحد ریال در جایگاه استاندارد --}}
                                    <div class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/60 focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-gray-900 focus-within:ring-2 focus-within:ring-indigo-500/20 shadow-2xs transition-all w-44 overflow-hidden">
                                        <input type="text"
                                               inputmode="numeric"
                                               :value="editDisplayPrices['{{ $opKey }}']"
                                               @input="formatPriceInput($event, '{{ $opKey }}')"
                                               class="w-full bg-transparent border-0 py-2 px-3 text-xs sm:text-sm font-black text-gray-900 dark:text-white focus:ring-0 text-center dir-ltr"
                                               placeholder="۰"
                                               required>
                                        <span class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-xs font-bold border-s border-gray-200 dark:border-gray-700 select-none shrink-0">
                                            ریال
                                        </span>
                                    </div>

                                    {{-- پیش‌نمایش در لحظه سود و مارجین --}}
                                    <div class="w-28 text-start text-[11px] space-y-0.5 shrink-0">
                                        <span class="font-black block"
                                              :class="(editPrices['{{ $opKey }}'] || 0) >= {{ $m['buy_price'] }} ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                              x-text="((editPrices['{{ $opKey }}'] || 0) >= {{ $m['buy_price'] }} ? '+ ' : '') + ((editPrices['{{ $opKey }}'] || 0) - {{ $m['buy_price'] }}).toLocaleString('fa-IR') + ' سود'"></span>
                                        <span class="text-gray-400 dark:text-gray-500 block font-medium"
                                              x-text="(editPrices['{{ $opKey }}'] > 0 ? (((editPrices['{{ $opKey }}'] - {{ $m['buy_price'] }}) / editPrices['{{ $opKey }}']) * 100).toFixed(1) : '0') + '% مارجین'"></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showEditPricesModal = false"
                                class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important; color: #ffffff !important;"
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs sm:text-sm hover:bg-indigo-700 transition-all shadow-md shadow-indigo-500/20 active:scale-95 cursor-pointer">
                            ذخیره نرخ‌های فروش
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودال ۲: ماشین‌حساب و شبیه‌ساز لحظه‌ای سود در تیراژ ارسال (انیمیشن روان و تفکیک کاما) --}}
    <div x-show="showSimulatorModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="simulator-modal-title" role="dialog" aria-modal="true"
         @keydown.escape.window="showSimulatorModal = false">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            {{-- بک‌دراپ تیره با ترنزیشن فید نرم --}}
            <div x-show="showSimulatorModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                 @click="showSimulatorModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- پنل اصلی مودال با ترنزیشن مقیاس و حرکت عمودی --}}
            <div x-show="showSimulatorModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom sm:align-middle bg-white dark:bg-gray-800 rounded-3xl text-start overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl w-full p-6 sm:p-8 border border-gray-100 dark:border-gray-700/80 space-y-6">

                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="simulator-modal-title" class="text-lg font-black text-gray-900 dark:text-white">شبیه‌ساز آنلاین سود فروش در تیراژ ارسال</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تخمین زنده درآمد ناخالص، هزینه خرید سرور و سود خالص بر اساس نرخ‌های جاری</p>
                        </div>
                    </div>
                    <button type="button" @click="showSimulatorModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-xl transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    {{-- انتخاب اپراتور --}}
                    <div>
                        <label class="{{ $labelClass }}">اپراتور و خط انتخابی برای شبیه‌سازی</label>
                        <select x-model="activeOp" class="{{ $inputClass }} cursor-pointer font-bold">
                            @foreach($margins as $opKey => $m)
                                <option value="{{ $opKey }}">{{ $m['title'] }} (خرید سرور: {{ $faNum(number_format($m['buy_price'])) }} ریال | فروش مصوب: {{ $faNum(number_format($m['selling_price'])) }} ریال)</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- دکمه‌های تیراژ سریع --}}
                    <div class="space-y-2">
                        <label class="{{ $labelClass }}">انتخاب سریع تیراژ ارسال پیامک</label>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <button type="button" @click="calcCount = 10000"
                                    :class="calcCount === 10000 ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all cursor-pointer">
                                ۱۰ هزار
                            </button>
                            <button type="button" @click="calcCount = 50000"
                                    :class="calcCount === 50000 ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all cursor-pointer">
                                ۵۰ هزار
                            </button>
                            <button type="button" @click="calcCount = 100000"
                                    :class="calcCount === 100000 ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all cursor-pointer">
                                ۱۰۰ هزار
                            </button>
                            <button type="button" @click="calcCount = 500000"
                                    :class="calcCount === 500000 ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all cursor-pointer">
                                ۵۰۰ هزار
                            </button>
                            <button type="button" @click="calcCount = 1000000"
                                    :class="calcCount === 1000000 ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="py-2 px-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all cursor-pointer">
                                ۱ میلیون
                            </button>
                        </div>
                    </div>

                    {{-- اینپوت تعداد دلخواه با فرمت کاما و واحد پیامک --}}
                    <div>
                        <label class="{{ $labelClass }}">یا درج تعداد دلخواه پیامک</label>
                        <div class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/60 focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-gray-900 focus-within:ring-2 focus-within:ring-indigo-500/20 shadow-2xs transition-all overflow-hidden">
                            <input type="text"
                                   inputmode="numeric"
                                   :value="calcCount > 0 ? calcCount.toLocaleString('en-US') : ''"
                                   @input="formatCalcCount($event)"
                                   class="w-full bg-transparent border-0 py-2.5 px-4 text-xs sm:text-sm font-black text-gray-900 dark:text-white focus:ring-0 text-center dir-ltr"
                                   placeholder="۰">
                            <span class="inline-flex items-center px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-xs font-bold border-s border-gray-200 dark:border-gray-700 select-none shrink-0">
                                پیامک
                            </span>
                        </div>
                    </div>

                    {{-- کارت‌های نتیجه شبیه‌سازی --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- سود خالص --}}
                        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 space-y-1">
                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">سود خالص تخمینی این تیراژ:</span>
                            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400"
                               x-text="Math.round(calcCount * ((operators[activeOp]?.selling_price || 0) - (operators[activeOp]?.buy_price || 0))).toLocaleString('fa-IR') + ' ریال'"></p>
                            <span class="text-[11px] text-emerald-600/80 font-bold block"
                                  x-text="'حاشیه سود: ' + (operators[activeOp]?.selling_price > 0 ? (((operators[activeOp]?.selling_price - operators[activeOp]?.buy_price) / operators[activeOp]?.selling_price) * 100).toFixed(1) : '0') + '%'"></span>
                        </div>

                        {{-- کل دریافتی از مشتری --}}
                        <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 space-y-1">
                            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400">کل دریافتی از مشتری (فروش):</span>
                            <p class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                               x-text="Math.round(calcCount * (operators[activeOp]?.selling_price || 0)).toLocaleString('fa-IR') + ' ریال'"></p>
                            <span class="text-[11px] text-indigo-600/80 font-bold block"
                                  x-text="'نرخ فروش: ' + Number(operators[activeOp]?.selling_price || 0).toLocaleString('fa-IR') + ' ریال'"></span>
                        </div>

                        {{-- هزینه خرید سرور --}}
                        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 space-y-1 sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-rose-700 dark:text-rose-400">هزینه خرید پیامک از سرور (تامین‌کننده):</span>
                                <strong class="text-base font-black text-rose-600 dark:text-rose-400"
                                        x-text="Math.round(calcCount * (operators[activeOp]?.buy_price || 0)).toLocaleString('fa-IR') + ' ریال'"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="showSimulatorModal = false"
                            class="px-6 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                        بستن پنجره
                    </button>
                </div>
            </div>
        </div>
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
