<div role="tabpanel" id="tabpanel-finance" aria-labelledby="tab-finance" x-show="activeTab === 'finance'" x-cloak class="space-y-6">
    <x-resellerhub::card>
        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </span>
                شماره‌گذاری فاکتور
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">الگوی تولید شماره برای فاکتورهای
                جدید
                را تعیین کنید.</p>
        </div>
        <div class="p-6 md:p-8 space-y-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <x-resellerhub::label>پیشوند (Prefix)</x-resellerhub::label>
                    <x-resellerhub::input class="dir-ltr text-left " type="text" name="invoice_prefix"
                                          x-model="invoicePrefix"
                                          @input="updateInvoicePreview()"
                                          value="{{ $settings['invoice_prefix'] ?? 'INV-' }}"

                                          placeholder="INV-"/>
                    <div class="flex items-center gap-1.5 mt-2">
                        <button type="button" @click="setPrefixPreset('INV-')"
                                :class="invoicePrefix === 'INV-' ? 'bg-indigo-600 text-white' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                class="text-[10px] px-2 py-0.5 rounded font-mono transition-colors cursor-pointer">INV-
                        </button>
                        <button type="button" @click="setPrefixPreset('FAC-')"
                                :class="invoicePrefix === 'FAC-' ? 'bg-indigo-600 text-white' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                class="text-[10px] px-2 py-0.5 rounded font-mono transition-colors cursor-pointer">FAC-
                        </button>
                    </div>
                </div>
                <div>
                    <x-resellerhub::label>میانی (مثلاً سال)</x-resellerhub::label>
                    <input type="text" name="invoice_middle_prefix" x-model="invoiceMiddle"
                           @input="updateInvoicePreview()"
                           value="{{ $settings['invoice_middle_prefix'] ?? (string)reseller_jalali_year() }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 dir-ltr text-left "
                           placeholder="{{ (string)reseller_jalali_year() }}">
                </div>
                <div>
                    <x-resellerhub::label>پسوند (Suffix)</x-resellerhub::label>
                    <x-resellerhub::input class="dir-ltr text-left " type="text" name="invoice_suffix"
                                          x-model="invoiceSuffix"
                                          @input="updateInvoicePreview()"
                                          value="{{ $settings['invoice_suffix'] ?? '' }}"

                                          placeholder="-CRM"/>
                </div>
                <div>
                    <x-resellerhub::label>طول شماره</x-resellerhub::label>
                    <x-resellerhub::input class="dir-ltr text-left" type="number" name="invoice_padding"
                                          x-model.number="invoicePadding"
                                          @input="updateInvoicePreview()" min="1" max="10"
                                          value="{{ $settings['invoice_padding'] ?? 4 }}"
                    />
                </div>
            </div>
            <div
                class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/10 border border-indigo-100 dark:border-indigo-500/20 flex flex-col items-center justify-center text-center">
                <span
                    class="text-xs font-bold uppercase tracking-widest text-indigo-400 dark:text-indigo-500 mb-2">پیش‌نمایش شماره فاکتور</span>
                <span
                    class="text-3xl font-black text-indigo-600 dark:text-indigo-300 tracking-wider dir-ltr"
                    x-text="invoicePreview"></span>
            </div>
            <div class="mt-6 border-t border-gray-100 dark:border-gray-700/60 pt-6">
                <label for="invoice_auto"
                       class="flex items-center justify-between gap-4 cursor-pointer group p-5 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                    <div class="flex-1">
                        <span class="text-base font-black text-gray-800 dark:text-gray-200 block">شماره‌گذاری اتوماتیک فاکتور</span>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1">در صورت فعال بودن، در صفحه ایجاد فاکتور، سیستم به صورت خودکار فرمت را تکمیل و قفل می‌کند.</span>
                    </div>
                    <div class="relative shrink-0">
                        <input type="hidden" name="invoice_auto" value="0">
                        <input type="checkbox" id="invoice_auto" name="invoice_auto"
                               value="1"
                               x-model="invoiceAuto"
                               class="sr-only peer">
                        <div
                            class="w-14 h-8 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors duration-300 shadow-inner"></div>
                        <div
                            class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                            <svg
                                class="w-3 h-3 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </x-resellerhub::card>

    <x-resellerhub::card>
        <div
            class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10">
            <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-inner">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                            </svg>
                        </span>
                تنظیمات مالیات پیش‌فرض
            </h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2 mr-15">این درصد مالیات به
                صورت
                اتوماتیک در فاکتورهای جدید لحاظ می‌گردد.</p>
        </div>
        <div class="p-6 md:p-8">
            <div class="max-w-md">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">درصد مالیات
                    (٪)</label>
                <div class="relative flex items-center">
                    <input type="number" name="default_tax_rate" min="0" max="100" step="0.01"
                           x-model="defaultTaxRate"
                           value="{{ $settings['default_tax_rate'] ?? 10 }}"
                           class="w-full rounded-2xl border-2 border-gray-200 bg-gray-50 px-5 py-4 text-lg font-black text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:focus:bg-gray-800 dark:focus:border-emerald-500 text-center dir-ltr shadow-sm">
                    <span
                        class="absolute right-5 text-gray-400 font-black text-lg pointer-events-none">%</span>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <button type="button" @click="setTaxRate(0)"
                            :class="Number(defaultTaxRate) === 0 ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                        ۰٪ (معاف)
                    </button>
                    <button type="button" @click="setTaxRate(9)"
                            :class="Number(defaultTaxRate) === 9 ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                        ۹٪
                    </button>
                    <button type="button" @click="setTaxRate(10)"
                            :class="Number(defaultTaxRate) === 10 ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                        ۱۰٪ (پیش‌فرض قانونی)
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-3 flex items-start gap-1.5 leading-relaxed">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    در صفحه ایجاد فاکتور، این مقدار را به‌صورت خودکار دریافت می‌کنید. همچنین امکان ویرایش
                    (تایپ
                    کردن) یا تغییر مقدار مالیات برای هر فاکتور را خواهید داشت.
                </p>
            </div>
        </div>
    </x-resellerhub::card>
    <input type="hidden" name="tax_mode" value="invoice">
    <x-resellerhub::card>
        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                واحد پول سیستم
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">واحد پولی پایه برای محاسبات و
                نمایش
                در تمام بخش‌های سیستم.</p>
        </div>

        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            <div class="flex flex-col">
                <x-resellerhub::label>انتخاب واحد</x-resellerhub::label>
                <div
                    class="flex items-center gap-2 p-1.5 rounded-xl bg-gray-100 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                    <button type="button"
                            @click="currency = 'toman'; $dispatch('currency-changed', { currency: 'toman' })"
                            :class="currency === 'toman' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                            class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200 cursor-pointer">
                        تومان (Toman)
                    </button>
                    <button type="button"
                            @click="currency = 'rial'; $dispatch('currency-changed', { currency: 'rial' })"
                            :class="currency === 'rial' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                            class="flex-1 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-200 cursor-pointer">
                        ریال (Rial)
                    </button>
                </div>
                <input type="hidden" name="currency" x-model="currency">
            </div>

            <div class="flex flex-col">
                <div class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1 invisible">
                    &nbsp;
                </div>
                <div
                    class="flex-1 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700/60 p-4 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span
                        x-text="currency === 'rial' ? 'تمامی مبالغ در سیستم بر اساس ریال محاسبه خواهند شد.' : 'تمامی مبالغ در سیستم بر اساس تومان محاسبه خواهند شد.'"></span>
                </div>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- Rounding Settings Card --}}
    <x-resellerhub::card
        x-data="resellerRoundingPreview('{{ $settings['reseller_rounding_mode'] ?? 'none' }}', {{ $settings['reseller_rounding_factor'] ?? (($settings['currency'] ?? 'toman') === 'rial' ? 10000 : 1000) }}, '{{ $settings['currency'] ?? 'toman' }}')"
        @currency-changed.window="onCurrencyChanged($event.detail.currency)">
        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                    تنظیمات رند کردن مبالغ
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">
                    نحوه رند شدن مبالغ کل فاکتورها و پرداخت‌های نمایندگی را مشخص کنید.
                </p>
            </div>
            {{-- Active Currency Badge --}}
            <div class="flex items-center gap-2 self-start sm:self-auto px-3.5 py-1.5 rounded-xl bg-purple-50/70 dark:bg-purple-900/30 border border-purple-100 dark:border-purple-800/40 text-xs font-bold text-purple-700 dark:text-purple-300">
                <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                <span>مبنای مالی جاری: </span>
                <span class="font-extrabold" x-text="currencyLabel"></span>
            </div>
        </div>

        <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            {{-- Controls Column --}}
            <div class="space-y-6 flex flex-col justify-between">
                <div>
                    <x-resellerhub::label>نوع رندسازی</x-resellerhub::label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 p-1.5 rounded-xl bg-gray-100 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <button type="button" @click="mode = 'none'; updatePreview()"
                                :class="mode === 'none' ? 'bg-white dark:bg-gray-800 shadow-sm text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="py-2.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200 text-center cursor-pointer">
                            بدون رندسازی
                        </button>
                        <button type="button" @click="mode = 'nearest'; updatePreview()"
                                :class="mode === 'nearest' ? 'bg-white dark:bg-gray-800 shadow-sm text-blue-600 dark:text-blue-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="py-2.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200 text-center cursor-pointer">
                            به نزدیک‌ترین
                        </button>
                        <button type="button" @click="mode = 'up'; updatePreview()"
                                :class="mode === 'up' ? 'bg-white dark:bg-gray-800 shadow-sm text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="py-2.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200 text-center cursor-pointer">
                            رو به بالا
                        </button>
                        <button type="button" @click="mode = 'down'; updatePreview()"
                                :class="mode === 'down' ? 'bg-white dark:bg-gray-800 shadow-sm text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="py-2.5 px-2 rounded-lg text-xs font-semibold transition-all duration-200 text-center cursor-pointer">
                            رو به پایین
                        </button>
                    </div>
                    <input type="hidden" name="reseller_rounding_mode" x-model="mode">
                </div>

                <div :class="mode === 'none' ? 'opacity-40 pointer-events-none transition-opacity' : 'transition-opacity'">
                    <div class="mb-2">
                        <x-resellerhub::label for="reseller_rounding_factor_input">
                            ضریب گرد کردن (<span x-text="currencyLabel"></span>)
                        </x-resellerhub::label>
                    </div>
                    <div class="flex items-stretch rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-500/10 dark:focus-within:border-indigo-500 dark:focus-within:ring-indigo-500/20 transition-all overflow-hidden shadow-2xs">
                        <input type="text" inputmode="numeric" id="reseller_rounding_factor_input"
                               x-model="factorFormatted" @input="onFactorInput($event)"
                               :placeholder="placeholderFactor"
                               class="flex-1 min-w-0 bg-transparent focus:bg-transparent dark:bg-transparent dark:focus:bg-transparent border-0 px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 focus:outline-hidden text-left dir-ltr"
                               dir="ltr">
                        <input type="hidden" name="reseller_rounding_factor" :value="factor">
                        <span class="inline-flex items-center px-4 bg-gray-100 dark:bg-gray-800/80 border-s border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 select-none shrink-0"
                              x-text="currencyLabel"></span>
                    </div>

                    {{-- Quick Presets --}}
                    <div class="mt-3">
                        <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                            <span>ضرایب پیشنهادی سریع:</span>
                            <span class="text-[10px] text-gray-400">کلیک برای انتخاب</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <template x-for="p in currentPresets" :key="p">
                                <button type="button" @click="setFactor(p)"
                                        :class="factor === p ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                        class="px-2.5 py-1 rounded-lg border text-xs font-bold transition-colors cursor-pointer">
                                    <span x-text="formatNumber(p)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preview Column --}}
            <div class="flex flex-col">
                <x-resellerhub::label>پیش‌نمایش زنده اثر رندسازی</x-resellerhub::label>
                <div class="flex-1 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700/60 p-5 flex flex-col justify-between space-y-4 text-sm">

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">مبلغ نمونه فاکتور:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-sm"
                                  x-text="formatMoney(sampleAmount)"></span>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-gray-200/60 dark:border-gray-700/60">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 dark:text-gray-400">مبلغ نهایی رند شده:</span>
                                <span class="text-[10px] text-gray-400" x-text="modeDescription"></span>
                            </div>
                            <span class="font-extrabold text-base dir-ltr" :class="colorClass"
                                  x-text="formatMoney(result)"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-3 border-t border-gray-200/40 dark:border-gray-700/40">
                            <span class="text-gray-500 dark:text-gray-400">تفاوت اثر مالی:</span>
                            <span class="font-bold dir-ltr text-xs"
                                  :class="diff > 0 ? 'text-emerald-600 dark:text-emerald-400' : (diff < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400')"
                                  x-text="formattedDiff"></span>
                        </div>
                    </div>

                    {{-- Explanatory Footer note in preview --}}
                    <div class="p-3 rounded-xl bg-white/80 dark:bg-gray-800/80 border border-gray-200/70 dark:border-gray-700/50 text-[11px] leading-relaxed">
                        <template x-if="mode === 'none'">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>رندسازی غیرفعال است؛ مبالغ دقیقاً مطابق محاسبات اقلام و مالیات صادر می‌شوند.</span>
                            </span>
                        </template>
                        <template x-if="mode !== 'none' && diff === 0">
                            <span class="text-gray-500 dark:text-gray-400">مبلغ نمونه فاکتور دقیقاً مضربی از ضریب انتخابی است و نیازی به کسر یا افزایش ندارد.</span>
                        </template>
                        <template x-if="mode !== 'none' && diff > 0">
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium">
                                مبلغ فاکتور <strong x-text="formatNumber(Math.abs(diff)) + ' ' + currencyLabel"></strong> به نفع نماینده افزایش می‌یابد.
                            </span>
                        </template>
                        <template x-if="mode !== 'none' && diff < 0">
                            <span class="text-rose-700 dark:text-rose-400 font-medium">
                                مبلغ فاکتور <strong x-text="formatNumber(Math.abs(diff)) + ' ' + currencyLabel"></strong> به عنوان تخفیف رندسازی به نفع مشتری کسر می‌گردد.
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </x-resellerhub::card>

    <x-resellerhub::card>
        <div
            class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </span>
                همگام‌سازی روش‌های پرداخت
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">اتصال به درگاه‌های آنلاین،
                حساب‌های
                بانکی و کارتخوان‌های تعریف‌شده در ماژول تنظیمات اصلی سیستم</p>
        </div>
        <div class="p-6 md:p-8">
            <label
                class="flex items-center justify-between gap-6 cursor-pointer group p-5 rounded-2xl border-2 border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/30 hover:border-emerald-200 dark:hover:border-emerald-500/30 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-all">
                <div class="flex-1">
                    <span class="text-base font-black text-gray-800 dark:text-gray-200 block">فراخوانی روش‌های پرداخت از تنظیمات سیستم</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 block mt-1.5 leading-relaxed">
                                با فعال‌سازی این گزینه، تمامی درگاه‌های آنلاین، حساب‌های بانکی، کارت به کارت و دستگاه‌های کارتخوان (POS) که در تنظیمات اصلی سیستم تعریف شده‌اند، به صورت خودکار در صفحه «ثبت پرداختی» فاکتورهای نمایندگی بارگذاری خواهند شد.
                            </span>
                </div>
                <div class="relative shrink-0">
                    <input type="hidden" name="reseller_use_global_payment_settings" value="0">
                    <input type="checkbox" id="reseller_use_global_payment_settings"
                           name="reseller_use_global_payment_settings" value="1"
                           x-model="useGlobalPayment" class="sr-only peer">
                    <div
                        class="w-14 h-8 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                    <div
                        class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                        <svg
                            class="w-3.5 h-3.5 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </label>
        </div>
    </x-resellerhub::card>
    <x-resellerhub::card>
        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                شرایط، قوانین و یادداشت پایین فاکتور
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">
                این متن به صورت یکپارچه در پاورقی فاکتور و نسخه چاپی برای مشتریان درج می‌شود.
            </p>
        </div>
        <div class="p-6 md:p-8 space-y-6">
            <div>
                <x-resellerhub::label for="invoice_terms">متن شرایط، قوانین و یادداشت فاکتور
                    (اختیاری)
                </x-resellerhub::label>
                <x-resellerhub::textarea class="resize-y" id="invoice_terms" name="invoice_terms" rows="5"
                                         maxlength="2000"
                                         x-model="invoiceTerms"
                                         placeholder="مثال: کلیه خدمات و دسترسی‌های سامانه پس از تسویه فاکتور فعال می‌گردند. از حسن انتخاب و اعتماد شما سپاسگزاریم.">{{ $settings['invoice_terms'] ?? ($settings['invoice_footer_note'] ?? '') }}</x-resellerhub::textarea>
                <p class="text-xs text-gray-400 mt-2">این متن به عنوان پاورقی و شرایط فاکتور در تمام نسخه‌ها
                    نمایش داده می‌شود.</p>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="text-xs text-gray-400">عبارات پیشنهادی:</span>
                    <button type="button"
                            @click="insertTermsPreset('کلیه خدمات و دسترسی‌ها پس از تسویه فاکتور فعال می‌گردند.')"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        + تسویه فاکتور
                    </button>
                    <button type="button"
                            @click="insertTermsPreset('مهلت پرداخت این فاکتور حداکثر ۷ روز کاری می‌باشد.')"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        + مهلت پرداخت ۷ روز
                    </button>
                    <button type="button" @click="insertTermsPreset('از حسن انتخاب و اعتماد شما سپاسگزاریم.')"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        + تشکر از اعتماد
                    </button>
                </div>
            </div>

            <div
                class="mt-6 p-5 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 bg-gradient-to-r from-emerald-50/70 via-teal-50/40 to-transparent dark:from-emerald-950/30 dark:via-teal-950/20 dark:to-transparent flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <span
                        class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </span>
                    <div>
                        <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">تعرفه و
                            قیمت‌گذاری ماژول‌ها و امکانات</h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">تعیین قیمت پایه
                            ماهانه، سالانه و راه‌اندازی هر ماژول در صفحه اختصاصی تعرفه‌ها مدیریت می‌شود.</p>
                    </div>
                </div>
                <a href="{{ route('reseller.pricings.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold transition shadow-sm shrink-0">
                    <span>ورود به مدیریت تعرفه ماژول‌ها</span>
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </x-resellerhub::card>

    <x-resellerhub::card>
        <div
            class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/50 to-transparent dark:from-indigo-900/10">
            <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 shadow-inner shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </span>
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        تنظیمات چاپ و خروجی فاکتور
                    </h2>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">
                        قالب پیش‌فرض چاپ (استاندارد یا رسمی امور مالیاتی) و جهت صفحه فاکتور را انتخاب کنید.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                <div>
                    <x-resellerhub::label for="reseller_print_mode">حالت چاپ پیش‌فرض</x-resellerhub::label>
                    <x-resellerhub::select id="reseller_print_mode" name="reseller_print_mode">
                        <option
                            value="standard" @selected(($settings['reseller_print_mode'] ?? 'standard') === 'standard')>
                            استاندارد (قالب مدرن شرکتی)
                        </option>
                        <option
                            value="official" @selected(($settings['reseller_print_mode'] ?? '') === 'official')>
                            رسمی (قالب استاندارد امور مالیاتی)
                        </option>
                    </x-resellerhub::select>
                    <p class="text-[11px] text-gray-400 mt-1.5">فاکتور هنگام کلیک روی دکمه پرینت در این قالب
                        باز خواهد شد.</p>
                </div>

                <div>
                    <x-resellerhub::label for="reseller_official_invoice_orientation">جهت چاپ
                        فاکتور رسمی
                    </x-resellerhub::label>
                    <x-resellerhub::select id="reseller_official_invoice_orientation"
                                           name="reseller_official_invoice_orientation">
                        <option
                            value="portrait" @selected(($settings['reseller_official_invoice_orientation'] ?? 'portrait') === 'portrait')>
                            عمودی (Portrait - A4)
                        </option>
                        <option
                            value="landscape" @selected(($settings['reseller_official_invoice_orientation'] ?? '') === 'landscape')>
                            افقی (Landscape - عریض دارایی)
                        </option>
                    </x-resellerhub::select>
                    <p class="text-[11px] text-gray-400 mt-1.5">جهت صفحه در هنگام چاپ یا ذخیره PDF قالب رسمی
                        اعمال می‌گردد.</p>
                </div>
            </div>

            {{-- Official Tax Identity Box --}}
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/60">
                <h3 class="text-sm font-black text-gray-800 dark:text-gray-200 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>اطلاعات هویتی و مالیاتی فروشنده (نماینده) در فاکتور رسمی</span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-5 leading-relaxed">
                    این مشخصات در سربرگ و بخش فروشنده فاکتور رسمی درج می‌شوند. هر فیلدی که خالی بماند، در
                    فاکتور نشان داده نمی‌شود.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <x-resellerhub::label>شماره اقتصادی فروشنده</x-resellerhub::label>
                        <x-resellerhub::input class="dir-ltr text-center" type="text"
                                              name="reseller_economic_number"
                                              value="{{ $settings['reseller_economic_number'] ?? '' }}"
                                              placeholder="مثال: ۴۱۱۴۵۷۸۹۳۲۱"
                        />
                    </div>
                    <div>
                        <x-resellerhub::label>شناسه ملی / کد ملی</x-resellerhub::label>
                        <x-resellerhub::input class="dir-ltr text-center" type="text"
                                              name="reseller_national_id"
                                              value="{{ $settings['reseller_national_id'] ?? '' }}"
                                              placeholder="مثال: ۱۰۱۰۳۴۵۶۷۸۹"
                        />
                    </div>
                    <div>
                        <x-resellerhub::label>شماره ثبت</x-resellerhub::label>
                        <x-resellerhub::input class="dir-ltr text-center" type="text"
                                              name="reseller_registration_number"
                                              value="{{ $settings['reseller_registration_number'] ?? '' }}"
                                              placeholder="مثال: ۳۹۴۸۲۵"
                        />
                    </div>
                    <div>
                        <x-resellerhub::label>کد پستی ۱۰ رقمی</x-resellerhub::label>
                        <x-resellerhub::input class="dir-ltr text-center" type="text"
                                              name="reseller_postal_code"
                                              value="{{ $settings['reseller_postal_code'] ?? '' }}"
                                              placeholder="مثال: ۱۹۹۸۸۷۶۵۴۳"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <x-resellerhub::label>نشانی کامل پستی فروشنده</x-resellerhub::label>
                        <x-resellerhub::input type="text" name="reseller_address"
                                              value="{{ $settings['reseller_address'] ?? '' }}"
                                              placeholder="تهران، خیابان ولیعصر، برج سپهر، طبقه ۴"
                        />
                    </div>
                </div>
            </div>

            {{-- Stamp Upload Section --}}
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center justify-between mb-2">
                    <label
                        class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span>بارگذاری تصویر مهر و امضای نماینده</span>
                    </label>
                    <span
                        class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400">
                                    PNG با پس‌زمینه شفاف (Transparent) پیشنهاد می‌شود
                                </span>
                </div>

                <input type="file"
                       x-ref="sealInput"
                       name="brand_seal_signature"
                       accept="image/*"
                       class="hidden"
                       @change="handleFileSelect($event, 'seal')">
                <input type="hidden" name="remove_brand_seal_signature" :value="removeSealFlag ? 1 : 0">

                <div class="flex items-center gap-3">
                    <div class="relative flex-1"
                         @dragover.prevent="isDraggingSeal = true"
                         @dragleave.prevent="isDraggingSeal = false"
                         @drop.prevent="handleDrop($event, 'seal')">
                        <label @click="$refs.sealInput.click()"
                               :class="isDraggingSeal ? 'border-amber-500 bg-amber-50/80 dark:bg-amber-950/50 ring-2 ring-amber-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-amber-500 dark:hover:border-amber-500 bg-gray-50 dark:bg-gray-900/50'"
                               class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                            <svg class="w-5 h-5 ml-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate"
                                  x-text="sealFileName ? (sealFileName + ' (' + sealFileSize + ')') : 'انتخاب یا رها کردن تصویر مهر و امضا...'"></span>
                        </label>
                    </div>

                    <div class="relative group shrink-0">
                        <div
                            class="w-14 h-14 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden shadow-2xs"
                            style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 6px 6px;">
                            <template x-if="sealPreview">
                                <img :src="sealPreview" alt="پیش‌نمایش مهر"
                                     class="max-w-full max-h-full object-contain mix-blend-multiply">
                            </template>
                            <template x-if="!sealPreview">
                                <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </template>
                        </div>
                        <template x-if="sealPreview">
                            <button type="button"
                                    @click.stop="removeFile('seal')"
                                    title="حذف مهر"
                                    class="absolute -top-1.5 -end-1.5 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer transition-transform hover:scale-110">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-1.5 px-1">
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">حداکثر ۲ مگابایت • با بک‌گراند
                        ترنسپرنت جهت نمایش تمیز در نسخه‌های چاپی</p>
                    <template x-if="sealPreview">
                        <button type="button" @click="removeFile('seal')"
                                class="text-[11px] text-rose-500 hover:text-rose-600 font-medium hover:underline cursor-pointer">
                            حذف تصویر مهر
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- 8. Stamp & Signature Display Size Settings (Identical to Services Module) --}}
    <x-resellerhub::card>
        <div
            class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-amber-50/50 via-transparent to-transparent dark:from-amber-900/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 shadow-inner shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        تنظیمات اندازه تصویر مهر و امضا در چاپ فاکتور
                    </h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">
                        ابعاد نمایش (عرض و ارتفاع بر حسب پیکسل) تصویر مهر و امضا را به‌صورت مجزا برای پرینت
                        استاندارد و رسمی تعیین کنید.
                    </p>
                </div>
            </div>

            <div class="shrink-0">
                <template x-if="sealPreview">
                    <div
                        class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>تصویر مهر و امضا بارگذاری شده است</span>
                    </div>
                </template>
                <template x-if="!sealPreview">
                    <div
                        class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 text-amber-700 dark:text-amber-400 text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>تصویر مهر هنوز بارگذاری نشده</span>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-6 md:p-8"
             x-data="stampSizePreview('{{ $settings['reseller_stamp_standard_width'] ?? '' }}', '{{ $settings['reseller_stamp_standard_height'] ?? '' }}', '{{ $settings['reseller_stamp_official_width'] ?? '' }}', '{{ $settings['reseller_stamp_official_height'] ?? '' }}')">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div
                    class="rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/20 p-5 md:p-6 flex flex-col justify-between space-y-6">
                    <div>
                        <div
                            class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-200/80 dark:border-gray-700/60">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </span>
                                <div>
                                    <h3 class="font-black text-sm text-gray-900 dark:text-white">پرینت
                                        استاندارد فاکتور</h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">قالب فاکتور شرکتی
                                        و چاپی استاندارد</p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            استاندارد
                                        </span>
                        </div>

                        {{-- Width & Height Inputs --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-resellerhub::label for="reseller_stamp_standard_width">عرض
                                    تصویر (پیکسل)
                                </x-resellerhub::label>
                                <div class="relative">
                                    <x-resellerhub::input class="dir-ltr text-center font-bold"
                                                          type="number" min="20" max="800" step="1"
                                                          id="reseller_stamp_standard_width"
                                                          name="reseller_stamp_standard_width"
                                                          x-model="stdWidth"
                                                          placeholder="خودکار (۱۳۰)"
                                    />
                                    <span
                                        class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">px</span>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">پیش‌فرض: ۱۳۰ پیکسل</span>
                            </div>

                            <div>
                                <x-resellerhub::label for="reseller_stamp_standard_height">ارتفاع
                                    تصویر (پیکسل)
                                </x-resellerhub::label>
                                <div class="relative">
                                    <x-resellerhub::input class="dir-ltr text-center font-bold"
                                                          type="number" min="20" max="600" step="1"
                                                          id="reseller_stamp_standard_height"
                                                          name="reseller_stamp_standard_height"
                                                          x-model="stdHeight"
                                                          placeholder="خودکار (۴۵)"
                                    />
                                    <span
                                        class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">px</span>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">پیش‌فرض: ۴۵ پیکسل</span>
                            </div>
                        </div>

                        {{-- Quick Preset Buttons --}}
                        <div class="mt-4">
                            <label
                                class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5">اندازه‌های
                                پیشنهادی:</label>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" @click="setStandard(100, 40)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-blue-400 hover:text-blue-600 transition-colors shadow-2xs cursor-pointer">
                                    کوچک (100×40)
                                </button>
                                <button type="button" @click="setStandard(130, 45)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-blue-400 hover:text-blue-600 transition-colors shadow-2xs cursor-pointer">
                                    استاندارد (130×45)
                                </button>
                                <button type="button" @click="setStandard(170, 65)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-blue-400 hover:text-blue-600 transition-colors shadow-2xs cursor-pointer">
                                    بزرگ (170×65)
                                </button>
                                <button type="button" @click="resetStandard()"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 transition-colors cursor-pointer">
                                    بازنشانی
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div
                            class="flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            پیش‌نمایش زنده در قالب استاندارد
                                        </span>
                            <span class="text-[10px] text-gray-400 font-mono dir-ltr"
                                  x-text="(stdWidth || 'خودکار') + ' × ' + (stdHeight || 'خودکار') + ' px'"></span>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-4 flex flex-col items-center justify-center min-h-[140px] shadow-inner">
                            <div class="text-center w-full max-w-[240px] mx-auto">
                                <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">مهر و
                                    امضای شرکت:</p>
                                <div class="flex items-center justify-center py-1 transition-all"
                                     :style="'min-height: ' + (stdHeight ? stdHeight + 'px' : '45px')">
                                    <template x-if="sealPreview">
                                        <img :src="sealPreview"
                                             alt="پیش‌نمایش مهر و امضا"
                                             :style="stdImgStyle">
                                    </template>
                                    <template x-if="!sealPreview">
                                        <div
                                            class="flex flex-col items-center justify-center py-2 text-gray-300 dark:text-gray-600">
                                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="1.2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span
                                                class="text-[10px] text-gray-400 mt-1">بدون تصویر مهر</span>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-[10px] font-bold text-gray-700 dark:text-gray-300 pt-1.5 border-t border-dashed border-gray-300 dark:border-gray-600 mt-1 truncate">
                                    {{ $reseller->company_name ?? $reseller->brand_title ?? 'فروشنده / شرکت' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/20 p-5 md:p-6 flex flex-col justify-between space-y-6">
                    <div>
                        <div
                            class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-200/80 dark:border-gray-700/60">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </span>
                                <div>
                                    <h3 class="font-black text-sm text-gray-900 dark:text-white">پرینت رسمی
                                        فاکتور</h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">قالب رسمی سازمان
                                        امور مالیاتی</p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                            رسمی
                                        </span>
                        </div>

                        {{-- Width & Height Inputs --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-resellerhub::label for="reseller_stamp_official_width">عرض
                                    تصویر (پیکسل)
                                </x-resellerhub::label>
                                <div class="relative">
                                    <x-resellerhub::input class="dir-ltr text-center font-bold"
                                                          type="number" min="20" max="800" step="1"
                                                          id="reseller_stamp_official_width"
                                                          name="reseller_stamp_official_width"
                                                          x-model="offWidth"
                                                          placeholder="خودکار (۱۴۰)"
                                    />
                                    <span
                                        class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">px</span>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">پیش‌فرض: ۱۴۰ پیکسل</span>
                            </div>

                            <div>
                                <x-resellerhub::label for="reseller_stamp_official_height">ارتفاع
                                    تصویر (پیکسل)
                                </x-resellerhub::label>
                                <div class="relative">
                                    <x-resellerhub::input class="dir-ltr text-center font-bold"
                                                          type="number" min="20" max="600" step="1"
                                                          id="reseller_stamp_official_height"
                                                          name="reseller_stamp_official_height"
                                                          x-model="offHeight"
                                                          placeholder="خودکار (۵۵)"
                                    />
                                    <span
                                        class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">px</span>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 block">پیش‌فرض: ۵۵ پیکسل</span>
                            </div>
                        </div>

                        {{-- Quick Preset Buttons --}}
                        <div class="mt-4">
                            <label
                                class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-1.5">اندازه‌های
                                پیشنهادی:</label>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" @click="setOfficial(110, 45)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-purple-400 hover:text-purple-600 transition-colors shadow-2xs cursor-pointer">
                                    کوچک (110×45)
                                </button>
                                <button type="button" @click="setOfficial(140, 55)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-purple-400 hover:text-purple-600 transition-colors shadow-2xs cursor-pointer">
                                    رسمی (140×55)
                                </button>
                                <button type="button" @click="setOfficial(180, 75)"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-purple-400 hover:text-purple-600 transition-colors shadow-2xs cursor-pointer">
                                    بزرگ (180×75)
                                </button>
                                <button type="button" @click="resetOfficial()"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 transition-colors cursor-pointer">
                                    بازنشانی
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div
                            class="flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            پیش‌نمایش زنده در قالب رسمی
                                        </span>
                            <span class="text-[10px] text-gray-400 font-mono dir-ltr"
                                  x-text="(offWidth || 'خودکار') + ' × ' + (offHeight || 'خودکار') + ' px'"></span>
                        </div>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-gray-600 p-3 min-h-[140px] shadow-inner">
                            <div class="grid grid-cols-2 gap-2 text-center text-[10px]">
                                <div
                                    class="flex flex-col justify-between items-center border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-gray-50/60 dark:bg-gray-900/30">
                                    <p class="font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-1 w-full">
                                        مهر و امضای فروشنده
                                    </p>
                                    <div
                                        class="flex-1 flex items-center justify-center py-1 w-full transition-all"
                                        :style="'min-height: ' + (offHeight ? offHeight + 'px' : '55px')">
                                        <template x-if="sealPreview">
                                            <img :src="sealPreview"
                                                 alt="پیش‌نمایش مهر و امضا"
                                                 :style="offImgStyle">
                                        </template>
                                        <template x-if="!sealPreview">
                                            <div
                                                class="flex flex-col items-center justify-center py-2 text-gray-300 dark:text-gray-600">
                                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span
                                                    class="text-[9px] text-gray-400 mt-1">بدون تصویر مهر</span>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-[9px] font-bold text-gray-500 dark:text-gray-400 pt-1 border-t border-dashed border-gray-300 dark:border-gray-700 w-full truncate">
                                        {{ $reseller->company_name ?? $reseller->brand_title ?? 'فروشنده' }}
                                    </p>
                                </div>
                                <div
                                    class="flex flex-col justify-between items-center border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-gray-50/60 dark:bg-gray-900/30">
                                    <p class="font-bold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-1 w-full">
                                        مهر و امضای خریدار
                                    </p>
                                    <div
                                        class="flex-1 flex items-center justify-center py-1 w-full transition-all"
                                        :style="'min-height: ' + (offHeight ? offHeight + 'px' : '55px')">
                                        <span class="text-[9px] text-gray-300 dark:text-gray-600 italic">محل امضای خریدار</span>
                                    </div>
                                    <p class="text-[9px] font-bold text-gray-500 dark:text-gray-400 pt-1 border-t border-dashed border-gray-300 dark:border-gray-700 w-full">
                                        تأیید و امضاء
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </x-resellerhub::card>
</div>
