        <div x-show="createdTenant === null"
             class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200/80 dark:border-gray-700/60 shadow-xs">
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
                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-200 shadow-xs"
                        :class="currentStep === 1
                     ? 'bg-indigo-600 text-white ring-2 ring-indigo-600/30 shadow-xs scale-105'
                     : (currentStep > 1
                         ? 'bg-emerald-500 text-white shadow-xs'
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
                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-200 shadow-xs"
                        :class="currentStep === 2
                     ? 'bg-indigo-600 text-white ring-2 ring-indigo-600/30 shadow-xs scale-105'
                     : (currentStep > 2
                         ? 'bg-emerald-500 text-white shadow-xs'
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
                    اطلاعات کاربری سامانه
                </span>
                        <span
                            class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">انتخاب حساب کاربری</span>
                    </div>
                </button>

                <button type="button" @click="goToStep(3)"
                        class="flex-1 flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-200 shadow-xs"
                        :class="currentStep === 3
                     ? 'bg-indigo-600 text-white ring-2 ring-indigo-600/30 shadow-xs scale-105'
                     : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700'">
                        <span>۳</span>
                    </div>
                    <div class="text-center">
                <span class="text-xs font-bold transition-colors block"
                      :class="currentStep === 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'">
                    پکیج و فاکتور
                </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">انتخاب پکیج و صدور فاکتور</span>
                    </div>
                </button>
            </div>
        </div>
