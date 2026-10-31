<div class="max-w-2xl mx-auto mb-8 sm:mb-10 text-center">
    <div
        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-50/90 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 text-xs font-bold shadow-xs mb-3 transition-all duration-300">
        <span
            class="flex h-2 w-2 rounded-full {{ $step === 2 ? 'bg-emerald-500 animate-pulse' : 'bg-indigo-600 animate-pulse' }}"></span>
        <span class="text-gray-700 dark:text-gray-300">تور راه‌اندازی سریع سامانه</span>
        <span class="text-gray-300 dark:text-gray-700">|</span>
        <span
            class="{{ $step === 2 ? 'text-emerald-600 dark:text-emerald-400 font-black' : 'text-indigo-600 dark:text-indigo-400 font-black' }}">
            {{ $step === 1 ? 'مرحله ۱ از ۲ (۵۰٪ تکمیل شده)' : 'مرحله ۲ از ۲ (۱۰۰٪ - گام نهایی)' }}
        </span>
    </div>

    {{-- Main Title & Subtitle --}}
    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
        {{ __('resellerhub::messages.portal.checkout.title') }}
    </h2>
    <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-lg mx-auto leading-relaxed">
        {{ __('resellerhub::messages.portal.checkout.subtitle') }}
    </p>

    {{-- Tour Stepper Card --}}
    <div
        class="mt-6 sm:mt-7 relative bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-gray-200/80 dark:border-gray-800/80 rounded-3xl p-5 sm:p-6 shadow-xl shadow-gray-200/50 dark:shadow-none select-none">

        {{-- Progress Bar Header Strip --}}
        <div
            class="flex items-center justify-between text-xs font-bold mb-6 pb-3.5 border-b border-gray-100 dark:border-gray-800/80">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $step === 2 ? 'bg-emerald-500' : 'bg-indigo-600' }}"></span>
                <span class="text-gray-600 dark:text-gray-300">پیشرفت راه‌اندازی:</span>
                <span
                    class="font-black {{ $step === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-400' }}">
                    {{ $step === 1 ? '۵۰٪' : '۱۰۰٪' }}
                </span>
            </div>
            <div class="text-[11px] text-gray-400 dark:text-gray-500">
                @if($step === 1)
                    <span>گام بعد: فاکتور و درگاه پرداخت</span>
                @else
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">آماده پرداخت و تحویل سامانه</span>
                @endif
            </div>
        </div>

        {{-- Connecting Bridge & Steps --}}
        <div class="relative flex items-start justify-between max-w-lg mx-auto">

            {{-- Connecting Progress Track Line Behind Circles --}}
            <div
                class="absolute top-6 start-[20%] end-[20%] sm:start-[22%] sm:end-[22%] h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden z-0">
                <div
                    class="h-full rounded-full bg-gradient-to-l from-indigo-500 to-emerald-500 transition-all duration-700 ease-out"
                    style="width: {{ $step === 1 ? '0%' : '100%' }};"></div>
            </div>

            {{-- Step 1: پکیج و دامنه --}}
            <div
                @if($step === 2) wire:click="$set('step', 1)" title="کلیک برای بازگشت و ویرایش پکیج و دامنه" @endif
            class="flex-1 flex flex-col items-center relative z-10 text-center group {{ $step === 2 ? 'cursor-pointer' : '' }}">

                {{-- Step 1 Circle / Badge --}}
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm
                    {{ $step === 1
                        ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/25 shadow-lg shadow-indigo-600/30 scale-105'
                        : 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25 group-hover:scale-110 group-hover:bg-emerald-600' }}">
                    @if($step === 2)
                        {{-- Icon Tiki (Checkmark) --}}
                        <svg class="w-6 h-6 text-white transform transition-transform group-hover:scale-110" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <span>۱</span>
                    @endif
                </div>

                {{-- Step 1 Labels --}}
                <div class="mt-2.5 space-y-0.5">
                    <span class="text-xs sm:text-sm font-black transition-colors block
                        {{ $step === 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400 group-hover:underline' }}">
                        {{ __('resellerhub::messages.portal.checkout.step1_title') }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 hidden sm:block">
                        {{ $step === 2 ? 'تکمیل شده (ویرایش)' : 'انتخاب پلن و آدرس' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold mt-1
                        {{ $step === 1
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60'
                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60' }}">
                        {{ $step === 1 ? 'گام فعلی' : 'تکمیل شد ✓' }}
                    </span>
                </div>
            </div>

            {{-- Step 2: فاکتور و پرداخت --}}
            <div class="flex-1 flex flex-col items-center relative z-10 text-center">

                {{-- Step 2 Circle / Badge --}}
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-sm transition-all duration-300 shadow-sm
                    {{ $step === 2
                        ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/25 shadow-lg shadow-indigo-600/30 scale-105'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700' }}">
                    @if($step === 2)
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @else
                        <span>۲</span>
                    @endif
                </div>

                {{-- Step 2 Labels --}}
                <div class="mt-2.5 space-y-0.5">
                    <span class="text-xs sm:text-sm font-black transition-colors block
                        {{ $step === 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}">
                        {{ __('resellerhub::messages.portal.checkout.step2_title') }}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 hidden sm:block">
                        {{ $step === 2 ? 'بررسی و پرداخت آنلاین' : 'صدور پیش‌فاکتور' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold mt-1
                        {{ $step === 2
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60'
                            : 'bg-gray-100 text-gray-500 dark:bg-gray-800/80 dark:text-gray-400 border border-gray-200/60 dark:border-gray-700/60' }}">
                        {{ $step === 2 ? 'گام نهایی' : 'گام بعدی' }}
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>
