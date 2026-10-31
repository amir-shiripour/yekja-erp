<div x-show="openUpgradeModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
             @click="!upgradeSubmitting && (openUpgradeModal = false)"></div>

        {{-- Modal Card --}}
        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-right"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div
                        class="p-3 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl shadow-md shadow-amber-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">ارتقای پکیج به سطح
                            بالاتر</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">محاسبه مابه‌التفاوت و صدور
                            فاکتور جدید برای سفارش {{ reseller_fa_num($order->order_number) }}</p>
                    </div>
                </div>
                <button type="button"
                        @click="!upgradeSubmitting && (openUpgradeModal = false)"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Current Subscription Summary Banner --}}
            <div
                class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/60 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-2">
                    <span class="text-xs font-bold text-gray-400 block mb-1">پکیج فعلی</span>
                    <span
                        class="text-xs font-black text-gray-800 dark:text-gray-100">{{ $package?->name ?? 'طرح قبلی' }}</span>
                </div>
                <div class="p-2">
                    <span class="text-xs font-bold text-gray-400 block mb-1">دوره اشتراک</span>
                    <span
                        class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}</span>
                </div>
                <div class="p-2">
                    <span class="text-xs font-bold text-gray-400 block mb-1">مبلغ پرداختی پایه</span>
                    <span
                        class="text-xs font-black text-gray-800 dark:text-gray-100 tabular-nums">{{ reseller_price($order->total_amount, $currencyLabel) }}</span>
                </div>
                <div class="p-2">
                    <span class="text-xs font-bold text-gray-400 block mb-1">زمان باقیمانده</span>
                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                                @if($remainingDaysCount > 0)
                            {{ reseller_fa_num($remainingDaysCount) }} روز
                        @else
                            پایان‌یافته
                        @endif
                            </span>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('reseller.orders.upgrade-package', $order->id) }}"
                  @submit="upgradeSubmitting = true" class="space-y-6">
                @csrf
                <input type="hidden" name="new_package_id" :value="selectedUpgradePackageId">
                <input type="hidden" name="calculation_mode" :value="upgradeCalcMode">
                <input type="hidden" name="custom_price"
                       :value="upgradeCalcMode === 'custom' ? finalUpgradeAmount : ''">
                <input type="hidden" name="issue_invoice" :value="upgradeIssueInvoice ? '1' : '0'">

                {{-- Step 1: Package Selector --}}
                <div>
                    <label class="block text-xs font-black text-gray-700 dark:text-gray-300 mb-2">
                        ۱. انتخاب پکیج جدید جهت ارتقا <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto pr-1">
                        <template x-for="pkg in packagesData" :key="pkg.id">
                            <div @click="canSelectPackage(pkg) && selectUpgradePackage(pkg.id)"
                                 class="p-4 rounded-2xl border transition-all text-right flex flex-col justify-between"
                                 :class="[
                                             !canSelectPackage(pkg) ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-800/40 border-gray-200 dark:border-gray-800' :
                                             selectedUpgradePackageId == pkg.id ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 ring-2 ring-amber-500/30 cursor-pointer' :
                                             'border-gray-200 dark:border-gray-700/70 hover:border-amber-300 bg-white dark:bg-gray-800/60 cursor-pointer'
                                         ]">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full"
                                                      :style="`background-color: ${pkg.color || '#6366f1'}`"></span>
                                        <span class="font-black text-sm text-gray-900 dark:text-white"
                                              x-text="pkg.name"></span>
                                    </div>
                                    <template x-if="pkg.id == currentPackageId">
                                                <span
                                                    class="text-xs font-bold text-gray-500 bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded-full">فعلی</span>
                                    </template>
                                    <template x-if="pkg.id != currentPackageId && isPackageCheaper(pkg)">
                                                <span
                                                    class="text-xs font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/60 dark:border-rose-800/50 px-2 py-0.5 rounded-full">تعرفه کمتر</span>
                                    </template>
                                    <template
                                        x-if="pkg.id != currentPackageId && !isPackageCheaper(pkg) && selectedUpgradePackageId == pkg.id">
                                                <span
                                                    class="text-xs font-bold text-amber-600 bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 rounded-full">انتخاب‌شده</span>
                                    </template>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2"
                                     x-text="toFa(pkg.modules_count) + ' ماژول پیش‌فرض'"></div>
                                <div
                                    class="flex items-center justify-between text-xs pt-2 border-t border-gray-100 dark:border-gray-700/50">
                                    <span class="text-gray-400 font-bold">قیمت دوره:</span>
                                    <span class="font-black text-gray-900 dark:text-white tabular-nums dir-ltr"
                                          x-text="toFa(formatNumber(pkg.price_for_order_cycle)) + ' {{ $currencyLabel }}'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Step 2: Calculation Mode --}}
                <div x-show="selectedUpgradePackageId" class="space-y-3">
                    <label class="block text-xs font-black text-gray-700 dark:text-gray-300">
                        ۲. شیوه محاسبه مابه‌التفاوت
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label
                            class="flex items-center gap-2.5 p-3 rounded-xl border text-xs font-bold cursor-pointer transition"
                            :class="upgradeCalcMode === 'prorated' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/20 text-amber-900 dark:text-amber-200 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" value="prorated" x-model="upgradeCalcMode" class="sr-only">
                            <span
                                class="w-3.5 h-3.5 rounded-full border border-gray-400 flex items-center justify-center"
                                :class="upgradeCalcMode === 'prorated' ? 'border-amber-600 bg-amber-600' : ''"></span>
                            <span>تسهیم به نسبت (روزهای باقیمانده)</span>
                        </label>

                        <label
                            class="flex items-center gap-2.5 p-3 rounded-xl border text-xs font-bold cursor-pointer transition"
                            :class="upgradeCalcMode === 'full' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/20 text-amber-900 dark:text-amber-200 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" value="full" x-model="upgradeCalcMode" class="sr-only">
                            <span
                                class="w-3.5 h-3.5 rounded-full border border-gray-400 flex items-center justify-center"
                                :class="upgradeCalcMode === 'full' ? 'border-amber-600 bg-amber-600' : ''"></span>
                            <span>مابه‌التفاوت کامل دوره</span>
                        </label>

                        <label
                            class="flex items-center gap-2.5 p-3 rounded-xl border text-xs font-bold cursor-pointer transition"
                            :class="upgradeCalcMode === 'custom' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/20 text-amber-900 dark:text-amber-200 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" value="custom" x-model="upgradeCalcMode" class="sr-only">
                            <span
                                class="w-3.5 h-3.5 rounded-full border border-gray-400 flex items-center justify-center"
                                :class="upgradeCalcMode === 'custom' ? 'border-amber-600 bg-amber-600' : ''"></span>
                            <span>مبلغ دستی / توافقی</span>
                        </label>
                    </div>

                    <div x-show="upgradeCalcMode === 'custom'" class="pt-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">مبلغ مورد نظر مابه‌التفاوت
                            ({{ $currencyLabel }}):</label>
                        <input type="text"
                               x-model="customUpgradePrice"
                               @input="formatCustomPrice($event)"
                               placeholder="مثال: ۵۰۰,۰۰۰"
                               class="w-full text-sm rounded-xl border border-gray-200 bg-white dark:bg-gray-900 px-4 py-2.5 font-bold dir-ltr text-right focus:border-amber-500 focus:ring-amber-500/20">
                    </div>
                </div>

                {{-- Calculation Breakdown Table --}}
                <div x-show="selectedUpgradePackageId"
                     class="p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/10 border border-amber-200/80 dark:border-amber-800/40 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                <span>قیمت پکیج جدید (<span
                                        x-text="selectedNewPackage ? selectedNewPackage.name : ''"></span>):</span>
                        <span class="font-bold tabular-nums dir-ltr text-gray-900 dark:text-white"
                              x-text="toFa(formatNumber(newPackagePrice)) + ' {{ $currencyLabel }}'"></span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                        <span>کسر بابت پکیج فعلی:</span>
                        <span class="font-bold tabular-nums dir-ltr text-gray-900 dark:text-white"
                              x-text="toFa(formatNumber(oldPackagePrice)) + ' {{ $currencyLabel }}'"></span>
                    </div>
                    <template x-if="upgradeCalcMode === 'prorated' && remainingDaysCount > 0">
                        <div
                            class="flex items-center justify-between text-amber-700 dark:text-amber-400 text-xs pt-1 border-t border-amber-200/40">
                            <span>نسبت روزهای باقیمانده:</span>
                            <span class="font-bold tabular-nums dir-ltr"
                                  x-text="toFa(remainingDaysCount) + ' از ' + toFa(totalCycleDaysCount) + ' روز'"></span>
                        </div>
                    </template>
                    <div
                        class="flex items-center justify-between text-sm font-black pt-2 border-t border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200">
                                <span
                                    x-text="upgradeIssueInvoice ? 'مبلغ نهایی فاکتور ارتقا:' : 'مابه‌التفاوت محاسبه‌شده:'"></span>
                        <span class="text-base tabular-nums dir-ltr text-amber-600 dark:text-amber-400"
                              x-text="finalUpgradeAmountFormatted + ' {{ $currencyLabel }}'"></span>
                    </div>
                </div>

                {{-- Issue Invoice Switch Card --}}
                <div
                    class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-4">
                    <div>
                        <span
                            class="text-xs font-black text-gray-800 dark:text-gray-200 block">صدور فاکتور برای ارتقا</span>
                        <p class="text-xs text-gray-400 mt-0.5">در صورت غیرفعال بودن، ارتقا مستقیماً اعمال
                            می‌شود.</p>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                                <span class="text-xs sm:text-sm font-bold transition-colors"
                                      :class="upgradeIssueInvoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                                      x-text="upgradeIssueInvoice ? 'فاکتور صادر شود' : 'عدم صدور'"></span>
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" x-model="upgradeIssueInvoice" class="sr-only peer">
                            <div
                                class="w-13 h-7 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                            <div
                                class="absolute right-1 top-1 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                <svg
                                    class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1"
                           x-text="upgradeIssueInvoice ? 'یادداشت برای فاکتور ارتقا (اختیاری)' : 'یادداشت ارتقای پکیج (اختیاری)'"></label>
                    <input type="text"
                           name="notes"
                           x-model="upgradeNotes"
                           placeholder="توضیحات تکمیلی پیرامون ارتقا..."
                           class="w-full text-xs rounded-xl border border-gray-200 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-gray-800 dark:text-gray-200 focus:border-amber-500">
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button"
                            @click="openUpgradeModal = false"
                            :disabled="upgradeSubmitting"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit"
                            :disabled="!selectedUpgradePackageId || !canSelectPackage(selectedNewPackage) || upgradeSubmitting"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 active:scale-95 text-white font-black text-xs shadow-md shadow-amber-500/20 transition flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="upgradeSubmitting" class="w-4 h-4 animate-spin" fill="none"
                             viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span
                            x-text="upgradeIssueInvoice ? 'تایید و صدور فاکتور ارتقا' : 'تایید و اعمال ارتقای پکیج'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

        {{-- Status Change Confirmation Modal --}}
<div x-show="openStatusModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
             @click="!statusSubmitting && (openStatusModal = false)"></div>

        {{-- Modal Card --}}
        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-right"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4">

            {{-- Close Button --}}
            <button type="button"
                    @click="openStatusModal = false"
                    :disabled="statusSubmitting"
                    class="absolute top-5 left-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Header Icon & Title --}}
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-xs shrink-0"
                     :style="`background: ${pendingStatusColor}20; color: ${pendingStatusColor}`">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">تایید تغییر وضعیت سفارش</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">سفارش شماره
                        #{{ $order->order_number }}</p>
                </div>
            </div>

            {{-- Status Transition Visual Box --}}
            <div
                class="bg-gray-50/80 dark:bg-gray-800/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-around">
                    {{-- Current Status --}}
                    <div class="text-center space-y-1.5">
                        <span class="text-xs font-bold text-gray-400 block">وضعیت فعلی</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black"
                              style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border: 1px solid {{ $statusColor }}30">
                                    <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>
                                    {{ $statusLabel }}
                                </span>
                    </div>

                    {{-- Direction Arrow --}}
                    <div class="text-gray-400 dark:text-gray-500 pt-3">
                        <svg class="w-6 h-6 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>

                    {{-- Target Status --}}
                    <div class="text-center space-y-1.5">
                        <span class="text-xs font-bold text-gray-400 block">وضعیت جدید</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black"
                              :style="`background: ${pendingStatusColor}15; color: ${pendingStatusColor}; border: 1px solid ${pendingStatusColor}30`">
                                    <span class="w-2 h-2 rounded-full"
                                          :style="`background: ${pendingStatusColor}`"></span>
                                    <span x-text="pendingStatusName"></span>
                                </span>
                    </div>
                </div>
            </div>

            {{-- Contextual Explanation Alert --}}
            <div class="p-3.5 rounded-2xl border text-xs leading-relaxed space-y-1"
                 :class="{
                             'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/30 dark:border-rose-800 dark:text-rose-300': pendingStatusId === 'cancelled',
                             'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950/30 dark:border-amber-800 dark:text-amber-300': pendingStatusId === 'inactive' || pendingStatusId === 'suspended',
                             'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/30 dark:border-emerald-800 dark:text-emerald-300': pendingStatusId === 'active' || pendingStatusId === 'completed',
                             'bg-sky-50 border-sky-200 text-sky-800 dark:bg-sky-950/30 dark:border-sky-800 dark:text-sky-300': pendingStatusId === 'pending',
                         }">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <template x-if="pendingStatusId === 'cancelled'">
                            <p>هشدار: با تغییر وضعیت به «لغو شده»، اشتراک و خدمات مربوط به این سفارش لغو گردیده
                                و گردش‌کارهای خودکار مربوطه متوقف یا اعمال خواهند شد.</p>
                        </template>
                        <template x-if="pendingStatusId === 'inactive' || pendingStatusId === 'suspended'">
                            <p>با تغییر وضعیت به «غیر فعال»، وضعیت سفارش به حالت معلق درآمده و اقدامات مقتضی
                                گردش کار اعمال می‌شود.</p>
                        </template>
                        <template x-if="pendingStatusId === 'active' || pendingStatusId === 'completed'">
                            <p>با فعال‌سازی وضعیت سفارش، دسترسی‌ها و یادآورهای تمدید دوره‌ای فعال خواهند
                                بود.</p>
                        </template>
                        <template x-if="pendingStatusId === 'pending'">
                            <p>سفارش به وضعیت «در انتظار» تغییر خواهد یافت تا بررسی‌ها یا فرایند پرداخت تکمیل
                                شود.</p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <form action="{{ route('reseller.orders.update', $order->id) }}" method="POST"
                  @submit="statusSubmitting = true">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" :value="pendingStatusId">
                <input type="hidden" name="renewal_price_type"
                       value="{{ $order->renewal_price_type ?? 'auto' }}">

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button"
                            @click="openStatusModal = false"
                            :disabled="statusSubmitting"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit"
                            :disabled="statusSubmitting"
                            class="px-6 py-2.5 rounded-xl text-white font-black text-xs shadow-md transition flex items-center gap-2 cursor-pointer bg-indigo-600 hover:bg-indigo-700 active:scale-95"
                            :style="pendingStatusColor ? `background: ${pendingStatusColor}` : ''">
                        <svg x-show="statusSubmitting" class="w-4 h-4 animate-spin" fill="none"
                             viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>تایید و تغییر وضعیت</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
