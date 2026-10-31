@extends('layouts.user')

@section('title', 'مدیریت کارگزاران دامنه')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
        openAddModal: @json($autoOpenAdd ?? false),
        activeEditReseller: null,
        showAddApiKey: false,
        showEditApiKey: false,
        addConnectionType: 'api',
        addAuthUserId: '',
        selectAddType(type) {
            this.addConnectionType = type;
            if (type === 'manual' && !this.addAuthUserId) {
                this.addAuthUserId = 'manual';
            }
        },

        // Add Modal Balances
        addDisplayBalance: '۰',
        addRawBalance: '0',
        addDisplayLocked: '۰',
        addRawLocked: '0',

        // Edit Modal Balances
        editDisplayBalance: '۰',
        editRawBalance: '0',
        editDisplayLocked: '۰',
        editRawLocked: '0',

        // Currency Formatting Helper Functions
        formatToPersianCurrency(val) {
            if (val === null || val === undefined || val === '') return '۰';
            let str = val.toString();
            let enStr = str.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
            let cleanNum = enStr.replace(/[^0-9]/g, '');
            if (!cleanNum || cleanNum === '0') return '۰';
            let formattedEn = Number(cleanNum).toLocaleString('en-US');
            return formattedEn.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        },

        extractRawNumber(val) {
            if (val === null || val === undefined || val === '') return '0';
            let str = val.toString();
            let enStr = str.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
            let cleanNum = enStr.replace(/[^0-9]/g, '');
            return cleanNum || '0';
        },

        onAddBalanceInput(e) {
            let raw = this.extractRawNumber(e.target.value);
            this.addRawBalance = raw;
            this.addDisplayBalance = this.formatToPersianCurrency(raw);
        },

        onAddLockedInput(e) {
            let raw = this.extractRawNumber(e.target.value);
            this.addRawLocked = raw;
            this.addDisplayLocked = this.formatToPersianCurrency(raw);
        },

        openEdit(reseller) {
            this.activeEditReseller = reseller;
            this.showEditApiKey = false;
            this.editRawBalance = this.extractRawNumber(reseller.balance);
            this.editDisplayBalance = this.formatToPersianCurrency(reseller.balance);
            this.editRawLocked = this.extractRawNumber(reseller.locked_balance);
            this.editDisplayLocked = this.formatToPersianCurrency(reseller.locked_balance);
        },

        onEditBalanceInput(e) {
            let raw = this.extractRawNumber(e.target.value);
            this.editRawBalance = raw;
            this.editDisplayBalance = this.formatToPersianCurrency(raw);
        },

        onEditLockedInput(e) {
            let raw = this.extractRawNumber(e.target.value);
            this.editRawLocked = raw;
            this.editDisplayLocked = this.formatToPersianCurrency(raw);
        }
     }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </span>
                <span>تنظیمات کارگزاری‌های دامنه</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                مدیریت حساب‌های نمایندگی و کلیدهای API جهت اتصال زنده، ثبت خودکار و تمدید دامنه‌ها
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.domainmanager.domains.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>لیست دامنه‌ها</span>
            </a>

            <button @click="openAddModal = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>افزودن کارگزار جدید</span>
            </button>
        </div>
    </div>

    {{-- IP Whitelist Warning Box --}}
    <div
        class="p-5 rounded-3xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-900 dark:text-amber-300 text-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-start gap-4">
            <div
                class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <span class="font-black block text-base mb-0.5">ثبت IP سرور در پنل نمایندگی الزامی است</span>
                <p class="text-xs opacity-90 leading-relaxed">
                    جهت برقراری ارتباط موفق با API، حتماً آدرس IP سرور خود را در بخش <strong>Settings &gt; API</strong>
                    پنل کارگزاری وایت‌لیست کنید.
                </p>
            </div>
        </div>

        <div
            class="flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2.5 rounded-2xl border border-amber-200 dark:border-gray-700 text-xs shadow-2xs shrink-0 select-all">
            <span class="text-gray-500 dark:text-gray-400 font-bold">IP سرور:</span>
            <span class="font-black text-indigo-600 dark:text-indigo-400"
                  dir="ltr">{{ request()->server('SERVER_ADDR') ?: $_SERVER['SERVER_ADDR'] ?? '127.0.0.1' }}</span>
        </div>
    </div>

    {{-- Status Flash --}}
    @if(session('status'))
        <div
            class="p-5 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ session('status') }}
            </div>
        </div>
    @endif

    {{-- Reseller Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($resellers as $res)
            <div class="{{ $cardClass }} flex flex-col justify-between overflow-hidden"
                 x-data="{ testing: false, testResult: null, testSuccess: null }">
                <div>
                    {{-- Card Header --}}
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="relative flex h-3.5 w-3.5">
                                @if($res->is_active)
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                                @else
                                    <span
                                        class="relative inline-flex rounded-full h-3.5 w-3.5 bg-gray-400 dark:bg-gray-600"></span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white flex flex-wrap items-center gap-2">
                                    <span>{{ $res->name }}</span>
                                    @if($res->isManual())
                                        <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                            دستی (آفلاین)
                                        </span>
                                    @endif
                                    @if($res->is_active)
                                        <span
                                            class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            فعال
                                        </span>
                                    @else
                                        <span
                                            class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                            غیرفعال
                                        </span>
                                    @endif
                                </h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="openEdit({{ $res->toJson() }})"
                                    class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-all cursor-pointer"
                                    title="ویرایش تنظیمات کارگزار">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            <button type="button"
                                    @click="$dispatch('open-confirm-modal', {
                                        title: 'حذف حساب کارگزار',
                                        message: 'آیا از حذف این کارگزار اطمینان دارید؟ اطلاعات اتصال و دسترسی وب‌سرویس این حساب حذف خواهد شد.',
                                        targetName: '{{ $res->name }} (شناسه: {{ $res->auth_userid }})',
                                        actionUrl: '{{ route('user.domainmanager.connections.destroy', $res) }}',
                                        method: 'DELETE',
                                        confirmText: 'بله، حذف شود'
                                    })"
                                    class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 dark:hover:text-rose-400 transition-all cursor-pointer"
                                    title="حذف کارگزار">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">شناسه نمایندگی (Reseller ID)</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white block font-sans">
                                    {{ $faNum($res->auth_userid) }}
                                </span>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">آخرین موجودی کش‌شده</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 font-sans">
                                        {{ $faNum(number_format($res->balance, 0)) }}
                                    </span>
                                    <span
                                        class="text-[11px] font-bold text-gray-400 dark:text-gray-500 font-sans">ریال</span>
                                </div>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">مبلغ مسدود شده (Locked Charge)</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black text-rose-600 dark:text-rose-400 font-sans">
                                        {{ $faNum(number_format($res->locked_balance, 0)) }}
                                    </span>
                                    <span
                                        class="text-[11px] font-bold text-gray-400 dark:text-gray-500 font-sans">ریال</span>
                                </div>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span
                                    class="text-xs font-bold text-gray-500 dark:text-gray-400 block">آخرین اتصال موفق</span>
                                <span
                                    class="text-xs font-bold text-gray-800 dark:text-gray-200 block truncate font-sans">
                                    {{ $res->last_connected_at ? $faNum(jdate($res->last_connected_at)->format('Y/m/d H:i')) : 'هنوز ثبت نشده' }}
                                </span>
                            </div>
                        </div>

                        {{-- Live test output --}}
                        <div class="pt-2">
                            <div x-show="testing"
                                 class="p-3.5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center gap-3 text-indigo-700 dark:text-indigo-400 text-xs font-bold">
                                <svg class="animate-spin h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                     fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>در حال برقراری ارتباط با API و دریافت موجودی زنده...</span>
                            </div>

                            <div x-show="testResult && testSuccess === true" x-cloak
                                 class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>اتصال با کارگزار برقرار است!</span>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 font-bold text-xs border border-emerald-200 dark:border-emerald-500/30"
                                    dir="ltr">
                                    موجودی زنده: <span
                                        x-text="parseFloat(testResult.balance?.sellingbalance || 0).toFixed(2)"></span> USD
                                </span>
                            </div>

                            <div x-show="testResult && testSuccess === false" x-cloak
                                 class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs space-y-2">
                                <div class="font-bold flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>خطا در برقراری ارتباط:</span>
                                </div>
                                <div
                                    class="text-xs font-medium text-rose-800 dark:text-rose-300 bg-white/80 dark:bg-gray-900/60 p-2.5 rounded-xl border border-rose-200/60 dark:border-rose-500/20 leading-relaxed"
                                    x-text="testResult.error"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div
                    class="p-4 sm:px-6 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-b-3xl flex items-center justify-between gap-3">
                    <button type="button"
                            @click="testing = true; testResult = null;
                                    axios.post('{{ route('user.domainmanager.connections.test') }}', { reseller_id: {{ $res->id }} })
                                        .then(res => {
                                            testSuccess = res.data.success;
                                            testResult = res.data;
                                        })
                                        .catch(err => {
                                            testSuccess = false;
                                            testResult = { error: err.message };
                                        })
                                        .finally(() => testing = false)"
                            class="px-4 py-2.5 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm flex items-center gap-2 active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>تست اتصال زنده</span>
                    </button>

                    <form action="{{ route('user.domainmanager.domains.sync-all') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reseller_id" value="{{ $res->id }}">
                        <button type="submit"
                                class="px-4 py-2.5 text-xs font-bold rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all shadow-sm flex items-center gap-2 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                            <span>همگام‌سازی دامنه‌ها</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full {{ $cardClass }} p-12 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path
                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-800 dark:text-gray-200">هیچ کارگزاری ثبت نشده است</h3>
                    <p class="text-xs text-gray-400 mt-1">جهت شروع ثبت دامنه و استعلام زنده، لطفاً اطلاعات حساب کارگزاری
                        خود را ثبت نمایید.</p>
                </div>
                <button @click="openAddModal = true"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>افزودن اولین کارگزار</span>
                </button>
            </div>
        @endforelse
    </div>
    <div x-show="openAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            {{-- Backdrop blur --}}
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                 @click="openAddModal = false"></div>

            {{-- Modal Card --}}
            <div
                class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800">
                {{-- Modal Header --}}
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-gray-50/90 via-white to-gray-50/90 dark:from-gray-900/90 dark:via-gray-800/80 dark:to-gray-900/90 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path
                                    d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">ثبت حساب کارگزاری جدید</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">افزودن مشخصات اتصال وب‌سرویس
                                کارگزاری جهت ثبت و استعلام خودکار دامنه‌ها</p>
                        </div>
                    </div>
                    <button @click="openAddModal = false"
                            class="p-2.5 rounded-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Mode Switcher: Online API vs Offline Manual Reseller --}}
                <div class="px-6 sm:px-8 pt-6">
                    <div class="grid grid-cols-2 gap-3 p-1.5 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <button type="button" @click="selectAddType('api')"
                                :class="addConnectionType === 'api' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                            </svg>
                            <span>کارگزار آنلاین (API)</span>
                        </button>
                        <button type="button" @click="selectAddType('manual')"
                                :class="addConnectionType === 'manual' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>کارگزار دستی / آفلاین</span>
                        </button>
                    </div>
                </div>

                {{-- Modal Body Form --}}
                <form method="POST" action="{{ route('user.domainmanager.connections.store') }}"
                      class="p-6 sm:p-8 space-y-6">
                    @csrf
                    <input type="hidden" name="connection_type" :value="addConnectionType">
                    {{-- Hidden raw English inputs for clean numerical storage in DB --}}
                    <input type="hidden" name="balance" :value="addRawBalance">
                    <input type="hidden" name="locked_balance" :value="addRawLocked">

                    {{-- Group 1: Display Name --}}
                    <div class="space-y-2">
                        <label class="{{ $labelClass }}">
                            نام نمایشی کارگزار <span class="text-rose-500 font-black">*</span>
                        </label>
                        <div class="relative flex items-center" dir="rtl">
                            <input type="text" name="name" required
                                   class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right"
                                   placeholder="مثلاً: کارگزار اصلی دامنه یا ثبت دستی" dir="rtl">
                        </div>
                        <p class="text-[11px] text-gray-400 ms-1">یک عنوان مشخص جهت تمایز این کارگزاری در پیشخوان و
                            فرم‌های ثبت دامنه انتخاب کنید.</p>
                    </div>

                    {{-- Group 2: Credentials (Reseller ID & API Key) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 space-y-4">
                        <span class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200 block">مشخصات احراز هویت کارگزار</span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    شناسه نمایندگی (Reseller ID) <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="relative flex items-center" dir="ltr">
                                    <input type="text" name="auth_userid" x-model="addAuthUserId" required
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-left"
                                           placeholder="728912 یا manual" dir="ltr">
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">
                                    <span>کلید دسترسی (API Key)</span>
                                    <span x-show="addConnectionType === 'api'" class="text-rose-500 font-black">*</span>
                                    <span x-show="addConnectionType === 'manual'" class="text-xs text-gray-400 font-normal ms-1">(اختیاری)</span>
                                </label>
                                <div class="relative flex items-center" dir="ltr">
                                    <input :type="showAddApiKey ? 'text' : 'password'" name="api_key" :required="addConnectionType === 'api'"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 pl-4 pr-12 py-3 text-sm font-mono text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-left"
                                           placeholder="••••••••••••••••" dir="ltr">
                                    <button type="button" @click="showAddApiKey = !showAddApiKey"
                                            class="absolute right-2.5 flex items-center justify-center w-8 h-8 rounded-xl text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors p-1 cursor-pointer"
                                            title="نمایش / مخفی‌سازی کلید">
                                        <svg x-show="!showAddApiKey" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showAddApiKey" x-cloak class="w-4.5 h-4.5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Group 3: Financial Balances (Persian 3-digit comma formatted) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200">موجودی و محاسبات مالی اولیه (ریال)</span>
                            <span
                                class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-0.5 rounded-full border border-indigo-200/50 dark:border-indigo-500/20">
                                تبدیل خودکار سه‌رقمی
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">موجودی اولیه (ریال)</label>
                                <div class="relative flex items-center" dir="rtl">
                                    <input type="text"
                                           :value="addDisplayBalance"
                                           @input="onAddBalanceInput($event)"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 pr-4 pl-16 py-3 text-sm font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right font-sans"
                                           placeholder="۰" dir="rtl">
                                    <span
                                        class="absolute left-2.5 px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-black text-gray-500 dark:text-gray-400 select-none pointer-events-none border border-gray-200/80 dark:border-gray-700 font-sans">
                                        ریال
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1 ms-1">موجودی فعلی حساب شما در کارگزاری</p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">مبلغ مسدود شده (ریال)</label>
                                <div class="relative flex items-center" dir="rtl">
                                    <input type="text"
                                           :value="addDisplayLocked"
                                           @input="onAddLockedInput($event)"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 pr-4 pl-16 py-3 text-sm font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right font-sans"
                                           placeholder="۰" dir="rtl">
                                    <span
                                        class="absolute left-2.5 px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-black text-gray-500 dark:text-gray-400 select-none pointer-events-none border border-gray-200/80 dark:border-gray-700 font-sans">
                                        ریال
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1 ms-1">مبالغ قفل‌شده بابت سفارشات در جریان</p>
                            </div>
                        </div>
                    </div>

                    {{-- Group 4: Active Status Option --}}
                    <div class="pt-2">
                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/80 cursor-pointer hover:bg-gray-100/70 dark:hover:bg-gray-800/80 transition-all">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5 cursor-pointer">
                            <div>
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 block">حساب کارگزار فعال باشد</span>
                                <span class="text-[11px] text-gray-400 block mt-0.5">امکان استفاده در فرم‌های ثبت، جستجو، استعلام و تمدید خودکار دامنه</span>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openAddModal = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-black hover:shadow-indigo-500/40 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2 cursor-pointer">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ثبت و ذخیره کارگزار</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div x-show="activeEditReseller !== null" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                 @click="activeEditReseller = null"></div>

            <div
                class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800">
                {{-- Modal Header --}}
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-gray-50/90 via-white to-gray-50/90 dark:from-gray-900/90 dark:via-gray-800/80 dark:to-gray-900/90 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">ویرایش تنظیمات کارگزار</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">به‌روزرسانی شناسه، کلید دسترسی
                                API یا موجودی کارگزاری</p>
                        </div>
                    </div>
                    <button @click="activeEditReseller = null"
                            class="p-2.5 rounded-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Form --}}
                <form method="POST"
                      :action="`{{ url('user/domainmanager/connections') }}/${activeEditReseller ? activeEditReseller.id : ''}`"
                      class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Hidden raw English inputs for clean numerical storage in DB --}}
                    <input type="hidden" name="balance" :value="editRawBalance">
                    <input type="hidden" name="locked_balance" :value="editRawLocked">

                    {{-- Group 1: Display Name --}}
                    <div class="space-y-2">
                        <label class="{{ $labelClass }}">
                            نام نمایشی کارگزار <span class="text-rose-500 font-black">*</span>
                        </label>
                        <div class="relative flex items-center" dir="rtl">
                            <input type="text" name="name" required
                                   :value="activeEditReseller ? activeEditReseller.name : ''"
                                   class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right"
                                   dir="rtl">
                        </div>
                    </div>

                    {{-- Group 2: Credentials (Reseller ID & API Key) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 space-y-4">
                        <span class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200 block">مشخصات وب‌سرویس کارگزار (API Credentials)</span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    شناسه نمایندگی (Reseller ID) <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="relative flex items-center" dir="ltr">
                                    <input type="text" name="auth_userid" required
                                           :value="activeEditReseller ? activeEditReseller.auth_userid : ''"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-left"
                                           dir="ltr">
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">
                                    کلید دسترسی جدید <span class="text-xs text-gray-400 font-normal ms-1">(تنها در صورت تغییر وارد کنید)</span>
                                </label>
                                <div class="relative flex items-center" dir="ltr">
                                    <input :type="showEditApiKey ? 'text' : 'password'" name="api_key"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 pl-4 pr-12 py-3 text-sm font-mono text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-left"
                                           placeholder="••••••••••••••••" dir="ltr">
                                    <button type="button" @click="showEditApiKey = !showEditApiKey"
                                            class="absolute right-2.5 flex items-center justify-center w-8 h-8 rounded-xl text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors p-1 cursor-pointer"
                                            title="نمایش / مخفی‌سازی کلید">
                                        <svg x-show="!showEditApiKey" class="w-4.5 h-4.5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showEditApiKey" x-cloak class="w-4.5 h-4.5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Group 3: Financial Balances (Persian 3-digit comma formatted) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200">موجودی و محاسبات مالی (ریال)</span>
                            <span
                                class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-0.5 rounded-full border border-indigo-200/50 dark:border-indigo-500/20">
                                تبدیل خودکار سه‌رقمی
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">موجودی (ریال)</label>
                                <div class="relative flex items-center" dir="rtl">
                                    <input type="text"
                                           :value="editDisplayBalance"
                                           @input="onEditBalanceInput($event)"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 pr-4 pl-16 py-3 text-sm font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right font-sans"
                                           dir="rtl">
                                    <span
                                        class="absolute left-2.5 px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-black text-gray-500 dark:text-gray-400 select-none pointer-events-none border border-gray-200/80 dark:border-gray-700 font-sans">
                                        ریال
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">مبلغ مسدود شده (ریال)</label>
                                <div class="relative flex items-center" dir="rtl">
                                    <input type="text"
                                           :value="editDisplayLocked"
                                           @input="onEditLockedInput($event)"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 pr-4 pl-16 py-3 text-sm font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right font-sans"
                                           dir="rtl">
                                    <span
                                        class="absolute left-2.5 px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-black text-gray-500 dark:text-gray-400 select-none pointer-events-none border border-gray-200/80 dark:border-gray-700 font-sans">
                                        ریال
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Group 4: Active Status Option --}}
                    <div class="pt-2">
                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/80 cursor-pointer hover:bg-gray-100/70 dark:hover:bg-gray-800/80 transition-all">
                            <input type="checkbox" name="is_active" value="1"
                                   :checked="activeEditReseller && activeEditReseller.is_active"
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5 cursor-pointer">
                            <div>
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 block">حساب کارگزار فعال باشد</span>
                                <span class="text-[11px] text-gray-400 block mt-0.5">امکان استفاده در فرم‌های ثبت، جستجو، استعلام و تمدید خودکار دامنه</span>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="activeEditReseller = null"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-black hover:shadow-indigo-500/40 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2 cursor-pointer">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ذخیره تغییرات</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Include Confirmation Modal Component --}}
    @include('domainmanager::partials.confirm-modal')

</div>
@endsection
