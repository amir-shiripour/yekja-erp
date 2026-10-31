<div
    class="relative overflow-hidden rounded-3xl border border-indigo-100 dark:border-indigo-900/40 bg-white dark:bg-gray-800/90 shadow-sm">
    <div
        class="absolute -top-20 -left-16 w-56 h-56 rounded-full bg-indigo-100/70 dark:bg-indigo-500/10 blur-3xl pointer-events-none"></div>
    <div
        class="absolute -bottom-24 -right-10 w-72 h-72 rounded-full bg-violet-100/50 dark:bg-violet-500/10 blur-3xl pointer-events-none"></div>
    <div
        class="absolute top-1/2 left-1/3 w-40 h-40 rounded-full bg-indigo-50/80 dark:bg-indigo-400/5 blur-2xl pointer-events-none"></div>

    <div class="relative p-5 sm:p-7 space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <span
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 ring-1 ring-indigo-100 dark:ring-indigo-900/50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            {{ __('resellerhub::messages.wizard.step3.showcase_title') }}</h3>
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-black border border-indigo-100 dark:border-indigo-900/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                            {{ __('resellerhub::messages.wizard.step3.shortcut_badge') }}
                                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1.5 leading-6 max-w-xl">
                        با یک کلیک، ترکیب آمادهٔ ماژول‌ها روی سامانه اعمال می‌شود.
                        مناسب برای مشاغل پرتکرار مثل دندانپزشکی، املاک، فروشگاه و …
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <button type="button" @click="savePackageModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-sm shadow-indigo-600/20 transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>{{ __('resellerhub::messages.wizard.step3.save_current_preset') }}</span>
                </button>
                <a href="{{ route('reseller.packages.index') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 text-xs font-bold border border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800 transition">
                    {{ __('resellerhub::messages.wizard.step3.manage_packages') }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>

        <template x-if="packages.length === 0">
            <div
                class="rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-dashed border-indigo-200 dark:border-indigo-900/50 p-6 sm:p-8 text-center">
                <div
                    class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-500 dark:text-indigo-400 flex items-center justify-center ring-1 ring-indigo-100 dark:ring-indigo-900/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h4 class="text-sm font-black text-gray-900 dark:text-white mb-1.5">هنوز پکیجی
                    نساخته‌اید</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-6 max-w-md mx-auto">
                    ماژول‌های دلخواه را انتخاب کنید و روی
                    <span
                        class="font-black text-indigo-600 dark:text-indigo-400">«ذخیره ترکیب فعلی»</span>
                    کلیک کنید تا مثلاً
                    <span
                        class="font-black text-indigo-600 dark:text-indigo-400">«پکیج دندانپزشکی»</span>
                    برای دفعات بعد آماده باشد.
                </p>
                <button type="button" @click="savePackageModalOpen = true"
                        class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-sm transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('resellerhub::messages.wizard.step3.create_first_package') }}
                </button>
            </div>
        </template>

        <template x-if="packages.length > 0">
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3.5">
                    <template x-for="pkg in packages" :key="pkg.id">
                        <button type="button" @click="togglePackage(pkg)"
                                class="group relative text-start rounded-2xl p-4 transition-all duration-200 cursor-pointer select-none overflow-hidden border"
                                :class="activePackageId === pkg.id
                                                    ? 'shadow-sm scale-[1.01]'
                                                    : 'bg-gray-50/60 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-white dark:hover:bg-gray-800 hover:shadow-md hover:scale-[1.01]'"
                                :style="getPackageCardStyle(pkg)">

                            <template x-if="activePackageId === pkg.id">
                                <span
                                    class="absolute top-0 left-0 px-3 py-1 rounded-br-xl text-white text-xs font-black flex items-center gap-1 shadow-xs"
                                    :style="getPackageRibbonStyle(pkg)">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ __('resellerhub::messages.wizard.step3.active_package_badge') }}
                                                </span>
                            </template>

                            <div class="flex items-start gap-3.5">
                                <span
                                    class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                    :class="getPackageIconClass(pkg.color, activePackageId === pkg.id)"
                                    :style="getPackageIconStyle(pkg.color, activePackageId === pkg.id)"
                                    x-html="renderIcon(pkg.icon, 'w-5 h-5')"></span>

                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h4 class="text-sm font-black truncate leading-6"
                                        :class="activePackageId === pkg.id ? 'text-indigo-900 dark:text-indigo-200' : 'text-gray-900 dark:text-white'"
                                        x-text="pkg.name"></h4>

                                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold"
                                            :class="activePackageId === pkg.id
                                                                  ? 'bg-white/80 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300'
                                                                  : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700'">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                 viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2"
                                                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                            </svg>
                                                            <span
                                                                x-text="faNum(pkg.modules ? pkg.modules.length : 0) + ' ماژول'"></span>
                                                        </span>

                                        <template x-if="pkg.features && pkg.features.length > 0">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold"
                                                :class="activePackageId === pkg.id
                                                                      ? 'bg-indigo-100/80 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300'
                                                                      : 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50'">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                     viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2"
                                                                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                                                </svg>
                                                                <span
                                                                    x-text="faNum(pkg.features.length) + ' قابلیت'"></span>
                                                            </span>
                                        </template>

                                        <template x-if="getPackageLimitsCount(pkg) > 0">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                     viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2"
                                                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                                </svg>
                                                                <span
                                                                    x-text="faNum(getPackageLimitsCount(pkg)) + ' سقف/لیمیت'"></span>
                                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3.5 pt-3 border-t flex items-center justify-between gap-2"
                                 :class="activePackageId === pkg.id ? 'border-indigo-200/70 dark:border-indigo-800/50' : 'border-gray-200/70 dark:border-gray-700/60'">
                                <template x-if="pkg.yearly_price && parseInt(pkg.yearly_price) > 0">
                                    <div class="flex items-baseline gap-1 min-w-0">
                                        <span
                                            class="text-sm font-black tabular-nums truncate text-emerald-600 dark:text-emerald-400"
                                            x-text="formatAmount(pkg.yearly_price)"></span>
                                        <span
                                            class="text-xs font-bold shrink-0 text-gray-400 dark:text-gray-500"
                                            x-text="currencyLabel + ' / سال'"></span>
                                    </div>
                                </template>
                                <template
                                    x-if="(!pkg.yearly_price || parseInt(pkg.yearly_price) === 0) && pkg.monthly_price && parseInt(pkg.monthly_price) > 0">
                                    <div class="flex items-baseline gap-1 min-w-0">
                                                        <span
                                                            class="text-sm font-black tabular-nums truncate text-emerald-600 dark:text-emerald-400"
                                                            x-text="formatAmount(pkg.monthly_price)"></span>
                                        <span
                                            class="text-xs font-bold shrink-0 text-gray-400 dark:text-gray-500"
                                            x-text="currencyLabel + ' / ماه'"></span>
                                    </div>
                                </template>
                                <template
                                    x-if="(!pkg.yearly_price || parseInt(pkg.yearly_price) === 0) && (!pkg.monthly_price || parseInt(pkg.monthly_price) === 0)">
                                    <span
                                        class="text-xs font-bold text-gray-400 dark:text-gray-500">بدون تعرفه ثابت</span>
                                </template>

                                <span
                                    class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-all"
                                    :class="activePackageId === pkg.id
                                                          ? 'text-white shadow-sm'
                                                          : 'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 group-hover:border-indigo-300 group-hover:text-indigo-600'"
                                    :style="getPackageCheckStyle(pkg)">
                                                    <svg x-show="activePackageId === pkg.id" class="w-3.5 h-3.5"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <svg x-show="activePackageId !== pkg.id" class="w-3.5 h-3.5"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                    </svg>
                                                </span>
                            </div>
                        </button>
                    </template>
                </div>

                <template x-if="activePackageId">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 px-4 py-3">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-800 dark:text-indigo-200">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                پکیج اعمال شد — ماژول‌ها و قابلیت‌های پکیج انتخاب شدند (امکان سفارشی‌سازی وجود دارد)
                                            </span>
                            <template x-if="isCustomPackage()">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/40 text-xs font-black">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                                                    پکیج سفارشی‌شده
                                                    <template x-if="getExtraModulesCount() > 0">
                                                        <span
                                                            x-text="'(+ ' + faNum(getExtraModulesCount()) + ' ماژول مازاد)'"></span>
                                                    </template>
                                                </span>
                            </template>
                            <template x-if="getPackageLimitsCount(getActivePackage()) > 0">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40 text-xs font-black">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24"><path
                                            stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span
                                                        x-text="'سقف/لیمیت‌های پکیج اعمال شد (' + faNum(getPackageLimitsCount(getActivePackage())) + ' مورد)'"></span>
                                                </span>
                            </template>
                        </div>
                        <button type="button"
                                @click="activePackageId = null; syncInvoiceItemsFromModules(); showToast('انتخاب پکیج لغو شد.');"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-rose-500 underline underline-offset-4 transition cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            لغو انتخاب پکیج
                        </button>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>

{{-- Package Duration & Pricing Selector --}}
<div
    class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-xs space-y-3">
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
            <div>
                <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">مدت زمان لایسنس
                    اولیه سامانه</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">قیمت و بازه زمانی لایسنس
                    به
                    صورت مستقیم از تعرفه پکیج انتخابی استخراج و در فاکتور نهایی اعمال می‌گردد.</p>
            </div>
        </div>
        <template x-if="getActivePackage()">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-black border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>تعرفه فعال: <span x-text="getActivePackage().name"></span></span>
                        </span>
        </template>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 pt-1">
        <template x-for="opt in availableDurationOptions" :key="opt.value">
            <label @click="selectPackageDuration(opt.value)"
                   class="relative flex flex-col justify-between p-3.5 rounded-2xl border cursor-pointer transition-all select-none text-center group"
                   :class="String(form.expires_in_months) === String(opt.value)
                                      ? 'bg-indigo-50/80 dark:bg-indigo-950/40 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20 shadow-sm'
                                      : 'bg-gray-50/60 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-white'">
                <input type="radio" name="plan_duration_months" :value="opt.value"
                       :checked="String(form.expires_in_months) === String(opt.value)" class="sr-only">

                <template x-if="opt.value === '12'">
                                <span
                                    class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[9px] font-black shadow-xs">
                                    توصیه شده
                                </span>
                </template>

                <div class="space-y-1">
                    <span class="text-xs font-black block" x-text="opt.label"></span>
                    <template x-if="getActivePackage()">
                        <div class="pt-1">
                            <template x-if="getPackageDurationPrice(getActivePackage(), opt.value) > 0">
                                <div
                                    class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                                <span
                                                    x-text="formatAmount(getPackageDurationPrice(getActivePackage(), opt.value))"></span>
                                    <span class="text-[9px] font-normal" x-text="currencyLabel"></span>
                                </div>
                            </template>
                            <template
                                x-if="getPackageDurationPrice(getActivePackage(), opt.value) === 0">
                                                <span
                                                    class="text-xs font-bold text-gray-400">رایگان / بدون هزینه</span>
                            </template>
                        </div>
                    </template>
                </div>

                <div
                    class="mt-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-center">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                      :class="String(form.expires_in_months) === String(opt.value) ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-600'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"
                                          x-show="String(form.expires_in_months) === String(opt.value)"></span>
                                </span>
                </div>
            </label>
        </template>
    </div>
</div>

{{-- Modules & Capabilities Section Header with Toggle --}}
<div
    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
    <div class="flex items-center gap-3">
                        <span
                            class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </span>
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">فهرست ماژول‌ها و
                    دسترسی‌های تفکیکی</h3>
                <span
                    class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50"
                    x-text="faNum(selectedModules.length) + ' ماژول {{ __('resellerhub::messages.wizard.step3.active_package_badge') }}'"></span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                مشاهده، سفارشی‌سازی جزئیات ماژول‌ها و تنظیم قابلیت‌ها و لیمیت‌ها
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <button type="button" @click="showModulesGrid = !showModulesGrid"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black transition-all cursor-pointer border"
                :class="showModulesGrid
                                    ? 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 dark:border-gray-600'
                                    : 'bg-indigo-600 hover:bg-indigo-700 text-white border-transparent shadow-sm shadow-indigo-500/20'">
            <svg class="w-4 h-4 transition-transform duration-200"
                 :class="showModulesGrid ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"/>
            </svg>
            <span
                x-text="showModulesGrid ? 'پنهان‌سازی بخش ماژول‌ها' : 'نمایش و ویرایش ماژول‌ها'"></span>
        </button>
    </div>
</div>
