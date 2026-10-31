        <div x-show="createdTenant === null"
             class="bg-white dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-xs backdrop-blur-xl">
            <div class="flex items-start max-w-3xl mx-auto relative">
                <div
                    role="progressbar" :aria-valuenow="currentStep" aria-valuemin="1" aria-valuemax="3" aria-label="مراحل ساخت مشتری"
                    class="absolute top-5 start-[16.6%] end-[16.6%] h-1 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    <div
                        class="h-full rounded-full bg-indigo-600 transition-all duration-500 ease-out"
                        :style="'width: ' + ((currentStep - 1) / 2 * 100) + '%'"></div>
                </div>
                <button type="button" @click="goToStep(1)"
                        class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                    <div
                        class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                        :class="currentStep === 1
                     ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                     : (currentStep > 1
                         ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                         : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700')">
                        <svg x-show="currentStep > 1" class="w-5 h-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-show="currentStep <= 1">۱</span>
                    </div>
                    <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 1 ? 'text-indigo-600 dark:text-indigo-400' : (currentStep > 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500')">
                    آدرس و ظاهر سامانه
                </span>
                        <span
                            class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">دامنه و هویت بصری</span>
                    </div>
                </button>
                <button type="button" @click="goToStep(2)"
                        class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                    <div
                        class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                        :class="currentStep === 2
                     ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                     : (currentStep > 2
                         ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                         : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700')">
                        <svg x-show="currentStep > 2" class="w-5 h-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-show="currentStep <= 2">۲</span>
                    </div>
                    <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 2 ? 'text-indigo-600 dark:text-indigo-400' : (currentStep > 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500')">
                    خریدار سامانه
                </span>
                        <span
                            class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">انتخاب حساب خریدار</span>
                    </div>
                </button>

                <button type="button" @click="goToStep(3)"
                        class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                    <div
                        class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                        :class="currentStep === 3
                     ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                     : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700'">
                        <span>۳</span>
                    </div>
                    <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'">
                    پکیج و فاکتور
                </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">انتخاب پلن و صدور فاکتور</span>
                    </div>
                </button>
            </div>
        </div>
