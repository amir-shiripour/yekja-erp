@php
    $dashboardUrl = Route::has('user.dashboard') ? route('user.dashboard') : url('/user/dashboard');
    $clientsUrl = Route::has('user.clients.index')
        ? route('user.clients.index')
        : (Route::has('user.users.index') ? route('user.users.index') : $dashboardUrl);
    $createClientUrl = Route::has('user.clients.create')
        ? route('user.clients.create')
        : (Route::has('user.users.create') ? route('user.users.create') : null);
    $createUserUrl = Route::has('user.users.create') ? route('user.users.create') : null;
    $settingsUrl = Route::has('user.settings.index')
        ? route('user.settings.index')
        : (Route::has('settings.index') ? route('settings.index') : null);
@endphp

<div
    x-data="{ quickOpen: false }"
    @click.outside="quickOpen = false"
    class="md:hidden fixed bottom-3 inset-x-3 z-40 pointer-events-none"
>
    {{-- Quick Action Sheet (+) --}}
    <div
        x-cloak
        x-show="quickOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="pointer-events-auto mb-3 p-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-3xl shadow-2xl space-y-2"
    >
        <div class="text-[11px] font-black text-gray-400 px-2 pb-1 border-b border-gray-100 dark:border-gray-800">
            اقدام سریع
        </div>
        <div class="grid grid-cols-2 gap-2 text-xs font-bold">
            @if($createClientUrl)
                <a href="{{ $createClientUrl }}"
                   class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                    <span
                        class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm">+</span>
                    <span>ثبت مشتری</span>
                </a>
            @endif
            @if($createUserUrl)
                <a href="{{ $createUserUrl }}"
                   class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm">+</span>
                    <span>کاربر جدید</span>
                </a>
            @endif
            <button
                type="button"
                @click="quickOpen = false; $dispatch('open-command-palette')"
                class="p-2.5 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 flex items-center gap-2 col-span-2 justify-center"
            >
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>جستجوی سریع در ماژول‌ها</span>
            </button>
        </div>
    </div>

    {{-- Floating Dock Bar --}}
    <nav
        class="pointer-events-auto bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-gray-200/80 dark:border-gray-700/80 rounded-3xl shadow-2xl px-3 py-2 flex items-center justify-around">
        {{-- 1. Dashboard --}}
        <a href="{{ $dashboardUrl }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-2xl transition-colors {{ request()->routeIs('user.dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-bold">خانه</span>
        </a>

        {{-- 2. Clients / Users --}}
        <a href="{{ $clientsUrl }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-2xl transition-colors {{ request()->routeIs('user.clients.*', 'user.users.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-[10px] font-bold">مشتریان</span>
        </a>

        {{-- 3. Central Plus Action Button --}}
        <button
            type="button"
            @click="quickOpen = !quickOpen"
            class="-mt-5 w-12 h-12 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/35 flex items-center justify-center transition-transform active:scale-90"
            :class="quickOpen ? 'rotate-45 bg-rose-600' : ''"
            aria-label="اقدام سریع"
        >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </button>

        {{-- 4. Search / Command Palette --}}
        <button
            type="button"
            @click="$dispatch('open-command-palette')"
            class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-2xl text-gray-500 dark:text-gray-400 hover:text-indigo-600 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-[10px] font-bold">جستجو</span>
        </button>

        {{-- 5. Full Sidebar Menu --}}
        <button
            type="button"
            @click="if (typeof mobileOpen !== 'undefined') { mobileOpen = true; } else { $dispatch('toggle-mobile-sidebar'); }"
            class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-2xl text-gray-500 dark:text-gray-400 hover:text-indigo-600 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="text-[10px] font-bold">منو</span>
        </button>
    </nav>
</div>
