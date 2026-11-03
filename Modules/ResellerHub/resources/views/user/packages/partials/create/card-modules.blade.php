<x-resellerhub::card class="p-6 sm:p-8 space-y-6">
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۲</span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">انتخاب ماژول‌ها، قابلیت‌ها و
                    تعرفه‌های تفکیکی</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ماژول‌ها و دسترسی‌های این پکیج را
                    تعیین و تعرفه هر بخش را در صورت نیاز شخصی‌سازی نمایید.</p>
            </div>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <div class="flex items-center gap-2 font-bold">
                <button type="button" @click="selectAllAllowed()"
                        class="text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">انتخاب
                    همه
                    مجازها
                </button>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <button type="button" @click="deselectAll()"
                        class="text-gray-500 hover:underline cursor-pointer">{{ __('resellerhub::messages.packages.form.deselect_all') }}
                </button>
            </div>
            <span
                class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/60 font-black text-indigo-600 dark:text-indigo-400"
                x-text="'منتخب: ' + faNum(selectedModules.length) + ' ماژول • ' + faNum(selectedFeatures.length) + ' قابلیت'"></span>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
        <template x-for="mod in allModules" :key="mod.slug">
            <div
                class="rounded-2xl border transition-all duration-200 overflow-hidden bg-white dark:bg-gray-800"
                :class="!mod.is_allowed
                              ? 'opacity-40 border-gray-200 dark:border-gray-800'
                              : (isModuleSelected(mod.slug)
                                  ? 'border-indigo-300 dark:border-indigo-700 shadow-sm ring-1 ring-indigo-500/20'
                                  : 'border-gray-200/80 dark:border-gray-700/80 hover:border-gray-300')">
                <div class="p-3.5 flex items-start justify-between gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                                    <span class="text-xs sm:text-sm font-bold block truncate"
                                          :class="!mod.is_allowed
                                              ? 'text-gray-400 dark:text-gray-500'
                                              : (isModuleSelected(mod.slug) ? 'text-indigo-900 dark:text-indigo-200 font-black' : 'text-gray-800 dark:text-gray-200')"
                                          x-text="mod.title"></span>
                            <template x-if="mod.is_allowed && isModuleSelected(mod.slug)">
                                        <span
                                            class="px-1.5 py-0.5 rounded text-xs font-bold bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 shrink-0">فعال</span>
                            </template>
                        </div>
                        <span class="text-xs text-gray-400 block mt-0.5 dir-ltr text-right truncate"
                              x-text="mod.slug"></span>
                        <template x-if="!mod.is_allowed">
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-red-500 dark:text-red-400 mt-1 font-bold">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-4a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('resellerhub::messages.packages.form.disallowed_reseller') }}
                                    </span>
                        </template>
                    </div>
                    <label
                        class="relative shrink-0 w-9 h-5 rounded-full transition-colors duration-200 ease-in-out cursor-pointer select-none"
                        :class="!mod.is_allowed
                                          ? 'bg-gray-200 dark:bg-gray-700 cursor-not-allowed'
                                          : (isModuleSelected(mod.slug) ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600')">
                        <input type="checkbox"
                               name="modules[]"
                               :value="mod.slug"
                               :checked="isModuleSelected(mod.slug)"
                               :disabled="!mod.is_allowed"
                               @change="toggleModule(mod.slug)"
                               class="sr-only">
                        <div
                            class="absolute top-0.5 right-0.5 w-4 h-4 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out"
                            :class="(mod.is_allowed && isModuleSelected(mod.slug)) ? '-translate-x-4' : 'translate-x-0'"></div>
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
                        <span>{{ __('resellerhub::messages.packages.form.module_features_quotas_label') }}</span>
                        <span
                            class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-xs font-bold"
                            x-text="getModuleSummaryLabel(mod.slug)"></span>
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
                     class="p-3 bg-gray-50/80 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700/60 space-y-3">
                    <template
                        x-if="getModuleQuotas(mod.slug).length === 0 && getModuleBooleanFeatures(mod.slug).length === 0">
                        <div
                            class="py-2.5 px-3 text-center text-xs text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800/60 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                            این ماژول فاقد قابلیت تفکیک‌پذیر یا سهمیه عددی است (با فعال‌سازی ماژول، تمامی
                            امکانات آن در پکیج لحاظ می‌گردند).
                        </div>
                    </template>
                    <template x-if="getModuleQuotas(mod.slug).length > 0">
                        <div class="space-y-1.5">
                            <div
                                class="flex items-center justify-between text-xs font-bold text-amber-700 dark:text-amber-400 pb-1 border-b border-gray-200/60 dark:border-gray-700/60">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                                <span>{{ __('resellerhub::messages.packages.form.numeric_quotas_label') }}</span>
                                            </span>
                            </div>

                            <div class="space-y-1.5">
                                <template x-for="q in getModuleQuotas(mod.slug)" :key="q.key">
                                    <div
                                        class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-2xs hover:border-amber-300 dark:hover:border-amber-600 transition">
                                        <div class="min-w-0 flex-1">
                                                        <span
                                                            class="text-xs font-bold text-gray-800 dark:text-gray-200 block truncate"
                                                            x-text="q.title" :title="q.description || q.title"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <input type="number" min="0"
                                                   :name="`limits[${q.key}]`"
                                                   x-model="moduleLimits[q.key]"
                                                   :disabled="!isModuleSelected(mod.slug)"
                                                   placeholder="{{ __('resellerhub::messages.packages.form.unlimited_placeholder') }}"
                                                   dir="ltr"
                                                   class="w-20 text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50/70 dark:bg-gray-900/70 px-2 py-1 text-xs font-bold text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 disabled:opacity-40">
                                            <span
                                                class="text-xs font-medium text-gray-400 dark:text-gray-500 select-none shrink-0"
                                                x-text="q.unit || 'عدد'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="getModuleBooleanFeatures(mod.slug).length > 0">
                        <div class="space-y-1.5"
                             :class="getModuleQuotas(mod.slug).length > 0 ? 'pt-2 border-t border-gray-200/70 dark:border-gray-700/60' : ''">
                            <div
                                class="flex items-center justify-between text-xs pb-1 border-b border-gray-200/60 dark:border-gray-700/60">
                                            <span
                                                class="font-bold text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>{{ __('resellerhub::messages.packages.form.package_features_label') }}</span>
                                            </span>
                                <div class="flex items-center gap-2 text-xs">
                                    <button type="button" @click="selectAllModuleFeatures(mod.slug)"
                                            :disabled="!isModuleSelected(mod.slug)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold disabled:opacity-40 cursor-pointer">
                                        {{ __('resellerhub::messages.packages.form.select_all') }}
                                    </button>
                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                    <button type="button" @click="deselectAllModuleFeatures(mod.slug)"
                                            :disabled="!isModuleSelected(mod.slug)"
                                            class="text-gray-500 hover:underline disabled:opacity-40 cursor-pointer">
                                        {{ __('resellerhub::messages.packages.form.deselect_all_short') }}
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-1.5 max-h-56 overflow-y-auto custom-scrollbar pr-0.5">
                                <template x-for="feat in getModuleBooleanFeatures(mod.slug)"
                                          :key="feat.key">
                                    <label
                                        class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-600 transition cursor-pointer select-none shadow-2xs"
                                        :title="feat.description || feat.title">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <input type="checkbox"
                                                   :checked="isFeatureSelected(feat.key)"
                                                   @change="toggleFeature(feat.key, mod.slug)"
                                                   :disabled="!isModuleSelected(mod.slug)"
                                                   class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500/20 cursor-pointer disabled:opacity-40 shrink-0">
                                            <span
                                                class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate"
                                                :class="!isModuleSelected(mod.slug) ? 'opacity-40' : ''"
                                                x-text="feat.title"></span>
                                        </div>
                                        <span
                                            class="text-xs font-bold px-1.5 py-0.5 rounded shrink-0 transition"
                                            :class="(isModuleSelected(mod.slug) && isFeatureSelected(feat.key))
                                                            ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50'
                                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-400 opacity-60'"
                                            x-text="(isModuleSelected(mod.slug) && isFeatureSelected(feat.key)) ? '{{ __('resellerhub::messages.packages.form.included_in_pkg') }}' : '{{ __('resellerhub::messages.packages.form.disabled_badge') }}'">
                                                    </span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</x-resellerhub::card>
