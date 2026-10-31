@extends('layouts.user')

@section('title', 'تعرفه و قیمت پسوندها (TLDs)')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
         x-data="{ query: '' }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
                    <span>تعرفه و قیمت پسوندها (TLDs)</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    جدول قیمت‌های زنده و رسمی ثبت، تمدید و انتقال دامنه‌ها در کارگزاری
                </p>
            </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.domainmanager.search.index') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-95">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>جستجو و ثبت دامنه</span>
            </a>
        </div>
    </div>

    {{-- Error Banner --}}
    @if($error)
        <div
            class="p-5 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 flex items-center gap-4 shadow-sm">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ $error }}
            </div>
        </div>
    @endif

    {{-- Filter Search Bar --}}
    @if(!empty($prices))
        <div
            class="bg-white dark:bg-gray-800/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" x-model="query" placeholder="فیلتر پسوند (مثلاً com یا ir)..."
                       class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 ps-10 pe-4 py-2 text-xs sm:text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white font-mono text-start"
                       dir="ltr">
            </div>
        </div>
    @endif

    {{-- Pricing Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
        @if(!empty($prices))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">پسوند دامنه (TLD)</th>
                        <th class="px-6 py-4 text-start">هزینه ثبت (Register)</th>
                        <th class="px-6 py-4 text-start">هزینه تمدید (Renew)</th>
                        <th class="px-6 py-4 text-start">هزینه انتقال (Transfer)</th>
                        <th class="px-6 py-4 text-center">وضعیت تخفیف / پروموشن</th>
                        <th class="px-6 py-4 text-end">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($prices as $tld => $pData)
                        @php
                            $regPrice = is_array($pData) ? ($pData['register'] ?? $pData['registration'] ?? $pData['add'] ?? '—') : $pData;
                            $renewPrice = is_array($pData) ? ($pData['renew'] ?? $pData['renewal'] ?? '—') : '—';
                            $transPrice = is_array($pData) ? ($pData['transfer'] ?? '—') : '—';
                            $promo = is_array($pData) ? ($pData['promo'] ?? null) : null;
                            $currency = is_array($pData) ? ($pData['currency'] ?? 'USD') : 'USD';
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200"
                            x-show="!query || '{{ strtolower($tld) }}'.includes(query.toLowerCase().trim().replace('.', ''))">
                            {{-- TLD --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs shrink-0 border border-indigo-100/50 dark:border-indigo-500/20 font-mono">
                                        .
                                    </div>
                                    <span
                                        class="font-mono font-black text-gray-900 dark:text-white text-base select-all"
                                        dir="ltr">
                                            .{{ ltrim($tld, '.') }}
                                        </span>
                                </div>
                            </td>

                            {{-- Register Price --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white font-mono"
                                dir="ltr">
                                {{ is_numeric($regPrice) ? $faNum(number_format($regPrice, 2)) . ' ' . $currency : $regPrice }}
                            </td>

                            {{-- Renew Price --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 font-mono"
                                dir="ltr">
                                {{ is_numeric($renewPrice) ? $faNum(number_format($renewPrice, 2)) . ' ' . $currency : $renewPrice }}
                            </td>

                            {{-- Transfer Price --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 font-mono"
                                dir="ltr">
                                {{ is_numeric($transPrice) ? $faNum(number_format($transPrice, 2)) . ' ' . $currency : $transPrice }}
                            </td>

                            {{-- Promo status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($promo)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            تخفیف فعال: {{ $promo }}
                                        </span>
                                @else
                                    <span class="text-xs text-gray-400 font-medium">تعرفه استاندارد</span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <a href="{{ route('user.domainmanager.search.index') }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-50 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors text-xs font-bold shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                    <span>استعلام و ثبت</span>
                                </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">جدول تعرفه‌ها در دسترس نیست</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        امکان دریافت زنده جدول قیمت‌ها از سرور کارگزار وجود ندارد. لطفاً ارتباط حساب کارگزاری را در بخش
                        تنظیمات بررسی فرمایید.
                    </p>
                </div>
                <a href="{{ route('user.domainmanager.connections.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    بررسی تنظیمات کارگزار
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
