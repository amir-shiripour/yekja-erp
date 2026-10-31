@php use Modules\ResellerHub\App\Helpers\ResellerHelper; @endphp
@extends('layouts.web')

@section('title', 'پلن‌های قیمتی سامانه CRM')

@section('content')
    <div class="w-full min-h-screen bg-gray-50 dark:bg-gray-950 pt-24 pb-32 font-vazir"
         x-data="{ currentPeriod: '{{ $defaultPeriod }}' }">
        <div class="w-full max-w-7xl mx-auto px-6">

            <div class="mx-auto max-w-4xl text-center mb-10">
                <h1 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">تعرفه‌ها و پلن‌های
                    خرید</h1>
                <p class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">سامانه‌ای
                    متناسب با نیاز کسب‌وکار شما</p>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">بدون هزینه‌های پنهان، با قابلیت ارتقاء در هر
                    زمان.</p>
            </div>

            {{-- Dynamic Tabs --}}
            @if($packages->isNotEmpty() && !empty($availablePeriods))
                <div class="flex justify-center mb-12">
                    <div role="tablist" aria-label="دوره‌های اشتراک"
                        class="inline-flex items-center p-1.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm gap-1.5 overflow-x-auto max-w-full hide-scrollbar">
                        @foreach($availablePeriods as $pKey => $pData)
                            <button
                                role="tab"
                                :aria-selected="currentPeriod === '{{ $pKey }}' ? 'true' : 'false'"
                                @click.prevent="currentPeriod = '{{ $pKey }}'"
                                :class="currentPeriod === '{{ $pKey }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'"
                                class="rounded-xl py-2 px-5 text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer shrink-0"
                            >
                                {{ $pData['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($packages->isEmpty())
                <div
                    class="text-center py-12 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">در حال حاضر هیچ پکیجی تعریف نشده است.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-stretch">
                    @foreach($packages as $pkg)
                        @php
                            $pkgModules = is_array($pkg->modules) ? $pkg->modules : [];
                            $limitsAll = is_array($pkg->limits ?? null) ? $pkg->limits : [];
                            $rawFeats = is_array($pkg->features) ? $pkg->features : [];
                            $pkgFeats = ResellerHelper::filterCommercialFeatures($rawFeats);
                            $currencyLabel = 'تومان';
                            $isFree = $pkg->isFree();
                        @endphp

                        <div
                            class="group flex flex-col hover:shadow-lg hover:-translate-y-0.5 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6">
                            <div class="flex-grow flex flex-col gap-5 h-full">
                                {{-- Header --}}
                                <div class="flex gap-4">
                                    <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center"
                                         style="{{ $pkg->light_bg_style }}"
                                         x-html="window.renderIndustryIcon ? window.renderIndustryIcon('{{ $pkg->icon }}', 'w-7 h-7 text-current') : ''"></div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-base font-black text-gray-900 dark:text-white truncate"
                                                title="{{ $pkg->name }}">{{ $pkg->name }}</h3>
                                            @if($isFree)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">رایگان</span>
                                            @elseif($pkg->hasActiveDiscount())
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                    @if($pkg->discount_type === 'percentage')
                                                        {{ ResellerHelper::toPersianDigits($pkg->discount_value) }}٪
                                                        تخفیف
                                                    @else
                                                        {{ ResellerHelper::toPersianDigits(number_format($pkg->discount_value)) }}
                                                        تومان تخفیف
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 leading-tight h-[30px]">{{ $pkg->description }}</div>
                                    </div>
                                </div>

                                {{-- Metrics Bar --}}
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex-1 flex items-center gap-2.5 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    </span>
                                        <div class="leading-tight">
                                            <div
                                                class="text-sm font-black text-gray-900 dark:text-white">{{ ResellerHelper::toPersianDigits(count($pkgModules)) }}</div>
                                            <div class="text-xs text-gray-400 font-bold">ماژول</div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex-1 flex items-center gap-2.5 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                        <div class="leading-tight">
                                            <div
                                                class="text-sm font-black text-gray-900 dark:text-white">{{ ResellerHelper::toPersianDigits(count($pkgFeats)) }}</div>
                                            <div class="text-xs text-gray-400 font-bold">قابلیت</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quotas & Features List --}}
                                <div class="flex-1 border-t border-gray-100 dark:border-gray-800 pt-4 mt-1">
                                    <div class="text-xs font-bold text-gray-900 dark:text-white mb-3">امکانات و سقف مجاز
                                        این پکیج:
                                    </div>
                                    <ul class="space-y-2.5">
                                        {{-- Numeric Quotas --}}
                                        @foreach($limitsAll as $lSlug => $lVal)
                                            @php
                                                $quotaTitle = ResellerHelper::getQuotaTitle($lSlug);
                                                $quotaUnit = ResellerHelper::getQuotaUnit($lSlug);
                                            @endphp
                                            <li class="flex items-start gap-2.5 text-xs">
                                            <span
                                                class="mt-0.5 w-4 h-4 rounded bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center shrink-0">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor"><path stroke-linecap="round"
                                                                                 stroke-linejoin="round"
                                                                                 stroke-width="2.5"
                                                                                 d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            </span>
                                                <div
                                                    class="flex-1 flex justify-between items-center gap-2 border-b border-gray-50 dark:border-gray-800/50 pb-1.5 border-dashed">
                                                    <span
                                                        class="text-gray-600 dark:text-gray-400">{{ $quotaTitle }}</span>
                                                    <span class="font-black text-gray-900 dark:text-white">
                                                    @if($lVal == -1)
                                                            نامحدود
                                                        @else
                                                            {{ ResellerHelper::toPersianDigits($lVal) }}
                                                            @if($quotaUnit)
                                                                <span
                                                                    class="text-xs font-normal text-gray-400">{{ $quotaUnit }}</span>
                                                            @endif
                                                        @endif
                                                </span>
                                                </div>
                                            </li>
                                        @endforeach

                                        {{-- Active Modules --}}
                                        @foreach($pkgModules as $slug)
                                            <li class="flex items-start gap-2.5 text-xs">
                                            <span
                                                class="mt-0.5 w-4 h-4 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center shrink-0">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor"><path stroke-linecap="round"
                                                                                 stroke-linejoin="round"
                                                                                 stroke-width="3"
                                                                                 d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                                <div
                                                    class="flex-1 text-gray-700 dark:text-gray-300 font-medium border-b border-gray-50 dark:border-gray-800/50 pb-1.5 border-dashed">
                                                    دسترسی به ماژول {{ ResellerHelper::getModuleTitle($slug) }}
                                                </div>
                                            </li>
                                        @endforeach

                                        {{-- Only Valid Commercial Features --}}
                                        @foreach($pkgFeats as $fKey)
                                            <li class="flex items-start gap-2.5 text-xs">
                                            <span
                                                class="mt-0.5 w-4 h-4 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center shrink-0">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor"><path stroke-linecap="round"
                                                                                 stroke-linejoin="round"
                                                                                 stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                                <div
                                                    class="flex-1 text-gray-700 dark:text-gray-300 font-medium border-b border-gray-50 dark:border-gray-800/50 pb-1.5 border-dashed">
                                                    {{ ResellerHelper::getFeatureTitle($fKey) }}
                                                </div>
                                            </li>
                                        @endforeach

                                        @if(empty($limitsAll) && empty($pkgModules) && empty($pkgFeats))
                                            <li class="text-xs text-gray-400 italic">هیچ امکاناتی برای این پکیج ثبت نشده
                                                است.
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                {{-- Pricing & CTA Box --}}
                                <div class="mt-auto pt-4 flex flex-col gap-4">
                                    @if($isFree)
                                        <div class="flex flex-col gap-4 h-full justify-end">
                                            <div
                                                class="rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-800/40 overflow-hidden">
                                                <div
                                                    class="px-4 py-3 flex items-center justify-between gap-3 min-h-[64px]">
                                                    <div class="min-w-0">
                                                        <div
                                                            class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                                                            پلن رایگان
                                                        </div>
                                                        <div
                                                            class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                                            رایگان
                                                            <span class="text-xs font-medium text-gray-400">(بدون هزینه اشتراک)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="{{ route('portal.get-started', ['package' => $pkg->id]) }}"
                                               class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black transition-all cursor-pointer shadow-md">
                                                <span>شروع رایگان</span>
                                            </a>
                                        </div>
                                    @elseif($pkg->isOneTime())
                                        <div class="flex flex-col gap-4 h-full justify-end">
                                            @php
                                                $rawOneTime = (int)$pkg->one_time_price;
                                                $finalOneTime = (int)$pkg->final_one_time_price;
                                                $hasOneTimeDiscount = $pkg->hasActiveDiscount() && $finalOneTime < $rawOneTime;
                                            @endphp
                                            <div
                                                class="rounded-2xl {{ $hasOneTimeDiscount ? 'bg-gradient-to-br from-rose-50/50 via-white to-emerald-50/30 dark:from-rose-950/20 dark:via-gray-900/60 dark:to-emerald-950/20 border border-rose-200/70 dark:border-rose-800/50' : 'bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60' }} p-3.5 space-y-2">
                                                {{-- Top Row: Label + Discount Badge --}}
                                                <div class="flex items-center justify-between gap-2">
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-bold">
                                                        پرداخت یک‌باره (مادام‌العمر)
                                                    </span>
                                                    @if($hasOneTimeDiscount)
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black bg-rose-100/80 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 shrink-0 tabular-nums">
                                                            {{ ResellerHelper::toPersianDigits(number_format($rawOneTime - $finalOneTime)) }} {{ $currencyLabel }} تخفیف
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Middle Row: Final Price + Strikethrough Original Price --}}
                                                <div class="flex items-baseline justify-between gap-2 pt-0.5">
                                                    @if($hasOneTimeDiscount)
                                                        <div class="flex items-baseline gap-1">
                                                            <span
                                                                class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                                                                {{ ResellerHelper::toPersianDigits(number_format($finalOneTime)) }}
                                                            </span>
                                                            <span
                                                                class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                                        </div>
                                                        <span
                                                            class="text-xs text-gray-400 line-through decoration-rose-400/80 tabular-nums">
                                                            {{ ResellerHelper::toPersianDigits(number_format($rawOneTime)) }} {{ $currencyLabel }}
                                                        </span>
                                                    @else
                                                        <div class="flex items-baseline gap-1">
                                                            <span
                                                                class="text-base sm:text-lg font-black text-indigo-600 dark:text-indigo-400 tabular-nums">
                                                                {{ ResellerHelper::toPersianDigits(number_format($rawOneTime)) }}
                                                            </span>
                                                            <span
                                                                class="text-xs font-bold text-gray-400">{{ $currencyLabel }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Bottom Meta Row: Expiry & Setup Fee --}}
                                                @if(($hasOneTimeDiscount && $pkg->discount_expires_at) || $pkg->setup_fee > 0)
                                                    <div
                                                        class="pt-2 border-t border-dashed border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-2 flex-wrap text-xs">
                                                        @if($hasOneTimeDiscount && $pkg->discount_expires_at)
                                                            <span
                                                                class="inline-flex items-center gap-1 text-amber-700 dark:text-amber-400 font-bold">
                                                                <svg class="w-3 h-3 shrink-0" fill="none"
                                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2"
                                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                                <span>مهلت تخفیف: تا {{ ResellerHelper::toPersianDigits($pkg->discount_expires_at_jalali) }}</span>
                                                            </span>
                                                        @endif
                                                        @if($pkg->setup_fee > 0)
                                                            <span
                                                                class="text-gray-500 dark:text-gray-400 font-medium ms-auto">
                                                                راه‌اندازی: <strong
                                                                    class="font-black text-amber-600 dark:text-amber-400">{{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <a href="{{ route('portal.get-started', ['package' => $pkg->id]) }}"
                                               class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black transition-all cursor-pointer shadow-md">
                                                <span>انتخاب و شروع</span>
                                            </a>
                                        </div>
                                    @else
                                        @foreach($availablePeriods as $pKey => $pData)
                                            <div x-show="currentPeriod === '{{ $pKey }}'" x-cloak style="display: none;"
                                                 class="flex flex-col gap-4 h-full justify-end">
                                                @if($pkg->isPeriodActive($pKey))
                                                    @php
                                                        $rawPriceForPeriod = $pkg->getPriceForMonths($pData['months']);
                                                        $finalPriceForPeriod = $pkg->getFinalPriceForMonths($pData['months']);
                                                        $hasDiscount = $pkg->hasActiveDiscountForMonths($pData['months']) && $finalPriceForPeriod < $rawPriceForPeriod;
                                                        $discountExpJalali = $pkg->getDiscountExpiresAtJalaliForMonths($pData['months']);
                                                    @endphp
                                                    <div
                                                        class="rounded-2xl {{ $hasDiscount ? 'bg-gradient-to-br from-rose-50/50 via-white to-emerald-50/30 dark:from-rose-950/20 dark:via-gray-900/60 dark:to-emerald-950/20 border border-rose-200/70 dark:border-rose-800/50' : 'bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60' }} p-3.5 space-y-2">
                                                        {{-- Top Row: Period Label + Discount Amount Pill --}}
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span
                                                                class="text-xs text-gray-500 dark:text-gray-400 font-bold truncate">
                                                                پرداخت {{ $pData['label'] }}
                                                            </span>
                                                            @if($hasDiscount)
                                                                <span
                                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black bg-rose-100/80 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 shrink-0 tabular-nums">
                                                                    {{ ResellerHelper::toPersianDigits(number_format($rawPriceForPeriod - $finalPriceForPeriod)) }} {{ $currencyLabel }} تخفیف
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Middle Row: Final Payable Price + Strikethrough Raw Price --}}
                                                        <div class="flex items-baseline justify-between gap-2 pt-0.5">
                                                            @if($hasDiscount)
                                                                <div class="flex items-baseline gap-1">
                                                                    <span
                                                                        class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                                                                        {{ ResellerHelper::toPersianDigits(number_format($finalPriceForPeriod)) }}
                                                                    </span>
                                                                    <span
                                                                        class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                                                </div>
                                                                <span
                                                                    class="text-xs text-gray-400 line-through decoration-rose-400/80 tabular-nums">
                                                                    {{ ResellerHelper::toPersianDigits(number_format($rawPriceForPeriod)) }} {{ $currencyLabel }}
                                                                </span>
                                                            @else
                                                                <div class="flex items-baseline gap-1">
                                                                    <span
                                                                        class="text-base sm:text-lg font-black text-indigo-600 dark:text-indigo-400 tabular-nums">
                                                                        {{ ResellerHelper::toPersianDigits(number_format($rawPriceForPeriod)) }}
                                                                    </span>
                                                                    <span
                                                                        class="text-xs font-bold text-gray-400">{{ $currencyLabel }}</span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Bottom Meta Row: Discount Expiry & Setup Fee --}}
                                                        @if(($hasDiscount && $discountExpJalali) || $pkg->setup_fee > 0)
                                                            <div
                                                                class="pt-2 border-t border-dashed border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-2 flex-wrap text-xs">
                                                                @if($hasDiscount && $discountExpJalali)
                                                                    <span
                                                                        class="inline-flex items-center gap-1 text-amber-700 dark:text-amber-400 font-bold">
                                                                        <svg class="w-3 h-3 shrink-0" fill="none"
                                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  stroke-width="2"
                                                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                        </svg>
                                                                        <span>مهلت تخفیف: تا {{ ResellerHelper::toPersianDigits($discountExpJalali) }}</span>
                                                                    </span>
                                                                @endif
                                                                @if($pkg->setup_fee > 0)
                                                                    <span
                                                                        class="text-gray-500 dark:text-gray-400 font-medium ms-auto">
                                                                        راه‌اندازی: <strong
                                                                            class="font-black text-amber-600 dark:text-amber-400">{{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }}</strong>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <a href="{{ route('portal.get-started', ['package' => $pkg->id, 'period' => $pKey]) }}"
                                                       class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black transition-all cursor-pointer shadow-md">
                                                        <span>انتخاب و شروع</span>
                                                    </a>
                                                @else
                                                    <div
                                                        class="rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 overflow-hidden">
                                                        <div
                                                            class="px-4 py-3 flex items-center justify-center min-h-[64px]">
                                                            <span class="text-xs font-bold text-gray-400">این پکیج فاقد دوره {{ $pData['label'] }} است</span>
                                                        </div>
                                                    </div>

                                                    <button disabled
                                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-black cursor-not-allowed">
                                                        <span>غیرقابل انتخاب در این دوره</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 flex justify-center w-full" dir="rtl">
                    {{ $packages->links('resellerhub::partials.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Fix for x-cloak flash */
        [x-cloak] {
            display: none !important;
        }

        /* Hide scrollbar but keep functionality */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @include('resellerhub::user.packages.partials.icons-script')
@endsection
