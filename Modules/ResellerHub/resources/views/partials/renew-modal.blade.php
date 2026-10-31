@php use Modules\ResellerHub\App\Enums\BillingCycle; @endphp
<div x-show="renewModalOpen"
     x-cloak
     class="fixed inset-0 z-[9999] overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
             @click="!renewLoading && closeRenewModal()"></div>

        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-xl w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-center">

            {{-- Icon Badge --}}
            <div
                class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center shadow-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-emerald-500/20">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            {{-- Title & Subtitle --}}
            <div class="space-y-1.5">
                <h3 class="text-xl font-black text-gray-900 dark:text-white">تمدید لایسنس و افزایش مدت اعتبار</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    افزایش مدت اعتبار لایسنس سامانه
                    <strong class="text-gray-900 dark:text-gray-100 font-bold"
                            x-text="selectedRenewTenant.name || ''"></strong>
                </p>
            </div>

            {{-- Tenant Info & Expiry Box --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs space-y-2.5 text-start">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold">نام کسب‌وکار / سامانه:</span>
                    <span class="font-black text-gray-800 dark:text-gray-200"
                          x-text="selectedRenewTenant.name || '—'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold">آدرس سابدامین:</span>
                    <span class="font-black text-indigo-600 dark:text-indigo-400" dir="ltr"
                          x-text="selectedRenewTenant.subdomain || '—'"></span>
                </div>
                <template x-if="selectedRenewTenant.package_name">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 font-bold">پکیج / طرح اشتراک:</span>
                        <span class="font-black text-purple-600 dark:text-purple-400"
                              x-text="selectedRenewTenant.package_name"></span>
                    </div>
                </template>
                <div class="flex justify-between items-center pt-1 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-400 font-bold">تاریخ انقضای فعلی:</span>
                    <span class="font-bold text-amber-600 dark:text-amber-400"
                          x-text="faNum(selectedRenewTenant.current_expires_at) || 'نامحدود'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold">تاریخ سررسید جدید پس از تمدید:</span>
                    <span class="font-black text-emerald-600 dark:text-emerald-400"
                          x-text="calculateNewExpiry(selectedRenewTenant.current_expires_at, isCustomMonths ? customMonthsInput : selectedMonths)"></span>
                </div>
            </div>

            {{-- Renewal Period Selection --}}
            <div class="space-y-3 text-start">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        انتخاب مدت زمان تمدید:
                    </label>
                    <button type="button"
                            @click="isCustomMonths = !isCustomMonths; if (isCustomMonths && !customMonthsInput) customMonthsInput = selectedMonths;"
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 cursor-pointer">
                        <span
                            x-text="isCustomMonths ? 'انتخاب از دوره‌های پکیج' : 'سایر دوره‌ها (تعداد ماه دلخواه)'"></span>
                    </button>
                </div>

                {{-- Option 1: Custom Months Input --}}
                <div x-show="isCustomMonths"
                     class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60 space-y-2">
                    <label class="block text-xs font-bold text-indigo-900 dark:text-indigo-200">
                        تعداد ماه تمدید دلخواه (۱ تا ۱۲۰ ماه):
                    </label>
                    <div class="relative flex items-center">
                        <input type="number"
                               min="1"
                               max="120"
                               x-model.number="customMonthsInput"
                               placeholder="مثلاً: 18"
                               class="w-full text-sm font-black py-2.5 px-3 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-800 text-left dir-ltr text-indigo-700 dark:text-indigo-300 focus:ring-2 focus:ring-indigo-500/20">
                        <span class="absolute left-3 text-xs font-bold text-gray-400 pointer-events-none">ماه</span>
                    </div>
                </div>

                {{-- Option 2: Dynamic Package Periods Grid --}}
                <div x-show="!isCustomMonths">
                    {{-- If Tenant Has Custom Package Periods --}}
                    <template x-if="selectedRenewTenant.periods && selectedRenewTenant.periods.length">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <template x-for="period in selectedRenewTenant.periods" :key="period.months">
                                <div @click="selectedMonths = period.months; isCustomMonths = false"
                                     :class="(!isCustomMonths && selectedMonths === period.months) ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 ring-2 ring-emerald-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/60 text-gray-700 dark:text-gray-300'"
                                     class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span
                                            class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition-colors"
                                            :class="(!isCustomMonths && selectedMonths === period.months) ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300 dark:border-gray-600'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white"
                                                  x-show="!isCustomMonths && selectedMonths === period.months"></span>
                                        </span>
                                        <div class="min-w-0">
                                            <span class="text-xs font-bold truncate block" x-text="period.label"></span>
                                            <span x-show="period.badge"
                                                  class="text-xs text-emerald-600 dark:text-emerald-400 font-bold block"
                                                  x-text="'(' + period.badge + ')'"></span>
                                        </div>
                                    </div>
                                    <div class="text-left shrink-0" x-show="period.price !== undefined">
                                        <template x-if="period.has_discount">
                                            <div>
                                                <div class="text-xs text-gray-400 line-through tabular-nums"
                                                     x-text="period.raw_price_formatted"></div>
                                                <div class="text-xs font-black text-rose-600 dark:text-rose-400">
                                                    <span
                                                        x-text="period.price_formatted || faNum(period.price)"></span>
                                                    <span class="text-xs text-gray-400"
                                                          x-text="selectedRenewTenant.currency || '{{ reseller_currency_label() }}'"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!period.has_discount">
                                            <div>
                                                <span class="text-xs font-black text-gray-900 dark:text-white"
                                                      x-text="period.price_formatted || faNum(period.price)"></span>
                                                <span class="text-xs text-gray-400"
                                                      x-text="selectedRenewTenant.currency || '{{ reseller_currency_label() }}'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Fallback Default Periods Grid using Enum & Helper --}}
                    <template x-if="!selectedRenewTenant.periods || !selectedRenewTenant.periods.length">
                        <div class="grid grid-cols-2 gap-2.5">
                            @foreach(BillingCycle::standardList() as $stdCycle)
                                @if(in_array((int)$stdCycle['months'], [1, 3, 6, 12], true))
                                    <div @click="selectedMonths = {{ $stdCycle['months'] }}; isCustomMonths = false"
                                         :class="(!isCustomMonths && selectedMonths === {{ $stdCycle['months'] }}) ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 ring-2 ring-emerald-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/60 text-gray-700 dark:text-gray-300'"
                                         class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span
                                                class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition-colors"
                                                :class="(!isCustomMonths && selectedMonths === {{ $stdCycle['months'] }}) ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300 dark:border-gray-600'">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white"
                                                      x-show="!isCustomMonths && selectedMonths === {{ $stdCycle['months'] }}"></span>
                                            </span>
                                            <div>
                                                <span
                                                    class="text-xs font-bold">{{ reseller_duration_label($stdCycle['months'], true) }}</span>
                                                @if((int)$stdCycle['months'] === 12)
                                                    <span
                                                        class="text-xs text-emerald-600 dark:text-emerald-400 font-bold block">(پیشنهادی)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            {{-- Issue Invoice Switch Card (Standard pattern matching ResellerHub) --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-4 text-start">
                <div>
                    <span
                        class="text-xs font-black text-gray-800 dark:text-gray-200 block">صدور فاکتور مالی برای تمدید</span>
                    <p class="text-xs text-gray-400 mt-0.5">در صورت فعال بودن، فاکتور رسمی تمدید صادر می‌شود. در غیر
                        این صورت، اعتبار لایسنس مستقیماً افزایش می‌یابد.</p>
                </div>
                <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                    <span class="text-xs sm:text-sm font-bold transition-colors"
                          :class="issueInvoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                          x-text="issueInvoice ? 'فاکتور صادر شود' : 'عدم صدور (تمدید مستقیم)'"></span>
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" x-model="issueInvoice" class="sr-only peer">
                        <div
                            class="w-13 h-7 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                        <div
                            class="absolute right-1 top-1 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                            <svg class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Optional Notes Input --}}
            <div class="text-start space-y-1">
                <label class="block text-xs font-bold text-gray-600 dark:text-gray-400"
                       x-text="issueInvoice ? 'یادداشت برای فاکتور تمدید (اختیاری):' : 'یادداشت تمدید لایسنس (اختیاری):'"></label>
                <input type="text"
                       x-model="renewNotes"
                       placeholder="مثلاً: تمدید سالانه به درخواست مدیر زیرمجموعه یا پیگیری پشتیبانی..."
                       class="w-full text-xs font-medium py-2.5 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20">
            </div>

            {{-- Modal Action Buttons --}}
            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="closeRenewModal()" :disabled="renewLoading"
                        class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-bold transition-all w-1/2 flex items-center justify-center cursor-pointer disabled:opacity-50">
                    انصراف
                </button>
                <button type="button" @click="confirmRenew()" :disabled="renewLoading"
                        class="px-5 py-3 rounded-2xl text-white text-sm font-black transition-all shadow-md shadow-emerald-600/20 bg-emerald-600 hover:bg-emerald-700 active:scale-95 flex items-center justify-center gap-2 w-1/2 disabled:opacity-50 cursor-pointer">
                    <template x-if="renewLoading">
                        <svg class="animate-spin w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <template x-if="!renewLoading">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span
                        x-text="renewLoading ? 'در حال انجام...' : (issueInvoice ? 'تایید و صدور فاکتور تمدید' : 'تایید و افزایش اعتبار لایسنس')"></span>
                </button>
            </div>
        </div>
    </div>
</div>
