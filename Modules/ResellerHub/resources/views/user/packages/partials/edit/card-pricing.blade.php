@php
    use Modules\ResellerHub\App\Enums\PackagePriceType;
@endphp

<x-resellerhub::card class="p-6 sm:p-8 space-y-6">
    <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <span
                    class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۳</span>
        <div>
            <h2 class="text-base font-black text-gray-900 dark:text-white">{{ __('resellerhub::messages.packages.form.card_pricing_title') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ __('resellerhub::messages.packages.form.card_pricing_desc') }}
            </p>
        </div>
    </div>

    {{-- Price Type Selector (Subscription vs One-time) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <button type="button" @click="pricing.price_type = '{{ PackagePriceType::SUBSCRIPTION->value }}'"
                class="p-4 rounded-2xl border-2 transition-all text-right cursor-pointer"
                :class="pricing.price_type === '{{ PackagePriceType::SUBSCRIPTION->value }}' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
            <div class="flex items-center justify-between mb-2">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center"
                              :class="pricing.price_type === '{{ PackagePriceType::SUBSCRIPTION->value }}' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                <template x-if="pricing.price_type === '{{ PackagePriceType::SUBSCRIPTION->value }}'">
                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                </template>
            </div>
            <span
                class="block text-sm font-black text-gray-800 dark:text-gray-100">{{ __('resellerhub::messages.packages.form.price_type_subscription') }}</span>
            <span class="block text-xs text-gray-400 mt-1">پرداخت ماهانه، فصلی، شش‌ماهه یا سالانه</span>
        </button>

        <button type="button" @click="pricing.price_type = '{{ PackagePriceType::ONE_TIME->value }}'"
                class="p-4 rounded-2xl border-2 transition-all text-right cursor-pointer"
                :class="pricing.price_type === '{{ PackagePriceType::ONE_TIME->value }}' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
            <div class="flex items-center justify-between mb-2">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center"
                              :class="pricing.price_type === '{{ PackagePriceType::ONE_TIME->value }}' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 9V7a4 4 0 00-8 0v2M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </span>
                <template x-if="pricing.price_type === '{{ PackagePriceType::ONE_TIME->value }}'">
                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                </template>
            </div>
            <span
                class="block text-sm font-black text-gray-800 dark:text-gray-100">{{ __('resellerhub::messages.packages.form.price_type_onetime') }}</span>
            <span
                class="block text-xs text-gray-400 mt-1">{{ __('resellerhub::messages.packages.form.price_type_onetime_desc') }}</span>
        </button>
    </div>

    {{-- Auto-detected Free Package Banner --}}
    <div x-show="isFreePackage" x-cloak
         class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-3 text-emerald-800 dark:text-emerald-200 transition-all">
        <div
            class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
                        <span
                            class="text-xs font-black block">{{ __('resellerhub::messages.packages.form.free_banner_title') }}</span>
            <span
                class="text-xs opacity-80 block mt-0.5">{{ __('resellerhub::messages.packages.form.free_banner_desc') }}</span>
        </div>
    </div>

    {{-- Subscription: Dynamic Period Rows (Grid of Cards with Vertical Fields & Custom Month Support) --}}
    <template x-if="pricing.price_type === 'subscription'">
        <div class="space-y-4 pt-2">
            {{-- Top Actions Bar --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <label class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200 block">
                        {{ __('resellerhub::messages.packages.form.periods_label') }}
                    </label>
                    <p class="text-xs text-gray-400 mt-0.5">
                        با دکمه «افزودن بازه زمانی»، دوره‌های مورد نظر (متداول یا دلخواه مثل ۱۷ ماه) را
                        اضافه کرده و تعرفه آن را مشخص نمایید.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="autoCalcPackagePeriods()"
                            title="{{ __('resellerhub::messages.packages.form.autocalc_tooltip') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-bold transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>{{ __('resellerhub::messages.packages.form.autocalc_from_1m') }}</span>
                    </button>

                    <button type="button" @click="addPeriodRow()"
                            :disabled="periodRows.length >= 30"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black shadow-md shadow-indigo-500/20 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('resellerhub::messages.packages.form.add_period_btn') }}</span>
                    </button>
                </div>
            </div>

            {{-- Empty State --}}
            <template x-if="periodRows.length === 0">
                <div
                    class="p-8 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-center space-y-3">
                    <div
                        class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">هنوز هیچ بازه زمانی
                        اضافه نشده است</h4>
                    <p class="text-xs text-gray-400 max-w-sm mx-auto">برای فعال‌سازی پلن‌های خرید مشتری،
                        حداقل یک بازه زمانی اضافه کنید.</p>
                    <div class="flex items-center justify-center gap-2 pt-2">
                        <button type="button" @click="addPeriodRow()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>{{ __('resellerhub::messages.packages.form.add_period_btn') }}</span>
                        </button>
                        <button type="button" @click="selectCommonPeriods()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            {{ __('resellerhub::messages.packages.form.set_common_periods') }}
                        </button>
                    </div>
                </div>
            </template>

            {{-- Period Cards Grid (Multiple items per row, Vertical dropdown + price) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="(row, idx) in periodRows" :key="row.id">
                    <div
                        class="p-4 rounded-2xl border border-gray-200/90 dark:border-gray-700/80 bg-white dark:bg-gray-800/80 shadow-xs hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md transition-all flex flex-col justify-between relative group">

                        {{-- Card Top: Row Badge, Title & Delete Button --}}
                        <div
                            class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-gray-700/60 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                            <span
                                                class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center shrink-0"
                                                x-text="faNum(idx + 1)"></span>
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 truncate"
                                      x-text="getPeriodTitle(row.months)"></span>
                            </div>
                            <button type="button" @click="removePeriodRow(row.id)"
                                    title="{{ __('resellerhub::messages.packages.form.remove_period_tooltip') }}"
                                    class="p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 border border-transparent hover:border-red-200 dark:hover:border-red-900 transition cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Card Body: Vertical Stack (Period Selection + Price Input) --}}
                        <div class="space-y-3 flex-1 flex flex-col justify-between">
                            {{-- 1. Period Selector (Dropdown or Custom Month Input) --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label
                                        class="block text-xs font-bold text-gray-500 dark:text-gray-400">
                                        {{ __('resellerhub::messages.packages.form.period_time_label') }}
                                    </label>
                                    <button type="button" @click="toggleCustomMode(row)"
                                            class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition cursor-pointer flex items-center gap-0.5">
                                                    <span
                                                        x-text="row.isCustom ? '{{ __('resellerhub::messages.packages.form.choose_from_list') }}' : '{{ __('resellerhub::messages.packages.form.custom_months_manual') }}'"></span>
                                    </button>
                                </div>

                                {{-- Dropdown Mode (Preset periods) --}}
                                <div x-show="!row.isCustom" x-data="{
                                                open: false,
                                                getSelected() {
                                                    return allPeriodOptions.find(o => o.months === row.months) || { months: row.months, label: row.months + ' ماهه', badge: '', badgeClass: '' };
                                                },
                                                select(opt) {
                                                    if (opt.isCustomTrigger) {
                                                        row.isCustom = true;
                                                        this.open = false;
                                                        return;
                                                    }
                                                    if (isMonthTakenElsewhere(opt.months, row.id)) return;
                                                    row.months = opt.months;
                                                    handleRowMonthChange(row);
                                                    this.open = false;
                                                }
                                            }" class="relative" :class="{ 'z-50': open }">
                                    <button type="button" @click="open = !open"
                                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-xs dark:border-gray-700 dark:bg-gray-900/50 dark:text-white flex items-center justify-between cursor-pointer text-start select-none">
                                        <div class="flex items-center gap-1.5 truncate">
                                                        <span class="w-2 h-2 rounded-full shrink-0"
                                                              :class="getSelected().months === 12 ? 'bg-indigo-500' : (getSelected().months === 1 ? 'bg-blue-500' : 'bg-emerald-500')"></span>
                                            <span x-text="getSelected().label"
                                                  class="truncate font-bold text-gray-800 dark:text-gray-100 text-xs"></span>
                                            <span x-show="getSelected().badge"
                                                  class="text-[9px] px-1 py-0.2 rounded border shrink-0 font-medium"
                                                  :class="getSelected().badgeClass"
                                                  x-text="getSelected().badge"></span>
                                        </div>
                                        <svg
                                            class="w-3.5 h-3.5 text-gray-400 shrink-0 ms-1 transition-transform duration-200"
                                            :class="open ? 'rotate-180 text-indigo-600' : ''"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Floating Menu --}}
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                         class="absolute z-[100] top-full mt-1 start-0 w-full min-w-[200px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-1 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10 space-y-0.5">
                                        <template x-for="opt in allPeriodOptions" :key="opt.months">
                                            <div @click="select(opt)"
                                                 class="px-2.5 py-1.5 text-xs rounded-xl transition-colors flex items-center justify-between gap-1.5 select-none"
                                                 :class="isMonthTakenElsewhere(opt.months, row.id)
                                                                 ? 'opacity-40 cursor-not-allowed bg-gray-50/50 dark:bg-gray-800/40 text-gray-400'
                                                                 : (row.months === opt.months
                                                                     ? 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold'
                                                                     : 'text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer')">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-text="opt.label" class="truncate"></span>
                                                    <span x-show="isMonthTakenElsewhere(opt.months, row.id)"
                                                          class="text-[9px] text-gray-400 font-normal">(انتخاب شده)</span>
                                                </div>
                                                <div class="flex items-center gap-1 shrink-0">
                                                                <span
                                                                    x-show="opt.badge && !isMonthTakenElsewhere(opt.months, row.id)"
                                                                    class="text-[9px] px-1 py-0.2 rounded border font-medium"
                                                                    :class="opt.badgeClass"
                                                                    x-text="opt.badge"></span>
                                                    <svg x-show="row.months === opt.months"
                                                         class="w-3 h-3 text-indigo-600 dark:text-indigo-400 shrink-0"
                                                         fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </template>
                                        {{-- Special Trigger for Custom Month --}}
                                        <div @click="select({isCustomTrigger: true})"
                                             class="px-2.5 py-2 text-xs rounded-xl text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 font-black cursor-pointer border-t border-gray-100 dark:border-gray-700/60 mt-1 flex items-center justify-between">
                                            <span>{{ __('resellerhub::messages.packages.form.custom_months_trigger') }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Custom Month Input Mode --}}
                                <div x-show="row.isCustom"
                                     class="relative flex items-center rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50/20 dark:bg-indigo-950/20 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 transition overflow-hidden">
                                    <input type="number"
                                           min="1"
                                           max="1200"
                                           :value="row.months"
                                           @input="setRowCustomMonths(row, $event.target.value)"
                                           placeholder="مثلاً ۱۷"
                                           class="w-full text-xs font-bold py-2 px-2.5 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white"
                                           dir="ltr">
                                    <span
                                        class="px-2 py-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-100/50 dark:bg-indigo-900/50 border-r border-indigo-200 dark:border-indigo-800 select-none shrink-0">
                                                    {{ __('resellerhub::messages.packages.form.custom_months_badge') }}
                                                </span>
                                </div>
                            </div>

                            {{-- 2. Price Input Field (Vertically Below Period Selector) --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label
                                        class="block text-xs font-bold text-gray-500 dark:text-gray-400">
                                        {{ __('resellerhub::messages.packages.form.period_pricing_label') }}
                                    </label>
                                    <template
                                        x-if="getEffectivePeriodDiscount(row.months).has_discount && getPeriodPrice(row.months) > 0">
                                                    <span
                                                        class="text-xs text-emerald-600 dark:text-emerald-400 font-bold truncate">
                                                        {{ __('resellerhub::messages.packages.form.discounted_label') }} <span
                                                            x-text="formatPriceNumber(calcDiscountedPeriodPrice(row.months))"></span>
                                                        <span x-show="!getPeriodDiscount(row.months).has_custom"
                                                              class="text-[9px] text-gray-400 font-normal">{{ __('resellerhub::messages.packages.form.global_discount_badge') }}</span>
                                                    </span>
                                    </template>
                                </div>
                                <div
                                    class="relative flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 transition shadow-2xs">
                                    <input type="text"
                                           :value="formatPriceNumber(getPeriodPrice(row.months))"
                                           @input="setPeriodPrice(row.months, $event.target.value)"
                                           placeholder="۰"
                                           dir="ltr"
                                           class="w-full text-xs sm:text-sm font-black py-2 px-2.5 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                    <span
                                        class="px-2 py-2 text-xs font-bold text-gray-400 bg-gray-50 dark:bg-gray-900/60 border-r border-gray-200 dark:border-gray-700 select-none shrink-0">
                                                    {{ $currencyLabel }}
                                                </span>
                                </div>
                                {{-- Monthly equivalent helper --}}
                                <template x-if="row.months > 1 && getPeriodPrice(row.months) > 0">
                                    <div
                                        class="mt-1 text-xs text-gray-400 dark:text-gray-500 font-medium text-end">
                                        {{ __('resellerhub::messages.packages.form.monthly_equiv_label') }} <span
                                            class="font-bold text-gray-600 dark:text-gray-300"
                                            x-text="formatPriceNumber(Math.round(calcDiscountedPeriodPrice(row.months) / row.months)) + ' ' + '{{ $currencyLabel }}'"></span>
                                        <template
                                            x-if="getEffectivePeriodDiscount(row.months).has_discount">
                                            <span class="text-rose-500 text-[9px] ms-1 font-bold">(تخفیف‌دار)</span>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- 3. Dedicated Period-Specific Discount Card --}}
                            <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2.5">
                                {{-- Toggle Checkbox for Custom Period Discount --}}
                                <div class="flex items-center justify-between gap-2">
                                    <label
                                        class="inline-flex items-center gap-2 cursor-pointer select-none group">
                                        <input type="checkbox"
                                               x-model="getPeriodDiscount(row.months).has_custom"
                                               class="w-4 h-4 rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500/30 dark:bg-gray-700 cursor-pointer transition">
                                        <span
                                            class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                                                        {{ __('resellerhub::messages.packages.form.custom_period_discount_toggle') }}
                                                    </span>
                                    </label>

                                    {{-- Status indicator --}}
                                    <div>
                                        <template
                                            x-if="!getPeriodDiscount(row.months).has_custom && hasGlobalDiscount()">
                                                        <span
                                                            class="text-[9px] font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-800/60">
                                                            {{ __('resellerhub::messages.packages.form.follow_global_discount') }}
                                                        </span>
                                        </template>
                                        <template
                                            x-if="!getPeriodDiscount(row.months).has_custom && !hasGlobalDiscount()">
                                                        <span
                                                            class="text-[9px] text-gray-400 dark:text-gray-500 font-medium">
                                                            {{ __('resellerhub::messages.packages.form.no_discount_label') }}
                                                        </span>
                                        </template>
                                        <template
                                            x-if="getPeriodDiscount(row.months).has_custom && getPeriodDiscount(row.months).discount_value > 0">
                                                        <span
                                                            class="text-[9px] font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/60 px-1.5 py-0.5 rounded">
                                                            {{ __('resellerhub::messages.packages.form.profit_label') }} <span
                                                                x-text="formatPriceNumber(getPeriodDiscount(row.months).discount_value)"></span> {{ $currencyLabel }}
                                                        </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Collapsible custom discount input panel with smooth animation --}}
                                <div x-show="getPeriodDiscount(row.months).has_custom"
                                     x-cloak
                                     x-transition:enter="transition-all ease-out duration-300"
                                     x-transition:enter-start="opacity-0 max-h-0 -translate-y-2 scale-98"
                                     x-transition:enter-end="opacity-100 max-h-96 translate-y-0 scale-100"
                                     x-transition:leave="transition-all ease-in duration-200"
                                     x-transition:leave-start="opacity-100 max-h-96 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 scale-98"
                                     class="overflow-hidden space-y-2 pt-1">

                                    {{-- Discount Amount Input --}}
                                    <div class="space-y-1">
                                        <label
                                            class="block text-xs font-bold text-gray-600 dark:text-gray-300">
                                            {{ __('resellerhub::messages.packages.form.custom_discount_amount_label') }}
                                        </label>
                                        <div
                                            class="relative flex items-center rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/20 dark:bg-rose-950/20 overflow-hidden focus-within:border-rose-500 focus-within:ring-2 focus-within:ring-rose-500/20 transition shadow-2xs">
                                            <input type="text"
                                                   :value="formatPriceNumber(getPeriodDiscount(row.months).discount_value)"
                                                   @input="setPeriodDiscountValue(row.months, $event.target.value)"
                                                   placeholder="مبلغ تخفیف این دوره ({{ $currencyLabel }})"
                                                   dir="ltr"
                                                   class="w-full text-xs font-bold py-1.5 px-2.5 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                            <span
                                                class="px-2 py-1.5 text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-100/50 dark:bg-rose-950/50 border-r border-rose-200 dark:border-rose-800 select-none shrink-0">
                                                            {{ $currencyLabel }}
                                                        </span>
                                        </div>
                                    </div>

                                    {{-- Dates: Starts at (مبدا) & Expires at (مقصد) --}}
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-0.5">
                                                {{ __('resellerhub::messages.packages.form.start_origin_date') }}
                                            </label>
                                            <div
                                                class="relative flex items-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-rose-500 transition shadow-2xs">
                                                <input type="text"
                                                       data-jdp
                                                       @focus="if(window.jalaliDatepicker) window.jalaliDatepicker.startWatch({selector: '[data-jdp]'});"
                                                       x-model="getPeriodDiscount(row.months).discount_starts_at"
                                                       placeholder="۱۴۰۵/۰۱/۰۱"
                                                       dir="ltr"
                                                       class="w-full text-xs font-bold py-1 px-1.5 border-0 bg-transparent text-center tabular-nums focus:ring-0 text-gray-800 dark:text-gray-200">
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-0.5">
                                                {{ __('resellerhub::messages.packages.form.end_dest_date') }}
                                            </label>
                                            <div
                                                class="relative flex items-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-rose-500 transition shadow-2xs">
                                                <input type="text"
                                                       data-jdp
                                                       @focus="if(window.jalaliDatepicker) window.jalaliDatepicker.startWatch({selector: '[data-jdp]'});"
                                                       x-model="getPeriodDiscount(row.months).discount_expires_at"
                                                       placeholder="۱۴۰۵/۰۲/۰۱"
                                                       dir="ltr"
                                                       class="w-full text-xs font-bold py-1 px-1.5 border-0 bg-transparent text-center tabular-nums focus:ring-0 text-gray-800 dark:text-gray-200">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Dashed Add Card in Grid --}}
                <template x-if="periodRows.length > 0 && periodRows.length < 30">
                    <button type="button" @click="addPeriodRow()"
                            class="min-h-[170px] p-4 rounded-2xl border-2 border-dashed border-indigo-200 dark:border-indigo-900/60 hover:border-indigo-400 dark:hover:border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/10 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all group">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-100/80 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <span
                            class="text-xs font-black">{{ __('resellerhub::messages.packages.form.add_new_period_dashed') }}</span>
                        <span
                            class="text-xs text-gray-400">{{ __('resellerhub::messages.packages.form.add_new_period_dashed_hint') }}</span>
                    </button>
                </template>
            </div>
        </div>
    </template>

    {{-- One-time price --}}
    <template x-if="pricing.price_type === 'one_time'">
        <div class="pt-2">
            <div
                class="p-4 sm:p-5 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 ring-1 ring-indigo-500/20 flex flex-col md:flex-row md:items-center gap-4 md:gap-6">

                {{-- Title & description --}}
                <div class="flex items-start gap-3 md:w-1/2">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <label
                                class="block text-xs sm:text-sm font-black text-indigo-800 dark:text-indigo-200">تعرفه
                                خرید یک‌باره</label>
                            <span
                                class="px-2 py-0.5 rounded-lg bg-indigo-100/80 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 text-xs font-bold">{{ __('resellerhub::messages.packages.form.lifetime_badge') }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">مشتری با یک بار پرداخت، دسترسی همیشگی به
                            سامانه خواهد داشت.</p>
                    </div>
                </div>

                {{-- Price input --}}
                <div class="flex-1 space-y-1.5">
                    <div
                        class="relative flex items-center rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 transition shadow-2xs">
                        <input type="text"
                               inputmode="numeric"
                               :value="formatPriceNumber(pricing.one_time_price)"
                               @input="setPricingField('one_time_price', $event.target.value)"
                               placeholder="۰"
                               dir="ltr"
                               class="w-full text-base font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-indigo-700 dark:text-indigo-300">
                        <span
                            class="px-3 py-2 text-xs font-bold text-indigo-500/80 bg-indigo-50/50 dark:bg-indigo-950/60 border-r border-indigo-200 dark:border-indigo-800 select-none">
                                        {{ $currencyLabel }}
                                    </span>
                    </div>

                    {{-- Live discounted preview --}}
                    <template x-if="pricing.discount_value > 0 && pricing.one_time_price > 0">
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-black">
                            {{ __('resellerhub::messages.packages.form.after_discount') }} <span
                                x-text="formatPriceNumber(calcDiscountedPrice(pricing.one_time_price)) + ' {{ $currencyLabel }}'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- SECTION: Setup Fee & Discount side-by-side (Balanced Grid, Purely Amount-based Discount & Timed Discount) --}}
    <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span>{{ __('resellerhub::messages.packages.form.financial_settings_title') }}</span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ __('resellerhub::messages.packages.form.financial_settings_desc') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">
            {{-- CARD 1: Setup Fee (One-time) --}}
            <div
                class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/60 flex flex-col justify-between space-y-3 transition-all hover:border-amber-300">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-7 h-7 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span
                                class="text-xs sm:text-sm font-black text-amber-900 dark:text-amber-200">{{ __('resellerhub::messages.packages.form.setup_fee_title') }}</span>
                        </div>
                        <span
                            class="px-2 py-0.5 rounded-lg bg-amber-100/80 dark:bg-amber-900/80 text-amber-700 dark:text-amber-300 text-xs font-bold">{{ __('resellerhub::messages.packages.form.setup_fee_badge') }}</span>
                    </div>
                    <p class="text-xs text-amber-700/80 dark:text-amber-400/80 mt-1">
                        علاوه بر تعرفه دوره، این مبلغ فقط یک‌بار در فاکتور اول لحاظ شده و در تمدیدها
                        محاسبه نمی‌گردد.
                    </p>
                </div>

                <div
                    class="relative flex items-center rounded-xl border border-amber-200 dark:border-amber-800 bg-white dark:bg-gray-800 overflow-hidden focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20 transition shadow-2xs">
                    <input type="text"
                           :value="formatPriceNumber(pricing.setup_fee)"
                           @input="setPricingField('setup_fee', $event.target.value)"
                           placeholder="۰"
                           dir="ltr"
                           class="w-full text-base font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-amber-900 dark:text-amber-200">
                    <span
                        class="px-3 py-2 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-100/50 dark:bg-amber-950/60 border-r border-amber-200 dark:border-amber-800 select-none">
                                    {{ $currencyLabel }}
                                </span>
                </div>
            </div>

            {{-- CARD 2: Package Discount (Purely Amount-based + Jalali Expiration Date) --}}
            <div
                class="p-5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/60 flex flex-col justify-between space-y-4 transition-all hover:border-emerald-300">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <span
                                class="text-xs sm:text-sm font-black text-emerald-900 dark:text-emerald-200">{{ __('resellerhub::messages.packages.form.discount_title') }}</span>
                        </div>
                        <span
                            class="px-2 py-0.5 rounded-lg bg-emerald-100/80 dark:bg-emerald-900/80 text-emerald-700 dark:text-emerald-300 text-xs font-bold">{{ __('resellerhub::messages.packages.form.discount_badge') }}</span>
                    </div>
                    <p class="text-xs text-emerald-700/80 dark:text-emerald-400/80 mt-1">
                        {{ __('resellerhub::messages.packages.form.discount_desc') }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-emerald-900 dark:text-emerald-200 mb-1">
                            {{ __('resellerhub::messages.packages.form.discount_amount_label') }}
                        </label>
                        <div
                            class="relative flex items-center rounded-xl border border-emerald-200 dark:border-emerald-800 bg-white dark:bg-gray-800 overflow-hidden focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition shadow-2xs">
                            <input type="text"
                                   :value="formatPriceNumber(pricing.discount_value)"
                                   @input="setPricingField('discount_value', $event.target.value)"
                                   placeholder="۰"
                                   dir="ltr"
                                   class="w-full text-base font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-emerald-900 dark:text-emerald-200">
                            <span
                                class="px-3 py-2 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100/50 dark:bg-emerald-950/60 border-r border-emerald-200 dark:border-emerald-800 select-none">
                                            {{ $currencyLabel }}
                                        </span>
                        </div>
                    </div>

                    {{-- Optional Jalali Date Pickers for Timed Discount (Start & End in one row) --}}
                    <div class="pt-3 border-t border-emerald-200/60 dark:border-emerald-800/50 space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Start Date --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="block text-xs font-bold text-emerald-900 dark:text-emerald-200">
                                        {{ __('resellerhub::messages.packages.form.discount_start_label') }}
                                    </label>
                                    <button type="button" x-show="pricing.discount_starts_at"
                                            @click="pricing.discount_starts_at = ''"
                                            class="text-xs text-red-500 hover:text-red-700 cursor-pointer">
                                        {{ __('resellerhub::messages.packages.form.clear_date') }}
                                    </button>
                                </div>
                                <div
                                    class="relative flex items-center rounded-xl border border-emerald-200 dark:border-emerald-800 bg-white dark:bg-gray-800 overflow-hidden focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition shadow-2xs">
                                                <span class="px-2.5 text-emerald-600 dark:text-emerald-400 select-none">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </span>
                                    <input type="text"
                                           name="discount_starts_at"
                                           data-jdp
                                           data-jdp-only-date
                                           x-model="pricing.discount_starts_at"
                                           placeholder="مثال: ۱۴۰۵/۰۲/۱۵"
                                           autocomplete="off"
                                           class="w-full text-xs font-bold py-2 px-2 border-0 bg-transparent text-right focus:ring-0 text-emerald-950 dark:text-emerald-200 placeholder-gray-400">
                                </div>
                            </div>

                            {{-- End Date --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="block text-xs font-bold text-emerald-900 dark:text-emerald-200">
                                        {{ __('resellerhub::messages.packages.form.discount_end_label') }}
                                    </label>
                                    <button type="button" x-show="pricing.discount_expires_at"
                                            @click="pricing.discount_expires_at = ''"
                                            class="text-xs text-red-500 hover:text-red-700 cursor-pointer">
                                        {{ __('resellerhub::messages.packages.form.clear_date') }}
                                    </button>
                                </div>
                                <div
                                    class="relative flex items-center rounded-xl border border-emerald-200 dark:border-emerald-800 bg-white dark:bg-gray-800 overflow-hidden focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition shadow-2xs">
                                                <span class="px-2.5 text-emerald-600 dark:text-emerald-400 select-none">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </span>
                                    <input type="text"
                                           name="discount_expires_at"
                                           data-jdp
                                           data-jdp-only-date
                                           x-model="pricing.discount_expires_at"
                                           placeholder="مثال: ۱۴۰۵/۰۲/۱۵"
                                           autocomplete="off"
                                           class="w-full text-xs font-bold py-2 px-2 border-0 bg-transparent text-right focus:ring-0 text-emerald-950 dark:text-emerald-200 placeholder-gray-400">
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400">
                            در صورت تعیین تاریخ، پس از سپری شدن این موعد، تخفیف پکیج به صورت خودکار غیرفعال
                            خواهد شد.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-resellerhub::card>
