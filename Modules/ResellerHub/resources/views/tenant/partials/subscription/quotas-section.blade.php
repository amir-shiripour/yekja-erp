<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div class="flex items-center gap-3">
                <span
                    class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 shrink-0 border border-indigo-100/80 dark:border-indigo-800/40">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </span>
            <div>
                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span>{{ __('resellerhub::messages.subscription.views.quotas_title') }}</span>
                    <span
                        class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $hasActivePlan ? 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' : 'bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300' }}">
                            @if($hasActivePlan)
                            {{ reseller_fa_num(count($quotasSummary['quotas'])) }} منبع
                        @else
                            {{ __('resellerhub::messages.subscription.views.no_active_plan_short') }}
                        @endif
                        </span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">آمار لحظه‌ای استفاده از ظرفیت ماژول‌های فعال در این
                    نرم‌افزار</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('resellerhub::messages.subscription.views.realtime_calc') }}
                </span>

            {{-- Toggle Button --}}
            <button type="button"
                    @click="toggleSection('quotas')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200/70 dark:border-gray-700 transition-all cursor-pointer group shadow-2xs">
                <span
                    x-text="sectionQuotasOpen ? '{{ __('resellerhub::messages.subscription.views.close_section') }}' : '{{ __('resellerhub::messages.subscription.views.open_section') }}'"></span>
                <svg class="w-4 h-4 transition-transform duration-300 ease-out group-hover:scale-110"
                     :class="sectionQuotasOpen ? 'rotate-180 text-indigo-600 dark:text-indigo-400' : 'text-gray-400'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="sectionQuotasOpen"
         x-transition:enter="transition ease-out duration-300 origin-top"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-y-95 max-h-0"
         x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave="transition ease-in duration-200 origin-top"
         x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-y-95"
         class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(!$hasActivePlan)
            <div
                class="col-span-full rounded-2xl p-6 sm:p-8 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-amber-900 dark:text-amber-200">سهمیه‌ها و ظرفیت منابع
                            هنوز تخصیص نیافته‌اند</h4>
                        <p class="text-xs text-amber-700/90 dark:text-amber-300/90 mt-1 max-w-2xl leading-relaxed">
                            به دلیل اینکه هنوز پکیج اشتراکی معتبری برای این سازمان فعال نشده است، دسترسی به ظرفیت
                            منابع (تعداد فاکتورها، پرونده‌ها، نوبت‌ها و ...) موقتاً غیرفعال است. جهت تخصیص سهمیه‌ها
                            و شروع استفاده، لطفاً پکیج مورد نظر خود را انتخاب فرمایید.
                        </p>
                    </div>
                </div>
                <button @click="upgradeModal = true" type="button"
                        class="shrink-0 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/25 transition cursor-pointer">
                    {{ $hasNoPackage ? __('resellerhub::messages.subscription.views.select_package_btn') : __('resellerhub::messages.subscription.views.pay_and_activate_pkg') }}
                </button>
            </div>
        @elseif(empty($quotasSummary['quotas']))
            <div
                class="col-span-full rounded-2xl p-6 bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-700/60 text-center py-6">
                <p class="text-xs text-gray-500 dark:text-gray-400">برای این پکیج سهمیه عددی محدودی تعریف نشده
                    است.</p>
            </div>
        @else
            @foreach($quotasSummary['quotas'] as $qKey => $qItem)
                <div
                    class="rounded-2xl p-5 bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/70 shadow-xs hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-700/60 transition-all duration-300 flex flex-col justify-between gap-4 group">
                    <div>
                        {{-- Card Header --}}
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border border-current/15 {{ $qItem['icon_bg_class'] }}">
                                    @if(($qItem['icon'] ?? '') === 'users')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    @elseif(($qItem['icon'] ?? '') === 'calendar')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif(($qItem['icon'] ?? '') === 'receipt')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                    @elseif(($qItem['icon'] ?? '') === 'shopping-bag')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    @elseif(($qItem['icon'] ?? '') === 'user-md')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @endif
                                </span>
                                <div>
                                    <h4 class="text-xs font-black text-gray-900 dark:text-white leading-tight">{{ $qItem['title'] }}</h4>
                                    <span
                                        class="text-xs text-gray-400 block">{{ $qItem['module_title'] }}</span>
                                </div>
                            </div>

                            @if($qItem['is_unlimited'])
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-black bg-blue-50 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">نامحدود ∞</span>
                            @elseif($qItem['is_exceeded'])
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800">تکمیل سقف</span>
                            @else
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-black {{ $qItem['percent_badge_class'] }}">{{ reseller_fa_num($qItem['percent']) }}٪</span>
                            @endif
                        </div>

                        {{-- Usage Number --}}
                        <div class="mt-4 flex items-baseline justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">میزان مصرف:</span>
                            <div class="font-bold text-gray-900 dark:text-white flex items-baseline gap-1">
                                <span
                                    class="text-lg font-black">{{ reseller_fa_num(number_format($qItem['used'])) }}</span>
                                <span class="text-xs text-gray-400 font-normal">از</span>
                                @if($qItem['is_unlimited'])
                                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400">نامحدود</span>
                                @else
                                    <span
                                        class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ reseller_fa_num(number_format($qItem['limit'])) }}</span>
                                    <span
                                        class="text-xs text-gray-400 font-normal">{{ $qItem['unit'] ?? '' }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-2.5">
                            @if(!$qItem['is_unlimited'])
                                <div
                                    class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-700/80 overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-700 ease-out {{ $qItem['progress_bar_class'] }}"
                                        style="width: {{ min(100, $qItem['percent']) }}%;"></div>
                                </div>
                            @else
                                <div class="w-full h-2 rounded-full bg-blue-50 dark:bg-blue-950/60 overflow-hidden">
                                    <div class="h-full rounded-full bg-blue-500 w-full opacity-60"></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div
                        class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                        @if(!$qItem['is_unlimited'])
                            <span class="text-gray-400 font-medium">باقیمانده: <strong
                                    class="text-gray-700 dark:text-gray-300 font-bold">{{ reseller_fa_num(number_format($qItem['remaining'])) }}</strong> {{ $qItem['unit'] ?? '' }}</span>
                            <button @click="upgradeModal = true" type="button"
                                    class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold hover:underline cursor-pointer group">
                                <span>ارتقای پکیج</span>
                                <svg
                                    class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </button>
                        @else
                            <span class="text-blue-600 dark:text-blue-400 font-medium">ظرفیت کاملاً نامحدود</span>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

