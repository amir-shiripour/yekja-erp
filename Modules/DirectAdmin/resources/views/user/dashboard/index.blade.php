@extends('layouts.user')

@section('title', 'داشبورد سرور دایرکت ادمین')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    if (!function_exists('formatDaBytesDash')) {
        function formatDaBytesDash($bytes, $precision = 1) {
            if ($bytes == -1 || $bytes === 'unlimited' || strtolower((string)$bytes) === 'unlimited') return 'نامحدود';
            if ($bytes <= 0) return '۰ مگابایت';
            $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $val = $bytes / pow(1024, $pow);
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $formatted = str_replace(range(0, 9), $persian, (string)round($val, $precision));
            return $formatted . ' ' . $units[$pow];
        }
    }
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header & Server Switcher --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
                <span>داشبورد سرور دایرکت ادمین</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                مشاهده وضعیت زنده، مدیریت منابع و نظارت بر اکانت‌های سرور <span
                    class="font-black text-gray-800 dark:text-gray-200">{{ $server->name }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($servers->count() > 1)
                <form method="GET" action="{{ route('user.directadmin.dashboard') }}" class="flex items-center">
                    <select name="server_id" onchange="this.form.submit()"
                            class="{{ $inputClass }} cursor-pointer font-bold text-xs sm:text-sm w-auto">
                        @foreach($servers as $srv)
                            <option value="{{ $srv->id }}" @selected($srv->id === $server->id)>سرور: {{ $srv->name }}
                                ({{ $srv->host }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <a href="{{ route('user.directadmin.accounts.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>لیست اکانت‌ها</span>
            </a>

            <form action="{{ route('user.directadmin.accounts.sync') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="server_id" value="{{ $server->id }}">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>همگام‌سازی اکانت‌ها</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Connection Alert if Failed --}}
    @if(!$connection['success'])
        <div
            class="p-5 rounded-3xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-800 dark:text-rose-300 text-sm flex flex-col sm:flex-row gap-4 items-start shadow-sm animate-in fade-in">
            <div
                class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <span class="font-black block text-base mb-1">خطا در اتصال به سرور دایرکت ادمین!</span>
                <p class="text-xs opacity-90">سیستم در برقراری ارتباط با وب‌سرویس سرور با خطا مواجه شد. اطلاعات نمایش
                    داده شده مربوط به آخرین وضعیت ذخیره شده در دیتابیس است.</p>
                <span
                    class="inline-block mt-2 font-mono text-xs bg-rose-100/70 dark:bg-rose-950/50 p-2.5 rounded-xl border border-rose-200/60 dark:border-rose-900/40 font-bold"
                    dir="ltr">
                    {{ $connection['error'] }}
                </span>
            </div>
        </div>
    @endif

    {{-- Quick stats block --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Stat 1: Connection Status --}}
        <div class="{{ $cardClass }} p-6">
            <div class="flex items-center gap-4">
                @if($connection['success'])
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت اتصال زنده</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">آنلاین</span>
                            <span
                                class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-black">
                                {{ $faNum($connection['latency']) }} ms
                            </span>
                        </div>
                    </div>
                @else
                    <div
                        class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت اتصال زنده</p>
                        <p class="text-lg font-black text-rose-600 dark:text-rose-400 mt-1">قطع ارتباط</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stat 2: Total Accounts --}}
        <div class="{{ $cardClass }} p-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">تعداد کل اکانت‌ها</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">{{ $faNum($localStats['total_accounts']) }}</p>
                </div>
            </div>
        </div>

        {{-- Stat 3: Active Accounts --}}
        <div class="{{ $cardClass }} p-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">اکانت‌های فعال</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $faNum($localStats['active_accounts']) }}</p>
                </div>
            </div>
        </div>

        {{-- Stat 4: Suspended Accounts --}}
        <div class="{{ $cardClass }} p-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">اکانت‌های مسدود (Suspend)</p>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-0.5">{{ $faNum($localStats['suspended_accounts']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Server System Info and Allocated Resource stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Card: Server info --}}
        <div class="{{ $cardClass }} lg:col-span-1 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    <div
                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                    </div>
                    <span>مشخصات فنی سرور</span>
                </h2>
            </div>

            <div class="p-6 space-y-4 text-sm">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">سیستم‌عامل سرور:</span>
                    <span class="font-black text-gray-900 dark:text-gray-100">{{ $serverState['os'] ?? 'Linux' }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">نسخه DirectAdmin:</span>
                    <span
                        class="font-mono text-xs font-black text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700/60 px-2.5 py-1 rounded-lg"
                        dir="ltr">{{ $serverState['version'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">پوسته فعال:</span>
                    <span
                        class="font-bold text-gray-900 dark:text-gray-100">{{ $serverState['theme'] ?? 'Evolution' }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">شناسه IP سرور:</span>
                    <span class="font-mono text-xs font-bold text-gray-900 dark:text-gray-100"
                          dir="ltr">{{ $server->host }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">پورت پنل:</span>
                    <span class="font-mono text-xs font-bold text-gray-900 dark:text-gray-100"
                          dir="ltr">{{ $server->port }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400 font-bold">پکیج نماینده:</span>
                    <span
                        class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-black text-xs">
                        {{ $resellerConfig['package'] ?? 'نمایندگی (Reseller)' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card: Resource Aggregation --}}
        <div class="{{ $cardClass }} lg:col-span-2 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    <div
                        class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span>مصرف کل منابع کاربران روی سرور</span>
                </h2>
                <span class="text-xs font-bold text-gray-400">آمار منابع نمایندگی</span>
            </div>

            @php
                $diskUsed = data_get($resellerUsage, 'quotaBytes.usage', 0);
                $diskLimit = data_get($resellerUsage, 'quotaBytes.limit', 0);
                $diskAllocated = data_get($resellerUsage, 'quotaBytes.allocation', 0);
                $diskUnlimited = data_get($resellerUsage, 'quotaBytes.unlimited', false) || $diskLimit == -1 || $diskLimit === 'unlimited' || strtolower((string)$diskLimit) === 'unlimited';
                $diskPercent = (!$diskUnlimited && $diskLimit > 0) ? min(round(($diskUsed / $diskLimit) * 100, 1), 100) : 0;

                $bandwidthUsed = data_get($resellerUsage, 'bandwidthBytes.usage', 0);
                $bandwidthLimit = data_get($resellerUsage, 'bandwidthBytes.limit', 0);
                $bandwidthAllocated = data_get($resellerUsage, 'bandwidthBytes.allocation', 0);
                $bandwidthUnlimited = data_get($resellerUsage, 'bandwidthBytes.unlimited', false) || $bandwidthLimit == -1 || $bandwidthLimit === 'unlimited' || strtolower((string)$bandwidthLimit) === 'unlimited';
                $bandwidthPercent = (!$bandwidthUnlimited && $bandwidthLimit > 0) ? min(round(($bandwidthUsed / $bandwidthLimit) * 100, 1), 100) : 0;

                $usersUsed = data_get($resellerUsage, 'users.usage', 0);
                $usersLimit = data_get($resellerUsage, 'users.limit', 0);
                $usersUnlimited = data_get($resellerUsage, 'users.unlimited', false) || $usersLimit == -1 || $usersLimit === 'unlimited' || strtolower((string)$usersLimit) === 'unlimited';
                $usersPercent = (!$usersUnlimited && $usersLimit > 0) ? min(round(($usersUsed / $usersLimit) * 100, 1), 100) : 0;
            @endphp

            <div class="p-6 space-y-6">
                {{-- Disk space progress bar --}}
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span
                            class="font-black text-gray-900 dark:text-white text-sm">فضای دیسک نمایندگی (Disk Space)</span>
                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white">{{ formatDaBytesDash($diskUsed) }}</span>
                            <span>از</span>
                            <span
                                class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $diskUnlimited ? 'نامحدود' : formatDaBytesDash($diskLimit) }}</span>
                            @if(!$diskUnlimited)
                                <span
                                    class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-black">
                                    {{ $faNum($diskPercent) }}٪
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(!$diskUnlimited)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden p-0.5">
                            <div
                                class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-full rounded-full transition-all duration-500 shadow-sm"
                                style="width: {{ $diskPercent }}%"></div>
                        </div>
                    @endif
                    <div class="flex flex-wrap justify-between gap-2 text-[11px] text-gray-400 font-bold pt-1">
                        <span>مقدار تخصیص‌داده‌شده: <span
                                class="text-gray-700 dark:text-gray-300">{{ formatDaBytesDash($diskAllocated) }}</span></span>
                        <span>باقی‌مانده: <span
                                class="text-gray-700 dark:text-gray-300">{{ $diskUnlimited ? 'نامحدود' : (($diskLimit > $diskAllocated) ? formatDaBytesDash($diskLimit - $diskAllocated) : '—') }}</span></span>
                    </div>
                </div>

                {{-- Bandwidth progress bar --}}
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="font-black text-gray-900 dark:text-white text-sm">پهنای باند ماهیانه نمایندگی (Bandwidth)</span>
                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white">{{ formatDaBytesDash($bandwidthUsed) }}</span>
                            <span>از</span>
                            <span
                                class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $bandwidthUnlimited ? 'نامحدود' : formatDaBytesDash($bandwidthLimit) }}</span>
                            @if(!$bandwidthUnlimited)
                                <span
                                    class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-black">
                                    {{ $faNum($bandwidthPercent) }}٪
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(!$bandwidthUnlimited)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden p-0.5">
                            <div
                                class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-full rounded-full transition-all duration-500 shadow-sm"
                                style="width: {{ $bandwidthPercent }}%"></div>
                        </div>
                    @endif
                    <div class="flex flex-wrap justify-between gap-2 text-[11px] text-gray-400 font-bold pt-1">
                        <span>مقدار تخصیص‌داده‌شده: <span
                                class="text-gray-700 dark:text-gray-300">{{ formatDaBytesDash($bandwidthAllocated) }}</span></span>
                        <span>باقی‌مانده: <span
                                class="text-gray-700 dark:text-gray-300">{{ $bandwidthUnlimited ? 'نامحدود' : (($bandwidthLimit > $bandwidthAllocated) ? formatDaBytesDash($bandwidthLimit - $bandwidthAllocated) : '—') }}</span></span>
                    </div>
                </div>

                {{-- Users Count progress bar --}}
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span
                            class="font-black text-gray-900 dark:text-white text-sm">تعداد کاربران مجاز (Users Count)</span>
                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white">{{ $faNum($usersUsed) }}</span>
                            <span>از</span>
                            <span
                                class="text-sm font-black text-amber-600 dark:text-amber-400">{{ $usersUnlimited ? 'نامحدود' : $faNum($usersLimit) }}</span>
                            @if(!$usersUnlimited)
                                <span
                                    class="px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 text-xs font-black">
                                    {{ $faNum($usersPercent) }}٪
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(!$usersUnlimited)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden p-0.5">
                            <div
                                class="bg-gradient-to-r from-amber-500 to-amber-600 h-full rounded-full transition-all duration-500 shadow-sm"
                                style="width: {{ $usersPercent }}%"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Resource Counts --}}
    <div class="{{ $cardClass }} overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
            <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <span>منابع اختصاص یافته در سرور (تعداد کل کاربران)</span>
            </h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Databases --}}
                @php
                    $dbLimit = data_get($resellerUsage, 'mySqlDatabases.limit', 0);
                    $dbUnlimited = data_get($resellerUsage, 'mySqlDatabases.unlimited', false) || $dbLimit == -1 || $dbLimit === 'unlimited' || strtolower((string)$dbLimit) === 'unlimited';
                @endphp
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">پایگاه‌های داده (MySQL)</span>
                        <span
                            class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-gray-900 dark:text-white">
                            {{ $faNum(data_get($resellerUsage, 'mySqlDatabases.usage', 0)) }}
                        </span>
                        <span class="text-xs font-bold text-gray-400">
                            / {{ $dbUnlimited ? 'نامحدود' : $faNum($dbLimit) }}
                        </span>
                    </div>
                </div>

                {{-- Domains --}}
                @php
                    $domLimit = data_get($resellerUsage, 'domains.limit', 0);
                    $domUnlimited = data_get($resellerUsage, 'domains.unlimited', false) || $domLimit == -1 || $domLimit === 'unlimited' || strtolower((string)$domLimit) === 'unlimited';
                @endphp
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">دامنه‌های ایجاد شده</span>
                        <span
                            class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-gray-900 dark:text-white">
                            {{ $faNum(data_get($resellerUsage, 'domains.usage', 0)) }}
                        </span>
                        <span class="text-xs font-bold text-gray-400">
                            / {{ $domUnlimited ? 'نامحدود' : $faNum($domLimit) }}
                        </span>
                    </div>
                </div>

                {{-- Email accounts --}}
                @php
                    $emailLimit = data_get($resellerUsage, 'emailAccounts.limit', 0);
                    $emailUnlimited = data_get($resellerUsage, 'emailAccounts.unlimited', false) || $emailLimit == -1 || $emailLimit === 'unlimited' || strtolower((string)$emailLimit) === 'unlimited';
                @endphp
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">حساب‌های ایمیل</span>
                        <span
                            class="p-1.5 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-gray-900 dark:text-white">
                            {{ $faNum(data_get($resellerUsage, 'emailAccounts.usage', 0)) }}
                        </span>
                        <span class="text-xs font-bold text-gray-400">
                            / {{ $emailUnlimited ? 'نامحدود' : $faNum($emailLimit) }}
                        </span>
                    </div>
                </div>

                {{-- FTP Accounts --}}
                @php
                    $ftpLimit = data_get($resellerUsage, 'ftpAccounts.limit', 0);
                    $ftpUnlimited = data_get($resellerUsage, 'ftpAccounts.unlimited', false) || $ftpLimit == -1 || $ftpLimit === 'unlimited' || strtolower((string)$ftpLimit) === 'unlimited';
                @endphp
                <div
                    class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">حساب‌های FTP</span>
                        <span
                            class="p-1.5 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-gray-900 dark:text-white">
                            {{ $faNum(data_get($resellerUsage, 'ftpAccounts.usage', 0)) }}
                        </span>
                        <span class="text-xs font-bold text-gray-400">
                            / {{ $ftpUnlimited ? 'نامحدود' : $faNum($ftpLimit) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
