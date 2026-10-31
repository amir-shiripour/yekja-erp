@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerPackage;
@endphp
{{-- Case 1: Package is Already Selected -> Show Sleek Summary Card --}}
@if($selectedPackage && !$showPackageSelector)
    @php
        $pkgModules = is_array($selectedPackage->modules) ? $selectedPackage->modules : [];
        $limitsAll = is_array($selectedPackage->limits ?? null) ? $selectedPackage->limits : [];
        $rawFeats = is_array($selectedPackage->features) ? $selectedPackage->features : [];
        $pkgFeats = ResellerHelper::filterCommercialFeatures($rawFeats);
        $isFreeAllowed = $isRegistrationWithoutPaymentAllowed ?? $this->isRegistrationWithoutPaymentAllowed();
        $isFree = $isFreeAllowed;
        $currentMonths = ResellerPackage::resolveMonthFromPeriodKey($billingPeriod);
        $rawPrice = $isFree ? 0 : $selectedPackage->getPriceForMonths($currentMonths);
        $currentPrice = $isFree ? 0 : $selectedPackage->getFinalPriceForMonths($currentMonths);
        $hasDiscount = !$isFree && ($selectedPackage->isSubscription() ? $selectedPackage->hasActiveDiscountForMonths($currentMonths) : $selectedPackage->hasActiveDiscount()) && $currentPrice < $rawPrice;
    @endphp
    <div
        class="mx-auto max-w-5xl sm:max-w-6xl rounded-3xl border border-indigo-100 dark:border-indigo-900/40 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xl shadow-indigo-600/5 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center shadow-sm"
                     style="{{ $selectedPackage->light_bg_style }}"
                     x-html="window.renderIndustryIcon ? window.renderIndustryIcon('{{ $selectedPackage->icon }}', 'w-7 h-7 text-current') : ''"></div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">{{ __('resellerhub::messages.wizard.step3.active_package_badge') }}</span>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white truncate">{{ $selectedPackage->name }}</h3>
                        @if($selectedPackage->isFree())
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                {{ $selectedPackage->getFinalSetupFeeAttribute() > 0 ? 'پلن رایگان + راه‌اندازی' : 'پلن رایگان' }}
                                            </span>
                        @elseif($isFreeAllowed)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">پکیج پیش‌فرض (شروع رایگان)</span>
                        @endif
                        @if($hasDiscount)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                @if($selectedPackage->discount_type === 'percentage')
                                    {{ ResellerHelper::toPersianDigits($selectedPackage->discount_value) }}
                                    ٪ تخفیف
                                @else
                                    {{ ResellerHelper::toPersianDigits(number_format($selectedPackage->discount_value)) }}
                                    تومان تخفیف
                                @endif
                                            </span>
                        @endif
                    </div>
                    <div
                        class="flex items-center gap-3 mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                        @if(!$isFree)
                            <span class="flex items-center gap-1 font-medium">
                                                {{ __('resellerhub::messages.portal.checkout.billing_cycle') }} <strong
                                    class="text-gray-800 dark:text-gray-200">{{ $availablePeriods[$billingPeriod]['label'] ?? 'ماهانه' }}</strong>
                                            </span>
                            <span>•</span>
                        @endif
                        @if($isFree)
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">رایگان</span>
                            @if($selectedPackage->getFinalSetupFeeAttribute() > 0)
                                <span>•</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">
                                                    + {{ ResellerHelper::formatPrice($selectedPackage->getFinalSetupFeeAttribute(), $currencyLabel) }} راه‌اندازی
                                                </span>
                            @endif
                        @elseif($hasDiscount)
                            <div class="inline-flex items-center gap-1.5">
                                                <span
                                                    class="text-xs text-gray-400 line-through tabular-nums">{{ ResellerHelper::formatPrice($rawPrice, $currencyLabel) }}</span>
                                <span
                                    class="font-bold text-rose-600 dark:text-rose-400">{{ ResellerHelper::formatPrice($currentPrice, $currencyLabel) }}</span>
                                @if($selectedPackage->discount_expires_at)
                                    <span
                                        class="text-xs text-amber-600 dark:text-amber-400 font-bold">(مهلت: تا {{ $selectedPackage->discount_expires_at_jalali }})</span>
                                @endif
                            </div>
                        @else
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ ResellerHelper::formatPrice($currentPrice, $currencyLabel) }}
                                            </span>
                        @endif
                        <span>•</span>
                        <span
                            class="text-gray-500">{{ ResellerHelper::toPersianDigits(count($pkgModules)) }} ماژول</span>
                        <span>•</span>
                        <span
                            class="text-gray-500">{{ ResellerHelper::toPersianDigits(count($pkgFeats)) }} قابلیت</span>
                    </div>
                </div>
            </div>

            <div class="shrink-0 flex items-center justify-end">
                <button wire:click="togglePackageSelector" type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200/60 dark:border-indigo-800/60 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>تغییر پکیج</span>
                </button>
            </div>
        </div>
    </div>
@else
    {{-- Case 2: Packages Grid (Opened when showPackageSelector is true or no package preselected) --}}
    <div
        class="rounded-3xl border border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-900/50 p-6 sm:p-8 backdrop-blur-sm shadow-sm"
        x-data="{ currentPeriod: '{{ $billingPeriod }}' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">انتخاب پکیج نرم‌افزار
                    CRM</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">پکیج متناسب با نیاز کسب‌وکار
                    خود را انتخاب کرده و ادامه دهید.</p>
            </div>
            @if($selectedPackageId)
                <button wire:click="togglePackageSelector" type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors shrink-0">
                    <span>بستن و ادامه با پکیج فعلی</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Dynamic Tabs --}}
        @if(!empty($availablePeriods))
            <div class="flex justify-center mb-8">
                <div role="tablist" aria-label="دوره‌های صورتحساب"
                     class="inline-flex items-center p-1.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm gap-1.5 overflow-x-auto max-w-full hide-scrollbar">
                    @foreach($availablePeriods as $pKey => $pData)
                        <button role="tab" :aria-selected="currentPeriod === '{{ $pKey }}' ? 'true' : 'false'"
                                @click.prevent="currentPeriod = '{{ $pKey }}'; $wire.set('billingPeriod', '{{ $pKey }}')"
                                :class="currentPeriod === '{{ $pKey }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'"
                                class="rounded-xl py-2 px-5 text-xs sm:text-sm font-bold whitespace-nowrap transition-all duration-200 cursor-pointer shrink-0"
                        >
                            {{ $pData['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Packages Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-stretch">
            @foreach($packages as $pkg)
                @php
                    $pkgModules = is_array($pkg->modules) ? $pkg->modules : [];
                    $limitsAll = is_array($pkg->limits ?? null) ? $pkg->limits : [];
                    $rawFeats = is_array($pkg->features) ? $pkg->features : [];
                    $pkgFeats = ResellerHelper::filterCommercialFeatures($rawFeats);
                    $isFree = $pkg->isFree();
                @endphp

                <div wire:click="selectPackage({{ $pkg->id }})"
                     class="group flex flex-col cursor-pointer transition-all rounded-3xl bg-white dark:bg-gray-900 border p-6 relative {{ $selectedPackageId === $pkg->id ? 'border-indigo-500 shadow-xl scale-[1.01] ring-2 ring-indigo-500/20 z-10' : 'border-gray-200 dark:border-gray-800 hover:shadow-lg hover:-translate-y-0.5 hover:border-indigo-300 dark:hover:border-indigo-700' }}">

                    @if($selectedPackageId === $pkg->id)
                        <div
                            class="absolute -top-2.5 -left-2.5 h-6 w-6 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-md">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    @endif

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
                                                {{ ResellerHelper::toPersianDigits($pkg->discount_value) }}
                                                ٪ تخفیف
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
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                   stroke-linejoin="round"
                                                                                   stroke-width="2"
                                                                                   d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></span>
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
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                   stroke-linejoin="round"
                                                                                   stroke-width="2"
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
                            <div class="text-xs font-bold text-gray-900 dark:text-white mb-3">امکانات
                                پکیج:
                            </div>
                            <ul class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                                @foreach($limitsAll as $lSlug => $lVal)
                                    @php
                                        $quotaTitle = ResellerHelper::getQuotaTitle($lSlug);
                                        $quotaUnit = ResellerHelper::getQuotaUnit($lSlug);
                                    @endphp
                                    <li class="flex items-start gap-2 text-xs">
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
                                                    {{ ResellerHelper::toPersianDigits($lVal) }} {{ $quotaUnit }}
                                                @endif
                                                            </span>
                                        </div>
                                    </li>
                                @endforeach

                                @foreach($pkgModules as $slug)
                                    <li class="flex items-start gap-2 text-xs">
                                                        <span
                                                            class="mt-0.5 w-4 h-4 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center shrink-0">
                                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor"><path stroke-linecap="round"
                                                                                             stroke-linejoin="round"
                                                                                             stroke-width="3"
                                                                                             d="M5 13l4 4L19 7"/></svg>
                                                        </span>
                                        <div
                                            class="flex-1 text-gray-700 dark:text-gray-300 font-medium border-b border-gray-50 dark:border-gray-800/50 pb-1.5 border-dashed">
                                            ماژول {{ ResellerHelper::getModuleTitle($slug) }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Pricing & CTA Box --}}
                        <div class="mt-auto pt-4 flex flex-col gap-4">
                            @if($isFree)
                                <div
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-white text-xs font-black shadow-md {{ $selectedPackageId === $pkg->id ? 'bg-emerald-500' : 'bg-emerald-600' }}">
                                    <span>{{ $selectedPackageId === $pkg->id ? 'انتخاب شده' : 'انتخاب پکیج' }}</span>
                                </div>
                                @if($pkg->getFinalSetupFeeAttribute() > 0)
                                    <div class="text-center text-xs font-bold text-amber-600 dark:text-amber-400">
                                        + {{ ResellerHelper::formatPrice($pkg->getFinalSetupFeeAttribute(), $currencyLabel) }}
                                        هزینه راه‌اندازی
                                    </div>
                                @endif
                            @else
                                @foreach($availablePeriods as $pKey => $pData)
                                    <div x-show="currentPeriod === '{{ $pKey }}'" x-cloak
                                         style="display: none;" class="flex flex-col gap-3">
                                        @if($pkg->isPeriodActive($pKey))
                                            @php
                                                $rawPriceForPeriod = $pkg->getPriceForMonths($pData['months']);
                                                $finalPriceForPeriod = $pkg->getFinalPriceForMonths($pData['months']);
                                                $hasPkgDiscount = $pkg->hasActiveDiscountForMonths($pData['months']) && $finalPriceForPeriod < $rawPriceForPeriod;
                                            @endphp
                                            <div
                                                class="px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                                <span class="text-xs text-gray-400 font-bold">پرداخت {{ $pData['label'] }}:</span>
                                                <div class="text-left">
                                                    @if($hasPkgDiscount)
                                                        <div
                                                            class="text-xs text-gray-400 line-through tabular-nums">{{ ResellerHelper::toPersianDigits(number_format($rawPriceForPeriod)) }}</div>
                                                        <div
                                                            class="text-sm font-black text-rose-600 dark:text-rose-400">
                                                            {{ ResellerHelper::toPersianDigits(number_format($finalPriceForPeriod)) }}
                                                            <span
                                                                class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-sm font-black text-indigo-600 dark:text-indigo-400">
                                                                            {{ ResellerHelper::toPersianDigits(number_format($rawPriceForPeriod)) }}
                                                                            <span
                                                                                class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div
                                                class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-white text-xs font-black shadow-md {{ $selectedPackageId === $pkg->id ? 'bg-indigo-500' : 'bg-indigo-600' }}">
                                                <span>{{ $selectedPackageId === $pkg->id ? 'انتخاب شده' : 'انتخاب پکیج' }}</span>
                                            </div>
                                        @else
                                            <div
                                                class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-black cursor-not-allowed">
                                                <span>فاقد دوره {{ $pData['label'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
