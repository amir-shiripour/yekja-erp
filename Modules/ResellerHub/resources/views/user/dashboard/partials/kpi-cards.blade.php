@php
    $suspendedRatio = $totalTenants > 0 ? round(($suspendedTenants / $totalTenants) * 100, 1) : 0;
    $expiredRatio = $totalTenants > 0 ? round(($expiredTenants / $totalTenants) * 100, 1) : 0;
@endphp

<section class="space-y-4">
    {{-- 4 Minimalist Executive Metric Cards with Subtle Ambient Glow & Alpine Reactivity --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Card 1: Active Fleet & Operational Health --}}
        <div
            class="spotlight-card relative overflow-hidden bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-5 sm:p-6 shadow-sm backdrop-blur-xl transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600 flex flex-col justify-between">
            {{-- Ambient Depth Glow --}}
            <div
                class="pointer-events-none absolute -top-12 -start-12 w-36 h-36 rounded-full bg-emerald-500/10 dark:bg-emerald-500/15 blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        سامانه‌های فعال CRM
                    </span>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60 tabular-nums"
                        x-text="faNum(activeRatio) + '٪ پایداری'">
                        {{ reseller_fa_num($activeRatio) }}٪ پایداری
                    </span>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight"
                          x-text="faNum(activeTenants)">
                        {{ reseller_fa_num($activeTenants) }}
                    </span>
                    <span class="text-xs font-medium text-gray-400">
                        از مجموع <span x-text="faNum(totalTenants)">{{ reseller_fa_num($totalTenants) }}</span> سامانه
                    </span>
                </div>
            </div>

            <div class="relative z-10 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2">
                {{-- Micro Distribution Bar --}}
                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700/60 overflow-hidden flex">
                    <div :style="'width: ' + activeRatio + '%'" style="width: {{ $activeRatio }}%"
                         class="h-full bg-emerald-500 transition-all duration-500" title="فعال"></div>
                    <div :style="'width: ' + suspendedRatio + '%'" style="width: {{ $suspendedRatio }}%"
                         class="h-full bg-amber-500 transition-all duration-500" title="معلق"></div>
                    <div :style="'width: ' + expiredRatio + '%'" style="width: {{ $expiredRatio }}%"
                         class="h-full bg-rose-500 transition-all duration-500" title="منقضی"></div>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-400 font-medium pt-0.5">
                    <span x-text="faNum(activeTenants) + ' فعال'">{{ reseller_fa_num($activeTenants) }} فعال</span>
                    <span
                        x-text="faNum(suspendedTenants) + ' معلق'">{{ reseller_fa_num($suspendedTenants) }} معلق</span>
                    <span x-text="faNum(expiredTenants) + ' منقضی'">{{ reseller_fa_num($expiredTenants) }} منقضی</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Period Realized Revenue & Growth --}}
        <div
            class="spotlight-card relative overflow-hidden bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-5 sm:p-6 shadow-sm backdrop-blur-xl transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600 flex flex-col justify-between">
            {{-- Ambient Depth Glow --}}
            <div
                class="pointer-events-none absolute -top-12 -start-12 w-36 h-36 rounded-full bg-indigo-500/10 dark:bg-indigo-500/15 blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                        درآمد وصولی (<span x-text="selectedPeriodLabel">{{ $selectedPeriodLabel }}</span>)
                    </span>

                    <template x-if="revenueGrowthPercent !== null && revenueGrowthPercent != 0">
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black tabular-nums"
                            :class="revenueGrowthPercent > 0 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-100 dark:border-rose-800/60'">
                            <svg x-show="revenueGrowthPercent > 0"
                                 class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <svg x-show="revenueGrowthPercent < 0" class="w-3 h-3 text-rose-600 dark:text-rose-400"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            <span
                                x-text="(revenueGrowthPercent > 0 ? '+' : '') + faNum(revenueGrowthPercent) + '٪'"></span>
                        </span>
                    </template>
                    <template x-if="revenueGrowthPercent === null || revenueGrowthPercent == 0">
                        <span class="text-xs font-bold text-gray-400 dark:text-gray-500">
                            {{ reseller_fa_num($paidInvoicesCount) }} فاکتور
                        </span>
                    </template>
                </div>

                <div class="mt-3 flex items-baseline gap-2 min-w-0">
                    <span
                        class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight truncate"
                        x-text="periodRevenueFormatted">
                        {{ reseller_price($periodRevenue) }}
                    </span>
                </div>
            </div>

            <div
                class="relative z-10 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs font-medium text-gray-400">
                <span class="truncate">
                    {{ reseller_fa_num($paidInvoicesCount) }} فاکتور وصول‌شده
                </span>
                <span class="truncate">
                    کل دوره‌ها: {{ reseller_price($totalRevenue) }}
                </span>
            </div>
        </div>

        {{-- Card 3: Receivables & Liquidity --}}
        <div
            class="spotlight-card relative overflow-hidden bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-5 sm:p-6 shadow-sm backdrop-blur-xl transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600 flex flex-col justify-between">
            {{-- Ambient Depth Glow --}}
            <div
                class="pointer-events-none absolute -top-12 -start-12 w-36 h-36 rounded-full {{ $overdueInvoicesCount > 0 ? 'bg-rose-500/10 dark:bg-rose-500/15' : 'bg-emerald-500/10 dark:bg-emerald-500/15' }} blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                        مطالبات در انتظار تسویه
                    </span>

                    @if($overdueInvoicesCount > 0)
                        <a href="{{ route('reseller.invoices.index', ['status' => 'overdue']) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                            {{ reseller_fa_num($overdueInvoicesCount) }} معوق
                        </a>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/50">
                            جریان نقدینگی پایدار
                        </span>
                    @endif
                </div>

                <div class="mt-3 flex items-baseline gap-2 min-w-0">
                    <span
                        class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight truncate">
                        {{ reseller_price($unpaidInvoicesTotal) }}
                    </span>
                </div>
            </div>

            <div
                class="relative z-10 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs font-medium text-gray-400">
                <span class="truncate">
                    {{ reseller_fa_num($unpaidInvoicesCount) }} فاکتور باز
                </span>
                <span class="truncate">
                    میانگین سامانه: {{ $arpu > 0 ? reseller_price($arpu) : '—' }}
                </span>
            </div>
        </div>

        {{-- Card 4: New Fleet Acquisition & Ecosystem --}}
        <div
            class="spotlight-card relative overflow-hidden bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 p-5 sm:p-6 shadow-sm backdrop-blur-xl transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600 flex flex-col justify-between">
            {{-- Ambient Depth Glow --}}
            <div
                class="pointer-events-none absolute -top-12 -start-12 w-36 h-36 rounded-full bg-violet-500/10 dark:bg-violet-500/15 blur-2xl"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                        سامانه جدید (<span x-text="selectedPeriodLabel">{{ $selectedPeriodLabel }}</span>)
                    </span>

                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60">
                        {{ reseller_fa_num($subAccountsCount) }} مشتری
                    </span>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight"
                          x-text="'+' + faNum(periodTenantsCreated)">
                        +{{ reseller_fa_num($periodTenantsCreated) }}
                    </span>
                    <span class="text-xs font-medium text-gray-400">CRM مستقر شده</span>
                </div>
            </div>

            <div
                class="relative z-10 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs font-medium text-gray-400">
                <span class="truncate">
                    {{ reseller_fa_num($activePackagesCount) }} پکیج فعال آماده فروش
                </span>
                <span class="truncate">
                    {{ reseller_fa_num($activeOrdersCount) }} سفارش جاری
                </span>
            </div>
        </div>
    </div>
</section>
