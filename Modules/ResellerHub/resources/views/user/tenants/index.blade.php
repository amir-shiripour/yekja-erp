@extends('layouts.user')

@section('title', 'مدیریت سامانه‌های ابری CRM')

@section('content')
    <div x-data="resellerDashboard({
            activeTab: 'fleet',
            selectedPeriod: '6m',
            chartLabels: [],
            tenantsData: [],
            revenueData: [],
            metrics: {},
            routes: {
                metrics: '{{ route('reseller.dashboard') }}',
                quickSubAccount: '{{ route('reseller.sub-accounts.quick-store') }}'
            }
         })"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                            <line x1="6" y1="6" x2="6.01" y2="6"></line>
                            <line x1="6" y1="18" x2="6.01" y2="18"></line>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5">
                            <span>مدیریت سامانه‌های ابری CRM</span>
                            <span
                                class="text-xs font-black px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                                Tenants Fleet
                            </span>
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                            مشاهده، جستجو، فیلتر و مدیریت ناوگان سامانه‌های مستقل، تمدید لایسنس و ورود مستقیم SSO
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('reseller.dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>پیشخوان نمایندگی</span>
                </a>

                @can('resellerhub.tenants.export')
                    <a href="{{ route('reseller.tenants.export') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60 text-xs sm:text-sm font-bold transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>استخراج دیتابیس (SQL Dump)</span>
                    </a>
                @endcan

                @can('resellerhub.tenants.create')
                    <a href="{{ route('reseller.tenants.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>افزودن سامانه جدید</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- Quick Stat Counters --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Card 1: Total Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل سامانه‌های ابری</span>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ reseller_fa_num($totalTenants ?? 0) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 2: Active Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">سامانه‌های فعال</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ reseller_fa_num($activeTenants ?? 0) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 3: Pending Payment Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">در انتظار تسویه</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">{{ reseller_fa_num($pendingPaymentTenants ?? 0) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 4: Suspended / Expired Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">مسدود یا منقضی</span>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5">{{ reseller_fa_num(($suspendedTenants ?? 0) + ($expiredTenants ?? 0)) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- Tenants Fleet Table Component --}}
        @include('resellerhub::user.dashboard.partials.tenants-table')

        {{-- Modal Partials --}}
        @include('resellerhub::partials.suspend-modal')
        @include('resellerhub::partials.renew-modal')
        @include('resellerhub::partials.tenant-delete-modal')

        {{-- Scripts --}}
        @include('resellerhub::user.dashboard.partials.scripts')
    </div>
@endsection
