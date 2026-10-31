@extends('layouts.user')

@section('title', 'لاگ‌های فعالیت و رویدادهای دامنه')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span>لاگ‌ها و گزارشات سیستم دامنه</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                ثبت تاریخچه کامل درخواست‌ها، تمدیدها، تغییرات دی‌ان‌اس و پاسخ‌های وب‌سرویس کارگزاری
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('user.domainmanager.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                </svg>
                <span>پیشخوان دامنه</span>
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

    {{-- Quick Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Logs --}}
        <div
            class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span
                    class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">کل لاگ‌های ثبت‌شده</span>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $faNum($totalLogsCount ?? $logs->total()) }}</p>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>

        {{-- Success Operations --}}
        <div
            class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">عملیات‌های موفق</span>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $faNum($successLogsCount ?? 0) }}</p>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Failed / Error Operations --}}
        <div
            class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">خطاها و هشدارها</span>
                <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $faNum($failedLogsCount ?? 0) }}</p>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        {{-- Sync Operations --}}
        <div
            class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">همگام‌سازی‌های کش</span>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $faNum($syncLogsCount ?? 0) }}</p>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Filters Form Card --}}
    <form method="GET"
          class="bg-white dark:bg-gray-800/60 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
            {{-- Search Input (Domain or Message) --}}
            <div class="relative lg:col-span-4">
                <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="جستجوی نام دامنه یا شرح رویداد..."
                       class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white font-medium text-start">
            </div>

            {{-- Action Filter --}}
            <div class="lg:col-span-3">
                <select name="action"
                        class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                    <option value="">همه نوع عملیات‌ها</option>
                    <option value="register" @selected(request('action') === 'register')>ثبت دامنه (Register)</option>
                    <option value="renew" @selected(request('action') === 'renew')>تمدید دامنه (Renew)</option>
                    <option value="transfer" @selected(request('action') === 'transfer')>انتقال دامنه (Transfer)
                    </option>
                    <option value="sync" @selected(request('action') === 'sync')>همگام‌سازی دستی (Sync)</option>
                    <option value="sync_all" @selected(request('action') === 'sync_all')>همگام‌سازی دسته‌جمعی (Sync
                        All)
                    </option>
                    <option value="modify_ns" @selected(request('action') === 'modify_ns')>تغییر دی‌ان‌اس‌ها
                        (Nameservers)
                    </option>
                    <option value="modify_contacts" @selected(request('action') === 'modify_contacts')>ویرایش مشخصات
                        WHOIS
                    </option>
                    <option value="privacy_protection" @selected(request('action') === 'privacy_protection')>مخفی‌سازی
                        WHOIS
                    </option>
                    <option value="toggle_lock" @selected(request('action') === 'toggle_lock')>قفل انتقال (Lock)
                    </option>
                    <option value="child_ns" @selected(request('action') === 'child_ns')>کارگزار فرزند (Child NS)
                    </option>
                    <option value="dnssec" @selected(request('action') === 'dnssec')>امنیت DNSSEC</option>
                    <option value="epp" @selected(request('action') === 'epp')>کد انتقال (EPP)</option>
                    <option value="update" @selected(request('action') === 'update')>تغییر مالک/مشتری</option>
                </select>
            </div>

            {{-- Reseller Filter --}}
            <div class="lg:col-span-2">
                <select name="reseller_id"
                        class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                    <option value="">همه کارگزاران</option>
                    @foreach($resellers as $res)
                        <option value="{{ $res->id }}" @selected(request('reseller_id') == $res->id)>
                            {{ $res->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="lg:col-span-2">
                <select name="status"
                        class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="success" @selected(request('status') === 'success')>موفق (Success)</option>
                    <option value="failed" @selected(request('status') === 'failed')>دارای خطا (Failed)</option>
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="lg:col-span-1 flex items-center gap-1.5">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center p-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all cursor-pointer"
                        title="اعمال فیلتر">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
                @if(request()->anyFilled(['q', 'action', 'reseller_id', 'status']))
                    <a href="{{ route('user.domainmanager.logs.index') }}"
                       class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all shrink-0 cursor-pointer"
                       title="پاکسازی فیلترها">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Activity Logs Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
        @if($logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">نوع عملیات</th>
                        <th class="px-6 py-4 text-start">دامنه مرتبط</th>
                        <th class="px-6 py-4 text-start">کارگزار</th>
                        <th class="px-6 py-4 text-start">کاربر</th>
                        <th class="px-6 py-4 text-start">شرح رویداد و پیام وب‌سرویس</th>
                        <th class="px-6 py-4 text-center">وضعیت</th>
                        <th class="px-6 py-4 text-start">زمان ثبت</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($logs as $log)
                        @php
                            $action = strtolower($log->action ?? 'info');
                            $actionLabel = match($action) {
                                'sync', 'sync_all' => 'همگام‌سازی',
                                'renew' => 'تمدید دامنه',
                                'register' => 'ثبت دامنه',
                                'transfer' => 'انتقال دامنه',
                                'modify_ns' => 'تغییر DNS',
                                'modify_contacts' => 'ویرایش WHOIS',
                                'privacy_protection' => 'مخفی‌سازی',
                                'toggle_lock' => 'قفل انتقال',
                                'child_ns' => 'دی‌ان‌اس فرزند',
                                'dnssec' => 'امنیت DNSSEC',
                                'epp' => 'کد EPP',
                                'update' => 'تغییر مالک',
                                default => $action
                            };

                            $actionClass = match($action) {
                                'sync', 'sync_all' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-200/80 dark:border-indigo-500/20',
                                'renew', 'register' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-500/20',
                                'transfer' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200/80 dark:border-amber-500/20',
                                'modify_ns', 'dnssec', 'child_ns' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border-purple-200/80 dark:border-purple-500/20',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'
                            };

                            $isSuccess = ($log->status ?? 'success') === 'success';
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            {{-- Action Type Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $actionClass }}">
                                        {{ $actionLabel }}
                                    </span>
                            </td>

                                {{-- Domain Name --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->domain_name)
                                        <a href="{{ route('user.domainmanager.domains.show', $log->domain_name) }}"
                                           class="font-bold text-xs text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors select-all inline-flex items-center gap-1.5"
                                           dir="ltr">
                                            <span>{{ $log->domain_name }}</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 font-bold">—</span>
                                    @endif
                                </td>

                                {{-- Reseller --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300 font-bold">
                                    {{ $log->reseller->name ?? '—' }}
                                </td>

                                {{-- User --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400 font-medium">
                                    {{ $log->user->name ?? 'سیستم' }}
                                </td>

                                {{-- Description / Message --}}
                                <td class="px-6 py-4 text-xs font-medium text-gray-800 dark:text-gray-200 max-w-md break-words leading-relaxed">
                                    {{ $log->description }}
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($isSuccess)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            موفق
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            خطا
                                        </span>
                                    @endif
                                </td>

                                {{-- Time --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-bold">
                                    {{ $log->created_at ? $faNum(jdate($log->created_at)->format('Y/m/d H:i:s')) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    نمایش
                    <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($logs->firstItem() ?? 0) }}</strong>
                    تا
                    <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($logs->lastItem() ?? 0) }}</strong>
                    از
                    <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($logs->total()) }}</strong>
                    رویداد
                </div>

                @if($logs->hasPages())
                    <div>
                        {{ $logs->withQueryString()->links('domainmanager::partials.pagination') }}
                    </div>
                @endif
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ لاگی یافت نشد</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        رویدادی مطابق با فیلترهای انتخابی شما پیدا نشد. می‌توانید فیلترها را تغییر داده یا پاکسازی
                        نمایید.
                    </p>
                </div>
                @if(request()->anyFilled(['q', 'action', 'reseller_id', 'status']))
                    <a href="{{ route('user.domainmanager.logs.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        پاک‌سازی تمام فیلترها
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
