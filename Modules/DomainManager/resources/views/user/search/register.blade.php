@extends('layouts.user')

@section('title', (!empty($domain) ? ($action === 'register' ? 'ثبت دامنه: ' : 'انتقال دامنه: ') . $domain : 'فرم سفارش و ثبت دامنه'))

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $initialAction = old('action', $action ?? 'register');
    $initialRegistrationMode = old('registration_mode', $registrationMode ?? 'manual');
    $initialDomain = old('domain', $domain ?? '');
    $initialResellerId = old('reseller_id', $reseller->id ?? '');
    $initialResellerName = $reseller->name ?? '';
    $initialExt = !empty($initialDomain) && str_contains($initialDomain, '.') ? pathinfo($initialDomain, PATHINFO_EXTENSION) : 'com';
    $resellersList = $resellers ?? collect([$reseller]);
@endphp

@section('content')
<script>
    function registerFormState() {
        return {
            actionType: '{{ addslashes($initialAction) }}',
            regMode: '{{ addslashes($initialRegistrationMode) }}',
            domainName: '{{ addslashes($initialDomain) }}',
            selectedResellerId: '{{ addslashes($initialResellerId) }}',
            selectedResellerName: '{{ addslashes($initialResellerName) }}',

            setDefaultNs() {
                if (this.$refs.ns1) this.$refs.ns1.value = 'ns1.example.com';
                if (this.$refs.ns2) this.$refs.ns2.value = 'ns2.example.com';
            },
            onResellerChange(e) {
                this.selectedResellerId = e.target.value;
                let opt = e.target.options[e.target.selectedIndex];
                if (opt) {
                    this.selectedResellerName = opt.getAttribute('data-name') || opt.text.split('(')[0].trim();
                    if (opt.getAttribute('data-is-manual') === '1') {
                        this.regMode = 'manual';
                    }
                }
            },
            get currentExtension() {
                if (!this.domainName || !this.domainName.includes('.')) return 'com';
                let parts = this.domainName.trim().split('.');
                return parts.length > 1 ? parts[parts.length - 1].toLowerCase() : 'com';
            }
        };
    }
</script>

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="registerFormState()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                {{-- Register Icon --}}
                <span x-show="actionType === 'register'"
                      class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                {{-- Transfer Icon --}}
                <span x-show="actionType === 'transfer'" x-cloak
                      class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </span>

                <span>
                    <template x-if="domainName">
                        <span>
                            <span x-text="actionType === 'register' ? 'ثبت دامنه: ' : 'انتقال دامنه: '"></span>
                            <span class="font-sans text-indigo-600 dark:text-indigo-400" dir="ltr"
                                  x-text="domainName"></span>
                        </span>
                    </template>
                    <template x-if="!domainName">
                        <span
                            x-text="actionType === 'register' ? 'فرم سفارش و ثبت دامنه جدید' : 'فرم درخواست انتقال دامنه به کارگزاری'">
                            {{ $initialAction === 'register' ? 'فرم سفارش و ثبت دامنه جدید' : 'فرم درخواست انتقال دامنه به کارگزاری' }}
                        </span>
                    </template>
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                تکمیل مشخصات مالک، نیم‌سرورها و ارسال سفارش به کارگزار <span
                    class="font-bold text-gray-800 dark:text-gray-200"
                    x-text="selectedResellerName">{{ $initialResellerName }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.domainmanager.search.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>مرکز استعلام و جستجو</span>
            </a>
        </div>
    </div>

    {{-- Error Banner --}}
    @if(session('error'))
        <div
            class="p-5 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main 2-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- Right Column: Main Form (2 Cols) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-5 sm:px-8 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-3xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">اطلاعات سفارش دامنه</h2>
                            <p class="text-xs text-gray-400 mt-0.5">لطفاً فیلدهای الزامی ستاره‌دار را به دقت تکمیل
                                نمایید</p>
                        </div>
                    </div>

                    {{-- Action Mode Selector Buttons --}}
                    <div
                        class="inline-flex items-center p-1 bg-gray-100 dark:bg-gray-800/80 rounded-2xl border border-gray-200/70 dark:border-gray-700/70 self-start sm:self-auto shrink-0">
                        <button type="button" @click="actionType = 'register'"
                                :class="actionType === 'register' ? 'bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-300 shadow-sm font-black' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 font-bold'"
                                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full"
                                  :class="actionType === 'register' ? 'bg-emerald-500' : 'bg-transparent'"></span>
                            <span>ثبت دامنه جدید</span>
                        </button>
                        <button type="button" @click="actionType = 'transfer'"
                                :class="actionType === 'transfer' ? 'bg-white dark:bg-gray-700 text-amber-700 dark:text-amber-300 shadow-sm font-black' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 font-bold'"
                                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full"
                                  :class="actionType === 'transfer' ? 'bg-amber-500' : 'bg-transparent'"></span>
                            <span>انتقال دامنه</span>
                        </button>
                    </div>
                </div>

                <form method="POST"
                      :action="actionType === 'register' ? '{{ route('user.domainmanager.register.submit') }}' : '{{ route('user.domainmanager.transfer.submit') }}'"
                      class="p-6 sm:p-8 space-y-8">
                    @csrf

                    {{-- Registration Mode Selector (Manual vs API) --}}
                    <div x-show="actionType === 'register'"
                         class="space-y-3 pb-6 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <label class="{{ $labelClass }} flex items-center gap-2 mb-0">
                                <span
                                    class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                <span>نوع و شیوه ثبت دامنه</span>
                                <span class="text-rose-500 font-black">*</span>
                            </label>
                            <span class="text-[11px] font-bold text-gray-400 ms-1"
                                  x-text="regMode === 'manual' ? 'حالت دستی (ثبت مستقیم در سامانه بدون تماس با وب‌سرویس)' : 'حالت آنلاین (ارسال سفارش خرید به وب‌سرویس کارگزار)'"></span>
                        </div>

                        <input type="hidden" name="registration_mode" :value="regMode">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            {{-- Option 1: Manual --}}
                            <div @click="regMode = 'manual'"
                                 :class="regMode === 'manual' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 dark:border-emerald-500/60 ring-2 ring-emerald-500/20 shadow-sm' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-900/40 hover:border-gray-300 dark:hover:border-gray-600'"
                                 class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex items-start gap-3.5 select-none">
                                <div class="mt-0.5 shrink-0">
                                    <div
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                        :class="regMode === 'manual' ? 'border-emerald-600 bg-emerald-600 dark:border-emerald-500 dark:bg-emerald-500' : 'border-gray-300 dark:border-gray-600 bg-transparent'">
                                        <span class="w-2 h-2 rounded-full bg-white"
                                              x-show="regMode === 'manual'"></span>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">ثبت دستی در سامانه (پیش‌فرض)</span>
                                        <span
                                            class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 font-bold">بدون نیاز به API</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                        دامنه مستقیماً در دیتابیس سامانه ثبت و فعال می‌شود. مناسب دامنه‌هایی که قبلاً یا
                                        خارج از این کارگزاری ثبت شده‌اند یا زمان عدم اتصال به وب‌سرویس.
                                    </p>
                                </div>
                            </div>

                            {{-- Option 2: API Online --}}
                            <div @click="regMode = 'api'"
                                 :class="regMode === 'api' ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/20 dark:border-indigo-500/60 ring-2 ring-indigo-500/20 shadow-sm' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-900/40 hover:border-gray-300 dark:hover:border-gray-600'"
                                 class="relative p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex items-start gap-3.5 select-none">
                                <div class="mt-0.5 shrink-0">
                                    <div
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                        :class="regMode === 'api' ? 'border-indigo-600 bg-indigo-600 dark:border-indigo-500 dark:bg-indigo-500' : 'border-gray-300 dark:border-gray-600 bg-transparent'">
                                        <span class="w-2 h-2 rounded-full bg-white" x-show="regMode === 'api'"></span>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">سفارش و ثبت آنلاین (API کارگزار)</span>
                                        <span
                                            class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-300 font-bold">اتصال زنده</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                        درخواست خرید دامنه به وب‌سرویس کارگزار ارسال شده و هزینه از کیف‌پول نمایندگی کسر
                                        می‌شود (نیازمند فعال بودن دسترسی Reseller).
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Manual Mode notice --}}
                        <div x-show="regMode === 'manual'"
                             class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-500/10 border border-emerald-200/60 dark:border-emerald-500/20 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>در حالت ثبت دستی، خطاهای کارگزار (مانند <code>You are not authorized reseller</code>) نادیده گرفته شده و رکورد دامنه فوراً در سیستم شما ثبت و فعال می‌شود.</span>
                        </div>
                    </div>

                    {{-- Section 0: Domain & Reseller --}}
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-100 dark:border-gray-800">
                        {{-- Domain Name Input --}}
                        <div class="space-y-1.5">
                            <label class="{{ $labelClass }}">نام دامنه <span
                                    class="text-rose-500 font-black">*</span></label>
                            <div class="relative flex items-center">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <path
                                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="domain" required
                                       x-model="domainName"
                                       value="{{ $initialDomain }}"
                                       placeholder="مثال: mybrand.com یا site.ir"
                                       class="w-full rounded-xl border border-gray-200 bg-white pl-11 pr-4 py-3 text-sm font-bold text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 text-left font-sans"
                                       dir="ltr" autocomplete="off">
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1 ms-1">نام دامنه به همراه پسوند (مثال: company.com
                                یا domain.ir)</p>
                        </div>

                        {{-- Reseller Selection --}}
                        <div class="space-y-1.5">
                            <label class="{{ $labelClass }}">کارگزاری ثبت دامنه <span
                                    class="text-rose-500 font-black">*</span></label>
                            @if($resellersList->count() > 1)
                                <select name="reseller_id" @change="onResellerChange($event)"
                                        class="{{ $inputClass }} cursor-pointer font-bold">
                                    @foreach($resellersList as $res)
                                        <option value="{{ $res->id }}"
                                                data-name="{{ $res->name }}"
                                                data-is-manual="{{ $res->auth_userid === 'manual' ? '1' : '0' }}"
                                            @selected($res->id == $initialResellerId)>
                                            {{ $res->name }}
                                            ({{ $res->auth_userid === 'manual' ? 'حالت دستی' : 'شناسه: ' . $faNum($res->auth_userid) }}
                                            )
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="reseller_id" value="{{ $reseller->id ?? '' }}">
                                <div
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-3 text-sm font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-200 flex items-center justify-between shadow-xs">
                                    <span>{{ $reseller->name ?? 'کارگزار پیش‌فرض' }}</span>
                                    <span class="text-xs text-gray-400 font-sans"
                                          dir="ltr">ID: {{ $faNum($reseller->auth_userid ?? '') }}</span>
                                </div>
                            @endif
                            <p class="text-[11px] text-gray-400 mt-1 ms-1"
                               x-text="actionType === 'register' ? (regMode === 'manual' ? 'کارگزار تنها جهت انتساب و دسته‌بندی دامنه ذخیره می‌شود (هیچ درخواستی به API کارگزار ارسال نخواهد شد).' : 'سفارش ثبت از طریق وب‌سرویس این کارگزار ارسال خواهد شد.') : 'درخواست انتقال به این کارگزار ارسال می‌شود.'"></p>
                        </div>
                    </div>

                    {{-- Section 1: Duration / Transfer Code --}}
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-800">
                            <span
                                class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </span>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white"
                                x-text="actionType === 'register' ? 'دوره ثبت دامنه' : 'اطلاعات احراز هویت و کد انتقال'">
                                {{ $initialAction === 'register' ? 'دوره ثبت دامنه' : 'اطلاعات احراز هویت و کد انتقال' }}
                            </h3>
                        </div>

                        {{-- Register: Duration Picker --}}
                        <div x-show="actionType === 'register'" class="max-w-md space-y-1.5 pt-1">
                            <label class="{{ $labelClass }}">مدت زمان ثبت دامنه <span
                                    class="text-rose-500 font-black">*</span></label>
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedYears: '{{ old('years', '1') }}',
                                options: [
                                    { value: '1', title: '۱ سال ثبت دامنه', subtitle: 'دوره پایه' },
                                    { value: '2', title: '۲ سال ثبت دامنه', subtitle: 'پیشنهادی' },
                                    { value: '3', title: '۳ سال ثبت دامنه', subtitle: '۳۶ ماهه' },
                                    { value: '4', title: '۴ سال ثبت دامنه', subtitle: '۴۸ ماهه' },
                                    { value: '5', title: '۵ سال ثبت دامنه', subtitle: 'اقتصادی ۵ ساله' },
                                    { value: '6', title: '۶ سال ثبت دامنه', subtitle: '۷۲ ماهه' },
                                    { value: '7', title: '۷ سال ثبت دامنه', subtitle: '۸۴ ماهه' },
                                    { value: '8', title: '۸ سال ثبت دامنه', subtitle: '۹۶ ماهه' },
                                    { value: '9', title: '۹ سال ثبت دامنه', subtitle: '۱۰۸ ماهه' },
                                    { value: '10', title: '۱۰ سال ثبت دامنه', subtitle: 'حداکثر بازه' }
                                ],
                                get filteredOptions() {
                                    if (!this.search.trim()) return this.options;
                                    const q = this.search.toLowerCase();
                                    return this.options.filter(o =>
                                        o.title.toLowerCase().includes(q) ||
                                        (o.subtitle && o.subtitle.toLowerCase().includes(q)) ||
                                        o.value.includes(q)
                                    );
                                },
                                select(opt) {
                                    this.selectedYears = opt ? String(opt.value) : '1';
                                    this.open = false;
                                    this.search = '';
                                },
                                getSelectedTitle() {
                                    let found = this.options.find(o => String(o.value) === String(this.selectedYears));
                                    if (!found) return 'انتخاب مدت زمان ثبت...';
                                    return found.title + (found.subtitle ? ' (' + found.subtitle + ')' : '');
                                }
                            }" class="relative" :class="{ 'z-50': open }">
                                <input type="hidden" name="years" :value="selectedYears"
                                       :disabled="actionType !== 'register'">

                                <button type="button" @click="open = !open"
                                        class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm font-bold">
                                    <span x-text="getSelectedTitle()" class="truncate font-medium">۱ سال ثبت دامنه (دوره پایه)</span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                         :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجوی دوره یا سال ثبت..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    <template x-for="opt in filteredOptions" :key="opt.value">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedYears) === String(opt.value) }">
                                            <span x-text="opt.title" class="truncate font-medium"></span>
                                            <span x-show="opt.subtitle"
                                                  class="text-[10px] px-1.5 py-0.5 rounded border border-gray-200/80 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shrink-0 font-medium"
                                                  x-text="opt.subtitle"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ گزینه‌ای پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Transfer: Auth Code Input --}}
                        <div x-show="actionType === 'transfer'" x-cloak class="space-y-2 pt-1">
                            <label class="{{ $labelClass }}">کد محرمانه انتقال (EPP / Auth Code) <span
                                    class="text-rose-500 font-black">*</span></label>
                            <div class="relative max-w-xl">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0110 0v4"></path>
                                    </svg>
                                </div>
                                <input type="text" name="auth_code" value="{{ old('auth_code') }}"
                                       :disabled="actionType !== 'transfer'"
                                       placeholder="کد محرمانه دریافتی از کارگزار قبلی"
                                       class="w-full rounded-xl border border-gray-200 bg-white pl-11 pr-4 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 text-left font-sans"
                                       dir="ltr">
                            </div>
                            <div
                                class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 text-xs text-amber-800 dark:text-amber-300 font-medium flex items-center gap-2.5 max-w-xl">
                                <svg class="w-4 h-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>برای دامنه‌های ملی (.ir) پین انتقال ایرنیک و برای سایر دامنه‌ها کد EPP دریافتی از پنل ثبت‌کننده فعلی الزامی است.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Nameservers --}}
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <span
                                    class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                                    </svg>
                                </span>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">کارگزارهای نام دامنه
                                    (Nameservers)</h3>
                            </div>

                            <button type="button" @click="setDefaultNs()"
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                تنظیم دی‌ان‌اس‌های پیش‌فرض
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="{{ $labelClass }}">Nameserver 1 <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" x-ref="ns1" name="ns1" required
                                       value="{{ old('ns1', 'ns1.example.com') }}"
                                       class="{{ $inputClass }} text-left font-sans"
                                       dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Nameserver 2 <span
                                        class="text-rose-500 font-black">*</span></label>
                                <input type="text" x-ref="ns2" name="ns2" required
                                       value="{{ old('ns2', 'ns2.example.com') }}"
                                       class="{{ $inputClass }} text-left font-sans"
                                       dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Nameserver 3 <span
                                        class="text-xs text-gray-400 font-normal ms-1">(اختیاری)</span></label>
                                <input type="text" name="ns3" value="{{ old('ns3') }}" placeholder="اختیاری"
                                       class="{{ $inputClass }} text-left font-sans" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Nameserver 4 <span
                                        class="text-xs text-gray-400 font-normal ms-1">(اختیاری)</span></label>
                                <input type="text" name="ns4" value="{{ old('ns4') }}" placeholder="اختیاری"
                                       class="{{ $inputClass }} text-left font-sans" dir="ltr">
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Holder Contact Handle --}}
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-800">
                            <span
                                class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">شناسه مخاطب WHOIS (Holder
                                Handle)</h3>
                        </div>

                        <div class="max-w-md space-y-1.5 pt-1">
                            <label class="{{ $labelClass }}">
                                <span>شناسه کاربری مالک در کارگزار</span>
                                <span x-show="actionType === 'register' && regMode === 'api'"
                                      class="text-rose-500 font-black">*</span>
                                <span x-show="actionType === 'register' && regMode === 'manual'"
                                      class="text-xs text-gray-400 font-normal ms-1">(اختیاری در ثبت دستی)</span>
                            </label>
                            <input type="text" name="holder_handle"
                                   :required="actionType === 'register' ? regMode === 'api' : true"
                                   value="{{ old('holder_handle', $reseller->auth_userid ?? '') }}"
                                   class="{{ $inputClass }} text-left font-sans" dir="ltr">
                            <p class="text-[11px] text-gray-400 mt-1 ms-1"
                               x-text="actionType === 'register' && regMode === 'manual' ? 'در حالت ثبت دستی اختیاری است و صرفاً برای نگهداری در سوابق دامنه کاربرد دارد.' : 'در صورت عدم تغییر، از شناسه پیش‌فرض کارگزاری ({{ $reseller->auth_userid ?? '' }}) استفاده خواهد شد.'"></p>
                        </div>
                    </div>

                    {{-- Submit Footer --}}
                    <div
                        class="pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                        <a href="{{ route('user.domainmanager.search.index') }}"
                           class="px-6 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            انصراف
                        </a>

                        <button type="submit"
                                class="px-8 py-3.5 rounded-xl text-white font-black text-xs sm:text-sm shadow-lg transition-all duration-200 active:scale-95 flex items-center gap-2 cursor-pointer"
                                :class="actionType === 'register' ? (regMode === 'manual' ? 'bg-gradient-to-r from-emerald-600 to-teal-700 hover:shadow-emerald-500/40 shadow-emerald-500/25' : 'bg-gradient-to-r from-indigo-600 to-indigo-700 hover:shadow-indigo-500/40 shadow-indigo-500/25') : 'bg-gradient-to-r from-amber-600 to-amber-700 hover:shadow-amber-500/40 shadow-amber-500/25'">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span
                                x-text="actionType === 'register' ? (regMode === 'manual' ? 'ثبت مستقیم و دستی دامنه در سامانه' : 'تایید و ارسال سفارش به کارگزار') : 'ارسال درخواست انتقال دامنه'">
                                {{ $initialAction === 'register' ? ($initialRegistrationMode === 'manual' ? 'ثبت مستقیم و دستی دامنه در سامانه' : 'تایید و ارسال سفارش به کارگزار') : 'ارسال درخواست انتقال دامنه' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Left Column: Summary & Guidance (1 Col) --}}
        <div class="space-y-6">
            {{-- Order Summary Card --}}
            <div class="{{ $cardClass }} p-6 space-y-6">
                <div class="flex items-center gap-2 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <span
                        class="p-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </span>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">مشخصات سفارش دامنه</h3>
                </div>

                <div class="space-y-4 text-xs sm:text-sm">
                    <div
                        class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">نام دامنه:</span>
                        <span class="font-black text-gray-900 dark:text-white select-all font-sans" dir="ltr"
                              x-text="domainName || 'در انتظار نام دامنه...'">{{ $initialDomain ?: 'در انتظار نام دامنه...' }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">نوع پسوند:</span>
                        <span
                            class="min-w-[40px] px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 font-bold text-xs text-center uppercase font-sans"
                            dir="ltr" x-text="'.' + currentExtension">.{{ $initialExt }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">کارگزار مقصد:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400"
                              x-text="selectedResellerName">{{ $initialResellerName }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">نوع عملیات:</span>
                        <span class="px-2.5 py-1 rounded-lg font-bold text-xs transition-colors"
                              :class="actionType === 'register' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'"
                              x-text="actionType === 'register' ? 'ثبت دامنه جدید' : 'درخواست انتقال'">
                            {{ $initialAction === 'register' ? 'ثبت دامنه جدید' : 'درخواست انتقال' }}
                        </span>
                    </div>

                    <div x-show="actionType === 'register'" class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400 font-bold">شیوه فرآیند:</span>
                        <span class="px-2 py-0.5 rounded-lg font-bold text-xs"
                              :class="regMode === 'manual' ? 'bg-emerald-100/70 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-indigo-100/70 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-300'"
                              x-text="regMode === 'manual' ? 'ثبت دستی در سامانه' : 'سفارش آنلاین (API)'">
                            {{ $initialRegistrationMode === 'manual' ? 'ثبت دستی در سامانه' : 'سفارش آنلاین (API)' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Process Guidance Card --}}
            <div class="{{ $cardClass }} p-6 space-y-4">
                <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span
                        x-text="actionType === 'register' ? 'نکات مهم پیش از ثبت دامنه:' : 'نکات مهم فرآیند انتقال دامنه:'">
                        {{ $initialAction === 'register' ? 'نکات مهم پیش از ثبت دامنه:' : 'نکات مهم فرآیند انتقال دامنه:' }}
                    </span>
                </h3>

                {{-- Register Tips --}}
                <ul x-show="actionType === 'register'"
                    class="text-xs text-gray-500 dark:text-gray-400 space-y-2.5 leading-relaxed">
                    <li class="flex items-start gap-2" x-show="regMode === 'manual'">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span><strong>ثبت دستی:</strong> دامنه بدون نیاز به ارتباط با وب‌سرویس کارگزار، مستقیماً در دیتابیس سامانه ثبت و مدیریت می‌شود.</span>
                    </li>
                    <li class="flex items-start gap-2" x-show="regMode === 'api'">
                        <span class="text-indigo-500 font-bold">✓</span>
                        <span><strong>ثبت آنلاین:</strong> سفارش خرید دامنه به وب‌سرویس کارگزار ارسال شده و هزینه از کیف‌پول کارگزاری کسر خواهد شد.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>تنظیمات نیم‌سرورها و تاریخ‌های محاسبه‌شده انقضا در پرونده دامنه ذخیره می‌گردند.</span>
                    </li>
                </ul>

                {{-- Transfer Tips --}}
                <ul x-show="actionType === 'transfer'" x-cloak
                    class="text-xs text-gray-500 dark:text-gray-400 space-y-2.5 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500 font-bold">✓</span>
                        <span>قفل انتقال دامنه (Transfer Lock / ClientTransferProhibited) در پنل کارگزار مبدا باید حتماً غیرفعال باشد.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500 font-bold">✓</span>
                        <span>مخفی‌سازی مشخصات WHOIS در کارگزار مبدا باید غیرفعال باشد تا ایمیل تایید انتقال دریافت شود.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500 font-bold">✓</span>
                        <span>حداقل ۶۰ روز باید از تاریخ ثبت اولیه یا انتقال قبلی دامنه سپری شده باشد.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

