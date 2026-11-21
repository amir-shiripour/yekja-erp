@props([
    'items' => null,
])

@php
    $isAdmin = request()->routeIs('admin.*');
    $dashboardUrl = $isAdmin && Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : (Route::has('user.dashboard') ? route('user.dashboard') : url('/'));

    // Determine the most operational CRM route (FollowUps / Tasks)
    $followupsUrl = Route::has('user.followups.index')
        ? route('user.followups.index')
        : (Route::has('user.tasks.index')
            ? route('user.tasks.index')
            : (Route::has('admin.users.index') ? route('admin.users.index') : $dashboardUrl));

    $createUserUrl = $isAdmin && Route::has('admin.users.create')
        ? route('admin.users.create')
        : (Route::has('user.users.create') ? route('user.users.create') : null);

    $createFollowupUrl = Route::has('user.followups.create')
        ? route('user.followups.create')
        : (Route::has('user.tasks.create') ? route('user.tasks.create') : null);

    $createRoleUrl = $isAdmin && Route::has('admin.roles.create')
        ? route('admin.roles.create')
        : null;
@endphp

{{-- Devigner UI MenuDock (Harmonized width matching container, neutral action buttons, blue center) --}}
<div
    x-data="{
        expanded: false,
        activeMenu: null,
        toggleActionMenu() {
            this.expanded = !this.expanded;
        },
        close() {
            this.expanded = false;
            this.activeMenu = null;
        }
    }"
    @click.outside="close()"
    @keydown.escape.window="close()"
    class="md:hidden fixed bottom-4 inset-x-4 max-w-sm sm:max-w-md mx-auto z-40 select-none"
    role="navigation"
    aria-label="Menu Dock"
>
    {{-- Expanded Menu Panel (Dock grows upward with exact matching width) --}}
    <div
        x-cloak
        x-show="expanded"
        x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-full mb-3 p-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-2xl border border-gray-200/90 dark:border-gray-800 rounded-3xl shadow-2xl flex flex-col gap-1.5 text-right"
        style="box-shadow: 0 20px 40px -15px rgba(0,0,0,0.28);"
    >
        {{-- Header / Back --}}
        <div
            class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-gray-800 text-xs font-bold text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                اقدامات و دسترسی سریع
            </span>
            <button
                type="button"
                @click="close()"
                class="p-1.5 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                title="بستن منو"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Action Items --}}
        <div class="flex flex-col gap-1 py-1">
            @if($createUserUrl)
                <a
                    href="{{ $createUserUrl }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl text-xs font-bold text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                >
                    <span
                        class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/40">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path
                                stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    <div class="flex flex-col">
                        <span>افزودن کاربر جدید</span>
                    </div>
                </a>
            @endif

            @if($createRoleUrl)
                <a
                    href="{{ $createRoleUrl }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl text-xs font-bold text-gray-800 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                >
                    <span
                        class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/40">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <div class="flex flex-col">
                        <span>تعریف نقش سازمانی جدید</span>
                    </div>
                </a>
            @endif

            @if($createFollowupUrl)
                <a
                    href="{{ $createFollowupUrl }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-2xl text-xs font-bold text-gray-800 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors"
                >
                    <span
                        class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-900/40">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                    <div class="flex flex-col">
                        <span>ثبت پیگیری یا وظیفه جدید</span>
                    </div>
                </a>
            @endif

            <button
                type="button"
                @click="close(); $dispatch('open-command-palette')"
                class="flex items-center justify-between px-3 py-2.5 rounded-2xl text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors w-full"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                                stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <span>جستجوی پالت دستورات</span>
                </div>
            </button>
        </div>
    </div>

    {{-- Main Dock Capsule: Fully responsive width harmonized with page container --}}
    <nav
        class="w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-2xl border border-gray-200/90 dark:border-gray-800 rounded-full px-3 py-2 shadow-2xl flex items-center justify-between ring-1 ring-black/5 dark:ring-white/10"
        style="box-shadow: 0 12px 32px -4px rgba(0,0,0,0.18), 0 4px 12px -2px rgba(0,0,0,0.08);"
    >
        {{-- 1. Home / Dashboard (Clean white/neutral) --}}
        <a
            href="{{ $dashboardUrl }}"
            class="relative size-10 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 active:scale-95"
            title="داشبورد و خانه"
            aria-label="خانه"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>

        {{-- 2. FollowUps / Tasks (Replaced users button, Clean white/neutral) --}}
        <a
            href="{{ $followupsUrl }}"
            class="relative size-10 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 active:scale-95"
            title="پیگیری‌ها و وظایف"
            aria-label="پیگیری‌ها"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </a>

        {{-- 3. Center Expand Action Button (+) -> ONLY THIS ONE IS BLUE --}}
        <button
            type="button"
            @click="toggleActionMenu()"
            class="relative size-11 rounded-full flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/35 transition-all duration-200 active:scale-90 shrink-0"
            :class="expanded ? 'rotate-45 !bg-rose-600 shadow-rose-600/30' : ''"
            title="اقدام سریع"
            aria-label="اقدام سریع"
            :aria-expanded="expanded"
        >
            <svg class="size-6 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </button>

        {{-- 4. Search / Command Palette (Clean white/neutral) --}}
        <button
            type="button"
            @click="close(); $dispatch('open-command-palette')"
            class="relative size-10 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 active:scale-95"
            title="جستجوی سریع (Ctrl+K)"
            aria-label="جستجو"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>

        {{-- 5. Mobile Sidebar Menu Trigger (Clean white/neutral) --}}
        <button
            type="button"
            @click="close(); if (typeof mobileOpen !== 'undefined') { mobileOpen = true; } else { $dispatch('toggle-mobile-sidebar'); }"
            class="relative size-10 rounded-full flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 active:scale-95"
            title="منوی ناوبری اصلی"
            aria-label="منو"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </nav>
</div>
