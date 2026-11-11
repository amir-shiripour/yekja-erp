@php
    use Modules\ResellerHub\App\Enums\OnboardingStep;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\TenantOnboardingState;
    use Modules\ResellerHub\App\Models\ResellerPackage;
    use Modules\ResellerHub\App\Models\ResellerSetting;

    $percentage = $progress['percentage'] ?? 0;
    $completedCount = $progress['completed'] ?? 0;
    $totalCount = $progress['total'] ?? 0;
    $nextStep = $progress['next_step'] ?? null;

    $journeyData = $progress['journeys'][0] ?? null;
    $steps = $journeyData['steps'] ?? [];

    $tourConfig = $tourConfig ?? ResellerSetting::getOnboardingTourConfig($tenant->reseller_id);
    $celebrationTitle = !empty($tourConfig['celebration_title']) ? $tourConfig['celebration_title'] : 'تبریک! سامانه CRM شما ۱۰۰٪ پیکربندی و فعال شد';
    $celebrationMessage = !empty($tourConfig['celebration_message']) ? $tourConfig['celebration_message'] : 'احراز هویت، دامنه اختصاصی، برند وایت‌لیبل و پکیج شما فعال هستند. تمامی ماژول‌ها در سایدبار در دسترس شما قرار دارند.';
    $displayMode = $tourConfig['display_mode'] ?? 'bento';
@endphp

<div class="relative w-full transition-all duration-300 mb-6"
     dir="rtl"
     wire:poll.10s="refreshWidget"
     @onboarding-tour-updated.window="$wire.refreshWidget()"
     x-data="{}"
     x-init="if (window.BroadcastChannel) { const ch = new BroadcastChannel('crm_onboarding_sync'); ch.onmessage = (e) => { if (e.data && e.data.type === 'tour-updated') { $wire.refreshWidget(); } }; }">

    {{-- Alert / Toast Notifications --}}
    @if (!empty($successMessage))
        <div
            class="mb-4 flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-800 dark:text-emerald-200 text-xs font-bold shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-500 text-white shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', '')"
                    class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-100 p-1 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if (!empty($isCompleted) || !empty($isTourDismissedOrPostponed))

    @elseif ($isDismissed)
        <div
            wire:click="reopen"
            role="button"
            tabindex="0"
            title="کلیک برای نمایش مراحل راه‌اندازی"
            class="group cursor-pointer flex items-center justify-between px-5 py-3.5 bg-white dark:bg-gray-800 hover:bg-indigo-50/40 dark:hover:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600/50 shadow-xs hover:shadow-md transition-all select-none">
            <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
                <span
                    class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    راه‌اندازی گام‌به‌گام سامانه
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ms-1.5">({{ reseller_fa_num($percentage) }}٪ تکمیل شده)</span>
                    @if(!empty($onlyOptionalRemaining))
                        <span
                            class="ms-2 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">مراحل باقیمانده اختیاری است</span>
                    @endif
                </span>
            </div>
            <button
                type="button"
                wire:click.stop="reopen"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/70 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all border border-indigo-200/80 dark:border-indigo-800 cursor-pointer"
            >
                <span>نمایش مراحل راه‌اندازی</span>
                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- State 2: Finished Celebratory Banner --}}
    @elseif ($isFinished)
        <div
            class="relative overflow-hidden rounded-2xl border border-emerald-200 dark:border-emerald-800/80 bg-white dark:bg-gray-800 p-6 sm:p-7 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div
                        class="flex items-center justify-center w-13 h-13 rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 shrink-0">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                {{ $celebrationTitle }}
                            </h3>
                            <span
                                class="px-2.5 py-0.5 text-xs font-black rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                ۱۰۰٪
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                            {{ $celebrationMessage }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-center">
                    <button type="button"
                        wire:click="openPackageModal"
                        class="px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all border border-indigo-200 dark:border-indigo-800 cursor-pointer"
                    >
                        مدیریت پکیج و تمدید
                    </button>
                    <button type="button"
                        wire:click="dismiss"
                        class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/60 rounded-xl transition-all cursor-pointer"
                    >
                        بستن پیام
                    </button>
                </div>
            </div>
        </div>

        {{-- State 3: Collapsed Compact Bar --}}
    @elseif ($isCollapsed)
        <div
            wire:click="toggleCollapse"
            role="button"
            tabindex="0"
            @keydown.enter="$wire.toggleCollapse()"
            @keydown.space.prevent="$wire.toggleCollapse()"
            title="کلیک برای باز کردن و مشاهده مراحل تور راه‌اندازی"
            class="group cursor-pointer bg-white dark:bg-gray-800 hover:bg-indigo-50/40 dark:hover:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600/50 p-4 sm:p-5 shadow-xs hover:shadow-md transition-all duration-200 select-none">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {{-- Left: Icon & Title & Summary --}}
                <div class="flex items-center gap-3.5 min-w-0">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/25 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                راه‌اندازی سامانه CRM
                            </span>
                            <span
                                class="px-2 py-0.5 text-xs font-black rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 shrink-0">
                                {{ reseller_fa_num($percentage) }}٪
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ reseller_fa_num($completedCount) }} از {{ reseller_fa_num($totalCount) }} گام راه‌اندازی
                            انجام شده است — <span class="text-indigo-600 dark:text-indigo-400 font-medium">کلیک برای مشاهده مراحل</span>
                        </p>
                    </div>
                </div>

                {{-- Right: Progress Bar & Next Step Action & Expand Button --}}
                <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 flex-wrap sm:flex-nowrap">
                    <div
                        wire:click.stop="toggleCollapse"
                        class="flex items-center gap-2 flex-1 sm:w-44 cursor-pointer hover:opacity-90 transition-opacity"
                        title="پیشرفت کل: {{ $percentage }}٪ (کلیک برای باز کردن تور)">
                        <div
                            class="flex-1 bg-gray-100 dark:bg-gray-700/80 rounded-full h-2.5 overflow-hidden border border-gray-200/50 dark:border-gray-700"
                            dir="rtl">
                            <div
                                class="h-full rounded-full bg-indigo-600 transition-all duration-500 shadow-xs"
                                style="width: {{ (int)$percentage }}%; min-width: {{ $percentage > 0 ? '6px' : '0' }};"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0">{{ reseller_fa_num($percentage) }}٪</span>
                    </div>

                    @if ($nextStep)
                        @php
                            $nSlug = $nextStep->slug;
                        @endphp
                        @if ($nSlug === 'complete_kyc')
                            <button
                                type="button"
                                wire:click.stop="openKycModal"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                                <span>{{ $nextStep->title }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                        @elseif ($nSlug === 'configure_domain')
                            <button
                                type="button"
                                wire:click.stop="openDomainModal"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                                <span>{{ $nextStep->title }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                        @elseif ($nSlug === 'configure_branding')
                            <button
                                type="button"
                                wire:click.stop="openBrandingModal"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                                <span>{{ $nextStep->title }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                        @elseif ($nSlug === 'select_package')
                            <button
                                type="button"
                                wire:click.stop="openPackageModal"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                                <span>{{ $nextStep->title }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                        @else
                            @php
                                $isNextQuotaLocked = false;
                                if ($journeyData && !empty($journeyData['steps'])) {
                                    foreach ($journeyData['steps'] as $jst) {
                                        if ($jst['step']->slug === $nSlug && !empty($jst['is_quota_locked'])) {
                                            $isNextQuotaLocked = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            @if($isNextQuotaLocked)
                                <button
                                    type="button"
                                    wire:click.stop="handleQuotaLockedStep('{{ $nSlug }}')"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>{{ $nextStep->title }} ({{ __('resellerhub::messages.onboarding.step_quota_locked_badge') }})</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    @if(in_array($nextStep->actionType, ['custom_fields', 'form'], true)) wire:click.stop="openCustomFieldsModal('{{ $nSlug }}')"
                                    @elseif($nextStep->actionType === 'link' && $nextStep->actionUrl) onclick="window.open('{{ $nextStep->actionUrl }}', '_blank')"
                                    @else wire:click.stop="completeCustomStep('{{ $nSlug }}')" @endif
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                                    <span>{{ $nextStep->title }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @endif
                        @endif
                    @endif

                    @if(!empty($onlyOptionalRemaining))
                        <button
                            type="button"
                            wire:click.stop="postponeOptionalTour"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-xs transition-all cursor-pointer shrink-0"
                            title="ورود به پیشخوان و تکمیل موارد اختیاری در آینده">
                            <span>پر کردن بعداً</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    @endif

                    {{-- Explicit Expand Button with Text & Icon --}}
                    <button
                        type="button"
                        wire:click.stop="toggleCollapse"
                        title="مشاهده و باز کردن مراحل راه‌اندازی"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 dark:bg-indigo-950/70 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all border border-indigo-200/80 dark:border-indigo-800 cursor-pointer shrink-0">
                        <span>مشاهده مراحل تور</span>
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- State 4: Expanded Stepper --}}
    @else
        <div
            class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs p-6 sm:p-8 space-y-7">


            {{-- Header Row --}}
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 dark:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                                مراحل راه‌اندازی و فعال‌سازی سامانه CRM
                            </h2>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                {{ reseller_fa_num($percentage) }}٪ تکمیل شده
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ reseller_fa_num($completedCount) }} از {{ reseller_fa_num($totalCount) }} مرحله تکمیل شده
                            است. @fa(4) مرحله
                            کلیدی زیر را برای آغاز به کار سامانه طی کنید.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end md:self-auto flex-wrap">
                    @if(!empty($onlyOptionalRemaining))
                        <button
                            type="button"
                            wire:click="postponeOptionalTour"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer shrink-0"
                            title="ورود به پیشخوان و تکمیل موارد اختیاری در آینده"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>پر کردن بعداً و ورود به پیشخوان</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    @endif

                    <button type="button"
                        wire:click="toggleCollapse"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-all border border-gray-200/80 dark:border-gray-600 cursor-pointer"
                    >
                        <span>جمع کردن</span>
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                    <button type="button"
                        wire:click="dismiss"
                        title="مخفی کردن موقت"
                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border border-gray-200/80 dark:border-gray-600 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            @if(!empty($onlyOptionalRemaining))
                <div
                    class="p-4 sm:p-5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span
                            class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-500/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs sm:text-sm font-black text-emerald-950 dark:text-emerald-200">
                                    تمامی مراحل پایه و اجباری راه‌اندازی با موفقیت انجام شدند
                                </h3>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
                                    گام‌های باقیمانده اختیاری هستند
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">
                                بخش‌های باقیمانده برای شخصی‌سازی بیشتر و اختیاری هستند. می‌توانید هم‌اکنون وارد پیشخوان
                                شوید و هر زمان مایل بودید آن‌ها را تکمیل کنید.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="postponeOptionalTour"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md shadow-emerald-500/25 transition-all cursor-pointer shrink-0"
                    >
                        <span>پر کردن بعداً و ورود به پیشخوان</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Interactive Enterprise 4-Step Connected Progress Stepper --}}
            <div
                class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-2xs">
                <div class="relative flex items-center justify-between max-w-4xl mx-auto px-4 sm:px-8">
                    {{-- Connected Background Track --}}
                    <div
                        class="absolute top-5 start-8 end-8 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full -translate-y-1/2 z-0">
                        {{-- Filled Progress Track Line --}}
                        <div
                            class="h-full rounded-full bg-indigo-600 transition-all duration-700 shadow-xs"
                            style="width: {{ (int)$percentage }}%; min-width: {{ $percentage > 0 ? '6px' : '0' }};"></div>
                    </div>

                    @foreach($steps as $idx => $item)
                        @php
                            $sData = $item['step'];
                            $sStatus = $item['status'];
                            $isDone = ($sStatus === TenantOnboardingState::STATUS_COMPLETED);
                            $isSkipped = ($sStatus === TenantOnboardingState::STATUS_SKIPPED);
                            $isAccessible = $item['is_accessible'];
                            $isQuotaLocked = !empty($item['is_quota_locked']);
                            $stepNum = $idx + 1;
                        @endphp
                        @php
                            $stepUrl = $this->getStepActionUrl($sData);
                            $isExternal = in_array(strtolower($sData->actionType), ['external', 'link'], true);
                            $stepTitleColor = 'text-gray-400';
                            if ($isDone) {
                                $stepTitleColor = 'text-emerald-600 dark:text-emerald-400';
                            } elseif ($isAccessible) {
                                $stepTitleColor = 'text-indigo-600 dark:text-indigo-400';
                            }
                            $stepBadgeText = $isAccessible ? 'گام جاری' : 'در انتظار';
                            if (!empty($sData->badge)) {
                                $stepBadgeText = $sData->badge;
                            }
                        @endphp
                        <button type="button"
                                @if($isAccessible)
                                    @if($isQuotaLocked && !$isDone) wire:click="handleQuotaLockedStep('{{ $sData->slug }}')"
                                @elseif($sData->slug === 'complete_kyc') wire:click="openKycModal"
                                @elseif($sData->slug === 'configure_domain') wire:click="openDomainModal"
                                @elseif($sData->slug === 'configure_branding') wire:click="openBrandingModal"
                                @elseif($sData->slug === 'select_package') wire:click="openPackageModal"
                                @elseif(in_array(strtolower($sData->actionType), ['custom_fields', 'form'], true)) wire:click="openCustomFieldsModal('{{ $sData->slug }}')"
                                @elseif($stepUrl)
                                    onclick="{{ $isExternal ? "window.open('{$stepUrl}', '_blank')" : "window.location.href = '{$stepUrl}'" }}"
                                @elseif($sData->canManualComplete && !$isDone)
                                    wire:click="completeCustomStep('{{ $sData->slug }}')"
                                @endif
                                @endif
                                class="relative z-10 flex flex-col items-center group {{ $isAccessible ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }} focus:outline-hidden">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-xs
                                @if ($isDone)
                                    bg-emerald-500 text-white shadow-emerald-500/20 scale-105
                                @elseif ($isSkipped)
                                    bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                                @elseif ($isQuotaLocked)
                                    bg-amber-500 text-white shadow-amber-500/20 ring-2 ring-amber-500/30 scale-105
                                @elseif ($isAccessible)
                                    bg-indigo-600 text-white ring-2 ring-indigo-600/30 shadow-xs scale-105
                                @else
                                    bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700
                                @endif
                            ">
                                @if ($isDone)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif ($isSkipped)
                                    <span class="text-xs">رد شد</span>
                                @elseif ($isQuotaLocked)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                @else
                                    <span>@fa($stepNum)</span>
                                @endif
                            </div>
                            <div class="text-center mt-2 max-w-[110px]">
                                <span
                                    class="text-xs font-bold block truncate transition-colors {{ $stepTitleColor }}">
                                    @fa($stepNum). {{ $sData->title }}
                                </span>
                                <span class="text-xs hidden sm:block mt-0.5">
                                    @if ($isDone)
                                        <span
                                            class="inline-flex items-center justify-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>تکمیل شد</span>
                                        </span>
                                    @elseif ($isSkipped)
                                        <span class="text-gray-400">صرف‌نظر شد</span>
                                    @else
                                        <span
                                            class="text-gray-400 dark:text-gray-500">{{ $stepBadgeText }}</span>
                                    @endif
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Dynamic Enterprise Step Bento Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(4, max(1, count($steps))) }} gap-4 pt-1">
                @foreach($steps as $idx => $item)
                    @php
                        $sData = $item['step'];
                        $sStatus = $item['status'];
                        $isDone = ($sStatus === TenantOnboardingState::STATUS_COMPLETED);
                        $isSkipped = ($sStatus === TenantOnboardingState::STATUS_SKIPPED);
                        $isAccessible = $item['is_accessible'];
                        $isQuotaLocked = !empty($item['is_quota_locked']);
                        $stepNum = $idx + 1;

                        $badgeText = 'قفل شده';
                        if ($isDone) {
                            $badgeText = 'تکمیل شد';
                        } elseif ($isSkipped) {
                            $badgeText = 'رد شد';
                        } elseif ($isQuotaLocked) {
                            $badgeText = __('resellerhub::messages.onboarding.step_quota_locked_badge');
                        } elseif ($isAccessible) {
                            $badgeText = !empty($sData->badge) ? $sData->badge : ('گام ' . $stepNum);
                        }

                        $btnClass = 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed';
                        if ($isAccessible) {
                            $btnClass = $isDone
                                ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer'
                                : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 active:scale-95 cursor-pointer';
                        }

                        $packageBtnClass = 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed';
                        if ($isAccessible) {
                            $packageBtnClass = $isDone
                                ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer'
                                : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 active:scale-95 cursor-pointer';
                        }
                    @endphp
                    <div class="group relative rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between
                        @if ($isDone)
                            bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/60
                        @elseif ($isSkipped)
                            bg-gray-50/60 dark:bg-gray-900/40 border-gray-200 dark:border-gray-800 opacity-70
                        @elseif ($isAccessible)
                            bg-white dark:bg-gray-800/90 border-indigo-200 dark:border-indigo-800/70 shadow-sm ring-2 ring-indigo-500/20
                        @else
                            bg-gray-50/50 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700/60 opacity-60
                        @endif
                    ">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl text-xs font-black
                                    @if ($isDone)
                                        bg-emerald-500 text-white shadow-sm shadow-emerald-500/30
                                    @elseif ($isSkipped)
                                        bg-gray-400 text-white
                                    @elseif ($isAccessible)
                                        bg-indigo-600 text-white shadow-sm shadow-indigo-600/30
                                    @else
                                        bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400
                                    @endif
                                ">
                                    @if ($isDone)
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif ($isSkipped)
                                        -
                                    @else
                                        @fa($stepNum)
                                    @endif
                                </span>

                                <div class="flex items-center gap-1.5">
                                    @if(!$sData->isCore && (!$sData->isRequired || $isQuotaLocked) && !$isDone && !$isSkipped)
                                        <button type="button"
                                                wire:click="skipStep('{{ $sData->slug }}')"
                                                title="{{ $isQuotaLocked ? __('resellerhub::messages.onboarding.skip_quota_locked_step') : 'رد کردن این مرحله اختیاری' }}"
                                                class="text-xs {{ $isQuotaLocked ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }} px-1.5 py-0.5 rounded-md cursor-pointer transition-colors">
                                            {{ __('رد کردن') }}
                                        </button>
                                    @endif
                                    <span class="text-xs font-extrabold px-2 py-0.5 rounded-full
                                        @if ($isDone)
                                            bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                        @elseif ($isSkipped)
                                            bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                                        @elseif ($isQuotaLocked)
                                            bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800
                                        @elseif ($isAccessible)
                                            bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300
                                        @else
                                            bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400
                                        @endif
                                    ">
                                        {{ $badgeText }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span>{{ $sData->title }}</span>
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                    {{ $sData->description }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 mt-2 border-t border-gray-100 dark:border-gray-700/60">
                            @if ($sData->slug === 'complete_kyc')
                                <button type="button" wire:click="openKycModal"
                                        class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $isDone ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 active:scale-95' }}">
                                    <span>{{ $isDone ? 'ویرایش مشخصات هویتی' : 'ثبت و احراز هویت' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @elseif ($sData->slug === 'configure_domain')
                                <button type="button" @if($isAccessible) wire:click="openDomainModal" @endif
                                @disabled(!$isAccessible)
                                class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ $btnClass }}">
                                    <span>{{ $isDone ? 'تغییر تنظیمات دامنه' : 'تنظیم دامین' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @elseif ($sData->slug === 'configure_branding')
                                <button type="button" @if($isAccessible) wire:click="openBrandingModal" @endif
                                @disabled(!$isAccessible)
                                class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ $btnClass }}">
                                    <span>{{ $isDone ? 'تغییر وایت‌لیبل' : 'تنظیم وایت‌لیبل' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @elseif ($sData->slug === 'select_package')
                                <button type="button" @if($isAccessible) wire:click="openPackageModal" @endif
                                @disabled(!$isAccessible)
                                class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ $packageBtnClass }}">
                                    <span>{{ $isDone ? 'مدیریت و ارتقای پکیج' : 'انتخاب پکیج و پرداخت' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @else
                                {{-- Enterprise Custom / Modular Step Actions --}}
                                @php
                                    $stepUrl = $this->getStepActionUrl($sData);
                                    $isExternal = in_array(strtolower($sData->actionType), ['external', 'link'], true);
                                    $isQuotaLocked = !empty($item['is_quota_locked']);
                                @endphp

                                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                                    @if($isQuotaLocked && !$isDone)
                                        <div
                                            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
                                            <button type="button"
                                                    wire:click="handleQuotaLockedStep('{{ $sData->slug }}')"
                                                    class="flex-1 inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-xs cursor-pointer transition-all active:scale-95">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                <span>{{ __('resellerhub::messages.onboarding.step_quota_locked_cta') }}</span>
                                            </button>
                                            @if(!$sData->isCore)
                                                <button type="button"
                                                        wire:click="skipStep('{{ $sData->slug }}')"
                                                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold cursor-pointer transition-colors shrink-0">
                                                    <span>{{ __('resellerhub::messages.onboarding.skip_quota_locked_step') }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($stepUrl)
                                        @php
                                            $urlBtnText = 'انجام مرحله (ورود به صفحه)';
                                            if ($isDone) {
                                                $urlBtnText = 'ورود به این بخش';
                                            } elseif ($isExternal) {
                                                $urlBtnText = 'مشاهده لینک خارجی';
                                            }
                                        @endphp
                                        <a href="{{ $stepUrl }}"
                                           @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                                           class="flex-1 inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer {{ $isDone ? 'bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/25 active:scale-95' }}">
                                            <span>{{ $urlBtnText }}</span>
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                @if($isExternal)
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                @endif
                                            </svg>
                                        </a>

                                        @if($isDone)
                                            <div
                                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-xl border border-emerald-200/80 dark:border-emerald-800/80 text-xs font-black shrink-0">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>تکمیل شد</span>
                                            </div>
                                        @elseif($sData->canManualComplete)
                                            <button type="button"
                                                    wire:click="completeCustomStep('{{ $sData->slug }}')"
                                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer shrink-0">
                                                <span>تأیید انجام</span>
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @else
                                            <span
                                                class="text-xs text-gray-400 dark:text-gray-500 font-medium shrink-0 flex items-center gap-1"
                                                title="پس از ثبت اطلاعات در بخش مربوطه، این مرحله به‌صورت خودکار تکمیل می‌شود">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor"><path stroke-linecap="round"
                                                                                 stroke-linejoin="round"
                                                                                 stroke-width="2"
                                                                                 d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>تأیید خودکار</span>
                                            </span>
                                        @endif
                                    @elseif(in_array(strtolower($sData->actionType), ['custom_fields', 'form'], true))
                                        {{-- Dynamic Custom Fields Form Modal --}}
                                        @if(!$isDone)
                                            <button type="button"
                                                    wire:click="openCustomFieldsModal('{{ $sData->slug }}')"
                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer">
                                                <span>تکمیل و ثبت فیلدهای این مرحله</span>
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button"
                                                    wire:click="openCustomFieldsModal('{{ $sData->slug }}')"
                                                    class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-xl border border-emerald-200/80 dark:border-emerald-800/80 text-xs font-black hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>تکمیل شد (مشاهده یا ویرایش)</span>
                                            </button>
                                        @endif
                                    @else
                                        {{-- No Action URL: Pure manual checklist step --}}
                                        @if(!$isDone)
                                            <button type="button"
                                                    wire:click="completeCustomStep('{{ $sData->slug }}')"
                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md shadow-emerald-600/25 active:scale-95 transition-all cursor-pointer">
                                                <span>تأیید و ثبت انجام مرحله</span>
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @else
                                            <div
                                                class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-xl border border-emerald-200/80 dark:border-emerald-800/80 text-xs font-black">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>این مرحله با موفقیت تأیید شد</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Onboarding Step Modals --}}
    @include('resellerhub::livewire.dashboard.modals.kyc-modal')
    @include('resellerhub::livewire.dashboard.modals.domain-modal')
    @include('resellerhub::livewire.dashboard.modals.branding-modal')
    @include('resellerhub::livewire.dashboard.modals.package-modal')

    {{-- Custom Dynamic Fields Modal --}}
    @if ($showCustomFieldsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="custom-fields-modal-title" role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     wire:click="closeCustomFieldsModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200/80 dark:border-gray-700">

                    <div
                        class="p-6 bg-gray-50/80 dark:bg-gray-850 border-b border-gray-200/80 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 bg-indigo-600 text-white rounded-2xl shadow-md shadow-indigo-600/30">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white"
                                    id="custom-fields-modal-title">
                                    {{ $activeCustomStepTitle }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $activeCustomStepDescription ?: 'لطفاً فیلدهای زیر را برای تکمیل راه‌اندازی با دقت تکمیل نمایید.' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCustomFieldsModal"
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveCustomFields">
                        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                            @if(empty($activeCustomStepFields))
                                <div
                                    class="p-6 text-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                    فیلد سفارشی برای این مرحله پیکربندی نشده است. می‌توانید دکمه ثبت را جهت تأیید گام
                                    بزنید.
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($activeCustomStepFields as $field)
                                        @php
                                            $fName = $field['name'] ?? $field['field_name'] ?? '';
                                            $fLabel = $field['label'] ?? $fName;
                                            $fType = strtolower($field['type'] ?? $field['field_type'] ?? 'text');
                                            $fRequired = !empty($field['required']) || !empty($field['is_required']);
                                            $fPlaceholder = $field['placeholder'] ?? '';
                                            $isFull = ($fType === 'textarea');
                                        @endphp
                                        <div class="{{ $isFull ? 'sm:col-span-2' : 'sm:col-span-1' }} space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <label class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $fLabel }}
                                                    @if($fRequired)
                                                        <span class="text-rose-500 font-black">*</span>
                                                    @else
                                                        <span
                                                            class="text-[10px] text-gray-400 font-normal">(اختیاری)</span>
                                                    @endif
                                                </label>
                                            </div>

                                            @if($fType === 'textarea')
                                                <textarea wire:model.defer="customFieldsForm.{{ $fName }}"
                                                          rows="3"
                                                          placeholder="{{ $fPlaceholder }}"
                                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                                            @elseif($fType === 'select')
                                                @php
                                                    $selectOpts = [];
                                                    if (is_array($field['options'] ?? null)) {
                                                        $selectOpts = $field['options'];
                                                    } elseif (!empty($field['options'])) {
                                                        $selectOpts = array_map('trim', explode(',', $field['options']));
                                                    }
                                                @endphp
                                                <select wire:model.defer="customFieldsForm.{{ $fName }}"
                                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                                    <option value="">انتخاب کنید...</option>
                                                    @foreach($selectOpts as $opt)
                                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($fType === 'checkbox')
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900">
                                                    <span
                                                        class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{ $fPlaceholder ?: 'فعال‌سازی / تأیید' }}</span>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox"
                                                               wire:model.defer="customFieldsForm.{{ $fName }}"
                                                               value="1" class="sr-only peer">
                                                        <div
                                                            class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                    </label>
                                                </div>
                                            @elseif($fType === 'currency')
                                                <div class="relative">
                                                    <input type="number"
                                                           wire:model.defer="customFieldsForm.{{ $fName }}"
                                                           placeholder="{{ $fPlaceholder ?: 'مبلغ به تومان' }}"
                                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 pl-14 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                                    <span
                                                        class="absolute inset-y-0 left-0 flex items-center px-3 text-[11px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 rounded-l-xl">تومان</span>
                                                </div>
                                            @elseif($fType === 'url')
                                                <input type="url" wire:model.defer="customFieldsForm.{{ $fName }}"
                                                       placeholder="{{ $fPlaceholder ?: 'https://...' }}"
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                            @elseif($fType === 'time')
                                                <input type="time" wire:model.defer="customFieldsForm.{{ $fName }}"
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-mono text-center text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                            @elseif($fType === 'mobile')
                                                <input type="tel" wire:model.defer="customFieldsForm.{{ $fName }}"
                                                       placeholder="{{ $fPlaceholder ?: '۰۹xxxxxxxxx' }}"
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                            @else
                                                <input
                                                    type="{{ in_array($fType, ['number', 'email', 'date', 'tel']) ? $fType : 'text' }}"
                                                    wire:model.defer="customFieldsForm.{{ $fName }}"
                                                    placeholder="{{ $fPlaceholder }}"
                                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 {{ in_array($fType, ['number', 'tel', 'email']) ? 'dir-ltr text-left' : '' }}">
                                            @endif

                                            @error('customFieldsForm.' . $fName)
                                            <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div
                                class="p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 flex items-center gap-2.5 text-xs text-indigo-900 dark:text-indigo-300">
                                <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>اطلاعات ثبت‌شده در این گام به شکل پایدار در بخش پروفایل و اطلاعات کاربری شما ذخیره می‌شوند و بعد از پایان تور نیز همواره قابل مشاهده و ویرایش هستند.</span>
                            </div>
                        </div>

                        <div
                            class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <button
                                type="button"
                                wire:click="closeCustomFieldsModal"
                                class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer">
                                انصراف
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>ذخیره اطلاعات و تأیید گام</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @includeIf('resellerhub::partials.invoice-payment-modal')
    @includeIf('resellerhub::partials.renewal-limit-modal')
</div>
