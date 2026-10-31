@php
    use App\Tenancy\TenantManager;$currentTenant = $currentTenant ?? app(TenantManager::class)->getTenant() ?? auth()->user()?->tenant;
    $menuService = app(App\Services\Modules\ModuleMenuService::class);
    $menuData = $menuService->getAllForUser(auth()->user());
    $menuItems = $menuData['items'] ?? [];
    $menuGroups = $menuData['groups'] ?? [];
    $settingsItems = $menuData['settings'] ?? [];

    $customMenuService = app(App\Services\Modules\MenuCustomizationService::class);
    $isCustomEnabled = $customMenuService->isCustomMenuEnabled();
    $isTwoStepEnabled = $customMenuService->isTwoStepMenuEnabled();

    // Backward compatibility for clients module only in core/default mode
    $clientsItems = [];
    if (!$isCustomEnabled) {
        foreach ($menuItems as $key => $item) {
            if ($item['group'] === 'clients') {
                $clientsItems[] = $item;
                unset($menuItems[$key]);
            }
        }
        foreach ($menuGroups as $key => $group) {
            if ($group['module'] === 'clients') {
                $clientsItems = $group['items'];
                unset($menuGroups[$key]);
            }
        }
    }

    $clientsGroupMeta = $menuData['clients_group_meta'] ?? null;
    $settingsGroupMeta = $menuData['settings_group_meta'] ?? null;

    if (!empty($clientsGroupMeta['hidden']) && !$isCustomEnabled) {
        $clientsItems = [];
    }
    $menuItems = array_values($menuItems);
    $menuGroups = array_values($menuGroups);
@endphp

{{-- Header section of the sidebar --}}
<div
    class="h-20 px-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-800/80 shrink-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md z-10 transition-colors">
    <div class="flex items-center gap-3 overflow-hidden">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 group">
            @if(isset($currentTenant) && $currentTenant && $currentTenant->getBranding('brand_logo'))
                <img src="{{ asset('storage/' . $currentTenant->getBranding('brand_logo')) }}" alt="Logo"
                     class="w-10 h-10 rounded-xl object-contain bg-white dark:bg-gray-800 p-1 border border-gray-100 dark:border-gray-700 shadow-xs">
            @else
                <div
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-md shadow-indigo-500/20 dark:shadow-indigo-900/40 group-hover:scale-105 transition-all duration-300">
                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
            @endif
            <div x-show="!sidebarCollapsed"
                 x-transition:enter="transition ease-out duration-300 delay-75"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="flex flex-col min-w-0">
                <span class="font-black text-base truncate text-gray-900 dark:text-white tracking-tight leading-tight">
                    {{ (isset($currentTenant) && $currentTenant) ? ($currentTenant->getBranding('brand_title') ?: $currentTenant->name) : config('app.name', 'پنل کاربری') }}
                </span>
                <span class="text-[11px] text-gray-400 dark:text-gray-400 font-medium">
                    {{ (isset($currentTenant) && $currentTenant) ? $currentTenant->name : 'داشبورد مدیریت' }}
                </span>
            </div>
        </a>
    </div>

    <button @click="toggleSidebar()"
            class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300 transition-all duration-200 hidden lg:flex items-center justify-center border border-gray-200/60 dark:border-gray-700/60"
            :title="sidebarCollapsed ? 'باز کردن منو' : 'جمع کردن منو'">
        <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-4 h-4 transition-transform hover:-translate-x-0.5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M9 6l6 6l-6 6"/>
        </svg>
        <svg x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-4 h-4 transition-transform hover:translate-x-0.5">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M15 6l-6 6l6 6"/>
        </svg>
    </button>
</div>

{{-- Sidebar Search / Filter --}}
<div x-show="!sidebarCollapsed"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="px-3.5 pt-3 pb-1 shrink-0">
    <div class="relative">
        <input type="text"
               x-model="sidebarFilter"
               placeholder="فیلتر سریع منوها..."
               class="w-full h-9 pr-8 pl-7 text-xs rounded-xl bg-gray-100/80 dark:bg-gray-800/90 border border-gray-200/50 dark:border-gray-700/60 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-900 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-400/20 transition-all">
        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 pointer-events-none">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
        <button x-show="sidebarFilter.length > 0"
                @click="sidebarFilter = ''"
                type="button"
                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 p-0.5 rounded-md text-[10px]">
            ✕
        </button>
    </div>
</div>

{{-- Navigation Links --}}
<nav class="p-3 flex-1 overflow-y-auto space-y-1.5 custom-scrollbar">
    @if(isset($currentTenant) && $currentTenant && method_exists($currentTenant, 'hasActivePaidPlan') && !$currentTenant->hasActivePaidPlan())
        {{-- Dashboard Only (Locked Mode) --}}
        <a href="{{ route('user.dashboard') }}"
           :title="sidebarCollapsed ? 'پیشخوان' : ''"
           :class="sidebarCollapsed ? 'justify-center p-2.5' : 'justify-between px-3 py-2.5'"
           class="group flex items-center rounded-xl font-bold transition-all duration-200 text-sm bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-200 border border-indigo-100 dark:border-indigo-800/60 shadow-xs">
            <div class="flex items-center gap-3">
                <span
                    class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">پیشخوان</span>
            </div>
        </a>

        {{-- Locked Notice (Expanded) --}}
        <div x-show="!sidebarCollapsed" x-cloak
             class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 mt-4 text-right space-y-2">
            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="text-xs font-bold">منوها در انتظار فعال‌سازی پکیج</span>
            </div>
            <p class="text-[11px] text-gray-600 dark:text-gray-400 leading-relaxed">
                پس از تکمیل مراحل راه‌اندازی و انتخاب و پرداخت پکیج، دسترسی به تمام ماژول‌ها باز خواهد شد.
            </p>
        </div>

        {{-- Locked Notice (Collapsed - Icon with Tooltip) --}}
        <div x-show="sidebarCollapsed" x-cloak class="mt-3 flex justify-center">
            <div
                class="w-10 h-10 rounded-xl bg-amber-50/90 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/70 text-amber-600 dark:text-amber-400 flex items-center justify-center cursor-help transition-all hover:scale-105"
                title="منوها در انتظار فعال‌سازی پکیج (دسترسی به ماژول‌ها پس از پرداخت پکیج فعال می‌شود)">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>
    @else
        @include('user.partials.sidebar-nav', [
            'menuItems' => $menuItems,
            'menuGroups' => $menuGroups,
            'clientsItems' => $clientsItems,
            'settingsItems' => $settingsItems,
            'clientsGroupMeta' => $clientsGroupMeta,
            'settingsGroupMeta' => $settingsGroupMeta,
            'isCustomEnabled' => $isCustomEnabled,
            'isTwoStepEnabled' => $isTwoStepEnabled,
            'menuBlocks' => $menuData['blocks'] ?? [],
        ])
    @endif
</nav>

{{-- Subscription / Upgrade Widget at bottom of sidebar --}}
@includeIf('resellerhub::partials.sidebar-subscription-widget')

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e5e7eb;
        border-radius: 10px;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #374151;
    }

    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
    }

    .dark .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background-color: #4b5563;
    }
</style>
