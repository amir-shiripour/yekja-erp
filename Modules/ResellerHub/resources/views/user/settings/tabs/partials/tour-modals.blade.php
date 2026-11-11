    {{-- Delete Confirmation Modal --}}
    <template x-teleport="body">
        <div x-show="deleteStepModalOpen"
             x-cloak
             class="fixed inset-0 z-[99999] overflow-y-auto"
             role="dialog"
             aria-modal="true"
             dir="rtl">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-6">
                {{-- Backdrop (Behind Modal) --}}
                <div x-show="deleteStepModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-950/70 dark:bg-black/85 backdrop-blur-xs transition-opacity"
                     @click="deleteStepModalOpen = false"
                     aria-hidden="true"></div>

                {{-- Modal Card (In Front with relative z-10) --}}
                <div x-show="deleteStepModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative z-10 w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all border border-gray-200/80 dark:border-gray-700/80 p-6 sm:p-7 space-y-5 my-8">

                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-inner">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">حذف گام سفارشی</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">این عملیات مرحله را از لیست تور
                                آنبوردینگ حذف می‌کند.</p>
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                        آیا از حذف مرحله <span class="font-black text-rose-600 dark:text-rose-400"
                                               x-text="'«' + stepToDeleteTitle + '»'"></span> اطمینان دارید؟
                        <span class="block mt-1 text-xs text-gray-500 dark:text-gray-400">در صورت وابستگی سایر گام‌ها به این مرحله، پیش‌نیاز آن‌ها نیز به‌صورت خودکار برداشته خواهد شد.</span>
                    </div>

                    <div
                        class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700/80 gap-4">
                        <button type="button"
                                @click="deleteStepModalOpen = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button type="button"
                                @click="executeDeleteStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            <span>تأیید و حذف مرحله</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Alert Dialog Modal --}}
    <template x-teleport="body">
        <div x-show="alertModalOpen"
             x-cloak
             class="fixed inset-0 z-[99999] overflow-y-auto"
             role="dialog"
             aria-modal="true"
             dir="rtl">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-6">
                {{-- Backdrop (Behind Modal) --}}
                <div x-show="alertModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-950/70 dark:bg-black/85 backdrop-blur-xs transition-opacity"
                     @click="alertModalOpen = false"
                     aria-hidden="true"></div>

                {{-- Modal Card (In Front with relative z-10) --}}
                <div x-show="alertModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative z-10 w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all border border-gray-200/80 dark:border-gray-700/80 p-6 sm:p-7 space-y-4 my-8">

                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 shadow-inner"
                             :class="{
                                 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400': alertModalType === 'warning',
                                 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400': alertModalType === 'error',
                                 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400': alertModalType === 'info'
                             }">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white"
                                x-text="alertModalTitle"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed"
                               x-text="alertModalMessage"></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="button"
                                @click="alertModalOpen = false"
                                class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold transition-colors cursor-pointer">
                            متوجه شدم
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
