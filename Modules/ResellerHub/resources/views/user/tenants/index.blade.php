@extends('layouts.user')

@section('title', 'مدیریت سامانه‌ها و کاربران')

@section('content')
    <div x-data="resellerDashboard({
            activeTab: 'fleet',
            hubTab: '{{ request()->input('tab', $activeTab ?? 'tenants') }}',
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

        {{-- Header Section (Enclosed in cohesive card container) --}}
        <header class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-5 sm:p-6 shadow-sm backdrop-blur-xl transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-3 min-w-0">
                    <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500" aria-label="مسیر راهنما">
                        <a href="{{ route('user.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <a href="{{ route('reseller.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">نمایندگی فروش</a>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black">مدیریت سامانه‌ها و کاربران</span>
                    </nav>

                    <div class="flex items-center gap-3.5">
                        <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2.5">
                                <span>مدیریت سامانه‌ها و کاربران</span>
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap shrink-0">
                    <a href="{{ route('reseller.dashboard') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 border border-gray-200/80 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-all shadow-2xs cursor-pointer h-9 shrink-0">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>پیشخوان نمایندگی</span>
                    </a>

                    @can('resellerhub.sub-accounts.create')
                        <a href="{{ route('reseller.sub-accounts.create') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100/80 text-emerald-700 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800 text-xs font-bold transition-all shadow-2xs cursor-pointer h-9 shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span>ایجاد کاربر جدید</span>
                        </a>
                    @endcan

                    @can('resellerhub.tenants.create')
                        <a href="{{ route('reseller.tenants.create') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-sm shadow-indigo-500/20 transition-all cursor-pointer h-9 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>ساخت سامانه جدید</span>
                        </a>
                    @endcan
                </div>
            </div>
        </header>

        {{-- Unified Stat Counters --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Card 1: Total Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل سامانه‌های ابری</span>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ reseller_fa_num($totalTenants ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 2: Total Sub-Accounts --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کاربران</span>
                    <p class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1.5">{{ reseller_fa_num($totalSubAccounts ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 3: Active Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">سامانه‌های فعال</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ reseller_fa_num($activeTenants ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </x-resellerhub::card>

            {{-- Card 4: Suspended / Expired Tenants --}}
            <x-resellerhub::card class="p-5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">مسدود یا منقضی</span>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5">{{ reseller_fa_num(($suspendedTenants ?? 0) + ($expiredTenants ?? 0)) }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- Unified CRMs & Clients Table --}}
        <div id="unified-tenants-panel">
            @include('resellerhub::user.dashboard.partials.tenants-table')
        </div>

        {{-- Modal Partials --}}
        @include('resellerhub::partials.suspend-modal')
        @include('resellerhub::partials.renew-modal')
        @include('resellerhub::partials.tenant-delete-modal')
        @include('resellerhub::partials.subaccount-delete-modal')

        {{-- Scripts --}}
        @include('resellerhub::user.dashboard.partials.scripts')
    </div>
@endsection
