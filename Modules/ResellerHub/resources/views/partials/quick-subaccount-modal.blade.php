<div x-show="quickSubModalOpen" x-cloak @click.stop="$event.stopPropagation()"
     class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
    <div @click.away="quickSubModalOpen = false" @click.stop="$event.stopPropagation()"
         class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span
                    class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">ایجاد کاربر زیرمجموعه</h3>
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

        <form @submit.prevent="submitQuickSubAccount()" class="p-5 sm:p-6 space-y-4">
            <div>
                <x-resellerhub::label>
                    نام و نام خانوادگی
                    <span class="text-red-500 font-bold">*</span>
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
                <template x-if="quickSubErrors.mobile">
                    <p class="text-xs text-red-500 mt-1" x-text="quickSubErrors.mobile[0]"></p>
                </template>
            </div>

            <div
                class="pt-3 flex items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700/60">
                <button type="button" @click="quickSubModalOpen = false"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    انصراف
                </button>
                <button type="submit" :disabled="quickSubSaving"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-2">
                    <template x-if="quickSubSaving">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <span>تایید</span>
                </button>
            </div>
        </form>
    </div>
</div>
