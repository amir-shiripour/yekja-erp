<div
    class="rounded-3xl p-6 sm:p-7 bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/70 shadow-xs space-y-5">
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
                <span
                    class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 shrink-0 border border-indigo-100/80 dark:border-indigo-800/40">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            <div>
                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span>{{ __('resellerhub::messages.subscription.views.features_title') }}</span>
                    <span
                        class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $hasActivePlan ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                            @if($hasActivePlan)
                            {{ reseller_fa_num(collect($quotasSummary['features'])->where('is_enabled', true)->count()) }}
                            فعال
                        @else
                            قفل (نیازمند پکیج)
                        @endif
                        </span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">وضعیت فعال یا غیرفعال بودن ویژگی‌های ماژولار این سامانه بر
                    اساس پکیج فعال</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button @click="upgradeModal = true" type="button"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline cursor-pointer group">
                <span>{{ $hasActivePlan ? __('resellerhub::messages.subscription.views.upgrade_or_renew_btn') : __('resellerhub::messages.subscription.views.select_package_btn') }}</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>

            {{-- Feature Matrix Toggle Button --}}
            <button type="button"
                    @click="toggleSection('features')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200/70 dark:border-gray-700 transition-all cursor-pointer group shadow-2xs">
                <span
                    x-text="sectionFeaturesOpen ? '{{ __('resellerhub::messages.subscription.views.close_section') }}' : '{{ __('resellerhub::messages.subscription.views.open_section') }}'"></span>
                <svg class="w-4 h-4 transition-transform duration-300 ease-out group-hover:scale-110"
                     :class="sectionFeaturesOpen ? 'rotate-180 text-indigo-600 dark:text-indigo-400' : 'text-gray-400'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="sectionFeaturesOpen"
         x-transition:enter="transition ease-out duration-300 origin-top"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-y-95 max-h-0"
         x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave="transition ease-in duration-200 origin-top"
         x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-y-95"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(!$hasActivePlan)
            <div
                class="col-span-full rounded-2xl p-6 sm:p-8 bg-gray-50/80 dark:bg-gray-800/50 border border-gray-200/70 dark:border-gray-700/60 text-center py-8">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-black text-gray-900 dark:text-white">امکانات و ماژول‌ها در انتظار فعال‌سازی
                    اشتراک</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1 leading-relaxed">
                    قابلیت‌های پیشرفته سامانه (پذیرش آنلاین، اتصال درگاه پرداخت، گزارش‌گیری هوشمند و ...) پس از
                    انتخاب و فعال‌سازی پکیج در دسترس قرار خواهند گرفت.
                </p>
                <button @click="upgradeModal = true" type="button"
                        class="mt-4 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition cursor-pointer">
                    {{ $hasNoPackage ? 'انتخاب و خرید پکیج' : 'فعال‌سازی اشتراک' }}
                </button>
            </div>
        @else
            @foreach($quotasSummary['features'] as $fKey => $fItem)
                <div
                    class="p-4 rounded-2xl border transition-all duration-200 {{ $fItem['is_enabled'] ? 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-200/80 dark:border-emerald-800/50' : 'bg-gray-50 dark:bg-gray-900/40 border-gray-200/70 dark:border-gray-800/80 opacity-75' }}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                            <span
                                class="text-xs font-black text-gray-900 dark:text-white leading-snug">{{ $fItem['title'] }}</span>
                        @if($fItem['is_enabled'])
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100/90 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shrink-0">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"/></svg>
                                    <span>فعال</span>
                                </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                                    <span>غیرفعال</span>
                                </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $fItem['description'] }}</p>
                </div>
            @endforeach
        @endif
    </div>
</div>

