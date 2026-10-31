<div x-show="dnsModalOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="dnsModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             @click="dnsModalOpen = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="dnsModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-start overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700/60 p-6 space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span
                        class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">راهنمای اتصال CNAME دامنه
                            اختصاصی</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="'تنظیمات اتصال دامنه ' + customDomain"></p>
                    </div>
                </div>
                <button type="button" @click="dnsModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    ✕
                </button>
            </div>

            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                جهت فعال‌سازی این دامنه اختصاصی برای مشتری، کافی است یک رکورد CNAME در سامانه مدیریت DNS دامنه
                (مانند Cloudflare، ابرآروان یا کنترل‌پنل دامنه) ثبت نمایید:
            </p>

            <div class="space-y-3">
                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <span
                            class="font-bold text-gray-800 dark:text-gray-200 text-xs">رکورد CNAME جهت اتصال مستقیم</span>
                        <span
                            class="text-xs px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-300 font-bold">CNAME</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div
                            class="p-2.5 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block mb-1">نوع رکورد / Record Type</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">CNAME</span>
                        </div>
                        <div
                            class="p-2.5 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-400 block mb-1">مقدار هدف / Target Value</span>
                            <div class="flex items-center justify-between dir-ltr">
                                <span class="font-bold text-gray-900 dark:text-white text-xs"
                                      x-text="cnameTarget"></span>
                                <button type="button"
                                        x-data="{ copied: false }"
                                        @click="copyText(cnameTarget, 'مقدار CNAME کپی شد'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="px-3 py-1 rounded-lg font-bold transition-all text-xs cursor-pointer flex items-center gap-1"
                                        :class="copied ? 'bg-emerald-600 text-white' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'">
                                    <template x-if="copied">
                                        <span class="flex items-center gap-1 font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>کپی شد!</span>
                                        </span>
                                    </template>
                                    <template x-if="!copied">
                                        <span>کپی</span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="dnsModalOpen = false"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition cursor-pointer">
                    متوجه شدم و بستن
                </button>
            </div>
        </div>
    </div>
</div>
