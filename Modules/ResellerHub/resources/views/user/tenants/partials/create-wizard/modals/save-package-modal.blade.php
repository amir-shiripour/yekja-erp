<div x-show="savePackageModalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
    <div @click.away="savePackageModalOpen = false"
         class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                    <span
                        class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-black shadow-md shrink-0 transition-all duration-200"
                        :class="getColorClass(newPackageColor)"
                        :style="`background-color: ${getColorHex(newPackageColor)}; color: #ffffff;`"
                        x-html="renderIcon(newPackageIcon, 'w-6 h-6')">
                    </span>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">ذخیره ترکیب ماژول‌ها به عنوان
                        پکیج جدید</h3>
                    <p class="text-xs text-gray-400 mt-0.5"
                       x-text="'شامل ' + faNum(selectedModules.length) + ' ماژول انتخاب‌شده فعلی'"></p>
                </div>
            </div>
            <button type="button" @click="savePackageModalOpen = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="saveCurrentAsPackage()" class="p-6 space-y-4">
            <div>
                <x-resellerhub::label>
                    نام پکیج <span class="text-red-500 font-bold">*</span>
                </x-resellerhub::label>
                <x-resellerhub::input type="text" x-model="newPackageName" required
                                      placeholder="مثلاً: کلینیک دندانپزشکی یا مشاور املاک"
                />
            </div>

            {{-- Color & Icon Picker in Modal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">رنگ
                        پکیج:</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <template x-for="c in (availableColors || window.resellerPackageColors || [])"
                                  :key="c.key">
                            <button type="button" @click="newPackageColor = c.key"
                                    :title="c.label || c.key"
                                    :class="newPackageColor === c.key ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110' : 'opacity-70 hover:opacity-100'"
                                    class="w-6 h-6 rounded-full transition-all cursor-pointer shadow-xs flex items-center justify-center text-white text-[9px]"
                                    :style="`background-color: ${c.hex}`">
                                <template x-if="newPackageColor === c.key">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">آیکون
                            صنف:</label>
                        <span
                            class="text-xs font-bold text-indigo-600 dark:text-indigo-400 truncate max-w-[110px]"
                            x-text="(availableIcons.find(i => i.key === newPackageIcon) || {}).label || ''"></span>
                    </div>
                    <div
                        class="grid grid-cols-6 gap-1 p-2 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 max-h-28 overflow-y-auto custom-scrollbar">
                        <template x-for="ic in availableIcons" :key="ic.key">
                            <button type="button" @click="newPackageIcon = ic.key"
                                    :title="ic.label"
                                    :class="newPackageIcon === ic.key ? 'bg-indigo-600 text-white shadow-xs ring-2 ring-indigo-500/30' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                                    class="h-7 rounded-lg transition-all cursor-pointer flex items-center justify-center"
                                    x-html="renderIcon(ic.key, 'w-3.5 h-3.5')">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div>
                <x-resellerhub::label>
                    توضیحات کوتاه (اختیاری)
                </x-resellerhub::label>
                <x-resellerhub::textarea class="resize-none" x-model="newPackageDescription" rows="2"
                                         placeholder="توضیح دهید این پکیج برای چه صنفی است..."
                ></x-resellerhub::textarea>
            </div>

            <div
                class="p-3.5 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 text-xs text-indigo-900 dark:text-indigo-200">
                <span class="font-bold">ماژول‌های داخل این پکیج: </span>
                <span class="text-xs" x-text="selectedModules.join(', ')"></span>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" @click="savePackageModalOpen = false"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    انصراف
                </button>
                <button type="submit" :disabled="savingPackage"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black shadow-md transition cursor-pointer flex items-center gap-2">
                    <template x-if="savingPackage">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <span>ذخیره و اضافه شدن به لیست</span>
                </button>
            </div>
        </form>
    </div>
</div>
