@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Enums\PackageIcon;
@endphp

@extends('layouts.web')

@section('title', 'تعرفه‌ها و پکیج ها')

@section('content')
    <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-950 pt-24 pb-32"
         x-data="{ currentPeriod: '{{ $defaultPeriod }}' }">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mx-auto max-w-3xl text-center mb-12">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs mb-4">
                    <span>تعرفه‌ها و پکیج‌های رسمی سامانه</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">
                    سرمایه‌گذاری روی رشد کسب‌وکار، <span
                        class="text-indigo-600 dark:text-indigo-400">با تعرفه‌های شفاف</span>
                </h1>
                <p class="mt-4 text-sm sm:text-base text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                    شفاف، بدون هزینه‌های پنهان، با قابلیت ارتقاء و تغییر دوره در هر زمان.
                </p>
                <div
                    class="mt-5 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                         stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        بدون هزینه‌های پنهان
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                         stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        فعال‌سازی آنی پس از پرداخت
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                         stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        امکان تغییر دوره یا ارتقاء پکیج
                    </span>
                </div>
            </div>

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
                    class="text-center py-16 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm max-w-md mx-auto">
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
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8 items-stretch">
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
                        @endphp

                        <div
                            class="group flex flex-col justify-between hover:shadow-2xl hover:-translate-y-1 hover:border-indigo-400/80 dark:hover:border-indigo-600 transition-all duration-300 rounded-3xl bg-white dark:bg-gray-900 border border-gray-200/90 dark:border-gray-800 p-6 sm:p-7 relative">
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
                                                <span>انتخاب و شروع</span>
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
                                                    @if($dailyEquivalent > 0)
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
                                                    @if(($hasDiscount && $discountExpJalali) || $pkg->setup_fee > 0)
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
                                                            @if($pkg->setup_fee > 0)
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
                                                        <span>انتخاب و شروع</span>
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
            @if($allPackages->count() > 1 && !empty($comparisonData['groups']))
                @php
                    $totalComparisonFeatures = collect($comparisonData['groups'])->sum(fn($g) => count($g['rows'] ?? []));
                @endphp
                <div id="plan-comparison-section"
                     x-data="{ showComparison: window.location.hash === '#plan-comparison-section' }"
                     class="mt-16 sm:mt-24 scroll-mt-20">

                    {{-- Minimalist Toggle Card --}}
                    <div
                        class="rounded-3xl p-6 sm:p-7 bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 transition-all duration-300">
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
