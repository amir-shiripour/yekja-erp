{{-- Quick Create Sub-Account Modal --}}
<div x-show="quickSubModalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
    <div @click.away="quickSubModalOpen = false"
         class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                    <span
                        class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </span>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">تعریف سریع حساب
                        زیرمجموعه</h3>
                    <p class="text-xs text-gray-400 mt-0.5">افزودن خریدار جدید به عنوان زیرمجموعه</p>
                </div>
            </div>
            <button type="button" @click="quickSubModalOpen = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitQuickSubAccount()" class="p-6 space-y-4">
            <div
                class="p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 text-xs text-indigo-900 dark:text-indigo-300 leading-relaxed">
                فقط نام و شماره تماس مشتری را وارد کنید؛ ایمیل سازمانی و گذرواژه امن ورود به CRM به صورت هوشمند
                توسط سامانه تولید خواهد شد.
            </div>

            <div>
                <x-resellerhub::label>
                    نام و نام خانوادگی خریدار / مشتری <span class="text-red-500 font-bold">*</span>
                </x-resellerhub::label>
                <x-resellerhub::input type="text" x-model="quickSubForm.name" required
                                      x-bind:aria-invalid="quickSubErrors.name ? 'true' : 'false'"
                                      placeholder="مثال: علی رضایی"
                />
                <template x-if="quickSubErrors.name">
                    <p class="text-xs text-red-500 mt-1" x-text="quickSubErrors.name[0]"></p>
                </template>
            </div>

            <div>
                <x-resellerhub::label>
                    شماره تلفن همراه <span class="text-red-500 font-bold">*</span>
                </x-resellerhub::label>
                <x-resellerhub::input class="text-left" type="text" x-model="quickSubForm.mobile" required
                                      aria-invalid="false" x-bind:aria-invalid="!!quickSubErrors.mobile"
                                      dir="ltr"
                                      placeholder="09123456789"
                />
                <p class="text-xs text-gray-400 mt-1">جهت ارسال پیامک خوش‌آمدگویی و مشخصات ورود به مشتری</p>
                <template x-if="quickSubErrors.mobile">
                    <p class="text-xs text-red-500 mt-1" x-text="quickSubErrors.mobile[0]"></p>
                </template>
            </div>

            <div
                class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700/60">
                <button type="button" @click="quickSubModalOpen = false"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    انصراف
                </button>
                <button type="submit" :disabled="quickSubSaving"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black shadow-md transition cursor-pointer flex items-center gap-2">
                    <template x-if="quickSubSaving">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <span>ثبت وانتخاب انی مشتری</span>
                </button>
            </div>
        </form>
    </div>
</div>
