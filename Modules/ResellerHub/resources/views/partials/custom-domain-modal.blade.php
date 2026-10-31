<div x-show="domainModalOpen"
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
             @click="!domainLoading && closeDomainModal()"></div>

        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-center">

            {{-- Icon Badge --}}
            <div
                class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center shadow-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 shadow-indigo-500/20">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
            </div>

            {{-- Title & Subtitle --}}
            <div class="space-y-1.5">
                <h3 class="text-xl font-black text-gray-900 dark:text-white">اتصال و مدیریت دامنه اختصاصی</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    اتصال یا تغییر دامنه اختصاصی برای سامانه
                    <strong class="text-gray-900 dark:text-gray-100 font-bold" x-text="name || ''"></strong>
                </p>
            </div>

            {{-- Info Box: Subdomain & Target --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs space-y-2.5 text-start">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold">آدرس اولیه سامانه:</span>
                    <span dir="ltr" class="font-black text-indigo-600 dark:text-indigo-400"
                          x-text="subdomain || '—'"></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-gray-400 font-bold">وضعیت فعلی دامنه اختصاصی:</span>
                    <span class="font-bold"
                          :class="customDomain ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                          x-text="customDomain ? ('متصل به ' + customDomain) : 'هنوز تنظیم نشده'"></span>
                </div>
            </div>

            {{-- Domain Input Field --}}
            <div class="space-y-2 text-left">
                <label for="reseller-custom-domain" dir="rtl"
                       class="block text-xs font-bold text-gray-700 dark:text-gray-300 text-left">
                    آدرس دامنه اختصاصی مورد نظر:
                </label>
                <div dir="ltr"
                     class="flex items-stretch rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all shadow-xs">
                    <span
                        class="inline-flex items-center px-3.5 bg-gray-50 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 font-mono text-xs font-bold border-r border-gray-200 dark:border-gray-700 select-none">
                        https://
                    </span>
                    <input type="text"
                           id="reseller-custom-domain"
                           x-model="domainInput"
                           @keydown.enter.prevent="saveCustomDomain()"
                           dir="ltr"
                           placeholder="crm.company.com"
                           class="flex-1 min-w-0 px-3.5 py-3 text-sm font-bold bg-transparent text-gray-900 dark:text-white placeholder:text-gray-400 border-0 focus:ring-0 focus:outline-hidden text-left font-mono">
                </div>
                <p class="text-xs text-gray-400 leading-normal" dir="rtl">
                    پیشنهاد می‌شود از یک سابدامین روی دامنه برند زیرمجموعه (مانند <span
                        class="font-bold text-gray-600 dark:text-gray-300 dir-ltr">crm.yourbrand.ir</span>) استفاده
                    فرمایید.
                </p>
                <template x-if="domainError">
                    <p class="text-xs text-red-600 dark:text-red-400 font-bold" x-text="domainError"></p>
                </template>
            </div>

            {{-- CNAME Instructions Box --}}
            <div
                class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 text-start space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200">رکورد CNAME مورد نیاز در DNS دامنه</span>
                    </div>
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold">CNAME</span>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    در کنترل‌پنل مدیریت DNS دامنه زیرمجموعه (مثل Cloudflare یا ابرآروان)، یک رکورد CNAME با مقدار هدف زیر
                    ثبت فرمایید:
                </p>
                <div dir="ltr"
                     class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-indigo-200/60 dark:border-indigo-800/60">
                    <span class="font-mono text-xs font-bold text-gray-900 dark:text-white truncate"
                          x-text="cnameTarget"></span>
                    <button type="button"
                            x-data="{ copied: false }"
                            @click="copyText(cnameTarget, 'مقدار هدف CNAME کپی شد'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all cursor-pointer shrink-0 flex items-center gap-1.5 shadow-2xs"
                            :class="copied ? 'bg-emerald-600 text-white' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:hover:bg-indigo-900/80 dark:text-indigo-300'">
                        <template x-if="copied">
                            <span class="flex items-center gap-1 font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>کپی شد!</span>
                            </span>
                        </template>
                        <template x-if="!copied">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span>کپی</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            {{-- Disconnect Option (if domain currently exists) --}}
            <template x-if="customDomain">
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <template x-if="!domainRemoving">
                        <button type="button"
                                @click="domainRemoving = true"
                                class="text-xs font-bold text-red-500 hover:text-red-700 dark:text-red-400 transition cursor-pointer flex items-center justify-center gap-1.5 mx-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>قطع اتصال و حذف دامنه اختصاصی</span>
                        </button>
                    </template>
                    <template x-if="domainRemoving">
                        <div
                            class="p-3 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 space-y-2">
                            <p class="text-xs text-red-600 dark:text-red-300 font-bold">
                                آیا از حذف دامنه اختصاصی این سامانه اطمینان دارید؟
                            </p>
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                        @click="domainRemoving = false"
                                        class="px-3 py-1 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 cursor-pointer">
                                    انصراف
                                </button>
                                <button type="button"
                                        @click="removeCustomDomain()"
                                        :disabled="domainLoading"
                                        class="px-3 py-1 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition cursor-pointer flex items-center gap-1 disabled:opacity-50">
                                    <template x-if="domainLoading">
                                        <svg class="animate-spin w-3 h-3 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <span>بله، حذف دامنه</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Modal Action Buttons --}}
            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button"
                        @click="closeDomainModal()"
                        :disabled="domainLoading"
                        class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-bold transition-all w-1/2 flex items-center justify-center cursor-pointer disabled:opacity-50">
                    انصراف
                </button>
                <button type="button"
                        @click="saveCustomDomain()"
                        :disabled="domainLoading"
                        class="px-5 py-3 rounded-2xl text-white text-sm font-black transition-all shadow-md shadow-indigo-600/20 bg-indigo-600 hover:bg-indigo-700 active:scale-95 flex items-center justify-center gap-2 w-1/2 disabled:opacity-50 cursor-pointer">
                    <template x-if="domainLoading">
                        <svg class="animate-spin w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <template x-if="!domainLoading">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span x-text="domainLoading ? 'در حال ثبت...' : 'ذخیره و اتصال دامنه'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
