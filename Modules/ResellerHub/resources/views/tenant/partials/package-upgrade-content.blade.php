@php
    use Illuminate\Support\Collection;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;

    /** @var Collection $availableUpgradePackages */
    /** @var array $availableGateways */
    /** @var string|null $defaultGateway */
    /** @var array $advanceRenewalLimit */
    /** @var Collection|null $pendingUpgradeInvoices */

    $currentPackage = $package ?? null;
    $hasUnpaidInvoices = !empty($pendingUpgradeInvoices) && $pendingUpgradeInvoices->isNotEmpty();
    $firstPendingInvoice = $hasUnpaidInvoices ? $pendingUpgradeInvoices->first() : null;

    $initialPackageId = null;
    $upgradePkg = $availableUpgradePackages->firstWhere('is_current', false);
    if ($upgradePkg) {
        $initialPackageId = $upgradePkg->id;
    } elseif ($availableUpgradePackages->isNotEmpty()) {
        $initialPackageId = $availableUpgradePackages->first()->id;
    }

    $defaultSelectedMonths = 12;
    if ($availableUpgradePackages->isNotEmpty()) {
        $firstPkgPeriods = $availableUpgradePackages->first()->available_periods ?? [];
        $has12 = collect($firstPkgPeriods)->contains('months', 12);
        $defaultSelectedMonths = $has12 ? 12 : ($firstPkgPeriods[0]['months'] ?? 1);
    }
@endphp

@includeIf('resellerhub::user.packages.partials.icons-script')

<div x-data="packageUpgradeManager()" class="space-y-4">
    @if($hasUnpaidInvoices && $firstPendingInvoice)
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50/70 dark:bg-amber-950/30 px-4 py-3.5 shadow-xs">
            <div class="flex items-start gap-3 min-w-0">
                <span
                    class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-xs font-bold text-gray-900 dark:text-gray-100">
                            فاکتور شماره {{ reseller_fa_num($firstPendingInvoice->invoice_number) }} در انتظار پرداخت
                            است
                        </p>
                        @if($pendingUpgradeInvoices->count() > 1)
                            <span
                                class="px-2 py-0.2 rounded-md bg-amber-200/60 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 text-[10px] font-bold">
                                و {{ reseller_fa_num($pendingUpgradeInvoices->count() - 1) }} فاکتور دیگر
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">
                        مبلغ {{ reseller_fa_num(number_format($firstPendingInvoice->total)) }} {{ $currencyLabel }} — با
                        انتخاب پکیج جدید، سفارش شما با شرایط جدید جایگزین و ثبت می‌شود.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <a href="{{ route('portal.invoices.pay', ['invoice' => $firstPendingInvoice->id, 'gateway' => $defaultGateway]) }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-all shadow-xs">
                    پرداخت سریع فاکتور
                </a>
                <button type="button" @click="activeTab = 'billing_history'"
                        class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 text-xs font-semibold transition-colors cursor-pointer">
                    سوابق مالی
                </button>
            </div>
        </div>
    @endif

    {{--(Unified Header & Period Selector) --}}
    <div
        class="rounded-2xl border border-gray-200/80 dark:border-gray-700/70 bg-white dark:bg-gray-900 p-3.5 sm:p-4 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3.5">
            <div class="flex items-center gap-3 min-w-0">
                <span
                    class="w-2.5 h-2.5 rounded-full {{ $isExpired ? 'bg-rose-500 animate-pulse' : (($daysRemaining > 0 && $daysRemaining <= 7) ? 'bg-amber-500 animate-ping' : 'bg-emerald-500') }} shrink-0"></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">خرید و ارتقای اشتراک</h3>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/50">
                            پکیج جاری: {{ $packageName }}
                        </span>
                        @if($hasActivePlan && $daysRemaining > 0)
                            <span
                                class="text-[11px] font-bold {{ $daysRemaining <= 7 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                ({{ reseller_fa_num($daysRemaining) }} روز مانده)
                            </span>
                        @elseif($isExpired)
                            <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400">(منقضی شده)</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">پکیج و دوره مدنظر را مشخص کنید؛
                        پرداخت و فعال‌سازی در همین صفحه انجام می‌پذیرد.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full lg:w-auto shrink-0" x-show="availableDurations.length > 0"
                 x-data="{ otherDurationsOpen: false }">
                <div class="flex items-center p-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 gap-1 w-full sm:w-auto">
                    <template x-for="duration in primaryDurations" :key="duration.months">
                        <button type="button"
                                @click="selectedMonths = duration.months; otherDurationsOpen = false"
                                :class="selectedMonths === duration.months
                                    ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                                class="relative flex-1 sm:flex-initial px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer min-h-[40px] select-none">
                            <span class="sm:hidden" x-text="duration.short"></span>
                            <span class="hidden sm:inline" x-text="duration.label"></span>

                            <template x-if="getDurationBadge(duration)">
                                <span
                                    class="absolute -top-2 left-1/2 -translate-x-1/2 sm:static sm:translate-x-0 px-1.5 py-0.2 rounded-full text-[9px] font-black tracking-tight shadow-xs whitespace-nowrap"
                                    :class="selectedMonths === duration.months
                                          ? 'bg-indigo-600 text-white'
                                          : 'bg-emerald-600 text-white dark:bg-emerald-500'"
                                    x-text="getDurationBadge(duration)">
                                </span>
                            </template>
                        </button>
                    </template>

                    <template x-if="secondaryDurations.length > 0">
                        <div class="relative flex-1 sm:flex-initial" @click.outside="otherDurationsOpen = false">
                            <button type="button"
                                    @click="otherDurationsOpen = !otherDurationsOpen"
                                    :class="selectedSecondaryDuration
                                        ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm font-black'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                                    class="w-full relative px-2.5 sm:px-3.5 py-2 sm:py-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1 cursor-pointer min-h-[40px] select-none">
                                <span class="truncate"
                                      x-text="selectedSecondaryDuration ? selectedSecondaryDuration.short : 'سایر دوره‌ها'"></span>

                                <template
                                    x-if="selectedSecondaryDuration && getDurationBadge(selectedSecondaryDuration)">
                                    <span
                                        class="hidden md:inline px-1 py-0.2 rounded-full text-[8.5px] font-black bg-indigo-600 text-white shadow-xs whitespace-nowrap"
                                        x-text="getDurationBadge(selectedSecondaryDuration)"></span>
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
                                 class="absolute end-0 top-full mt-1.5 w-48 sm:w-52 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/90 dark:border-gray-700 shadow-lg p-1 z-30 space-y-0.5">
                                <div
                                    class="px-2.5 py-1.5 text-[10.5px] font-bold text-gray-400 border-b border-gray-100 dark:border-gray-700/60 mb-1">
                                    سایر دوره‌های زمانی:
                                </div>
                                <template x-for="duration in secondaryDurations" :key="duration.months">
                                    <button type="button"
                                            @click="selectedMonths = duration.months; otherDurationsOpen = false"
                                            :class="selectedMonths === duration.months
                                                ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-750 font-medium'"
                                            class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-xs transition-colors cursor-pointer text-right">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span x-text="duration.label"></span>
                                            <template x-if="getDurationBadge(duration)">
                                                <span
                                                    class="px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300"
                                                    x-text="getDurationBadge(duration)"></span>
                                            </template>
                                        </div>
                                        <template x-if="selectedMonths === duration.months">
                                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        <div class="lg:col-span-8">
            @if($availableUpgradePackages->isEmpty())
                <div
                    class="rounded-2xl border border-gray-200/80 dark:border-gray-700/70 bg-white dark:bg-gray-900 p-12 text-center shadow-xs">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto mb-3 shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">پکیجی برای انتخاب یا ارتقا یافت نشد</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 max-w-sm mx-auto leading-relaxed">
                        در حال حاضر از بالاترین پکیج ممکن استفاده می‌کنید یا پکیج دیگری برای این سازمان تعریف نشده است.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
                    @foreach($availableUpgradePackages as $pkg)
                        @php
                            $pkgModules = is_array($pkg->modules) ? $pkg->modules : [];
                            $limitsAll = is_array($pkg->limits ?? null) ? $pkg->limits : [];
                            $rawFeats = is_array($pkg->features) ? $pkg->features : [];
                            $pkgFeats = ResellerHelper::filterCommercialFeatures($rawFeats);

                            $keyHighlights = [];
                            foreach ($limitsAll as $lSlug => $lVal) {
                                $quotaTitle = ResellerHelper::getQuotaTitle($lSlug);
                                $quotaUnit = ResellerHelper::getQuotaUnit($lSlug);
                                $valText = ($lVal == -1 || $lVal === 0) ? 'نامحدود' : ResellerHelper::toPersianDigits($lVal) . ($quotaUnit ? ' ' . $quotaUnit : '');
                                $keyHighlights[] = ['type' => 'quota', 'text' => $quotaTitle . ': ' . $valText];
                            }
                            foreach ($pkgModules as $mSlug) {
                                $keyHighlights[] = ['type' => 'module', 'text' => 'ماژول ' . ResellerHelper::getModuleTitle($mSlug)];
                            }
                            foreach ($pkgFeats as $fKey) {
                                $keyHighlights[] = ['type' => 'feature', 'text' => ResellerHelper::getFeatureTitle($fKey)];
                            }
                        @endphp

                        <div @click="selectedPackageId = {{ $pkg->id }}"
                             :class="{
                                 'border-indigo-600 dark:border-indigo-500 ring-2 ring-indigo-600/20 dark:ring-indigo-500/20 shadow-lg shadow-indigo-500/10 -translate-y-1 bg-indigo-50/15 dark:bg-indigo-950/20': selectedPackageId === {{ $pkg->id }},
                                 'border-gray-200/90 dark:border-gray-700/70 hover:border-gray-300 dark:hover:border-gray-600 hover:-translate-y-0.5 hover:shadow-md': selectedPackageId !== {{ $pkg->id }} && ({{ $pkg->is_current ? ($pkg->renewal_allowed ? 'true' : 'false') : 'true' }}),
                                 'opacity-60 bg-gray-50/70 dark:bg-gray-800/40 cursor-not-allowed': {{ $pkg->is_current && !$pkg->renewal_allowed ? 'true' : 'false' }}
                             }"
                             class="group relative flex flex-col rounded-2xl border bg-white dark:bg-gray-900 p-5 transition-all duration-200 cursor-pointer">

                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center"
                                         style="{{ $pkg->light_bg_style }}"
                                         x-html="window.renderIndustryIcon ? window.renderIndustryIcon('{{ $pkg->icon }}', 'w-5 h-5 text-current') : ''"></div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                                title="{{ $pkg->name }}">{{ $pkg->name }}</h3>

                                            @if($pkg->is_current)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60">
                                                    پکیج فعلی
                                                </span>
                                            @elseif($pkg->hasActiveDiscount())
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/70 dark:border-rose-800/60">
                                                    @if($pkg->discount_type === 'percentage')
                                                        {{ ResellerHelper::toPersianDigits($pkg->discount_value) }}٪
                                                        تخفیف
                                                    @else
                                                        {{ ResellerHelper::toPersianDigits(number_format($pkg->discount_value)) }} {{ $currencyLabel }}
                                                        تخفیف
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-[11.5px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 leading-relaxed min-h-[34px]">
                                            {{ $pkg->description ?: 'پکیج نرم‌افزاری با قابلیت‌های سازمانی و امکان ارتقای آنی' }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200 shrink-0 mt-0.5"
                                    :class="selectedPackageId === {{ $pkg->id }}
                                         ? 'border-indigo-600 bg-indigo-600 text-white'
                                         : 'border-gray-300 dark:border-gray-600 bg-transparent'">
                                    <svg x-show="selectedPackageId === {{ $pkg->id }}" class="w-3 h-3" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>

                            @if($hasActivePlan && !$pkg->is_current && (!empty($pkg->new_modules) || !empty($pkg->quota_diffs)))
                                <div
                                    class="mt-3.5 p-2.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100/90 dark:border-indigo-800/60">
                                    <div
                                        class="text-[10.5px] font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1 mb-1.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        <span>مزایای ارتقا نسبت به پکیج فعلی:</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($pkg->new_modules ?? [], 0, 3) as $nm)
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-700/60 shadow-xs">
                                                + ماژول {{ $nm['title'] ?? $nm['slug'] }}
                                            </span>
                                        @endforeach
                                        @foreach(array_slice($pkg->quota_diffs ?? [], 0, 3) as $qd)
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-700/60 shadow-xs">
                                                + {{ $qd['title'] }}: {{ $qd['is_unlimited'] ? 'نامحدود' : ResellerHelper::toPersianDigits($qd['to']) }} {{ $qd['unit'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div
                                class="mt-4 rounded-2xl bg-gray-50/90 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800/80 p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                        پرداخت <span
                                            x-text="getPackagePeriodLabel(packages.find(p => p.id == {{ $pkg->id }}))"></span>
                                    </span>
                                    <template x-if="hasPackageDiscount(packages.find(p => p.id == {{ $pkg->id }}))">
                                        <div class="flex items-center gap-1 text-xs">
                                            <span class="text-gray-400 dark:text-gray-500 text-[11px]">قیمت اصلی:</span>
                                            <span
                                                class="text-gray-400 dark:text-gray-500 line-through decoration-rose-500 font-bold tabular-nums"
                                                x-text="formatFaNumber(getPackageRawPrice(packages.find(p => p.id == {{ $pkg->id }})))"></span>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-baseline gap-1.5">
                                        <span
                                            class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight transition-all duration-200"
                                            x-text="formatFaNumber(getPackageFinalPrice(packages.find(p => p.id == {{ $pkg->id }})))"></span>
                                        <span
                                            class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                    </div>

                                    <template x-if="hasPackageDiscount(packages.find(p => p.id == {{ $pkg->id }}))">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/60 shadow-xs">
                                            با تخفیف ویژه
                                        </span>
                                    </template>
                                    <template
                                        x-if="!hasPackageDiscount(packages.find(p => p.id == {{ $pkg->id }})) && selectedMonths === 12 && getAnnualSavingsPercent(packages.find(p => p.id == {{ $pkg->id }})) > 0">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60 shadow-xs">
                                            <span
                                                x-text="formatFaNumber(getAnnualSavingsPercent(packages.find(p => p.id == {{ $pkg->id }}))) + '٪ صرفه‌جویی'"></span>
                                        </span>
                                    </template>
                                </div>
                                <div
                                    class="px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between shadow-xs text-xs">
                                    <span
                                        class="text-gray-600 dark:text-gray-300 font-medium flex items-center gap-1.5 text-[11.5px]">
                                        <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400 shrink-0"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        معادل روزانه:
                                    </span>
                                    <div class="flex items-center gap-1.5 font-bold">
                                        <span class="text-indigo-600 dark:text-indigo-400 tabular-nums text-xs">
                                            <span
                                                x-text="formatFaNumber(getPackageDailyEquivalent(packages.find(p => p.id == {{ $pkg->id }})))"></span>
                                            {{ $currencyLabel }}
                                        </span>
                                        <template x-if="selectedMonths > 1">
                                            <span
                                                class="hidden sm:inline text-[10px] text-gray-400 dark:text-gray-500 font-normal">
                                                (ماهانه: <strong class="text-gray-700 dark:text-gray-300 font-semibold"
                                                                 x-text="formatFaNumber(Math.round(getPackageFinalPrice(packages.find(p => p.id == {{ $pkg->id }})) / selectedMonths))"></strong>)
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                @if($pkg->setup_fee > 0)
                                    <div
                                        class="pt-2 border-t border-gray-200/60 dark:border-gray-700/50 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                        <span>هزینه راه‌اندازی (یک‌بار):</span>
                                        <span class="font-bold text-gray-700 dark:text-gray-200 tabular-nums">
                                            {{ ResellerHelper::toPersianDigits(number_format($pkg->setup_fee)) }} {{ $currencyLabel }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-4 flex-1" x-data="{ expandedFeatures: false }">
                                <ul class="space-y-1.5">
                                    @foreach($keyHighlights as $idx => $kh)
                                        <li x-show="expandedFeatures || {{ $idx }} < 5"
                                            class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-300">
                                            @if($kh['type'] === 'quota')
                                                <svg
                                                    class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400 shrink-0 mt-0.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                            @elseif($kh['type'] === 'module')
                                                <svg
                                                    class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400 shrink-0 mt-0.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                            <span class="leading-relaxed">{{ $kh['text'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if(count($keyHighlights) > 5)
                                    <button type="button" @click.stop="expandedFeatures = !expandedFeatures"
                                            class="mt-2 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer flex items-center gap-1">
                                        <span
                                            x-text="expandedFeatures ? 'بستن لیست' : '+ مشاهده {{ count($keyHighlights) - 5 }} قابلیت دیگر'"></span>
                                        <svg class="w-3 h-3 transition-transform duration-200"
                                             :class="expandedFeatures ? 'rotate-180' : ''" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            @if($pkg->is_current && !$pkg->renewal_allowed)
                                <div
                                    class="mt-3 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/60 flex items-start gap-2 text-[11px] text-amber-800 dark:text-amber-300 font-medium leading-relaxed">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    <span>{{ $pkg->renewal_message ?: 'سقف تمدید پیش از موعد این پکیج تکمیل شده است.' }}</span>
                                </div>
                            @endif

                            <button type="button"
                                    @if($pkg->is_current && !$pkg->renewal_allowed) disabled @endif
                                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl text-xs font-bold transition-all"
                                    :class="{
                                        @if($pkg->is_current && !$pkg->renewal_allowed)
                                            'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700': true
                                        @else
                                            'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 active:scale-[0.99]': selectedPackageId === {{ $pkg->id }},
                                            'bg-transparent text-indigo-600 dark:text-indigo-400 border border-gray-200 dark:border-gray-700 group-hover:border-indigo-300 dark:group-hover:border-indigo-700': selectedPackageId !== {{ $pkg->id }}
                                        @endif
                                    }">
                                @if($pkg->is_current && !$pkg->renewal_allowed)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                @else
                                    <svg x-show="selectedPackageId === {{ $pkg->id }}" class="w-3.5 h-3.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                                @if($pkg->is_current)
                                    <span
                                        x-text="selectedPackageId === {{ $pkg->id }} ? 'تمدید پکیج فعلی انتخاب شده' : 'تمدید همین پکیج'"></span>
                                @else
                                    <span
                                        x-text="selectedPackageId === {{ $pkg->id }} ? 'انتخاب شده' : 'انتخاب این پکیج'"></span>
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-6" id="order-summary-box">
            <div
                class="rounded-2xl border border-gray-200/80 dark:border-gray-700/70 bg-white dark:bg-gray-900 p-5 shadow-xs">

                <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-7 h-7 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex items-center justify-center shrink-0 border border-gray-200/60 dark:border-gray-700/60 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <h4 class="text-sm font-black text-gray-900 dark:text-white">خلاصه سفارش</h4>
                    </div>
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/60 dark:border-emerald-800/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        فعال‌سازی آنی
                    </span>
                </div>

                <template x-if="selectedPackage">
                    <div class="py-3.5 space-y-3 text-[11.5px]">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">پکیج انتخابی</span>
                            <span class="font-bold text-gray-900 dark:text-white truncate"
                                  x-text="selectedPackage.name"></span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">نوع سفارش</span>
                            <span class="font-bold"
                                  :class="selectedPackage.is_current ? 'text-amber-600 dark:text-amber-400' : 'text-indigo-600 dark:text-indigo-400'"
                                  x-text="selectedPackage.is_current ? 'تمدید اشتراک' : 'ارتقای پکیج'"></span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400">دوره انتخابی</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200" x-text="currentPeriodLabel"></span>
                        </div>

                        @if($hasActivePlan && $daysRemaining > 0)
                            <div
                                class="rounded-xl bg-gray-50/80 dark:bg-gray-800/50 border border-gray-200/60 dark:border-gray-700/60 p-3 space-y-2">
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span
                                        class="text-gray-600 dark:text-gray-300 font-medium flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        اعتبار پس از فعال‌سازی
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-black text-xs tabular-nums border border-indigo-100 dark:border-indigo-800/50 shadow-2xs"
                                        x-text="getProjectedValidityText()"></span>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 text-[11px] text-emerald-700 dark:text-emerald-400 font-medium pt-2 border-t border-gray-200/50 dark:border-gray-700/40">
                                    <span
                                        class="w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                             stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span>
                                        <strong class="font-black text-emerald-800 dark:text-emerald-300"
                                                x-text="formatFaNumber(daysRemaining) + ' روز باقی‌مانده'"></strong> از پکیج فعلی حفظ و اضافه می‌شود.
                                    </span>
                                </div>
                            </div>
                        @endif

                        <template x-if="hasDiscount">
                            <div class="flex items-center justify-between gap-2 text-rose-600 dark:text-rose-400">
                                <span>تخفیف دوره</span>
                                <span class="font-bold tabular-nums"
                                      x-text="'- ' + formatFaNumber(discountAmount) + ' {{ $currencyLabel }}'"></span>
                            </div>
                        </template>

                        <template x-if="selectedMonths === 12 && getAnnualSavingsPercent(selectedPackage) > 0">
                            <div class="flex items-center justify-between gap-2 text-emerald-600 dark:text-emerald-400">
                                <span>صرفه‌جویی اشتراک سالانه</span>
                                <span class="font-bold tabular-nums"
                                      x-text="formatFaNumber(getAnnualSavingsPercent(selectedPackage)) + '٪'"></span>
                            </div>
                        </template>

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div
                                class="p-3 rounded-xl bg-gray-50/90 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800/80 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200 text-xs">مبلغ قابل پرداخت</span>
                                </div>
                                <div class="flex items-baseline gap-1 text-left">
                                    <span
                                        class="text-lg font-black text-gray-900 dark:text-white tabular-nums tracking-tight"
                                        x-text="formatFaNumber(finalPrice)"></span>
                                    <span
                                        class="text-[11px] font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="pt-3.5 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-200">درگاه پرداخت آنلاین</label>

                    @if(empty($availableGateways))
                        <div
                            class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                            درگاه پرداخت آنلاینی تعریف نشده است. جهت فعال‌سازی لطفاً با پشتیبانی در ارتباط باشید.
                        </div>
                    @elseif(count($availableGateways) === 1)
                        @php
                            $singleKey = array_key_first($availableGateways);
                            $singleG = $availableGateways[$singleKey];
                        @endphp
                        <div
                            class="p-3 rounded-xl border border-indigo-200/80 dark:border-indigo-800/60 bg-indigo-50/40 dark:bg-indigo-950/20 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <div>
                                    <div
                                        class="text-xs font-bold text-gray-900 dark:text-white">{{ $singleG['title'] ?? $singleG['name'] ?? 'درگاه پرداخت شاپرک' }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">درگاه فعال و مورد
                                        تأیید شبکه بانکی
                                    </div>
                                </div>
                            </div>
                            <span
                                class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-900">
                                پیش‌فرض
                            </span>
                        </div>
                    @else
                        <div class="space-y-1.5">
                            @foreach($availableGateways as $gKey => $gData)
                                <label
                                    class="flex items-center gap-3 px-3.5 py-3 min-h-[48px] rounded-xl border cursor-pointer transition-colors"
                                    :class="selectedGateway === '{{ $gKey }}'
                                           ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 ring-1 ring-indigo-500/20'
                                           : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                                    <input type="radio" name="payment_gateway" value="{{ $gKey }}"
                                           x-model="selectedGateway"
                                           class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800">
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $gData['title'] ?? $gData['name'] ?? $gKey }}</div>
                                        <div
                                            class="text-[10.5px] text-gray-400 dark:text-gray-500 truncate">{{ $gData['description'] ?? 'کلیه کارت‌های عضو شبکه شتاب' }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div x-data="{ showNotes: false }" class="pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="showNotes = !showNotes"
                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer flex items-center gap-1">
                        <span
                            x-text="showNotes ? '- بستن توضیحات' : '+ افزودن یادداشت / توضیحات به سفارش (اختیاری)'"></span>
                    </button>
                    <div x-show="showNotes" x-cloak class="mt-2">
                        <textarea x-model="notes" rows="2" placeholder="توضیحات اختیاری درباره این فاکتور..."
                                  class="w-full text-xs p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div x-show="errorMessage" x-cloak
                     class="mt-3 px-3 py-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/60 text-xs text-rose-700 dark:text-rose-300 font-medium">
                    <p class="leading-relaxed" x-text="errorMessage"></p>
                </div>

                <template x-if="selectedPackage && selectedPackage.is_current && !selectedPackage.renewal_allowed">
                    <div
                        class="mt-3 px-3 py-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 font-medium">
                        <p class="leading-relaxed"
                           x-text="selectedPackage.renewal_message || 'به دلیل محدودیت تنظیم‌شده توسط ارائه‌دهنده، در حال حاضر امکان تمدید بیش از این سقف وجود ندارد.'"></p>
                    </div>
                </template>

                <button type="button"
                        @click="submitOrder()"
                        :disabled="loading || (selectedPackage && selectedPackage.is_current && !selectedPackage.renewal_allowed)"
                        class="mt-4 w-full h-12 py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs sm:text-sm transition-all shadow-md shadow-indigo-600/25 active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>

                    <span
                        x-text="loading ? 'در حال اتصال به درگاه...' : ('پرداخت ' + formatFaNumber(finalPrice) + ' {{ $currencyLabel }} و ' + (selectedPackage && selectedPackage.is_current ? 'تمدید' : 'فعال‌سازی'))"></span>
                </button>

                <div
                    class="mt-3 flex items-center justify-center gap-1.5 text-[10.5px] text-gray-500 dark:text-gray-400">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>پرداخت امن و مستقیم شاپرک · تحویل بلادرنگ اشتراک</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ۴. نوار دسترسی سریع پرداخت در موبایل (جلوگیری از اسکرول زیاد، درون کانتینر) --}}
    <div x-show="selectedPackage && !summaryInView"
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="lg:hidden sticky bottom-3 z-30 rounded-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border border-gray-200/80 dark:border-gray-700/70 p-3 sm:p-3.5 shadow-lg shadow-gray-200/40 dark:shadow-black/20">

        {{-- اعلان خطا در صورت وجود --}}
        <div x-show="errorMessage" x-cloak
             class="mb-2 p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center justify-between gap-2 shadow-xs">
            <span class="truncate" x-text="errorMessage"></span>
            <button type="button" @click="errorMessage = null"
                    class="p-1 rounded-md text-rose-500 hover:text-rose-700 dark:hover:text-rose-200 cursor-pointer"
                    aria-label="بستن">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex items-center justify-between gap-3">
            {{-- مشخصات پکیج و قیمت نهایی + لینک دسترسی سریع به فاکتور --}}
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="text-xs font-bold text-gray-900 dark:text-white truncate"
                          x-text="selectedPackage?.name"></span>
                    <span class="text-gray-300 dark:text-gray-600">·</span>
                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400"
                          x-text="currentPeriodLabel"></span>
                    <template x-if="hasDiscount">
                        <span
                            class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-black bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/60">
                            تخفیف
                        </span>
                    </template>
                </div>

                <div class="flex items-center gap-2 mt-0.5">
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-base sm:text-lg font-black text-gray-900 dark:text-white tabular-nums tracking-tight"
                            x-text="formatFaNumber(finalPrice)"></span>
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                    </div>

                    <span class="text-gray-300 dark:text-gray-600">·</span>

                    <button type="button"
                            @click="document.querySelector('#order-summary-box')?.scrollIntoView({ behavior: 'smooth' })"
                            class="text-[10.5px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline cursor-pointer inline-flex items-center gap-0.5">
                        <span>جزئیات فاکتور و درگاه</span>
                        <svg class="w-3 h-3 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- دکمه پرداخت سریع --}}
            <button type="button"
                    @click="submitOrder()"
                    :disabled="loading || (selectedPackage && selectedPackage.is_current && !selectedPackage.renewal_allowed)"
                    class="h-11 px-4 sm:px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-600/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                <template x-if="loading">
                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <svg x-show="!loading" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span
                    x-text="loading ? 'اتصال...' : (selectedPackage && selectedPackage.is_current ? 'تمدید سریع' : 'پرداخت سریع')"></span>
            </button>
        </div>
    </div>
</div>

<script>
    function packageUpgradeManager() {
        return {
            packages: @json($availableUpgradePackages),
            gateways: @json($availableGateways),
            selectedPackageId: {{ $initialPackageId ? (int)$initialPackageId : 'null' }},
            selectedMonths: {{ (int)$defaultSelectedMonths }},
            selectedGateway: '{{ $defaultGateway ?: (array_key_first($availableGateways) ?? '') }}',
            daysRemaining: {{ (int)($daysRemaining ?? 0) }},
            notes: '',
            loading: false,
            errorMessage: null,
            summaryInView: false,

            init() {
                if (this.availableDurations.length > 0 && !this.availableDurations.some(d => d.months === this.selectedMonths)) {
                    this.selectedMonths = (this.availableDurations.find(d => d.months === 12) || this.availableDurations[0]).months;
                }

                this.$nextTick(() => {
                    const el = document.querySelector('#order-summary-box');
                    if (el && 'IntersectionObserver' in window) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                this.summaryInView = entry.isIntersecting;
                            });
                        }, {
                            threshold: 0.05,
                            rootMargin: '0px 0px -20px 0px'
                        });
                        observer.observe(el);
                    }
                });
            },

            get availableDurations() {
                const monthsMap = new Map();
                (this.packages || []).forEach(pkg => {
                    (pkg.available_periods || []).forEach(per => {
                        const m = Number(per.months);
                        if (!monthsMap.has(m)) {
                            monthsMap.set(m, {
                                months: m,
                                label: per.label,
                                short: per.short || per.label,
                            });
                        }
                    });
                });

                return Array.from(monthsMap.values()).sort((a, b) => a.months - b.months);
            },

            get primaryDurations() {
                const all = this.availableDurations;
                const has1 = all.some(d => d.months === 1);
                const has12 = all.some(d => d.months === 12);
                if (has1 || has12) {
                    return all.filter(d => d.months === 1 || d.months === 12);
                }
                return all.slice(0, 2);
            },

            get secondaryDurations() {
                const primaryMonths = this.primaryDurations.map(d => d.months);
                return this.availableDurations.filter(d => !primaryMonths.includes(d.months));
            },

            get selectedSecondaryDuration() {
                return this.secondaryDurations.find(d => d.months === this.selectedMonths) || null;
            },

            getDurationBadge(duration) {
                if (!duration) return null;
                const m = duration.months;
                if (this.selectedPackage) {
                    const period = (this.selectedPackage.available_periods || []).find(p => p.months === m);
                    if (period && period.has_discount) {
                        return 'تخفیف ویژه';
                    }
                    if (m === 12 && this.getAnnualSavingsPercent(this.selectedPackage) > 0) {
                        return 'بیشترین صرفه';
                    }
                } else {
                    const anyDiscount = (this.packages || []).some(pkg => {
                        const p = (pkg.available_periods || []).find(x => x.months === m);
                        return p && p.has_discount;
                    });
                    if (anyDiscount) return 'تخفیف ویژه';
                    if (m === 12) return 'بیشترین صرفه';
                }
                return null;
            },

            get selectedPackage() {
                return this.packages.find(p => p.id == this.selectedPackageId) || null;
            },

            getPackagePeriod(pkg) {
                if (!pkg || !pkg.available_periods || pkg.available_periods.length === 0) return null;
                const match = pkg.available_periods.find(p => p.months == this.selectedMonths);
                return match || pkg.available_periods[0];
            },

            getPackagePeriodLabel(pkg) {
                const per = this.getPackagePeriod(pkg);
                return per ? per.label : (this.selectedMonths + ' ماهه');
            },

            get currentPeriod() {
                if (!this.selectedPackage) return null;
                return this.getPackagePeriod(this.selectedPackage);
            },

            get currentPeriodLabel() {
                return this.currentPeriod ? this.currentPeriod.label : (this.selectedMonths + ' ماهه');
            },

            get finalPrice() {
                return this.currentPeriod ? this.currentPeriod.final_price : 0;
            },

            get rawPrice() {
                return this.currentPeriod ? this.currentPeriod.raw_price : 0;
            },

            get hasDiscount() {
                return this.currentPeriod ? (this.currentPeriod.has_discount || this.rawPrice > this.finalPrice) : false;
            },

            get discountAmount() {
                return Math.max(0, this.rawPrice - this.finalPrice);
            },

            getPackageFinalPrice(pkg) {
                const per = this.getPackagePeriod(pkg);
                return per ? per.final_price : 0;
            },

            getPackageRawPrice(pkg) {
                const per = this.getPackagePeriod(pkg);
                return per ? per.raw_price : 0;
            },

            getPackageDailyEquivalent(pkg) {
                const per = this.getPackagePeriod(pkg);
                return per ? (per.daily_equivalent || 0) : 0;
            },

            hasPackageDiscount(pkg) {
                const raw = this.getPackageRawPrice(pkg);
                const fin = this.getPackageFinalPrice(pkg);
                return raw > fin;
            },

            getAnnualSavingsPercent(pkg) {
                if (!pkg) return 0;
                return pkg.annual_savings_percent || 0;
            },

            getProjectedValidityText() {
                const durationDays = this.currentPeriod ? (this.currentPeriod.days || (this.selectedMonths * 30)) : (this.selectedMonths * 30);
                const totalDays = Math.max(0, this.daysRemaining) + durationDays;
                const months = Math.floor(totalDays / 30);
                const remainingDays = totalDays % 30;

                if (months > 0 && remainingDays > 0) {
                    return this.formatFaNumber(months) + ' ماه و ' + this.formatFaNumber(remainingDays) + ' روز';
                }
                if (months > 0) {
                    return this.formatFaNumber(months) + ' ماه';
                }
                return this.formatFaNumber(totalDays) + ' روز';
            },

            formatFaNumber(num) {
                if (num === null || num === undefined) return '۰';
                return new Intl.NumberFormat('fa-IR').format(num);
            },

            async submitOrder() {
                if (!this.selectedPackageId) {
                    this.errorMessage = 'لطفاً یک پکیج را جهت پرداخت انتخاب فرمایید.';
                    return;
                }

                if (this.selectedPackage && this.selectedPackage.is_current && !this.selectedPackage.renewal_allowed) {
                    this.errorMessage = this.selectedPackage.renewal_message || 'امکان تمدید این پکیج در حال حاضر وجود ندارد.';
                    return;
                }

                this.loading = true;
                this.errorMessage = null;

                try {
                    const response = await fetch('{{ route('tenant.subscription.request') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            package_id: this.selectedPackageId,
                            duration_months: this.selectedMonths,
                            gateway: this.selectedGateway,
                            message: this.notes
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        if (data.pay_url) {
                            window.location.href = data.pay_url;
                            return;
                        }
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                    } else {
                        if (data.has_pending_invoice && data.pay_url) {
                            window.location.href = data.pay_url;
                            return;
                        }
                        this.errorMessage = data.message || 'خطا در ثبت سفارش ارتقا/تمدید پکیج.';
                    }
                } catch (e) {
                    this.errorMessage = 'خطای ارتباط با سرور. لطفاً اتصال اینترنت خود را بررسی و مجدداً تلاش فرمایید.';
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
