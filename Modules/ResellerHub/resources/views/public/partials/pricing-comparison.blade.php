@php
    use Illuminate\Database\Eloquent\Collection;
    use Modules\ResellerHub\App\Enums\QuotaKey;
    use Modules\ResellerHub\App\Enums\PackageIcon;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerPackage;

    /**
     * @var Collection<int,ResellerPackage> $packages
     * @var array $comparisonData
     * @var array $availablePeriods
     * @var array $packagePeriods
     */

    $groups         = $comparisonData['groups']     ?? [];
    $periodRows     = $comparisonData['periodRows'] ?? [];
    $packagePeriods = $packagePeriods ?? [];

    $currencyLabel = ResellerHelper::currencyLabel();

    // Total features count across all groups
    $totalFeaturesCount = collect($groups)->sum(static fn($g) => count($g['rows'] ?? []));

    // Pre-calculate metadata for Alpine search, filtering & empty states
    $groupsMeta = collect($groups)->mapWithKeys(static function($g) {
        $rows = collect($g['rows'])->map(static fn($r) => [
            'label'         => $r['label'],
            'has_diff'      => (bool)($r['has_difference'] ?? true),
        ]);
        return [$g['category'] => $rows];
    })->toArray();

    $categoryCounts = [
        'all'                 => $totalFeaturesCount,
        'modules'             => count($groupsMeta['modules'] ?? []),
        'quotas_and_features' => count($groupsMeta['quotas_and_features'] ?? []),
    ];

    $validFeatureSlugs = collect(QuotaKey::cases())
        ->filter(static fn(QuotaKey $q) => $q->isFeature())
        ->map(static fn(QuotaKey $q) => $q->value)
        ->all();

    $packageMeta = $packages->map(static function($p) use ($validFeatureSlugs) {
        $featuresCount = count(array_intersect(is_array($p->features) ? $p->features : [], $validFeatureSlugs));
        $modulesCount  = is_array($p->modules) ? count($p->modules) : 0;
        $limitsCount   = is_array($p->limits) ? count(array_filter($p->limits, static fn($v) => $v !== null && $v != 0)) : 0;
        return [
            'id'             => (int) $p->id,
            'name'           => (string) $p->name,
            'monthly_price'  => (int) ($p->monthly_price ?? 0),
            'sort_order'     => (int) ($p->sort_order ?? 0),
            'features_count' => ($featuresCount * 1000) + $modulesCount + $limitsCount,
            'has_discount'   => $p->hasActiveDiscount() || $p->hasActiveDiscountForMonths(1),
            'is_free'        => $p->isFree(),
        ];
    })->values()->toArray();
@endphp

<section class="w-full mt-28 sm:mt-36 pt-12 border-t border-gray-200/80 dark:border-gray-800 scroll-mt-20"
         aria-labelledby="comparison-heading"
         x-data="{
             searchQuery: '',
             onlyDifferences: false,
             selectedCategory: 'all',
             showQuickFilters: false,
             showTopScroll: false,
             isMobile: typeof window !== 'undefined' ? (window.innerWidth < 1024) : false,
             selectedPkgIndex: 0,
             scrollPercent: 0,
             get pkgProgressPercent() {
                 return this.pkgList.length > 0 ? Math.round(((this.selectedPkgIndex + 1) / this.pkgList.length) * 100) : 0;
             },
             isProgrammaticScroll: false,
             pkgList: @js($packages->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()->toArray()),
             tableScrollWidth: 0,
             tableClientWidth: 0,
             hasHorizontalOverflow: false,
             scrollToRatio(ratio) {
                 const clampedRatio = Math.min(1, Math.max(0, ratio));
                 this.scrollPercent = Math.round(clampedRatio * 100);

                 const el = this.$refs.tableContainer;
                 if (!el) return;
                 const maxScroll = el.scrollWidth - el.clientWidth;
                 if (maxScroll <= 0) return;

                 this.isProgrammaticScroll = true;
                 const targetOffset = clampedRatio * maxScroll;

                 // Detect if RTL scrollLeft is negative:
                 const prev = el.scrollLeft;
                 el.scrollLeft = -5;
                 const isNegative = el.scrollLeft < 0;
                 el.scrollLeft = prev;

                 const targetLeft = isNegative ? -targetOffset : targetOffset;

                 el.scrollTo({
                     left: targetLeft,
                     behavior: 'smooth'
                 });

                 setTimeout(() => {
                     this.isProgrammaticScroll = false;
                 }, 600);
             },
             selectPackage(index) {
                 if (index < 0 || index >= this.pkgList.length) return;
                 this.selectedPkgIndex = index;
                 const pkg = this.pkgList[index];
                 this.hoveredPkgId = pkg ? pkg.id : null;

                 const ratio = this.pkgList.length > 1 ? (index / (this.pkgList.length - 1)) : 1;
                 this.scrollToRatio(ratio);
             },
             nextPackage() {
                 if (this.selectedPkgIndex < this.pkgList.length - 1) {
                     this.selectPackage(this.selectedPkgIndex + 1);
                 }
             },
             prevPackage() {
                 if (this.selectedPkgIndex > 0) {
                     this.selectPackage(this.selectedPkgIndex - 1);
                 }
             },
             onTableScroll() {
                 const el = this.$refs.tableContainer;
                 if (!el) return;
                 const maxScroll = el.scrollWidth - el.clientWidth;
                 if (maxScroll > 0) {
                     const currentScroll = Math.abs(el.scrollLeft);
                     this.scrollPercent = Math.min(100, Math.max(0, Math.round((currentScroll / maxScroll) * 100)));
                     if (!this.isProgrammaticScroll && this.pkgList.length > 1) {
                         const nearestIndex = Math.min(
                             this.pkgList.length - 1,
                             Math.max(0, Math.round((currentScroll / maxScroll) * (this.pkgList.length - 1)))
                         );
                         this.selectedPkgIndex = nearestIndex;
                         this.hoveredPkgId = this.pkgList[nearestIndex]?.id;
                     }
                 }
             },
             updateScrollMetrics() {
                 if (!this.$refs.tableContainer) return;
                 const el = this.$refs.tableContainer;
                 this.tableScrollWidth = el.scrollWidth;
                 this.tableClientWidth = el.clientWidth;
                 this.hasHorizontalOverflow = this.tableScrollWidth > (this.tableClientWidth + 10);
             },
             syncFromTop() {
                 if (this.isSyncingBottom) return;
                 this.isSyncingTop = true;
                 if (this.$refs.tableContainer && this.$refs.topScroll) {
                     this.$refs.tableContainer.scrollLeft = this.$refs.topScroll.scrollLeft;
                 }
                 requestAnimationFrame(() => { this.isSyncingTop = false; });
             },
             syncFromBottom() {
                 if (this.isSyncingTop) return;
                 this.isSyncingBottom = true;
                 if (this.$refs.topScroll && this.$refs.tableContainer) {
                     this.$refs.topScroll.scrollLeft = this.$refs.tableContainer.scrollLeft;
                 }
                 requestAnimationFrame(() => { this.isSyncingBottom = false; });
             },
             hoveredPkgId: null,
             activeTooltip: null,
             packageSortMode: 'features_desc',
             rowSortMode: 'smart',
             packageMeta: @js($packageMeta),
             groupsMeta: @js($groupsMeta),
             openGroups: {
                 modules: true,
                 quotas_and_features: true
             },
             setPackageSort(mode) {
                 this.packageSortMode = mode;
                 this.applyPackageSort();
             },
             applyPackageSort() {
                 let sorted = [...this.packageMeta];
                 if (this.packageSortMode === 'price_asc') {
                     sorted.sort((a, b) => a.monthly_price - b.monthly_price || a.id - b.id);
                 } else if (this.packageSortMode === 'price_desc') {
                     sorted.sort((a, b) => b.monthly_price - a.monthly_price || b.id - a.id);
                 } else if (this.packageSortMode === 'features_desc') {
                     sorted.sort((a, b) => b.features_count - a.features_count || a.monthly_price - b.monthly_price);
                 } else if (this.packageSortMode === 'discount_first') {
                     sorted.sort((a, b) => (b.has_discount ? 1 : 0) - (a.has_discount ? 1 : 0) || a.monthly_price - b.monthly_price);
                 }

                 this.pkgList = sorted.map(p => ({ id: p.id, name: p.name }));
                 this.selectedPkgIndex = 0;

                 const container = this.$refs.tableContainer;
                 if (!container) return;
                 const table = container.querySelector('table');
                 if (!table) return;

                 const sortedIds = sorted.map(p => p.id);

                 // 1. Reorder thead th cells
                 const theadRow = table.querySelector('thead tr');
                 if (theadRow) {
                     sortedIds.forEach(id => {
                         const el = theadRow.querySelector('[data-pkg-id=\'' + id + '\']');
                         if (el) theadRow.appendChild(el);
                     });
                 }

                 // 2. Reorder each tbody row package cells
                 const tbodyRows = table.querySelectorAll('tbody tr');
                 tbodyRows.forEach(row => {
                     if (row.querySelector('td[colspan]')) return;
                     sortedIds.forEach(id => {
                         const el = row.querySelector('[data-pkg-id=\'' + id + '\']');
                         if (el) row.appendChild(el);
                     });
                 });

                 // 3. Reorder tfoot td cells
                 const tfootRow = table.querySelector('tfoot tr');
                 if (tfootRow) {
                     sortedIds.forEach(id => {
                         const el = tfootRow.querySelector('[data-pkg-id=\'' + id + '\']');
                         if (el) tfootRow.appendChild(el);
                     });
                 }

                 this.$nextTick(() => {
                     this.updateScrollMetrics();
                     this.onTableScroll();
                 });
             },
             setRowSort(mode) {
                 this.rowSortMode = mode;
                 this.applyRowSort();
             },
             applyRowSort() {
                 const container = this.$refs.tableContainer;
                 if (!container) return;
                 const tbody = container.querySelector('tbody');
                 if (!tbody) return;

                 ['modules', 'quotas_and_features'].forEach(cat => {
                     const rows = Array.from(tbody.querySelectorAll('tr[data-cat=\'' + cat + '\']'));
                     if (rows.length === 0) return;

                     if (this.rowSortMode === 'alpha') {
                         rows.sort((a, b) => {
                             const labelA = (a.dataset.label || '').trim();
                             const labelB = (b.dataset.label || '').trim();
                             return labelA.localeCompare(labelB, 'fa');
                         });
                     } else {
                         // Smart default: based on original adoption ordering
                         rows.sort((a, b) => {
                             const idxA = parseInt(a.dataset.originalIndex, 10) || 0;
                             const idxB = parseInt(b.dataset.originalIndex, 10) || 0;
                             return idxA - idxB;
                         });
                     }

                     const groupHeader = tbody.querySelector('tr[data-group-header=\'' + cat + '\']');
                     if (!groupHeader) return;

                     let lastNode = groupHeader;
                     rows.forEach(row => {
                         lastNode.after(row);
                         lastNode = row;
                     });
                 });
             },
             init() {
                 this.$nextTick(() => {
                     this.isMobile = window.innerWidth < 1024;
                     this.updateScrollMetrics();
                     window.addEventListener('resize', () => {
                         this.isMobile = window.innerWidth < 1024;
                         this.updateScrollMetrics();
                     });
                 });
                 this.$watch('searchQuery', (val) => {
                     if (val && val.trim().length > 0) {
                         this.selectedCategory = 'all';
                         for (const cat in this.groupsMeta) {
                             if (this.groupHasMatches(cat)) {
                                 this.openGroups[cat] = true;
                             }
                         }
                     }
                 });
             },
             normalizePersian(str) {
                 if (!str) return '';
                 return str.toString()
                     .replace(/ي/g, 'ی')
                     .replace(/ك/g, 'ک')
                     .replace(/\u200c/g, ' ')
                     .toLowerCase()
                     .trim();
             },
             groupHasMatches(cat) {
                 if (this.selectedCategory !== 'all' && this.selectedCategory !== cat) {
                     return false;
                 }
                 const rows = this.groupsMeta[cat] || [];
                 if (!this.searchQuery.trim() && !this.onlyDifferences) return true;
                 return rows.some(r => this.rowMatches(r.label, r.has_diff));
             },
             get totalMatchesCount() {
                 let count = 0;
                 for (const cat in this.groupsMeta) {
                     if (this.selectedCategory !== 'all' && this.selectedCategory !== cat) continue;
                     const rows = this.groupsMeta[cat] || [];
                     count += rows.filter(r => this.rowMatches(r.label, r.has_diff)).length;
                 }
                 return count;
             },
             toggleGroup(cat) {
                 this.openGroups[cat] = !this.openGroups[cat];
             },
             rowMatches(label, hasDiff) {
                 if (this.onlyDifferences && !hasDiff) return false;
                 const q = this.normalizePersian(this.searchQuery);
                 if (!q) return true;
                 const l = this.normalizePersian(label);
                 return l.includes(q);
             },
             subSectionHasMatches(cat, secTitle) {
                 if (!this.groupHasMatches(cat)) return false;
                 const rows = (this.groupsMeta[cat] || []).filter(r => r.section_title === secTitle);
                 if (!this.searchQuery.trim() && !this.onlyDifferences) return rows.length > 0;
                 return rows.some(r => this.rowMatches(r.label, r.has_diff));
             }
         }">

    {{-- Section Header --}}
    <div class="text-center mb-10">
        <h2 id="comparison-heading"
            class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white">
            مقایسه تفصیلی و مشخصات فنی تمام پکیج ها
        </h2>
        <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            بررسی و مقایسه دقیق ماژول‌ها، سقف‌ها و امکانات به تفکیک هر پکیج
        </p>
    </div>

    {{-- Modern Unified Toolbar with Search & Quick Filters --}}
    <div
        class="mb-6 p-2.5 sm:p-3 bg-white dark:bg-gray-900 border border-gray-200/90 dark:border-gray-800 rounded-2xl shadow-xs">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">

            {{-- Right: Full-width Search Input --}}
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="جستجوی ویژگی یا ماژول در تمام پکیج ها..."
                       class="w-full ps-10 pe-9 py-2.5 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                <button type="button"
                        x-show="searchQuery.length > 0"
                        x-cloak
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 end-0 pe-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                        aria-label="پاک کردن جستجو">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Left: Controls (Unified Clean Row for Mobile & Desktop) --}}
            <div class="flex items-center gap-1.5 sm:gap-2 w-full sm:w-auto shrink-0">
                {{-- Quick Filters Toggle Button --}}
                <button type="button"
                        @click="showQuickFilters = !showQuickFilters"
                        :aria-expanded="showQuickFilters ? 'true' : 'false'"
                        :class="showQuickFilters || selectedCategory !== 'all'
                            ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border-indigo-300 dark:border-indigo-700 ring-2 ring-indigo-500/20'
                            : 'bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border-gray-200 dark:border-gray-700/60'"
                        class="flex-1 sm:flex-initial h-9.5 sm:h-auto inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2 sm:px-3.5 py-2 sm:py-2.5 rounded-xl text-xs font-black border transition-all cursor-pointer select-none">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="hidden sm:inline whitespace-nowrap">فیلترهای سریع</span>
                    <span class="sm:hidden whitespace-nowrap">فیلترها</span>
                    <span x-show="selectedCategory !== 'all'" x-cloak
                          class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-200 shrink-0"
                         :class="showQuickFilters ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Top Scroll / Header Fast Navigation Toggle Button (Desktop Only) --}}
                <button type="button"
                        @click="showTopScroll = !showTopScroll; if(showTopScroll) { $nextTick(() => { updateScrollMetrics(); onTableScroll(); }); }"
                        :aria-expanded="showTopScroll ? 'true' : 'false'"
                        :class="showTopScroll
                            ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border-indigo-300 dark:border-indigo-700 ring-2 ring-indigo-500/20'
                            : 'bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border-gray-200 dark:border-gray-700/60'"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-black border transition-all cursor-pointer select-none">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="whitespace-nowrap">اسکرول هدر</span>
                </button>

                {{-- Comparison Matrix Sort Dropdown --}}
                <div class="relative flex-1 sm:flex-initial" x-data="{ sortDropdownOpen: false }">
                    <button type="button"
                            @click="sortDropdownOpen = !sortDropdownOpen"
                            @click.outside="sortDropdownOpen = false"
                            :class="(packageSortMode !== 'price_asc' || rowSortMode !== 'smart')
                                ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border-indigo-300 dark:border-indigo-700 ring-2 ring-indigo-500/20'
                                : 'bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border-gray-200 dark:border-gray-700/60 border'"
                            class="w-full sm:w-auto h-9.5 sm:h-auto inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-2 sm:py-2.5 rounded-xl text-xs font-black transition-all cursor-pointer select-none"
                            aria-label="تنظیمات مرتب‌سازی مقایسه">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        <span class="whitespace-nowrap">مرتب‌سازی</span>
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="(packageSortMode !== 'price_asc' || rowSortMode !== 'smart') ? 'bg-indigo-600 dark:bg-indigo-400' : 'bg-gray-300 dark:bg-gray-600'"></span>
                    </button>

                    {{-- Dropdown Panel with Light/Dark Support --}}
                    <div x-show="sortDropdownOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute top-full mt-2 left-1/2 -translate-x-1/2 sm:translate-x-0 sm:left-auto sm:right-0 z-50 w-72 max-w-[calc(100vw-2rem)] p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/90 dark:border-gray-700/80 shadow-2xl shadow-gray-900/10 dark:shadow-black/60 backdrop-blur-md">

                        {{-- Section 1: Packages Horizontal Sorting --}}
                        <div>
                            <div
                                class="flex items-center justify-between text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase px-2 mb-1.5">
                                <span>مرتب‌سازی پکیج‌ها (افقی)</span>
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">ستون‌ها</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button"
                                        @click="setPackageSort('price_asc'); sortDropdownOpen = false"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-bold transition-colors text-right"
                                        :class="packageSortMode === 'price_asc'
                                            ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-black'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                                        <span>قیمت: کم به زیاد (پیش‌فرض)</span>
                                    </span>
                                    <span x-show="packageSortMode === 'price_asc'"
                                          class="text-indigo-600 dark:text-indigo-400 text-xs">✓</span>
                                </button>

                                <button type="button"
                                        @click="setPackageSort('price_desc'); sortDropdownOpen = false"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-bold transition-colors text-right"
                                        :class="packageSortMode === 'price_desc'
                                            ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-black'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                                        <span>قیمت: زیاد به کم</span>
                                    </span>
                                    <span x-show="packageSortMode === 'price_desc'"
                                          class="text-indigo-600 dark:text-indigo-400 text-xs">✓</span>
                                </button>

                                <button type="button"
                                        @click="setPackageSort('features_desc'); sortDropdownOpen = false"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-bold transition-colors text-right"
                                        :class="packageSortMode === 'features_desc'
                                            ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-black'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span>بیشترین امکانات و ماژول‌ها</span>
                                    </span>
                                    <span x-show="packageSortMode === 'features_desc'"
                                          class="text-indigo-600 dark:text-indigo-400 text-xs">✓</span>
                                </button>

                                <button type="button"
                                        @click="setPackageSort('discount_desc'); sortDropdownOpen = false"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-bold transition-colors text-right"
                                        :class="packageSortMode === 'discount_desc'
                                            ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 font-black'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        <span>دارای تخفیف ویژه اول</span>
                                    </span>
                                    <span x-show="packageSortMode === 'discount_desc'"
                                          class="text-indigo-600 dark:text-indigo-400 text-xs">✓</span>
                                </button>
                            </div>
                        </div>

                        {{-- Section 2: Features Rows Sorting --}}
                        <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-800">
                            <div
                                class="flex items-center justify-between text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase px-2 mb-1.5">
                                <span>مرتب‌سازی امکانات (عمودی)</span>
                                <span
                                    class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">ردیف‌ها</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button"
                                        @click="setRowSort('smart'); sortDropdownOpen = false"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-bold transition-colors text-right"
                                        :class="rowSortMode === 'smart'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 font-black'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>هوشمند: فراگیری امکانات (پیش‌فرض)</span>
                                    </span>
                                    <span x-show="rowSortMode === 'smart'"
                                          class="text-emerald-600 dark:text-emerald-400 text-xs">✓</span>
                                </button>

                                <button type="button"
                                        @click="setRowSort('alpha'); sortDropdownOpen = false"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-bold transition-colors text-right"
                                        :class="rowSortMode === 'alpha'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 font-black'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                        <span>الفبایی: از الف تا ی</span>
                                    </span>
                                    <span x-show="rowSortMode === 'alpha'"
                                          class="text-emerald-600 dark:text-emerald-400 text-xs">✓</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Only Differences Toggle --}}
                <button type="button"
                        @click="onlyDifferences = !onlyDifferences"
                        :aria-pressed="onlyDifferences ? 'true' : 'false'"
                        :class="onlyDifferences
                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25 ring-2 ring-indigo-500/30'
                            : 'bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border-gray-200 dark:border-gray-700/60 border'"
                        class="flex-1 sm:flex-initial h-9.5 sm:h-auto inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2 sm:px-3.5 py-2 sm:py-2.5 rounded-xl text-xs font-black transition-all cursor-pointer select-none">
                    <span
                        class="w-5 sm:w-6 h-3 sm:h-3.5 rounded-full p-0.5 transition-colors flex items-center shrink-0"
                        :class="onlyDifferences ? 'bg-white/30 justify-end' : 'bg-gray-300 dark:bg-gray-600 justify-start'">
                        <span class="w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-white shadow-xs"></span>
                    </span>
                    <span class="hidden sm:inline whitespace-nowrap">فقط تفاوت‌ها</span>
                    <span class="sm:hidden whitespace-nowrap">تفاوت‌ها</span>
                </button>

                {{-- Quick Collapse/Expand Toggle --}}
                <button type="button"
                        @click="const allOpen = openGroups.modules && openGroups.quotas_and_features; openGroups.modules = !allOpen; openGroups.quotas_and_features = !allOpen;"
                        class="w-9.5 sm:w-auto h-9.5 sm:h-auto p-2 sm:p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-200 dark:border-gray-700/60 transition-colors shrink-0 flex items-center justify-center cursor-pointer shadow-2xs"
                        :title="openGroups.modules && openGroups.quotas_and_features ? 'بستن همه بخش‌ها' : 'باز کردن همه بخش‌ها'">
                    <svg class="w-4 h-4 transform transition-transform"
                         :class="openGroups.modules && openGroups.quotas_and_features ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Expandable Quick Filters Sub-Row --}}
        <div x-show="showQuickFilters"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="pt-3 mt-3 border-t border-gray-100 dark:border-gray-800/80 flex flex-col sm:flex-row sm:items-center gap-2"
             role="tablist" aria-label="فیلترهای سریع دسته‌بندی امکانات">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 shrink-0">فیلتر بر اساس دسته‌بندی:</span>

            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto hide-scrollbar py-0.5 sm:flex-wrap">
                <button type="button"
                        role="tab"
                        @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all'
                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-3 sm:px-3.5 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <span>همه امکانات</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px]"
                          :class="selectedCategory === 'all' ? 'bg-white/20 dark:bg-black/20 text-white dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        {{ ResellerHelper::toPersianDigits($categoryCounts['all']) }}
                    </span>
                </button>

                <button type="button"
                        role="tab"
                        @click="selectedCategory = 'quotas_and_features'"
                        :class="selectedCategory === 'quotas_and_features'
                            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-3 sm:px-3.5 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>ظرفیت‌ها و امکانات اختصاصی</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px]"
                          :class="selectedCategory === 'quotas_and_features' ? 'bg-white/25 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        {{ ResellerHelper::toPersianDigits($categoryCounts['quotas_and_features']) }}
                    </span>
                </button>

                <button type="button"
                        role="tab"
                        @click="selectedCategory = 'modules'"
                        :class="selectedCategory === 'modules'
                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-3 sm:px-3.5 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>ماژول‌های فعال</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px]"
                          :class="selectedCategory === 'modules' ? 'bg-white/25 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        {{ ResellerHelper::toPersianDigits($categoryCounts['modules']) }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Dedicated Header Scroll & Package-by-Package Stepper Bar (Always visible on mobile, toggleable on desktop) --}}
    <div x-show="showTopScroll || isMobile"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-3.5 p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/90 dark:border-gray-800 shadow-xs">

        {{-- MOBILE DEDICATED LAYOUT (Ultra-Clean, Compact 2-Tier Structure) --}}
        <div class="md:hidden flex flex-col gap-2.5 w-full">
            {{-- Tier 1: Stepper Navigation Buttons with Centered Active Package Card --}}
            <div class="flex items-center justify-between gap-2 w-full" dir="rtl">
                <button type="button"
                        @click="prevPackage()"
                        :disabled="selectedPkgIndex === 0"
                        :class="selectedPkgIndex === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-95 cursor-pointer text-gray-900 dark:text-white'"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs font-black border border-gray-200 dark:border-gray-700 shadow-2xs transition-all shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span>پکیج قبلی</span>
                </button>

                <div
                    class="flex-1 min-w-0 text-center px-3 py-1.5 rounded-xl bg-indigo-50/90 dark:bg-indigo-950/80 border border-indigo-100 dark:border-indigo-800/70 shadow-2xs">
                    <span class="text-[10px] font-bold text-indigo-500 dark:text-indigo-400 block leading-tight"
                          x-text="`پکیج ${selectedPkgIndex + 1} از ${pkgList.length}`"></span>
                    <span
                        class="text-xs font-black text-indigo-700 dark:text-indigo-300 truncate block mt-0.5 leading-tight"
                        x-text="pkgList[selectedPkgIndex]?.name"></span>
                </div>

                <button type="button"
                        @click="nextPackage()"
                        :disabled="selectedPkgIndex === pkgList.length - 1"
                        :class="selectedPkgIndex === pkgList.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-95 cursor-pointer text-gray-900 dark:text-white'"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs font-black border border-gray-200 dark:border-gray-700 shadow-2xs transition-all shrink-0">
                    <span>پکیج بعدی</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Tier 2: Wide Responsive Table Position Bar --}}
            <div class="flex items-center gap-2.5 w-full pt-0.5" dir="rtl">
                <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 shrink-0">پیشرفت:</span>
                <div
                    @click="const rect = $el.getBoundingClientRect(); const ratio = Math.min(1, Math.max(0, (rect.right - $event.clientX) / rect.width)); const nearest = pkgList.length > 0 ? Math.min(pkgList.length - 1, Math.max(0, Math.floor(ratio * pkgList.length))) : 0; selectPackage(nearest);"
                    class="flex-1 relative h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full cursor-pointer overflow-hidden p-0.5 border border-gray-200/80 dark:border-gray-700/80 shadow-inner group">
                    <div
                        class="h-full bg-gradient-to-l from-indigo-500 to-indigo-600 dark:from-indigo-400 dark:to-indigo-500 rounded-full transition-all duration-300 relative shadow-xs"
                        :style="`width: ${pkgProgressPercent}%;`">
                        <span
                            class="absolute left-1 top-1/2 -translate-y-1/2 w-1 h-1 rounded-full bg-white opacity-90"></span>
                    </div>
                </div>
                <span
                    class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 tabular-nums w-8 text-left shrink-0"
                    x-text="`${pkgProgressPercent}%`"></span>
            </div>
        </div>

        {{-- DESKTOP INLINE LAYOUT (Horizontal Bar) --}}
        <div class="hidden md:flex items-center justify-between gap-4 w-full">
            <div class="flex items-center gap-3">
                <div
                    class="flex items-center gap-1.5 text-xs font-black text-gray-800 dark:text-gray-200 whitespace-nowrap">
                    <span
                        class="w-7 h-7 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </span>
                    <span>انتخاب پکیج:</span>
                </div>

                <div class="flex items-center gap-1.5" dir="rtl">
                    <button type="button"
                            @click="prevPackage()"
                            :disabled="selectedPkgIndex === 0"
                            :class="selectedPkgIndex === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer active:scale-95'"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-black border border-gray-200 dark:border-gray-700 shadow-2xs transition-all"
                            title="مشاهده پکیج قبلی">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>پکیج قبلی</span>
                    </button>

                    <div
                        class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-100 dark:border-indigo-800/60 text-xs font-black text-indigo-700 dark:text-indigo-300 flex items-center gap-1 max-w-[200px] truncate"
                        :title="pkgList[selectedPkgIndex]?.name">
                        <span class="text-[10px] text-indigo-400 font-bold"
                              x-text="`${selectedPkgIndex + 1}/${pkgList.length}:`"></span>
                        <span class="truncate" x-text="pkgList[selectedPkgIndex]?.name"></span>
                    </div>

                    <button type="button"
                            @click="nextPackage()"
                            :disabled="selectedPkgIndex === pkgList.length - 1"
                            :class="selectedPkgIndex === pkgList.length - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer active:scale-95'"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-black border border-gray-200 dark:border-gray-700 shadow-2xs transition-all"
                            title="مشاهده پکیج بعدی">
                        <span>پکیج بعدی</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 flex items-center gap-3 min-w-0" dir="rtl">
                <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 whitespace-nowrap">پیشرفت:</span>
                <div
                    @click="const rect = $el.getBoundingClientRect(); const ratio = Math.min(1, Math.max(0, (rect.right - $event.clientX) / rect.width)); const nearest = pkgList.length > 1 ? Math.min(pkgList.length - 1, Math.max(0, Math.round(ratio * (pkgList.length - 1)))) : 0; selectPackage(nearest);"
                    class="flex-1 relative h-3 bg-gray-100 dark:bg-gray-800 rounded-full cursor-pointer overflow-hidden p-0.5 border border-gray-200/80 dark:border-gray-700/80 shadow-inner group"
                    title="کلیک روی نوار برای جابجایی سریع">
                    <div
                        class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full transition-all duration-300 shadow-sm relative"
                        :style="`width: ${scrollPercent}%;`">
                        <span
                            class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-white shadow-xs opacity-90"></span>
                    </div>
                </div>
                <span
                    class="text-xs font-black text-indigo-600 dark:text-indigo-400 tabular-nums w-10 text-left shrink-0"
                    x-text="`${scrollPercent}%`"></span>
            </div>
        </div>

    </div>

    {{-- Main Comparison Matrix Table with Vertical Sticky Header & Mobile-optimized Sticky Column --}}
    @php
        $compPkgCount = $packages->count();
        $tableWrapperMaxClass = match($compPkgCount) {
            1 => 'max-w-xl mx-auto',
            2 => 'max-w-4xl mx-auto',
            3 => 'max-w-6xl mx-auto',
            default => 'w-full',
        };
    @endphp
    <div class="w-full flex justify-center">
        <div x-ref="tableContainer"
             @scroll="onTableScroll()"
             class="w-full {{ $tableWrapperMaxClass }} overflow-x-auto rounded-3xl border border-gray-200/90 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm relative"
             style="-webkit-overflow-scrolling: touch;">
            <table class="w-full border-collapse text-sm text-right table-fixed" role="table" dir="rtl">
                <colgroup>
                    <col class="w-[140px] sm:w-[240px] lg:w-[320px]" style="width: 320px;">
                    @foreach($packages as $pkg)
                        <col class="min-w-[170px]" style="{{ $compPkgCount <= 2 ? 'width: 50%;' : '' }}">
                    @endforeach
                </colgroup>

                {{-- Sticky thead (Vertical Sticky Header) --}}
                <thead class="sticky top-0 z-30 bg-white dark:bg-gray-900 shadow-sm">
                <tr class="border-b border-gray-200 dark:border-gray-800">

                    {{-- Corner Header: Modern Structured Legend & Specifications Overview --}}
                    <th scope="col"
                        class="sticky right-0 top-0 z-40 bg-gray-50 dark:bg-gray-900 border-l-2 border-gray-200 dark:border-gray-800 shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.1)] dark:shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.6)] p-3 sm:p-4 text-right align-middle w-[140px] sm:w-[240px] lg:w-[320px] min-w-[140px] sm:min-w-[240px] lg:min-w-[320px]">
                        <div class="flex flex-col justify-center h-full min-h-[220px]">
                            {{-- Centered Unified Legend Card (Stripe & Vercel Style - Eliminates White Gap) --}}
                            <div
                                class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/90 dark:border-gray-700/80 shadow-xs flex flex-col gap-3 w-full">
                                {{-- Card Integrated Header --}}
                                <div
                                    class="flex items-center gap-2 pb-2.5 border-b border-gray-100 dark:border-gray-700/70">
                                <span
                                    class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800/60 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                    <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">راهنمای مقایسه</span>
                                </div>

                                {{-- Card Items --}}
                                <div class="flex flex-col gap-2.5 text-xs">
                                    <div class="flex items-center gap-2.5 text-gray-800 dark:text-gray-200 font-bold">
                                    <span
                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-500 text-white shrink-0 shadow-2xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                        <span>شامل پکیج</span>
                                    </div>
                                    <div class="flex items-center gap-2.5 text-gray-500 dark:text-gray-400 font-medium">
                                    <span
                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-500 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40 shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </span>
                                        <span>عدم دسترسی</span>
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 text-emerald-700 dark:text-emerald-400 font-bold">
                                    <span
                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 shrink-0 shadow-2xs">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"/>
                                        </svg>
                                    </span>
                                        <span>سقف نامحدود</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Package Columns: Enforce Exact Horizontal Baseline Alignment of CTA Buttons --}}
                    @foreach($packages as $pkg)
                        @php
                            $pkgStructuredPeriods = $packagePeriods[$pkg->id] ?? [];
                        @endphp
                        <th scope="col"
                            id="package-col-{{ $pkg->id }}"
                            data-pkg-id="{{ $pkg->id }}"
                            @mouseenter="hoveredPkgId = {{ $pkg->id }}"
                            @mouseleave="hoveredPkgId = null"
                            class="relative p-4 text-center align-top min-w-[160px] sm:min-w-[200px] transition-colors duration-150 bg-white dark:bg-gray-900"
                            :class="hoveredPkgId === {{ $pkg->id }} ? 'bg-indigo-50/50 dark:bg-indigo-950/30' : ''">

                            <div class="flex flex-col justify-between h-full min-h-[220px]">
                                {{-- Top Part: Icon & Package Name --}}
                                <div>
                                    <div
                                        class="mx-auto mb-2 w-11 h-11 rounded-2xl flex items-center justify-center shadow-xs"
                                        style="{{ $pkg->light_bg_style }}">
                                        {!! PackageIcon::fromOrDefault($pkg->icon)->svg('w-5 h-5 text-current') !!}
                                    </div>
                                    <div
                                        class="font-black text-gray-900 dark:text-white text-xs sm:text-base leading-tight truncate px-1"
                                        title="{{ $pkg->name }}">
                                        {{ $pkg->name }}
                                    </div>
                                </div>

                                {{-- Middle Part: Uniform Price Block with Consistent Height --}}
                                <div
                                    class="min-h-[85px] sm:min-h-[95px] flex flex-col justify-center items-center py-1">
                                    @if($pkg->isFree())
                                        <div
                                            class="text-emerald-600 dark:text-emerald-400 font-black text-base sm:text-xl">
                                            {{ __('resellerhub::messages.comparison.free_badge') }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 font-medium mt-1">پکیج دائمی بدون هزینه
                                        </div>
                                    @else
                                        @foreach($periodRows as $pRow)
                                            @php
                                                $pKey         = $pRow['key'];
                                                $months       = max(1, (int)($pRow['months'] ?? 1));
                                                $periodInfo   = $pkgStructuredPeriods[$pKey] ?? null;
                                                $isActive     = $periodInfo !== null || $pkg->isPeriodActive($pKey);
                                                $rawPrice     = $periodInfo['raw_price'] ?? ($isActive ? $pkg->getPriceForMonths($months) : 0);
                                                $finPrice     = $periodInfo['final_price'] ?? ($isActive ? $pkg->getFinalPriceForMonths($months) : 0);
                                                $days         = ($months == 12) ? 365 : ($months * 30);
                                                $dailyRate    = ($days > 0 && $finPrice > 0) ? (int)round($finPrice / $days) : 0;
                                                $hasDiscount  = $periodInfo['has_discount'] ?? ($isActive && $pkg->hasActiveDiscountForMonths($months));
                                                $discountPct  = $periodInfo['discount_percent'] ?? 0;
                                            @endphp
                                            <div x-show="currentPeriod === '{{ $pKey }}'"
                                                 x-cloak
                                                 class="w-full flex flex-col items-center justify-center">
                                                @if($isActive)
                                                    <div class="flex items-baseline gap-1">
                                                        @if($hasDiscount)
                                                            <span
                                                                class="text-[10px] sm:text-xs text-gray-400 line-through tabular-nums">
                                                            {{ ResellerHelper::toPersianDigits(number_format($rawPrice)) }}
                                                        </span>
                                                        @endif
                                                        <span
                                                            class="font-black text-base sm:text-lg text-gray-900 dark:text-white tabular-nums">
                                                        {{ ResellerHelper::toPersianDigits(number_format($finPrice)) }}
                                                    </span>
                                                        <span
                                                            class="text-[10px] sm:text-xs font-bold text-gray-400 dark:text-gray-500">{{ $currencyLabel }}</span>
                                                    </div>

                                                    @if($dailyRate > 0)
                                                        <div class="flex items-center gap-1 mt-0.5">
                                                        <span
                                                            class="text-[10px] text-indigo-700 dark:text-indigo-400 font-bold tabular-nums">
                                                            {{ ResellerHelper::toPersianDigits(number_format($dailyRate)) }} {{ $currencyLabel }}
                                                        </span>
                                                            @if($discountPct > 0)
                                                                <span
                                                                    class="px-1 py-0.2 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 text-[9px] font-black border border-rose-200/60">
                                                                {{ ResellerHelper::toPersianDigits($discountPct) }}٪-
                                                            </span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($pkg->setup_fee > 0)
                                                        <span
                                                            class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                                                        + {{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }} راه‌اندازی
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
                                </div>

                                {{-- Bottom Part: CTA Button (Pinned to Bottom for Perfect Horizontal Alignment) --}}
                                <div class="mt-auto w-full pt-2">
                                    @foreach($periodRows as $pRow)
                                        @php
                                            $pKey     = $pRow['key'];
                                            $isActive = isset($pkgStructuredPeriods[$pKey]) || $pkg->isPeriodActive($pKey);
                                        @endphp
                                        <div x-show="currentPeriod === '{{ $pKey }}'" x-cloak class="w-full">
                                            @if($pkg->isFree() || $isActive)
                                                <a href="{{ route('portal.get-started', ['package' => $pkg->id, 'period' => $pKey]) }}"
                                                   class="inline-flex items-center justify-center gap-1 w-full px-3 py-2 rounded-xl text-xs font-black transition-all transform cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/20 active:scale-[0.99]"
                                                   :class="hoveredPkgId === {{ $pkg->id }} ? 'scale-[1.02] shadow-md' : ''"
                                                   aria-label="{{ __('resellerhub::messages.comparison.get_started') }} - {{ $pkg->name }}">
                                                    <span>{{ __('resellerhub::messages.comparison.get_started') }}</span>
                                                </a>
                                            @else
                                                <button type="button" disabled
                                                        class="inline-flex items-center justify-center gap-1 w-full px-3 py-2 rounded-xl text-xs font-bold cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500">
                                                    <span>{{ __('resellerhub::messages.comparison.not_in_period') }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </th>
                    @endforeach
                </tr>
                </thead>

                <tbody>
                {{-- Empty State Row if no search matches --}}
                <tr x-show="totalMatchesCount === 0" x-cloak class="border-b border-gray-100 dark:border-gray-800">
                    <td colspan="{{ $packages->count() + 1 }}" class="py-16 text-center">
                        <div class="max-w-xs mx-auto flex flex-col items-center">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('resellerhub::messages.comparison.no_results') }}</p>
                            <button type="button"
                                    @click="searchQuery = ''; onlyDifferences = false; selectedCategory = 'all';"
                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                                <span>{{ __('resellerhub::messages.comparison.clear_search') }}</span>
                            </button>
                        </div>
                    </td>
                </tr>

                @foreach($groups as $group)
                    @php $cat = $group['category']; @endphp

                    {{-- Group Header Row (Styled beautifully with category theme) --}}
                    <tr data-group-header="{{ $cat }}" class="border-y border-gray-200/80 dark:border-gray-700/80 cursor-pointer select-none transition-colors
                           {{ $cat === 'modules' ? 'bg-indigo-50/70 hover:bg-indigo-100/70 dark:bg-indigo-950/40 dark:hover:bg-indigo-950/60' : 'bg-emerald-50/70 hover:bg-emerald-100/70 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/60' }}"
                        x-show="groupHasMatches('{{ $cat }}')"
                        role="button"
                        tabindex="0"
                        :aria-expanded="openGroups['{{ $cat }}'] ? 'true' : 'false'"
                        @keydown.enter.prevent="toggleGroup('{{ $cat }}')"
                        @keydown.space.prevent="toggleGroup('{{ $cat }}')"
                        @click="toggleGroup('{{ $cat }}')">

                        <th scope="row"
                            class="sticky right-0 z-20 border-l-2 border-gray-200 dark:border-gray-700 shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.1)] dark:shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.6)] py-3.5 px-3 sm:px-4 text-xs font-black uppercase tracking-wider text-right w-[140px] sm:w-[240px] lg:w-[320px] min-w-[140px] sm:min-w-[240px] lg:min-w-[320px]
                               {{ $cat === 'modules' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-900 dark:text-indigo-200' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-900 dark:text-emerald-200' }}">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    @if($cat === 'modules')
                                        <span
                                            class="w-6 h-6 rounded-lg bg-indigo-200/60 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                             aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2"
                                                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    </span>
                                    @else
                                        <span
                                            class="w-6 h-6 rounded-lg bg-emerald-200/60 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                             aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2"
                                                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </span>
                                    @endif
                                    <span class="font-black text-xs sm:text-sm">{{ $group['label'] }}</span>
                                    <span
                                        class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-white/70 dark:bg-black/30 border border-black/5 dark:border-white/10">
                                    {{ ResellerHelper::toPersianDigits(count($group['rows'])) }}
                                </span>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 transform transition-transform duration-200 shrink-0"
                                     :class="openGroups['{{ $cat }}'] ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </th>

                        {{-- Option 2: Clean Numerical Badges per Package Column (e.g. "۱۰ ویژگی" / "—") --}}
                        @foreach($packages as $pkg)
                            @php
                                $activeCountInGrp = 0;
                                foreach ($group['rows'] as $r) {
                                    $v = $r['values'][$pkg->id] ?? null;
                                    $isActive = ($r['type'] === 'quota') ? ($v !== null && $v > 0) : ($v === true);
                                    if ($isActive) $activeCountInGrp++;
                                }
                            @endphp
                            <td data-pkg-id="{{ $pkg->id }}"
                                class="py-2.5 px-3 sm:px-4 text-center align-middle transition-colors
                                   {{ $cat === 'modules' ? 'bg-indigo-50/50 hover:bg-indigo-100/60 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50' : 'bg-emerald-50/50 hover:bg-emerald-100/60 dark:bg-emerald-950/30 dark:hover:bg-emerald-950/50' }}">

                                {{-- When Group is OPEN: Quiet minimal space --}}
                                <div x-show="openGroups['{{ $cat }}']"
                                     class="opacity-0 group-hover:opacity-100 transition-opacity text-[10px] font-medium text-gray-400">
                                    <span class="sr-only">کلیک برای بستن</span>
                                </div>

                                {{-- When Group is COLLAPSED (Option 2): Simple readable text badge --}}
                                <div x-show="!openGroups['{{ $cat }}']"
                                     x-cloak
                                     class="flex items-center justify-center py-1">
                                    @if($activeCountInGrp > 0)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black shadow-2xs transition-all
                                                 {{ $cat === 'modules'
                                                     ? 'bg-indigo-100/80 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70'
                                                     : 'bg-emerald-100/80 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70' }}">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ ResellerHelper::toPersianDigits($activeCountInGrp) }} {{ $cat === 'modules' ? 'ماژول' : 'ویژگی' }}</span>
                                    </span>
                                    @else
                                        <span class="text-xs font-black text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    {{-- Feature Rows --}}
                    @foreach($group['rows'] as $rowIndex => $row)
                        @php
                            $rowLabel   = $row['label'];
                            $hasDiff    = (bool)($row['has_difference'] ?? true);
                            $rowType    = $row['type'] ?? 'module';
                            $maxQuota   = $row['max_quota'] ?? 100;
                            $hasDesc    = !empty($row['description']);
                            $rowSlug    = $row['slug'] ?? ('row_' . $loop->parent->index . '_' . $rowIndex);
                        @endphp
                        <tr data-cat="{{ $cat }}"
                            data-label="{{ $rowLabel }}"
                            data-original-index="{{ $rowIndex }}"
                            :style="activeTooltip === '{{ $rowSlug }}' ? 'position: relative; z-index: 25;' : ''"
                            class="group border-b border-gray-100 dark:border-gray-800/50 transition-colors duration-150
                               {{ $rowIndex % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/20' }}"
                            x-show="openGroups['{{ $cat }}'] && rowMatches(@js($rowLabel), {{ $hasDiff ? 'true' : 'false' }})"
                            x-cloak>

                            {{-- Sticky first column: Elevated z-index on active tooltip to prevent clipping --}}
                            <th scope="row"
                                :style="activeTooltip === '{{ $rowSlug }}' ? 'z-index: 35;' : ''"
                                class="sticky right-0 z-20 py-3.5 px-3 sm:px-4 text-right text-xs sm:text-sm text-gray-700 dark:text-gray-300 font-medium border-l-2 border-gray-200 dark:border-gray-800 shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.1)] dark:shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.6)] transition-colors duration-150
                                   w-[140px] sm:w-[240px] lg:w-[320px] min-w-[140px] sm:min-w-[240px] lg:min-w-[320px] whitespace-normal break-words
                                   {{ $rowIndex % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}
                                   group-hover:bg-indigo-50 dark:group-hover:bg-gray-700">

                                <div class="flex items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="leading-snug">{{ $rowLabel }}</span>
                                    </div>

                                    {{-- Modern Tooltip Info Button: Fully Synced with Dark/Light Mode --}}
                                    @if($hasDesc)
                                        <div class="relative inline-flex items-center shrink-0"
                                             @click.outside="if (activeTooltip === '{{ $rowSlug }}') activeTooltip = null">
                                            <button type="button"
                                                    @click.stop="activeTooltip = (activeTooltip === '{{ $rowSlug }}' ? null : '{{ $rowSlug }}')"
                                                    @mouseenter="if (!isMobile) activeTooltip = '{{ $rowSlug }}'"
                                                    @mouseleave="if (!isMobile) activeTooltip = null"
                                                    :class="activeTooltip === '{{ $rowSlug }}'
                                                    ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/80 ring-2 ring-indigo-500/20'
                                                    : 'text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/70'"
                                                    class="w-5 h-5 rounded-full flex items-center justify-center transition-all cursor-help focus:outline-none"
                                                    aria-label="اطلاعات بیشتر درباره {{ $rowLabel }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                            <div x-show="activeTooltip === '{{ $rowSlug }}'"
                                                 x-cloak
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                                 class="absolute top-full mt-1.5 right-0 z-50 w-52 sm:w-72 max-w-[210px] sm:max-w-xs p-3 sm:p-3.5 rounded-2xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-md text-gray-800 dark:text-gray-100 shadow-xl shadow-gray-900/10 dark:shadow-2xl dark:shadow-black/70 border border-gray-200/90 dark:border-gray-700/80 pointer-events-none text-right transition-colors">
                                                <div
                                                    class="flex items-center gap-1.5 font-black text-indigo-600 dark:text-indigo-400 text-xs mb-1.5">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="truncate">{{ $rowLabel }}</span>
                                                </div>
                                                <p class="text-[11px] leading-relaxed text-gray-600 dark:text-gray-300 font-normal">
                                                    {{ $row['description'] }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </th>

                            {{-- Value Cells per Package --}}
                            @foreach($packages as $pkg)
                                @php
                                    $val = $row['values'][$pkg->id] ?? null;
                                @endphp
                                <td data-pkg-id="{{ $pkg->id }}"
                                    @mouseenter="hoveredPkgId = {{ $pkg->id }}"
                                    @mouseleave="hoveredPkgId = null"
                                    class="py-3.5 px-3 sm:px-4 text-center align-middle transition-colors duration-150 group-hover:bg-indigo-50/40 dark:group-hover:bg-indigo-950/20"
                                    :class="hoveredPkgId === {{ $pkg->id }} ? 'bg-indigo-50/60 dark:bg-indigo-950/30' : ''">

                                    @if($rowType === 'module' || $rowType === 'feature')
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
                                                  class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-50/80 dark:bg-rose-950/40 text-rose-500 dark:text-rose-400 mx-auto transition-transform hover:scale-110">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                                      d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </span>
                                        @endif

                                    @elseif($rowType === 'quota')
                                        @if($val === null)
                                            <span title="{{ __('resellerhub::messages.comparison.not_included') }}"
                                                  class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-50/80 dark:bg-rose-950/40 text-rose-500 dark:text-rose-400 mx-auto transition-transform hover:scale-110">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                                      d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </span>
                                        @elseif($val === -1)
                                            <span title="{{ __('resellerhub::messages.comparison.unlimited') }}"
                                                  class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 mx-auto transition-transform hover:scale-110 shadow-xs">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                                 aria-hidden="true">
                                                <path
                                                    d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"/>
                                            </svg>
                                        </span>
                                        @else
                                            @php
                                                $quotaPct = $maxQuota > 0 ? min(100, max(12, round(($val / $maxQuota) * 100))) : 50;
                                            @endphp
                                            <div
                                                class="flex flex-col items-center justify-center gap-1 mx-auto max-w-[120px]">
                                                <div class="flex items-baseline gap-1">
                                                <span
                                                    class="text-xs sm:text-sm font-black text-gray-900 dark:text-white tabular-nums">
                                                    {{ ResellerHelper::toPersianDigits(number_format($val)) }}
                                                </span>
                                                    @if(!empty($row['unit']))
                                                        <span
                                                            class="text-[10px] text-gray-500 dark:text-gray-400 font-normal">
                                                        {{ $row['unit'] }}
                                                    </span>
                                                    @endif
                                                </div>
                                                <div
                                                    class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-[2.5px] overflow-hidden">
                                                    <div
                                                        class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full transition-all duration-300"
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

                {{-- Table Footer with Action Buttons (Only visible when table groups are open) --}}
                <tfoot x-show="openGroups.modules || openGroups.quotas_and_features" x-cloak>
                <tr class="border-t-2 border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900/80">
                    <td class="sticky right-0 z-20 bg-gray-50 dark:bg-gray-900 border-l-2 border-gray-200 dark:border-gray-800 shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.1)] dark:shadow-[-8px_0_16px_-4px_rgba(0,0,0,0.6)] py-6 px-3 sm:px-4 text-right w-[140px] sm:w-[240px] lg:w-[320px] min-w-[140px] sm:min-w-[240px] lg:min-w-[320px]">
                        <div class="font-black text-xs sm:text-sm text-gray-900 dark:text-white leading-tight">
                            {{ __('resellerhub::messages.comparison.cta_footer_title') }}
                        </div>
                        <div class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-[200px]">
                            {{ __('resellerhub::messages.comparison.cta_footer_desc') }}
                        </div>
                    </td>

                    @foreach($packages as $pkg)
                        @php
                            $pkgStructuredPeriods = $packagePeriods[$pkg->id] ?? [];
                        @endphp
                        <td data-pkg-id="{{ $pkg->id }}"
                            @mouseenter="hoveredPkgId = {{ $pkg->id }}"
                            @mouseleave="hoveredPkgId = null"
                            class="py-6 px-3 sm:px-4 text-center align-middle transition-colors duration-150"
                            :class="hoveredPkgId === {{ $pkg->id }} ? 'bg-indigo-50/60 dark:bg-indigo-950/30' : ''">

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
                                           class="inline-flex items-center justify-center gap-1 w-full px-3 py-2.5 rounded-xl text-xs font-black transition-all transform cursor-pointer bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 active:scale-[0.99]"
                                           :class="hoveredPkgId === {{ $pkg->id }} ? 'scale-[1.02] shadow-md' : ''"
                                           aria-label="{{ __('resellerhub::messages.comparison.get_started') }} - {{ $pkg->name }}">
                                            <span>{{ __('resellerhub::messages.comparison.get_started') }}</span>
                                        </a>
                                    @else
                                        <button type="button" disabled
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
    </div>
</section>
