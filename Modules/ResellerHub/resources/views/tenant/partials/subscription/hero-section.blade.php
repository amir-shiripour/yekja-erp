<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
    <div
        class="lg:col-span-7 xl:col-span-8 rounded-3xl p-6 sm:p-8 bg-gradient-to-br from-indigo-50/70 via-white to-purple-50/40 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 border border-indigo-100/90 dark:border-gray-700/70 shadow-xs relative overflow-hidden flex flex-col justify-between">
        {{-- Ambient background glow --}}
        <div
            class="absolute -top-24 -left-24 w-72 h-72 bg-indigo-500/10 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div
            class="absolute -bottom-24 -right-24 w-60 h-60 bg-purple-500/10 dark:bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            {{-- Top Badge Row --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    @if(!$hasActivePlan)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/80">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                {{ __('resellerhub::messages.subscription.views.no_active_plan_short') }}
                            </span>
                    @elseif($isExpired)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/80">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                {{ __('resellerhub::messages.subscription.views.expired_badge') }}
                            </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ __('resellerhub::messages.subscription.views.active_badge') }}
                            </span>
                    @endif

                    <span
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300 border border-gray-200/70 dark:border-gray-700">
                            {{ __('resellerhub::messages.subscription.views.org_prefix') }} <strong
                            class="font-bold text-gray-900 dark:text-white">{{ $tenant->name }}</strong>
                        </span>
                </div>

                <button @click="upgradeModal = true" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>{{ $hasActivePlan ? __('resellerhub::messages.subscription.views.upgrade_or_renew_btn') : __('resellerhub::messages.subscription.views.select_package_btn') }}</span>
                </button>
            </div>

            {{-- Hero Title and Plan Info --}}
            <div class="space-y-2">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">پلن
                    اشتراکی سامانه
                </div>
                <div class="flex flex-wrap items-baseline gap-3">
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $packageName }}</h2>
                    <span
                        class="text-xs text-gray-400 font-medium">{{ __('resellerhub::messages.subscription.views.hero_subtitle') }}</span>
                </div>
            </div>

            {{-- Metric Cards Strip --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                {{-- Expiry Date --}}
                <div
                    class="p-4 rounded-2xl bg-white/95 dark:bg-gray-900/60 border border-indigo-100/90 dark:border-gray-700/60 shadow-xs backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('resellerhub::messages.subscription.views.expiry_date_label') }}</span>
                        <span
                            class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $hasActivePlan ? 'پایان دوره' : 'وضعیت' }}</span>
                    </div>
                    <div
                        class="text-xl font-black text-gray-900 dark:text-white mt-1 tracking-tight">{{ $hasActivePlan ? reseller_fa_num($expiresAtJalali) : '—' }}</div>
                </div>

                {{-- Days Remaining --}}
                <div
                    class="p-4 rounded-2xl bg-white/95 dark:bg-gray-900/60 border border-indigo-100/90 dark:border-gray-700/60 shadow-xs backdrop-blur-md">
                    <div class="flex items-center justify-between">
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('resellerhub::messages.subscription.views.license_remaining_term') }}</span>
                        @if(!empty($isExpiringSoon))
                            <span
                                class="text-xs font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">{{ __('resellerhub::messages.subscription.views.capacity_warning') }}</span>
                        @endif
                    </div>
                    <div
                        class="text-xl font-black {{ $hasActivePlan ? ($daysRemainingClass ?? 'text-emerald-600 dark:text-emerald-400') : 'text-gray-400 dark:text-gray-500' }} mt-1 tracking-tight">
                        @if(!$hasActivePlan)
                            —
                        @elseif($daysRemaining > 0)
                            {{ reseller_fa_num($daysRemaining) . ' روز' }}
                        @elseif($isExpired)
                            منقضی شده
                        @else
                            {{ __('resellerhub::messages.subscription.views.unlimited') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Verified Reseller & Support Concierge--}}
    <div
        class="lg:col-span-5 xl:col-span-4 rounded-3xl p-6 sm:p-7 bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between relative overflow-hidden">
        <div class="space-y-5">
            {{-- Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-1.5 text-xs font-black text-gray-900 dark:text-white">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span>نماینده رسمی ارائه‌دهنده سرویس</span>
                </div>
                <span
                    class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300">تأیید شده</span>
            </div>

            {{-- Partner Identity --}}
            <div class="flex items-center gap-4">
                @if(!empty($resellerInfo['brand_logo']))
                    <img src="{{ asset('storage/' . $resellerInfo['brand_logo']) }}" alt="لوگوی نماینده"
                         class="w-14 h-14 rounded-2xl object-contain bg-gray-50 dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700 p-1.5 shadow-2xs">
                @else
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-black text-xl flex items-center justify-center shadow-md shadow-indigo-600/20 shrink-0">
                        {{ mb_substr($resellerInfo['brand_title'] ?: $resellerInfo['company_name'], 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h3 class="text-base font-black text-gray-900 dark:text-white truncate">{{ $resellerInfo['brand_title'] ?: $resellerInfo['company_name'] }}</h3>
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $resellerInfo['company_name'] }}</p>
                </div>
            </div>

            {{-- Support Contact Channels --}}
            <div class="space-y-2.5 pt-1">
                @if(!empty($resellerInfo['support_phone']))
                    <a href="tel:{{ $resellerInfo['support_phone'] }}"
                       class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/60 text-gray-700 dark:text-gray-200 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-white dark:hover:bg-gray-900 transition group">
                            <span
                                class="flex items-center gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                <span
                                    class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </span>
                                <span>تماس تلفنی پشتیبانی:</span>
                            </span>
                        <span
                            class="font-bold text-xs text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ reseller_fa_num($resellerInfo['support_phone']) }}</span>
                    </a>
                @endif

                @if(!empty($resellerInfo['support_email']))
                    <a href="mailto:{{ $resellerInfo['support_email'] }}"
                       class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/60 text-gray-700 dark:text-gray-200 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-white dark:hover:bg-gray-900 transition group">
                            <span
                                class="flex items-center gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                <span
                                    class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <span>ایمیل ارتباطی:</span>
                            </span>
                        <span
                            class="font-bold text-xs dir-ltr truncate max-w-[150px] text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $resellerInfo['support_email'] }}</span>
                    </a>
                @endif
            </div>
        </div>

        @if(!empty($resellerInfo['footer_text']))
            <p class="text-xs text-gray-400 dark:text-gray-500 text-center border-t border-gray-100 dark:border-gray-700/60 pt-3 mt-4">{{ $resellerInfo['footer_text'] }}</p>
        @endif
    </div>
</div>

