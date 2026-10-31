<div role="tabpanel" id="tabpanel-general" aria-labelledby="tab-general" x-show="activeTab === 'general'" x-cloak
     class="space-y-6 pb-24 relative transition-all"
     :class="{ 'z-50': defaultPackageDropdownOpen, 'z-10': !defaultPackageDropdownOpen }">
    <x-resellerhub::card class="p-6 sm:p-8 space-y-6 relative transition-all"
                         x-bind:class="{ 'z-50': defaultPackageDropdownOpen, 'z-10': !defaultPackageDropdownOpen }">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span
                class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">قوانین و پیش‌فرض‌های
                    اشتراک</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تنظیم مدت‌زمان اولیه سامانه‌ها،
                    رفتار
                    سیستم پس از انقضا و مهلت‌های ارفاقی</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1.5 relative transition-all"
                 :class="{ 'z-40': durationDropdownOpen, 'z-10': !durationDropdownOpen }">
                <x-resellerhub::label>
                    مدت‌زمان پیش‌فرض لایسنس اولیه
                </x-resellerhub::label>
                <input type="hidden" name="default_plan_duration_months" :value="defaultDuration">
                <div class="relative">
                    <button type="button" @click="durationDropdownOpen = !durationDropdownOpen"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 focus:border-indigo-500 transition-colors">
                        <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                            <span class="truncate font-bold" x-text="durationLabel"></span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                             :class="{ 'rotate-180': durationDropdownOpen }" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="durationDropdownOpen" @click.outside="durationDropdownOpen = false" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                         class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-1.5 space-y-1 ring-1 ring-black/5 dark:ring-white/10">
                        <template x-for="d in durationOptions" :key="d.value">
                            <div @click="defaultDuration = d.value; durationDropdownOpen = false"
                                 class="px-3 py-2 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between"
                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(defaultDuration) === String(d.value) }">
                                <span x-text="d.label"></span>
                                <span x-show="String(defaultDuration) === String(d.value)"
                                      class="text-indigo-600 dark:text-indigo-400 shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                            </div>
                        </template>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5 ms-1">در هنگام باز شدن ویزارد ساخت CRM، این مقدار به عنوان
                    انتخاب پیش‌فرض لایسنس اولیه خواهد بود.</p>
            </div>

            {{-- Max Advance Renewal Cycles --}}
            <div class="space-y-1.5 relative transition-all"
                 :class="{ 'z-40': renewalCyclesDropdownOpen, 'z-10': !renewalCyclesDropdownOpen }">
                <x-resellerhub::label>
                    حداکثر دوره‌های مجاز تمدید آتی
                </x-resellerhub::label>
                <input type="hidden" name="max_advance_renewal_cycles" :value="maxAdvanceRenewalCycles">
                <div class="relative">
                    <button type="button" @click="renewalCyclesDropdownOpen = !renewalCyclesDropdownOpen"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 focus:border-indigo-500 transition-colors">
                        <div class="flex items-center gap-2 truncate">
                            <span
                                class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </span>
                            <span class="truncate font-bold" x-text="renewalCyclesLabel"></span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                             :class="{ 'rotate-180': renewalCyclesDropdownOpen }" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="renewalCyclesDropdownOpen" @click.outside="renewalCyclesDropdownOpen = false" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                         class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-1.5 space-y-1 ring-1 ring-black/5 dark:ring-white/10">
                        <template x-for="c in renewalCyclesOptions" :key="c.value">
                            <div @click="maxAdvanceRenewalCycles = c.value; renewalCyclesDropdownOpen = false"
                                 class="px-3 py-2 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between"
                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(maxAdvanceRenewalCycles) === String(c.value) }">
                                <span x-text="c.label"></span>
                                <span x-show="String(maxAdvanceRenewalCycles) === String(c.value)"
                                      class="text-indigo-600 dark:text-indigo-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5 ms-1">حداکثر تعداد دوره‌هایی که مشتری در حین فعال بودن پلن،
                    می‌تواند پیشاپیش تمدید کند (جهت جلوگیری از تمدید متوالی بی‌رویه).</p>
            </div>
        </div>

        {{-- Custom Domain Section --}}
        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-3">
            <x-resellerhub::label>
                دامنه اختصاصی
            </x-resellerhub::label>
            <div
                class="p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40 space-y-3.5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="allow_custom_domains" value="1"
                           x-model="allowCustomDomains"
                           class="mt-1 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    <div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white block">اجازه اتصال دامنه اختصاصی (Custom Domain)</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">امکان اتصال دامنه اختصاصی توسط زیرمجموعه‌ها مانند clinic.ir به جای سابدامین اشتراکی.</span>
                    </div>
                </label>

                {{-- Custom Domain Pricing Configuration --}}
                <div x-show="allowCustomDomains" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="pt-3.5 border-t border-gray-200/60 dark:border-gray-700/60 space-y-3">
                    <div>
                                    <span
                                        class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        تعرفه و قیمت‌گذاری اتصال دامنه اختصاصی:
                                    </span>
                        <p class="text-xs text-gray-400 mt-0.5">
                            در صورت فعال بودن دامنه اختصاصی، این تعرفه به صورت تفکیک‌شده به فاکتور و
                            لایسنس
                            خریدار اضافه خواهد شد:
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Yearly Price --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                تعرفه سالانه ({{ $currencyLabel }})
                            </label>
                            <div
                                class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15 transition overflow-hidden">
                                <input type="text"
                                       x-model="customDomainYearlyDisplay"
                                       @input="customDomainYearlyPrice = $event.target.value.replace(/[^0-9]/g, ''); customDomainYearlyDisplay = customDomainYearlyPrice ? Number(customDomainYearlyPrice).toLocaleString('en-US') : ''"
                                       placeholder="۰"
                                       class="w-full px-3 py-2 text-xs font-bold text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 dir-ltr text-left">
                                <input type="hidden" name="custom_domain_yearly_price"
                                       :value="customDomainYearlyPrice">
                            </div>
                        </div>

                        {{-- Monthly Price --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                تعرفه ماهانه ({{ $currencyLabel }})
                            </label>
                            <div
                                class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15 transition overflow-hidden">
                                <input type="text"
                                       x-model="customDomainMonthlyDisplay"
                                       @input="customDomainMonthlyPrice = $event.target.value.replace(/[^0-9]/g, ''); customDomainMonthlyDisplay = customDomainMonthlyPrice ? Number(customDomainMonthlyPrice).toLocaleString('en-US') : ''"
                                       placeholder="۰"
                                       class="w-full px-3 py-2 text-xs font-bold text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 dir-ltr text-left">
                                <input type="hidden" name="custom_domain_monthly_price"
                                       :value="customDomainMonthlyPrice">
                            </div>
                        </div>

                        {{-- Setup Fee --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                هزینه راه‌اندازی اولیه ({{ $currencyLabel }})
                            </label>
                            <div
                                class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/15 transition overflow-hidden">
                                <input type="text"
                                       x-model="customDomainSetupDisplay"
                                       @input="customDomainSetupFee = $event.target.value.replace(/[^0-9]/g, ''); customDomainSetupDisplay = customDomainSetupFee ? Number(customDomainSetupFee).toLocaleString('en-US') : ''"
                                       placeholder="۰"
                                       class="w-full px-3 py-2 text-xs font-bold text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 dir-ltr text-left">
                                <input type="hidden" name="custom_domain_setup_fee"
                                       :value="customDomainSetupFee">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Secure DNS Instructions Card for Resellers --}}
                <div x-show="allowCustomDomains" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="pt-3 border-t border-gray-200/60 dark:border-gray-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        راهنمای امن اتصال دامنه جهت ارائه به مشتریان:
                                    </span>
                    </div>
                    <div
                        class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2 text-xs">
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-xs">
                            مشتری کافی است در پنل مدیریت دامنه خود (مانند Cloudflare، ابرآروان یا
                            هاستینگ)،
                            یک رکورد <strong>CNAME</strong> با مقدار هدف زیر ثبت نماید:
                        </p>
                        <div
                            class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg dir-ltr border border-gray-200/70 dark:border-gray-700/60">
                                        <span class="font-bold text-xs text-indigo-600 dark:text-indigo-400"
                                              x-text="cnameTarget"></span>
                            <button type="button"
                                    @click="copyToClipboard(cnameTarget, 'آدرس اتصال CNAME کپی شد')"
                                    class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 text-xs font-bold px-2.5 py-1 rounded-md bg-indigo-50 hover:bg-indigo-100/70 dark:bg-indigo-950/60 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                کپی مقدار هدف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Subdomain Pattern Configuration (User-Friendly & Interactive) --}}
        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
            <div class="flex items-center gap-3">
                <span
                    class="p-2.5 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-md shadow-indigo-500/20 rounded-2xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">الگوی نام‌گذاری خودکار آدرس سامانه
                        مشتریان</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        آدرس اختصاصی سامانه مشتریان پس از نام دامنه اصلی شما قرار می‌گیرد (مانند <span
                            class="text-indigo-600 dark:text-indigo-400 dir-ltr">domain.com/ali</span>).
                    </p>
                </div>
            </div>

            <div
                class="p-5 sm:p-6 rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 space-y-5">
                <div
                    class="p-4 rounded-2xl bg-gradient-to-r from-indigo-500/10 via-purple-500/5 to-transparent border border-indigo-200/80 dark:border-indigo-800/60 bg-white dark:bg-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-indigo-600/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">پیش‌نمایش زنده آدرس سامانه تولیدی:</span>
                            <div class="flex items-center gap-1.5 mt-0.5" dir="ltr">
                                <span class="text-xs sm:text-sm font-semibold text-gray-400">http://</span>
                                <span
                                    class="text-sm sm:text-base font-black text-indigo-600 dark:text-indigo-400 tracking-wide"
                                    x-text="subdomainPreview || (window.location.host + '/ali')"></span>
                            </div>
                        </div>
                    </div>
                    <span
                        class="self-start sm:self-center px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
                        آماده و بدون تداخل
                    </span>
                </div>

                {{-- Quick Presets / Templates --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        الگوهای آماده و پیشنهادی (یک‌کلیک برای انتخاب):
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                @click="setSubdomainPatternPreset('{name}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border"
                                :class="subdomainPattern === '{name}' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-600/25' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400'">
                            <span>نام ثبت‌نامی کاربر (توصیه‌شده):</span>
                            <code class="text-xs dir-ltr">{name}</code>
                        </button>
                        <button type="button"
                                @click="setSubdomainPatternPreset('{name}-{rand}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border"
                                :class="subdomainPattern === '{name}-{rand}' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-600/25' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400'">
                            <span>نام کاربر + عدد:</span>
                            <code class="text-xs dir-ltr">{name}-{rand}</code>
                        </button>
                        <button type="button"
                                @click="setSubdomainPatternPreset('crm-{rand}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border"
                                :class="subdomainPattern === 'crm-{rand}' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-600/25' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400'">
                            <span>استاندارد عددی:</span>
                            <code class="text-xs dir-ltr">crm-{rand}</code>
                        </button>
                        <button type="button"
                                @click="setSubdomainPatternPreset('app-{mobile_tail}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border"
                                :class="subdomainPattern === 'app-{mobile_tail}' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-600/25' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400'">
                            <span>شماره موبایل:</span>
                            <code class="text-xs dir-ltr">app-{mobile_tail}</code>
                        </button>
                    </div>
                </div>

                {{-- Interactive Input Field with Tag Insertion --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            فرمول الگو (امکان نوشتن دستی یا کلیک روی متغیرها):
                        </label>
                        <span class="text-xs text-gray-400">حروف انگلیسی، اعداد و خط‌تیره</span>
                    </div>

                    <div class="relative">
                        <input type="text"
                               name="subdomain_pattern"
                               x-model="subdomainPattern"
                               @input="updateSubdomainPreview()"
                               placeholder="{name}"
                               class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-sm font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition shadow-sm dir-ltr text-left">
                    </div>

                    {{-- Interactive Clickable Tags Pill List --}}
                    <div class="pt-2 space-y-2">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>برای افزودن به فرمول، روی متغیر دلخواه کلیک کنید:</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5">
                            <button type="button"
                                    @click="insertSubdomainTag('{name}')"
                                    class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 text-right transition cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <code
                                        class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold dir-ltr">{name}</code>
                                    <span
                                        class="text-xs text-indigo-600 opacity-0 group-hover:opacity-100 font-bold transition">+ درج</span>
                                </div>
                                <span
                                    class="block text-xs text-gray-500 dark:text-gray-400 mt-1">نام ثبت‌نامی کاربر</span>
                            </button>

                            <button type="button"
                                    @click="insertSubdomainTag('{rand}')"
                                    class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 text-right transition cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <code
                                        class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold dir-ltr">{rand}</code>
                                    <span
                                        class="text-xs text-indigo-600 opacity-0 group-hover:opacity-100 font-bold transition">+ درج</span>
                                </div>
                                <span
                                    class="block text-xs text-gray-500 dark:text-gray-400 mt-1">۶ رقم عدد تصادفی یکتا</span>
                            </button>

                            <button type="button"
                                    @click="insertSubdomainTag('{mobile_tail}')"
                                    class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 text-right transition cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <code
                                        class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold dir-ltr">{mobile_tail}</code>
                                    <span
                                        class="text-xs text-indigo-600 opacity-0 group-hover:opacity-100 font-bold transition">+ درج</span>
                                </div>
                                <span
                                    class="block text-xs text-gray-500 dark:text-gray-400 mt-1">۵ رقم آخر شماره موبایل</span>
                            </button>

                            <button type="button"
                                    @click="insertSubdomainTag('{alpha:5}')"
                                    class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 text-right transition cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <code
                                        class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold dir-ltr">{alpha:5}</code>
                                    <span
                                        class="text-xs text-indigo-600 opacity-0 group-hover:opacity-100 font-bold transition">+ درج</span>
                                </div>
                                <span
                                    class="block text-xs text-gray-500 dark:text-gray-400 mt-1">۵ حرف تصادفی انگلیسی</span>
                            </button>

                            <button type="button"
                                    @click="insertSubdomainTag('{date}')"
                                    class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 text-right transition cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <code
                                        class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold dir-ltr">{date}</code>
                                    <span
                                        class="text-xs text-indigo-600 opacity-0 group-hover:opacity-100 font-bold transition">+ درج</span>
                                </div>
                                <span
                                    class="block text-xs text-gray-500 dark:text-gray-400 mt-1">سال خورشیدی (۱۴۰۵)</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Security & Collision Notice --}}
                <div
                    class="p-3.5 rounded-2xl bg-gray-100/80 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700 flex items-start gap-2.5 text-xs text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <p class="leading-relaxed text-xs">
                        <strong>جلوگیری خودکار از تداخل نام:</strong> سامانه نام‌های رزرو شده (مانند admin, api, app) را
                        کنترل کرده و در صورت ثبت نام تکراری، به طور هوشمند شمارنده ترتیبی اضافه می‌کند تا هیچ‌گاه خطای
                        ایجاد دامین رخ ندهد.
                    </p>
                </div>
            </div>
        </div>

        {{-- Default Package Direct Registration Configuration --}}
        @php
            $moduleTitleResolver = app(\Modules\ResellerHub\App\Services\Support\ModuleTitleResolver::class);
            $packageDropdownList = ($resellerPackages ?? collect([]))->map(function ($pkg) use ($moduleTitleResolver) {
                $modulesList = is_array($pkg->modules) ? $pkg->modules : (json_decode($pkg->modules, true) ?: []);
                $persianModules = array_map(function ($slug) use ($moduleTitleResolver) {
                    return $moduleTitleResolver->resolve($slug);
                }, $modulesList);

                return [
                    'id' => (string) $pkg->id,
                    'name' => $pkg->name,
                    'is_free' => (bool) $pkg->isFree(),
                    'is_free_label' => $pkg->isFree() ? 'پلن رایگان' : 'پلن پولی',
                    'modules_count' => count($modulesList),
                    'modules_persian' => array_values($persianModules),
                    'modules_summary' => implode('، ', array_slice($persianModules, 0, 4)) . (count($persianModules) > 4 ? ' و...' : ''),
                ];
            })->values()->all();
        @endphp

        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
            <div class="flex items-center gap-3">
                <span
                    class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">پکیج و ماژول پیش‌فرض برای ثبت‌نام
                        مستقیم خریداران</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">امکان ثبت‌نام خریداران بدون خرید پکیج
                        پولی با انتساب خودکار پکیج یا ماژول‌های انتخابی</p>
                </div>
            </div>

            <div
                class="p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 space-y-4 relative transition-all"
                :class="{ 'z-50': defaultPackageDropdownOpen, 'z-10': !defaultPackageDropdownOpen }">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="enable_default_package_registration" value="1"
                           x-model="enableDefaultPackageRegistration"
                           class="mt-1 w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    <div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white block">فعال‌سازی ثبت‌نام بدون خرید پکیج پولی (ثبت‌نام با پکیج پیش‌فرض)</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">در صورت فعال بودن، کاربران جدید می‌توانند بدون پرداخت هزینه، با پکیج پیش‌فرض تعیین‌شده ثبت‌نام کرده و سامانه دریافت کنند.</span>
                    </div>
                </label>

                <div x-show="enableDefaultPackageRegistration" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="pt-4 border-t border-gray-200/60 dark:border-gray-700/60 space-y-3">
                    <div class="space-y-2 max-w-2xl relative"
                         :class="{ 'z-50': open, 'z-10': !open }"
                         x-data="{
                             open: false,
                             search: '',
                             packages: {{ Js::from($packageDropdownList) }},
                             get filteredPackages() {
                                 if (!this.search.trim()) return this.packages;
                                 const q = this.search.toLowerCase();
                                 return this.packages.filter(p =>
                                     (p.name && p.name.toLowerCase().includes(q)) ||
                                     (p.is_free_label && p.is_free_label.toLowerCase().includes(q)) ||
                                     (p.modules_persian && p.modules_persian.some(m => m.toLowerCase().includes(q)))
                                 );
                             },
                             select(pkg) {
                                 defaultPackageId = pkg ? String(pkg.id) : '';
                                 this.open = false;
                                 defaultPackageDropdownOpen = false;
                                 this.search = '';
                             },
                             getSelectedPackage() {
                                 return this.packages.find(p => String(p.id) === String(defaultPackageId)) || null;
                             },
                             getSelectedTitle() {
                                 const pkg = this.getSelectedPackage();
                                 if (!pkg) return 'لطفاً یک پکیج را انتخاب کنید...';
                                 return pkg.name;
                             },
                             toPersianDigits(num) {
                                 if (num === null || num === undefined) return '';
                                 return String(num).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                             }
                         }">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            انتخاب پکیج پیش‌فرض ثبت‌نام:
                        </label>

                        {{-- Hidden Form Input Bound to defaultPackageId --}}
                        <input type="hidden" name="default_package_id" :value="defaultPackageId">

                        {{-- Searchable Dropdown Container Matching Accounting Expenses Create UX --}}
                        <div class="relative" :class="{ 'z-50': open }">
                            <button type="button" @click="open = !open; defaultPackageDropdownOpen = open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 focus:border-indigo-500 transition-colors">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </span>
                                    <span class="truncate font-bold" x-text="getSelectedTitle()"></span>
                                    <template x-if="getSelectedPackage()">
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full border font-bold"
                                                  :class="getSelectedPackage().is_free ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50'"
                                                  x-text="getSelectedPackage().is_free_label"></span>
                                            <span class="text-[10px] text-gray-400 font-medium hidden sm:inline"
                                                  x-text="'(' + toPersianDigits(getSelectedPackage().modules_count) + ' ماژول)'"></span>
                                        </div>
                                    </template>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                     :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false; defaultPackageDropdownOpen = false" x-cloak
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[460px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search"
                                           placeholder="جستجوی نام پکیج، ماژول‌ها یا نوع پلن..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                </div>

                                {{-- Reset / None option --}}
                                <div @click="select(null)"
                                     class="px-3 py-2 text-xs rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/60 cursor-pointer text-gray-500 dark:text-gray-400 transition-colors flex items-center justify-between"
                                     :class="{ 'bg-gray-100 dark:bg-gray-700 font-bold text-gray-900 dark:text-white': !defaultPackageId }">
                                    <span>-- بدون انتخاب پکیج پیش‌فرض (غیرفعال) --</span>
                                </div>

                                <template x-for="pkg in filteredPackages" :key="pkg.id">
                                    <div @click="select(pkg)"
                                         class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors space-y-1.5"
                                         :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(defaultPackageId) === String(pkg.id) }">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="w-2 h-2 rounded-full"
                                                      :class="pkg.is_free ? 'bg-emerald-500' : 'bg-indigo-500'"></span>
                                                <span x-text="pkg.name"
                                                      class="font-bold truncate text-gray-900 dark:text-white"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="text-[10px] px-2 py-0.5 rounded-full border font-bold"
                                                      :class="pkg.is_free ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50'"
                                                      x-text="pkg.is_free_label"></span>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                                    x-text="toPersianDigits(pkg.modules_count) + ' ماژول'"></span>
                                            </div>
                                        </div>
                                        {{-- Persian module pills inside option --}}
                                        <div
                                            class="flex flex-wrap items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400">
                                            <span class="text-[10px] text-gray-400">ماژول‌ها:</span>
                                            <template x-for="(modName, idx) in pkg.modules_persian.slice(0, 5)"
                                                      :key="idx">
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-white/80 dark:bg-gray-900/60 border border-gray-200/70 dark:border-gray-700/60 text-gray-700 dark:text-gray-300"
                                                    x-text="modName"></span>
                                            </template>
                                            <span x-show="pkg.modules_persian.length > 5"
                                                  class="text-[10px] text-gray-400"
                                                  x-text="'+ ' + toPersianDigits(pkg.modules_persian.length - 5) + ' ماژول دیگر'"></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="filteredPackages.length === 0"
                                     class="p-3 text-xs text-gray-400 text-center">
                                    هیچ پکیجی با این مشخصات یافت نشد
                                </div>
                            </div>
                        </div>

                        {{-- Selected Package Rich Preview Card --}}
                        <template x-if="getSelectedPackage()">
                            <div
                                class="mt-2 p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>پکیج پیش‌فرض انتخاب‌شده:</span>
                                            <strong class="text-indigo-600 dark:text-indigo-400"
                                                    x-text="getSelectedPackage().name"></strong>
                                        </span>
                                    </div>
                                    <button type="button" @click="select(null)"
                                            class="text-[11px] text-rose-600 hover:text-rose-700 dark:text-rose-400 font-bold cursor-pointer">
                                        تغییر یا حذف انتخاب
                                    </button>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 block">ماژول‌های فعال در این پکیج:</span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <template x-for="(modName, idx) in getSelectedPackage().modules_persian"
                                                  :key="idx">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 shadow-xs"
                                                x-text="modName"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <p class="text-xs text-gray-400 mt-1">این پکیج به عنوان پکیج پایه اولیه به
                            خریدارانی که مستقیماً ثبت‌نام می‌کنند تخصیص داده خواهد شد.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60">
            <div
                class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-indigo-50/80 to-blue-50/40 dark:from-indigo-950/30 dark:to-blue-950/20 border border-indigo-100 dark:border-indigo-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div
                        class="p-2.5 rounded-xl bg-indigo-600 text-white shrink-0 shadow-sm shadow-indigo-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-black text-indigo-950 dark:text-indigo-200">
                            مدیریت خودکار تعلیق، مهلت ارفاقی و هشدارها توسط موتور گردش کار</h4>
                        <p class="text-xs text-indigo-800/80 dark:text-indigo-300/80 mt-1 leading-relaxed">
                            تعلیق خودکار سفارش‌ها در موعد سررسید فاکتور، مهلت ارفاقی ۷ روزه پیش از لغو قطعی،
                            و ارسال یادآوری‌های پیامکی به طور کامل و بدون تداخل توسط <strong>گردش کارهای
                                سامانه (Workflows)</strong> مدیریت و پردازش می‌شوند.
                        </p>
                    </div>
                </div>
                <button type="button" @click="activeTab = 'automation'"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-all text-xs font-bold shrink-0 self-start sm:self-center cursor-pointer">
                    <span>مشاهده گردش کارهای فعال</span>
                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </x-resellerhub::card>
</div>
