        <div x-show="currentStep === 3" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="p-6 sm:p-8 space-y-8">

            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۳</span>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">انتخاب ماژول‌های فعال و بروزرسانی
                            نهایی</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ماژول‌های مورد نیاز مشتری را فعال یا
                            غیرفعال فرمایید.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-2 font-bold">
                        <button type="button" @click="selectAllAllowedModules()"
                                class="text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">انتخاب همه
                            مجازها
                        </button>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button type="button" @click="deselectAllModules()"
                                class="text-gray-500 hover:underline cursor-pointer">لغو انتخاب
                        </button>
                    </div>
                    <span
                        class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 font-bold text-gray-600 dark:text-gray-300 hidden sm:inline">
                        {{ $faNum(count($availableModules)) }} ماژول
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($availableModules as $mod)
                    <div
                        class="rounded-2xl border transition-all duration-200 overflow-hidden bg-white dark:bg-gray-800"
                        :class="selectedModules.includes('{{ $mod->slug }}')
                            ? 'border-indigo-300 dark:border-indigo-700/80 shadow-xs'
                            : 'border-gray-200 dark:border-gray-700/60 opacity-80'">
                        {{-- Card Header: Module Title + Status + Switch --}}
                        <div class="p-4 flex items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-800/50">
                            <div class="min-w-0 pr-1 flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs sm:text-sm font-bold truncate text-gray-900 dark:text-white">{{ $mod->title }}</span>
                                    <template x-if="selectedModules.includes('{{ $mod->slug }}')">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 shrink-0">
                                            فعال
                                        </span>
                                    </template>
                                </div>
                                <span
                                    class="text-xs text-gray-400 block mt-0.5 dir-ltr text-right truncate">{{ $mod->slug }}</span>
                                @if(!$mod->is_allowed)
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-red-500 dark:text-red-400 mt-1 font-bold">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        در سهمیه شما فعال نیست
                                    </span>
                                @endif
                            </div>

                            {{-- Switch toggle --}}
                            <label
                                class="relative shrink-0 w-10 h-6 rounded-full transition-colors duration-200 ease-in-out {{ $mod->is_allowed ? 'cursor-pointer' : 'cursor-not-allowed bg-gray-200 dark:bg-gray-700' }}"
                                @if($mod->is_allowed)
                                    :class="selectedModules.includes('{{ $mod->slug }}') ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                                @endif>
                                <input type="checkbox"
                                       name="modules[]"
                                       value="{{ $mod->slug }}"
                                       x-model="selectedModules"
                                       @change="if (selectedModules.includes('{{ $mod->slug }}') && (!selectedFeatures['{{ $mod->slug }}'] || selectedFeatures['{{ $mod->slug }}'].length === 0)) { selectAllFeatures('{{ $mod->slug }}'); } syncInvoiceItemsFromEditModules();"
                                       {{ !$mod->is_allowed ? 'disabled' : '' }}
                                       class="sr-only">
                                <div
                                    class="absolute top-1 right-1 w-4 h-4 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out"
                                    @if($mod->is_allowed)
                                        :class="selectedModules.includes('{{ $mod->slug }}') ? '-translate-x-4' : 'translate-x-0'"
                                    @else
                                        class="translate-x-0"
                                    @endif></div>
                            </label>
                        </div>

                        {{-- Accordion for Capabilities & Quotas (if module has features) --}}
                        <template x-if="getModuleFeatures('{{ $mod->slug }}').length > 0">
                            <div class="border-t border-gray-100 dark:border-gray-700/60">
                                <button type="button"
                                        @click="toggleModuleExpand('{{ $mod->slug }}')"
                                        class="w-full px-4 py-2.5 bg-gray-50/80 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-750 flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300 transition cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                        </svg>
                                        <span>قابلیت‌ها و سقف‌ها:</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50"
                                            x-text="faNum(getSelectedFeaturesCount('{{ $mod->slug }}')) + ' از ' + faNum(getModuleFeaturesCount('{{ $mod->slug }}')) + ' فعال'"></span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-200"
                                         :class="isModuleExpanded('{{ $mod->slug }}') ? 'rotate-180 text-indigo-600' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- Capabilities Content --}}
                                <div x-show="isModuleExpanded('{{ $mod->slug }}')" x-cloak
                                     class="p-3.5 bg-gray-50/40 dark:bg-gray-900/40 space-y-2 border-t border-gray-100 dark:border-gray-700/60">
                                    <div
                                        class="flex items-center justify-between text-xs pb-1.5 border-b border-gray-200/60 dark:border-gray-700/60">
                                        <span
                                            class="text-gray-500 dark:text-gray-400 font-bold">دسترسی‌ها و سقف منابع:</span>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="selectAllFeatures('{{ $mod->slug }}')"
                                                    :disabled="!selectedModules.includes('{{ $mod->slug }}')"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                                انتخاب همه
                                            </button>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button type="button" @click="deselectAllFeatures('{{ $mod->slug }}')"
                                                    :disabled="!selectedModules.includes('{{ $mod->slug }}')"
                                                    class="text-gray-500 hover:underline disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                                لغو همه
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 pt-1">
                                        <template x-for="feat in getModuleFeatures('{{ $mod->slug }}')" :key="feat.key">
                                            <div
                                                class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 transition text-xs hover:border-indigo-300 dark:hover:border-indigo-700">
                                                {{-- Feature Checkbox & Title --}}
                                                <label
                                                    class="flex items-center gap-2 min-w-0 flex-1 cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           :checked="isFeatureSelected('{{ $mod->slug }}', feat.key)"
                                                           @change="toggleFeature('{{ $mod->slug }}', feat.key)"
                                                           :disabled="!selectedModules.includes('{{ $mod->slug }}')"
                                                           class="w-4 h-4 text-indigo-600 rounded-md border-gray-300 dark:border-gray-600 focus:ring-indigo-500/20 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                    <div class="min-w-0">
                                                        <span
                                                            class="font-bold text-gray-900 dark:text-white block truncate"
                                                            :class="!selectedModules.includes('{{ $mod->slug }}') ? 'opacity-50' : ''"
                                                            x-text="feat.title"></span>
                                                        <template x-if="feat.description">
                                                            <span class="text-xs text-gray-400 block truncate"
                                                                  x-text="feat.description"></span>
                                                        </template>
                                                    </div>
                                                </label>

                                                {{-- Quota / Limit Input --}}
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <template
                                                        x-if="feat.type === 'quota' || moduleLimits[feat.key] !== undefined">
                                                        <div class="flex items-center gap-1"
                                                             x-show="isFeatureSelected('{{ $mod->slug }}', feat.key) && selectedModules.includes('{{ $mod->slug }}')">
                                                            <input type="number"
                                                                   min="0"
                                                                   :name="'limits[' + feat.key + ']'"
                                                                   x-model="moduleLimits[feat.key]"
                                                                   placeholder="سقف"
                                                                   title="سقف مجاز (خالی = نامحدود)"
                                                                   class="w-16 text-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-1 py-0.5 text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20">
                                                            <span class="text-xs text-gray-400"
                                                                  x-text="feat.unit || 'عدد'"></span>
                                                        </div>
                                                    </template>
                                                    <span class="text-xs font-mono text-gray-400 dir-ltr text-right"
                                                          x-text="feat.key"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @empty
                    <div
                        class="col-span-full p-4 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 rounded-xl text-xs">
                        هیچ ماژول فعالی برای واگذاری در سیستم یافت نشد.
                    </div>
                @endforelse
            </div>

            {{-- Unified Resource Quotas Management Panel --}}
            <div
                class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">سقف‌های مجاز مصرف منابع و
                                سهمیه‌ها (Resource Quotas)</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">سقف‌های عددی مجاز این سامانه را برای
                                ماژول‌های فعال تعیین یا ویرایش فرمایید. (خالی گذاشتن فیلد = بدون محدودیت / نامحدود)</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    @php
                        $trackedQuotas = [
                            ['key' => 'invoices', 'title' => 'سقف صدور فاکتور', 'module' => 'accounting', 'unit' => 'عدد'],
                            ['key' => 'clients', 'title' => 'سقف ثبت مشتریان / پرونده‌ها', 'module' => 'clients', 'unit' => 'نفر'],
                            ['key' => 'appointments', 'title' => 'سقف ثبت نوبت‌ها', 'module' => 'booking', 'unit' => 'نوبت'],
                            ['key' => 'booking_providers', 'title' => 'سقف پرسنل نوبت‌دهی', 'module' => 'booking', 'unit' => 'پرسنل'],
                            ['key' => 'products', 'title' => 'سقف کالاها و خدمات', 'module' => 'market', 'unit' => 'کالا'],
                            ['key' => 'warehouses', 'title' => 'سقف انبارها', 'module' => 'market', 'unit' => 'انبار'],
                            ['key' => 'stores', 'title' => 'سقف شعب / فروشگاه‌ها', 'module' => 'market', 'unit' => 'شعبه'],
                            ['key' => 'orders', 'title' => 'سقف سفارشات ثبت‌شده', 'module' => 'market', 'unit' => 'سفارش'],
                        ];
                    @endphp

                    @foreach($trackedQuotas as $tq)
                        <div class="p-3.5 rounded-xl border transition-all"
                             :class="selectedModules.includes('{{ $tq['module'] }}')
                                ? 'bg-gray-50/70 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700'
                                : 'bg-gray-100/40 dark:bg-gray-900/20 border-gray-100 dark:border-gray-800 opacity-50'">
                            <div class="flex items-center justify-between mb-2">
                                <span
                                    class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $tq['title'] }}</span>
                                <span class="text-xs text-gray-400"
                                      x-text="selectedModules.includes('{{ $tq['module'] }}') ? '{{ $tq['unit'] }}' : 'ماژول غیرفعال'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number"
                                       min="0"
                                       name="limits[{{ $tq['key'] }}]"
                                       x-model="moduleLimits['{{ $tq['key'] }}']"
                                       :disabled="!selectedModules.includes('{{ $tq['module'] }}')"
                                       placeholder="نامحدود"
                                       class="w-full text-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 disabled:opacity-40 disabled:cursor-not-allowed">
                                <button type="button"
                                        @click="moduleLimits['{{ $tq['key'] }}'] = ''"
                                        :disabled="!selectedModules.includes('{{ $tq['module'] }}')"
                                        title="تنظیم به نامحدود"
                                        class="px-2 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 disabled:opacity-40 cursor-pointer">
                                    نامحدود
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div
                class="bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-200/60 dark:border-gray-700/60 space-y-3">
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>خلاصه مشخصات جهت ذخیره و همگام‌سازی:</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-gray-400 block mb-0.5">نام سازمان:</span>
                        <span class="font-bold text-gray-900 dark:text-white truncate block"
                              x-text="form.name || '-'"></span>
                    </div>
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-gray-400 block mb-0.5">آدرس اختصاصی CRM:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 truncate block dir-ltr"
                              x-text="'{{ request()->getHttpHost() }}/' + (form.subdomain || '-')"></span>
                    </div>
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-gray-400 block mb-0.5">مدیر سامانه (خریدار):</span>
                        <span class="font-bold text-gray-900 dark:text-white truncate block"
                              x-text="form.admin_name || '-'"></span>
                    </div>
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-gray-400 block mb-0.5">وضعیت سامانه:</span>
                        <span class="font-bold block"
                              :class="{
                                  'text-emerald-600 dark:text-emerald-400': form.status === 'active',
                                  'text-amber-600 dark:text-amber-400': form.status === 'suspended',
                                  'text-red-600 dark:text-red-400': form.status === 'expired'
                              }"
                              x-text="form.status === 'active' ? 'فعال' : (form.status === 'suspended' ? 'معلق شده' : 'منقضی شده')"></span>
                    </div>
                </div>
            </div>
            <x-resellerhub::card class="p-6 sm:p-8 space-y-6 mt-8"
                                 x-effect="onIssueInvoiceToggle()">
                <div
                    class="flex items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-3">
                            <span
                                class="p-2.5 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-xl">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">صدور فاکتور برای تغییرات این
                                سامانه</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                شماره‌گذاری و مالیات بر اساس تنظیمات مالی سامانه نمایندگی (اختیاری)
                            </p>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                        <span class="text-xs sm:text-sm font-bold transition-colors"
                              :class="issueInvoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                              x-text="issueInvoice ? 'فاکتور صادر شود' : 'بدون فاکتور'"></span>
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" x-model="issueInvoice" class="sr-only peer">
                            <div
                                class="w-13 h-7 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                            <div
                                class="absolute right-1 top-1 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                <svg
                                    class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </label>
                </div>

                <div x-show="issueInvoice" x-transition class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between mb-1">
                                <x-resellerhub::label class="!mb-0">شماره فاکتور
                                    <span class="text-rose-500 font-black">*</span>
                                </x-resellerhub::label>
                                <button type="button" @click="toggleInvoiceNumberAuto()"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black border transition-all cursor-pointer"
                                        :class="invoiceNumberAuto
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400'
                                                : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'">
                                    <svg x-show="invoiceNumberAuto" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span x-text="invoiceNumberAuto ? 'تولید خودکار (از تنظیمات)' : 'ورود دستی'"></span>
                                </button>
                            </div>

                            <x-resellerhub::input
                                class="text-start font-medium tabular-nums transition-all duration-300" type="text"
                                dir="ltr" x-bind:class="invoiceNumberAuto
                                           ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed shadow-inner dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0 focus:border-gray-300 dark:focus:border-gray-700'
                                           : ''"
                                placeholder="مثال: INV-14040001"
                                x-model="invoiceNumberAuto ? invoiceNumberPreview : invoiceNumberOverride"
                                x-bind:readonly="invoiceNumberAuto"/>

                            <p class="text-xs text-gray-400 ms-1" x-text="invoiceNumberAuto
                                    ? 'شماره‌گذاری طبق قالب تعریف‌شده در «تنظیمات / مالی و فاکتور» انجام می‌شود.'
                                    : 'در صورت ورود دستی، یکتا بودن شماره به عهده شما خواهد بود.'"></p>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between mb-1">
                                <x-resellerhub::label class="!mb-0">نرخ و روش محاسبه مالیات</x-resellerhub::label>
                                <button type="button" @click="toggleTaxRateLock()"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black border transition-all cursor-pointer"
                                        :class="taxRateLocked
                                                ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400'
                                                : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              :d="taxRateLocked
                                                      ? 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v2h8z'
                                                      : 'M8 11V7a4 4 0 118 0m-4 9v2m-6 2h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'"/>
                                    </svg>
                                    <span x-text="taxRateLocked ? 'قفل از تنظیمات' : 'ویرایش دستی'"></span>
                                </button>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <x-resellerhub::input
                                    class="!px-3 text-center font-bold tabular-nums col-span-1 transition-all duration-300"
                                    type="text" inputmode="numeric" dir="ltr" x-bind:class="taxRateLocked
                                               ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed shadow-inner dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0'
                                               : ''"
                                    x-model="invoiceTaxRate" x-bind:readonly="taxRateLocked"/>

                                <x-resellerhub::select class="!px-3 col-span-2 transition-all duration-300"
                                                       x-model="invoiceTaxMode" x-bind:disabled="taxModeLocked"
                                                       @change="applyTaxToItems()" x-bind:class="taxModeLocked
                                                ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400'
                                                : ''">
                                    <option value="invoice">مالیات روی کل فاکتور</option>
                                    <option value="item">مالیات روی هر ردیف</option>
                                </x-resellerhub::select>
                            </div>

                            <p class="text-xs text-gray-400 ms-1" x-text="taxRateLocked
                                    ? 'نرخ و روش مالیات از «تنظیمات / مالی و فاکتور» خوانده می‌شود.'
                                    : 'این تغییر فقط برای همین فاکتور اعمال می‌شود.'"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <x-resellerhub::label class="!mb-0">تاریخ صدور <span
                                    class="text-rose-500 font-black">*</span></x-resellerhub::label>
                            <div class="relative">
                                <x-resellerhub::input
                                    class="cursor-pointer focus:ring-amber-500/20 focus:border-amber-500" type="text"
                                    x-model="invoiceIssueDate" data-jdp data-jdp-only-date

                                    placeholder="انتخاب تاریخ صدور" autocomplete="off" readonly/>
                                <svg
                                    class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <x-resellerhub::label class="!mb-0">تاریخ سررسید</x-resellerhub::label>
                            <div class="relative">
                                <x-resellerhub::input
                                    class="cursor-pointer focus:ring-amber-500/20 focus:border-amber-500" type="text"
                                    x-model="invoiceDueDate" data-jdp data-jdp-only-date
                                    x-bind:data-jdp-min-date="invoiceIssueDate || 'today'"

                                    placeholder="انتخاب تاریخ سررسید" autocomplete="off" readonly/>
                                <svg
                                    class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <button type="button" @click="invoiceDueDate = addDaysToJalaliToday(7)"
                                        class="flex-1 py-1.5 text-xs font-bold rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-100 transition-all shadow-sm dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 active:scale-95">
                                    ۱ هفته بعد
                                </button>
                                <button type="button" @click="invoiceDueDate = addDaysToJalaliToday(30)"
                                        class="flex-1 py-1.5 text-xs font-bold rounded-xl bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white border border-violet-100 transition-all shadow-sm dark:bg-violet-500/10 dark:text-violet-400 dark:border-violet-500/20 active:scale-95">
                                    ۱ ماه بعد
                                </button>
                                <button type="button" @click="invoiceDueDate = addDaysToJalaliToday(365)"
                                        class="flex-1 py-1.5 text-xs font-bold rounded-xl bg-fuchsia-50 text-fuchsia-600 hover:bg-fuchsia-600 hover:text-white border border-fuchsia-100 transition-all shadow-sm dark:bg-fuchsia-500/10 dark:text-fuchsia-400 dark:border-fuchsia-500/20 active:scale-95">
                                    ۱ سال بعد
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                                <span
                                    class="text-sm font-black text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                    اقلام فاکتور
                                    <span
                                        class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg"
                                        x-text="faNum(selectedModules.length) + ' ماژول فعال'"></span>
                                </span>
                            <button type="button" @click="addCustomInvoiceItem()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 active:scale-95 text-white text-xs font-bold shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                افزودن ردیف دلخواه
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700/60">
                            <table class="w-full text-sm min-w-[720px]">
                                <thead>
                                <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/10">
                                    <th scope="col" class="text-start font-black px-4 py-3 w-[40%]">شرح خدمت</th>
                                    <th scope="col" class="text-center font-black px-2 py-3 w-16">تعداد</th>
                                    <th scope="col" class="text-center font-black px-2 py-3 w-28">مبلغ واحد</th>
                                    <th scope="col" class="text-center font-black px-2 py-3 w-28">تخفیف</th>
                                    <th scope="col" class="text-center font-black px-2 py-3 w-32">مبلغ کل</th>
                                    <th scope="col" class="text-center font-black px-2 py-3 w-12"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                <template x-for="(item, idx) in invoiceItems"
                                          :key="idx + '-' + (item.item_id || 'custom')">
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                        <td class="px-4 py-3">
                                            <template x-if="item.item_type === 'custom'">
                                                <input type="text" x-model="item.description"
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-3 py-2 text-xs font-medium text-gray-900 dark:text-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/15 transition-all"
                                                       placeholder="شرح ردیف دلخواه...">
                                            </template>
                                            <template x-if="item.item_type !== 'custom'">
                                                <div class="flex items-center gap-2">
                                                        <span
                                                            class="shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-xs font-black"
                                                            :class="{
                                                                  'bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400': item.item_type === 'module',
                                                                  'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400': item.item_type === 'setup_fee',
                                                              }">
                                                            <span x-text="faNum(idx + 1)"></span>
                                                        </span>
                                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200 leading-5"
                                                       x-text="item.description"></p>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-2 py-3 text-center">
                                            <input type="text" inputmode="numeric" x-model="item.quantity"
                                                   @change="updateItemQty(item)"
                                                   class="w-14 mx-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-2 py-1.5 text-center text-xs font-bold text-gray-900 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/15 transition-all tabular-nums">
                                        </td>
                                        <td class="px-2 py-3 text-center">
                                            <input type="text" inputmode="numeric" x-model="item.display_price"
                                                   @change="updateItemPrice(item)"
                                                   class="w-28 mx-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-2 py-1.5 text-center text-xs font-bold text-gray-900 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/15 transition-all tabular-nums"
                                                   placeholder="رایگان">
                                        </td>
                                        <td class="px-2 py-3 text-center">
                                            <input type="text" inputmode="numeric" x-model="item.display_discount"
                                                   @change="updateItemDiscount(item)"
                                                   class="w-24 mx-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-2 py-1.5 text-center text-xs font-bold text-gray-900 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/15 transition-all tabular-nums"
                                                   placeholder="—">
                                        </td>
                                        <td class="px-2 py-3 text-center">
                                                <span
                                                    class="text-xs font-black text-gray-900 dark:text-white tabular-nums"
                                                    x-text="formatAmount(getItemRowTotal(item))"></span>
                                            <span
                                                class="text-xs text-gray-400">{{ $currencyLabel ?? 'تومان' }}</span>
                                        </td>
                                        <td class="px-2 py-3 text-center">
                                            <button type="button" @click="removeInvoiceItem(idx)"
                                                    class="p-1.5 rounded-lg text-gray-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer"
                                                    title="حذف ردیف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>

                        {{-- خلاصه مالی --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1.5">
                                    <input type="text" inputmode="numeric" x-model="invoiceDiscountDisplay"
                                           @change="updateInvoiceDiscount()"
                                           class="w-24 rounded-lg border border-rose-200 dark:border-rose-500/30 bg-white dark:bg-gray-900/60 px-2 py-1.5 text-center text-xs font-black text-gray-900 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/15 transition-all tabular-nums"
                                           placeholder="تخفیف">
                                    <span
                                        class="text-xs text-rose-400 font-bold">{{ $currencyLabel ?? 'تومان' }}</span>
                                </div>
                                <span class="text-xs text-gray-400">تخفیف کل</span>
                                <span
                                    class="text-xs font-black px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400"
                                    x-text="(invoiceTaxMode === 'item') ? 'مالیات ردیفی' : 'مالیات کل'"></span>
                            </div>
                            <div class="flex items-center gap-4 text-xs">
                                <span class="text-gray-500 dark:text-gray-400">جمع: <b
                                        class="text-gray-800 dark:text-gray-200 tabular-nums"
                                        x-text="formatAmount(getInvoiceSubtotal())"></b></span>
                                <span class="text-gray-500 dark:text-gray-400">مالیات: <b
                                        class="text-amber-600 dark:text-amber-400 tabular-nums"
                                        x-text="formatAmount(getInvoiceTax())"></b></span>
                                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums"
                                      x-text="formatAmount(getInvoiceTotal()) + ' ' + currencyLabel"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-resellerhub::card>
            <div
                class="pt-6 border-t border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="button" @click="prevStep()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>مرحله قبلی: مشخصات مدیر</span>
                </button>

                <button type="submit"
                        :disabled="submitting || subdomainStatus === 'unavailable'"
                        class="w-full sm:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-black rounded-xl transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 cursor-pointer">
                    <template x-if="submitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>در حال ذخیره و بروزرسانی تغییرات سامانه...</span>
                        </span>
                    </template>
                    <template x-if="!submitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>تایید نهایی و ذخیره تغییرات CRM</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>
