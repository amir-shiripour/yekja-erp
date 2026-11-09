<div
    class="w-full rounded-2xl p-6 sm:p-8 bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/60 shadow-xs relative overflow-hidden flex flex-col justify-between">
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

            <button @click="activeTab = 'package_upgrade'; window.scrollTo({top: 0, behavior: 'smooth'})" type="button"
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

        {{-- تایم‌لاین بصری پیشرفت دوره اشتراک --}}
        @if($hasActivePlan && $expiresAt && $daysRemaining > 0)
            @php
                $totalDaysEst = $daysRemaining > 30 ? (ceil($daysRemaining / 30) * 30) : 30;
                $passedDaysEst = max(0, $totalDaysEst - $daysRemaining);
                $progressPercent = min(100, max(5, round(($passedDaysEst / $totalDaysEst) * 100)));
            @endphp
            <div
                class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/60 shadow-2xs space-y-2.5">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span class="flex items-center gap-1.5 text-gray-700 dark:text-gray-200">
                        <span
                            class="w-2 h-2 rounded-full {{ $daysRemaining <= 7 ? 'bg-amber-500' : 'bg-emerald-500' }} animate-pulse"></span>
                        پیشرفت دوره اشتراک جاری:
                    </span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-black tabular-nums">
                        {{ reseller_fa_num($daysRemaining) }} روز تا پایان اعتبار
                    </span>
                </div>
                <div
                    class="relative w-full h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden p-0.5 border border-gray-200/60 dark:border-gray-700/60">
                    <div
                        class="h-full rounded-full transition-all duration-700 ease-out bg-gradient-to-l {{ $daysRemaining <= 7 ? 'from-amber-500 to-rose-500' : 'from-indigo-500 to-emerald-500' }}"
                        style="width: {{ $progressPercent }}%;"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500">
                    <span>آغاز دوره جاری</span>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">تاریخ پایان اعتبار: <strong
                            class="font-bold text-gray-900 dark:text-white">{{ reseller_fa_num($expiresAtJalali) }}</strong></span>
                </div>
            </div>
        @endif

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
