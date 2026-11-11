@if ($showPackageModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             wire:click="closePackageModal"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-200/80 dark:border-gray-700">
                <div
                    class="p-6 bg-gray-50/80 dark:bg-gray-850 border-b border-gray-200/80 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <span
                            class="flex items-center justify-center w-11 h-11 rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/25 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">
                                انتخاب و ارتقای پکیج سامانه CRM
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                پلن متناسب با نیاز خود را برگزینید. بلافاصله پس از فعال‌سازی، منوهای سایدبار در
                                دسترس خواهند بود.
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closePackageModal"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-6">
                    @if ($errors->has('package'))
                        <div
                            class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold rounded-xl">
                            {{ $errors->first('package') }}
                        </div>
                    @endif
                    <div class="flex justify-center overflow-x-auto max-w-full pb-1">
                        <div
                            class="inline-flex p-1 bg-gray-100 dark:bg-gray-900/60 rounded-2xl border border-gray-200/80 dark:border-gray-700 gap-1 overflow-x-auto max-w-full hide-scrollbar">
                            @foreach($availablePeriods ?? [] as $pKey => $pData)
                                <button
                                    type="button"
                                    wire:click="$set('selectedPeriod', '{{ $pKey }}')"
                                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap shrink-0 {{ $selectedPeriod === $pKey ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                >
                                    {{ $pData['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse ($packages as $pkg)
                            @php
                                $isCurrentPkg = ($tenant->package_id === $pkg->id);
                                $months = ResellerPackage::resolveMonthFromPeriodKey($selectedPeriod ?? 'yearly_price');
                                $rawPrice = $pkg->isFree() ? 0 : $pkg->getPriceForMonths($months);
                                $price = $pkg->isFree() ? 0 : $pkg->getFinalPriceForMonths($months);
                                $hasDiscount = !$pkg->isFree() && ($pkg->isSubscription() ? $pkg->hasActiveDiscountForMonths($months) : $pkg->hasActiveDiscount()) && $price < $rawPrice;
                                $modules = [];
                                if (is_array($pkg->modules)) {
                                    $modules = $pkg->modules;
                                } elseif (!empty($pkg->modules)) {
                                    $decodedMod = json_decode((string)$pkg->modules, true);
                                    $modules = is_array($decodedMod) ? $decodedMod : [];
                                }
                            @endphp
                            <div wire:key="package-card-{{ $pkg->id }}" class="relative rounded-2xl p-5 border transition-all flex flex-col justify-between
                                @if ($isCurrentPkg)
                                    bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-700 ring-2 ring-emerald-500/20
                                @else
                                    bg-white dark:bg-gray-800/90 border-gray-200 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-800
                                @endif
                            ">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <h4 class="text-base font-black text-gray-900 dark:text-white">{{ $pkg->name }}</h4>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @if ($isCurrentPkg)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                        پلن فعال شما
                                                    </span>
                                            @elseif ($pkg->isFree())
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                                        رایگان
                                                    </span>
                                            @endif
                                            @if ($hasDiscount)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                                        @if($pkg->discount_type === 'percentage')
                                                        {{ reseller_fa_num($pkg->discount_value) }}٪ تخفیف
                                                    @else
                                                        {{ reseller_price($pkg->discount_value, 'تومان') }}
                                                        تخفیف
                                                    @endif
                                                    </span>
                                            @endif
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ $pkg->description ?: 'دسترسی کامل به ماژول‌ها و ظرفیت مشخص' }}
                                    </p>

                                    {{-- Price Display --}}
                                    <div class="py-3 border-y border-gray-100 dark:border-gray-700/60">
                                        @if ($pkg->isFree())
                                            <span
                                                class="text-xl font-black text-emerald-600 dark:text-emerald-400">رایگان</span>
                                            <span class="text-xs text-gray-500 ms-1">/ بدون هزینه</span>
                                        @else
                                            @if ($hasDiscount)
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="text-xs text-gray-400 line-through tabular-nums">{{ reseller_price($rawPrice, 'تومان') }}</span>
                                                    <span
                                                        class="text-xs font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded-md tabular-nums">
                                                            {{ reseller_price($rawPrice - $price, '') }}-
                                                        </span>
                                                </div>
                                            @endif
                                            <div class="flex items-baseline gap-1">
                                                    <span
                                                        class="text-2xl font-black {{ $hasDiscount ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ reseller_fa_num(number_format($price)) }}</span>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">تومان</span>
                                            </div>
                                            <div
                                                class="flex items-center justify-between text-xs text-gray-400 mt-0.5">
                                                <span>برای مدت {{ reseller_duration_label($months) }}</span>
                                                @if ($hasDiscount && $pkg->discount_expires_at)
                                                    <span
                                                        class="text-amber-600 dark:text-amber-400 font-bold text-xs">تا {{ $pkg->discount_expires_at_jalali }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Module features pill list --}}
                                    <div class="space-y-1.5 pt-1">
                                        <span
                                            class="text-xs font-bold text-gray-400 block">ماژول‌های فعال:</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse (array_slice($modules, 0, 5) as $modKey)
                                                @php
                                                    $mSlug = is_array($modKey) ? ($modKey['slug'] ?? $modKey['name'] ?? '') : (string)$modKey;
                                                    $mTitle = \Modules\ResellerHub\App\Helpers\ResellerHelper::getModuleTitle($mSlug);
                                                @endphp
                                                <span
                                                    class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
                                                        {{ $mTitle }}
                                                    </span>
                                            @empty
                                                <span class="text-xs text-gray-400">تمام امکانات پایه</span>
                                            @endforelse
                                            @if(count($modules) > 5)
                                                <span
                                                    class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-bold">
                                                        +{{ reseller_fa_num(count($modules) - 5) }} ماژول دیگر
                                                    </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/60">
                                    <button
                                        type="button"
                                        wire:click="selectPackage({{ $pkg->id }})"
                                        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold transition-all cursor-pointer
                                            @if ($isCurrentPkg)
                                                bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                                            @elseif ($pkg->isFree())
                                                bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/25 active:scale-95
                                            @else
                                                bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 active:scale-95
                                            @endif
                                        "
                                    >
                                        @php
                                            $packageBtnLabel = 'انتخاب و پرداخت آنلاین';
                                            if ($isCurrentPkg) {
                                                $packageBtnLabel = 'انتخاب مجدد';
                                            } elseif ($pkg->isFree()) {
                                                $packageBtnLabel = 'فعال‌سازی پلن رایگان';
                                            }
                                        @endphp
                                        <span>{{ $packageBtnLabel }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8 text-gray-500">
                                پکیجی تعریف نشده است.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div
                    class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <button
                        type="button"
                        wire:click="closePackageModal"
                        class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                    >
                        بستن
                    </button>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        پرداخت از طریق درگاه مستقیم بانکی و بلافاصله پس از تسویه فعال‌سازی انجام می‌پذیرد.
                    </span>
                </div>

            </div>
        </div>
    </div>
@endif
