<!DOCTYPE html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="10;url={{ $redirectUrl }}">
    <title>سامانه شما آماده شد - {{ $brandTitle }}</title>

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
            font-family: 'IRANYekanX', system-ui, -apple-system, sans-serif;
            font-size: 14px;
        }

        .progress-bar-fill {
            transition: width 1s linear;
        }
    </style>
</head>
<body
    class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen flex items-center justify-center p-4">

<div
    class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">

    {{-- لوگو یا نام برند --}}
    <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-100 dark:border-gray-700/60">
        @if($brandLogo)
            <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandTitle }}"
                 class="h-7 w-auto max-w-[140px] object-contain">
        @else
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $brandTitle }}</span>
        @endif

        <span
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>آماده‌سازی کامل</span>
            </span>
    </div>
    <div class="text-center mb-6">
        <div
            class="mx-auto w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/80 flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
        </div>

        <h1 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">
            سامانه شما با موفقیت آماده شد{{ $userName ? '، ' . $userName : '' }}
        </h1>
        <p class="mt-1.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
            پایگاه داده اختصاصی، ماژول‌ها و میزکار شما پیکربندی و فعال شدند.
        </p>
    </div>
        <div
            class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-3.5 border border-gray-100 dark:border-gray-700/60 space-y-2.5 mb-6 text-xs">
            <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    <span>تخصیص زیردامنه و فضای ابری</span>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">فعال</span>
            </div>

            <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    <span>پیکربندی پایگاه داده و کاربر مدیر</span>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">انجام شد</span>
            </div>

            <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    <span>فعال‌سازی ماژول‌ها و لایسنس پکیج</span>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">آماده</span>
            </div>
        </div>
        <div class="space-y-2 mb-5">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>انتقال به میزکار</span>
                <span class="font-medium digits-fa">
                    <span id="countdown-sec" class="font-bold text-gray-900 dark:text-white">۱۰</span> ثانیه
                </span>
            </div>

            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div id="progress-bar-el"
                     class="progress-bar-fill h-full bg-emerald-600 dark:bg-emerald-500 rounded-full"
                     style="width: 100%;"></div>
            </div>
        </div>
        <a id="btn-dashboard"
           href="{{ $redirectUrl }}"
           class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-medium text-sm transition-colors cursor-pointer">
            <span>ورود مستقیم به داشبورد سامانه</span>
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
        </a>
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700/60 text-center">
            <span class="text-xs text-gray-400 dark:text-gray-500">
                اتصال امن برقرار شد • سامانه ابری CRM
            </span>
        </div>
    </div>

    <script>
        (function () {
            var targetUrl = @json($redirectUrl);
            var total = 10;
            var remaining = total;
            var numEl = document.getElementById('countdown-sec');
            var barEl = document.getElementById('progress-bar-el');
            var redirected = false;
            var farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

            function toPersianDigits(n) {
                return (n + '').replace(/\d/g, function (x) {
                    return farsiDigits[x];
                });
            }

            function doRedirect() {
                if (redirected) return;
                redirected = true;
                if (targetUrl) {
                    window.location.replace(targetUrl);
                }
            }

            var timer = setInterval(function () {
                remaining--;
                if (numEl) {
                    numEl.textContent = toPersianDigits(Math.max(0, remaining));
                }
                if (barEl) {
                    var percent = Math.max(0, (remaining / total) * 100);
                    barEl.style.width = percent + '%';
                }

                if (remaining <= 0) {
                    clearInterval(timer);
                    doRedirect();
                }
            }, 1000);

            var btn = document.getElementById('btn-dashboard');
            if (btn) {
                btn.addEventListener('click', function (e) {
                    doRedirect();
                });
            }
        })();
    </script>
</body>
</html>
