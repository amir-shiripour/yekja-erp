<div
    class="bg-white dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-xs backdrop-blur-xl">
    <div class="flex items-start max-w-3xl mx-auto relative">
        {{-- Progress Bar Line --}}
        <div role="progressbar" :aria-valuenow="currentStep" aria-valuemin="1" aria-valuemax="3"
             aria-label="مراحل ساخت و تنظیم پکیج"
             class="absolute top-5 start-[16.6%] end-[16.6%] h-1 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
            <div class="h-full rounded-full bg-indigo-600 transition-all duration-500 ease-out"
                 :style="'width: ' + ((currentStep - 1) / 2 * 100) + '%'"></div>
        </div>

        {{-- Step 1 Button --}}
        <button type="button" @click="goToStep(1)"
                class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
            <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                :class="currentStep === 1
                     ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                     : (currentStep > 1
                         ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                         : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700')">
                <svg x-show="currentStep > 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <template x-if="currentStep <= 1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </template>
            </div>
            <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 1 ? 'text-indigo-600 dark:text-indigo-400' : (currentStep > 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500')">
                    مشخصات و هویت پکیج
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">نام، توضیحات، آیکون و رنگ</span>
            </div>
        </button>

        {{-- Step 2 Button --}}
        <button type="button" @click="goToStep(2)"
                class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
            <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                :class="currentStep === 2
                     ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                     : (currentStep > 2
                         ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                         : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700')">
                <svg x-show="currentStep > 2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <template x-if="currentStep <= 2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </template>
            </div>
            <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 2 ? 'text-indigo-600 dark:text-indigo-400' : (currentStep > 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500')">
                    ماژول‌ها و دسترسی‌ها
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">انتخاب امکانات و فیچرها</span>
            </div>
        </button>

        {{-- Step 3 Button --}}
        <button type="button" @click="goToStep(3)"
                class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
            <div
                class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                :class="currentStep === 3
                     ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                     : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'">
                    تعرفه‌گذاری و قیمت
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">دوره‌های اشتراک و تخفیف‌ها</span>
            </div>
        </button>
    </div>
</div>
