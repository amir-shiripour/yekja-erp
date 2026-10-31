@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerPackage;

    $isFreeAllowed = $isRegistrationWithoutPaymentAllowed ?? $this->isRegistrationWithoutPaymentAllowed();
    $isPackageFree = $isFreeAllowed;
    $currentMonths = ResellerPackage::resolveMonthFromPeriodKey($billingPeriod);
    $rawPackagePrice = $isPackageFree ? 0 : $selectedPackage->getPriceForMonths($currentMonths);
    $packagePrice = $isPackageFree ? 0 : $selectedPackage->getFinalPriceForMonths($currentMonths);
    $packageSetupFee = $isPackageFree ? 0 : $selectedPackage->getFinalSetupFeeAttribute();
    $hasPkgDiscount = !$isPackageFree && ($selectedPackage->isSubscription() ? $selectedPackage->hasActiveDiscountForMonths($currentMonths) : $selectedPackage->hasActiveDiscount()) && $packagePrice < $rawPackagePrice;
    $wlFee = $this->getWhiteLabelFee($currentMonths);
    $rawTotal = $isPackageFree ? 0 : ($packagePrice + $packageSetupFee + $wlFee['total']);
    $totalPrice = $rawTotal > 0 ? ResellerHelper::roundPrice($rawTotal) : 0;
    $pkgPeriods = $packagePeriods ?? ($selectedPackage && method_exists($selectedPackage, 'getPackagePeriods') ? $selectedPackage->getPackagePeriods() : ($availablePeriods ?? []));
@endphp
<div
    class="mt-4 mx-auto w-full max-w-5xl sm:max-w-6xl rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-xl shadow-gray-200/50 dark:border-gray-800 dark:bg-gray-900">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
        <div
            class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">بررسی نهایی فاکتور و
                ایجاد سامانه</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">اطلاعات سفارش خود را بررسی و شیوه پرداخت را
                مشخص نمایید.</p>
        </div>
    </div>

    {{-- Responsive 2-Column Grid: Order Details & Period on Right, Gateways & Summary on Left --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Right Column: Order Details & Period Selector --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Summary Items Box --}}
            <div
                class="p-4 sm:p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 space-y-3.5 text-xs sm:text-sm">
                <div
                    class="flex justify-between items-center pb-3 border-b border-gray-200/70 dark:border-gray-700/60">
                                <span
                                    class="text-gray-500 dark:text-gray-400">{{ __('resellerhub::messages.domain_branding.direct_login_url') }}</span>
                    <span class="font-black text-indigo-600 dark:text-indigo-400 dir-ltr select-all"
                          dir="ltr">
                                    {{ $urlScheme }}{{ $baseDomain }}/{{ $subdomain }}
                                </span>
                </div>

                @if($domainType === 'custom_domain' && !empty($customDomain))
                    <div
                        class="flex justify-between items-center pb-3 border-b border-gray-200/70 dark:border-gray-700/60">
                                    <span
                                        class="text-gray-500 dark:text-gray-400">{{ __('resellerhub::messages.domain_branding.custom_domain_label') }}</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 dir-ltr select-all"
                              dir="ltr">
                                        {{ $urlScheme }}{{ $customDomain }}
                                    </span>
                    </div>
                @endif

                <div
                    class="flex justify-between items-center pb-3 border-b border-gray-200/70 dark:border-gray-700/60">
                    <span class="text-gray-500 dark:text-gray-400">برندینگ و تم سامانه:</span>
                    <div class="flex items-center gap-2">
                        @if($whiteLabelType === 'custom')
                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs"
                                  style="background-color: {{ $brandPrimaryColor }}"></span>
                            <span class="font-bold text-purple-600 dark:text-purple-400">
                                            وایت‌لیبل اختصاصی ({{ $brandTitle ?: 'شخصی‌سازی شده' }})
                                        </span>
                        @else
                            <span class="font-bold text-gray-700 dark:text-gray-300">
                                            برندینگ پیش‌فرض سامانه
                                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400">پکیج انتخابی:</span>
                    <span
                        class="font-black text-gray-900 dark:text-white">{{ $selectedPackage->name }}</span>
                </div>
            </div>

            {{-- Billing Period Selector --}}
            @if(!$isPackageFree && !empty($pkgPeriods))
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">
                            دوره تمدید و اشتراک را انتخاب کنید:
                        </label>
                        @if(count($pkgPeriods) > 4)
                            <span
                                class="text-xs text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-full border border-indigo-200/50 dark:border-indigo-800/50">
                                            {{ ResellerHelper::toPersianDigits(count($pkgPeriods)) }} دوره در دسترس
                                        </span>
                        @endif
                    </div>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 {{ count($pkgPeriods) > 4 ? 'lg:grid-cols-3' : '' }} gap-3 {{ count($pkgPeriods) > 6 ? 'max-h-[380px] overflow-y-auto pr-1' : '' }} select-none">
                        @foreach($pkgPeriods as $pKey => $pData)
                            @php
                                $periodMonths = (int)($pData['months'] ?? ResellerPackage::resolveMonthFromPeriodKey($pKey));
                                $rawPkgPriceForPeriod = (int)($pData['raw_price'] ?? $selectedPackage->getPriceForMonths($periodMonths));
                                $finalPkgPriceForPeriod = (int)($pData['final_price'] ?? $selectedPackage->getFinalPriceForMonths($periodMonths));
                                $hasPeriodDiscount = !empty($pData['has_discount']) || ($selectedPackage->hasActiveDiscountForMonths($periodMonths) && $finalPkgPriceForPeriod < $rawPkgPriceForPeriod);
                                $wlForPeriod = $this->getWhiteLabelFee($periodMonths);
                                $periodSetupFee = $selectedPackage->getFinalSetupFeeAttribute();
                                $periodCombined = ResellerHelper::roundPrice($finalPkgPriceForPeriod + $periodSetupFee + $wlForPeriod['total']);
                                $periodDiscountPercent = (int)($pData['discount_percent'] ?? ($rawPkgPriceForPeriod > 0 ? round((($rawPkgPriceForPeriod - $finalPkgPriceForPeriod) / $rawPkgPriceForPeriod) * 100) : 0));
                            @endphp
                            <label
                                class="relative flex cursor-pointer rounded-2xl border bg-white p-3.5 shadow-xs focus:outline-none dark:bg-gray-800 {{ $billingPeriod === $pKey ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/20 dark:bg-indigo-950/20 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300' }} transition-all">
                                <input type="radio" wire:model.live="billingPeriod" value="{{ $pKey }}"
                                       class="sr-only">
                                <div class="flex flex-col text-center w-full">
                                                <span
                                                    class="text-xs font-bold text-gray-900 dark:text-white">{{ $pData['label'] ?? ResellerHelper::durationLabel($periodMonths, true) }}</span>
                                    @if($hasPeriodDiscount)
                                        <div class="flex items-center justify-center gap-1.5 mt-0.5">
                                                        <span
                                                            class="text-[11px] text-gray-400 line-through tabular-nums">
                                                            {{ ResellerHelper::formatPrice($rawPkgPriceForPeriod + $wlForPeriod['total'], $currencyLabel) }}
                                                        </span>
                                            @if($periodDiscountPercent > 0)
                                                <span
                                                    class="px-1.5 py-0.2 rounded-md text-[10px] font-black bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                                                                {{ ResellerHelper::toPersianDigits($periodDiscountPercent) }}٪ تخفیف
                                                            </span>
                                            @endif
                                        </div>
                                        <span
                                            class="text-xs font-black text-rose-600 dark:text-rose-400 mt-0.5">
                                                        {{ ResellerHelper::formatPrice($periodCombined, $currencyLabel) }}
                                                    </span>
                                    @else
                                        <span
                                            class="mt-1 text-xs font-black text-indigo-600 dark:text-indigo-400">
                                                        {{ ResellerHelper::formatPrice($periodCombined, $currencyLabel) }}
                                                    </span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Left Column: Gateways, Total Summary, Action CTA Buttons (Sticky on Desktop) --}}
        <div class="lg:col-span-5 sticky top-6 self-start space-y-5">
            {{-- Gateways Selection --}}
            @if(!$isPackageFree)
                <div class="space-y-2.5">
                    <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">
                        درگاه پرداخت آنلاین:
                    </label>
                    @if(!empty($availableGateways))
                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach($availableGateways as $gKey => $gData)
                                <label
                                    class="relative flex cursor-pointer items-center justify-between rounded-2xl border bg-white p-3 shadow-xs focus:outline-none dark:bg-gray-800 {{ $selectedGateway === $gKey ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/20 dark:bg-indigo-950/20 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300' }} transition-all">
                                    <input type="radio" wire:model.live="selectedGateway"
                                           value="{{ $gKey }}"
                                           class="sr-only">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 font-bold text-xs {{ $selectedGateway === $gKey ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col text-right">
                                                        <span
                                                            class="text-xs font-bold text-gray-900 dark:text-white">{{ $gData['name'] }}</span>
                                            <span
                                                class="text-xs text-gray-400 dark:text-gray-500">{{ $gData['description'] }}</span>
                                        </div>
                                    </div>
                                    <div
                                        class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 {{ $selectedGateway === $gKey ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-600' }}">
                                        @if($selectedGateway === $gKey)
                                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300 space-y-1">
                            <div class="flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>درگاه پرداخت آنلاینی در دسترس نیست</span>
                            </div>
                            <p class="text-[11px] leading-relaxed text-amber-700 dark:text-amber-400">
                                در حال حاضر هیچ درگاه پرداخت آنلاینی با اطلاعات احراز هویت معتبر در ماژول
                                تنظیمات فعال نیست. لطفاً با مدیر سیستم تماس بگیرید.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Total Calculation Box --}}
            <div
                class="rounded-2xl bg-gray-50 p-5 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 space-y-2.5">
                @if($hasPkgDiscount)
                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                        <span>تعرفه مصوب پکیج:</span>
                        <span class="line-through text-gray-400 tabular-nums">
                                        {{ ResellerHelper::formatPrice($rawPackagePrice, $currencyLabel) }}
                                    </span>
                    </div>
                    <div
                        class="flex justify-between items-center text-xs text-rose-600 dark:text-rose-400 font-bold">
                        <span>تخفیف ویژه پکیج:</span>
                        <span>
                                        {{ ResellerHelper::toPersianDigits(number_format($rawPackagePrice - $packagePrice)) }}- {{ $currencyLabel }}
                                    </span>
                    </div>
                @endif
                @if($packageSetupFee > 0)
                    <div
                        class="flex justify-between items-center text-xs text-amber-600 dark:text-amber-400">
                        <span>هزینه راه‌اندازی اولیه پکیج:</span>
                        <span class="font-bold">
                                        {{ ResellerHelper::formatPrice($packageSetupFee, $currencyLabel) }}
                                    </span>
                    </div>
                @endif
                @if($whiteLabelType === 'custom' && ($wlFee['total'] ?? 0) > 0)
                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                        <span>هزینه نهایی پکیج نرم‌افزاری:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">
                                        {{ $isPackageFree ? 'رایگان' : ResellerHelper::formatPrice($packagePrice, $currencyLabel) }}
                                    </span>
                    </div>
                    <div
                        class="flex justify-between items-center text-xs text-purple-600 dark:text-purple-400">
                        <span>لایسنس وایت‌لیبل اختصاصی:</span>
                        <span class="font-bold">
                                        {{ ResellerHelper::formatPrice($wlFee['total'], $currencyLabel) }}
                                    </span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-2"></div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">مبلغ کل قابل پرداخت:</span>
                    <span class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400">
                                    @if($isPackageFree && ($totalPrice <= 0))
                            رایگان
                        @else
                            {{ ResellerHelper::formatPrice($totalPrice, $currencyLabel) }}
                        @endif
                                </span>
                </div>
            </div>

            @error('payment')
            <div
                class="rounded-xl bg-red-50 p-3 text-xs font-medium text-red-600 border border-red-100">{{ $message }}</div>
            @enderror

            {{-- Action Buttons --}}
            <div class="flex flex-col gap-3 pt-1">
                @if(!$isPackageFree && $totalPrice > 0)
                    @if(!empty($availableGateways))
                        <button wire:click="pay" wire:loading.attr="disabled"
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-xs sm:text-sm font-black text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-500 transition-all active:scale-[0.99] disabled:opacity-70 cursor-pointer">
                            <span wire:loading.remove wire:target="pay">پرداخت امن با {{ ResellerHelper::getGatewayName($selectedGateway) }} و راه‌اندازی سامانه</span>
                            <span wire:loading.flex wire:target="pay" class="items-center gap-2">
                                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle
                                                    class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle><path class="opacity-75"
                                                                                    fill="currentColor"
                                                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            در حال اتصال به درگاه پرداخت...
                                        </span>
                        </button>
                    @else
                        <button disabled
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gray-200 dark:bg-gray-800 py-3.5 text-xs sm:text-sm font-bold text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            امکان پرداخت آنلاین وجود ندارد (درگاه فعال نیست)
                        </button>
                    @endif
                @else
                    <button wire:click="registerFree" wire:loading.attr="disabled"
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-xs sm:text-sm font-black text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-500 transition-all active:scale-[0.99] disabled:opacity-70 cursor-pointer">
                                    <span wire:loading.remove
                                          wire:target="registerFree, payLater">ایجاد و راه‌اندازی رایگان سامانه</span>
                        <span wire:loading.flex wire:target="registerFree, payLater"
                              class="justify-center items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle
                                                class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle><path class="opacity-75" fill="currentColor"
                                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        در حال ساخت سامانه شما...
                                    </span>
                    </button>
                @endif

                <button wire:click="$set('step', 1)"
                        type="button"
                        class="text-xs font-medium text-gray-500 hover:text-gray-700 mt-1 transition-colors dark:text-gray-400 dark:hover:text-gray-200 text-center cursor-pointer">
                    انصراف و بازگشت به مرحله قبل
                </button>
            </div>
        </div>
    </div>
</div>
