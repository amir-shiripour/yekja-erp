<div class="sticky bottom-4 z-40">
    <div
        class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-gray-700/60 shadow-[0_10px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex items-center justify-between gap-4">

        <div>
            <a x-show="currentStep === 1"
               href="{{ route('reseller.packages.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ __('resellerhub::messages.packages.form.cancel') }}</span>
            </a>

            <button type="button"
                    x-show="currentStep > 1"
                    x-cloak
                    @click="prevStep()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span x-text="currentStep === 2 ? 'مرحله قبلی: مشخصات پکیج' : 'مرحله قبلی: ماژول‌ها و امکانات'"></span>
            </button>
        </div>

        {{-- Center: Step Indicator Pill --}}
        <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-gray-400">
            <span>مرحله <span class="text-indigo-600 dark:text-indigo-400 font-black text-sm"
                              x-text="currentStep"></span> از ۳:</span>
            <span class="text-gray-700 dark:text-gray-200"
                  x-text="currentStep === 1 ? 'مشخصات و هویت پکیج' : (currentStep === 2 ? 'ماژول‌ها و دسترسی‌ها' : 'تعرفه‌گذاری و قیمت')"></span>
        </div>

        {{-- Left Side (RTL End): Next Step on Steps 1 & 2, Submit on Step 3 --}}
        <div class="flex items-center gap-3">
            <button x-show="currentStep < 3"
                    type="button"
                    @click="nextStep()"
                    :disabled="currentStep === 1 ? !packageName.trim() : selectedModules.length === 0"
                    class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-sm shadow-lg shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer">
                <span
                    x-text="currentStep === 1 ? 'مرحله بعدی: ماژول‌ها و دسترسی‌ها' : 'مرحله بعدی: تعرفه‌گذاری و قیمت'"></span>
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>

            <button x-show="currentStep === 3"
                    x-cloak
                    type="submit"
                    :disabled="submitting || selectedModules.length === 0 || hasPricingErrors()"
                    :title="hasPricingErrors() ? 'لطفاً ابتدا خطاهای مربوط به مبالغ تخفیف را اصلاح نمایید' : ''"
                    class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-sm shadow-lg shadow-indigo-500/30 transition-all active:scale-95 cursor-pointer">
                <template x-if="submitting">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ __('resellerhub::messages.packages.form.saving') }}</span>
                    </span>
                </template>
                <template x-if="!submitting">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('resellerhub::messages.packages.form.submit_create') }}</span>
                    </span>
                </template>
            </button>
        </div>
    </div>
</div>
