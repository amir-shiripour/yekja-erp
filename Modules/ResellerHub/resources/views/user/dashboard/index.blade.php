@php
    use App\Tenancy\TenantManager;
@endphp

@extends('layouts.user')

@section('title', 'مرکز نمایندگی فروش')

@section('content')

    @include('resellerhub::user.dashboard.partials.scripts')

    <div
        x-data="resellerDashboard({
            activeTenants: {{ (int)$activeTenants }},
            suspendedTenants: {{ (int)$suspendedTenants }},
            totalTenants: {{ (int)$totalTenants }},
            expiredTenants: {{ (int)$expiredTenants }},
            selectedPeriod: '{{ $selectedPeriod }}',
            selectedPeriodLabel: '{{ $selectedPeriodLabel }}',
            periodRevenue: {{ (int)$periodRevenue }},
            periodRevenueFormatted: '{{ reseller_price($periodRevenue) }}',
            periodTenantsCreated: {{ (int)$periodTenantsCreated }},
            revenueGrowthPercent: {{ $revenueGrowthPercent !== null ? $revenueGrowthPercent : 'null' }},
            tenantsGrowthPercent: {{ $tenantsGrowthPercent !== null ? $tenantsGrowthPercent : 'null' }},
            todayJalali: '{{ reseller_format_jalali(now(), 'Y/m/d', false) }}',
            currencyLabel: '{{ $currencyLabel }}',
            dashboardUrl: '{{ route('reseller.dashboard') }}'
        })"
        class="space-y-6 sm:space-y-8 relative">

        <header
            class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-5 sm:p-6 shadow-sm backdrop-blur-xl transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-3 min-w-0">
                    <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                         aria-label="مسیر راهنما">
                        <a href="{{ route('user.dashboard') }}"
                           class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">نمایندگی فروش</span>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black">مرکز عملیات Reseller Hub</span>
                    </nav>

                    <div class="flex items-start sm:items-center gap-3.5">
                        <div
                            class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                                    مرکز مدیریت نمایندگی فروش
                                </h1>
                                @if($reseller)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        {{ $reseller->display_name }}
                                    </span>
                                @elseif($isSuperAdmin)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-800 text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                        دید فراگیر مدیر پلتفرم (Platform View)
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                واگذاری و استقرار آنی سامانه‌های CRM، مدیریت لایسنس، نظارت بر جریان درآمدی و
                                زیرمجموعه‌ها
                            </p>
                        </div>
                    </div>

                    {{-- Focused Meta Ribbon: Only Jalali Date & Current Analytical Period --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-200/60 dark:border-gray-700/60 text-xs font-bold text-gray-600 dark:text-gray-300">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ reseller_fa_num(reseller_format_jalali(now(), 'l، d F Y')) }}
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/50 text-xs font-bold text-indigo-700 dark:text-indigo-300">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>دوره تحلیلی:</span>
                            <span x-text="selectedPeriodLabel">{{ $selectedPeriodLabel }}</span>
                        </span>
                    </div>
                </div>

                {{-- Action Bar: Single Core Operational Action (CRM Creation) --}}
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('reseller.tenants.create') }}"
                       class="group inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-500/20 transition-all transform active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>ساخت CRM جدید</span>
                    </a>
                </div>
            </div>
        </header>

        @include('resellerhub::user.dashboard.partials.kpi-cards')
        <style>
            .spotlight-card {
                position: relative;
                overflow: hidden;
                transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, border-color 0.2s ease;
            }

            .spotlight-card:hover {
                transform: translateY(-2px);
            }

            .spotlight-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                border-radius: inherit;
                background: radial-gradient(350px circle at 50% 0%, rgba(99, 102, 241, 0.08), transparent 70%);
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
                z-index: 2;
            }

            .spotlight-card:hover::before {
                opacity: 1;
            }

            html.dark .spotlight-card::before {
                background: radial-gradient(350px circle at 50% 0%, rgba(129, 140, 248, 0.14), transparent 70%);
            }

            /* Dark Mode Seamless Cohesion: Eliminate stark white/light borders across cards, badges, and pipeline */
            html.dark .border-gray-100,
            html.dark .border-gray-200,
            html.dark [class*="border-gray-100"],
            html.dark [class*="border-gray-200"],
            html.dark [class*="border-gray-700"] {
                border-color: rgba(255, 255, 255, 0.08) !important;
            }

            html.dark [class*="border-gray-800"] {
                border-color: rgba(255, 255, 255, 0.05) !important;
            }

            /* Eliminate all pastel light 100/200 borders that appear white in dark mode */
            html.dark [class*="border-rose-100"],
            html.dark [class*="border-rose-200"] {
                border-color: rgba(244, 63, 94, 0.25) !important;
            }

            html.dark [class*="border-indigo-100"],
            html.dark [class*="border-indigo-200"] {
                border-color: rgba(99, 102, 241, 0.25) !important;
            }

            html.dark [class*="border-emerald-100"],
            html.dark [class*="border-emerald-200"] {
                border-color: rgba(16, 185, 129, 0.25) !important;
            }

            html.dark [class*="border-cyan-100"],
            html.dark [class*="border-cyan-200"] {
                border-color: rgba(6, 182, 212, 0.25) !important;
            }

            html.dark [class*="border-amber-100"],
            html.dark [class*="border-amber-200"] {
                border-color: rgba(245, 158, 11, 0.25) !important;
            }

            html.dark [class*="border-purple-100"],
            html.dark [class*="border-purple-200"] {
                border-color: rgba(168, 85, 247, 0.25) !important;
            }

            html.dark [class*="border-teal-100"],
            html.dark [class*="border-teal-200"] {
                border-color: rgba(20, 184, 166, 0.25) !important;
            }

            html.dark .pipeline-card-rose {
                border-color: rgba(244, 63, 94, 0.22) !important;
                background-color: rgba(17, 24, 39, 0.5) !important;
            }

            html.dark .pipeline-card-indigo {
                border-color: rgba(99, 102, 241, 0.22) !important;
                background-color: rgba(17, 24, 39, 0.5) !important;
            }

            html.dark .pipeline-card-emerald {
                border-color: rgba(16, 185, 129, 0.22) !important;
                background-color: rgba(17, 24, 39, 0.5) !important;
            }
        </style>

        <div class="space-y-3 pt-1">
            <div
                role="tablist" aria-label="بخش‌های داشبورد نماینده" class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-2.5 p-1.5 sm:p-2 bg-gray-100/80 dark:bg-gray-800/80 border border-gray-200/70 dark:border-gray-800/80 rounded-3xl backdrop-blur-xl shadow-xs">
                <button type="button" role="tab" id="tab-fleet" aria-controls="tabpanel-fleet" :aria-selected="activeTab === 'fleet' ? 'true' : 'false'"
                        @click="setTab('fleet')"
                        :class="activeTab === 'fleet' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/40 dark:hover:bg-gray-700/30'"
                        class="px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-2xl transition-all duration-200 flex items-center justify-between gap-2.5 text-start cursor-pointer group">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <span
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
                            :class="activeTab === 'fleet' ? 'bg-emerald-500 text-white shadow-xs shadow-emerald-500/20' : 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 border border-gray-200/60 dark:border-gray-700/60'">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black tracking-tight truncate">سامانه‌ها و مشترکین</div>
                            <div
                                class="text-xs text-gray-400 dark:text-gray-500 font-medium truncate hidden md:block">
                                پایش و جدول پایگاه‌ها
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-xs font-black tabular-nums shrink-0"
                          :class="activeTab === 'fleet' ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-gray-200/70 dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                        {{ reseller_fa_num($totalTenants) }}
                    </span>
                </button>
                <button type="button" role="tab" id="tab-operations" aria-controls="tabpanel-operations" :aria-selected="activeTab === 'operations' ? 'true' : 'false'"
                        @click="setTab('operations')"
                        :class="activeTab === 'operations' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/40 dark:hover:bg-gray-700/30'"
                        class="px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-2xl transition-all duration-200 flex items-center justify-between gap-2.5 text-start cursor-pointer group">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <span
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
                            :class="activeTab === 'operations' ? 'bg-rose-500 text-white shadow-xs shadow-rose-500/20' : 'bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 border border-gray-200/60 dark:border-gray-700/60'">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black tracking-tight truncate">مرکز عملیات و اقدام</div>
                            <div
                                class="text-xs text-gray-400 dark:text-gray-500 font-medium truncate hidden md:block">
                                سررسیدها و معوقات
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-xs font-black tabular-nums shrink-0"
                          :class="activeTab === 'operations' ? 'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/40' : 'bg-gray-200/70 dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                        {{ reseller_fa_num($expiringSoonTenants + $unpaidInvoicesCount) }} اقدام
                    </span>
                </button>
                <button type="button" role="tab" id="tab-analytics" aria-controls="tabpanel-analytics" :aria-selected="activeTab === 'analytics' ? 'true' : 'false'"
                        @click="setTab('analytics')"
                        :class="activeTab === 'analytics' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/40 dark:hover:bg-gray-700/30'"
                        class="px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-2xl transition-all duration-200 flex items-center justify-between gap-2.5 text-start cursor-pointer group">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <span
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
                            :class="activeTab === 'analytics' ? 'bg-indigo-500 text-white shadow-xs shadow-indigo-500/20' : 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border border-gray-200/60 dark:border-gray-700/60'">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black tracking-tight truncate">تحلیل‌های مالی و عملکرد
                            </div>
                            <div
                                class="text-xs text-gray-400 dark:text-gray-500 font-medium truncate hidden md:block">
                                روند درآمد و نقدینگی
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-xs font-black tabular-nums shrink-0"
                          :class="activeTab === 'analytics' ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/40' : 'bg-gray-200/70 dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                        {{ reseller_fa_num($activeRatio) }}٪
                    </span>
                </button>
                <button type="button" role="tab" id="tab-intelligence" aria-controls="tabpanel-intelligence" :aria-selected="activeTab === 'intelligence' ? 'true' : 'false'"
                        @click="setTab('intelligence')"
                        :class="activeTab === 'intelligence' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/40 dark:hover:bg-gray-700/30'"
                        class="px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-2xl transition-all duration-200 flex items-center justify-between gap-2.5 text-start cursor-pointer group">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <span
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
                            :class="activeTab === 'intelligence' ? 'bg-amber-500 text-white shadow-xs shadow-amber-500/20' : 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 border border-gray-200/60 dark:border-gray-700/60'">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black tracking-tight truncate">هوش تجاری و رفتار بازار
                            </div>
                            <div
                                class="text-xs text-gray-400 dark:text-gray-500 font-medium truncate hidden md:block">
                                سقف منابع و کوهورت
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg text-xs font-black shrink-0"
                          :class="activeTab === 'intelligence' ? 'bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40' : 'bg-gray-200/70 dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                        هوشمند
                    </span>
                </button>
            </div>
            <div role="tabpanel" id="tabpanel-fleet" aria-labelledby="tab-fleet" x-show="activeTab === 'fleet'"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="space-y-6 sm:space-y-8">

                <div id="tenants-table-section">
                    @include('resellerhub::user.dashboard.partials.tenants-table')
                </div>
            </div>

            <div role="tabpanel" id="tabpanel-operations" aria-labelledby="tab-operations" x-show="activeTab === 'operations'"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-cloak
                 class="space-y-6 sm:space-y-8">

                @include('resellerhub::user.dashboard.partials.action-lists')
                @include('resellerhub::user.dashboard.partials.shortcuts')
            </div>

            <div role="tabpanel" id="tabpanel-analytics" aria-labelledby="tab-analytics" x-show="activeTab === 'analytics'"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-cloak
                 class="space-y-6 sm:space-y-8">

                @include('resellerhub::user.dashboard.partials.charts')
            </div>

            <div role="tabpanel" id="tabpanel-intelligence" aria-labelledby="tab-intelligence" x-show="activeTab === 'intelligence'"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-cloak
                 class="space-y-6 sm:space-y-8">

                @include('resellerhub::user.dashboard.partials.intelligence')
            </div>

            {{-- Reusable Real-time Suspend/Unsuspend Confirmation Modal --}}
            @include('resellerhub::partials.suspend-modal')

            {{-- Reusable Real-time License Renewal Modal --}}
            @include('resellerhub::partials.renew-modal')
        </div>
@endsection
