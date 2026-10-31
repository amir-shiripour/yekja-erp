<div x-show="statusModalOpen" x-cloak
     class="fixed inset-0 z-[9999] overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-250"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="closeStatusModal()"
     role="dialog"
     aria-modal="true"
     dir="rtl">

    <div class="flex items-center justify-center min-h-screen w-full px-4 py-8 text-center sm:p-0">
        {{-- Fullscreen Backdrop covering 100% of viewport with blur --}}
        <div class="fixed inset-0 w-full h-full bg-gray-950/75 dark:bg-black/85 backdrop-blur-sm transition-opacity"
             @click="closeStatusModal()"
             aria-hidden="true"></div>

        {{-- Modal Dialog Card (Minimal, Chic border, harmonious styling) --}}
        <div @click.stop="$event.stopPropagation()"
             class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 text-center space-y-5 my-8"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- ERROR STATE --}}
            <template x-if="statusErrorMsg">
                <div class="space-y-4">
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-inner border border-rose-100 dark:border-rose-900/40">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">خطا در تغییر
                            وضعیت</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-normal">امکان به‌روزرسانی
                            وضعیت این پکیج وجود ندارد:</p>
                    </div>
                    <div
                        class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-xs sm:text-sm text-rose-900 dark:text-rose-200 text-start leading-relaxed font-medium"
                        x-text="statusErrorMsg"></div>
                    <div class="pt-2">
                        <button type="button" @click="closeStatusModal()"
                                class="w-full py-3 px-5 rounded-2xl bg-gray-900 hover:bg-black dark:bg-gray-800 dark:hover:bg-gray-700 text-white text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer shadow-md">
                            متوجه شدم
                        </button>
                    </div>
                </div>
            </template>

            {{-- CONFIRM TOGGLE STATUS FORM --}}
            <template x-if="!statusErrorMsg && packageToToggle">
                <div class="space-y-5">
                    {{-- Icon Badge: Amber for Deactivation, Emerald for Activation --}}
                    <div>
                        <template x-if="packageToToggle.is_active">
                            <div
                                class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-inner border border-amber-100 dark:border-amber-900/40">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </template>
                        <template x-if="!packageToToggle.is_active">
                            <div
                                class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-inner border border-emerald-100 dark:border-emerald-900/40">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Title & Subtitle --}}
                    <div class="space-y-1.5">
                        <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white"
                            x-text="packageToToggle.is_active ? 'تأیید غیرفعال‌سازی پکیج' : 'تأیید فعال‌سازی پکیج'"></h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                            پکیج انتخابی: <span class="font-bold text-gray-900 dark:text-white"
                                                x-text="'«' + (packageToToggle.name || '') + '»'"></span>
                        </p>
                    </div>

                    {{-- Target Item Preview Card --}}
                    <div
                        class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 flex items-center gap-3 text-start">
                        <span
                            class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-xs"
                            :style="`background-color: ${getColorHex(packageToToggle.color)}; color: #ffffff;`"
                            x-html="renderIcon(packageToToggle.icon, 'w-5 h-5')"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate"
                                 x-text="packageToToggle.name"></div>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                                <span
                                    x-text="faNum(packageToToggle.modules_count ?? (packageToToggle.modules?.length || 0)) + ' ماژول'"></span>
                                <span>•</span>
                                <span class="font-semibold text-gray-600 dark:text-gray-300"
                                      x-text="faNum(packageToToggle.tenants_count || 0) + ' سامانه متصل'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Explanatory Notice --}}
                    <div>
                        {{-- Deactivation Context Note --}}
                        <template x-if="packageToToggle.is_active">
                            <div
                                class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 text-xs text-amber-900 dark:text-amber-200 text-start leading-relaxed space-y-1.5">
                                <div class="font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>نحوه عملکرد پس از غیرفعال‌سازی:</span>
                                </div>
                                <p>این پکیج از لیست خرید و سفارش‌گذاری برای مشتریان جدید خارج می‌شود؛ اما <strong>هیچ‌گونه
                                        آسیبی به سامانه‌های متصل قبلی وارد نخواهد شد</strong> و مشتریان فعلی همچنان با
                                    دسترسی‌های قبلی به کار خود ادامه می‌دهند.</p>
                            </div>
                        </template>

                        {{-- Activation Context Note --}}
                        <template x-if="!packageToToggle.is_active">
                            <div
                                class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/50 text-xs text-emerald-900 dark:text-emerald-200 text-start leading-relaxed space-y-1.5">
                                <div class="font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>نحوه عملکرد پس از فعال‌سازی:</span>
                                </div>
                                <p>این پکیج مجدداً در دسترس خریداران قرار گرفته و در فهرست سفارش‌گذاری و ساخت فوری CRM
                                    نمایش داده خواهد شد.</p>
                            </div>
                        </template>
                    </div>

                    {{-- Action Buttons: Symmetrical 50/50 Grid --}}
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button" @click="closeStatusModal()" :disabled="isTogglingStatus"
                                class="py-3 px-4 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-[0.99] transition-all cursor-pointer disabled:opacity-50">
                            انصراف
                        </button>
                        <button type="button" @click="confirmToggleStatus()" :disabled="isTogglingStatus"
                                :class="packageToToggle.is_active ? 'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-amber-500/25' : 'bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 shadow-emerald-600/25'"
                                class="py-3 px-4 rounded-2xl text-white text-xs sm:text-sm font-black shadow-lg active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50">
                            <template x-if="isTogglingStatus">
                                <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!isTogglingStatus && packageToToggle.is_active">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                            <template x-if="!isTogglingStatus && !packageToToggle.is_active">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                            <span x-text="packageToToggle.is_active ? 'بله، غیرفعال شود' : 'بله، فعال شود'"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
