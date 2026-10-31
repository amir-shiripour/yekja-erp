@php
    use Modules\ResellerHub\App\Enums\QuotaKey;$quotas = $quotasSummary['quotas'] ?? [];
    $features = $quotasSummary['features'] ?? [];
    $allHealthy = $quotasSummary['all_healthy'] ?? true;

    $activeModuleSlugs = $tenant->modules->where('is_active', true)->pluck('module_slug')->toArray();

    $unlimitedCount = collect($quotas)->where('is_unlimited', true)->count();
    $limitedCount = collect($quotas)->where('is_unlimited', false)->count();
    $exceededCount = collect($quotas)->where('is_exceeded', true)->count();
    $warningCount = collect($quotas)->filter(fn($q) => !$q['is_unlimited'] && ($q['percent'] ?? 0) >= 75 && !$q['is_exceeded'])->count();
    $activeFeaturesCount = collect($features)->where('is_enabled', true)->count();
    $totalFeaturesCount = count($features);
@endphp

{{-- Resource Quotas & Live Usages Card --}}
<div
    class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-6 backdrop-blur-xl"
    x-data="{ quotaFilter: 'all' }">

    {{-- Card Header --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
            <span
                class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 shrink-0 border border-indigo-100/80 dark:border-indigo-800/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">سقف‌های مجاز و میزان مصرف لحظه‌ای
                    منابع</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    پایش زنده مصرف پرونده‌ها، نوبت‌ها، فاکتورها و سقف‌های تخصیص‌یافته به این CRM
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if($allHealthy && $exceededCount === 0)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-bold border border-emerald-200/60 dark:border-emerald-800/40">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    سقف‌ها در وضعیت عادی
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 rounded-full text-xs font-bold border border-rose-200/60 dark:border-rose-800/40">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    نیاز به ارتقای ظرفیت
                </span>
            @endif

            <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-xl transition border border-indigo-100/80 dark:border-indigo-800/40">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>ویرایش و تغییر سقف‌ها</span>
            </a>
        </div>
    </div>

    {{-- Top KPI Summary Stat Bar --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- 1. System Health --}}
        <div
            class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
            <span
                class="w-10 h-10 rounded-xl {{ $allHealthy ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }} flex items-center justify-center shrink-0 border {{ $allHealthy ? 'border-emerald-100 dark:border-emerald-800/40' : 'border-rose-100 dark:border-rose-800/40' }}">
                @if($allHealthy)
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @endif
            </span>
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block">سلامت منابع سامانه</span>
                <span
                    class="text-xs font-black {{ $allHealthy ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} block truncate">
                    {{ $allHealthy ? 'کاملاً نرمال و پایدار' : ($exceededCount > 0 ? reseller_fa_num($exceededCount) . ' سقف تکمیل‌شده' : 'هشدار نزدیک به سقف') }}
                </span>
            </div>
        </div>

        {{-- 2. Unlimited Resources --}}
        <div
            class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
            <span
                class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block">منابع با سقف نامحدود</span>
                <span class="text-xs font-black text-gray-900 dark:text-white tabular-nums">
                    {{ reseller_fa_num($unlimitedCount) }} <span class="text-xs font-bold text-gray-400">از {{ reseller_fa_num(count($quotas)) }} منبع</span>
                </span>
            </div>
        </div>

        {{-- 3. Constrained Limits --}}
        <div
            class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
            <span
                class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-800/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </span>
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block">سقف‌های عددی فعال</span>
                <span class="text-xs font-black text-gray-900 dark:text-white tabular-nums">
                    {{ reseller_fa_num($limitedCount) }} <span
                        class="text-xs font-bold text-gray-400">سقف محدود</span>
                </span>
            </div>
        </div>

        {{-- 4. Active Features --}}
        <div
            class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
            <span
                class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 border border-purple-100 dark:border-purple-800/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block">قابلیت‌های ویژه فعال</span>
                <span class="text-xs font-black text-gray-900 dark:text-white tabular-nums">
                    {{ reseller_fa_num($activeFeaturesCount) }} <span class="text-xs font-bold text-gray-400">از {{ reseller_fa_num($totalFeaturesCount) }} قابلیت</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Interactive Filter Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold scrollbar-none">
        <button type="button"
                @click="quotaFilter = 'all'"
                :class="quotaFilter === 'all'
                    ? 'bg-indigo-600 text-white shadow-xs'
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="px-3.5 py-1.5 rounded-xl transition cursor-pointer shrink-0">
            همه منابع ({{ reseller_fa_num(count($quotas)) }})
        </button>

        <button type="button"
                @click="quotaFilter = 'active_modules'"
                :class="quotaFilter === 'active_modules'
                    ? 'bg-indigo-600 text-white shadow-xs'
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="px-3.5 py-1.5 rounded-xl transition cursor-pointer shrink-0">
            ماژول‌های فعال این سامانه
        </button>

        <button type="button"
                @click="quotaFilter = 'limited'"
                :class="quotaFilter === 'limited'
                    ? 'bg-indigo-600 text-white shadow-xs'
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="px-3.5 py-1.5 rounded-xl transition cursor-pointer shrink-0">
            دارای سقف عددی ({{ reseller_fa_num($limitedCount) }})
        </button>

        <button type="button"
                @click="quotaFilter = 'unlimited'"
                :class="quotaFilter === 'unlimited'
                    ? 'bg-indigo-600 text-white shadow-xs'
                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="px-3.5 py-1.5 rounded-xl transition cursor-pointer shrink-0">
            نامحدودها ({{ reseller_fa_num($unlimitedCount) }})
        </button>

        @if($warningCount > 0 || $exceededCount > 0)
            <button type="button"
                    @click="quotaFilter = 'warnings'"
                    :class="quotaFilter === 'warnings'
                        ? 'bg-rose-600 text-white shadow-xs'
                        : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/60 dark:border-rose-800/40'"
                    class="px-3.5 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>نزدیک به تکمیل / پر شده ({{ reseller_fa_num($warningCount + $exceededCount) }})</span>
            </button>
        @endif
    </div>

    {{-- Quotas Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($quotas as $key => $q)
            @php
                $isModuleActive = in_array($q['module'], $activeModuleSlugs, true);
                $isUnlimited = (bool)($q['is_unlimited'] ?? false);
                $isExceeded = (bool)($q['is_exceeded'] ?? false);
                $used = (int)($q['used'] ?? 0);
                $limit = $q['limit'];
                $percent = (int)($q['percent'] ?? 0);
                $remaining = $q['remaining'];
                $unit = $q['unit'] ?? 'مورد';

                $qEnum = QuotaKey::tryFrom($key);
                $quotaStyling = $qEnum?->styling() ?? [
                    'bg' => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                    'accent' => 'gray',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>',
                ];
            @endphp

            <div x-show="(quotaFilter === 'all') ||
                         (quotaFilter === 'active_modules' && {{ $isModuleActive ? 'true' : 'false' }}) ||
                         (quotaFilter === 'limited' && !{{ $isUnlimited ? 'true' : 'false' }}) ||
                         (quotaFilter === 'unlimited' && {{ $isUnlimited ? 'true' : 'false' }}) ||
                         (quotaFilter === 'warnings' && ({{ $percent >= 75 ? 'true' : 'false' }} || {{ $isExceeded ? 'true' : 'false' }}))"
                 x-cloak
                 class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between space-y-4 hover:border-gray-200 dark:hover:border-gray-600 transition-colors {{ !$isModuleActive ? 'opacity-85' : '' }}">

                {{-- Card Top Row: Icon + Module Tag + Status Pill --}}
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-10 h-10 rounded-xl {{ $quotaStyling['bg'] }} flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $quotaStyling['icon'] !!}
                                </svg>
                            </span>
                            <div>
                                <span class="text-xs font-bold text-gray-900 dark:text-white block line-clamp-1"
                                      title="{{ $q['title'] }}">
                                    {{ $q['title'] }}
                                </span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-xs font-bold bg-gray-200/70 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                        {{ $q['module_title'] }}
                                    </span>
                                    @if(!$isModuleActive)
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-bold"
                                              title="ماژول مربوطه برای این سامانه غیرفعال است">
                                            (ماژول غیرفعال)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Status Pill --}}
                        <div class="shrink-0">
                            @if($isUnlimited)
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    نامحدود
                                </span>
                            @elseif($isExceeded || $percent >= 100)
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/40 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    تکمیل ظرفیت
                                </span>
                            @elseif($percent >= 75)
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ reseller_fa_num($percent) }}٪ مصرف
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700/60">
                                    {{ reseller_fa_num($percent) }}٪
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Usage / Limit Metric Numbers --}}
                    <div class="pt-1">
                        <div class="flex items-baseline justify-between">
                            <span class="text-2xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                                {{ reseller_fa_num($used) }}
                            </span>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                سقف:
                                @if($isUnlimited)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">نامحدود</span>
                                @else
                                    <strong
                                        class="text-gray-800 dark:text-gray-200">{{ reseller_fa_num($limit) }}</strong> {{ $unit }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Solid Progress Bar --}}
                    <div class="w-full bg-gray-200/80 dark:bg-gray-700/80 rounded-full h-2.5 overflow-hidden">
                        @if($isUnlimited)
                            <div class="h-full rounded-full bg-emerald-500 w-full"></div>
                        @else
                            <div
                                class="h-full rounded-full transition-all duration-500 {{ $isExceeded || $percent >= 90 ? 'bg-rose-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-indigo-600') }}"
                                style="width: {{ min(100, $percent) }}%;"></div>
                        @endif
                    </div>
                </div>

                {{-- Footer Info: Remaining Count or Unlimited Tag --}}
                <div
                    class="pt-2 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                    @if($isUnlimited)
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                            ظرفیت نامحدود در پلن
                        </span>
                    @elseif($isExceeded)
                        <span class="text-rose-600 dark:text-rose-400 font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            سقف مجاز تکمیل شده است
                        </span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">
                            باقی‌مانده: <strong
                                class="text-gray-800 dark:text-gray-200 font-black">{{ reseller_fa_num($remaining) }}</strong> {{ $unit }}
                        </span>
                    @endif

                    <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
                       class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-xs">
                        تغییر سقف
                    </a>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-sm text-gray-400 col-span-full">
                هیچ اطلاعات سقفی برای این سامانه ثبت نشده است.
            </div>
        @endforelse
    </div>
</div>
