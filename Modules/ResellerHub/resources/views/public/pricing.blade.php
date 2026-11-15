@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Enums\PackageIcon;

    $settings = $settings ?? [];
    $heroEnabled = $settings['portal_hero_enabled'] ?? true;
    $heroBadge = $settings['portal_hero_badge'] ?? 'تعرفه‌ها و پکیج‌های رسمی سامانه';
    $heroTitle = $settings['portal_hero_title'] ?? 'سرمایه‌گذاری روی رشد کسب‌وکار، با تعرفه‌های شفاف';
    $heroSubtitle = $settings['portal_hero_subtitle'] ?? 'شفاف، بدون هزینه‌های پنهان، با قابلیت ارتقاء و تغییر دوره در هر زمان.';
    $heroTrustBadges = $settings['portal_hero_trust_badges'] ?? [
        'بدون هزینه‌های پنهان',
        'فعال‌سازی آنی پس از پرداخت',
        'امکان تغییر دوره یا ارتقاء پکیج',
    ];
    if (is_string($heroTrustBadges)) {
        $heroTrustBadges = json_decode($heroTrustBadges, true) ?: [];
    }

    $gridColumns = (string)($settings['portal_grid_columns'] ?? '3');
    $gridColsClass = match ($gridColumns) {
        '2' => 'grid-cols-1 md:grid-cols-2 max-w-5xl mx-auto',
        '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3',
    };

    $featuredPackageId = $settings['portal_featured_package_id'] ?? null;
    $showDailyPrice = $settings['portal_show_daily_price'] ?? true;
    $showSetupFee = $settings['portal_show_setup_fee'] ?? true;
    $ctaText = !empty(trim((string)($settings['portal_cta_text'] ?? ''))) ? $settings['portal_cta_text'] : 'انتخاب و شروع';

    $enableFilters = $settings['portal_enable_filters'] ?? true;
    $enableModuleFilter = $settings['portal_enable_module_filter'] ?? true;
    $comparisonMode = $settings['portal_comparison_mode'] ?? 'collapsible';

    $faqEnabled = $settings['portal_faq_enabled'] ?? true;
    $faqItems = $settings['portal_faq_items'] ?? [
        [
            'q' => 'آیا امکان تغییر دوره یا ارتقاء پکیج پس از خرید وجود دارد؟',
            'a' => 'بله، در هر لحظه از پنل کاربری می‌توانید پکیج خود را ارتقاء داده یا دوره اشتراک را تمدید فرمایید.',
        ],
        [
            'q' => 'راه‌اندازی سامانه چقدر زمان می‌برد؟',
            'a' => 'فرآیند ایجاد پایگاه داده و پیکربندی اولیه به صورت ۱۰۰٪ خودکار و آنی بلافاصله پس از پرداخت انجام می‌شود.',
        ],
        [
            'q' => 'آیا امکان اتصال دامنه اختصاصی وجود دارد؟',
            'a' => 'بله، علاوه بر آدرس رایگان ساب‌دامین، می‌توانید دامنه اختصاصی شرکت خود را به سامانه متصل فرمایید.',
        ],
    ];
    if (is_string($faqItems)) {
        $faqItems = json_decode($faqItems, true) ?: [];
    }

    $allModulesInPackages = $packages->flatMap(fn($p) => is_array($p->modules) ? $p->modules : [])->unique()->values();
@endphp

@extends('layouts.web')

@section('title', 'تعرفه‌ها و پکیج ها')

@section('content')
    <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-950 pt-24 pb-32"
         x-data="{
            currentPeriod: '{{ $defaultPeriod }}',
            searchQuery: '',
            selectedModule: 'all',
            filterPackage(name, modules) {
                const q = (this.searchQuery || '').trim().toLowerCase();
                const matchName = !q || name.toLowerCase().includes(q);
                const matchModule = this.selectedModule === 'all' || (Array.isArray(modules) && modules.includes(this.selectedModule));
                return matchName && matchModule;
            }
         }">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($heroEnabled)
                <div class="mx-auto max-w-3xl text-center mb-12">
                    @if(!empty($heroBadge))
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs mb-4">
                            <span>{{ $heroBadge }}</span>
                        </div>
                    @endif
                    @if(!empty($heroTitle))
                        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">
                            {{ $heroTitle }}
                        </h1>
                    @endif
                    @if(!empty($heroSubtitle))
                        <p class="mt-4 text-sm sm:text-base text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                            {{ $heroSubtitle }}
                        </p>
                    @endif

                    @if(!empty($heroTrustBadges))
                        <div
                            class="mt-5 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                            @foreach($heroTrustBadges as $badge)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                                     stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Subscription Period Tabs (Responsive Hybrid Pattern matching Package Upgrade) --}}
            @if($packages->isNotEmpty() && !empty($availablePeriods))
                @php
                    $primaryPeriods = [];
                    $secondaryPeriods = [];
                    $hasMonthlyOrYearly = isset($availablePeriods['monthly_price']) || isset($availablePeriods['yearly_price']);

                    if ($hasMonthlyOrYearly) {
                        foreach ($availablePeriods as $k => $data) {
                            if ($k === 'monthly_price' || $k === 'yearly_price') {
                                $primaryPeriods[$k] = $data;
                            } else {
                                $secondaryPeriods[$k] = $data;
                            }
                        }
                    } else {
                        $idx = 0;
                        foreach ($availablePeriods as $k => $data) {
                            if ($idx < 2) {
                                $primaryPeriods[$k] = $data;
                            } else {
                                $secondaryPeriods[$k] = $data;
                            }
                            $idx++;
                        }
                    }

                    $secondaryTitles = [];
                    foreach ($secondaryPeriods as $secKey => $secData) {
                        $secondaryTitles[$secKey] = [
                            'label' => $secData['label'],
                            'short' => $secData['short'] ?? ResellerHelper::durationLabel($secData['months'] ?? null, false),
                            'badge' => $secData['badge'] ?? '',
                        ];
                    }
                @endphp

                <div class="flex justify-center mb-12 px-4"
                     x-data="{
                         otherDurationsOpen: false,
                         secondaryKeys: {{ json_encode(array_keys($secondaryPeriods)) }},
                         secondaryTitles: {{ json_encode($secondaryTitles, JSON_UNESCAPED_UNICODE) }}
                     }">
                    <div role="tablist" aria-label="دوره‌های اشتراک"
                         class="inline-flex items-center p-1 sm:p-1.5 bg-gray-100 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/60 rounded-2xl shadow-xs gap-1 sm:gap-1.5 w-full sm:w-auto max-w-md sm:max-w-none justify-center">
                        @foreach($primaryPeriods as $pKey => $pData)
                            @php
                                $pShort = $pData['short'] ?? ResellerHelper::durationLabel($pData['months'] ?? null, false);
                                $pBadge = $pData['badge'] ?? (($pData['months'] ?? null) == 12 ? 'بیشترین صرفه' : '');
                            @endphp
                            <button type="button"
                                    role="tab"
                                    :aria-selected="currentPeriod === '{{ $pKey }}' ? 'true' : 'false'"
                                    @click="currentPeriod = '{{ $pKey }}'; otherDurationsOpen = false"
                                    :class="currentPeriod === '{{ $pKey }}'
                                        ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                                    class="relative flex-1 sm:flex-initial px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer min-h-[42px] select-none">
                                <span class="sm:hidden">{{ $pShort }}</span>
                                <span class="hidden sm:inline">{{ $pData['label'] }}</span>

                                @if(!empty($pBadge))
                                    <span
                                        class="absolute -top-2 left-1/2 -translate-x-1/2 sm:static sm:translate-x-0 px-1.5 py-0.5 rounded-full text-[9px] font-black tracking-tight shadow-xs whitespace-nowrap"
                                        :class="currentPeriod === '{{ $pKey }}'
                                              ? 'bg-indigo-600 text-white'
                                              : 'bg-emerald-600 text-white dark:bg-emerald-500'">
                                        {{ $pBadge }}
                                    </span>
                                @endif
                            </button>
                        @endforeach

                        @if(!empty($secondaryPeriods))
                            <div class="relative flex-1 sm:flex-initial" @click.outside="otherDurationsOpen = false">
                                <button type="button"
                                        @click="otherDurationsOpen = !otherDurationsOpen"
                                        :class="secondaryKeys.includes(currentPeriod)
                                            ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black'
                                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                                        class="w-full relative px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-1 cursor-pointer min-h-[42px] select-none">
                                    <span class="truncate">
                                        <span class="sm:hidden"
                                              x-text="secondaryTitles[currentPeriod] ? secondaryTitles[currentPeriod].short : 'سایر دوره‌ها'">سایر دوره‌ها</span>
                                        <span class="hidden sm:inline"
                                              x-text="secondaryTitles[currentPeriod] ? secondaryTitles[currentPeriod].label : 'سایر دوره‌ها'">سایر دوره‌ها</span>
                                    </span>

                                    <template
                                        x-if="secondaryTitles[currentPeriod] && secondaryTitles[currentPeriod].badge">
                                        <span
                                            class="hidden md:inline px-1.5 py-0.5 rounded-full text-[8.5px] font-black bg-indigo-600 text-white shadow-xs whitespace-nowrap"
                                            x-text="secondaryTitles[currentPeriod].badge"></span>
                                    </template>

                                    <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
                                         :class="otherDurationsOpen ? 'rotate-180 text-indigo-600 dark:text-indigo-300' : 'text-gray-400'"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="otherDurationsOpen"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute end-0 top-full mt-2 w-48 sm:w-56 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/90 dark:border-gray-700 shadow-xl p-1.5 z-30 space-y-0.5">
                                    <div
                                        class="px-2.5 py-1.5 text-[10.5px] font-bold text-gray-400 border-b border-gray-100 dark:border-gray-700/60 mb-1">
                                        سایر دوره‌های زمانی:
                                    </div>
                                    @foreach($secondaryPeriods as $secKey => $secData)
                                        @php
                                            $secBadge = $secData['badge'] ?? '';
                                        @endphp
                                        <button type="button"
                                                @click="currentPeriod = '{{ $secKey }}'; otherDurationsOpen = false"
                                                :class="currentPeriod === '{{ $secKey }}'
                                                    ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-750 font-medium'"
                                                class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition-colors cursor-pointer text-right">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span>{{ $secData['label'] }}</span>
                                                @if(!empty($secBadge))
                                                    <span
                                                        class="px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300">
                                                        {{ $secBadge }}
                                                    </span>
                                                @endif
                                            </div>
                                            <template x-if="currentPeriod === '{{ $secKey }}'">
                                                <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                     stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </template>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($packages->isEmpty())
                <div
                    class="text-center py-16 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm max-w-md mx-auto">
                    <div
                        class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">در حال حاضر هیچ پکیجی تعریف نشده
                        است.</p>
                </div>
            @else
                {{-- Interactive Search & Filters Bar --}}
                @if($enableFilters && $packages->isNotEmpty())
                    <div class="mb-10 max-w-4xl mx-auto space-y-3.5">
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div class="relative w-full sm:flex-1">
                                <input type="text"
                                       x-model="searchQuery"
                                       placeholder="جستجوی سریع پکیج مورد نظر (بر اساس نام)..."
                                       class="w-full rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 px-4 py-2.5 ps-10 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 shadow-xs transition-all">
                                <svg class="w-4 h-4 text-gray-400 absolute start-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        @if($enableModuleFilter && $allModulesInPackages->isNotEmpty())
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <span class="text-[11px] font-bold text-gray-400 shrink-0 ms-1">فیلتر ماژول:</span>
                                <button type="button"
                                        @click="selectedModule = 'all'"
                                        :class="selectedModule === 'all' ? 'bg-indigo-600 text-white shadow-xs font-black' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-700 hover:bg-gray-50 font-bold'"
                                        class="px-3 py-1.5 rounded-xl text-xs transition-all shrink-0 cursor-pointer">
                                    همه
                                </button>
                                @foreach($allModulesInPackages as $mSlug)
                                    <button type="button"
                                            @click="selectedModule = '{{ $mSlug }}'"
                                            :class="selectedModule === '{{ $mSlug }}' ? 'bg-indigo-600 text-white shadow-xs font-black' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-700 hover:bg-gray-50 font-bold'"
                                            class="px-3 py-1.5 rounded-xl text-xs transition-all shrink-0 cursor-pointer">
                                        {{ ResellerHelper::getModuleTitle($mSlug) }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="grid {{ $gridColsClass }} gap-6 lg:gap-8 items-stretch">
                    @foreach($packages as $pkg)
                        @php
                            $pkgModules = is_array($pkg->modules) ? $pkg->modules : [];
                            $limitsAll = is_array($pkg->limits ?? null) ? $pkg->limits : [];
                            $rawFeats = is_array($pkg->features) ? $pkg->features : [];
                            $pkgFeats = ResellerHelper::filterCommercialFeatures($rawFeats);
                            $currencyLabel = ResellerHelper::currencyLabel();
                            $isFree = $pkg->isFree();
                            $keyHighlights = [];
                            foreach ($limitsAll as $lSlug => $lVal) {
                                $quotaTitle = ResellerHelper::getQuotaTitle($lSlug);
                                $quotaUnit = ResellerHelper::getQuotaUnit($lSlug);
                                $valText = ($lVal == -1) ? 'نامحدود' : ResellerHelper::toPersianDigits($lVal) . ($quotaUnit ? ' ' . $quotaUnit : '');
                                $keyHighlights[] = ['type' => 'quota', 'text' => $quotaTitle . ': ' . $valText];
                                if (count($keyHighlights) >= 3) break;
                            }
                            foreach ($pkgModules as $mSlug) {
                                if (count($keyHighlights) >= 5) break;
                                $keyHighlights[] = ['type' => 'module', 'text' => 'ماژول ' . ResellerHelper::getModuleTitle($mSlug)];
                            }
                            foreach ($pkgFeats as $fKey) {
                                if (count($keyHighlights) >= 6) break;
                                $keyHighlights[] = ['type' => 'feature', 'text' => ResellerHelper::getFeatureTitle($fKey)];
                            }
                            $isFeatured = ($featuredPackageId && (int)$featuredPackageId === (int)$pkg->id);
                        @endphp

                        <div
                            x-show="filterPackage('{{ addslashes($pkg->name) }}', {{ json_encode($pkgModules) }})"
                            class="group flex flex-col justify-between hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 rounded-2xl bg-white dark:bg-gray-900 border p-6 sm:p-7 relative {{ $isFeatured ? 'border-indigo-500 dark:border-indigo-400 ring-2 ring-indigo-500/20 shadow-lg' : 'border-gray-200/90 dark:border-gray-800 hover:border-indigo-400/80 dark:hover:border-indigo-600' }}">

                            @if($isFeatured)
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-[11px] font-black bg-indigo-600 text-white shadow-md flex items-center gap-1 whitespace-nowrap z-10">
                                    <svg class="w-3.5 h-3.5 text-amber-300 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>پیشنهاد ما</span>
                                </div>
                            @endif

                            <div class="flex flex-col">
                                <div class="flex items-start gap-4 mb-5">
                                    <div
                                        class="w-13 h-13 sm:w-14 sm:h-14 shrink-0 rounded-2xl flex items-center justify-center shadow-xs"
                                        style="{{ $pkg->light_bg_style }}">
                                        {!! PackageIcon::fromOrDefault($pkg->icon)->svg('w-7 h-7 text-current') !!}
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white truncate"
                                                title="{{ $pkg->name }}">{{ $pkg->name }}</h3>
                                            @if($isFree)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">رایگان</span>
                                            @elseif($pkg->hasActiveDiscount())
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    @if($pkg->discount_type === 'percentage')
                                                        {{ ResellerHelper::toPersianDigits($pkg->discount_value) }}٪
                                                        تخفیف
                                                    @else
                                                        {{ ResellerHelper::toPersianDigits(number_format($pkg->discount_value)) }}
                                                        {{ $currencyLabel }} تخفیف
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-2 leading-relaxed h-[36px]">{{ $pkg->description }}</p>
                                    </div>
                                </div>

                                {{-- Hero Pricing Box (Directly below header) --}}
                                <div
                                    class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 mb-6">
                                    @if($isFree)
                                        <div class="flex flex-col gap-3.5">
                                            <div class="flex items-baseline justify-between">
                                                <div>
                                                    <span
                                                        class="text-xs text-emerald-600 dark:text-emerald-400 font-bold block mb-1">پکیج دائمی</span>
                                                    <span class="text-3xl font-black text-gray-900 dark:text-white">رایگان</span>
                                                </div>
                                                <span class="text-xs text-gray-400 font-medium">بدون هزینه اولیه</span>
                                            </div>
                                            <a href="{{ route('portal.get-started', ['package' => $pkg->id]) }}"
                                               class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-black transition-all shadow-md shadow-emerald-600/20 active:scale-[0.99] cursor-pointer">
                                                <span>شروع رایگان</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @elseif($pkg->isOneTime())
                                        @php
                                            $rawOneTime = (int)$pkg->one_time_price;
                                            $finalOneTime = (int)$pkg->final_one_time_price;
                                            $hasOneTimeDiscount = $pkg->hasActiveDiscount() && $finalOneTime < $rawOneTime;
                                        @endphp
                                        <div class="flex flex-col gap-3.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400 font-bold">پرداخت یک‌باره</span>
                                                @if($hasOneTimeDiscount)
                                                    <div class="flex items-center gap-1.5 text-xs">
                                                        <span class="text-gray-400 dark:text-gray-500 font-medium">قیمت اصلی:</span>
                                                        <span
                                                            class="text-gray-400 line-through decoration-rose-500 decoration-1.5 font-bold tabular-nums">
                                                            {{ ResellerHelper::toPersianDigits(number_format($rawOneTime)) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-baseline justify-between gap-1.5">
                                                <div class="flex items-baseline gap-1.5">
                                                    <span
                                                        class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tabular-nums">
                                                        {{ ResellerHelper::toPersianDigits(number_format($hasOneTimeDiscount ? $finalOneTime : $rawOneTime)) }}
                                                    </span>
                                                    <span
                                                        class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                                </div>
                                                @if($hasOneTimeDiscount)
                                                    <span
                                                        class="text-[11px] font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 rounded-md border border-rose-200/60 dark:border-rose-800/60">
                                                        با تخفیف ویژه
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Meta: Expiry & Setup Fee --}}
                                            @if(($hasOneTimeDiscount && $pkg->discount_expires_at) || $pkg->setup_fee > 0)
                                                <div
                                                    class="pt-2.5 border-t border-gray-200/60 dark:border-gray-700/50 flex flex-col gap-2 text-xs">
                                                    @if($hasOneTimeDiscount && $pkg->discount_expires_at)
                                                        <div
                                                            class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-bold bg-amber-50/80 dark:bg-amber-950/40 px-2.5 py-1.5 rounded-lg border border-amber-200/60 dark:border-amber-800/50">
                                                            <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2"
                                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            <span>مهلت تخفیف: تا {{ ResellerHelper::toPersianDigits($pkg->discount_expires_at_jalali) }}</span>
                                                        </div>
                                                    @endif
                                                    @if($pkg->setup_fee > 0)
                                                        <div
                                                            class="text-gray-600 dark:text-gray-300 flex items-center justify-between px-1">
                                                            <span class="font-medium">هزینه اولیه راه‌اندازی:</span>
                                                            <strong
                                                                class="font-black text-gray-900 dark:text-white tabular-nums">{{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }}</strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <a href="{{ route('portal.get-started', ['package' => $pkg->id]) }}"
                                               class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-black transition-all shadow-md shadow-indigo-600/20 active:scale-[0.99] cursor-pointer">
                                                <span>{{ $ctaText }}</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        @foreach($availablePeriods as $pKey => $pData)
                                            <div x-show="currentPeriod === '{{ $pKey }}'" x-cloak style="display: none;"
                                                 class="flex flex-col gap-3.5">
                                                @if($pkg->isPeriodActive($pKey))
                                                    @php
                                                        $rawPriceForPeriod = $pkg->getPriceForMonths($pData['months']);
                                                        $finalPriceForPeriod = $pkg->getFinalPriceForMonths($pData['months']);
                                                        $hasDiscount = $pkg->hasActiveDiscountForMonths($pData['months']) && $finalPriceForPeriod < $rawPriceForPeriod;
                                                        $discountExpJalali = $pkg->getDiscountExpiresAtJalaliForMonths($pData['months']);
                                                        $months = $pData['months'] ?? 1;
                                                        $days = ($months == 12) ? 365 : ($months * 30);
                                                        $dailyEquivalent = ($days > 0 && $finalPriceForPeriod > 0) ? (int)round($finalPriceForPeriod / $days) : 0;
                                                    @endphp

                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="text-xs text-gray-500 dark:text-gray-400 font-bold truncate">پرداخت {{ $pData['label'] }}</span>
                                                        @if($hasDiscount)
                                                            <div class="flex items-center gap-1 text-xs">
                                                                <span
                                                                    class="text-gray-400 dark:text-gray-500 font-medium text-[11px]">قیمت اصلی:</span>
                                                                <span
                                                                    class="text-gray-400 line-through decoration-rose-500 decoration-1.5 font-bold tabular-nums text-xs">
                                                                    {{ ResellerHelper::toPersianDigits(number_format($rawPriceForPeriod)) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="flex items-baseline justify-between gap-1.5">
                                                        <div class="flex items-baseline gap-1.5">
                                                            <span
                                                                class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tabular-nums">
                                                                {{ ResellerHelper::toPersianDigits(number_format($finalPriceForPeriod)) }}
                                                            </span>
                                                            <span
                                                                class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                                        </div>
                                                        @if($hasDiscount)
                                                            <span
                                                                class="text-[11px] font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 rounded-md border border-rose-200/60 dark:border-rose-800/60">
                                                                با تخفیف ویژه
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Prominent Daily & Monthly Equivalent Box for All Periods --}}
                                                    @if($showDailyPrice && $dailyEquivalent > 0)
                                                        <div
                                                            class="px-3.5 py-2.5 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/70 flex items-center justify-between shadow-2xs gap-2">
                                                            <span
                                                                class="text-indigo-900 dark:text-indigo-200 font-bold flex items-center gap-1.5 text-xs shrink-0">
                                                                <svg
                                                                    class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                              stroke-linejoin="round"
                                                                                              stroke-width="2"
                                                                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                معادل روزانه:
                                                            </span>
                                                            <span
                                                                class="font-black text-indigo-700 dark:text-indigo-300 tabular-nums text-xs sm:text-sm">
                                                                {{ ResellerHelper::toPersianDigits(number_format($dailyEquivalent)) }} {{ $currencyLabel }}
                                                            </span>
                                                        </div>
                                                    @endif


                                                    {{-- Meta: Expiry & Setup Fee --}}
                                                    @if(($hasDiscount && $discountExpJalali) || ($showSetupFee && $pkg->setup_fee > 0))
                                                        <div
                                                            class="pt-2.5 border-t border-gray-200/60 dark:border-gray-700/50 flex flex-col gap-2 text-xs">
                                                            @if($hasDiscount && $discountExpJalali)
                                                                <div
                                                                    class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-bold bg-amber-50/80 dark:bg-amber-950/40 px-2.5 py-1.5 rounded-lg border border-amber-200/60 dark:border-amber-800/50">
                                                                    <svg class="w-4 h-4 shrink-0 text-amber-500"
                                                                         fill="none" viewBox="0 0 24 24"
                                                                         stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                              stroke-linejoin="round" stroke-width="2"
                                                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                    <span>مهلت تخفیف ویژه: تا {{ ResellerHelper::toPersianDigits($discountExpJalali) }}</span>
                                                                </div>
                                                            @endif
                                                            @if($showSetupFee && $pkg->setup_fee > 0)
                                                                <div
                                                                    class="text-gray-600 dark:text-gray-300 flex items-center justify-between px-1">
                                                                    <span
                                                                        class="font-medium">هزینه اولیه راه‌اندازی:</span>
                                                                    <strong
                                                                        class="font-black text-gray-900 dark:text-white tabular-nums">{{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }}</strong>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <a href="{{ route('portal.get-started', ['package' => $pkg->id, 'period' => $pKey]) }}"
                                                       class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-black transition-all shadow-md shadow-indigo-600/20 active:scale-[0.99] cursor-pointer">
                                                        <span>{{ $ctaText }}</span>
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <div class="py-4 text-center">
                                                        <span class="text-xs font-bold text-gray-400">این پکیج فاقد دوره {{ $pData['label'] }} است</span>
                                                    </div>
                                                    <button type="button" disabled
                                                            class="w-full inline-flex items-center justify-center py-3 px-4 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-bold cursor-not-allowed">
                                                        <span>غیرقابل انتخاب در این دوره</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Features Section --}}
                                <div class="pt-1.5">
                                    <div
                                        class="text-xs mb-4 gap-1 font-black text-gray-800 dark:text-gray-200 flex items-center">
                                        <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                        <span>امکانات و ظرفیت‌های اصلی:</span>
                                    </div>
                                    <ul class="space-y-2.5">
                                        @forelse($keyHighlights as $kh)
                                            <li class="flex items-center gap-2.5 p-2 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60 text-xs text-gray-700 dark:text-gray-300">
                                                <span
                                                    class="w-5 h-5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-800/50">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                   stroke-linejoin="round"
                                                                                   stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                                <span
                                                    class="font-medium leading-relaxed truncate">{{ $kh['text'] }}</span>
                                            </li>
                                        @empty
                                            <li class="text-xs text-gray-400 italic p-2">اطلاعاتی ثبت نشده است.</li>
                                        @endforelse
                                    </ul>
                                </div>

                            </div>

                            {{-- Bottom Secondary Action --}}
                            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/80 text-center">
                                <a href="#plan-comparison-section"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                                    <span>مشاهده مقایسه کامل امکانات</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Plan Comparison Matrix with Minimal Toggle Button --}}
            @if($comparisonMode !== 'disabled' && $allPackages->count() > 1 && !empty($comparisonData['groups']))
                @php
                    $totalComparisonFeatures = collect($comparisonData['groups'])->sum(fn($g) => count($g['rows'] ?? []));
                @endphp
                <div id="plan-comparison-section"
                     x-data="{ showComparison: {{ $comparisonMode === 'expanded' ? 'true' : "window.location.hash === '#plan-comparison-section'" }} }"
                     class="mt-16 sm:mt-24 scroll-mt-20">

                    {{-- Minimalist Toggle Card --}}
                    <div
                        class="rounded-2xl p-6 sm:p-7 bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 transition-all duration-300">
                        <div class="flex items-center gap-4 text-right w-full sm:w-auto">
                            <div
                                class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                        مقایسه دقیق امکانات و دسترسی‌ها
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <button type="button"
                                @click="showComparison = !showComparison"
                                :class="showComparison
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25 ring-2 ring-indigo-500/20'
                                    : 'bg-gray-100 hover:bg-gray-600 text-gray-700 dark:bg-gray-800 dark:text-gray-200'"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition-all duration-200 cursor-pointer shrink-0">
                            <span x-text="showComparison ? 'بستن جدول مقایسه' : 'نمایش جدول مقایسه کامل'"></span>
                            <svg class="w-4 h-4 transition-transform duration-300"
                                 :class="showComparison ? 'rotate-180' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Collapsible Comparison Container --}}
                    <div x-show="showComparison"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         x-cloak>
                        @include('resellerhub::public.partials.pricing-comparison', [
                            'packages'         => $allPackages,
                            'comparisonData'   => $comparisonData,
                            'availablePeriods' => $availablePeriods,
                            'packagePeriods'   => $packagePeriods ?? [],
                        ])
                    </div>
                </div>
            @endif

            {{-- FAQ Section --}}
            @if($faqEnabled && !empty($faqItems))
                <div class="mt-16 sm:mt-24 max-w-4xl mx-auto scroll-mt-20" id="faq-section">
                    <div class="text-center mb-10">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs mb-3">
                            <span>پاسخ به ابهامات متداول</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">
                            سوالات متداول خریداران
                        </h2>
                    </div>

                    <div class="space-y-3" x-data="{ activeFaq: null }">
                        @foreach($faqItems as $fIdx => $fItem)
                            @if(!empty(trim($fItem['q'] ?? '')))
                                <div class="rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-2xs transition-all">
                                    <button type="button"
                                            @click="activeFaq = (activeFaq === {{ $fIdx }} ? null : {{ $fIdx }})"
                                            class="w-full flex items-center justify-between p-4 sm:p-5 text-right font-black text-xs sm:text-sm text-gray-900 dark:text-white cursor-pointer hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                        <span class="flex items-center gap-3">
                                            <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black shrink-0">
                                                ؟
                                            </span>
                                            <span>{{ $fItem['q'] }}</span>
                                        </span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                                             :class="activeFaq === {{ $fIdx }} ? 'rotate-180 text-indigo-600 dark:text-indigo-400' : ''"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="activeFaq === {{ $fIdx }}"
                                         x-collapse
                                         x-cloak
                                         class="px-4 sm:px-5 pb-5 pt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-gray-800">
                                        {{ $fItem['a'] ?? '' }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>

@endsection
