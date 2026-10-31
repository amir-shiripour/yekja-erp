<x-resellerhub::card class="p-6 sm:p-8 space-y-6"
                     x-init="if (!invoiceIssueDate) setTodayIssueDate()">
    <div
        class="flex items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
            <span
                class="p-2.5 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">صدور فاکتور برای این
                    سامانه</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ __('resellerhub::messages.wizard.step3.invoice_calc_desc') }}
                </p>
            </div>
        </div>
        <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                            <span class="text-xs sm:text-sm font-bold transition-colors"
                                  :class="issueInvoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                                  x-text="issueInvoice ? '{{ __('resellerhub::messages.wizard.step3.issue_invoice_toggle') }}' : '{{ __('resellerhub::messages.wizard.step3.free_package_notice') }}'"></span>
            <div class="relative inline-flex items-center">
                <input type="checkbox" x-model="issueInvoice" class="sr-only peer">
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

    <div x-show="issueInvoice" x-transition class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6 items-start w-full">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center justify-between h-[28px] mb-2">
                    <x-resellerhub::label
                        class="!mb-0 truncate">{{ __('resellerhub::messages.invoices.invoice_number') }}
                        <span class="text-rose-500 font-black">*</span>
                    </x-resellerhub::label>
                    <button type="button" @click="toggleInvoiceNumberAuto()"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black border transition-all cursor-pointer shrink-0"
                            :class="invoiceNumberAuto
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400'
                                                : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'">
                        <svg x-show="invoiceNumberAuto" class="w-3.5 h-3.5" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span
                            x-text="invoiceNumberAuto ? '{{ __('resellerhub::messages.wizard.step3.invoice_number_label') }}' : '{{ __('resellerhub::messages.invoices.source_manual') }}'"></span>
                    </button>
                </div>

                <div class="relative">
                    <x-resellerhub::input
                        class="!h-11 min-h-[44px] max-h-[44px] text-start font-medium tabular-nums transition-all duration-300"
                        type="text" dir="ltr" x-bind:class="invoiceNumberAuto
                                               ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed shadow-inner dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0 focus:border-gray-300 dark:focus:border-gray-700'
                                               : ''"
                        placeholder="مثال: INV-14040001"
                        x-model="invoiceNumberAuto ? invoiceNumberPreview : invoiceNumberOverride"
                        style="height: 44px !important;" x-bind:readonly="invoiceNumberAuto"/>
                </div>

                <p class="text-xs text-gray-400 ms-1 line-clamp-1" x-text="invoiceNumberAuto
                                    ? 'شماره‌گذاری خودکار از تنظیمات مالی'
                                    : 'در صورت ورود دستی، یکتا بودن شماره الزامی است.'"></p>
            </div>

            <div class="min-w-0 space-y-2">
                <div class="flex items-center h-[28px] mb-2">
                    <x-resellerhub::label class="!mb-0 truncate">{{ __('resellerhub::messages.invoices.issue_date') }}
                        <span
                            class="text-rose-500 font-black">*</span></x-resellerhub::label>
                </div>
                <div class="relative">
                    <x-resellerhub::input
                        class="!h-11 min-h-[44px] max-h-[44px] cursor-pointer focus:ring-amber-500/20 focus:border-amber-500"
                        type="text" x-model="invoiceIssueDate" data-jdp data-jdp-only-date

                        style="height: 44px !important; padding-inline-end: 2.75rem;"
                        placeholder="انتخاب {{ __('resellerhub::messages.invoices.issue_date') }}" autocomplete="off"
                        readonly/>
                    <svg
                        class="w-5 h-5 absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 ms-1 truncate">تاریخ رسمی صدور فاکتور در سامانه</p>
            </div>
            <div class="min-w-0 space-y-2">
                <div class="flex items-center justify-between h-[28px] mb-2">
                    <x-resellerhub::label
                        class="!mb-0 truncate">{{ __('resellerhub::messages.invoices.due_date') }}</x-resellerhub::label>
                    <div class="inline-flex items-center gap-1">
                        <button type="button" @click="invoiceDueDate = addDaysToJalaliToday(7)"
                                class="px-2 py-0.5 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-100 transition-all shadow-xs dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 active:scale-95">
                            ۱ هفته
                        </button>
                        <button type="button" @click="invoiceDueDate = addDaysToJalaliToday(30)"
                                class="px-2 py-0.5 text-xs font-bold rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white border border-violet-100 transition-all shadow-xs dark:bg-violet-500/10 dark:text-violet-400 dark:border-violet-500/20 active:scale-95">
                            ۱ ماه
                        </button>
                        <button type="button" @click="invoiceDueDate = addDaysToJalaliToday(365)"
                                class="px-2 py-0.5 text-xs font-bold rounded-lg bg-fuchsia-50 text-fuchsia-600 hover:bg-fuchsia-600 hover:text-white border border-fuchsia-100 transition-all shadow-xs dark:bg-fuchsia-500/10 dark:text-fuchsia-400 dark:border-fuchsia-500/20 active:scale-95">
                            ۱ سال
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <x-resellerhub::input
                        class="!h-11 min-h-[44px] max-h-[44px] cursor-pointer focus:ring-amber-500/20 focus:border-amber-500"
                        type="text" x-model="invoiceDueDate" data-jdp data-jdp-only-date
                        x-bind:data-jdp-min-date="invoiceIssueDate || 'today'"

                        style="height: 44px !important; padding-inline-end: 2.75rem;"
                        placeholder="انتخاب {{ __('resellerhub::messages.invoices.due_date') }}" autocomplete="off"
                        readonly/>
                    <svg
                        class="w-5 h-5 absolute end-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-400 ms-1 truncate">مهلت پرداخت فاکتور توسط مشتری</p>
            </div>
        </div>
    </div>
</x-resellerhub::card>
<x-resellerhub::card class="overflow-hidden" x-show="issueInvoice" x-transition>
    <div
        class="p-6 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/20 flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-base font-black text-gray-800 dark:text-gray-100 flex flex-wrap items-center gap-3">
            <span
                class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </span>
            <span>اقلام فاکتور</span>
            <span
                class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg"
                x-text="'بر اساس ' + durationLabel + ' و ' + faNum(selectedModules.length) + ' ماژول انتخابی'"></span>
            <template x-if="getActiveLimitsCount() > 0">
                <span
                    class="inline-flex items-center gap-1.5 text-xs font-black text-amber-700 bg-amber-50 dark:bg-amber-500/20 dark:text-amber-300 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-500/30">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <span x-text="faNum(getActiveLimitsCount()) + ' سقف/لیمیت فعال'"></span>
                                </span>
            </template>
            <template x-if="enableModuleItemizedPricing">
                <span
                    class="inline-flex items-center gap-1 text-xs font-black text-emerald-700 bg-emerald-50 dark:bg-emerald-500/20 dark:text-emerald-300 px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-500/30">
                                    قیمت‌گذاری مستقل ماژول‌ها فعال است
                                </span>
            </template>
        </h3>
        <button type="button" @click="addCustomInvoiceItem()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 active:scale-95 text-white text-xs font-bold shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            افزودن ردیف دلخواه
        </button>
    </div>

    <div class="overflow-x-auto" x-show="invoiceItems.length > 0">
        <table class="w-full text-sm min-w-[760px]">
            <thead>
            <tr x-show="!enableModuleItemizedPricing"
                class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/10">
                <th scope="col" class="text-start font-black px-4 py-3">شرح خدمت / پکیج و ماژول‌ها</th>
                <th scope="col" class="text-center font-black px-3 py-3 w-36">نوع قلم</th>
                <th scope="col" class="text-center font-black px-3 py-3 w-28">دوره / تعداد</th>
                <th scope="col" class="text-center font-black px-4 py-3 w-60">وضعیت در فاکتور</th>
                <th scope="col" class="text-center font-black px-2 py-3 w-12"></th>
            </tr>
            <tr x-show="enableModuleItemizedPricing"
                class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/10">
                <th scope="col" class="text-start font-black px-4 py-3 w-[38%]">شرح خدمت</th>
                <th scope="col" class="text-center font-black px-2 py-3 w-16">تعداد</th>
                <th scope="col" class="text-center font-black px-2 py-3 w-32">مبلغ واحد ({{ $currencyLabel }})</th>
                <th scope="col" class="text-center font-black px-2 py-3 w-32">تخفیف ردیف</th>
                <th scope="col" class="text-center font-black px-2 py-3 w-36">مبلغ کل</th>
                <th scope="col" class="text-center font-black px-2 py-3 w-12"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <template x-for="(item, idx) in invoiceItems" :key="idx + '-' + (item.item_id || 'custom')">
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                    <td class="px-4 py-3">
                        <template x-if="item.item_type === 'custom'">
                            <input type="text" x-model="item.description"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-3 py-2 text-xs font-medium text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all"
                                   placeholder="شرح ردیف دلخواه...">
                        </template>
                        <template x-if="item.item_type !== 'custom'">
                            <div class="flex items-start gap-2.5">
                                                <span
                                                    class="shrink-0 w-6 h-6 mt-0.5 flex items-center justify-center rounded-lg text-xs font-black"
                                                    :class="{
                                                          'bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400': item.item_type === 'module',
                                                          'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400': item.item_type === 'package',
                                                          'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400': item.item_type === 'setup_fee',
                                                          'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400': item.item_type === 'custom_domain' || item.item_type === 'white_label',
                                                      }">
                                                    <svg x-show="item.item_type === 'setup_fee'" class="w-3.5 h-3.5"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span x-show="item.item_type !== 'setup_fee'"
                                                          x-text="faNum(idx + 1)"></span>
                                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200 leading-5"
                                       x-text="item.description"></p>
                                    <template x-if="getItemLimits(item).length > 0">
                                        <div class="flex items-center gap-1.5 flex-wrap mt-1.5">
                                                            <span
                                                                class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                                                                <svg class="w-3 h-3 shrink-0" fill="none"
                                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2"
                                                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                                </svg>
                                                                سقف‌های مصرف:
                                                            </span>
                                            <template x-for="lim in getItemLimits(item)" :key="lim.key">
                                                                <span
                                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40 text-xs font-black">
                                                                    <span
                                                                        x-text="lim.label + ': ' + faNum(lim.value) + ' عدد'"></span>
                                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                    <template
                                        x-if="item.item_type === 'module' && getItemLimits(item).length === 0">
                                        <div class="mt-1">
                                                            <span
                                                                class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium">
                                                                <svg class="w-2.5 h-2.5 text-emerald-500" fill="none"
                                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                                سقف مصرف: نامحدود
                                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </td>

                    <td x-show="!enableModuleItemizedPricing"
                        class="px-3 py-3 text-center whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black"
                            :class="{
                                                  'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300': item.item_type === 'package',
                                                  'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300': item.item_type === 'module' && !item.is_extra,
                                                  'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300': (item.item_type === 'module' && item.is_extra) || item.item_type === 'setup_fee',
                                                  'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300': item.item_type === 'custom_domain' || item.item_type === 'white_label',
                                                  'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': item.item_type === 'custom',
                                              }">
                                            <span
                                                x-text="item.item_type === 'package' ? 'پکیج اصلی' : (item.item_type === 'module' ? (item.is_extra ? 'ماژول الحاقی' : 'ماژول پکیج') : (item.item_type === 'setup_fee' ? 'راه‌اندازی' : (item.item_type === 'custom_domain' ? 'دامنه اختصاصی' : (item.item_type === 'white_label' ? 'وایت‌لیبل' : 'سفارشی'))))"></span>
                                        </span>
                    </td>

                    <td x-show="!enableModuleItemizedPricing"
                        class="px-3 py-3 text-center whitespace-nowrap">
                        <template x-if="item.item_type === 'custom'">
                            <input type="text" inputmode="numeric"
                                   :value="faNum(item.quantity)"
                                   @input="item.quantity = Math.max(1, parsePriceInput($event.target.value) || 1)"
                                   class="w-14 mx-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-2 py-1.5 text-center text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all tabular-nums">
                        </template>
                        <template x-if="item.item_type !== 'custom'">
                                            <span
                                                class="text-xs font-bold text-gray-600 dark:text-gray-300 tabular-nums"
                                                x-text="item.duration_months > 0 ? (item.duration_months === 12 ? '۱ ساله' : (item.duration_months === 1 ? '۱ ماهه' : (item.duration_months === 3 ? '۳ ماهه' : (item.duration_months === 6 ? '۶ ماهه' : faNum(item.duration_months) + ' ماهه')))) : (faNum(item.quantity || 1) + ' عدد')"></span>
                        </template>
                    </td>

                    <td x-show="!enableModuleItemizedPricing"
                        class="px-4 py-3 text-center whitespace-nowrap">
                        <template x-if="item.item_type === 'package'">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                        stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                محاسبه در خلاصه مالی
                                            </span>
                        </template>
                        <template x-if="item.item_type === 'module'">
                            <div class="flex flex-col items-center gap-1">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                                    :class="item.is_extra ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'">
                                                    <span class="w-1.5 h-1.5 rounded-full"
                                                          :class="item.is_extra ? 'bg-amber-500' : 'bg-gray-400'"></span>
                                                    <span
                                                        x-text="item.is_extra ? 'الحاقی (محاسبه در پکیج)' : 'شامل در پکیج (بدون هزینه مجزا)'"></span>
                                                </span>
                                <template x-if="getItemLimits(item).length > 0">
                                                    <span
                                                        class="text-xs font-bold text-amber-600 dark:text-amber-400"
                                                        x-text="faNum(getItemLimits(item).length) + ' سقف مشخص‌شده'"></span>
                                </template>
                            </div>
                        </template>
                        <template x-if="item.item_type === 'setup_fee'">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                                هزینه یکباره راه‌اندازی
                                            </span>
                        </template>
                        <template
                            x-if="item.item_type === 'custom_domain' || item.item_type === 'white_label'">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30">
                                                خدمت جانبی اختصاصی
                                            </span>
                        </template>
                        <template x-if="item.item_type === 'custom'">
                            <div class="flex items-center justify-center gap-1 text-xs">
                                <span class="text-gray-400 text-xs">مبلغ:</span>
                                <input type="text" inputmode="numeric"
                                       x-effect="if(document.activeElement !== $el) $el.value = (item.unit_price > 0 ? formatPriceInput(item.unit_price) : '')"
                                       @input="let val = parsePriceInput($event.target.value); item.unit_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                       class="w-24 rounded-lg border border-gray-200 dark:border-gray-700 px-2 py-1 text-center text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:outline-none"
                                       placeholder="مبلغ...">
                                <span class="text-xs text-gray-400">{{ $currencyLabel }}</span>
                            </div>
                        </template>
                    </td>

                    <td x-show="enableModuleItemizedPricing" class="px-2 py-3 text-center">
                        <input type="text" inputmode="numeric"
                               :value="faNum(item.quantity)"
                               @input="item.quantity = Math.max(1, parsePriceInput($event.target.value) || 1)"
                               class="w-14 mx-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-2 py-1.5 text-center text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all tabular-nums">
                    </td>
                    <td x-show="enableModuleItemizedPricing" class="px-2 py-3 text-center">
                        <div class="flex items-center gap-1.5 justify-center">
                            <div class="relative flex-1 max-w-[120px]">
                                <input type="text" inputmode="numeric"
                                       x-effect="if(document.activeElement !== $el) $el.value = (item.unit_price > 0 ? formatPriceInput(item.unit_price) : '')"
                                       @input="let val = parsePriceInput($event.target.value); item.unit_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                       @blur="$el.value = (item.unit_price > 0 ? formatPriceInput(item.unit_price) : '')"
                                       :readonly="item.item_type !== 'custom' && !item._priceUnlocked"
                                       :class="(item.item_type !== 'custom' && !item._priceUnlocked) ? 'bg-gray-100/70 dark:bg-gray-800/60 cursor-not-allowed text-gray-500 dark:text-gray-400' : 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white'"
                                       class="w-full rounded-lg border border-gray-200 dark:border-gray-700 px-2 py-1.5 text-center text-xs font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all tabular-nums"
                                       placeholder="رایگان">
                            </div>
                            <template x-if="item.item_type !== 'custom'">
                                <button type="button"
                                        @click="item._priceUnlocked = !item._priceUnlocked; if(item._priceUnlocked) { $nextTick(() => { const inp = $el.closest('td').querySelector('input'); if(inp) { inp.removeAttribute('readonly'); inp.focus(); inp.select(); } }); }"
                                        class="p-1.5 rounded-lg border transition-colors cursor-pointer shrink-0"
                                        :class="item._priceUnlocked ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 dark:border-gray-700 text-gray-400 hover:text-indigo-500 hover:border-indigo-300'"
                                        :title="item._priceUnlocked ? 'قفل کردن قیمت' : 'ویرایش دستی مبلغ واحد'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </td>
                    <td x-show="enableModuleItemizedPricing" class="px-2 py-3 text-center">
                        <div class="flex items-center gap-1.5 justify-center">
                            <div class="relative flex-1 max-w-[110px]">
                                <input type="text" inputmode="numeric"
                                       x-effect="if(document.activeElement !== $el) $el.value = (item.discount_amount > 0 ? formatPriceInput(item.discount_amount) : '')"
                                       @input="let val = parsePriceInput($event.target.value); item.discount_amount = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                       @blur="$el.value = (item.discount_amount > 0 ? formatPriceInput(item.discount_amount) : '')"
                                       :readonly="item.item_type !== 'custom' && !item._discountUnlocked"
                                       :class="(item.item_type !== 'custom' && !item._discountUnlocked) ? 'bg-gray-100/70 dark:bg-gray-800/60 cursor-not-allowed text-gray-500 dark:text-gray-400' : 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white'"
                                       class="w-full rounded-lg border border-gray-200 dark:border-gray-700 px-2 py-1.5 text-center text-xs font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all tabular-nums"
                                       placeholder="—">
                            </div>
                            <template x-if="item.item_type !== 'custom'">
                                <button type="button"
                                        @click="item._discountUnlocked = !item._discountUnlocked; if(item._discountUnlocked) { $nextTick(() => { const inp = $el.closest('td').querySelector('input'); if(inp) { inp.removeAttribute('readonly'); inp.focus(); inp.select(); } }); }"
                                        class="p-1.5 rounded-lg border transition-colors cursor-pointer shrink-0"
                                        :class="item._discountUnlocked ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 dark:border-gray-700 text-gray-400 hover:text-indigo-500 hover:border-indigo-300'"
                                        :title="item._discountUnlocked ? 'قفل کردن تخفیف' : 'ویرایش دستی تخفیف ردیف'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </td>
                    <td x-show="enableModuleItemizedPricing" class="px-2 py-3 text-center">
                        <span class="text-xs font-black text-gray-900 dark:text-white tabular-nums"
                              x-text="formatAmount(getItemRowTotal(item))"></span>
                        <span class="text-xs text-gray-400">{{ $currencyLabel }}</span>
                    </td>
                    <td class="px-2 py-3 text-center">
                        <button type="button" @click="removeInvoiceItem(idx)"
                                class="p-1.5 rounded-lg text-gray-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer"
                                title="حذف ردیف">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
    <div x-show="!enableModuleItemizedPricing"
         class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 border-t border-indigo-100 dark:border-indigo-900/40 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-indigo-800 dark:text-indigo-200">
            <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">تعرفه ماژول‌ها در قالب پکیج کلی تجمیع شده است. ستون‌های قیمت و تخفیف به صورت مجزا پنهان بوده و مبالغ در بخش «خلاصه مالی فاکتور» محاسبه شده‌اند.</span>
        </div>
        <a href="{{ route('reseller.pricings.index') }}" target="_blank"
           class="inline-flex items-center gap-1 text-xs font-black text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200 underline">
            <span>تنظیمات قیمت‌گذاری ماژول‌ها</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>


    <div x-show="invoiceItems.length === 0" class="p-8 text-center text-xs text-gray-400">
        هنوز موردی به فاکتور اضافه نشده — ماژول‌ها و پکیج را در مراحل قبل انتخاب کنید یا ردیف دلخواه
        بسازید.
    </div>
</x-resellerhub::card>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6" x-show="issueInvoice" x-transition>
    <x-resellerhub::card class="p-6 sm:p-7 space-y-5 lg:col-span-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                شرایط، قوانین و یادداشت فاکتور
            </h3>
            <button type="button" @click="invoiceTermsLocked = !invoiceTermsLocked"
                    class="text-xs font-black px-2.5 py-1 rounded-lg border transition-all cursor-pointer"
                    :class="invoiceTermsLocked
                                        ? 'bg-indigo-50 border-indigo-200 text-indigo-600 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400'
                                        : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'"
                    x-text="invoiceTermsLocked ? 'از تنظیمات' : 'ویرایش'"></button>
        </div>


        <x-resellerhub::textarea class="!text-xs leading-6 resize-y transition-all duration-300"
                                 x-model="invoiceTerms" rows="5" x-bind:readonly="invoiceTermsLocked"
                                 x-bind:class="invoiceTermsLocked
                                      ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0'
                                      : ''"
                                 placeholder="شرایط، قوانین و یادداشت فاکتور..."></x-resellerhub::textarea>
    </x-resellerhub::card>

    <x-resellerhub::card
        class="p-6 sm:p-7 space-y-4 lg:col-span-2 bg-gray-50 dark:bg-gray-800/80 flex flex-col justify-between"
    >
        <div>
            <div
                class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <span
                        class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 7h6m-6 4h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                        </svg>
                                    </span>
                    خلاصه مالی فاکتور
                </h3>
                <span
                    class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md"
                    x-text="'واحد: ' + '{{ $currencyLabel }}'"></span>
            </div>

            <div class="space-y-3 pt-3 text-xs">
                <div
                    class="p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-between gap-3 shadow-xs transition-all">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </span>
                        <div>
                            <div class="text-xs font-bold text-indigo-900 dark:text-indigo-200">جمع کل
                                اقلام
                            </div>
                            <div
                                class="text-xs text-indigo-500/80 dark:text-indigo-400 font-medium mt-0.5"
                                x-text="faNum(invoiceItems.length) + ' ردیف قلم فاکتور'"></div>
                        </div>
                    </div>
                    <div class="text-end">
                                        <span
                                            class="text-sm font-black text-indigo-900 dark:text-indigo-100 tabular-nums"
                                            x-text="formatAmount(getInvoiceSubtotal())"></span>
                        <span
                            class="text-xs font-bold text-indigo-500/80 dark:text-indigo-400 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>

                <div
                    class="p-3 rounded-2xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/30 flex items-center justify-between gap-3 shadow-xs transition-all">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                        <div>
                            <div class="text-xs font-bold text-amber-900 dark:text-amber-200">مالیات بر
                                ارزش افزوده
                            </div>
                            <div
                                class="text-xs font-black text-amber-600 dark:text-amber-400 tabular-nums mt-0.5">
                                <span>+ </span>
                                <span x-text="formatAmount(getInvoiceTax())"></span>
                                <span
                                    class="text-[9px] font-bold text-amber-500/80 ms-0.5">{{ $currencyLabel }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="relative inline-flex items-center">
                            <input type="text"
                                   :value="faNum(invoiceTaxRate)"
                                   @input="let val = parsePriceInput($event.target.value); invoiceTaxRate = Math.min(100, Math.max(0, val || 0)); applyTaxToItems();"
                                   class="w-14 rounded-xl border-2 px-2 py-1 text-xs text-center tabular-nums font-black outline-none transition-colors"
                                   :class="!taxRateLocked
                                                       ? 'border-amber-400 text-amber-900 dark:text-amber-200 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-amber-500/20 shadow-xs'
                                                       : 'border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 bg-amber-100/60 dark:bg-amber-900/30 cursor-not-allowed opacity-80'"
                                   dir="ltr" maxlength="3"
                                   :readonly="taxRateLocked">
                            <span
                                class="ms-1 text-xs font-black text-amber-600 dark:text-amber-400 select-none">٪</span>
                        </div>
                        <button type="button" @click="toggleTaxRateLock()"
                                class="p-1.5 rounded-xl border-2 transition-all active:scale-95 cursor-pointer shrink-0"
                                :class="!taxRateLocked
                                                    ? 'border-amber-500 bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:border-amber-500/50 dark:text-amber-400 shadow-sm'
                                                    : 'border-amber-200 text-amber-500 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50/50 dark:border-amber-700/50 dark:text-amber-500 dark:hover:bg-amber-900/30'"
                                :title="taxRateLocked ? 'ویرایش دستی نرخ مالیات' : 'قفل کردن نرخ مالیات (طبق تنظیمات)'">
                            <svg x-show="taxRateLocked" class="w-3.5 h-3.5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <svg x-show="!taxRateLocked" class="w-3.5 h-3.5" x-cloak fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    class="p-3 rounded-2xl bg-rose-50/70 dark:bg-rose-500/10 border border-rose-200/80 dark:border-rose-500/30 flex items-center justify-between gap-3 shadow-xs transition-all">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        </span>
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span
                                    class="text-xs font-bold text-rose-900 dark:text-rose-200">{{ __('resellerhub::messages.wizard.step3.reseller_discount') }}</span>
                                <template x-if="getActivePackage() && getActivePackageDiscount() > 0">
                                    <span
                                        class="text-[9px] px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 font-bold"
                                        x-text="'(شامل تخفیف پکیج)'"></span>
                                </template>
                            </div>
                            <template x-if="getInvoiceDiscount() > 0">
                                <div
                                    class="text-xs font-black text-rose-600 dark:text-rose-400 tabular-nums mt-0.5"
                                    x-text="'− ' + formatAmount(getInvoiceDiscount()) + ' {{ $currencyLabel }}'"></div>
                            </template>
                            <template x-if="!getInvoiceDiscount() || getInvoiceDiscount() === 0">
                                <div class="text-xs text-rose-400/80 font-medium mt-0.5">بدون
                                    تخفیف
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <input type="text" inputmode="numeric" x-model="invoiceDiscountDisplay"
                               @input="handleInvoiceDiscountInput($event)"
                               @change="updateInvoiceDiscount()"
                               class="w-28 sm:w-32 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-white dark:bg-gray-900/60 px-2 py-1.5 text-center text-xs font-black text-rose-900 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/15 transition-all tabular-nums shadow-2xs"
                               placeholder="۰">
                        <span
                            class="text-xs text-rose-400 font-bold shrink-0">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-3">
            <div
                class="p-4 rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span
                        class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-xs flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                    <div>
                        <div
                            class="text-xs font-bold text-emerald-100">{{ __('resellerhub::messages.wizard.step3.final_payable') }}</div>
                        <div class="text-xs text-emerald-200/80 font-medium mt-0.5">شامل مالیات و
                            اعمال تخفیف
                        </div>
                    </div>
                </div>
                <div class="text-end">
                                    <span class="text-xl sm:text-2xl font-black text-white tabular-nums tracking-tight"
                                          x-text="formatAmount(getInvoiceTotal())"></span>
                    <span class="text-xs font-bold text-emerald-200 ms-1">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>
    </x-resellerhub::card>
</div>
<x-resellerhub::card
    class="p-4 sm:p-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3"
>
    <button type="button" @click="prevStep()"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-black text-gray-600 dark:text-gray-300 transition-all active:scale-95 cursor-pointer">
        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>{{ __('resellerhub::messages.wizard.step3.prev_step') }}</span>
    </button>

    <button type="submit" :disabled="submitting"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-md shadow-indigo-600/20 transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
        <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <svg x-show="submitting" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span
            x-text="submitting ? '{{ __('resellerhub::messages.common.save') }}...' : '{{ __('resellerhub::messages.wizard.step3.submit_tenant') }}'"></span>
    </button>
</x-resellerhub::card>
