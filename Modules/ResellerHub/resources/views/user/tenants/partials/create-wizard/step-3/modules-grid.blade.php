<div x-show="showModulesGrid" x-transition
     class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
    <template x-for="mod in allModules" :key="mod.slug">
        <div
            class="rounded-2xl border transition-all duration-200 overflow-hidden bg-white dark:bg-gray-800"
            :class="!isModuleAllowed(mod.slug)
                             ? 'opacity-40 border-gray-200 dark:border-gray-800'
                             : (selectedModules.includes(mod.slug)
                                 ? 'border-indigo-300 dark:border-indigo-700 shadow-sm ring-1 ring-indigo-500/20'
                                 : 'border-gray-200/80 dark:border-gray-700/80 hover:border-gray-300')">

            {{-- Module Card Top Header --}}
            <div class="p-3.5 flex items-start justify-between gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-bold block truncate"
                              :class="!isModuleAllowed(mod.slug)
                                              ? 'text-gray-400 dark:text-gray-500'
                                              : (selectedModules.includes(mod.slug) ? 'text-indigo-900 dark:text-indigo-200 font-black' : 'text-gray-800 dark:text-gray-200')"
                              x-text="mod.title"></span>
                        <template
                            x-if="isModuleAllowed(mod.slug) && selectedModules.includes(mod.slug)">
                            <div class="flex items-center gap-1">
                                <template x-if="isModuleInActivePackage(mod.slug)">
                                    <span
                                        class="px-1.5 py-0.5 rounded text-xs font-bold bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200/60 shrink-0">
                                                    موجود در پکیج
                                                </span>
                                </template>
                                <template x-if="activePackageId && !isModuleInActivePackage(mod.slug)">
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200/60 shrink-0">
                                                    الحاقی (سفارشی)
                                                </span>
                                </template>
                                <template x-if="!activePackageId">
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 shrink-0">
                                                    فعال
                                                </span>
                                </template>
                            </div>
                        </template>
                    </div>
                    <span
                        class="text-xs text-gray-400 block mt-0.5 dir-ltr text-right truncate"
                        x-text="mod.slug"></span>
                    <template x-if="!isModuleAllowed(mod.slug)">
                        <span
                            class="inline-flex items-center gap-1 text-xs text-red-500 dark:text-red-400 mt-1 font-bold">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        خارج از سهمیه نماینده
                                    </span>
                    </template>
                </div>

                <label
                    class="relative shrink-0 w-9 h-5 rounded-full transition-colors duration-200 ease-in-out select-none"
                    :class="!isModuleAllowed(mod.slug)
                                      ? 'bg-gray-200 dark:bg-gray-700 cursor-not-allowed'
                                       : (selectedModules.includes(mod.slug) ? 'bg-indigo-600 cursor-pointer' : 'bg-gray-300 dark:bg-gray-600 cursor-pointer')">
                    <input type="checkbox"
                           name="modules[]"
                           :value="mod.slug"
                           x-model="selectedModules"
                           :disabled="!isModuleAllowed(mod.slug)"
                           @change="if (selectedModules.includes(mod.slug) && (!selectedFeatures[mod.slug] || selectedFeatures[mod.slug].length === 0)) { selectAllFeatures(mod.slug); } syncInvoiceItemsFromModules();"
                           class="sr-only">
                    <div
                        class="absolute top-0.5 right-0.5 w-4 h-4 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out"
                        :class="(isModuleAllowed(mod.slug) && selectedModules.includes(mod.slug)) ? '-translate-x-4' : 'translate-x-0'"></div>
                </label>
            </div>

            <div
                class="px-3.5 py-2 border-t border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-800 flex items-center justify-between">
                <button type="button" @click="toggleModuleAccordion(mod.slug)"
                        class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition cursor-pointer select-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>قابلیت‌ها و دسترسی‌ها:</span>
                    <span
                        class="px-1.5 py-0.2 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-xs"
                        x-text="faNum(getSelectedFeaturesCount(mod.slug)) + ' از ' + faNum(getModuleFeaturesCount(mod.slug))"></span>
                </button>

                <button type="button" @click="toggleModuleAccordion(mod.slug)"
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition cursor-pointer">
                    <svg class="w-4 h-4 transform transition-transform duration-200"
                         :class="isModuleExpanded(mod.slug) ? 'rotate-180 text-indigo-600' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div x-show="isModuleExpanded(mod.slug)" x-cloak
                 class="p-3 bg-gray-50/70 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 space-y-2">
                <div
                    class="flex items-center justify-between text-xs pb-1 border-b border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-gray-500 font-bold">انتخاب دسترسی‌های ماژول:</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectAllFeatures(mod.slug)"
                                :disabled="!selectedModules.includes(mod.slug)"
                                class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                            انتخاب همه
                        </button>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button type="button" @click="deselectAllFeatures(mod.slug)"
                                :disabled="!selectedModules.includes(mod.slug)"
                                class="text-gray-500 hover:underline disabled:opacity-40 disabled:cursor-not-allowed">
                            لغو همه
                        </button>
                    </div>
                </div>

                <template x-if="getModuleFeatures(mod.slug).length === 0">
                    <div class="py-2 text-center text-xs text-gray-400">
                        دسترسی اختصاصی جداگانه‌ای برای این ماژول تعریف نشده (تمام امکانات فعال خواهند
                        بود).
                    </div>
                </template>

                <template x-if="getModuleFeatures(mod.slug).length > 0">
                    <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1.5 pr-0.5">
                        <template x-for="feat in getModuleFeatures(mod.slug)" :key="feat.key">
                            <div
                                class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 transition text-xs hover:border-indigo-200 dark:hover:border-indigo-800">
                                <label
                                    class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer select-none">
                                    <input type="checkbox"
                                           :checked="isFeatureSelected(mod.slug, feat.key)"
                                           @change="toggleFeature(mod.slug, feat.key)"
                                           :disabled="!selectedModules.includes(mod.slug)"
                                           class="w-4 h-4 text-indigo-600 rounded-md border-gray-300 dark:border-gray-600 focus:ring-indigo-500/20 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                    <span class="truncate font-medium text-gray-800 dark:text-gray-200"
                                          :class="!selectedModules.includes(mod.slug) ? 'opacity-60' : ''"
                                          x-text="feat.title"></span>
                                </label>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div class="flex items-center gap-1"
                                         x-show="isFeatureSelected(mod.slug, feat.key) && selectedModules.includes(mod.slug)">
                                        <input type="number"
                                               min="0"
                                               :name="'limits[' + feat.key + ']'"
                                               x-model="moduleLimits[feat.key]"
                                               @input.debounce.250ms="syncInvoiceItemsFromModules()"
                                               placeholder="سقف"
                                               title="سقف مجاز این قابلیت (خالی = نامحدود)"
                                               class="w-16 text-center rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-1 py-0.5 text-xs font-bold text-gray-800 dark:text-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20">
                                    </div>
                                    <span
                                        class="text-xs text-gray-400 dir-ltr text-right"
                                        x-text="feat.key.split('.').pop()"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
<div
    class="bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-200/60 dark:border-gray-700/60 space-y-3">
    <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>خلاصه مشخصات جهت راه‌اندازی:</span>
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <div
            class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-gray-400 block mb-0.5">عنوان / برند سامانه:</span>
            <span class="font-bold text-gray-900 dark:text-white truncate block"
                  x-text="whiteLabelBrandTitle || (selectedSubAccount ? (selectedSubAccount.company_name || selectedSubAccount.name) : '') || form.subdomain || '—'"></span>
        </div>
        <div
            class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-gray-400 block mb-0.5"
                  x-text="domainType === 'custom' ? 'دامنه اختصاصی CRM:' : 'آدرس اختصاصی CRM:'"></span>
            <span class="font-bold text-indigo-600 dark:text-indigo-400 truncate block dir-ltr"
                  x-text="domainType === 'custom' ? (form.custom_domain || '-') : ('{{ request()->getHttpHost() }}/' + (form.subdomain || '-'))"></span>
        </div>
        <div
            class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-gray-400 block mb-0.5">خریدار (زیرمجموعه):</span>
            <span class="font-bold text-gray-900 dark:text-white truncate block"
                  x-text="form.admin_name ? (form.admin_name + (form.admin_email ? ' (' + form.admin_email + ')' : '')) : '-'"></span>
        </div>
        <div
            class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
            <span class="text-gray-400 block mb-0.5">مدت لایسنس:</span>
            <span class="font-bold text-gray-900 dark:text-white block" x-text="durationLabel"></span>
        </div>
    </div>
</div>
