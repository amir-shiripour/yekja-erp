@extends('layouts.user')

@section('title', 'مدیریت اکانت‌های دایرکت ادمین')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    if (!function_exists('formatDaBytes2')) {
        function formatDaBytes2($bytes, $precision = 1) {
            if ($bytes == -1 || $bytes === 'unlimited' || strtolower((string)$bytes) === 'unlimited') return 'نامحدود';
            if ($bytes <= 0) return '۰ MB';
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $val = $bytes / pow(1024, $pow);
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $formatted = str_replace(range(0, 9), $persian, (string)round($val, $precision));
            return $formatted . ' ' . $units[$pow];
        }
    }

    if (!function_exists('daSortUrl')) {
        function daSortUrl($col) {
            $currentCol = request('sort_by');
            $currentOrder = strtolower(request('sort_order', 'desc'));
            $newOrder = ($currentCol === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort_by' => $col, 'sort_order' => $newOrder]);
        }
    }
@endphp

@section('content')
<div x-data="accountsManager()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('directadmin::partials.toast')

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </span>
                <span>اکانت‌های دایرکت ادمین</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 ms-1">
                مدیریت حساب‌های میزبانی، سهمیه دیسک، وضعیت تعلیق و اتصال به مشتریان در سرورهای دایرکت ادمین
            </p>
        </div>

        <div class="flex items-center gap-3">
            @can('directadmin.accounts.create')
                <a href="{{ route('user.directadmin.accounts.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ایجاد اکانت جدید</span>
                </a>
            @endcan
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
            class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 text-red-800 dark:text-red-400 text-sm font-bold flex items-center gap-3 shadow-xs animate-fade-in">
            <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Quick Stats Cards --}}
    @if(isset($stats))
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">کل اکانت‌ها</span>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $faNum($stats['total']) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">اکانت‌های فعال</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1"
                       x-text="faNum(stats.active)">{{ $faNum($stats['active']) }}</p>
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
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">اکانت‌های مسدود</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1"
                       x-text="faNum(stats.suspended)">{{ $faNum($stats['suspended']) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">سرورهای متصل</span>
                    <p class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">{{ $faNum($stats['servers']) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    {{-- Filters Form --}}
    <form method="GET"
          class="bg-white dark:bg-gray-800/60 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
            {{-- Search Input --}}
            <div class="relative lg:col-span-3">
                <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="جستجوی نام کاربری، دامنه یا ایمیل..."
                       class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
            </div>

            {{-- Server Filter --}}
            <div class="lg:col-span-3">
                <select name="server_id"
                        class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                    <option value="">همه سرورها</option>
                    @foreach($servers as $srv)
                        <option value="{{ $srv->id }}" @selected(request('server_id') == $srv->id)>
                            {{ $srv->name }} ({{ $srv->host }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Package Filter --}}
            <div class="lg:col-span-2">
                <select name="package"
                        class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                    <option value="">همه پکیج‌ها</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg }}" @selected(request('package') === $pkg)>{{ $pkg }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="lg:col-span-2">
                <select name="status"
                        class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" @selected(request('status') === 'active')>فعال (Active)</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>مسدود (Suspended)</option>
                </select>
            </div>

            {{-- Filter Action Buttons --}}
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all cursor-pointer">
                    اعمال فیلتر
                </button>
                @if(request()->anyFilled(['q', 'server_id', 'status', 'package']))
                    <a href="{{ route('user.directadmin.accounts.index') }}"
                       class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all shrink-0 cursor-pointer"
                       title="پاکسازی فیلترها">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Server Sync Section --}}
    @if(request('server_id') && $servers->firstWhere('id', request('server_id')))
        @php
            $syncSrv = $servers->firstWhere('id', request('server_id'));
        @endphp
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100/70 dark:border-indigo-800/30 rounded-3xl backdrop-blur-xl">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                        همگام‌سازی اکانت‌های سرور <span
                            class="text-indigo-600 dark:text-indigo-400">{{ $syncSrv->name }}</span>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        دریافت آخرین اطلاعات اکانت‌ها و میزان مصرف دیسک از طریق API دایرکت ادمین
                    </p>
                </div>
            </div>
            <form action="{{ route('user.directadmin.accounts.sync') }}" method="POST"
                  class="shrink-0 w-full sm:w-auto">
                @csrf
                <input type="hidden" name="server_id" value="{{ $syncSrv->id }}">
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>همگام‌سازی با سرور</span>
                </button>
            </form>
        </div>
    @endif

    {{-- Accounts Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
        @if($accounts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">
                            <a href="{{ daSortUrl('user') }}"
                               class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                <span>کاربر / دامنه</span>
                                <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-start">
                            <a href="{{ daSortUrl('client') }}"
                               class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                <span>مشتری CRM</span>
                                <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-start">سرور میزبان</th>
                        <th class="px-6 py-4 text-center">
                            <a href="{{ daSortUrl('package') }}"
                               class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                <span>پکیج</span>
                                <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-start">
                            <a href="{{ daSortUrl('disk') }}"
                               class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                <span>مصرف دیسک</span>
                                <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <a href="{{ daSortUrl('status') }}"
                               class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                <span>وضعیت</span>
                                <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-start">
                            <a href="{{ daSortUrl('synced') }}"
                               class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                <span>آخرین سینک</span>
                                <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-end">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($accounts as $acc)
                        <tr id="account-row-{{ $acc->id }}"
                            class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            {{-- User / Domain --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-indigo-100/50 dark:border-indigo-500/20">
                                        {{ substr($acc->username, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('user.directadmin.accounts.show', $acc->username) }}"
                                           class="font-black text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block text-sm font-mono">
                                            {{ $acc->username }}
                                        </a>
                                        <span
                                            class="text-xs text-gray-400 dark:text-gray-500 block truncate font-mono mt-0.5"
                                            dir="ltr">
                                                {{ $acc->domain }}
                                            </span>
                                    </div>
                                    <a href="http://{{ $acc->domain }}" target="_blank" rel="noopener"
                                       class="p-1 rounded-lg text-gray-300 hover:text-indigo-500 transition-colors"
                                       title="مشاهده وب‌سایت در تب جدید">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>

                            {{-- Client / Customer / Order --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="space-y-1">
                                    @if($acc->client)
                                        <a href="{{ route('user.clients.show', $acc->client->id) }}"
                                           class="font-semibold text-sm text-gray-800 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $acc->client->full_name }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 font-medium">—</span>
                                    @endif

                                    @if($acc->order)
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('services.orders.show', $acc->order_id) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400 text-xs font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                               title="مشاهده سفارش متصل">
                                                <span>سفارش #{{ $acc->order->order_number }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Server --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span class="font-bold">{{ $acc->server->name ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Package --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg border border-gray-200/60 dark:border-gray-600/50">
                                        {{ $acc->package }}
                                    </span>
                            </td>

                            {{-- Disk Usage Progress bar --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $diskUsed = $acc->disk_usage_bytes;
                                    $diskLimit = $acc->disk_limit_bytes;
                                    $diskUnlimited = ($diskLimit == -1 || $diskLimit === 'unlimited' || strtolower((string)$diskLimit) === 'unlimited');
                                    $diskPercent = (!$diskUnlimited && $diskLimit > 0) ? min(round(($diskUsed / $diskLimit) * 100, 1), 100) : 0;
                                @endphp
                                <div class="flex flex-col gap-1.5 w-44">
                                    <div
                                        class="flex justify-between text-xs font-bold text-gray-600 dark:text-gray-400 tabular-nums">
                                        <span
                                            class="text-gray-900 dark:text-gray-200">{{ formatDaBytes2($diskUsed) }}</span>
                                        <span
                                            class="text-gray-400">/ {{ $diskUnlimited ? 'نامحدود' : formatDaBytes2($diskLimit) }}</span>
                                    </div>
                                    @if(!$diskUnlimited)
                                        <div
                                            class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                            <div class="bg-indigo-600 h-full rounded-full transition-all duration-300"
                                                 style="width: {{ $diskPercent }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center" id="account-status-{{ $acc->id }}">
                                @if($acc->suspended)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            مسدود (Suspended)
                                        </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            فعال (Active)
                                        </span>
                                @endif
                            </td>

                            {{-- Synced At --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                                {{ $acc->synced_at ? $faNum(jdate($acc->synced_at)->format('Y/m/d H:i')) : '—' }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Show details --}}
                                    <a href="{{ route('user.directadmin.accounts.show', $acc->username) }}"
                                       class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors shadow-2xs"
                                       title="جزئیات و تنظیمات">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    {{-- Real-time Suspend/Unsuspend Button --}}
                                    @can('directadmin.accounts.suspend')
                                        <button type="button"
                                                id="btn-suspend-{{ $acc->id }}"
                                                @click="openSuspendModal({
                                                        id: {{ $acc->id }},
                                                        username: '{{ $acc->username }}',
                                                        domain: '{{ $acc->domain }}',
                                                        server_name: '{{ $acc->server->name ?? 'سرور' }}',
                                                        suspended: {{ $acc->suspended ? 'true' : 'false' }},
                                                        toggle_url: '{{ route('user.directadmin.accounts.toggle-suspension', $acc) }}'
                                                    })"
                                                class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-amber-600 hover:bg-amber-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-amber-900/30 dark:hover:text-amber-400 transition-colors shadow-2xs cursor-pointer"
                                                title="{{ $acc->suspended ? 'رفع مسدودیت اکانت' : 'مسدودسازی اکانت' }}">
                                            @if($acc->suspended)
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($accounts->total() > 0)
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            نمایش
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($accounts->firstItem() ?? 0) }}</strong>
                            تا
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($accounts->lastItem() ?? 0) }}</strong>
                            از
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($accounts->total()) }}</strong>
                            اکانت
                        </span>

                        <span class="text-gray-300 dark:text-gray-600">•</span>

                        <form method="GET" class="inline-flex items-center gap-1.5 m-0">
                            @if(request('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            @if(request('server_id'))
                                <input type="hidden" name="server_id" value="{{ request('server_id') }}">
                            @endif
                            @if(request('package'))
                                <input type="hidden" name="package" value="{{ request('package') }}">
                            @endif
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('sort_by'))
                                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                            @endif
                            @if(request('sort_order'))
                                <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                            @endif
                            <span>تعداد در صفحه:</span>
                            <select name="per_page" onchange="this.form.submit()"
                                    class="rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1 px-2 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                                @foreach([10, 15, 25, 50, 100] as $size)
                                    <option
                                        value="{{ $size }}" @selected(request('per_page', 15) == $size)>{{ $faNum($size) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @if($accounts->hasPages())
                        <div>
                            {{ $accounts->withQueryString()->links('directadmin::partials.pagination') }}
                        </div>
                    @endif
                </div>
            @endif
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ اکانت میزبانی یافت نشد</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        اکانتی مطابق با فیلترهای انتخابی پیدا نشد. می‌توانید فیلترها را تغییر داده یا از دکمه
                        «همگام‌سازی با سرور» جهت بروزرسانی لیست اکانت‌ها استفاده کنید.
                    </p>
                </div>
                @if(request()->anyFilled(['q', 'server_id', 'status', 'package']))
                    <a href="{{ route('user.directadmin.accounts.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        پاک‌سازی تمام فیلترها
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- Reusable Real-time Suspend/Unsuspend Confirmation Modal --}}
    @include('directadmin::partials.suspend-modal')
</div>

<script>
    function accountsManager() {
        return {
            suspendModalOpen: false,
            selectedAccount: {
                id: null,
                username: '',
                domain: '',
                server: '',
                suspended: false,
                toggle_url: ''
            },
            loading: false,
            stats: {
                active: {{ $stats['active'] ?? 0 }},
                suspended: {{ $stats['suspended'] ?? 0 }}
            },
            toast: {
                show: false,
                message: '',
                type: 'success',
                timer: null
            },
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                clearTimeout(this.toast.timer);
                this.toast.timer = setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },
            faNum(str) {
                if (str === null || str === undefined) return '';
                const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                return String(str).replace(/[0-9]/g, function (w) {
                    return persian[+w];
                });
            },
            openSuspendModal(account) {
                this.selectedAccount = {
                    id: account.id,
                    username: account.username,
                    domain: account.domain,
                    server: account.server_name || account.server || '',
                    suspended: account.suspended,
                    toggle_url: account.toggle_url
                };
                this.suspendModalOpen = true;
            },
            closeModal() {
                this.suspendModalOpen = false;
                this.loading = false;
            },
            async confirmSuspend() {
                if (!this.selectedAccount || this.loading) return;
                this.loading = true;

                try {
                    const response = await fetch(this.selectedAccount.toggle_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        const accId = this.selectedAccount.id;
                        const isNowSuspended = data.suspended;

                        // Update Table Row Badge
                        const statusCell = document.getElementById(`account-status-${accId}`);
                        if (statusCell) {
                            if (isNowSuspended) {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        مسدود (Suspended)
                                    </span>
                                `;
                            } else {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        فعال (Active)
                                    </span>
                                `;
                            }
                        }

                        // Update Button Icon and Title
                        const btn = document.getElementById(`btn-suspend-${accId}`);
                        if (btn) {
                            btn.setAttribute('title', isNowSuspended ? 'رفع مسدودیت اکانت' : 'مسدودسازی اکانت');
                            if (isNowSuspended) {
                                btn.innerHTML = `
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                `;
                            } else {
                                btn.innerHTML = `
                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                `;
                            }
                        }

                        // Update Stats
                        if (isNowSuspended) {
                            this.stats.suspended++;
                            if (this.stats.active > 0) this.stats.active--;
                        } else {
                            this.stats.active++;
                            if (this.stats.suspended > 0) this.stats.suspended--;
                        }

                        this.showToast(data.message, 'success');
                        this.closeModal();
                    } else {
                        this.showToast(data.message || 'خطا در تغییر وضعیت اکانت.', 'error');
                        this.loading = false;
                    }
                } catch (err) {
                    this.showToast('خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error');
                    this.loading = false;
                }
            }
        };
    }
</script>
@endsection

