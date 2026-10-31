<div x-show="openRenewalModal"
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
        <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
             @click="!renewalSubmitting && (openRenewalModal = false)"></div>
        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-right"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4">

            {{-- Close Button --}}
            <button type="button" @click="openRenewalModal = false" :disabled="renewalSubmitting"
                    class="absolute top-5 left-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Header --}}
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">صدور فوری فاکتور تمدید</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        دوره تمدید به‌صورت خودکار از پکیج سفارش #{{ reseller_fa_num($order->order_number) }} تعیین
                        می‌شود
                    </p>
                </div>
            </div>

            {{-- Summary Box --}}
            <div
                class="space-y-3 bg-gray-50/80 dark:bg-gray-800/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 text-xs">
                <div class="flex justify-between items-center py-1">
                    <span class="text-gray-400 font-bold">زیرمجموعه (مشتری):</span>
                    <span class="font-black text-gray-800 dark:text-gray-200">{{ $clientName }}</span>
                </div>
                @if($tenant)
                    <div
                        class="flex justify-between items-center py-1 border-t border-gray-100 dark:border-gray-700/50">
                        <span class="text-gray-400 font-bold">سامانه متصل:</span>
                        <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $tenant->name }}</span>
                    </div>
                @endif
                <div
                    class="flex justify-between items-center py-1 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-400 font-bold">پکیج / طرح:</span>
                    <span class="font-black text-gray-800 dark:text-gray-200">
                        {{ $package?->name ?? 'طرح اختصاصی (سفارشی)' }}
                    </span>
                </div>
                <div
                    class="flex justify-between items-center py-1 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-400 font-bold">مبلغ فاکتور تمدید:</span>
                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm tabular-nums">
                        {{ reseller_price($renewalDisplayPrice, $currencyLabel) }}
                    </span>
                </div>
                <div
                    class="flex justify-between items-center py-1 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-400 font-bold">تاریخ سررسید بعدی:</span>
                    <span
                        class="font-black text-gray-900 dark:text-white tabular-nums dir-ltr text-right inline-block">
                        {{ $renewalDisplayNextDate ? reseller_fa_num($renewalDisplayNextDate) : 'یک دوره پس از سررسید فعلی' }}
                    </span>
                </div>
            </div>
            <form action="{{ route('reseller.orders.renewal-invoice', $order->id) }}" method="POST"
                  @submit="renewalSubmitting = true" class="space-y-5">
                @csrf

                <div class="space-y-2 text-right">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">دوره مالی فاکتور
                            تمدید:</label>
                        <span
                            class="inline-flex items-center gap-1 text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 px-2 py-0.5 rounded-md select-none">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            ثابت — بر اساس پکیج سفارش
                        </span>
                    </div>

                            <div
                                class="relative p-4 rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-indigo-50/70 dark:bg-indigo-950/40 flex items-center justify-between gap-3 select-none">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span
                                            class="block font-black text-sm text-indigo-700 dark:text-indigo-300">{{ $renewalDurationLbl }}</span>
                                        <span
                                            class="block text-xs font-bold text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $isCustomPeriod
                                        ? 'پکیج سفارشی — دقیقاً همان مدت سفارش ثبت‌شده تمدید می‌شود'
                                        : 'پکیج سیستمی — همان دوره ثبت‌شده در سفارش' }}
                                </span>
                                    </div>
                                </div>
                                <div class="text-left shrink-0">
                                    <span class="block text-xs font-bold text-gray-400 mb-0.5">مبلغ این دوره</span>
                                    <span
                                        class="block font-black text-sm text-indigo-700 dark:text-indigo-300 tabular-nums">
                                        {{ reseller_price($renewalDisplayPrice, $currencyLabel) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                  {{-- Notice --}}
                  <div
                      class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/40 flex items-start gap-3 text-amber-800 dark:text-amber-300 text-xs leading-relaxed text-right">
                      <svg class="w-5 h-5 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24"
                           stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <p>
                          دوره تمدید «{{ $renewalDurationLbl }}» به‌صورت خودکار از پکیج این سفارش تعیین شده و قابل
                          تغییر نیست.
                          با تایید، فاکتور جدید در وضعیت «در انتظار پرداخت» ثبت شده و سررسید تمدید سفارش به همان
                          اندازه
                          به جلو منتقل می‌شود.
                      </p>
                        </div>

                  {{-- Actions --}}
                  <div class="flex items-center justify-end gap-3 pt-2">
                      <button type="button" @click="openRenewalModal = false" :disabled="renewalSubmitting"
                              class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                          انصراف
                      </button>
                      <button type="submit" :disabled="renewalSubmitting"
                              class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-black text-xs shadow-md shadow-indigo-500/20 transition flex items-center gap-2 cursor-pointer">
                          <svg x-show="renewalSubmitting" class="w-4 h-4 animate-spin" fill="none"
                               viewBox="0 0 24 24" style="display: none;">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                      stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          <span>تایید و صدور فاکتور</span>
                      </button>
                  </div>
              </form>
         </div>
    </div>
</div>
