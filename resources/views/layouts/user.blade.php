@php
    $currentTenant = $currentTenant ?? app(\App\Tenancy\TenantManager::class)->getTenant() ?? auth()->user()?->tenant;
    $brandTitle = ($currentTenant && $currentTenant->getBranding('brand_title'))
        ? $currentTenant->getBranding('brand_title')
        : ($currentTenant?->name ?: config('app.name', 'Laravel'));
    $brandLogo = $currentTenant?->getBranding('brand_logo');
    $brandFavicon = $currentTenant?->getBranding('brand_favicon');
    $brandPrimaryColor = $currentTenant?->getBranding('brand_primary_color');
    $brandFooterText = $currentTenant?->getBranding('brand_footer_text');
    $supportPhone = $currentTenant?->getBranding('support_phone');
    $supportEmail = $currentTenant?->getBranding('support_email');

    $hexToRgb = function(?string $hex) {
        if (empty($hex)) return '79, 70, 229';
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } elseif (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            return '79, 70, 229';
        }
        return "{$r}, {$g}, {$b}";
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
    <style>
        body {
            font-size: 14px;
        }

        jdp-container {
            z-index: 9999 !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @if(!empty($brandPrimaryColor))
        <style>
            :root {
                --brand-primary: {{ $brandPrimaryColor }};
                --brand-primary-rgb: {{ $hexToRgb($brandPrimaryColor) }};
            }

            .bg-indigo-600, .bg-indigo-500, button.bg-indigo-600, a.bg-indigo-600 {
                background-color: var(--brand-primary) !important;
            }

            .hover\:bg-indigo-700:hover, button.bg-indigo-600:hover, a.bg-indigo-600:hover {
                filter: brightness(0.9) !important;
            }

            .text-indigo-600, .text-indigo-500, .text-indigo-700 {
                color: var(--brand-primary) !important;
            }

            .border-indigo-500, .border-indigo-600 {
                border-color: var(--brand-primary) !important;
            }

            .focus\:ring-indigo-500:focus, .ring-indigo-500:focus {
                --tw-ring-color: var(--brand-primary) !important;
            }

            .focus\:border-indigo-500:focus {
                border-color: var(--brand-primary) !important;
            }

            .from-indigo-500, .from-indigo-600 {
                --tw-gradient-from: var(--brand-primary) var(--tw-gradient-from-position) !important;
                --tw-gradient-to: rgb(var(--brand-primary-rgb) / 0) var(--tw-gradient-to-position) !important;
                --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
            }

            .bg-indigo-50, .dark .bg-indigo-950\/60, .dark .bg-indigo-900\/60 {
                background-color: rgba(var(--brand-primary-rgb), 0.1) !important;
            }

            .border-indigo-100, .border-indigo-200, .dark .border-indigo-800\/60 {
                border-color: rgba(var(--brand-primary-rgb), 0.25) !important;
            }
        </style>
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(function_exists('is_panel_indexing_blocked') && is_panel_indexing_blocked())
        <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
        <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
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
            let type = 'info';
            let text = '';
            let duration = 5000;

            if (typeof detail === 'string') {
                text = detail;
            } else if (typeof detail === 'object' && detail !== null) {
                let rawType = detail.type ? String(detail.type).toLowerCase() : '';
                let rawText = detail.text || detail.message || detail.title || '';
                duration = detail.duration || 5000;

                if (validTypes.includes(rawType)) {
                    type = rawType === 'danger' ? 'error' : rawType;
                    text = rawText;
                } else if (validTypes.includes(String(rawText).toLowerCase())) {
                    type = String(rawText).toLowerCase() === 'danger' ? 'error' : String(rawText).toLowerCase();
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
        const initialMsgs = @js($initialFlashMessages);
        if (Array.isArray(initialMsgs)) {
            initialMsgs.forEach(m => notify(m));
        }
    "
    class="fixed top-5 right-5 sm:right-6 z-[99999] w-96 max-w-[calc(100vw-2.5rem)] space-y-3 pointer-events-none font-sans"
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
                <p class="text-xs sm:text-sm font-semibold leading-relaxed break-words font-sans"
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
                return this.theme === 'dark' ? 'حالت تاریک'
                    : this.theme === 'light' ? 'حالت روشن'
                        : 'همسان با سیستم';
            },
            updateThemeIcon() {
                this.themeIcon = this.theme === 'dark' ? 'dark'
                    : this.theme === 'light' ? 'light'
                        : 'system';
            },
            applyTheme() {
                const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = this.theme === 'dark' || (this.theme === 'system' && sysDark);
                document.documentElement.classList.toggle('dark', isDark);
                this.updateThemeIcon();
            },
            cycleTheme() {
                this.theme = this.theme === 'system' ? 'dark' : this.theme === 'dark' ? 'light' : 'system';
                localStorage.setItem('theme', this.theme);
                this.applyTheme();
            },
        }
    }
</script>
@livewireScripts
@livewireScriptConfig
@includeIf('partials.jalali-date-picker')
@includeIf('resellerhub::partials.impersonation-banner')
@includeIf('resellerhub::partials.prevent-double-submit')
@includeIf('resellerhub::partials.quota-exceeded-modal')
@includeIf('resellerhub::partials.invoice-payment-modal')
@includeIf('resellerhub::partials.renewal-limit-modal')
@includeIf('resellerhub::partials.cancel-invoice-modal')
@stack('scripts')
@stack('js')
</body>
</html>
