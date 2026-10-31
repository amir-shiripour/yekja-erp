<div
    class="flex min-h-screen items-center justify-center bg-slate-50/90 dark:bg-gray-950 p-4 font-sans text-gray-800 dark:text-gray-100 antialiased"
    wire:poll.1000ms="checkProgress"
    x-data="{
         progress: @entangle('progress'),
         completed: @entangle('completed'),
         redirectUrl: @entangle('redirectUrl'),
         hasNavigated: false,
         toPersian(n) {
             const d = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
             return (n + '').replace(/\d/g, x => d[x]);
         },
         navigate() {
             if (this.hasNavigated) return;
             if (this.redirectUrl) {
                 this.hasNavigated = true;
                 window.location.replace(this.redirectUrl);
             }
         },
         init() {
             if (this.completed && this.redirectUrl) {
                 setTimeout(() => this.navigate(), 800);
             }
             this.$watch('completed', value => {
                 if (value && this.redirectUrl) {
                     setTimeout(() => this.navigate(), 800);
                 }
             });
             this.$watch('redirectUrl', url => {
                 if (this.completed && url) {
                     setTimeout(() => this.navigate(), 800);
                 }
             });
         }
     }">

    <div
        class="w-full max-w-lg bg-white/95 dark:bg-gray-900/95 backdrop-blur-2xl rounded-3xl border border-gray-100 dark:border-gray-800/80 shadow-2xl shadow-indigo-600/10 dark:shadow-none p-6 sm:p-9 relative overflow-hidden transition-all duration-300">

        {{-- Ambient Top Glow --}}
        <div
            class="absolute -top-24 start-1/2 -translate-x-1/2 w-64 h-32 bg-indigo-500/10 dark:bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Top Ribbon Bar --}}
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100 dark:border-gray-800/80">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                <span class="text-xs font-bold text-gray-600 dark:text-gray-300">استقرار ابری سامانه CRM</span>
            </div>

            <template x-if="!completed">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-ping"></span>
                    <span>در حال استقرار خودکار</span>
                </span>
            </template>
            <template x-if="completed">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>راه‌اندازی کامل شد</span>
                </span>
            </template>
        </div>

        {{-- Center Hero Icon & Status Message --}}
        <div class="text-center mb-6">
            <template x-if="!completed">
                <div class="relative mx-auto w-16 h-16 mb-4 flex items-center justify-center">
                    {{-- Outer Orbital Ring --}}
                    <div
                        class="absolute inset-0 rounded-2xl border-2 border-dashed border-indigo-300 dark:border-indigo-700/60 animate-[spin_8s_linear_infinite]"></div>
                    {{-- Inner Pulse Core --}}
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="animate-spin w-6 h-6 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </template>
            <template x-if="completed">
                <div
                    class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center mb-4 shadow-xl shadow-emerald-500/30 transform scale-105 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </div>
            </template>

            <h2 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white tracking-tight"
                x-text="completed ? 'سامانه اختصاصی شما با موفقیت مستقر شد' : 'در حال پیکربندی و استقرار سامانه'"></h2>
            <p class="mt-1.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed"
               x-text="completed ? 'میزکار شما با تمامی دسترسی‌ها آماده استفاده است. در حال انتقال امن...' : 'منابع اختصاصی، دیتابیس و ماژول‌های سامانه در حال راه‌اندازی هستند.'"></p>
        </div>

        {{-- Progress Bar & Percentage --}}
        <div class="space-y-2 mb-6">
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="text-gray-500 dark:text-gray-400 truncate max-w-[280px]" x-text="'{{ $message }}'"></span>
                <span class="text-sm font-black tabular-nums transition-colors duration-300"
                      :class="completed ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-400'"
                      x-text="toPersian(progress) + '٪'"></span>
            </div>

            {{-- Elevated Progress Bar --}}
            <div
                class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden p-0.5 border border-gray-100 dark:border-gray-800">
                <div class="h-full rounded-full transition-all duration-700 ease-out relative overflow-hidden"
                     :class="completed ? 'bg-gradient-to-l from-emerald-500 to-teal-500 shadow-sm shadow-emerald-500/40' : 'bg-gradient-to-l from-indigo-500 to-indigo-600 shadow-sm shadow-indigo-500/40'"
                     :style="`width: ${progress}%`">
                    {{-- Shimmer Effect --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-transparent via-white/25 to-transparent animate-[shimmer_2s_infinite]"></div>
                </div>
            </div>
        </div>

        {{-- Telemetry Deployment Steps --}}
        <div
            class="bg-slate-50/70 dark:bg-gray-800/40 rounded-2xl p-4 border border-gray-100 dark:border-gray-800/80 space-y-3 text-xs mb-6 text-right">
            {{-- Step 1 --}}
            <div class="flex items-center justify-between transition-colors duration-200"
                 :class="progress >= 15 ? 'text-gray-900 dark:text-gray-100 font-bold' : 'text-gray-400 dark:text-gray-500 font-medium'">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 text-xs transition-all duration-300"
                        :class="progress >= 15 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'">
                        <template x-if="progress >= 15">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </template>
                        <template x-if="progress < 15"><span class="tabular-nums">۱</span></template>
                    </span>
                    <span>تخصیص زیردامنه و فضای ابری ایزوله</span>
                </div>
                <span class="text-[11px] font-bold"
                      :class="progress >= 15 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                    <span
                        x-text="progress >= 15 ? 'انجام شد' : (progress < 15 && progress > 0 ? 'در حال انجام...' : 'در انتظار')"></span>
                </span>
            </div>

            {{-- Step 2 --}}
            <div class="flex items-center justify-between transition-colors duration-200"
                 :class="progress >= 40 ? 'text-gray-900 dark:text-gray-100 font-bold' : 'text-gray-400 dark:text-gray-500 font-medium'">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 text-xs transition-all duration-300"
                        :class="progress >= 40 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'">
                        <template x-if="progress >= 40">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </template>
                        <template x-if="progress < 40"><span class="tabular-nums">۲</span></template>
                    </span>
                    <span>پیکربندی پایگاه داده و کاربر مدیر سامانه</span>
                </div>
                <span class="text-[11px] font-bold"
                      :class="progress >= 40 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                    <span
                        x-text="progress >= 40 ? 'انجام شد' : (progress >= 15 && progress < 40 ? 'در حال انجام...' : 'در انتظار')"></span>
                </span>
            </div>

            {{-- Step 3 --}}
            <div class="flex items-center justify-between transition-colors duration-200"
                 :class="progress >= 70 ? 'text-gray-900 dark:text-gray-100 font-bold' : 'text-gray-400 dark:text-gray-500 font-medium'">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 text-xs transition-all duration-300"
                        :class="progress >= 70 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'">
                        <template x-if="progress >= 70">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </template>
                        <template x-if="progress < 70"><span class="tabular-nums">۳</span></template>
                    </span>
                    <span>فعال‌سازی ماژول‌های منتخب و لایسنس پکیج</span>
                </div>
                <span class="text-[11px] font-bold"
                      :class="progress >= 70 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                    <span
                        x-text="progress >= 70 ? 'انجام شد' : (progress >= 40 && progress < 70 ? 'در حال انجام...' : 'در انتظار')"></span>
                </span>
            </div>

            {{-- Step 4 --}}
            <div class="flex items-center justify-between transition-colors duration-200"
                 :class="progress >= 100 ? 'text-gray-900 dark:text-gray-100 font-bold' : 'text-gray-400 dark:text-gray-500 font-medium'">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 text-xs transition-all duration-300"
                        :class="progress >= 100 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'">
                        <template x-if="progress >= 100">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </template>
                        <template x-if="progress < 100"><span class="tabular-nums">۴</span></template>
                    </span>
                    <span>نهایی‌سازی تنظیمات و تحویل میزکار اختصاصی</span>
                </div>
                <span class="text-[11px] font-bold"
                      :class="progress >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                    <span
                        x-text="progress >= 100 ? 'انجام شد' : (progress >= 70 && progress < 100 ? 'در حال انجام...' : 'در انتظار')"></span>
                </span>
            </div>
        </div>

        {{-- Direct Jump Button (on completion) --}}
        <template x-if="completed">
            <a :href="redirectUrl"
               @click.prevent="navigate()"
               class="w-full inline-flex items-center justify-center gap-2.5 py-3 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold text-sm shadow-lg shadow-emerald-600/25 transition-all cursor-pointer group">
                <span>ورود مستقیم به پیشخوان سامانه</span>
                <svg class="w-4 h-4 rtl:rotate-180 transform group-hover:-translate-x-1 transition-transform"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </template>

        {{-- Security Note Footer --}}
        <div class="mt-5 pt-3.5 border-t border-gray-100 dark:border-gray-800/80 text-center">
            <span class="inline-flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500 font-medium">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>پیکربندی امن خودکار با ایزولاسیون کامل داده‌ها</span>
            </span>
        </div>
    </div>
</div>
