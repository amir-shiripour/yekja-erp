@extends('layouts.user')

@section('title', 'پیشخوان کارگزاری دامنه')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white ps-11 pe-4 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
        checking: false,
        searchDomain: '',
        checkedResult: null,
        toast: { show: false, message: '', type: 'success', timer: null },
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 4000);
        },

        // Edit Reseller Modal State
        openEditModal: false,
        showEditApiKey: false,
        editName: '{{ addslashes($reseller->name) }}',
        editAuthUserId: '{{ addslashes($reseller->auth_userid) }}',
        editDisplayBalance: '{{ $faNum(number_format($reseller->balance, 0)) }}',
        editRawBalance: '{{ (int)$reseller->balance }}',
        editDisplayLocked: '{{ $faNum(number_format($reseller->locked_balance, 0)) }}',
        editRawLocked: '{{ (int)$reseller->locked_balance }}',
        editIsActive: {{ $reseller->is_active ? 'true' : 'false' }},
        testingConnection: false,
        testConnectionResult: null,
        testConnectionSuccess: null,

        formatToPersianCurrency(val) {
            if (val === null || val === undefined || val === '') return '۰';
            let str = val.toString();
            let enStr = str.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧۸۹'.indexOf(d));
            let cleanNum = enStr.replace(/[^0-9]/g, '');
            if (!cleanNum || cleanNum === '0') return '۰';
            let formattedEn = Number(cleanNum).toLocaleString('en-US');
            return formattedEn.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        },

        extractRawNumber(val) {
            if (val === null || val === undefined || val === '') return '0';
            let str = val.toString();
            let enStr = str.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧۸۹'.indexOf(d));
            let cleanNum = enStr.replace(/[^0-9]/g, '');
            return cleanNum || '0';
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
        },

        testLiveConnection() {
            this.testingConnection = true;
            this.testConnectionResult = null;
            let currentApiKey = this.$refs.modalApiKey ? this.$refs.modalApiKey.value : null;
            axios.post('{{ route('user.domainmanager.connections.test') }}', {
                reseller_id: {{ $reseller->id }},
                auth_userid: this.editAuthUserId,
                api_key: currentApiKey
            })
            .then(res => {
                this.testConnectionSuccess = res.data.success;
                this.testConnectionResult = res.data;
                if (res.data.success) {
                    this.showToast('اتصال با وب‌سرویس کارگزار با موفقیت برقرار شد!', 'success');
                } else {
                    this.showToast(res.data.error || 'خطا در ارتباط با کارگزار', 'error');
                }
            })
            .catch(err => {
                this.testConnectionSuccess = false;
                this.testConnectionResult = { error: 'خطا در ارتباط با سرور: ' + (err.response?.data?.message || err.message) };
                this.showToast('خطا در برقراری ارتباط با وب‌سرویس', 'error');
            })
            .finally(() => this.testingConnection = false);
        }
     }">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('domainmanager::partials.toast')

    {{-- Header & Reseller Switcher --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path
                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </span>
                <span>پیشخوان کارگزاری دامنه</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                مشاهده موجودی زنده، ثبت و تمدید آنلاین دامنه‌ها و نظارت بر کارگزار فعال <span
                    class="font-bold text-gray-800 dark:text-gray-200">{{ $reseller->name }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($resellers->count() > 1)
                <form method="GET" action="{{ route('user.domainmanager.dashboard') }}" class="flex items-center">
                    <select name="reseller_id" onchange="this.form.submit()"
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs sm:text-sm font-bold px-3 py-2.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                        @foreach($resellers as $res)
                            <option value="{{ $res->id }}" @selected($res->id === $reseller->id)>
                                کارگزار: {{ $res->name }} (شناسه: {{ $faNum($res->auth_userid) }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <a href="{{ route('user.domainmanager.domains.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>لیست دامنه‌ها</span>
            </a>

            <form method="POST" action="{{ route('user.domainmanager.domains.sync-all') }}" class="inline">
                @csrf
                <input type="hidden" name="reseller_id" value="{{ $reseller->id }}">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <svg class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>بروزرسانی داده‌ها</span>
                </button>
            </form>

            <a href="{{ route('user.domainmanager.search.index') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-95">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>جستجو و ثبت دامنه</span>
            </a>
        </div>
    </div>

    {{-- Flash Session Messages --}}
    @if(session('status'))
        <div
            class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 shadow-xs animate-fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div
            class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3 shadow-xs animate-fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Quick Stats Block --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Stat 1: Wallet Balance --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">موجودی کیف پول کارگزاری</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ $faNum(number_format($liveBalance, 0)) }} <span
                            class="text-xs font-bold text-gray-500 dark:text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>مبلغ مسدود شده:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($lockedBalance, 0)) }} ریال</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-400">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    اتصال فعال
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openEditModal = true"
                            class="text-amber-600 dark:text-amber-400 font-bold hover:underline cursor-pointer">
                        ویرایش
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <a href="{{ route('user.domainmanager.connections.index') }}"
                       class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">تنظیمات</a>
                </div>
            </div>
        </div>

        {{-- Stat 2: Active Domains --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">دامنه‌های فعال</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $faNum($totalActive) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-400">
                <span>دامنه‌های ثبت شده و معتبر</span>
                <a href="{{ route('user.domainmanager.domains.index', ['status' => 'active', 'reseller_id' => $reseller->id]) }}"
                   class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">مشاهده لیست</a>
            </div>
        </div>

        {{-- Stat 3: Expired Domains --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">دامنه‌های منقضی شده</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $faNum($totalExpired) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-400">
                <span>نیازمند تمدید فوری</span>
                <a href="{{ route('user.domainmanager.domains.index', ['status' => 'expired', 'reseller_id' => $reseller->id]) }}"
                   class="text-rose-600 dark:text-rose-400 font-bold hover:underline">مشاهده لیست</a>
            </div>
        </div>

        {{-- Stat 4: Pending Domains --}}
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">در حال انتقال (Pending)</p>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $faNum($totalPending) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-400">
                <span>در حال انتقال از سایر کارگزاران</span>
                <a href="{{ route('user.domainmanager.domains.index', ['status' => 'pending', 'reseller_id' => $reseller->id]) }}"
                   class="text-amber-600 dark:text-amber-400 font-bold hover:underline">مشاهده لیست</a>
            </div>
        </div>
    </div>

    {{-- Main Layout: Two Columns --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Column 1: Live Domain Search & Quick Check (2 Cols) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-5 sm:px-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <span>بررسی سریع و برخط وضعیت دامنه</span>
                    </h2>
                    <span class="text-xs font-bold text-gray-400">استعلام آنی از کارگزار</span>
                </div>

                <div class="p-6 space-y-4">
                    <form @submit.prevent="checking = true; checkedResult = null;
                                 axios.post('{{ route('user.domainmanager.search.check') }}', { domain: searchDomain, reseller_id: {{ $reseller->id }} })
                                     .then(res => {
                                         checkedResult = res.data;
                                     })
                                     .catch(err => {
                                         checkedResult = { status: 'error', message: 'خطا در برقراری ارتباط با کارگزار' };
                                     })
                                     .finally(() => checking = false)"
                          class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="{{ $labelClass }}">نام دامنه مورد نظر:</label>
                            <div class="flex flex-col sm:flex-row items-stretch gap-3">
                                <div class="relative flex-1">
                                    <div
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" x-model="searchDomain" required
                                           placeholder="مثال: mybrand یا domain.com"
                                           class="w-full rounded-xl border border-gray-200 bg-white pr-11 pl-10 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 text-left"
                                           dir="ltr" autocomplete="off">

                                    {{-- Clear input button --}}
                                    <button type="button" x-show="searchDomain && searchDomain.length > 0"
                                            @click="searchDomain = ''; checkedResult = null"
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors cursor-pointer z-10"
                                            title="پاک کردن متن">
                                        <span
                                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2.5 shrink-0">
                                    <button type="submit" :disabled="checking || !searchDomain.trim()"
                                            class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                                        <template x-if="!checking">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <circle cx="11" cy="11" r="8"></circle>
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                                </svg>
                                                <span>استعلام دامنه</span>
                                            </span>
                                        </template>
                                        <template x-if="checking">
                                            <span class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4 text-white" fill="none"
                                                     viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>در حال استعلام...</span>
                                            </span>
                                        </template>
                                    </button>

                                    <button type="button" @click="checking = true; checkedResult = null;
                                                axios.post('{{ route('user.domainmanager.search.check-ir') }}', { domain: searchDomain })
                                                     .then(res => {
                                                         checkedResult = {};
                                                         checkedResult[res.data.domain] = { status: res.data.status, provider: 'استعلام مستقیم IRNIC' };
                                                     })
                                                     .catch(err => {
                                                         checkedResult = { status: 'error', message: 'خطا در استعلام از سرور IRNIC' };
                                                     })
                                                     .finally(() => checking = false)"
                                            :disabled="checking || !searchDomain.trim()"
                                            class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-xs font-bold shadow-2xs transition-all disabled:opacity-50 flex items-center gap-1.5 active:scale-95 cursor-pointer shrink-0"
                                            title="استعلام فوری و مستقیم پسوند ملی .IR">
                                        <span>🇮🇷 استعلام .IR</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Search Results Wrapper --}}
                    <div x-show="checkedResult" class="pt-4 border-t border-gray-100 dark:border-gray-700/50 space-y-3"
                         style="display: none;" x-transition>
                        {{-- Error Alert --}}
                        <div x-show="checkedResult && checkedResult.status === 'error'"
                             class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs font-bold flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span x-text="checkedResult ? checkedResult.message : ''"></span>
                        </div>

                        {{-- Success Results --}}
                        <div x-show="checkedResult && checkedResult.status !== 'error'" class="space-y-3">
                            <template x-for="(info, key) in checkedResult" :key="key">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="min-w-[48px] h-10 px-2.5 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center font-bold text-xs text-indigo-600 dark:text-indigo-400 border border-gray-200/60 dark:border-gray-700 shadow-2xs uppercase tracking-tight">
                                            <span x-text="'.' + key.split('.').pop()"></span>
                                        </div>
                                        <div>
                                            <span
                                                class="font-bold text-gray-900 dark:text-white text-sm select-all block"
                                                dir="ltr" x-text="key"></span>
                                            <template x-if="info.provider">
                                                <span class="text-[10px] text-gray-400 font-bold block"
                                                      x-text="info.provider"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <template x-if="info.status === 'available'">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    آزاد و قابل ثبت
                                                </span>
                                                <a :href="`{{ route('user.domainmanager.register.show') }}?domain=${key}&reseller_id={{ $reseller->id }}&action=register`"
                                                   class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:shadow-indigo-500/40 text-white text-xs font-bold transition-all shadow-xs active:scale-95">
                                                    ثبت دامنه
                                                </a>
                                            </div>
                                        </template>

                                        <template x-if="info.status !== 'available'">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                    ثبت شده
                                                </span>
                                                <a :href="`{{ route('user.domainmanager.register.show') }}?domain=${key}&reseller_id={{ $reseller->id }}&action=transfer`"
                                                   class="px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold transition-all active:scale-95 shadow-2xs">
                                                    انتقال دامنه
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Column 2: Expiring Soon & Reseller Specs (1 Col) --}}
        <div class="space-y-6">
            {{-- Card: Expiring Soon --}}
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-5 sm:px-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span>دامنه‌های در آستانه انقضا</span>
                    </h2>
                    <span class="text-xs font-bold text-gray-400">۳۰ روز آینده</span>
                </div>

                <div class="p-6">
                    @if($expiringSoon->isEmpty())
                        <div class="py-8 text-center text-gray-400 text-xs space-y-2">
                            <div
                                class="w-10 h-10 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p class="font-bold">هیچ دامنه‌ای در ۳۰ روز آینده منقضی نخواهد شد.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/50 space-y-3">
                            @foreach($expiringSoon as $dom)
                                <div class="pt-3 first:pt-0 flex justify-between items-center text-xs">
                                    <div class="space-y-1">
                                        <p class="font-bold text-gray-900 dark:text-white"
                                           dir="ltr">{{ $dom->domain_name }}</p>
                                        <p class="text-rose-600 dark:text-rose-400 font-bold">
                                            انقضا: {{ $faNum(jdate($dom->expiry_date)->format('Y/m/d')) }}
                                            ({{ $faNum(now()->diffInDays($dom->expiry_date)) }} روز مانده)
                                        </p>
                                    </div>
                                    <a href="{{ route('user.domainmanager.domains.show', $dom->domain_name) }}"
                                       class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 font-black hover:bg-rose-100 transition-all text-xs shadow-2xs">
                                        تمدید فوری
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card: Reseller specs --}}
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-5 sm:px-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span>مشخصات فنی کارگزار فعال</span>
                    </h2>
                </div>

                <div class="p-6 space-y-4 text-xs sm:text-sm">
                    <div
                        class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">نام کارگزار:</span>
                        <span class="font-black text-gray-900 dark:text-gray-100">{{ $reseller->name }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">شناسه نمایندگی (Reseller ID):</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100 font-sans">
                            {{ $faNum($reseller->auth_userid) }}
                        </span>
                    </div>
                    <div
                        class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">IP سرور شما:</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100" dir="ltr">
                            {{ request()->server('SERVER_ADDR') ?: $_SERVER['SERVER_ADDR'] ?? '127.0.0.1' }}
                        </span>
                    </div>
                    <div
                        class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">وضعیت حساب:</span>
                        <span
                            class="px-2.5 py-1 rounded-lg {{ $reseller->is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300' }} font-black text-xs">
                            {{ $reseller->is_active ? 'فعال و آماده ثبت' : 'غیرفعال' }}
                        </span>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="openEditModal = true"
                                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-2xl border border-dashed border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 font-bold hover:bg-indigo-100/70 dark:hover:bg-indigo-900/40 transition-all text-xs sm:text-sm cursor-pointer shadow-2xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>ویرایش مشخصات و کلید API کارگزار</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Reseller Modal --}}
    <div x-show="openEditModal"
         x-cloak
         @keydown.escape.window="openEditModal = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        {{-- Backdrop --}}
        <div x-show="openEditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
             @click="openEditModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="openEditModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-700/60"
                 @click.stop>

                {{-- Modal Header --}}
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-gray-50/90 via-white to-gray-50/90 dark:from-gray-900/90 dark:via-gray-800/80 dark:to-gray-900/90 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">ویرایش تنظیمات کارگزار</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">به‌روزرسانی شناسه، کلید دسترسی
                                API، وضعیت و موجودی کارگزاری فعال</p>
                        </div>
                    </div>
                    <button @click="openEditModal = false"
                            class="p-2.5 rounded-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Form --}}
                <form method="POST" action="{{ route('user.domainmanager.connections.update', $reseller->id) }}"
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
                                   x-model="editName"
                                   class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-right"
                                   placeholder="مثلاً: کارگزار اصلی دامنه (ResellerClub)" dir="rtl">
                        </div>
                        <p class="text-[11px] text-gray-400 ms-1">یک عنوان مشخص جهت تمایز این کارگزاری در پیشخوان و
                            فرم‌های ثبت دامنه انتخاب کنید.</p>
                    </div>

                    {{-- Group 2: Credentials (Reseller ID & API Key) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200 block">مشخصات وب‌سرویس کارگزار (API Credentials)</span>
                            <button type="button" @click="testLiveConnection()"
                                    :disabled="testingConnection"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 font-bold text-xs transition-all disabled:opacity-50 cursor-pointer">
                                <svg x-show="!testingConnection" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <svg x-show="testingConnection" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none"
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="testingConnection ? 'در حال بررسی اتصال...' : 'تست آنلاین اتصال'"></span>
                            </button>
                        </div>

                        {{-- Connection Test Result Alert --}}
                        <template x-if="testConnectionResult">
                            <div
                                :class="testConnectionSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60'"
                                class="p-3.5 rounded-2xl border text-xs flex items-center gap-2.5 transition-all">
                                <template x-if="testConnectionSuccess">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <template x-if="!testConnectionSuccess">
                                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <span
                                    x-text="testConnectionSuccess ? 'اتصال با وب‌سرویس کارگزار با موفقیت برقرار شد و موجودی به‌روزرسانی شد.' : (testConnectionResult.error || 'خطا در برقراری اتصال')"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    شناسه نمایندگی (Reseller ID) <span class="text-rose-500 font-black">*</span>
                                </label>
                                <div class="relative flex items-center" dir="ltr">
                                    <input type="text" name="auth_userid" required
                                           x-model="editAuthUserId"
                                           class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs text-left font-sans"
                                           placeholder="728912" dir="ltr">
                                </div>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">
                                    کلید دسترسی جدید <span class="text-xs text-gray-400 font-normal ms-1">(تنها در صورت تغییر وارد کنید)</span>
                                </label>
                                <div class="relative flex items-center" dir="ltr">
                                    <input :type="showEditApiKey ? 'text' : 'password'" name="api_key"
                                           x-ref="modalApiKey"
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
                                <label class="{{ $labelClass }}">موجودی کارگزاری (ریال)</label>
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
                                   x-model="editIsActive"
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5 cursor-pointer">
                            <div>
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 block">حساب کارگزار فعال باشد</span>
                                <span class="text-[11px] text-gray-400 block mt-0.5">امکان استفاده در فرم‌های ثبت، جستجو، استعلام و تمدید خودکار دامنه</span>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openEditModal = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-black hover:shadow-indigo-500/40 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2 cursor-pointer">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ذخیره تغییرات کارگزار</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
