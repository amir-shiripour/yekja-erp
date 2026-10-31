@php
    $otherCount = max(0, $totalTenants - $activeTenants - $suspendedTenants);
@endphp

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">

    <div
        class="lg:col-span-2 bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between spotlight-card">
        <div
            class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100/80 dark:border-indigo-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </span>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        تحلیل روند رشد مشترکین و گردش درآمدی
                    </h2>
                </div>
                <p class="text-xs text-gray-400 font-medium ms-10">
                    نمودار عملکرد بر مبنای تقویم شمسی و فاکتورهای وصول‌شده
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3.5">
                <div
                    class="inline-flex items-center p-1 bg-gray-100/90 dark:bg-gray-900/60 rounded-xl border border-gray-200/60 dark:border-gray-800/80 text-xs font-bold"
                    role="group" aria-label="انتخاب بازه زمانی">
                    @foreach([
                        '30d' => '۳۰ روز',
                        '3m' => '۳ ماه',
                        '6m' => '۶ ماه',
                        '12m' => '۱ سال',
                        'ytd' => 'امسال'
                    ] as $pKey => $pLabel)
                        <button type="button"
                                @click="switchPeriod('{{ $pKey }}')"
                                :class="selectedPeriod === '{{ $pKey }}' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-2xs font-black' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                :disabled="chartLoading"
                                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                            {{ $pLabel }}
                        </button>
                    @endforeach
                </div>
                <div class="hidden sm:flex items-center gap-3 text-xs font-bold">
                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <span class="w-2.5 h-2.5 rounded-sm bg-indigo-500"></span>
                        سامانه جدید
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                        درآمد وصولی
                    </span>
                </div>
            </div>
        </div>
        <div class="p-5 sm:p-6 flex-1 flex flex-col justify-center relative">
            <div x-show="chartLoading"
                 x-transition.opacity
                 style="display: none;"
                 class="absolute inset-0 z-10 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xs flex items-center justify-center rounded-2xl">
                <div
                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 shadow-md border border-gray-100 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4 animate-spin text-indigo-600 dark:text-indigo-400" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>در حال دریافت داده‌ها...</span>
                </div>
            </div>

            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="resellerGrowthChart" aria-label="نمودار روند رشد مشترکین و گردش درآمدی" role="img"></canvas>
            </div>
        </div>
        <div
            class="px-5 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 grid grid-cols-2 gap-4">
            <div class="flex items-center gap-3">
                <span
                    class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                <div>
                    <div class="text-sm font-black text-gray-900 dark:text-white tabular-nums">
                        <span x-text="faNum(periodTenantsCreated)">{{ reseller_fa_num($periodTenantsCreated) }}</span>
                        سامانه
                    </div>
                    <div class="text-xs font-bold text-gray-400">توسعه در <span
                            x-text="selectedPeriodLabel">{{ $selectedPeriodLabel }}</span></div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span
                    class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <div class="text-sm font-black text-gray-900 dark:text-white tabular-nums"
                         x-text="periodRevenueFormatted">
                        {{ reseller_price($periodRevenue) }}
                    </div>
                    <div class="text-xs font-bold text-gray-400">گردش دریافتی <span
                            x-text="selectedPeriodLabel">{{ $selectedPeriodLabel }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between spotlight-card">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800/80">
            <div class="flex items-center gap-2.5">
                <span
                    class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100/80 dark:border-purple-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        کانال‌های ثبت و مبدا سفارشات
                    </h2>
                </div>
            </div>
            <p class="text-xs text-gray-400 font-medium mt-1 ms-10">
                سهم خرید آنلاین از پرتال در برابر ثبت مستقیم نماینده
            </p>
        </div>
        <div class="p-5 sm:p-6 flex-1 flex flex-col items-center justify-center">
            @if($totalOrdersCount > 0)
                <div class="relative h-48 sm:h-52 w-full flex items-center justify-center">
                    <canvas id="resellerStatusChart" aria-label="نمودار مبدا سفارشات" role="img"></canvas>
                    <div
                        class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-2xl font-black text-gray-900 dark:text-white tabular-nums">
                            {{ reseller_fa_num($totalOrdersCount) }}
                        </span>
                        <span class="text-xs font-bold text-gray-400">مجموع سفارشات</span>
                    </div>
                </div>
                <div class="mt-6 w-full space-y-2.5">
                    <div
                        class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100/80 dark:border-gray-800/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">خرید آنلاین از سایت</span>
                        </div>
                        <div class="text-end">
                            <span class="text-gray-900 dark:text-white tabular-nums font-black">
                                {{ reseller_fa_num($onlineOrdersCount) }} سفارش
                            </span>
                            <span class="text-purple-600 dark:text-purple-400 font-normal text-xs ms-1">({{ reseller_fa_num(number_format($onlineOrdersRatio, 1)) }}٪)</span>
                            <div class="text-xs text-gray-400 font-medium tabular-nums mt-0.5">
                                {{ reseller_price($onlineOrdersRevenue) }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100/80 dark:border-gray-800/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-gray-700 dark:text-gray-300">ثبت دستی نماینده</span>
                        </div>
                        <div class="text-end">
                            <span class="text-gray-900 dark:text-white tabular-nums font-black">
                                {{ reseller_fa_num($manualOrdersCount) }} سفارش
                            </span>
                            <span class="text-blue-600 dark:text-blue-400 font-normal text-xs ms-1">({{ reseller_fa_num(number_format($manualOrdersRatio, 1)) }}٪)</span>
                            <div class="text-xs text-gray-400 font-medium tabular-nums mt-0.5">
                                {{ reseller_price($manualOrdersRevenue) }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-8 text-center space-y-2">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">سفارشی جهت تحلیل ثبت نشده است</p>
                </div>
            @endif
        </div>
        <div
            class="px-5 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs font-bold">
            <span class="text-gray-500 dark:text-gray-400">
                ارزش کل: <strong
                    class="text-gray-900 dark:text-white tabular-nums font-black">{{ reseller_price($onlineOrdersRevenue + $manualOrdersRevenue) }}</strong>
            </span>
            <a href="{{ route('reseller.orders.index') }}"
               class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                <span>مدیریت سفارشات</span>
                <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

</section>

<section class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch mt-6">
    <div
        class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between spotlight-card">
        <div
            class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-8 h-8 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-100/80 dark:border-cyan-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        پیش‌بینی سررسید لایسنس‌ها و جریان نقدینگی
                    </h2>
                </div>
                <p class="text-xs text-gray-400 font-medium ms-10">
                    برآورد سامانه‌های در آستانه انقضا و گردش حاصل از تمدید در ۳ ماه آتی
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-cyan-50/70 dark:bg-cyan-950/40 border border-cyan-100/80 dark:border-cyan-900/40 text-xs font-black text-cyan-700 dark:text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    <span>مجموع ۳ ماهه:</span>
                    <span class="tabular-nums">{{ reseller_price($totalPipelineRevenue) }}</span>
                </span>
            </div>
        </div>
        <div class="p-6 flex-1 flex flex-col justify-center">
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="resellerPipelineChart" aria-label="نمودار پیش‌بینی سررسید لایسنس‌ها و جریان نقدینگی"
                        role="img"></canvas>
            </div>
        </div>
        <div class="p-5 sm:p-6 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/60 dark:bg-gray-900/40">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                @php
                    $pct30 = $totalPipelineRevenue > 0 ? round(($pipelineData['30d']['revenue'] / $totalPipelineRevenue) * 100) : 0;
                    $pct60 = $totalPipelineRevenue > 0 ? round(($pipelineData['60d']['revenue'] / $totalPipelineRevenue) * 100) : 0;
                    $pct90 = $totalPipelineRevenue > 0 ? round(($pipelineData['90d']['revenue'] / $totalPipelineRevenue) * 100) : 0;
                @endphp
                <div
                    class="p-3.5 rounded-2xl bg-white dark:bg-gray-800/80 border border-rose-100/80 dark:border-rose-900/30 shadow-2xs flex flex-col justify-between gap-2.5 pipeline-card-rose">
                    <div class="flex items-center justify-between gap-1">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black text-gray-800 dark:text-gray-200">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>۳۰ روز آینده</span>
                        </span>
                        <span
                            class="px-2 py-0.5 rounded-lg text-xs font-black bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-900/40 tabular-nums">
                            {{ reseller_fa_num($pipelineData['30d']['count']) }} سامانه
                        </span>
                    </div>
                    <div>
                        <div
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                            {{ reseller_price($pipelineData['30d']['revenue']) }}
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-400 font-bold mt-1">
                            <span>سهم از تمدید</span>
                            <span class="tabular-nums text-rose-600 dark:text-rose-400 font-black">{{ reseller_fa_num($pct30) }}٪</span>
                        </div>
                        <div class="w-full h-1 bg-gray-100 dark:bg-gray-700/40 rounded-full overflow-hidden mt-1">
                            <div class="h-full bg-rose-500 rounded-full transition-all duration-500"
                                 style="width: {{ $pct30 }}%;"></div>
                        </div>
                    </div>
                </div>
                <div
                    class="p-3.5 rounded-2xl bg-white dark:bg-gray-800/80 border border-indigo-100/80 dark:border-indigo-900/30 shadow-2xs flex flex-col justify-between gap-2.5 pipeline-card-indigo">
                    <div class="flex items-center justify-between gap-1">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black text-gray-800 dark:text-gray-200">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>۳۱ تا ۶۰ روز</span>
                        </span>
                        <span
                            class="px-2 py-0.5 rounded-lg text-xs font-black bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-900/40 tabular-nums">
                            {{ reseller_fa_num($pipelineData['60d']['count']) }} سامانه
                        </span>
                    </div>
                    <div>
                        <div
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                            {{ reseller_price($pipelineData['60d']['revenue']) }}
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-400 font-bold mt-1">
                            <span>سهم از تمدید</span>
                            <span class="tabular-nums text-indigo-600 dark:text-indigo-400 font-black">{{ reseller_fa_num($pct60) }}٪</span>
                        </div>
                        <div class="w-full h-1 bg-gray-100 dark:bg-gray-700/40 rounded-full overflow-hidden mt-1">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                                 style="width: {{ $pct60 }}%;"></div>
                        </div>
                    </div>
                </div>
                <div
                    class="p-3.5 rounded-2xl bg-white dark:bg-gray-800/80 border border-emerald-100/80 dark:border-emerald-900/30 shadow-2xs flex flex-col justify-between gap-2.5 pipeline-card-emerald">
                    <div class="flex items-center justify-between gap-1">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black text-gray-800 dark:text-gray-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>۶۱ تا ۹۰ روز</span>
                        </span>
                        <span
                            class="px-2 py-0.5 rounded-lg text-xs font-black bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900/40 tabular-nums">
                            {{ reseller_fa_num($pipelineData['90d']['count']) }} سامانه
                        </span>
                    </div>
                    <div>
                        <div
                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                            {{ reseller_price($pipelineData['90d']['revenue']) }}
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-400 font-bold mt-1">
                            <span>سهم از تمدید</span>
                            <span class="tabular-nums text-emerald-600 dark:text-emerald-400 font-black">{{ reseller_fa_num($pct90) }}٪</span>
                        </div>
                        <div class="w-full h-1 bg-gray-100 dark:bg-gray-700/40 rounded-full overflow-hidden mt-1">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                 style="width: {{ $pct90 }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between spotlight-card">
        <div
            class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100/80 dark:border-emerald-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 3.05a2.5 2.5 0 100 5 2.5 2.5 0 000-5zm5 3a2.5 2.5 0 110 5 2.5 2.5 0 010-5zm-10.5 4a2.5 2.5 0 100 5 2.5 2.5 0 000-5zM12 12a2.5 2.5 0 100 5 2.5 2.5 0 000-5z"/>
                        </svg>
                    </span>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        ساختار و سلامت جریان مالی
                    </h2>
                </div>
                <p class="text-xs text-gray-400 font-medium ms-10">
                    ترکیب درآمد وصولی، مطالبات در انتظار و ظرفیت بالقوه تمدیدها
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-100/80 dark:border-emerald-900/40 text-xs font-black text-emerald-700 dark:text-cyan-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>نرخ وصول نقدی:</span>
                    <span class="tabular-nums">{{ reseller_fa_num(number_format($collectionRate, 1)) }}٪</span>
                </span>
            </div>
        </div>
        <div class="p-5 sm:p-6 flex-1 flex flex-col items-center justify-center">
            @if($totalFinancialVolume > 0)
                <div class="relative h-48 sm:h-52 w-full flex items-center justify-center">
                    <canvas id="resellerRevenueCompChart" aria-label="نمودار ساختار جریان مالی" role="img"></canvas>
                    <div
                        class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-4">
                        <span class="text-xs font-bold text-gray-400">گردش کل ۳ وجهی</span>
                        <span class="text-sm sm:text-base font-black text-gray-900 dark:text-white tabular-nums mt-0.5">
                            {{ reseller_price($totalFinancialVolume) }}
                        </span>
                    </div>
                </div>
                <div class="mt-6 w-full space-y-2.5">
                    @php
                        $collectedRatio = $totalFinancialVolume > 0 ? round(($revenueComposition['collected']['amount'] / $totalFinancialVolume) * 100, 1) : 0;
                        $receivablesRatio = $totalFinancialVolume > 0 ? round(($revenueComposition['receivables']['amount'] / $totalFinancialVolume) * 100, 1) : 0;
                        $forecastedRatio = $totalFinancialVolume > 0 ? round(($revenueComposition['forecasted']['amount'] / $totalFinancialVolume) * 100, 1) : 0;
                    @endphp
                    <div
                        class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100/80 dark:border-gray-800/60">
                        <span class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>{{ $revenueComposition['collected']['label'] }}</span>
                            <span class="text-xs text-gray-400 font-normal">({{ reseller_fa_num($revenueComposition['collected']['count']) }} تراکنش)</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-gray-900 dark:text-white tabular-nums font-black">{{ reseller_price($revenueComposition['collected']['amount']) }}</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs">({{ reseller_fa_num(number_format($collectedRatio, 1)) }}٪)</span>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100/80 dark:border-gray-800/60">
                        <span class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>{{ $revenueComposition['receivables']['label'] }}</span>
                            <span class="text-xs text-gray-400 font-normal">({{ reseller_fa_num($revenueComposition['receivables']['count']) }} فاکتور)</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-gray-900 dark:text-white tabular-nums font-black">{{ reseller_price($revenueComposition['receivables']['amount']) }}</span>
                            <span class="text-amber-600 dark:text-amber-400 font-bold text-xs">({{ reseller_fa_num(number_format($receivablesRatio, 1)) }}٪)</span>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100/80 dark:border-gray-800/60">
                        <span class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <span>{{ $revenueComposition['forecasted']['label'] }}</span>
                            <span class="text-xs text-gray-400 font-normal">({{ reseller_fa_num($revenueComposition['forecasted']['count']) }} لایسنس)</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-gray-900 dark:text-white tabular-nums font-black">{{ reseller_price($revenueComposition['forecasted']['amount']) }}</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-xs">({{ reseller_fa_num(number_format($forecastedRatio, 1)) }}٪)</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-8 text-center space-y-2">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">گردش مالی ثبت‌شده‌ای جهت ترسیم ساختار
                        وجود ندارد</p>
                </div>
            @endif
        </div>
        <div
            class="px-5 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs font-bold">
            <span class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                @if($unpaidInvoicesCount === 0)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>وضعیت وصولی پایدار: کلیه صورتحساب‌ها تسویه شده‌اند</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>اقدام تسویه: <span
                            class="tabular-nums font-black text-amber-600 dark:text-amber-400">{{ reseller_fa_num($unpaidInvoicesCount) }}</span> فاکتور در انتظار پیگیری</span>
                @endif
            </span>
            <a href="{{ route('reseller.invoices.index', ['status' => 'unpaid']) }}"
               class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                <span>مشاهده مالی</span>
                <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

</section>
