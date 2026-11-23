@php
    use App\Models\User;
    use App\Tenancy\TenantManager;
    use Illuminate\Support\Facades\View;

    /** @var User|null $authUser */
    $authUser = auth()->user();
    $currentTenant = $currentTenant ?? app(TenantManager::class)->getTenant() ?? $authUser?->tenant;

    $customTitle = $currentTenant?->getBranding('brand_title');
    $brandTitle = $customTitle ?: ($currentTenant?->name ?? config('app.name', 'Laravel'));

    $brandLogo = $currentTenant?->getBranding('brand_logo');
    $brandFavicon = $currentTenant?->getBranding('brand_favicon');
    $brandPrimaryColor = $currentTenant?->getBranding('brand_primary_color');
    $brandFooterText = $currentTenant?->getBranding('brand_footer_text');
    $supportPhone = $currentTenant?->getBranding('support_phone');
    $supportEmail = $currentTenant?->getBranding('support_email');

    $hexToRgb = static function(?string $hex): string {
        if (empty($hex)) {
            return '79, 70, 229';
        }
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } elseif (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            return '79, 70, 229';
        }
        return "$r, $g, $b";
    };
@endphp

    <!doctype html>
<html lang="fa" dir="rtl" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>@yield('title', $brandTitle) - {{ $brandTitle }}</title>

    @if($brandFavicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $brandFavicon) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $brandFavicon) }}">
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
    @livewireStyles
    <!--suppress CssUnknownElement -->
    <style>
        :root {
            --brand-primary: #4f46e5;
            --brand-primary-rgb: 79, 70, 229;
        }

        body {
            font-size: 14px;
        }

        /* noinspection CssUnknownElement */
        :is(jdp-container), jdp-container {
            z-index: 9999 !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @if(!empty($brandPrimaryColor))
        <!--suppress CssUnresolvedCustomProperty, CssUnusedSymbol -->
        <style>
            /* noinspection ALL */
            :root {
                --brand-primary: {{ $brandPrimaryColor }};
                --brand-primary-rgb: {{ $hexToRgb($brandPrimaryColor) }};
            }

            /* noinspection CssUnusedSymbol */
            .bg-indigo-600, .bg-indigo-500, button.bg-indigo-600, a.bg-indigo-600 {
                background-color: var(--brand-primary, #4f46e5) !important;
            }

            /* noinspection CssUnusedSymbol */
            .hover\:bg-indigo-700:hover, button.bg-indigo-600:hover, a.bg-indigo-600:hover {
                filter: brightness(0.9) !important;
            }

            /* noinspection CssUnusedSymbol */
            .text-indigo-600, .text-indigo-500, .text-indigo-700 {
                color: var(--brand-primary, #4f46e5) !important;
            }

            /* noinspection CssUnusedSymbol */
            .border-indigo-500, .border-indigo-600 {
                border-color: var(--brand-primary, #4f46e5) !important;
            }

            /* noinspection CssUnusedSymbol */
            .focus\:ring-indigo-500:focus, .ring-indigo-500:focus {
                --tw-ring-color: var(--brand-primary, #4f46e5) !important;
            }

            /* noinspection CssUnusedSymbol */
            .focus\:border-indigo-500:focus {
                border-color: var(--brand-primary, #4f46e5) !important;
            }

            /* noinspection CssUnusedSymbol */
            .bg-indigo-50, .dark .bg-indigo-950\/60, .dark .bg-indigo-900\/60 {
                background-color: rgba(var(--brand-primary-rgb, 79, 70, 229), 0.1) !important;
            }

            /* noinspection CssUnusedSymbol */
            .border-indigo-100, .border-indigo-200, .dark .border-indigo-800\/60 {
                border-color: rgba(var(--brand-primary-rgb, 79, 70, 229), 0.25) !important;
            }
        </style>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(function_exists('is_panel_indexing_blocked') && is_panel_indexing_blocked())
        <!-- spell-checker: disable -->
        <!--suppress SpellCheckingInspection -->
        <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
        <!--suppress SpellCheckingInspection -->
        <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
        <!-- spell-checker: enable -->
    @endif
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
<div
    x-data="{
        items: [],
        notify(e) {
            const id = Date.now() + Math.random();
            const detail = (e && e.detail !== undefined) ? e.detail : e;
            const validTypes = ['success', 'error', 'warning', 'info', 'danger'];
            let type;
            let text;
            let duration = 5000;

            if (typeof detail === 'string') {
                text = detail;
            } else if (typeof detail === 'object' && detail !== null) {
                const rawType = detail.type ? String(detail.type).toLowerCase() : '';
                const rawText = detail.text || detail.message || detail.title || '';
                duration = detail.duration || 5000;

                if (validTypes.includes(rawType)) {
                    type = rawType === 'danger' ? 'error' : rawType;
                    text = rawText;
                } else if (validTypes.includes(String(rawText).toLowerCase())) {
                    const norm = String(rawText).toLowerCase();
                    type = norm === 'danger' ? 'error' : norm;
                    text = detail.type || '';
                } else {
                    type = 'info';
                    text = rawText || detail.type || '';
                }
            }

            if (!text) {
                text = typeof detail === 'string' ? detail : (detail?.message || detail?.text || '');
            }

            this.items.push({ id, type, text });

            setTimeout(() => {
                this.remove(id);
            }, duration);
        },
        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
        }
    }"
    @php
        $initialFlashMessages = [];
        if (session()->has('success')) {
            $initialFlashMessages[] = ['type' => 'success', 'text' => (string) session('success')];
        }
        if (session()->has('error')) {
            $initialFlashMessages[] = ['type' => 'error', 'text' => (string) session('error')];
        }
        if (session()->has('warning')) {
            $initialFlashMessages[] = ['type' => 'warning', 'text' => (string) session('warning')];
        }
        if (session()->has('info')) {
            $initialFlashMessages[] = ['type' => 'info', 'text' => (string) session('info')];
        }
        if (isset($errors) && method_exists($errors, 'any') && $errors->any()) {
            foreach ($errors->all() as $error) {
                $initialFlashMessages[] = ['type' => 'error', 'text' => (string) $error];
            }
        }
    @endphp
    x-on:notify.window="notify($event)"
    x-init="
        const initialMsgs = @json($initialFlashMessages, JSON_THROW_ON_ERROR);
        if (Array.isArray(initialMsgs)) {
            initialMsgs.forEach(m => notify(m));
        }
    "
    class="fixed top-5 right-4 left-4 sm:left-auto sm:right-6 z-[99999] sm:w-96 max-w-[calc(100vw-2rem)] sm:max-w-md space-y-3 pointer-events-none font-sans"
    style="z-index: 99999;"
    dir="rtl"
>
    <template x-for="item in items" :key="item.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-y-4 opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="translate-x-4 opacity-0 scale-95"
            class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl shadow-xl border backdrop-blur-md transition-all font-sans"
            :class="{
                'bg-emerald-50/95 dark:bg-slate-800/95 border-emerald-200 dark:border-emerald-500/40 text-emerald-950 dark:text-emerald-100 shadow-emerald-500/10': item.type === 'success',
                'bg-rose-50/95 dark:bg-slate-800/95 border-rose-200 dark:border-rose-500/40 text-rose-950 dark:text-rose-100 shadow-rose-500/10': item.type === 'error',
                'bg-amber-50/95 dark:bg-slate-800/95 border-amber-200 dark:border-amber-500/40 text-amber-950 dark:text-amber-100 shadow-amber-500/10': item.type === 'warning',
                'bg-indigo-50/95 dark:bg-slate-800/95 border-indigo-200 dark:border-indigo-500/40 text-indigo-950 dark:text-indigo-100 shadow-indigo-500/10': item.type === 'info',
            }"
        >
            {{-- Icon Container --}}
            <div
                class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center shadow-inner"
                :class="{
                    'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': item.type === 'success',
                    'bg-rose-500/15 text-rose-600 dark:text-rose-400': item.type === 'error',
                    'bg-amber-500/15 text-amber-600 dark:text-amber-400': item.type === 'warning',
                    'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400': item.type === 'info',
                }"
            >
                {{-- Success Icon --}}
                <svg x-show="item.type === 'success'" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>

                {{-- Error Icon --}}
                <svg x-show="item.type === 'error'" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>

                {{-- Warning Icon --}}
                <svg x-show="item.type === 'warning'" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>

                {{-- Info Icon --}}
                <svg x-show="item.type === 'info'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
            </div>

            {{-- Message text --}}
            <div class="flex-1 pt-0.5 min-w-0">
                <p class="text-xs sm:text-sm font-semibold leading-relaxed wrap-break-word font-sans"
                    x-text="item.text"></p>
            </div>

            {{-- Close button --}}
            <button
                type="button"
                class="shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                @click="remove(item.id)"
                aria-label="بستن"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<div x-data="dashboardLayout()" x-init="init()" class="min-h-dvh flex">

    {{-- Desktop Sidebar --}}
    <aside :class="sidebarCollapsed ? 'w-20' : 'w-72'"
           class="transition-all duration-200 ease-in-out bg-white/95 dark:bg-gray-800/95 border-l lg:border-l-0 lg:border-r border-gray-200/70 dark:border-gray-700/60 h-screen max-h-screen sticky top-0 z-30 hidden lg:flex flex-col overflow-hidden">
        @include('user.partials.sidebar')
    </aside>

    {{-- Mobile Sidebar --}}
    <div x-show="mobileOpen" x-cloak class="lg:hidden" x-ref="dialog" aria-modal="true">
        {{-- Overlay --}}
        <div x-show="mobileOpen" x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/80 z-40"></div>

        {{-- Sidebar Content --}}
        <div class="fixed inset-0 flex z-50">
            <div x-show="mobileOpen" @click.outside="mobileOpen = false"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                 class="relative ml-auto flex h-full w-full max-w-xs flex-col overflow-y-auto bg-white dark:bg-gray-800 pb-12 shadow-xl">

                {{-- Close button for mobile sidebar --}}
                <div class="absolute top-0 left-0 -ml-12 pt-2">
                    <button type="button" class="flex h-10 w-10 items-center justify-center p-2 text-gray-400"
                            @click="mobileOpen = false">
                        <span class="sr-only">بستن منو</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                             aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- By setting sidebarCollapsed to false, we ensure the mobile menu is always expanded --}}
                <div x-data="{ sidebarCollapsed: false }" class="flex flex-col h-full">
                    @include('user.partials.sidebar')
                </div>
            </div>
        </div>
    </div>

    {{-- Main column --}}
    <div class="flex-1 min-w-0 flex flex-col">
        {{-- Topbar --}}
        @include('user.partials.topbar')

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if (View::hasSection('content'))
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        {{-- Footer if custom footer text or support contact is defined --}}
        @if(!empty($brandFooterText) || !empty($supportPhone) || !empty($supportEmail))
            <footer
                class="mt-auto px-6 py-4 text-xs text-gray-400 dark:text-gray-500 border-t border-gray-100 dark:border-gray-800/80 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white/40 dark:bg-gray-900/40 backdrop-blur-xs">
                <div>
                    {{ $brandFooterText ?: ($brandTitle . ' - تمامی حقوق محفوظ است.') }}
                </div>
                @if(!empty($supportPhone) || !empty($supportEmail))
                    <div class="flex items-center gap-4 text-[11px]">
                        @if(!empty($supportPhone))
                            <span class="inline-flex items-center gap-1">
                                <span>پشتیبانی:</span>
                                <a href="tel:{{ $supportPhone }}"
                                   class="font-bold hover:text-gray-600 dark:hover:text-gray-300 dir-ltr">{{ $supportPhone }}</a>
                            </span>
                        @endif
                        @if(!empty($supportEmail))
                            <span class="inline-flex items-center gap-1">
                                <a href="mailto:{{ $supportEmail }}"
                                   class="hover:text-gray-600 dark:hover:text-gray-300 dir-ltr">{{ $supportEmail }}</a>
                            </span>
                        @endif
                    </div>
                @endif
            </footer>
        @endif
    </div>
</div>


{{-- Alpine helpers --}}
<script>
    window.showToast = function (param1, param2, duration = 5000) {
        const validTypes = ['success', 'error', 'warning', 'info', 'danger'];
        let type = 'info';
        let message = '';

        if (typeof param1 === 'string' && validTypes.includes(param1.toLowerCase())) {
            type = param1.toLowerCase() === 'danger' ? 'error' : param1.toLowerCase();
            message = param2 || '';
        } else if (typeof param2 === 'string' && validTypes.includes(param2.toLowerCase())) {
            type = param2.toLowerCase() === 'danger' ? 'error' : param2.toLowerCase();
            message = param1 || '';
        } else if (typeof param1 === 'object' && param1 !== null) {
            let t = param1.type ? String(param1.type).toLowerCase() : '';
            type = validTypes.includes(t) ? (t === 'danger' ? 'error' : t) : 'info';
            message = param1.text || param1.message || param1.title || '';
            if (param1.duration) duration = param1.duration;
        } else {
            message = param1 || '';
            type = (typeof param2 === 'string' && validTypes.includes(param2.toLowerCase())) ? (param2.toLowerCase() === 'danger' ? 'error' : param2.toLowerCase()) : 'info';
        }

        window.dispatchEvent(new CustomEvent('notify', {
            detail: {type: type, text: message, message: message, duration: duration}
        }));
    };

    function dashboardLayout() {
        return {
            mobileOpen: false,
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            sidebarFilter: '',
            openedMenuKey: localStorage.getItem('openedMenuKey') || null,
            activeClosedKeys: JSON.parse(localStorage.getItem('activeClosedKeys') || '{}'),
            closedAll: false,
            theme: localStorage.getItem('theme') || 'system',
            themeIcon: 'system',

            init() {
                this.applyTheme();
                this.$watch('theme', () => this.applyTheme());
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                    if (this.theme === 'system') this.applyTheme();
                });
                localStorage.removeItem('openMenus');
            },

            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            },

            isMenuOpen(key, isGroupActive = false) {
                if (this.sidebarCollapsed) return false;
                if (this.sidebarFilter && this.sidebarFilter.trim().length > 0) return true;
                if (this.closedAll) return false;
                if (isGroupActive) {
                    return !this.activeClosedKeys[key];
                }
                return this.openedMenuKey === key;
            },
            toggleMenu(key, isGroupActive = false) {
                if (this.sidebarCollapsed) return;
                this.closedAll = false;
                if (isGroupActive) {
                    this.activeClosedKeys[key] = !this.activeClosedKeys[key];
                    localStorage.setItem('activeClosedKeys', JSON.stringify(this.activeClosedKeys));
                } else {
                    if (this.openedMenuKey === key) {
                        this.openedMenuKey = null;
                    } else {
                        this.openedMenuKey = key;
                    }
                    localStorage.setItem('openedMenuKey', this.openedMenuKey || '');
                }
            },
            closeAllMenus() {
                this.openedMenuKey = null;
                this.closedAll = true;
                this.activeClosedKeys = {};
                this.sidebarFilter = '';
                this.mobileOpen = false;
                try {
                    localStorage.removeItem('openedMenuKey');
                    localStorage.removeItem('activeClosedKeys');
                } catch (e) {
                }
                window.dispatchEvent(new CustomEvent('close-all-menus'));
            },

            themeTitle() {
                if (this.theme === 'dark') return 'حالت تاریک';
                if (this.theme === 'light') return 'حالت روشن';
                return 'همسان با سیستم';
            },
            updateThemeIcon() {
                if (this.theme === 'dark') {
                    this.themeIcon = 'dark';
                } else if (this.theme === 'light') {
                    this.themeIcon = 'light';
                } else {
                    this.themeIcon = 'system';
                }
            },
            applyTheme() {
                const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = this.theme === 'dark' || (this.theme === 'system' && sysDark);
                document.documentElement.classList.toggle('dark', isDark);
                this.updateThemeIcon();
            },
            cycleTheme() {
                const nextTheme = {
                    'system': 'dark',
                    'dark': 'light',
                    'light': 'system'
                };
                this.theme = nextTheme[this.theme] || 'system';
                localStorage.setItem('theme', this.theme);
                this.applyTheme();
            },
        }
    }
</script>

{{-- پالت دستورات سراسری Devigner UI (Ctrl+K / Cmd+K و دکمه جستجوی داک موبایل) --}}
<x-ui.command-palette />

{{-- داک ناوبری و دسترسی سریع موبایل Devigner UI --}}
<x-ui.dock />

{{-- سیستم اعلان و توست سراسری وایب‌فارسی برای عملیات تعاملی صفحه (کپی، حذف، ساخت خودکار) --}}
<x-ui.toast position="top-center" />

{{-- دستیار کپی در کلیپ‌بورد سراسری با پشتیبانی از محیط‌های لوکال و امن و بافر داخلی کلیپ‌بورد --}}
<script>
    window.__clipboardText = window.__clipboardText || '';

    window.copyToClipboard = function(text, successMessage = 'متن با موفقیت کپی شد.') {
        if (!text) return;
        window.__clipboardText = String(text);

        const notify = () => {
            if (window.toast) {
                window.toast.success(successMessage);
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(notify).catch(() => fallback(text, notify));
        } else {
            fallback(text, notify);
        }

        function fallback(val, cb) {
            const ta = document.createElement('textarea');
            ta.value = val;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                cb();
            } catch (e) {
                cb();
            }
            document.body.removeChild(ta);
        }
    };
</script>

{{-- منوی راست‌کلیک هوشمند Devigner UI برای تمامی اینپوت‌ها و فیلدهای فرم با عملکرد کامل جایگذاری و کپی --}}
<div
    x-data="{
        open: false,
        x: 0,
        y: 0,
        activeEl: null,
        val: '',
        isPassword: false,
        selStart: 0,
        selEnd: 0,

        init() {
            document.addEventListener('contextmenu', (e) => {
                const target = e.target.closest('input, textarea');
                if (!target || target.type === 'hidden' || target.type === 'checkbox' || target.type === 'radio' || target.type === 'file') {
                    return;
                }
                e.preventDefault();
                this.activeEl = target;
                this.val = target.value || '';
                this.selStart = target.selectionStart ?? 0;
                this.selEnd = target.selectionEnd ?? target.value.length;
                this.isPassword = target.type === 'password' ||
                    (target.name && target.name.toLowerCase().includes('password')) ||
                    (target.id && target.id.toLowerCase().includes('password'));

                this.open = true;
                this.$nextTick(() => {
                    const menu = this.$refs.ctxMenu;
                    const mW = menu ? menu.offsetWidth : 220;
                    const mH = menu ? menu.offsetHeight : 230;
                    const vw = window.innerWidth;
                    const vh = window.innerHeight;

                    let px = e.clientX;
                    let py = e.clientY;

                    if (px + mW > vw - 16) px = vw - mW - 16;
                    if (px < 16) px = 16;
                    if (py + mH > vh - 16) py = vh - mH - 16;
                    if (py < 16) py = 16;

                    this.x = px;
                    this.y = py;
                });
            });
        },

        copy() {
            if (!this.activeEl) return;
            const full = this.activeEl.value || '';
            const sel = (this.selStart !== this.selEnd) ? full.slice(this.selStart, this.selEnd) : full;
            const textToCopy = sel || full;
            if (textToCopy) {
                window.copyToClipboard(textToCopy, 'متن در حافظه کپی شد');
            }
            this.open = false;
        },

        cut() {
            if (!this.activeEl) return;
            const full = this.activeEl.value || '';
            const hasSel = (this.selStart !== this.selEnd);
            const textToCopy = hasSel ? full.slice(this.selStart, this.selEnd) : full;
            if (textToCopy) {
                window.copyToClipboard(textToCopy, 'متن برش داده شد');
            }
            if (hasSel) {
                this.activeEl.value = full.slice(0, this.selStart) + full.slice(this.selEnd);
                this.activeEl.selectionStart = this.activeEl.selectionEnd = this.selStart;
            } else {
                this.activeEl.value = '';
            }
            this.activeEl.dispatchEvent(new Event('input', { bubbles: true }));
            this.activeEl.dispatchEvent(new Event('change', { bubbles: true }));
            this.open = false;
        },

        async paste() {
            if (!this.activeEl) return;
            let text = '';

            // مرحله ۱: تلاش برای خواندن مستقیم از کلیپ‌بورد سیستم
            if (navigator.clipboard && navigator.clipboard.readText && window.isSecureContext) {
                try {
                    text = await navigator.clipboard.readText();
                } catch (e) {}
            }

            // مرحله ۲: استفاده از بافر اختصاصی کلیپ‌بورد CRM (برای محیط‌های لوکال HTTP)
            if (!text && window.__clipboardText) {
                text = window.__clipboardText;
            }

            // مرحله ۳: در صورت عدم وجود هر دو، پرسش سریع از کاربر
            if (!text) {
                text = window.prompt('متن مورد نظر جهت جایگذاری را وارد کنید:');
            }

            if (text) {
                const full = this.activeEl.value || '';
                const start = this.activeEl.selectionStart ?? this.selStart ?? full.length;
                const end = this.activeEl.selectionEnd ?? this.selEnd ?? full.length;

                this.activeEl.value = full.slice(0, start) + text + full.slice(end);
                this.activeEl.selectionStart = this.activeEl.selectionEnd = start + text.length;
                this.activeEl.focus();

                this.activeEl.dispatchEvent(new Event('input', { bubbles: true }));
                this.activeEl.dispatchEvent(new Event('change', { bubbles: true }));
                if (window.toast) {
                    window.toast.success('متن با موفقیت جایگذاری شد.');
                }
            } else if (window.toast) {
                window.toast.info('محتوایی در حافظه کلیپ‌بورد برای جایگذاری یافت نشد.');
            }
            this.open = false;
        },

        selectAll() {
            if (!this.activeEl) return;
            this.activeEl.focus();
            this.activeEl.select();
            this.open = false;
        },

        clear() {
            if (!this.activeEl) return;
            this.activeEl.value = '';
            this.activeEl.dispatchEvent(new Event('input', { bubbles: true }));
            this.activeEl.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.toast) {
                window.toast.info('محتوای فیلد پاک گردید.');
            }
            this.open = false;
        },

        generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
            let pass = '';
            for (let i = 0; i < 12; i++) {
                pass += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            if (this.activeEl) {
                this.activeEl.value = pass;
                this.activeEl.dispatchEvent(new Event('input', { bubbles: true }));
                this.activeEl.dispatchEvent(new Event('change', { bubbles: true }));
                window.copyToClipboard(pass, 'رمز عبور قوی تولید، درج و در کلیپ‌بورد کپی شد');
            }
            this.open = false;
        }
    }"
    x-cloak
    @keydown.escape.window="open = false"
    @scroll.window="open = false"
    @click.outside="open = false"
>
    <div
        x-ref="ctxMenu"
        x-show="open"
        x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="{ top: y + 'px', left: x + 'px' }"
        class="fixed z-[9999] min-w-[210px] p-1.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-2xl border border-gray-200/90 dark:border-gray-800 rounded-2xl shadow-2xl text-xs space-y-0.5 select-none"
        style="box-shadow: 0 16px 36px -4px rgba(0,0,0,0.25);"
    >
        {{-- دکمه کپی متن --}}
        <button
            type="button"
            @click="copy()"
            :disabled="!val"
            class="flex items-center justify-between w-full px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed group text-right cursor-pointer"
        >
            <div class="flex items-center gap-2">
                <svg class="size-4 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span class="font-bold">کپی متن</span>
            </div>
            <span class="text-[10px] text-gray-400 font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">Ctrl+C</span>
        </button>

        {{-- دکمه برش متن --}}
        <button
            type="button"
            @click="cut()"
            :disabled="!val"
            class="flex items-center justify-between w-full px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed group text-right cursor-pointer"
        >
            <div class="flex items-center gap-2">
                <svg class="size-4 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>
                </svg>
                <span class="font-bold">برش متن</span>
            </div>
            <span class="text-[10px] text-gray-400 font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">Ctrl+X</span>
        </button>

        {{-- دکمه جایگذاری با پشتیبانی هوشمند --}}
        <button
            type="button"
            @click="paste()"
            class="flex items-center justify-between w-full px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 rounded-xl transition-colors group text-right cursor-pointer"
        >
            <div class="flex items-center gap-2">
                <svg class="size-4 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="font-bold">جایگذاری</span>
            </div>
            <span class="text-[10px] text-gray-400 font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">Ctrl+V</span>
        </button>

        {{-- خط جداکننده --}}
        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

        {{-- گزینه تولید رمز تصادفی اگر فیلد پسورد باشد --}}
        <template x-if="isPassword">
            <button
                type="button"
                @click="generatePassword()"
                class="flex items-center justify-between w-full px-3 py-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-colors group text-right font-bold cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>تولید رمز عبور تصادفی</span>
                </div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold">اتوماتیک</span>
            </button>
        </template>

        {{-- انتخاب همه --}}
        <button
            type="button"
            @click="selectAll()"
            class="flex items-center justify-between w-full px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 rounded-xl transition-colors group text-right cursor-pointer"
        >
            <div class="flex items-center gap-2">
                <svg class="size-4 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span class="font-bold">انتخاب همه</span>
            </div>
            <span class="text-[10px] text-gray-400 font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">Ctrl+A</span>
        </button>

        {{-- پاک‌کردن فیلد --}}
        <button
            type="button"
            @click="clear()"
            :disabled="!val"
            class="flex items-center justify-between w-full px-3 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed group text-right cursor-pointer"
        >
            <div class="flex items-center gap-2">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span class="font-bold">پاک کردن فیلد</span>
            </div>
        </button>
    </div>
</div>

@livewireScripts
@livewireScriptConfig
@includeIf('partials.jalali-date-picker')
@stack('scripts')
@stack('js')
</body>
</html>
