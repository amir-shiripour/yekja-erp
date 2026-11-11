@php
    $wizardBaseDomain = reseller_base_domain();
@endphp
<div x-show="currentStep === 1"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="p-6 sm:p-8 space-y-8">

    <div class="flex items-center gap-3 pb-1.5 dark:border-gray-700/60">
                <span
                    class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۱</span>
        <div>
            <h2 class="text-base font-black text-gray-900 dark:text-white">
                آدرس و ظاهر سامانه
            </h2>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-resellerhub::label>
                نام کسب‌وکار / کلینیک <span class="text-red-500 font-bold">*</span>
            </x-resellerhub::label>
            <x-resellerhub::input type="text" name="name" x-model="form.name" required
                                  placeholder="مثال: کلینیک تخصصی دکتر پارسا"
            />
            <template x-if="errors.name">
                <p class="text-xs text-red-500 mt-1.5" x-text="errors.name[0]"></p>
            </template>
        </div>
        <div>
            <x-resellerhub::label>
                شناسه و آدرس اختصاصی CRM (مسیر ورود) <span class="text-red-500 font-bold">*</span>
            </x-resellerhub::label>
            <div dir="ltr"
                 class="flex items-center rounded-xl bg-gray-50/70 dark:bg-gray-900/50 border overflow-hidden focus-within:ring-4 focus-within:ring-indigo-500/15 focus-within:border-indigo-500 transition"
                 :class="subdomainStatus === 'available' ? 'border-emerald-500 ring-1 ring-emerald-500/30' : (subdomainStatus === 'unavailable' ? 'border-red-500 ring-1 ring-red-500/30' : 'border-gray-200 dark:border-gray-700')">
                        <span
                            class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 select-none bg-gray-100 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 dir-ltr font-medium shrink-0">
                            {{ $wizardBaseDomain }}/
                        </span>
                <input type="text" name="subdomain" x-model="form.subdomain"
                       @input.debounce.400ms="checkSubdomain()" required placeholder="parsa-clinic"
                       class="flex-1 min-w-0 px-4 py-2.5 bg-transparent border-0 text-xs sm:text-sm text-left dir-ltr focus:ring-0">
            </div>

            <div class="mt-2 text-xs flex items-center gap-1.5">
                <template x-if="subdomainChecking">
                            <span class="text-gray-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                در حال بررسی آزاد بودن شناسه آدرس...
                            </span>
                </template>

                <template x-if="!subdomainChecking && subdomainStatus === 'available'">
                            <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="subdomainMsg"></span>
                            </span>
                </template>

                <template x-if="!subdomainChecking && subdomainStatus === 'unavailable'">
                            <span class="text-red-500 flex items-center gap-1.5 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="subdomainMsg"></span>
                            </span>
                </template>

                <template x-if="!subdomainChecking && subdomainStatus === 'idle'">
                            <span class="text-gray-400">
                                حروف کوچک انگلیسی و اعداد مجاز است (مثال: <span
                                    class="text-indigo-500 font-bold">clinic-shiraz</span>)
                            </span>
                </template>
            </div>

            <template x-if="errors.subdomain">
                <p class="text-xs text-red-500 mt-1.5" x-text="errors.subdomain[0]"></p>
            </template>
        </div>
        {{-- Custom Domain Section --}}
        <div
            class="md:col-span-2 space-y-3 p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/40 dark:bg-gray-900/30">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <x-resellerhub::label class="!mb-0 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <span>دامنه اختصاصی مشتری (Custom Domain)</span>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold">اختیاری</span>
                    </x-resellerhub::label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        اتصال آدرس وب‌سایت یا سابدامین اختصاصی مشتری (مانند <span
                            class="dir-ltr text-indigo-600 dark:text-indigo-400 font-bold">crm.clinic.ir</span>)
                        به جای آدرس اشتراکی سرور.
                    </p>
                </div>

                <button type="button" @click="dnsGuideOpen = !dnsGuideOpen"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 transition cursor-pointer self-start sm:self-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="dnsGuideOpen ? 'بستن راهنمای DNS' : 'راهنمای رکوردهای DNS'"></span>
                </button>
            </div>

            <template x-if="!allowsCustomDomains">
                <div
                    class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200/80 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>قابلیت دامنه اختصاصی در تنظیمات پنل نمایندگی شما غیرفعال است. جهت اتصال دامنه، ابتدا آن را از بخش تنظیمات فعال کنید.</span>
                </div>
            </template>

            {{-- Domain Input & Live Check Button --}}
            <div class="flex flex-col sm:flex-row items-stretch gap-2.5">
                <div class="relative flex-1">
                    <x-resellerhub::input class="text-left pl-10" type="text" name="custom_domain"
                                          x-model="form.custom_domain"
                                          @input.debounce.500ms="checkCustomDomain()"
                                          placeholder="crm.parsa-clinic.ir"
                                          dir="ltr"
                    />
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                </div>

                <button type="button" @click="checkCustomDomain()"
                        :disabled="domainChecking || !form.custom_domain"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl border border-indigo-200/60 dark:border-indigo-800/50 transition cursor-pointer disabled:opacity-50 shrink-0">
                    <svg x-show="domainChecking" class="w-3.5 h-3.5 animate-spin" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <svg x-show="!domainChecking" class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>بررسی اتصال DNS</span>
                </button>
            </div>

            {{-- Live Status Indicator --}}
            <div x-show="domainStatus !== 'idle' && form.custom_domain" x-cloak class="text-xs">
                <template x-if="domainChecking">
                            <span class="text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>در حال بررسی رکوردهای DNS و اعتبار دامنه...</span>
                            </span>
                </template>

                <template x-if="!domainChecking && domainStatus === 'verified'">
                    <div
                        class="p-3 bg-emerald-50/80 dark:bg-emerald-950/40 rounded-xl border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3">
                                <span class="flex items-center gap-1.5 font-bold">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="domainMsg"></span>
                                </span>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full bg-emerald-600 text-white font-bold shrink-0">DNS متصل است</span>
                    </div>
                </template>

                <template x-if="!domainChecking && domainStatus === 'pending'">
                    <div
                        class="p-3 bg-amber-50/80 dark:bg-amber-950/40 rounded-xl border border-amber-200/80 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1 space-y-1">
                            <span class="font-bold block" x-text="domainMsg"></span>
                        </div>
                    </div>
                </template>

                <template x-if="!domainChecking && domainStatus === 'unavailable'">
                    <div
                        class="p-3.5 bg-red-50/80 dark:bg-red-950/40 rounded-xl border border-red-200/80 dark:border-red-800/60 text-red-700 dark:text-red-300 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1 space-y-0.5">
                            <span class="font-bold block leading-relaxed" x-text="domainMsg"></span>
                        </div>
                    </div>
                </template>

                <template x-if="!domainChecking && domainStatus === 'available'">
                    <div
                        class="p-3 bg-blue-50/80 dark:bg-blue-950/40 rounded-xl border border-blue-200/80 dark:border-blue-800/60 text-blue-700 dark:text-blue-300 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-bold" x-text="domainMsg"></span>
                    </div>
                </template>
            </div>

            <template x-if="errors.custom_domain">
                <p class="text-xs text-red-500 mt-1" x-text="errors.custom_domain[0]"></p>
            </template>

            {{-- Secure DNS Configuration Guide (CNAME Only) --}}
            <div x-show="dnsGuideOpen" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-900/50 shadow-2xs space-y-3 text-xs">
                <div
                    class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                راهنمای تنظیم رکورد CNAME در کنترل‌پنل دامنه مشتری
                            </span>
                    <span class="text-xs text-gray-400">اتصال امن بدون افشای IP</span>
                </div>

                <div
                    class="p-3.5 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <span
                            class="font-bold text-gray-800 dark:text-gray-200 text-xs">رکورد CNAME جهت اتصال مستقیم</span>
                        <span
                            class="text-xs px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold">CNAME Record</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        در کنترل‌پنل هاستینگ یا سامانه مدیریت DNS دامنه مشتری، رکورد زیر را تنظیم فرمایید:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div
                            class="p-2.5 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block mb-1">نوع رکورد / Record Type</span>
                            <span
                                class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">CNAME</span>
                        </div>
                        <div
                            class="p-2.5 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block mb-1">مقدار هدف / Target Value</span>
                            <div class="flex items-center justify-between dir-ltr">
                                        <span class="font-bold text-gray-900 dark:text-white text-xs"
                                              x-text="cnameTarget"></span>
                                <button type="button"
                                        @click="copyToClipboard(cnameTarget, 'مقدار CNAME کپی شد')"
                                        class="text-indigo-600 hover:text-indigo-700 text-xs font-bold px-2 py-0.5 rounded hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition cursor-pointer">
                                    کپی
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div x-show="form.subdomain" x-cloak
         class="p-4 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-2xl border border-indigo-100 dark:border-indigo-900/40 text-xs sm:text-sm text-indigo-900 dark:text-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <span class="font-bold">آدرس مستقیم ورود مشتری به CRM:</span>
        <span class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr text-sm"
              x-text="'{{ request()->isSecure() ? 'https://' : 'http://' }}{{ $wizardBaseDomain }}/' + form.subdomain.toLowerCase().trim()"></span>
    </div>

    {{-- White-Label Branding Unified Card --}}
    <div
        class="rounded-3xl border border-gray-200/90 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/40 p-5 sm:p-7 space-y-6">
        {{-- Section Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-gray-200/70 dark:border-gray-700/60">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">ظاهر اختصاصی سامانه</h3>
                    </div>
                </div>
            </div>

        {{-- Mode Switchers --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-5">
                {{-- Option 1: Default Reseller Branding --}}
                <div tabindex="0" role="button" aria-label="ظاهر پیش‌فرض نماینده"
                     @click="whiteLabelType = 'default'"
                     :class="whiteLabelType === 'default'
                                 ? 'border-indigo-600 bg-white dark:bg-gray-800 shadow-md shadow-indigo-600/10 ring-2 ring-indigo-500/30'
                                 : 'border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/60 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-white dark:hover:bg-gray-800'"
                     class="relative p-3.5 sm:p-5 rounded-2xl border cursor-pointer transition-all duration-200 select-none group">
                    <div class="flex items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-center gap-3 sm:gap-3.5 min-w-0">
                            <div
                                :class="whiteLabelType === 'default' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'bg-gray-100 dark:bg-gray-700/80 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200'"
                                class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="flex items-center gap-2 min-w-0">
                                <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white whitespace-nowrap">ظاهر پیش‌فرض نماینده</h4>
                            </div>
                        </div>
                        <div
                            :class="whiteLabelType === 'default' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'"
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all">
                            <div x-show="whiteLabelType === 'default'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>
                </div>

                {{-- Option 2: Custom White-Label --}}
                <div tabindex="0" role="button" aria-label="ظاهر اختصاصی"
                     @click="whiteLabelType = 'custom'"
                     :class="whiteLabelType === 'custom'
                                 ? 'border-indigo-600 bg-white dark:bg-gray-800 shadow-md shadow-indigo-600/10 ring-2 ring-indigo-500/30'
                                 : 'border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/60 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-white dark:hover:bg-gray-800'"
                     class="relative p-3.5 sm:p-5 rounded-2xl border cursor-pointer transition-all duration-200 select-none group">
                    <div class="flex items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-center gap-3 sm:gap-3.5 min-w-0">
                            <div
                                :class="whiteLabelType === 'custom' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'bg-gray-100 dark:bg-gray-700/80 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200'"
                                class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>
                            <div class="flex items-center gap-2 min-w-0">
                                <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white whitespace-nowrap">ظاهر اختصاصی</h4>
                            </div>
                        </div>
                        <div
                            :class="whiteLabelType === 'custom' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'"
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all">
                            <div x-show="whiteLabelType === 'custom'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Custom White-Label Config Section --}}
        <div x-show="whiteLabelType === 'custom'" x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="pt-5 border-t border-gray-200/70 dark:border-gray-700/60 space-y-6">

            {{-- 1. Brand Identity Text Fields Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Brand Title --}}
                <div>
                    <x-resellerhub::label>
                        عنوان برند یا نام تجاری اختصاصی سامانه
                    </x-resellerhub::label>
                    <x-resellerhub::input type="text" x-model="whiteLabelBrandTitle"
                                          x-bind:placeholder="form.name ? ('پیش‌فرض: ' + form.name) : 'مثال: سامانه اختصاصی درمانگاه امید'"
                    />
                    <p class="text-xs text-gray-400 mt-1">این عنوان در عنوان تب مرورگر، هدر پرتال و
                        ایمیل‌های ارسالی درج می‌گردد.</p>
                </div>

                {{-- Footer Copyright --}}
                <div>
                    <x-resellerhub::label>
                        متن کپی‌رایت پاورقی (Footer Copyright)
                    </x-resellerhub::label>
                    <x-resellerhub::input type="text" x-model="whiteLabelFooterText"
                                          x-bind:placeholder="whiteLabelBrandTitle ? ('تمامی حقوق برای ' + whiteLabelBrandTitle + ' محفوظ است.') : 'مثال: تمامی حقوق این سامانه متعلق به کلینیک پارسا می‌باشد.'"
                    />
                </div>

                {{-- Support Phone --}}
                <div>
                    <x-resellerhub::label>
                        تلفن پشتیبانی مشتریان (پاورقی / تیکت‌ها)
                    </x-resellerhub::label>
                    <x-resellerhub::input class="dir-ltr text-left" type="text" x-model="whiteLabelSupportPhone"
                                          placeholder="مثال: ۰۲۱-۸۸۸۸۸۸۸۸"
                    />
                </div>

                {{-- Support Email --}}
                <div>
                    <x-resellerhub::label>
                        ایمیل پشتیبانی مشتریان
                    </x-resellerhub::label>
                    <x-resellerhub::input class="dir-ltr text-left" type="email" x-model="whiteLabelSupportEmail"
                                          placeholder="مثال: support@myclinic.com"
                    />
                </div>
            </div>

            {{-- 2. Modern Logo & Favicon Upload Zones (Matching Settings Page) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                {{-- Brand Logo Zone --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                            لوگوی اختصاصی برند
                        </label>
                        <span
                            class="text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                    انواع فرمت‌های تصویری
                                </span>
                    </div>

                    <!-- Hidden inputs -->
                    <input type="file"
                           x-ref="logoInput"
                           id="tenant_brand_logo"
                           name="tenant_brand_logo"
                           accept="image/*,.ico"
                           class="hidden"
                           @change="handleFileSelect($event, 'logo')">
                    <input type="hidden" name="remove_tenant_brand_logo" :value="removeLogo ? 1 : 0">

                    <div class="flex items-center gap-3">
                        <!-- Drag & Drop Zone -->
                        <div class="relative flex-1"
                             @dragover.prevent="isDraggingLogo = true"
                             @dragleave.prevent="isDraggingLogo = false"
                             @drop.prevent="handleDrop($event, 'logo')">
                            <label @click="$refs.logoInput.click()"
                                   :class="isDraggingLogo ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/50 ring-2 ring-indigo-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 bg-white dark:bg-gray-800'"
                                   class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                                <svg class="w-5 h-5 me-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate"
                                      x-text="logoFileName ? (logoFileName + ' (' + logoFileSize + ')') : 'انتخاب یا رها کردن فایل لوگو...'"></span>
                            </label>
                        </div>

                        <!-- Preview Box -->
                        <div class="relative group shrink-0">
                            <div
                                class="w-12 h-12 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-gray-50 dark:bg-gray-800/80 flex items-center justify-center overflow-hidden shadow-2xs">
                                <template x-if="whiteLabelLogoPreview">
                                    <img :src="whiteLabelLogoPreview" alt="پیش‌نمایش لوگو"
                                         class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="!whiteLabelLogoPreview">
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>
                            <template x-if="whiteLabelLogoPreview">
                                <button type="button"
                                        @click.stop="removeFile('logo')"
                                        title="حذف لوگو"
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
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            حداکثر ۲ مگابایت • تبدیل خودکار به فرمت بهینه WebP
                        </p>
                        <template x-if="whiteLabelLogoPreview">
                            <button type="button"
                                    @click="removeFile('logo')"
                                    class="text-xs text-rose-500 hover:text-rose-600 font-medium hover:underline cursor-pointer">
                                حذف لوگو
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Brand Favicon Zone --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                            فاوآیکون تب مرورگر (Favicon)
                        </label>
                        <span
                            class="text-xs font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                    فرمت ICO / PNG
                                </span>
                    </div>

                    <!-- Hidden inputs -->
                    <input type="file"
                           x-ref="faviconInput"
                           id="tenant_brand_favicon"
                           name="tenant_brand_favicon"
                           accept="image/x-icon,image/png,image/svg+xml,.ico"
                           class="hidden"
                           @change="handleFileSelect($event, 'favicon')">
                    <input type="hidden" name="remove_tenant_brand_favicon" :value="removeFavicon ? 1 : 0">

                    <div class="flex items-center gap-3">
                        <!-- Drag & Drop Zone -->
                        <div class="relative flex-1"
                             @dragover.prevent="isDraggingFavicon = true"
                             @dragleave.prevent="isDraggingFavicon = false"
                             @drop.prevent="handleDrop($event, 'favicon')">
                            <label @click="$refs.faviconInput.click()"
                                   :class="isDraggingFavicon ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/50 ring-2 ring-indigo-500/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 bg-white dark:bg-gray-800'"
                                   class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed rounded-xl cursor-pointer transition-colors text-xs sm:text-sm text-gray-500 dark:text-gray-400 select-none">
                                <svg class="w-5 h-5 me-2 text-gray-400 dark:text-gray-500 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="truncate"
                                      x-text="faviconFileName ? (faviconFileName + ' (' + faviconFileSize + ')') : 'انتخاب یا رها کردن فاوآیکون...'"></span>
                            </label>
                        </div>

                        <!-- Preview Box -->
                        <div class="relative group shrink-0">
                            <div
                                class="w-12 h-12 rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-gray-50 dark:bg-gray-800/80 flex items-center justify-center overflow-hidden shadow-2xs">
                                <template x-if="whiteLabelFaviconPreview">
                                    <img :src="whiteLabelFaviconPreview" alt="پیش‌نمایش فاوآیکون"
                                         class="w-6 h-6 object-contain">
                                </template>
                                <template x-if="!whiteLabelFaviconPreview">
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </template>
                            </div>
                            <template x-if="whiteLabelFaviconPreview">
                                <button type="button"
                                        @click.stop="removeFile('favicon')"
                                        title="حذف فاوآیکون"
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
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            فرمت ICO یا PNG • ابعاد استاندارد ۳۲×۳۲ پیکسل
                        </p>
                        <template x-if="whiteLabelFaviconPreview">
                            <button type="button"
                                    @click="removeFile('favicon')"
                                    class="text-xs text-rose-500 hover:text-rose-600 font-medium hover:underline cursor-pointer">
                                حذف فاوآیکون
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- 3. Color Palette & Spacious Live Mockup Window (Matching Settings Grid) --}}
            <div
                class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-4 border-t border-gray-200/70 dark:border-gray-700/60">
                {{-- Left: Color Picker & Preset Palettes (5 Columns) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div>
                        <label
                            class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1">
                            رنگ سازمانی و تم اصلی سامانه
                        </label>
                        <div class="flex items-center gap-3">
                            <div
                                class="relative flex items-center justify-center w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 shrink-0 overflow-hidden shadow-2xs cursor-pointer group">
                                <input type="color" x-model="whiteLabelPrimaryColor"
                                       class="absolute -inset-2 w-16 h-16 cursor-pointer border-0 p-0">
                            </div>
                            <input type="text" x-model="whiteLabelPrimaryColor"
                                   placeholder="#4f46e5"
                                   class="w-32 rounded-xl border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-white uppercase focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dir-ltr text-center">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">کد رنگ HEX را وارد کنید یا یکی از رنگ‌های
                            استاندارد زیر را برگزینید.</p>
                    </div>

                    {{-- Standard Color Presets --}}
                    <div class="space-y-2 pt-2">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">پالت رنگ‌های محبوب و استاندارد</span>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="preset in whiteLabelColorPresets" :key="preset.hex">
                                <button type="button" @click="whiteLabelPrimaryColor = preset.hex"
                                        :class="whiteLabelPrimaryColor.toLowerCase() === preset.hex.toLowerCase() ? 'ring-2 ring-indigo-500 dark:ring-indigo-400 border-transparent shadow-xs' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                                        class="flex items-center gap-2 p-2 rounded-xl border bg-white dark:bg-gray-800 text-right transition cursor-pointer group">
                                            <span
                                                class="w-4 h-4 rounded-full shrink-0 shadow-2xs transition-transform group-hover:scale-110"
                                                :style="'background-color: ' + preset.hex"></span>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate"
                                        x-text="preset.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    
                </div>

                {{-- Right: Live Interactive Mockup Window (7 Columns) --}}
                <div class="lg:col-span-7">
                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-900/90 text-white shadow-xl overflow-hidden">
                        {{-- Mock Browser Chrome --}}
                        <div
                            class="px-3 sm:px-4 py-2 sm:py-3 bg-gray-800/90 border-b border-gray-700/80 flex items-center justify-between gap-2 sm:gap-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                            </div>
                            <div
                                class="flex-1 max-w-[160px] sm:max-w-xs mx-auto bg-gray-900/80 rounded-lg px-2 sm:px-3 py-1 text-[11px] sm:text-xs text-gray-400 text-center truncate border border-gray-700/60 dir-ltr flex items-center justify-center gap-1.5">
                                <template x-if="whiteLabelFaviconPreview">
                                    <img :src="whiteLabelFaviconPreview"
                                         class="w-3 h-3 object-contain shrink-0">
                                </template>
                                <span class="truncate"
                                      x-text="form.custom_domain ? ('https://' + form.custom_domain) : ('https://{{ $wizardBaseDomain }}/' + (form.subdomain || 'crm'))"></span>
                            </div>
                            <span
                                class="text-[10px] sm:text-xs font-bold px-1.5 sm:px-2 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-800/50 shrink-0">پیش‌نمایش</span>
                        </div>

                        {{-- Mock Portal Interface --}}
                        <div
                            class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 p-2.5 sm:p-4 min-h-[260px] sm:min-h-[300px] flex gap-2 sm:gap-3 text-xs overflow-hidden">
                            {{-- Mock Sidebar --}}
                            <div
                                class="w-11 sm:w-44 bg-white dark:bg-gray-800 rounded-xl p-2 sm:p-3 border border-gray-200/80 dark:border-gray-700/80 space-y-2 sm:space-y-3 shrink-0 flex flex-col justify-between shadow-2xs">
                                <div class="space-y-3">
                                    {{-- Mock Brand Logo / Title --}}
                                    <div
                                        class="flex items-center gap-2 pb-2.5 border-b border-gray-100 dark:border-gray-700">
                                        <template x-if="whiteLabelLogoPreview">
                                            <img :src="whiteLabelLogoPreview"
                                                 class="w-6 h-6 object-contain rounded shrink-0">
                                        </template>
                                        <template x-if="!whiteLabelLogoPreview">
                                                    <span
                                                        class="w-6 h-6 rounded flex items-center justify-center font-black text-white text-xs shrink-0"
                                                        :style="'background-color: ' + whiteLabelPrimaryColor">
                                                        <span
                                                            x-text="(whiteLabelBrandTitle || form.name || 'P').charAt(0)"></span>
                                                    </span>
                                        </template>
                                        <span
                                            class="hidden sm:inline font-black text-xs text-gray-900 dark:text-white truncate"
                                            x-text="whiteLabelBrandTitle || form.name || 'سامانه اختصاصی'"></span>
                                    </div>

                                    {{-- Mock Navigation Items --}}
                                    <div class="space-y-1">
                                        {{-- Active Item in Brand Color --}}
                                        <div
                                            class="px-2 sm:px-2.5 py-1.5 rounded-lg font-bold text-xs text-white flex items-center justify-center sm:justify-start gap-2 shadow-2xs"
                                            :style="'background-color: ' + whiteLabelPrimaryColor">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                            <span class="hidden sm:inline">پیشخوان</span>
                                        </div>
                                        <div
                                            class="px-2 sm:px-2.5 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center sm:justify-start gap-2">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <span class="hidden sm:inline">پرونده‌ها</span>
                                        </div>
                                        <div
                                            class="px-2 sm:px-2.5 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center sm:justify-start gap-2">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="hidden sm:inline">نوبت‌دهی</span>
                                        </div>
                                        <div
                                            class="px-2 sm:px-2.5 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center sm:justify-start gap-2">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            <span class="hidden sm:inline">گزارش‌ها</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Mock Support Badge --}}
                                <div
                                    class="p-1 sm:p-2 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-[10px] text-gray-400 dark:text-gray-400 truncate text-center">
                                    <span class="hidden sm:inline">پشتیبانی: </span>
                                    <span dir="ltr"
                                          x-text="whiteLabelSupportPhone || '۰۲۱...' "></span>
                                </div>
                            </div>

                            {{-- Mock Main Content Area --}}
                            <div class="flex-1 min-w-0 space-y-2 sm:space-y-3">
                                {{-- Mock Header Bar --}}
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-xl p-2 sm:p-3 border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="min-w-0">
                                        <div
                                            class="font-black text-gray-900 dark:text-white text-xs truncate"
                                            x-text="whiteLabelBrandTitle || form.name || 'سامانه اختصاصی'"></div>
                                        <div
                                            class="text-[10px] sm:text-xs text-gray-400 truncate hidden xs:block">
                                            خوش‌آمدید
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-white font-bold text-[10px] sm:text-xs shadow-2xs cursor-default"
                                                    :style="'background-color: ' + whiteLabelPrimaryColor">
                                                    <svg class="w-3 h-3 shrink-0" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <span class="hidden xs:inline sm:inline">ثبت نوبت</span>
                                                </span>
                                        <div
                                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gray-200 dark:bg-gray-700 shrink-0"></div>
                                    </div>
                                </div>

                                {{-- Mock Stat Cards --}}
                                <div class="grid grid-cols-2 gap-1.5 sm:gap-2">
                                    <div
                                        class="p-2 sm:p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 space-y-0.5 sm:space-y-1 min-w-0">
                                                    <span
                                                        class="text-[10px] sm:text-xs text-gray-400 block truncate">بیماران امروز</span>
                                        <div class="flex items-center justify-between">
                                                        <span
                                                            class="font-bold text-gray-900 dark:text-white text-[11px] sm:text-xs">۲۴ نفر</span>
                                            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full shrink-0"
                                                  :style="'background-color: ' + whiteLabelPrimaryColor"></span>
                                        </div>
                                    </div>
                                    <div
                                        class="p-2 sm:p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 space-y-0.5 sm:space-y-1 min-w-0">
                                                    <span
                                                        class="text-[10px] sm:text-xs text-gray-400 block truncate">نوبت‌های فعال</span>
                                        <div class="flex items-center justify-between">
                                                        <span
                                                            class="font-bold text-gray-900 dark:text-white text-[11px] sm:text-xs">۵ نوبت</span>
                                            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Mock Footer --}}
                                <div
                                    class="pt-1.5 sm:pt-2 text-center text-[10px] sm:text-xs text-gray-400 border-t border-gray-200/60 dark:border-gray-800 truncate"
                                    x-text="whiteLabelFooterText || ('تمامی حقوق متعلق به ' + (whiteLabelBrandTitle || form.name || 'سامانه') + ' است.')"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-4 sm:pt-6 border-t border-gray-100 dark:border-gray-700/60 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 sticky bottom-0 sm:static bg-white/95 dark:bg-gray-800/95 backdrop-blur-md -mx-4 sm:mx-0 px-4 sm:px-0 py-3 sm:py-0 z-20">
        <span class="text-xs text-gray-400 text-center sm:text-right">مرحله ۱ از ۳: آدرس و ظاهر سامانه</span>
        <button type="button" @click="nextStep()"
                :disabled="!form.name || !form.subdomain || subdomainStatus === 'unavailable'"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-black rounded-xl transition shadow-lg shadow-indigo-600/25 cursor-pointer">
            <span>مرحله بعدی: اطلاعات کاربری سامانه</span>
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
    </div>
</div>
