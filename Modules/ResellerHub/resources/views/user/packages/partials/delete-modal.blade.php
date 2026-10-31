<div x-show="deleteModalOpen" x-cloak
     class="fixed inset-0 z-[9999] overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="closeDeleteModal()"
     role="dialog"
     aria-modal="true"
     dir="rtl">

    <div class="flex items-center justify-center min-h-screen w-full px-4 py-8 text-center sm:p-0">
        {{-- Fullscreen Backdrop covering 100% of viewport with blur --}}
        <div class="fixed inset-0 w-full h-full bg-gray-950/75 dark:bg-black/85 backdrop-blur-sm transition-opacity"
             @click="closeDeleteModal()"
             aria-hidden="true"></div>

        {{-- Modal Dialog Card (Minimal, Chic border, no thick colored top line) --}}
        <div @click.stop="$event.stopPropagation()"
             class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 text-center space-y-5 my-8"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- STATE 1: SERVER ERROR OR BLOCKED REASON --}}
            <template x-if="deleteErrorMsg">
                <div class="space-y-4">
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-inner border border-rose-100 dark:border-rose-900/40">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">عدم امکان انجام
                            عملیات</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-normal">خطایی در فرآیند حذف
                            رخ داد:</p>
                    </div>
                    <div
                        class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-xs sm:text-sm text-rose-900 dark:text-rose-200 text-start leading-relaxed font-medium"
                        x-text="deleteErrorMsg"></div>
                    <div class="pt-2">
                        <button type="button" @click="closeDeleteModal()"
                                class="w-full py-3 px-5 rounded-2xl bg-gray-900 hover:bg-black dark:bg-gray-800 dark:hover:bg-gray-700 text-white text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer shadow-md">
                            متوجه شدم
                        </button>
                    </div>
                </div>
            </template>

            {{-- STATE 2: BLOCKED DUE TO ACTIVE TENANTS (PROACTIVE MODAL WARNING) --}}
            <template x-if="!deleteErrorMsg && (packageToDelete?.tenants_count > 0)">
                <div class="space-y-4">
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-inner border border-amber-100 dark:border-amber-900/40">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="space-y-1.5">
                        <div
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span x-text="faNum(packageToDelete?.tenants_count || 0) + ' سامانه فعال متصل'"></span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">امکان حذف این پکیج
                            وجود ندارد</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                            پکیج <span class="font-bold text-gray-900 dark:text-white"
                                       x-text="'«' + (packageToDelete?.name || '') + '»'"></span> در حال حاضر دارای
                            <span class="font-black text-amber-700 dark:text-amber-300"
                                  x-text="faNum(packageToDelete?.tenants_count || 0) + ' سامانه فعال'"></span> است.
                        </p>
                    </div>

                    {{-- Solution Card --}}
                    <div
                        class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 text-xs text-amber-900 dark:text-amber-200 text-start leading-relaxed space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>راهکار ایمن پیشنهادی:</span>
                        </div>
                        <p>جهت جلوگیری از قطع دسترسی و اختلال در عملکرد مشتریان، امکان حذف فیزیکی وجود ندارد؛ اما
                            می‌توانید این پکیج را به حالت <strong>غیرفعال</strong> تغییر دهید تا از لیست سفارشات جدید
                            خارج شود و مشتریان قبلی بدون تغییر به کار خود ادامه دهند.</p>
                    </div>

                    {{-- Actions: Structured Vertical Hierarchy for maximum clarity & elegance --}}
                    <div class="space-y-2.5 pt-2">
                        <template x-if="packageToDelete?.is_active">
                            <button type="button" @click="closeDeleteModal(); openStatusModal(packageToDelete)"
                                    class="w-full py-3 px-5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 active:scale-[0.99] text-white text-xs sm:text-sm font-black shadow-lg shadow-amber-500/25 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>غیرفعال‌سازی سریع این پکیج (پیشنهادی)</span>
                            </button>
                        </template>

                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button" @click="closeDeleteModal()"
                                    class="py-2.5 px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer flex items-center justify-center">
                                انصراف
                            </button>
                            <a :href="'/reseller/packages/' + packageToDelete?.id + '/edit'"
                               class="py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>ویرایش پکیج</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            {{-- STATE 3: CONFIRM DELETE (WHEN ZERO TENANTS) --}}
            <template x-if="!deleteErrorMsg && !(packageToDelete?.tenants_count > 0)">
                <div class="space-y-5">
                    {{-- Danger Icon Badge --}}
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-inner border border-rose-100 dark:border-rose-900/40">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">تأیید حذف پکیج
                            ماژول</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                            آیا از حذف پکیج <span class="font-bold text-gray-900 dark:text-white"
                                                  x-text="'«' + (packageToDelete?.name || '') + '»'"></span> اطمینان
                            دارید؟
                        </p>
                    </div>

                    {{-- Target Item Card --}}
                    <div
                        class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 flex items-center gap-3 text-start">
                        <span
                            class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-xs"
                            :style="`background-color: ${getColorHex(packageToDelete?.color)}; color: #ffffff;`"
                            x-html="renderIcon(packageToDelete?.icon, 'w-5 h-5')"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate"
                                 x-text="packageToDelete?.name"></div>
                            <div class="text-xs text-gray-400 mt-0.5"
                                 x-text="faNum(packageToDelete?.modules_count ?? (packageToDelete?.modules?.length || 0)) + ' ماژول فعال در این پکیج'"></div>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/40 p-3 rounded-2xl border border-gray-200 dark:border-gray-700/60 leading-relaxed text-start">
                        این پکیج فاقد سامانه فعال متصل است و پس از حذف، از لیست پکیج‌های شما خارج خواهد شد.
                    </p>

                    {{-- Action Buttons: Symmetrical 50/50 Grid --}}
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button" @click="closeDeleteModal()" :disabled="isDeleting"
                                class="py-3 px-4 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-[0.99] transition-all cursor-pointer disabled:opacity-50">
                            انصراف
                        </button>
                        <button type="button" @click="confirmDelete()" :disabled="isDeleting"
                                class="py-3 px-4 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 active:scale-[0.99] text-white text-xs sm:text-sm font-black shadow-lg shadow-rose-600/25 transition-all cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50">
                            <template x-if="isDeleting">
                                <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!isDeleting">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </template>
                            <span>بله، پکیج حذف شود</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
