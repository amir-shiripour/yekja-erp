        <div x-show="createdTenant !== null" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-emerald-500/30 p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">سامانه CRM با موفقیت ساخته شد!</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">پایگاه داده اختصاصی، ماژول‌ها و اطلاعات
                        ورود
                        مدیر با موفقیت پیکربندی شدند.</p>
                </div>
            </div>

            <div
                class="bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-4 border border-gray-200/60 dark:border-gray-700/60 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">نام سامانه / کلینیک:</span>
                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"
                          x-text="createdTenant?.name"></span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">آدرس مستقیم ورود به سامانه:</span>
                    <div class="flex items-center gap-2">
                        <a :href="createdTenant?.url" target="_blank"
                           class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline dir-ltr"
                           x-text="createdTenant?.url"></a>
                        <button type="button" @click="copyUrl(createdTenant?.url)"
                                class="p-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition"
                                title="کپی آدرس">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                        <span x-show="copied" x-cloak class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">کپی شد!</span>
                    </div>
                </div>
            </div>

            <template x-if="createdInvoice">
                <div
                    class="bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl p-4 sm:p-5 border border-indigo-100 dark:border-indigo-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-indigo-900 dark:text-indigo-200">فاکتور صادرشده:</span>
                            <span
                                class="px-3 py-1 rounded-xl bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-900/60 text-sm font-black text-indigo-700 dark:text-indigo-300 tabular-nums tracking-wide"
                                x-text="createdInvoice.invoice_number"></span>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-black bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300"
                                x-text="createdInvoice.status_label"></span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            مبلغ کل:
                            <strong class="text-gray-900 dark:text-white font-black tabular-nums"
                                    x-text="createdInvoice.formatted_total + ' ' + currencyLabel"></strong>
                        </div>
                    </div>
                    <a x-show="createdInvoice.url" x-cloak :href="createdInvoice.url" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black transition-all active:scale-95 shadow-sm shadow-indigo-600/25 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        مشاهده فاکتور
                    </a>
                </div>
            </template>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a :href="createdTenant?.url" target="_blank"
                   class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                    <span>ورود به CRM</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <a :href="redirectUrl"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl transition">
                    مشاهده جزییات سامانه
                </a>

                <button type="button" @click="resetForm()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm font-medium rounded-xl transition border border-gray-200 dark:border-gray-700">
                    ساخت یک سامانه دیگر
                </button>
            </div>
        </div>
