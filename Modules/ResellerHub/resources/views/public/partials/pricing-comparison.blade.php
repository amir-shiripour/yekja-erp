@php
    use Illuminate\Database\Eloquent\Collection;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerPackage;

    /**
     * @var Collection<int,ResellerPackage> $packages
     * @var array $comparisonData   ['groups' => [...], 'periodRows' => [...]]
     * @var array $availablePeriods
     * @var array $packagePeriods
     */

    $groups         = $comparisonData['groups']     ?? [];
    $periodRows     = $comparisonData['periodRows'] ?? [];
    $packagePeriods = $packagePeriods ?? [];

    // Determine the popular (recommended) package
    $nonFree      = $packages->filter(fn($p) => !$p->isFree());
    $popularPkgId = $nonFree->isNotEmpty()
        ? ($nonFree->sortByDesc('monthly_price')->first()?->id)
        : null;

    $currencyLabel = __('resellerhub::messages.comparison.currency');

    // Total features count across all groups
    $totalFeaturesCount = collect($groups)->sum(fn($g) => count($g['rows'] ?? []));
@endphp

<section class="w-full mt-24 scroll-mt-28" id="plan-comparison-section" aria-labelledby="comparison-heading"
         x-data="{
             searchQuery: '',
             onlyDifferences: false,
             hoveredPkgId: null,
             openGroups: {
                 modules: true,
                 quotas: true,
                 features: true
             },
             toggleGroup(cat) {
                 this.openGroups[cat] = !this.openGroups[cat];
             },
             rowMatches(label, hasDiff) {
                 if (this.onlyDifferences && !hasDiff) return false;
                 if (!this.searchQuery.trim()) return true;
                 return label.toLowerCase().includes(this.searchQuery.trim().toLowerCase());
             }
         }">

    <div class="text-center mb-8">
        <h2 id="comparison-heading"
            class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">
            {{ __('resellerhub::messages.comparison.section_title') }}
        </h2>
        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
            {{ __('resellerhub::messages.comparison.section_subtitle') }}
        </p>
    </div>

    <div
        class="mb-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm">

        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   x-model="searchQuery"
                   placeholder="{{ __('resellerhub::messages.comparison.search_placeholder') }}"
                   class="w-full pr-10 pl-9 py-2 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            <button type="button"
                    x-show="searchQuery.length > 0"
                    x-cloak
                    @click="searchQuery = ''"
                    class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Only Differences Toggle --}}
        <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button"
                    @click="onlyDifferences = !onlyDifferences"
                    :class="onlyDifferences
                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer select-none">
                <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                      :class="onlyDifferences ? 'border-white bg-white/20' : 'border-gray-400 bg-transparent'">
                    <svg x-show="onlyDifferences" class="w-2.5 h-2.5 text-white" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd"/>
                    </svg>
                </span>
                <span>{{ __('resellerhub::messages.comparison.only_differences') }}</span>
            </button>
        </div>

    </div>

    <div
        class="w-full overflow-x-auto rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm relative"
        style="-webkit-overflow-scrolling: touch;">
        <table class="w-full border-collapse text-sm text-right" role="table" dir="rtl">
            <thead>
            <tr class="border-b border-gray-200 dark:border-gray-800">

                <th scope="col"
                    class="sticky right-0 z-20 bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.06)] dark:shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.4)] py-6 px-5 text-right align-bottom min-w-[200px] w-[240px]">
                    <div class="text-xs font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">
                        {{ __('resellerhub::messages.comparison.feature') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('resellerhub::messages.comparison.items_count', ['count' => ResellerHelper::toPersianDigits($totalFeaturesCount)]) }}
                    </div>
                </th>

                {{-- Package Columns --}}
                @foreach($packages as $pkg)
                    @php
                        $isPopular = ($pkg->id === $popularPkgId);
                        $pkgStructuredPeriods = $packagePeriods[$pkg->id] ?? [];
                    @endphp
                    <th scope="col"
                        @mouseenter="hoveredPkgId = {{ $pkg->id }}"
                        @mouseleave="hoveredPkgId = null"
                        class="relative py-6 px-4 text-center align-top min-w-[190px] transition-colors duration-150
                                   {{ $isPopular
                                       ? 'bg-indigo-50/70 dark:bg-indigo-950/30 border-t-4 border-indigo-600 dark:border-indigo-500'
                                       : 'bg-white dark:bg-gray-900' }}"
                        :class="hoveredPkgId === {{ $pkg->id }} ? 'bg-indigo-50/90 dark:bg-indigo-950/50' : ''">

                        {{-- Popular Badge Bar --}}
                        <div class="h-6 flex items-center justify-center mb-1">
                            @if($isPopular)
                                <span
                                    class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-indigo-600 text-white text-[11px] font-black tracking-wide shadow-sm shadow-indigo-600/30">
                                        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                                             aria-hidden="true">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ __('resellerhub::messages.comparison.popular_badge') }}
                                    </span>
                            @endif
                        </div>

                        {{-- Package Icon --}}
                        <div class="mx-auto mb-2 w-11 h-11 rounded-2xl flex items-center justify-center shadow-xs"
                             style="{{ $pkg->light_bg_style }}"
                             x-html="window.renderIndustryIcon ? window.renderIndustryIcon('{{ $pkg->icon }}', 'w-5 h-5 text-current') : ''">
                        </div>

                        {{-- Package Name --}}
                        <div class="font-black text-gray-900 dark:text-white text-sm sm:text-base leading-tight">
                            {{ $pkg->name }}
                        </div>

                        {{-- Price per Active Period with smooth transition and monthly breakdown --}}
                        @if($pkg->isFree())
                            <div class="mt-2 text-emerald-600 dark:text-emerald-400 font-black text-lg">
                                {{ __('resellerhub::messages.comparison.free_badge') }}
                            </div>
                        @else
                            @foreach($periodRows as $pRow)
                                @php
                                    $pKey         = $pRow['key'];
                                    $months       = $pRow['months'] ?? 1;
                                    $periodInfo   = $pkgStructuredPeriods[$pKey] ?? null;
                                    $isActive     = $periodInfo !== null || $pkg->isPeriodActive($pKey);
                                    $rawPrice     = $periodInfo['raw_price'] ?? ($isActive ? $pkg->getPriceForMonths($months) : 0);
                                    $finPrice     = $periodInfo['final_price'] ?? ($isActive ? $pkg->getFinalPriceForMonths($months) : 0);
                                    $monthlyRate  = $periodInfo['monthly_rate'] ?? ($months > 0 ? (int)round($finPrice / $months) : $finPrice);
                                    $hasDiscount  = $periodInfo['has_discount'] ?? ($isActive && $pkg->hasActiveDiscountForMonths($months));
                                    $discountPct  = $periodInfo['discount_percent'] ?? 0;
                                @endphp
                                <div x-show="currentPeriod === '{{ $pKey }}'"
                                     x-cloak
                                     x-transition:enter="transition-opacity duration-200 ease-out"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     class="mt-2 min-h-[3.25rem] flex flex-col items-center justify-center gap-0.5">
                                    @if($isActive)
                                        <div class="flex items-baseline gap-1">
                                            @if($hasDiscount)
                                                <span
                                                    class="text-xs text-gray-400 dark:text-gray-500 line-through tabular-nums">
                                                        {{ ResellerHelper::toPersianDigits(number_format($rawPrice)) }}
                                                    </span>
                                            @endif
                                            <span
                                                class="font-black text-lg text-indigo-600 dark:text-indigo-400 tabular-nums">
                                                    {{ ResellerHelper::toPersianDigits(number_format($finPrice)) }}
                                                </span>
                                            <span
                                                class="text-xs font-bold text-gray-400 dark:text-gray-500">{{ $currencyLabel }}</span>
                                        </div>

                                        {{-- Monthly breakdown & Discount badge for multi-month periods --}}
                                        @if($months > 1)
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span
                                                        class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                                        {{ __('resellerhub::messages.comparison.monthly_equivalent', ['amount' => ResellerHelper::toPersianDigits(number_format($monthlyRate))]) }}
                                                    </span>
                                                @if($discountPct > 0)
                                                    <span
                                                        class="px-1.5 py-0.2 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 text-[10px] font-black border border-rose-200/60 dark:border-rose-800/40">
                                                            {{ __('resellerhub::messages.comparison.discount_off', ['percent' => ResellerHelper::toPersianDigits($discountPct)]) }}
                                                        </span>
                                                @endif
                                            </div>
                                        @endif

                                        @if($pkg->setup_fee > 0)
                                            <span
                                                class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-0.5">
                                                    + {{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }} {{ __('resellerhub::messages.comparison.setup_fee_label') }}
                                                </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                                {{ __('resellerhub::messages.comparison.not_in_period') }}
                                            </span>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        {{-- CTA Button in Header --}}
                        @foreach($periodRows as $pRow)
                            @php
                                $pKey     = $pRow['key'];
                                $isActive = isset($pkgStructuredPeriods[$pKey]) || $pkg->isPeriodActive($pKey);
                            @endphp
                            <div x-show="currentPeriod === '{{ $pKey }}'" x-cloak class="mt-3">
                                @if($pkg->isFree() || $isActive)
                                    <a href="{{ route('portal.get-started', ['package' => $pkg->id, 'period' => $pKey]) }}"
                                       class="inline-flex items-center justify-center gap-1 w-full px-3 py-2.5 rounded-xl text-xs font-black transition-all transform cursor-pointer
                                                  {{ $isPopular
                                                     ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30'
                                                     : 'bg-gray-100 dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-gray-700 dark:text-gray-300 hover:text-indigo-700 dark:hover:text-indigo-300' }}"
                                       :class="hoveredPkgId === {{ $pkg->id }} ? 'scale-[1.02] shadow-lg ring-2 ring-indigo-500/40' : ''">
                                        <span>{{ __('resellerhub::messages.comparison.get_started') }}</span>
                                    </a>
                                @else
                                    <button disabled
                                            class="inline-flex items-center justify-center gap-1 w-full px-3 py-2.5 rounded-xl text-xs font-black cursor-not-allowed bg-gray-50 dark:bg-gray-800/40 text-gray-400 dark:text-gray-500">
                                        <span>{{ __('resellerhub::messages.comparison.not_in_period') }}</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($groups as $group)
                @php $cat = $group['category']; @endphp
                <tr class="bg-gray-100/80 dark:bg-gray-800/80 border-y border-gray-200 dark:border-gray-700/80 cursor-pointer select-none"
                    @click="toggleGroup('{{ $cat }}')">
                    <td class="sticky right-0 z-10 bg-gray-100/90 dark:bg-gray-800/90 border-l border-gray-200 dark:border-gray-700 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.06)] dark:shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.4)] py-3 px-5 text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if($cat === 'modules')
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                @elseif($cat === 'quotas')
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                                <span>{{ $group['label'] }}</span>
                                <span
                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        {{ ResellerHelper::toPersianDigits(count($group['rows'])) }}
                                    </span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-200 shrink-0"
                                 :class="openGroups['{{ $cat }}'] ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </td>

                    <td colspan="{{ $packages->count() }}"
                        class="bg-gray-100/80 dark:bg-gray-800/80 py-3 px-4">
                    </td>
                </tr>

                @foreach($group['rows'] as $rowIndex => $row)
                    @php
                        $rowLabel   = $row['label'];
                        $hasDiff    = $row['has_difference'] ?? true;
                        $rowType    = $row['type'] ?? 'module';
                        $maxQuota   = $row['max_quota'] ?? 100;
                        $hasDesc    = !empty($row['description']);
                    @endphp
                    <tr class="group border-b border-gray-100 dark:border-gray-800/50 transition-colors duration-150
                                   {{ $rowIndex % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/20' }}"
                        x-show="openGroups['{{ $cat }}'] && rowMatches('{{ addslashes($rowLabel) }}', {{ $hasDiff ? 'true' : 'false' }})"
                        x-cloak>

                        {{-- Sticky first column: Label + Tooltip --}}
                        <td class="sticky right-0 z-10 py-3.5 px-5 text-right text-sm text-gray-700 dark:text-gray-300 font-medium border-l border-gray-100 dark:border-gray-800/50 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.06)] dark:shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.4)] transition-colors duration-150
                                       {{ $rowIndex % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/20' }}
                                       group-hover:bg-indigo-50/60 dark:group-hover:bg-indigo-950/30">

                            <div class="flex items-center justify-between gap-1.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="truncate">{{ $rowLabel }}</span>
                                    @if(!empty($row['unit']))
                                        <span class="text-xs text-gray-400 dark:text-gray-500 font-normal shrink-0">({{ $row['unit'] }})</span>
                                    @endif
                                </div>

                                {{-- Tooltip info button --}}
                                @if($hasDesc)
                                    <div class="relative group/tip inline-flex items-center shrink-0">
                                        <button type="button"
                                                class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-0.5 rounded-full transition-colors cursor-help"
                                                aria-label="اطلاعات بیشتر">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                        <div
                                            class="absolute bottom-full right-0 mb-2 hidden group-hover/tip:block z-30 w-60 p-2.5 rounded-xl bg-gray-900 text-white text-[11px] leading-relaxed shadow-xl border border-gray-700 pointer-events-none">
                                            {{ $row['description'] }}
                                            <div
                                                class="absolute top-full right-2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Value Cells per Package --}}
                        @foreach($packages as $pkg)
                            @php
                                $isPopular = ($pkg->id === $popularPkgId);
                                $val       = $row['values'][$pkg->id] ?? null;
                            @endphp
                            <td @mouseenter="hoveredPkgId = {{ $pkg->id }}"
                                @mouseleave="hoveredPkgId = null"
                                class="py-3.5 px-4 text-center align-middle transition-colors duration-150
                                           group-hover:bg-indigo-50/40 dark:group-hover:bg-indigo-950/20
                                           {{ $isPopular ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}"
                                :class="hoveredPkgId === {{ $pkg->id }} ? 'bg-indigo-50/70 dark:bg-indigo-950/40' : ''">

                                @if($rowType === 'module' || $rowType === 'feature')
                                    {{-- Boolean: included vs clean minimal dash --}}
                                    @if($val === true)
                                        <span title="{{ __('resellerhub::messages.comparison.included') }}"
                                              class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/60 mx-auto">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                     aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                    @else
                                        <span title="{{ __('resellerhub::messages.comparison.not_included') }}"
                                              class="text-gray-300 dark:text-gray-600 text-base font-light select-none">—</span>
                                    @endif

                                @elseif($rowType === 'quota')
                                    {{-- Numeric Quota --}}
                                    @if($val === null)
                                        <span title="{{ __('resellerhub::messages.comparison.not_included') }}"
                                              class="text-gray-300 dark:text-gray-600 text-base font-light select-none">—</span>
                                    @elseif($val === -1)
                                        {{-- Mathematical Real Infinity SVG --}}
                                        <div
                                            class="flex flex-col items-center justify-center gap-1 mx-auto max-w-[120px]">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 text-xs font-black shadow-xs">
                                                    <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2.5" stroke-linecap="round"
                                                         stroke-linejoin="round" aria-label="نامحدود">
                                                        <path
                                                            d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"/>
                                                    </svg>
                                                    <span>{{ __('resellerhub::messages.comparison.unlimited') }}</span>
                                                </span>
                                            {{-- 100% glowing progress bar --}}
                                            <div
                                                class="w-full h-[2.5px] bg-emerald-500 dark:bg-emerald-400 rounded-full shadow-[0_0_6px_rgba(16,185,129,0.5)]"></div>
                                        </div>
                                    @else
                                        {{-- Numeric Value with Visual Quota Bar --}}
                                        @php
                                            $quotaPct = $maxQuota > 0 ? min(100, max(12, round(($val / $maxQuota) * 100))) : 50;
                                        @endphp
                                        <div
                                            class="flex flex-col items-center justify-center gap-1 mx-auto max-w-[120px]">
                                            <div class="flex items-baseline gap-1">
                                                    <span
                                                        class="text-sm font-black text-gray-900 dark:text-white tabular-nums">
                                                        {{ ResellerHelper::toPersianDigits(number_format($val)) }}
                                                    </span>
                                                @if(!empty($row['unit']))
                                                    <span
                                                        class="text-[10px] text-gray-500 dark:text-gray-400 font-normal">
                                                            {{ $row['unit'] }}
                                                        </span>
                                                @endif
                                            </div>
                                            {{-- Proportional visual bar --}}
                                            <div
                                                class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-[2.5px] overflow-hidden">
                                                <div
                                                    class="bg-gradient-to-l from-indigo-500 to-indigo-600 h-full rounded-full transition-all duration-300"
                                                    style="width: {{ $quotaPct }}%;"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
            </tbody>
            <tfoot>
            <tr class="border-t-2 border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900/80">
                <td class="sticky right-0 z-10 bg-gray-50/90 dark:bg-gray-900/90 border-l border-gray-200 dark:border-gray-800 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.06)] dark:shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.4)] py-6 px-5 text-right">
                    <div class="font-black text-sm text-gray-900 dark:text-white leading-tight">
                        {{ __('resellerhub::messages.comparison.cta_footer_title') }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-[200px]">
                        {{ __('resellerhub::messages.comparison.cta_footer_desc') }}
                    </div>
                </td>

                @foreach($packages as $pkg)
                    @php
                        $isPopular = ($pkg->id === $popularPkgId);
                        $pkgStructuredPeriods = $packagePeriods[$pkg->id] ?? [];
                    @endphp
                    <td @mouseenter="hoveredPkgId = {{ $pkg->id }}"
                        @mouseleave="hoveredPkgId = null"
                        class="py-6 px-4 text-center align-middle transition-colors duration-150
                                   {{ $isPopular ? 'bg-indigo-50/70 dark:bg-indigo-950/30' : '' }}"
                        :class="hoveredPkgId === {{ $pkg->id }} ? 'bg-indigo-50/90 dark:bg-indigo-950/50' : ''">

                        <div class="font-black text-gray-900 dark:text-white text-xs mb-1">
                            {{ $pkg->name }}
                        </div>

                        @foreach($periodRows as $pRow)
                            @php
                                $pKey     = $pRow['key'];
                                $isActive = isset($pkgStructuredPeriods[$pKey]) || $pkg->isPeriodActive($pKey);
                            @endphp
                            <div x-show="currentPeriod === '{{ $pKey }}'" x-cloak class="mt-2">
                                @if($pkg->isFree() || $isActive)
                                    <a href="{{ route('portal.get-started', ['package' => $pkg->id, 'period' => $pKey]) }}"
                                       class="inline-flex items-center justify-center gap-1 w-full px-3.5 py-2.5 rounded-xl text-xs font-black transition-all transform cursor-pointer
                                                  {{ $isPopular
                                                     ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30'
                                                     : 'bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300' }}"
                                       :class="hoveredPkgId === {{ $pkg->id }} ? 'scale-[1.02] shadow-lg ring-2 ring-indigo-500/40' : ''">
                                        <span>{{ __('resellerhub::messages.comparison.get_started') }}</span>
                                    </a>
                                @else
                                    <button disabled
                                            class="inline-flex items-center justify-center gap-1 w-full px-3 py-2 rounded-xl text-xs font-black cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500">
                                        <span>{{ __('resellerhub::messages.comparison.not_in_period') }}</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </td>
                @endforeach
            </tr>
            </tfoot>
        </table>
    </div>

</section>
