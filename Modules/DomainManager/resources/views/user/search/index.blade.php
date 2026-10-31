@extends('layouts.user')

@section('title', 'مرکز استعلام و ثبت دامنه')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white ps-11 pe-10 py-3 text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $allTldsList = ['com', 'ir', 'net', 'org', 'co', 'io', 'online', 'store', 'biz', 'info', 'me', 'app', 'xyz'];
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="domainSearchEngine()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                    <span>مرکز استعلام و ثبت دامنه</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    استعلام زنده آزاد بودن دامنه‌ها، بررسی همزمان چندین پسوند و ثبت آنی در کارگزاری
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.domainmanager.register.show') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white text-xs sm:text-sm font-black shadow-md shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت مستقیم دامنه</span>
                </a>

                <a href="{{ route('user.domainmanager.prices.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>تعرفه‌های ثبت</span>
                </a>

                <a href="{{ route('user.domainmanager.domains.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20 text-xs sm:text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>لیست دامنه‌ها</span>
                </a>
            </div>
        </div>

        @if($resellers->isEmpty())
            <div class="{{ $cardClass }} p-12 text-center space-y-4">
                <div
                    class="w-16 h-16 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path
                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-800 dark:text-gray-200">هیچ کارگزاری فعالی ثبت نشده
                        است</h3>
                    <p class="text-xs text-gray-400 mt-1">جهت استعلام و ثبت دامنه، لطفاً ابتدا یک کارگزار فعال تعریف
                        نمایید.</p>
                </div>
                <a href="{{ route('user.domainmanager.connections.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-95">
                    <span>تنظیمات کارگزارها</span>
                </a>
            </div>
        @else
            {{-- Main Search Engine Console Card --}}
            <div class="{{ $cardClass }} overflow-hidden">
                {{-- Top Toolbar with Reseller Selection --}}
                <div
                    class="p-5 sm:px-8 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-600 dark:text-gray-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>موتور استعلام برخط وضعیت آزاد بودن دامنه‌ها</span>
                    </div>

                    @if($resellers->count() > 1)
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-400">کارگزار استعلام:</span>
                            <select x-model="selectedResellerId"
                                    class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold px-3 py-1.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                                @foreach($resellers as $res)
                                    <option value="{{ $res->id }}">{{ $res->name }} ({{ $res->auth_userid }})</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span>کارگزار متصل:</span>
                            <span
                                class="font-black text-indigo-600 dark:text-indigo-400">{{ $resellers->first()->name }}</span>
                        </div>
                    @endif
                </div>

                {{-- Search Bar Section --}}
                <div class="p-6 sm:p-8 space-y-6">
                    <form @submit.prevent="performSearch()" class="space-y-6">
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
                                           dir="ltr" autocomplete="off" autocorrect="off" autocapitalize="off">

                                    {{-- Clear input button --}}
                                    <button type="button" x-show="searchDomain && searchDomain.length > 0"
                                            @click="searchDomain = ''; checkedResult = null"
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors cursor-pointer z-10"
                                            title="پاک کردن متن">
                                    <span
                                        class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2.5 shrink-0">
                                    <button type="submit" :disabled="checking || !searchDomain.trim()"
                                            class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2 active:scale-95 cursor-pointer shrink-0">
                                        <template x-if="!checking">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2.5">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                            </svg>
                                            <span>استعلام پسوندها</span>
                                        </span>
                                        </template>
                                        <template x-if="checking">
                                        <span class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4 text-white" fill="none"
                                                 viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>در حال استعلام...</span>
                                        </span>
                                        </template>
                                    </button>

                                    <button type="button" @click="performIrCheck()"
                                            :disabled="checking || !searchDomain.trim()"
                                            class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-xs font-bold shadow-2xs transition-all disabled:opacity-50 flex items-center gap-1.5 active:scale-95 cursor-pointer shrink-0"
                                            title="استعلام مستقیم از سرور مرکز ثبت دامنه ایرنیک (whois.nic.ir)">
                                        <span>🇮🇷 استعلام .IR</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TLD Selector Matrix --}}
                        <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">پسوندهای انتخابی جهت بررسی همزمان:</span>
                                    <span
                                        class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400"
                                        x-text="selectedTlds.length + ' پسوند'"></span>
                                </div>

                                <div class="flex items-center gap-2 text-xs font-bold">
                                    <button type="button" @click="selectPopularTlds()"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                        محبوب‌ترین‌ها
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-700">|</span>
                                    <button type="button" @click="selectAllTlds()"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                        انتخاب همه
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-700">|</span>
                                    <button type="button" @click="clearTlds()"
                                            class="text-rose-500 hover:underline cursor-pointer">پیش‌فرض
                                    </button>
                                </div>
                            </div>

                            {{-- TLD Interactive Clickable Badges --}}
                            <div class="flex flex-wrap gap-2">
                                <template x-for="ext in allTlds" :key="ext">
                                    <button type="button"
                                            @click="toggleTld(ext)"
                                            :class="selectedTlds.includes(ext)
                                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs ring-2 ring-indigo-500/20'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-gray-600'"
                                            class="px-3.5 py-2 rounded-xl border text-xs font-bold transition-all cursor-pointer select-none flex items-center gap-1.5 active:scale-95">
                                        <span class="uppercase tracking-wide font-bold" x-text="'.' + ext"></span>
                                        <svg x-show="selectedTlds.includes(ext)" class="w-3.5 h-3.5 ms-0.5 text-white"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </form>

                    {{-- Recent Searches Pills --}}
                    <div x-show="recentSearches && recentSearches.length > 0"
                         class="pt-1 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-gray-400">جستجوهای اخیر:</span>
                        <template x-for="item in recentSearches" :key="item">
                            <button type="button" @click="searchDomain = item; performSearch()"
                                    class="px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/20 text-xs font-bold text-gray-600 dark:text-gray-400 transition-colors flex items-center gap-1 cursor-pointer">
                                <span x-text="item"></span>
                            </button>
                        </template>
                        <button type="button" @click="clearRecent()"
                                class="text-[11px] text-gray-400 hover:text-rose-500 ms-1 cursor-pointer">پاکسازی
                        </button>
                    </div>
                </div>

                {{-- Results Container --}}
                <div x-show="checkedResult"
                     class="p-6 sm:p-8 space-y-6 border-t border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/20"
                     style="display: none;" x-transition>
                    {{-- Error Alert --}}
                    <div x-show="checkedResult && checkedResult.status === 'error'"
                         class="p-5 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs font-bold flex items-center gap-3">
                        <svg class="w-6 h-6 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span x-text="checkedResult ? checkedResult.message : ''"></span>
                    </div>

                    {{-- Primary Match Hero Banner (If Available) --}}
                    <template x-if="primaryResult && primaryResult.status === 'available'">
                        <div
                            class="p-5 sm:p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                            <div class="flex items-center gap-3.5 text-start">
                                <div
                                    class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">دامنه انتخابی آزاد و آماده ثبت است!</span>
                                    <span
                                        class="text-lg sm:text-xl font-black text-gray-900 dark:text-white block select-all"
                                        dir="ltr" x-text="primaryResult.domain"></span>
                                </div>
                        </div>

                        <a :href="`{{ route('user.domainmanager.register.show') }}?domain=${primaryResult.domain}&reseller_id=${selectedResellerId}&action=register`"
                           class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black shadow-md shadow-emerald-600/25 hover:shadow-emerald-600/40 transition-all active:scale-95 flex items-center justify-center gap-2 shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>ثبت فوری این دامنه</span>
                        </a>
                    </div>
                </template>

                {{-- All Query Results Grid --}}
                <div x-show="checkedResult && checkedResult.status !== 'error'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                            <span>نتایج بررسی پسوندها:</span>
                        </h3>
                        <span class="text-xs text-gray-400 font-medium">استعلام زنده از وب‌سرویس</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <template x-for="(info, key) in checkedResult" :key="key">
                            <div
                                class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 shadow-2xs hover:shadow-sm transition-all flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="min-w-[48px] h-10 px-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center font-bold text-xs text-indigo-600 dark:text-indigo-400 border border-gray-200 dark:border-gray-700 shrink-0 uppercase tracking-tight">
                                        <span x-text="'.' + key.split('.').pop()"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-bold text-gray-900 dark:text-white text-sm select-all truncate block"
                                                dir="ltr" x-text="key"></span>
                                            <button type="button" @click="copyToClipboard(key)"
                                                    class="text-gray-400 hover:text-indigo-600 transition-colors p-0.5 rounded cursor-pointer shrink-0"
                                                    :title="copiedDomain === key ? 'کپی شد!' : 'کپی دامنه'">
                                                <svg x-show="copiedDomain !== key" class="w-3.5 h-3.5" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <svg x-show="copiedDomain === key" x-cloak
                                                     class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="text-[11px] text-gray-400 font-medium block truncate mt-0.5"
                                              x-text="info.provider || 'استعلام کارگزاری'"></span>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center gap-2">
                                    <template x-if="info.status === 'available'">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                آزاد
                                            </span>
                                            <a :href="`{{ route('user.domainmanager.register.show') }}?domain=${key}&reseller_id=${selectedResellerId}&action=register`"
                                               class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs font-bold shadow-xs hover:shadow-indigo-500/30 transition-all active:scale-95">
                                                ثبت دامنه
                                            </a>
                                        </div>
                                    </template>

                                    <template x-if="info.status !== 'available'">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                ثبت شده
                                            </span>
                                            <a :href="`{{ route('user.domainmanager.register.show') }}?domain=${key}&reseller_id=${selectedResellerId}&action=transfer`"
                                               class="px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold transition-all shadow-2xs active:scale-95">
                                                انتقال دامنه
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Intelligent Domain Name Suggestions --}}
                <div x-show="checkedResult && searchDomain && searchDomain.trim().length > 2"
                     class="pt-4 border-t border-gray-200 dark:border-gray-800 space-y-2.5">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">پیشنهاد نام‌های مشابه و برندپذیر:</span>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="prefix in ['get', 'my', 'the', 'app', 'hub', 'pro', 'cloud', 'ir']"
                                  :key="prefix">
                            <button type="button"
                                    @click="applySuggestion(prefix + searchDomain.trim().replace(/\..*/, ''))"
                                    class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 text-xs font-bold text-indigo-600 dark:text-indigo-400 transition-all shadow-2xs cursor-pointer active:scale-95">
                                <span x-text="prefix + searchDomain.trim().replace(/\..*/, '')"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

            {{-- Popular TLDs Price Reference Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                <div class="{{ $cardClass }} p-4 text-center space-y-1">
                    <span class="font-black text-lg text-gray-900 dark:text-white block">.COM</span>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 block">بین‌المللی</span>
                    <span class="text-[11px] text-gray-400 block">ثبت عمومی</span>
                </div>

                <div class="{{ $cardClass }} p-4 text-center space-y-1">
                    <span class="font-black text-lg text-emerald-600 dark:text-emerald-400 block">.IR</span>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 block">ملی ایران</span>
                    <span class="text-[11px] text-gray-400 block">ثبت ایرنیک</span>
                </div>

                <div class="{{ $cardClass }} p-4 text-center space-y-1">
                    <span class="font-black text-lg text-gray-900 dark:text-white block">.NET</span>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 block">شبکه و وب</span>
                    <span class="text-[11px] text-gray-400 block">ثبت عمومی</span>
                </div>

                <div class="{{ $cardClass }} p-4 text-center space-y-1">
                    <span class="font-black text-lg text-gray-900 dark:text-white block">.ORG</span>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 block">سازمانی</span>
                    <span class="text-[11px] text-gray-400 block">ثبت عمومی</span>
                </div>

                <div class="{{ $cardClass }} p-4 text-center space-y-1">
                    <span class="font-black text-lg text-gray-900 dark:text-white block">.CO</span>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 block">شرکتی</span>
                    <span class="text-[11px] text-gray-400 block">استارتاپ‌ها</span>
                </div>

                <div class="{{ $cardClass }} p-4 text-center space-y-1">
                    <span class="font-black text-lg text-purple-600 dark:text-purple-400 block">.IO</span>
                    <span class="text-xs font-bold text-purple-600 dark:text-purple-400 block">فناوری</span>
                    <span class="text-[11px] text-gray-400 block">تکنولوژی و نرم‌افزار</span>
                </div>
            </div>

            {{-- Direct Registration & Transfer Callout Card --}}
            <div
                class="rounded-3xl p-6 sm:p-8 bg-gradient-to-br from-indigo-900/5 via-white to-emerald-900/5 dark:from-indigo-950/30 dark:via-gray-800/80 dark:to-emerald-950/20 border border-indigo-100 dark:border-indigo-800/40 shadow-sm backdrop-blur-xl flex flex-col lg:flex-row lg:items-center justify-between gap-6 transition-all duration-300">
                <div class="flex items-start gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/25">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">
                            ثبت مستقیم بدون نیاز به استعلام
                        </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">سفارش فوری</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                            قصد ثبت یا انتقال مستقیم یک دامنه مشخص را دارید؟
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-2xl leading-relaxed">
                            اگر از آزاد بودن نام دامنه اطمینان دارید یا قصد دارید دامنه فعلی خود را مستقیماً از شرکت
                            دیگری
                            به این سامانه منتقل کنید، می‌توانید بدون فوت وقت مستقیماً مشخصات مالک و نیم‌سرورها را وارد
                            نموده
                            و فرآیند را تکمیل کنید.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 shrink-0">
                    <a href="{{ route('user.domainmanager.register.show') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all active:scale-95 cursor-pointer">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>ثبت مستقیم دامنه</span>
                    </a>

                <a href="{{ route('user.domainmanager.register.show', ['action' => 'transfer']) }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs sm:text-sm font-bold shadow-2xs transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4.5 h-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>انتقال مستقیم دامنه</span>
                </a>
            </div>
        </div>
        @endif

    </div>

    <script>
        function domainSearchEngine() {
            return {
                checking: false,
                searchDomain: '',
                selectedResellerId: {{ $resellers->first()->id ?? 0 }},
                checkedResult: null,
                allTlds: @json($allTldsList),
                selectedTlds: ['com', 'ir', 'net', 'org', 'co', 'io', 'online'],
                copiedDomain: null,
                recentSearches: JSON.parse(localStorage.getItem('dm_recent_searches') || '[]'),

                performSearch() {
                    if (!this.searchDomain.trim() || this.checking) return;
                    this.checking = true;
                    this.checkedResult = null;

                    let clean = this.searchDomain.trim().toLowerCase();
                    if (!this.recentSearches.includes(clean)) {
                        this.recentSearches.unshift(clean);
                        if (this.recentSearches.length > 8) {
                            this.recentSearches.pop();
                        }
                        localStorage.setItem('dm_recent_searches', JSON.stringify(this.recentSearches));
                    }

                    axios.post('{{ route('user.domainmanager.search.check') }}', {
                        domain: this.searchDomain,
                        reseller_id: this.selectedResellerId,
                        tlds: this.selectedTlds
                    })
                        .then(res => {
                            this.checkedResult = res.data;
                        })
                        .catch(err => {
                            this.checkedResult = {
                                status: 'error',
                                message: 'خطا در برقراری ارتباط با وب‌سرویس کارگزار دامنه'
                            };
                        })
                        .finally(() => {
                            this.checking = false;
                        });
                },

                performIrCheck() {
                    if (!this.searchDomain.trim() || this.checking) return;
                    this.checking = true;
                    this.checkedResult = null;

                    axios.post('{{ route('user.domainmanager.search.check-ir') }}', {domain: this.searchDomain})
                        .then(res => {
                            this.checkedResult = {};
                            this.checkedResult[res.data.domain] = {
                                status: res.data.status,
                                provider: 'استعلام مستقیم IRNIC (whois.nic.ir)'
                            };
                        })
                        .catch(err => {
                            this.checkedResult = {status: 'error', message: 'خطا در استعلام از سرور مرکزی IRNIC'};
                        })
                        .finally(() => {
                            this.checking = false;
                        });
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(text);
                    this.copiedDomain = text;
                    setTimeout(() => {
                        this.copiedDomain = null;
                    }, 2000);
                },

                toggleTld(tld) {
                    if (this.selectedTlds.includes(tld)) {
                        if (this.selectedTlds.length > 1) {
                            this.selectedTlds = this.selectedTlds.filter(t => t !== tld);
                        }
                    } else {
                        this.selectedTlds = [...this.selectedTlds, tld];
                    }
                    if (this.searchDomain.trim() && this.checkedResult) {
                        this.performSearch();
                    }
                },

                selectPopularTlds() {
                    this.selectedTlds = ['com', 'ir', 'net', 'org', 'co', 'io', 'online'];
                    if (this.searchDomain.trim() && this.checkedResult) {
                        this.performSearch();
                    }
                },

                selectAllTlds() {
                    this.selectedTlds = [...this.allTlds];
                    if (this.searchDomain.trim() && this.checkedResult) {
                        this.performSearch();
                    }
                },

                clearTlds() {
                    this.selectedTlds = ['com', 'ir'];
                    if (this.searchDomain.trim() && this.checkedResult) {
                        this.performSearch();
                    }
                },

                applySuggestion(name) {
                    this.searchDomain = name;
                    this.performSearch();
                },

                clearRecent() {
                    this.recentSearches = [];
                    localStorage.removeItem('dm_recent_searches');
                },

                get primaryResult() {
                    if (!this.checkedResult || this.checkedResult.status === 'error') return null;
                    let exact = this.searchDomain.trim().toLowerCase();
                    if (exact.includes('.')) {
                        if (this.checkedResult[exact]) {
                            return {domain: exact, ...this.checkedResult[exact]};
                        }
                    } else {
                        let firstTld = this.selectedTlds[0] || 'com';
                        let primary = exact + '.' + firstTld;
                        if (this.checkedResult[primary]) {
                            return {domain: primary, ...this.checkedResult[primary]};
                        }
                    }
                    return null;
                }
            };
        }
    </script>
@endsection
