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
            <button wire:click="$set('successMessage', '')"
                    class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-100 p-1 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- State 1: Dismissed Compact Pill --}}
    @if ($isDismissed)
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
                <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                    راه‌اندازی گام‌به‌گام سامانه
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 mr-1.5">({{ reseller_fa_num($percentage) }}٪ تکمیل شده)</span>
                </span>
            </div>
            <button
                type="button"
                wire:click.stop="reopen"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/70 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all border border-indigo-200/80 dark:border-indigo-800 cursor-pointer"
            >
                <span>نمایش مراحل راه‌اندازی</span>
                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- State 2: Finished Celebratory Banner --}}
    @elseif ($isFinished)
        <div
            class="relative overflow-hidden rounded-3xl border border-emerald-200 dark:border-emerald-800/80 bg-gradient-to-l from-emerald-500/10 via-teal-500/5 to-white dark:to-gray-800 p-6 sm:p-7 shadow-sm bg-white dark:bg-gray-800">
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
                    <button
                        wire:click="openPackageModal"
                        class="px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all border border-indigo-200 dark:border-indigo-800 cursor-pointer"
                    >
                        مدیریت پکیج و تمدید
                    </button>
                    <button
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
                            <span class="text-sm font-black text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                راه‌اندازی سامانه CRM
                            </span>
                            <span
                                class="px-2 py-0.5 text-xs font-black rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 shrink-0">
                                {{ reseller_fa_num($percentage) }}٪
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ reseller_fa_num($completedCount) }} از {{ reseller_fa_num($totalCount) }} گام راه‌اندازی انجام شده است — <span class="text-indigo-600 dark:text-indigo-400 font-medium">کلیک برای مشاهده مراحل</span>
                        </p>
                    </div>
                </div>

                {{-- Right: Progress Bar & Next Step Action & Expand Button --}}
                <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 flex-wrap sm:flex-nowrap">
                    <div
                        wire:click.stop="toggleCollapse"
                        class="flex items-center gap-2 flex-1 sm:w-44 cursor-pointer hover:opacity-90 transition-opacity"
                        title="پیشرفت کل: {{ $percentage }}٪ (کلیک برای باز کردن تور)">
                        <div class="flex-1 bg-gray-100 dark:bg-gray-700/80 rounded-full h-2.5 overflow-hidden border border-gray-200/50 dark:border-gray-700" dir="rtl">
                            <div
                                class="h-full rounded-full bg-indigo-600 transition-all duration-500 shadow-xs"
                                style="width: {{ (int)$percentage }}%; min-width: {{ $percentage > 0 ? '6px' : '0' }}; background-color: #4f46e5 !important; background: linear-gradient(to left, #4f46e5, #6366f1) !important;"></div>
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
                                    @if($nextStep->actionType === 'link' && $nextStep->actionUrl) onclick="window.open('{{ $nextStep->actionUrl }}', '_blank')"
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

                    {{-- Explicit Expand Button with Text & Icon --}}
                    <button
                        type="button"
                        wire:click.stop="toggleCollapse"
                        title="مشاهده و باز کردن مراحل راه‌اندازی"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 dark:bg-indigo-950/70 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all border border-indigo-200/80 dark:border-indigo-800 cursor-pointer shrink-0">
                        <span>مشاهده مراحل تور</span>
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- State 4: Expanded Full Enterprise 2026 Stepper --}}
    @else
        <div
            class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-md p-6 sm:p-8 space-y-7">
            {{-- Background decorative subtle glow --}}
            <div
                class="absolute -top-24 -left-24 w-80 h-80 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

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

                <div class="flex items-center gap-2 self-end md:self-auto">
                    <button
                        wire:click="toggleCollapse"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-all border border-gray-200/80 dark:border-gray-600 cursor-pointer"
                    >
                        <span>جمع کردن</span>
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                    <button
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

            {{-- Interactive Enterprise 4-Step Connected Progress Stepper --}}
            <div
                class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-2xs">
                <div class="relative flex items-center justify-between max-w-4xl mx-auto px-4 sm:px-8">
                    {{-- Connected Background Track --}}
                    <div
                        class="absolute top-5 start-8 end-8 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full -translate-y-1/2 z-0">
                        {{-- Filled Progress Track Line --}}
                        <div
                            class="h-full rounded-full bg-indigo-600 transition-all duration-700 shadow-sm"
                            style="width: {{ (int)$percentage }}%; min-width: {{ $percentage > 0 ? '6px' : '0' }}; background-color: #4f46e5 !important; background: linear-gradient(to left, #4f46e5, #6366f1) !important;"></div>
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
                        @endphp
                        <button type="button"
                                @if($isAccessible)
                                    @if($isQuotaLocked && !$isDone) wire:click="handleQuotaLockedStep('{{ $sData->slug }}')"
                                @elseif($sData->slug === 'complete_kyc') wire:click="openKycModal"
                                @elseif($sData->slug === 'configure_domain') wire:click="openDomainModal"
                                @elseif($sData->slug === 'configure_branding') wire:click="openBrandingModal"
                                @elseif($sData->slug === 'select_package') wire:click="openPackageModal"
                                @elseif($stepUrl)
                                    @if($isExternal)
                                        onclick="window.open('{{ $stepUrl }}', '_blank')"
                                @else
                                    onclick="window.location.href = '{{ $stepUrl }}'"
                                @endif
                                @elseif($sData->canManualComplete && !$isDone)
                                    wire:click="completeCustomStep('{{ $sData->slug }}')"
                                @endif
                                @endif
                                class="relative z-10 flex flex-col items-center group {{ $isAccessible ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }} focus:outline-hidden">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm
                                @if ($isDone)
                                    bg-emerald-500 text-white shadow-emerald-500/30 scale-105
                                @elseif ($isSkipped)
                                    bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                                @elseif ($isQuotaLocked)
                                    bg-amber-500 text-white shadow-amber-500/30 ring-4 ring-amber-500/20 scale-105
                                @elseif ($isAccessible)
                                    bg-indigo-600 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/30 scale-110 animate-pulse
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
                                    class="text-xs font-bold block truncate transition-colors {{ $isDone ? 'text-emerald-600 dark:text-emerald-400' : ($isAccessible ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400') }}">
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
                                            class="text-gray-400 dark:text-gray-500">{{ $sData->badge ?: ($isAccessible ? 'گام جاری' : 'در انتظار') }}</span>
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
                        $stepNum = $idx + 1;
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
                                        {{ $isDone ? 'تکمیل شد' : ($isSkipped ? 'رد شد' : ($isQuotaLocked ? __('resellerhub::messages.onboarding.step_quota_locked_badge') : ($isAccessible ? ($sData->badge ?: 'گام ' . $stepNum) : 'قفل شده'))) }}
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
                                <button wire:click="openKycModal"
                                        class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $isDone ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 active:scale-95' }}">
                                    <span>{{ $isDone ? 'ویرایش مشخصات هویتی' : 'ثبت و احراز هویت' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @elseif ($sData->slug === 'configure_domain')
                                <button @if($isAccessible) wire:click="openDomainModal" @endif
                                @disabled(!$isAccessible)
                                class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ !$isAccessible ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed' : ($isDone ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 active:scale-95 cursor-pointer') }}">
                                    <span>{{ $isDone ? 'تغییر تنظیمات دامنه' : 'تنظیم دامین' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @elseif ($sData->slug === 'configure_branding')
                                <button @if($isAccessible) wire:click="openBrandingModal" @endif
                                @disabled(!$isAccessible)
                                class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ !$isAccessible ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed' : ($isDone ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/25 active:scale-95 cursor-pointer') }}">
                                    <span>{{ $isDone ? 'تغییر وایت‌لیبل' : 'تنظیم وایت‌لیبل' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                            @elseif ($sData->slug === 'select_package')
                                <button @if($isAccessible) wire:click="openPackageModal" @endif
                                @disabled(!$isAccessible)
                                class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ !$isAccessible ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed' : ($isDone ? 'bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 active:scale-95 cursor-pointer') }}">
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
                                        <a href="{{ $stepUrl }}"
                                           @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                                           class="flex-1 inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer {{ $isDone ? 'bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/25 active:scale-95' }}">
                                            <span>{{ $isDone ? 'ورود به این بخش' : ($isExternal ? 'مشاهده لینک خارجی' : 'انجام مرحله (ورود به صفحه)') }}</span>
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

    @if ($showKycModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                 wire:click="closeKycModal"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700">

                    {{-- Modal Header --}}
                    <div
                        class="p-6 bg-gradient-to-l from-indigo-50/80 via-white to-indigo-50/20 dark:from-indigo-950/40 dark:via-gray-800 dark:to-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <span
                                class="flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/25 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">
                                    احراز هویت و اطلاعات مدیر سامانه
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    اطلاعات تکمیلی هویتی جهت راه‌اندازی و اعتبارسنجی اولیه سامانه
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeKycModal"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body Form --}}
                    <form wire:submit="saveKyc">
                        <div class="p-6 space-y-4">
                            @if ($errors->has('kyc'))
                                <div
                                    class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold rounded-xl">
                                    {{ $errors->first('kyc') }}
                                </div>
                            @endif

                            @if ($subAccountFields->isEmpty())
                                <div class="text-center py-6">
                                    <div
                                        class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">فیلد احراز هویت اختصاصی
                                        تعیین نشده است</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        می‌توانید با فشردن دکمه زیر، مستقیماً این گام را تایید و تکمیل نمایید.
                                    </p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($subAccountFields as $field)
                                        @php
                                            $fieldName = $field->field_name;
                                            $fieldType = strtolower($field->field_type ?? 'text');
                                            $fieldErrorKey = 'kycForm.' . $fieldName;
                                        @endphp
                                        <div wire:key="kyc-field-{{ $field->id }}" class="space-y-1.5">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                                {{ $field->label }}
                                                @if ($field->is_required)
                                                    <span class="text-red-500 font-extrabold mr-0.5">*</span>
                                                @else
                                                    <span
                                                        class="text-xs text-gray-400 font-normal mr-1">(اختیاری)</span>
                                                @endif
                                            </label>

                                            @if ($fieldType === 'textarea')
                                                <textarea
                                                    wire:model="kycForm.{{ $fieldName }}"
                                                    rows="3"
                                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all placeholder:text-gray-400"
                                                    placeholder="ورود {{ $field->label }}"
                                                ></textarea>
                                            @elseif ($fieldType === 'select')
                                                @php
                                                    $meta = is_array($field->meta) ? $field->meta : (json_decode($field->meta ?? '[]', true) ?: []);
                                                    $options = $meta['options'] ?? [];
                                                @endphp
                                                <select
                                                    wire:model="kycForm.{{ $fieldName }}"
                                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                                >
                                                    <option value="">انتخاب کنید...</option>
                                                    @foreach ($options as $optVal => $optLabel)
                                                        <option value="{{ $optVal }}">{{ $optLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input
                                                    type="{{ in_array($fieldType, ['number', 'date', 'email', 'tel']) ? $fieldType : 'text' }}"
                                                    wire:model="kycForm.{{ $fieldName }}"
                                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all placeholder:text-gray-400"
                                                    placeholder="ورود {{ $field->label }}"
                                                />
                                            @endif

                                            @error($fieldErrorKey)
                                            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Modal Footer --}}
                        <div
                            class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                            <button
                                type="button"
                                wire:click="closeKycModal"
                                class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                            >
                                انصراف
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
                            >
                                <span>ثبت و تایید احراز هویت</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showDomainModal)
        @php
            $displayHost = request()->getHost() ?: 'crm-laravel.test';
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
             x-data="{
                 slug: @entangle('subdomainInput').live,
                 get cleanSlug() {
                     return (this.slug || '').toString().toLowerCase().trim();
                 }
             }">
            <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity"
                 wire:click="closeDomainModal"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700/80">

                    {{-- Modal Header --}}
                    <div
                        class="p-6 bg-gradient-to-l from-indigo-50/90 via-white to-purple-50/30 dark:from-indigo-950/50 dark:via-gray-800 dark:to-purple-950/20 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <span
                                class="flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/25 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">
                                    تنظیم دامین دلخواه سامانه
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    تعیین نشانی اختصاصی سامانه یا اتصال دامنه مستقل (Custom Domain)
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeDomainModal"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body Form --}}
                    <form wire:submit="saveDomain">
                        <div class="p-6 space-y-5">
                            @if (!empty($allowCustomDomains))
                                {{-- Domain Mode Toggle Tabs --}}
                                <div
                                    class="flex p-1 bg-gray-100 dark:bg-gray-900/60 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                                    <button
                                        type="button"
                                        wire:click="$set('domainType', 'subdomain')"
                                        class="flex-1 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer {{ $domainType === 'subdomain' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                    >
                                        نشانی اختصاصی ابری (پیش‌فرض)
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="$set('domainType', 'custom_domain')"
                                        class="flex-1 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer {{ $domainType === 'custom_domain' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                    >
                                        دامنه اختصاصی (Custom Domain)
                                    </button>
                                </div>
                            @endif

                            @if (empty($allowCustomDomains) || $domainType === 'subdomain')
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                                نام و شناسه اختصاصی سامانه:
                                            </label>
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">
                                                فقط حروف انگلیسی، عدد و خط تیره (-)
                                            </span>
                                        </div>

                                        {{-- Input with Host Prefix (crm-laravel.test/ or host/) and empty input --}}
                                        <div
                                            class="flex items-center rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/50 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-gray-900 overflow-hidden shadow-xs transition-all"
                                            dir="ltr">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3.5 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-mono font-bold border-r border-gray-200 dark:border-gray-700 select-none shrink-0">
                                                <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                </svg>
                                                {{ $displayHost }}/
                                            </span>
                                            <input
                                                type="text"
                                                x-model="slug"
                                                placeholder="my-company"
                                                class="flex-1 px-3.5 py-3 bg-transparent text-gray-900 dark:text-white text-xs outline-none font-mono font-medium text-left placeholder:text-gray-400"
                                                autofocus
                                            />
                                        </div>

                                        @error('subdomainInput')
                                        <div class="flex items-center gap-1.5 text-xs text-red-500 font-semibold mt-1">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span>{{ $message }}</span>
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- Live Preview Section (بخش زنده پیش‌نمایش آدرس سامانه) --}}
                                    <div class="rounded-2xl p-4 bg-gradient-to-br from-indigo-50/70 via-white to-purple-50/40 dark:from-indigo-950/30 dark:via-gray-800/80 dark:to-purple-950/20 border border-indigo-100 dark:border-indigo-900/50 shadow-xs space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="relative flex h-2.5 w-2.5">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                                                          x-show="cleanSlug"></span>
                                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                                          :class="cleanSlug ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'"></span>
                                                </span>
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                    پیش‌نمایش زنده نشانی سامانه شما
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-all"
                                                  :class="cleanSlug ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'">
                                                <span x-show="cleanSlug">نشانی معتبر</span>
                                                <span x-show="!cleanSlug">در انتظار ورود نام</span>
                                            </span>
                                        </div>

                                        {{-- Address Bar Look --}}
                                        <div class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-mono shadow-inner overflow-x-auto" dir="ltr">
                                            <svg class="w-3.5 h-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span class="text-gray-400 dark:text-gray-500 text-[11px] select-none">https://</span>
                                            <span class="text-gray-700 dark:text-gray-200 font-semibold select-none">{{ $displayHost }}/</span>
                                            <span class="font-black text-indigo-600 dark:text-indigo-400 tracking-wide"
                                                  x-text="cleanSlug || 'my-company'"></span>
                                            <span class="text-gray-400 dark:text-gray-600 text-[11px] select-none">/user/dashboard</span>
                                        </div>

                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed text-right">
                                            این نشانی آدرس مستقیم سامانه شماست که مدیران و کارکنان با مراجعه به آن وارد پنل کاری می‌شوند.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                            آدرس دامنه مستقل شما (بدون http/https):
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="customDomainInput"
                                            placeholder="crm.yourbrand.ir"
                                            dir="ltr"
                                            class="w-full px-3.5 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                        />
                                        @error('customDomainInput')
                                        <div class="flex items-center gap-1.5 text-xs text-red-500 font-semibold mt-1">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span>{{ $message }}</span>
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- CNAME Guide Notice Box --}}
                                    <div
                                        class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-right space-y-2">
                                        <div
                                            class="flex items-center gap-2 text-amber-800 dark:text-amber-300 text-xs font-bold">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>راهنمای اتصال DNS دامنه اختصاصی</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                            برای فعال‌سازی دامنه، در پنل مدیریت DNS دامنه خود یک رکورد از نوع <span
                                                class="font-bold text-amber-700 dark:text-amber-400">CNAME</span> به سمت
                                            مقدار زیر تنظیم کنید:
                                        </p>
                                        <div
                                            class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-900 rounded-xl border border-amber-200 dark:border-amber-900 font-mono text-xs text-gray-800 dark:text-gray-200"
                                            dir="ltr">
                                            <span>{{ $cnameTarget }}</span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">CNAME</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Modal Footer --}}
                        <div
                            class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                            <button
                                type="button"
                                wire:click="closeDomainModal"
                                class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                            >
                                انصراف
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
                            >
                                <span>ذخیره و تایید دامین</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showBrandingModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                 wire:click="closeBrandingModal"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700">

                    {{-- Modal Header --}}
                    <div
                        class="p-6 bg-gradient-to-l from-indigo-50/80 via-white to-indigo-50/20 dark:from-indigo-950/40 dark:via-gray-800 dark:to-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <span
                                class="flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/25 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">
                                    شخصی‌سازی برند و وایت‌لیبل (White-Label)
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    هویت بصری، عنوان و مشخصات ارتباطی برند اختصاصی خود را تنظیم کنید.
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeBrandingModal"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form wire:submit="saveBranding">
                        <div class="p-6 space-y-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    عنوان برند / نام نمایشی سامانه <span class="text-red-500 font-extrabold">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="brandTitle"
                                    placeholder="مثال: کلینیک تخصصی دکتر رحیمی"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                />
                                @error('brandTitle')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    رنگ سازمانی و تم اصلی  <span class="text-red-500 font-extrabold">*</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input
                                        type="color"
                                        wire:model.live="brandPrimaryColor"
                                        class="w-12 h-10 p-1 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800"
                                    />
                                    <input
                                        type="text"
                                        wire:model="brandPrimaryColor"
                                        dir="ltr"
                                        class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                    />
                                    <div class="w-8 h-8 rounded-xl shrink-0 shadow-inner"
                                         style="background-color: {{ $brandPrimaryColor }}"></div>
                                </div>
                                @error('brandPrimaryColor')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Ready-made / default brand color presets --}}
                            <div class="space-y-1.5 pt-1">
                                <span class="block text-xs font-bold text-gray-400">
                                    پالت رنگ‌های آماده و پیش‌فرض
                                </span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach($colorPresets as $preset)
                                        <button
                                            type="button"
                                            wire:key="brand-color-preset-{{ $loop->index }}"
                                            wire:click="selectBrandPrimaryColor('{{ $preset['hex'] }}')"
                                            title="{{ $preset['name'] }}"
                                            class="flex items-center gap-1.5 p-2 rounded-xl border transition-all cursor-pointer text-start {{ strtolower((string) $brandPrimaryColor) === strtolower($preset['hex']) ? 'border-indigo-500 ring-2 ring-indigo-500/25 bg-indigo-50/60 dark:bg-indigo-950/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}"
                                        >
                                            <span class="w-4 h-4 rounded-full shrink-0 shadow-2xs"
                                                  style="background-color: {{ $preset['hex'] }}"></span>
                                            <span
                                                class="text-xs font-bold text-gray-600 dark:text-gray-300 truncate">
                                                {{ $preset['name'] }}
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        شماره تلفن پشتیبانی
                                    </label>
                                    <input
                                        type="tel"
                                        wire:model="supportPhone"
                                        placeholder="۰۲۱-۸۸۸۸۸۸۸۸"
                                        dir="ltr"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs text-left focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                    />
                                    <p class="text-xs text-gray-400 leading-relaxed">
                                        ورود ارقام فارسی یا انگلیسی مجاز است؛ شماره به صورت فارسی نمایش داده می‌شود.
                                    </p>
                                    @error('supportPhone')
                                    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        ایمیل پشتیبانی
                                    </label>
                                    <input
                                        type="email"
                                        wire:model="supportEmail"
                                        placeholder="support@brand.ir"
                                        dir="ltr"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-mono"
                                    />
                                    @error('supportEmail')
                                    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    متن کپی‌رایت فوتر
                                </label>
                                <input
                                    type="text"
                                    wire:model="brandFooterText"
                                    placeholder="تمامی حقوق محفوظ است."
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                />
                                @error('brandFooterText')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div
                            class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                            <button
                                type="button"
                                wire:click="closeBrandingModal"
                                class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                            >
                                انصراف
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
                            >
                                <span>ذخیره وایت‌لیبل</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showPackageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                 wire:click="closePackageModal"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100 dark:border-gray-700">
                    <div
                        class="p-6 bg-gradient-to-l from-emerald-50/80 via-white to-emerald-50/20 dark:from-emerald-950/40 dark:via-gray-800 dark:to-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <span
                                class="flex items-center justify-center w-11 h-11 rounded-2xl bg-emerald-600 text-white shadow-md shadow-emerald-600/25 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">
                                    انتخاب و ارتقای پکیج سامانه CRM
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    پلن متناسب با نیاز خود را برگزینید. بلافاصله پس از فعال‌سازی، منوهای سایدبار در
                                    دسترس خواهند بود.
                                </p>
                            </div>
                        </div>
                        <button wire:click="closePackageModal"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-6">
                        @if ($errors->has('package'))
                            <div
                                class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold rounded-xl">
                                {{ $errors->first('package') }}
                            </div>
                        @endif
                        <div class="flex justify-center overflow-x-auto max-w-full pb-1">
                            <div
                                class="inline-flex p-1 bg-gray-100 dark:bg-gray-900/60 rounded-2xl border border-gray-200/80 dark:border-gray-700 gap-1 overflow-x-auto max-w-full hide-scrollbar">
                                @foreach($availablePeriods ?? [] as $pKey => $pData)
                                    <button
                                        type="button"
                                        wire:click="$set('selectedPeriod', '{{ $pKey }}')"
                                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap shrink-0 {{ $selectedPeriod === $pKey ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                    >
                                        {{ $pData['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @forelse ($packages as $pkg)
                                @php
                                    $isCurrentPkg = ($tenant->package_id === $pkg->id);
                                    $months = ResellerPackage::resolveMonthFromPeriodKey($selectedPeriod ?? 'yearly_price');
                                    $rawPrice = $pkg->isFree() ? 0 : $pkg->getPriceForMonths($months);
                                    $price = $pkg->isFree() ? 0 : $pkg->getFinalPriceForMonths($months);
                                    $hasDiscount = !$pkg->isFree() && ($pkg->isSubscription() ? $pkg->hasActiveDiscountForMonths($months) : $pkg->hasActiveDiscount()) && $price < $rawPrice;
                                    $modules = is_array($pkg->modules) ? $pkg->modules : (json_decode($pkg->modules ?? '[]', true) ?: []);
                                @endphp
                                <div wire:key="package-card-{{ $pkg->id }}" class="relative rounded-2xl p-5 border transition-all flex flex-col justify-between
                                    @if ($isCurrentPkg)
                                        bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-700 ring-2 ring-emerald-500/20
                                    @else
                                        bg-white dark:bg-gray-800/90 border-gray-200 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-800
                                    @endif
                                ">
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <h4 class="text-base font-black text-gray-900 dark:text-white">{{ $pkg->name }}</h4>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @if ($isCurrentPkg)
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                            پلن فعال شما
                                                        </span>
                                                @elseif ($pkg->isFree())
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                                            رایگان
                                                        </span>
                                                @endif
                                                @if ($hasDiscount)
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                                            @if($pkg->discount_type === 'percentage')
                                                            {{ reseller_fa_num($pkg->discount_value) }}٪ تخفیف
                                                        @else
                                                            {{ reseller_price($pkg->discount_value, 'تومان') }}
                                                            تخفیف
                                                        @endif
                                                        </span>
                                                @endif
                                            </div>
                                        </div>

                                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {{ $pkg->description ?: 'دسترسی کامل به ماژول‌ها و ظرفیت مشخص' }}
                                        </p>

                                        {{-- Price Display --}}
                                        <div class="py-3 border-y border-gray-100 dark:border-gray-700/60">
                                            @if ($pkg->isFree())
                                                <span
                                                    class="text-xl font-black text-emerald-600 dark:text-emerald-400">رایگان</span>
                                                <span class="text-xs text-gray-500 mr-1">/ بدون هزینه</span>
                                            @else
                                                @if ($hasDiscount)
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span
                                                            class="text-xs text-gray-400 line-through tabular-nums">{{ reseller_price($rawPrice, 'تومان') }}</span>
                                                        <span
                                                            class="text-xs font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded-md tabular-nums">
                                                                {{ reseller_price($rawPrice - $price, '') }}-
                                                            </span>
                                                    </div>
                                                @endif
                                                <div class="flex items-baseline gap-1">
                                                        <span
                                                            class="text-2xl font-black {{ $hasDiscount ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ reseller_fa_num(number_format($price)) }}</span>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400">تومان</span>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between text-xs text-gray-400 mt-0.5">
                                                    <span>برای مدت {{ reseller_duration_label($months) }}</span>
                                                    @if ($hasDiscount && $pkg->discount_expires_at)
                                                        <span
                                                            class="text-amber-600 dark:text-amber-400 font-bold text-xs">تا {{ $pkg->discount_expires_at_jalali }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Module features pill list --}}
                                        <div class="space-y-1.5 pt-1">
                                            <span
                                                class="text-xs font-bold text-gray-400 block">ماژول‌های فعال:</span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @forelse (array_slice($modules, 0, 5) as $modKey)
                                                    @php
                                                        $mSlug = is_array($modKey) ? ($modKey['slug'] ?? $modKey['name'] ?? '') : (string)$modKey;
                                                        $mTitle = \Modules\ResellerHub\App\Helpers\ResellerHelper::getModuleTitle($mSlug);
                                                    @endphp
                                                    <span
                                                        class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
                                                            {{ $mTitle }}
                                                        </span>
                                                @empty
                                                    <span class="text-xs text-gray-400">تمام امکانات پایه</span>
                                                @endforelse
                                                @if(count($modules) > 5)
                                                    <span
                                                        class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-bold">
                                                            +{{ reseller_fa_num(count($modules) - 5) }} ماژول دیگر
                                                        </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-5 mt-4 border-t border-gray-100 dark:border-gray-700/60">
                                        <button
                                            type="button"
                                            wire:click="selectPackage({{ $pkg->id }})"
                                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold transition-all cursor-pointer
                                                @if ($isCurrentPkg)
                                                    bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600
                                                @elseif ($pkg->isFree())
                                                    bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/25 active:scale-95
                                                @else
                                                    bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 active:scale-95
                                                @endif
                                            "
                                        >
                                            <span>{{ $isCurrentPkg ? 'انتخاب مجدد' : ($pkg->isFree() ? 'فعال‌سازی پلن رایگان' : 'انتخاب و پرداخت آنلاین') }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M15 19l-7-7 7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 text-center py-8 text-gray-500">
                                    پکیجی تعریف نشده است.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div
                        class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <button
                            type="button"
                            wire:click="closePackageModal"
                            class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                        >
                            بستن
                        </button>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            پرداخت از طریق درگاه مستقیم بانکی و بلافاصله پس از تسویه فعال‌سازی انجام می‌پذیرد.
                        </span>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @includeIf('resellerhub::partials.invoice-payment-modal')
    @includeIf('resellerhub::partials.renewal-limit-modal')
</div>
