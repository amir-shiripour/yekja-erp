<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <noscript>
        <meta http-equiv="refresh" content="3;url={{ $redirectUrl }}">
    </noscript>
    <title>خوش برگشتید - {{ $brandTitle }}</title>

    @if(function_exists('is_panel_indexing_blocked') && is_panel_indexing_blocked())
        <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    @endif

    <script>
        (() => {
            const pref = localStorage.getItem('theme') || 'system';
            const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = pref === 'dark' || (pref === 'system' && sysDark);
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', 'Vazirmatn', system-ui, -apple-system, sans-serif;
            font-size: 14px;
        }

        @keyframes pulse-subtle {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.85;
            }
        }

        .animate-subtle {
            animation: pulse-subtle 2s infinite ease-in-out;
        }

        .smooth-bar {
            transition: width 3s linear;
        }
    </style>
</head>
<body
    class="bg-gradient-to-br from-slate-50 via-gray-100 to-indigo-50/30 dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950/20 text-gray-800 dark:text-gray-100 min-h-screen flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white">

<div class="relative w-full max-w-md">
    {{-- Decorative Ambient Glows --}}
    <div
        class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div
        class="absolute -bottom-12 -left-12 w-48 h-48 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Main Glass Card --}}
    <div
        class="relative bg-white/95 dark:bg-gray-850/95 backdrop-blur-xl rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-2xl shadow-indigo-950/5 dark:shadow-black/40 p-6 sm:p-8 space-y-6">

        {{-- Top Brand & Status Bar --}}
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center gap-2.5">
                @if(!empty($brandLogo))
                    <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandTitle }}"
                         class="h-8 w-auto max-w-[130px] object-contain">
                @else
                    <div
                        class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-sm shadow-indigo-600/30">
                        {{ mb_substr($brandTitle, 0, 1) }}
                    </div>
                    <span
                        class="text-xs font-black text-gray-700 dark:text-gray-300 truncate max-w-[150px]">{{ $brandTitle }}</span>
                @endif
            </div>

            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>ورود با شماره همراه</span>
            </span>
        </div>

        {{-- Centered Welcome Header --}}
        <div class="text-center space-y-3 pt-1">
            {{-- User Avatar / Welcome Icon with Glow --}}
            <div
                class="relative mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 animate-subtle">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>

            <div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    خوش برگشتید{{ $userName ? '، ' . $userName : '' }}
                </h1>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-sm mx-auto">
                    نشست کاربری شما تأیید و با موفقیت برقرار شد. در حال هدایت خودکار به میزکار اختصاصی شما...
                </p>
            </div>
        </div>

        {{-- Verification & Mobile Badge Info Card --}}
        <div
            class="bg-gray-50/80 dark:bg-gray-900/50 rounded-2xl p-3.5 border border-gray-100 dark:border-gray-800 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400 font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>شماره موبایل تأییدشده:</span>
                </span>
                <span class="font-bold text-gray-900 dark:text-white dir-ltr text-[13px]">
                    {{ $userMobile ?? 'شماره ثبت‌شده' }}
                </span>
            </div>

            <div
                class="flex items-center justify-between text-xs pt-2 border-t border-gray-200/50 dark:border-gray-700/50">
                <span class="text-gray-400 dark:text-gray-500">شیوه احراز هویت:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>رمز یکبار مصرف پیامکی (OTP)</span>
                </span>
            </div>
        </div>

        {{-- Countdown Timer & Animated Smooth Progress Bar --}}
        <div class="space-y-2 pt-1">
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>انتقال خودکار به داشبورد:</span>
                </span>
                <span class="font-black text-gray-900 dark:text-white">
                    <span id="countdown-sec" class="tabular-nums text-indigo-600 dark:text-indigo-400 text-sm">۳</span> ثانیه
                </span>
            </div>

            <div
                class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 overflow-hidden p-0.5 border border-gray-200/50 dark:border-gray-700/50">
                <div id="progress-bar-el"
                     class="smooth-bar h-full bg-gradient-to-l from-indigo-500 to-indigo-600 rounded-full shadow-sm"
                     style="width: 100%;"></div>
            </div>
        </div>

        {{-- Immediate CTA Button --}}
        <div class="space-y-2">
            <button id="btn-dashboard"
                    type="button"
                    class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-indigo-600/20 transition-all cursor-pointer group active:scale-[0.98]">
                <span>ورود مستقیم به داشبورد</span>
                <svg class="w-4 h-4 rtl:rotate-180 transition-transform duration-200 group-hover:translate-x-0.5"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </button>
        </div>

        {{-- Footer --}}
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 text-center">
            <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                میزکار اختصاصی  سامانه یکپارچه ابری CRM
            </span>
        </div>
    </div>
</div>

<script>
    (function () {
        var targetUrl = @json($redirectUrl);
        var totalSec = 3;
        var remainingSec = totalSec;
        var numEl = document.getElementById('countdown-sec');
        var barEl = document.getElementById('progress-bar-el');
        var btn = document.getElementById('btn-dashboard');
        var redirected = false;
        var faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        function toFa(n) {
            return (n + '').replace(/\d/g, function (x) {
                return faDigits[x];
            });
        }

        function doRedirect() {
            if (redirected) return;
            redirected = true;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>در حال انتقال...</span>';
            }
            if (targetUrl) {
                window.location.replace(targetUrl);
            }
        }

        // Trigger smooth linear bar animation right after initial paint
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (barEl) {
                    barEl.style.width = '0%';
                }
            });
        });

        // 1-second interval to update remaining seconds label
        var timer = setInterval(function () {
            remainingSec--;
            if (numEl) {
                numEl.textContent = toFa(Math.max(0, remainingSec));
            }

            if (remainingSec <= 0) {
                clearInterval(timer);
                doRedirect();
            }
        }, 1000);

        // Immediate click handler
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                clearInterval(timer);
                doRedirect();
            });
        }
    })();
</script>
</body>
</html>
