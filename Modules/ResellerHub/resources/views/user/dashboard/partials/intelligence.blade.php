<div class="space-y-6 sm:space-y-8" x-data="{ quotaTab: 'features' }">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div
            class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-5 shadow-xs backdrop-blur-xl relative overflow-hidden spotlight-card group">
            <div class="flex items-center justify-between">
                <span
                    class="text-xs font-bold text-gray-500 dark:text-gray-400">تراکم قابلیت‌های تجاری در سامانه‌ها</span>
                <span
                    class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100/80 dark:border-indigo-900/40 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                    {{ reseller_fa_num(number_format($avgFeaturesPerTenant, 1)) }}
                </span>
                <span class="text-xs font-bold text-gray-400">قابلیت / سامانه</span>
            </div>
            <p class="mt-2 text-xs font-semibold text-gray-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>مجموع {{ reseller_fa_num($totalFeaturesActiveCount) }} قابلیت فعال تخصصی</span>
            </p>
        </div>

        {{-- KPI 2: Leading Commercial Package --}}
        @php
            $topPkgColor = $topCommercialPackage ? ($topCommercialPackage['color'] ?? '#f59e0b') : '#f59e0b';
        @endphp
        <div
            class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-5 shadow-xs backdrop-blur-xl relative overflow-hidden spotlight-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">پلن تجاری برتر بازار</span>
                <span
                    class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform"
                    style="background-color: {{ $topPkgColor }}18; color: {{ $topPkgColor }}; border: 1px solid {{ $topPkgColor }}33;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-lg sm:text-xl font-black text-gray-900 dark:text-white truncate max-w-[190px]"
                      title="{{ $topCommercialPackage ? $topCommercialPackage['name'] : '—' }}">
                    {{ $topCommercialPackage ? $topCommercialPackage['name'] : '—' }}
                </span>
            </div>
            <p class="mt-2 text-xs font-semibold text-gray-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $topPkgColor }};"></span>
                <span>{{ reseller_fa_num(number_format($topCommercialPackage ? $topCommercialPackage['ratio'] : 0, 1)) }}٪ سهم تقاضای تجاری</span>
            </p>
        </div>

        {{-- KPI 3: Fleet Vitality Rate --}}
        <div
            class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-5 shadow-xs backdrop-blur-xl relative overflow-hidden spotlight-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">شاخص سلامت و پایداری</span>
                <span
                    class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100/80 dark:border-emerald-900/40 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                    {{ reseller_fa_num(number_format($activeRatio, 1)) }}٪
                </span>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">فعال و عملیاتی</span>
            </div>
            <p class="mt-2 text-xs font-semibold text-gray-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>{{ reseller_fa_num($activeTenants) }} از {{ reseller_fa_num($totalTenants) }} سامانه فعال</span>
            </p>
        </div>

        {{-- KPI 4: Fleet Maturity & Average Age --}}
        <div
            class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 p-5 shadow-xs backdrop-blur-xl relative overflow-hidden spotlight-card group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">میانگین سابقه مشترکین</span>
                <span
                    class="w-10 h-10 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-100/80 dark:border-cyan-900/40 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                    {{ reseller_fa_num($avgFleetAgeDays) }}
                </span>
                <span class="text-xs font-bold text-gray-400">روز استقرار</span>
            </div>
            <p class="mt-2 text-xs font-semibold text-gray-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                <span>حدود {{ reseller_fa_num(number_format($avgFleetAgeMonths, 1)) }} ماه میانگین عمر مفید</span>
            </p>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        <div
            class="lg:col-span-7 bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between spotlight-card">
            <div
                class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100/80 dark:border-indigo-900/40 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </span>
                        <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white"
                            x-text="quotaTab === 'features' ? 'تحلیل ضریب نفوذ قابلیت‌های تجاری' : 'تحلیل وضعیت سقف‌ها و سهمیه‌های مصرفی'">
                            تحلیل ضریب نفوذ قابلیت‌های تجاری
                        </h2>
                    </div>
                    <p class="text-xs text-gray-400 font-medium ms-10"
                       x-text="quotaTab === 'features' ? 'سنجش میزان بهره‌مندی مشترکین از ویژگی‌های تجاری و امکانات ارزش افزوده' : 'تفکیک سامانه‌ها بر اساس محدودیت سهمیه مصرفی در برابر لایسنس نامحدود'">
                        سنجش میزان بهره‌مندی مشترکین از ویژگی‌های تجاری و امکانات ارزش افزوده
                    </p>
                </div>
                <div
                    class="inline-flex p-1 rounded-xl bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200/50 dark:border-gray-800/80 text-xs font-bold">
                    <button type="button"
                            @click="quotaTab = 'features'; switchCapabilitiesView('features')"
                            :class="quotaTab === 'features' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-300 shadow-2xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        <span>قابلیت‌های تجاری</span>
                    </button>
                    <button type="button"
                            @click="quotaTab = 'limits'; switchCapabilitiesView('limits')"
                            :class="quotaTab === 'limits' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-300 shadow-2xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            class="px-3 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>سقف‌ها و سهمیه‌ها</span>
                    </button>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex-1 flex flex-col justify-center">
                <div class="relative h-64 sm:h-72 w-full">
                    <canvas id="resellerCapabilitiesChart" aria-label="نمودار تحلیل قابلیت‌ها و سقف‌ها"
                            role="img"></canvas>
                </div>
                <div x-show="quotaTab === 'features'" class="mt-6 w-full grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($featuresStats as $f)
                        <div
                            class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100/80 dark:border-gray-800/60">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                      style="background-color: {{ $f['color'] }};"></span>
                                <span class="text-gray-700 dark:text-gray-300 truncate max-w-[150px]"
                                      title="{{ $f['name'] }}">{{ $f['name'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900 dark:text-white tabular-nums font-black">{{ reseller_fa_num($f['count']) }} سامانه</span>
                                <span
                                    class="font-bold tabular-nums text-xs {{ $f['count'] > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' }}">({{ reseller_fa_num(number_format($f['ratio'], 1)) }}٪)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div x-show="quotaTab === 'limits'" x-cloak class="mt-6 w-full grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($limitsStats as $l)
                        <div
                            class="p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100/80 dark:border-gray-800/60 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                          style="background-color: {{ $l['color'] }};"></span>
                                    <span class="text-gray-700 dark:text-gray-300 truncate max-w-[150px]"
                                          title="{{ $l['name'] }}">{{ $l['name'] }}</span>
                                </div>
                                <span
                                    class="text-emerald-600 dark:text-emerald-400 tabular-nums font-black text-xs">
                                    {{ reseller_fa_num(number_format($l['limitedRatio'], 1)) }}٪ دارای سقف
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-400 font-medium pt-0.5">
                                <span>{{ reseller_fa_num($l['limitedCount']) }} سامانه محدود</span>
                                <span>{{ reseller_fa_num($l['unlimitedCount']) }} سامانه نامحدود</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div
                class="px-5 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs font-bold">
                <span class="text-gray-500 dark:text-gray-400">
                    شاخص تجاری:
                    <strong class="text-indigo-600 dark:text-indigo-400 font-black tabular-nums">
                        {{ reseller_fa_num(number_format($avgFeaturesPerTenant, 1)) }} قابلیت
                    </strong>
                    میانگین هر سامانه
                </span>
                <button type="button" @click="setTab('fleet')"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer">
                    <span>مشاهده لیست سامانه‌ها</span>
                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        <div
            class="lg:col-span-5 bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between spotlight-card">
            <div
                class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-800/80 flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100/80 dark:border-purple-900/40 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                        </span>
                        <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                            سهم بازار پکیج‌ها و پلن‌ها
                        </h2>
                    </div>
                    <p class="text-xs text-gray-400 font-medium ms-10">
                        ترکیب پلن‌های فعال در سامانه‌های مشترکین
                    </p>
                </div>
            </div>
            <div class="p-5 sm:p-6 flex-1 flex flex-col items-center justify-center">
                @if($packageDistribution->isNotEmpty())
                    <div class="relative h-48 sm:h-52 w-full flex items-center justify-center">
                        <canvas id="resellerPackagesChart" aria-label="نمودار سهم پکیج‌ها" role="img"></canvas>
                        <div
                            class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                            <span class="text-2xl font-black text-gray-900 dark:text-white tabular-nums">
                                {{ reseller_fa_num($totalTenants) }}
                            </span>
                            <span class="text-xs font-bold text-gray-400">کل لایسنس‌ها</span>
                        </div>
                    </div>
                    <div class="mt-6 w-full space-y-2">
                        @foreach($packageDistribution as $pkg)
                            <div
                                class="flex items-center justify-between text-xs font-bold p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100/80 dark:border-gray-800/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                          style="background-color: {{ $pkg['color'] }};"></span>
                                    <span class="text-gray-700 dark:text-gray-300 truncate max-w-[150px]"
                                          title="{{ $pkg['name'] }}">
                                        {{ $pkg['name'] }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-900 dark:text-white tabular-nums font-black">
                                        {{ reseller_fa_num($pkg['count']) }} سامانه
                                    </span>
                                    <span class="font-bold text-xs ms-1 tabular-nums"
                                          style="color: {{ $pkg['color'] }};">({{ reseller_fa_num(number_format($pkg['ratio'], 1)) }}٪)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center space-y-2">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400">پکیجی برای سامانه‌ها تعریف نشده
                            است</p>
                    </div>
                @endif
            </div>
            <div
                class="px-5 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs font-bold">
                <span class="text-gray-500 dark:text-gray-400">
                    تعداد پلن‌های تعریف‌شده: <strong
                        class="text-gray-900 dark:text-white tabular-nums font-black">{{ reseller_fa_num($packagesCount) }}</strong>
                </span>
                <a href="{{ route('reseller.packages.index') }}"
                   class="text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                    <span>مدیریت پلن‌ها</span>
                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
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
                        class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border border-teal-100/80 dark:border-teal-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </span>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        ماتریس ماندگاری و کوهورت طول عمر سامانه‌ها
                    </h2>
                </div>
                <p class="text-xs text-gray-400 font-medium ms-10">
                    دسته‌بندی مشترکین بر مبنای سابقه پیوستن به پلتفرم و بررسی نرخ وفاداری
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-teal-50/70 dark:bg-teal-950/40 border border-teal-100/80 dark:border-teal-900/40 text-xs font-black text-teal-700 dark:text-teal-300">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    <span>۵ رده سابقه مشترکین</span>
                </span>
            </div>
        </div>

        <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-7">
                <div class="relative h-60 sm:h-64 w-full">
                    <canvas id="resellerRetentionChart" aria-label="نمودار ماتریس ماندگاری" role="img"></canvas>
                </div>
            </div>
            <div class="lg:col-span-5 space-y-3">
                <div class="space-y-2">
                    @foreach($retentionCohorts as $key => $cohort)
                        <div
                            class="p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100/80 dark:border-gray-800/60 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full shrink-0"
                                      style="background-color: {{ $cohort['color'] }};"></span>
                                <div>
                                    <div
                                        class="text-xs font-black text-gray-900 dark:text-white">{{ $cohort['label'] }}</div>
                                    <div class="text-xs text-gray-400 font-medium">{{ $cohort['sublabel'] }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-xs font-black text-gray-900 dark:text-white tabular-nums">
                                    {{ reseller_fa_num($cohort['count']) }} سامانه
                                </span>
                                <div class="text-xs font-bold text-teal-600 dark:text-teal-400 tabular-nums">
                                    {{ reseller_fa_num(number_format($cohort['ratio'], 1)) }}٪
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div
                    class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/80 dark:border-indigo-900/40 space-y-1.5">
                    <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 font-black text-xs">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>تحلیل هوشمند نگه‌داشت (Retention Insight)</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        @if(($retentionCohorts['new']['ratio'] ?? 0) > 50)
                            شبکه مشترکین شما در فاز <strong class="text-indigo-600 dark:text-indigo-400 font-bold">رشد
                                شتابان و ورود کاربران جدید</strong> قرار دارد. اولویت کلیدی، توانمندسازی مشترکین نوپا در
                            ۳۰ روز نخست جهت افزایش ضریب تمدید است.
                        @else
                            توزیع سابقه مشترکین در وضعیت پایداری قرار دارد. تمرکز بر ارتقای ماژول‌ها و فروش مکمل
                            (Cross-sell) به مشترکین کهنه‌کار توصیه می‌شود.
                        @endif
                    </p>
                </div>
            </div>

        </div>
        <div
            class="px-5 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs font-bold">
            <span class="text-gray-500 dark:text-gray-400">
                شاخص ماندگاری ۳ ماهه نخست:
                <strong class="text-teal-600 dark:text-teal-400 tabular-nums font-black">
                    {{ reseller_fa_num(number_format((($retentionCohorts['new']['ratio'] ?? 0) + ($retentionCohorts['onboarding']['ratio'] ?? 0)), 1)) }}٪
                </strong>
            </span>
            <span class="text-gray-400 text-xs">به‌روزرسانی همگام با پایگاه داده</span>
        </div>
    </div>

</div>
