    <div
        class="bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-4 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-3 text-sm text-indigo-900 dark:text-indigo-200">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>در حال ویرایش سامانه: <strong
                    class="text-base text-indigo-700 dark:text-indigo-300">{{ $tenant->name }}</strong> (شناسه: {{ $faNum($tenant->id) }})</span>
        </div>
        <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hidden sm:inline">فرآیند ۳ مرحله‌ای ویرایش با ذخیره آنی (Zero-Reload)</span>
    </div>
    <div x-show="updatedTenant === null"
         class="bg-white dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-xs backdrop-blur-xl">
        <div class="flex items-center justify-between max-w-2xl mx-auto relative px-4 sm:px-8">
            <div class="absolute top-5 start-12 end-12 h-1 bg-gray-200 dark:bg-gray-700 -z-0">
                <div class="h-full bg-indigo-600 transition-all duration-500 ease-out"
                     :style="'width: ' + ((currentStep - 1) / 2 * 100) + '%'"></div>
            </div>
            <button type="button" @click="goToStep(1)"
                    class="flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                <div
                    class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                    :class="currentStep === 1
                         ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                         : (currentStep > 1
                             ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                             : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700')">
                    <template x-if="currentStep > 1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span>۱</span>
                    </template>
                </div>
                <div class="text-center">
                    <span class="text-xs font-bold transition-colors block"
                          :class="currentStep === 1 ? 'text-indigo-600 dark:text-indigo-400' : (currentStep > 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400')">
                        کسب‌وکار و سابدامین
                    </span>
                    <span class="text-xs text-gray-400 hidden sm:block">مشخصات و آدرس اختصاصی</span>
                </div>
            </button>
            <button type="button" @click="goToStep(2)"
                    class="flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                <div
                    class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                    :class="currentStep === 2
                         ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                         : (currentStep > 2
                             ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                             : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700')">
                    <template x-if="currentStep > 2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span>۲</span>
                    </template>
                </div>
                <div class="text-center">
                    <span class="text-xs font-bold transition-colors block"
                          :class="currentStep === 2 ? 'text-indigo-600 dark:text-indigo-400' : (currentStep > 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400')">
                        مدیر و زیرمجموعه
                    </span>
                    <span class="text-xs text-gray-400 hidden sm:block">مشخصات خریدار و وضعیت</span>
                </div>
            </button>
            <button type="button" @click="goToStep(3)"
                    class="flex flex-col items-center gap-2 relative z-10 group cursor-pointer focus:outline-hidden">
                <div
                    class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm"
                    :class="currentStep === 3
                         ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110'
                         : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700'">
                    <span>۳</span>
                </div>
                <div class="text-center">
                    <span class="text-xs font-bold transition-colors block"
                          :class="currentStep === 3 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400'">
                        ماژول‌ها و ذخیره
                    </span>
                    <span class="text-xs text-gray-400 hidden sm:block">انتخاب و بروزرسانی آنی</span>
                </div>
            </button>
        </div>
    </div>
