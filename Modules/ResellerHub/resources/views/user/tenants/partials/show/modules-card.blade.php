@php
    use Modules\ResellerHub\App\Enums\QuotaKey;use Modules\ResellerHub\App\Services\TenantProvisioningService;

    $features = $quotasSummary['features'] ?? [];
    $activeModules = $tenant->modules->where('is_active', true);
    $activeModuleSlugs = $activeModules->pluck('module_slug')->toArray();
    $totalActiveFeaturesCount = collect($features)->where('is_enabled', true)->count();

    // System modules list for identifying inactive ones (Dynamically queried from active system modules)
    $systemModulesList = \App\Models\Module::where('installed', true)
        ->where('active', true)
        ->whereNotIn('slug', ['resellerhub', 'settings', 'admin', 'core'])
        ->get();
    $allKnownModules = [];
    foreach ($systemModulesList as $sMod) {
        $sSlug = strtolower(trim($sMod->slug));
        $allKnownModules[$sSlug] = [
            'title' => TenantProvisioningService::resolveModuleTitle($sSlug),
            'icon' => match ($sSlug) {
                'booking' => 'calendar',
                'clients' => 'users',
                'services' => 'receipt',
                'reminders' => 'bell',
                'market' => 'shopping-cart',
                'sms' => 'chat',
                'workflows' => 'refresh',
                'accounting' => 'credit-card',
                default => 'cube',
            },
        ];
    }

    $inactiveSystemModules = array_diff_key($allKnownModules, array_flip($activeModuleSlugs));
@endphp

{{-- Active Modules & Capabilities Card --}}
<div
    class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-6 backdrop-blur-xl">

    {{-- Section Header --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
            <span
                class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-100/80 dark:border-emerald-800/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">ماژول‌ها و قابلیت‌های فعال سامانه</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    لایسنس ماژول‌های نصب‌شده و وضعیت فعال‌سازی قابلیت‌های اختصاصی هر بخش
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-bold border border-emerald-200/60 dark:border-emerald-800/40">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ reseller_fa_num($activeModules->count()) }} ماژول فعال
            </span>

            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-bold border border-indigo-200/60 dark:border-indigo-800/40">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                {{ reseller_fa_num($totalActiveFeaturesCount) }} قابلیت تخصصی فعال
            </span>

            <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-xl transition border border-indigo-100/80 dark:border-indigo-800/40">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>ویرایش ماژول‌ها و امکانات</span>
            </a>
        </div>
    </div>

    {{-- Active Modules Showcase --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse($tenant->modules as $mod)
            @php
                $modSlug = $mod->module_slug;
                $modTitle = TenantProvisioningService::resolveModuleTitle($modSlug);
                $modFeatures = collect($features)->where('module', $modSlug);
                $activeModFeaturesCount = $modFeatures->where('is_enabled', true)->count();

                $modIconSvg = match ($modSlug) {
                    'booking' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                    'clients' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                    'services' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>',
                    'reminders' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
                    'market' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
                };
            @endphp

            <div
                class="rounded-2xl border {{ $mod->is_active ? 'border-gray-200 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/50' : 'border-gray-200/60 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/20 opacity-70' }} p-5 space-y-4 flex flex-col justify-between">

                {{-- Module Header --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border border-gray-200/80 dark:border-gray-700/70 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $modIconSvg !!}
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ $modTitle }}</h3>
                                    <span class="text-xs text-gray-400 font-mono">({{ $modSlug }})</span>
                                </div>
                                <span class="text-xs text-gray-400 block mt-0.5">
                                    @if($mod->expires_at)
                                        تاریخ انقضای ماژول: {{ reseller_format_jalali($mod->expires_at, 'Y/m/d') }}
                                    @else
                                        اعتبار ماژول: تابع اشتراک اصلی سامانه
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Module Active Badge --}}
                        <div class="shrink-0">
                            @if($mod->is_active)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    فعال
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200/60 dark:border-gray-700/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    غیرفعال
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Capabilities Sub-grid --}}
                    @if($modFeatures->isNotEmpty())
                        <div class="pt-2 border-t border-gray-200/70 dark:border-gray-700/60 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span
                                    class="font-bold text-gray-700 dark:text-gray-300">قابلیت‌های تخصصی این ماژول:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ reseller_fa_num($activeModFeaturesCount) }} از {{ reseller_fa_num($modFeatures->count()) }} فعال
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($modFeatures as $fKey => $f)
                                    @php
                                        $fEnabled = (bool)($f['is_enabled'] ?? false);
                                        $fEnum = QuotaKey::tryFrom($fKey);
                                        $fIconSvg = $fEnum?->styling()['icon'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
                                    @endphp

                                    <div
                                        class="p-2.5 rounded-xl border flex items-center justify-between gap-2 {{ $fEnabled ? 'bg-white dark:bg-gray-800 border-gray-200/90 dark:border-gray-700/80 shadow-2xs' : 'bg-gray-100/60 dark:bg-gray-850 border-gray-200/40 dark:border-gray-800 opacity-60' }}">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span
                                                class="w-6 h-6 rounded-lg {{ $fEnabled ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-gray-200/70 dark:bg-gray-700 text-gray-400' }} flex items-center justify-center shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    {!! $fIconSvg !!}
                                                </svg>
                                            </span>
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate"
                                                  title="{{ $f['description'] ?? $f['title'] }}">
                                                {{ $f['title'] }}
                                            </span>
                                        </div>

                                        <span class="shrink-0">
                                            @if($fEnabled)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    فعال
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200/60 dark:border-gray-700/60">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                    غیرفعال
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="pt-2 border-t border-gray-200/70 dark:border-gray-700/60">
                            <div
                                class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/70 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>تمامی قابلیت‌ها و منوهای استاندارد این ماژول برای سامانه فعال و در دسترس می‌باشند.</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Module Footer --}}
                <div
                    class="pt-3 border-t border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between text-xs">
                    <span class="text-gray-400">
                        وضعیت دسترسی: <strong
                            class="text-gray-700 dark:text-gray-300">{{ $mod->is_active ? 'مجاز' : 'مسدود' }}</strong>
                    </span>
                    <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
                       class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-xs">
                        تنظیمات ماژول
                    </a>
                </div>
            </div>
        @empty
            <div
                class="p-8 text-center text-sm text-gray-400 col-span-full bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                هیچ ماژولی برای این سامانه تعریف یا فعال نشده است.
            </div>
        @endforelse
    </div>

    {{-- Inactive System Modules Showcase (Upsell & Visibility) --}}
    @if(!empty($inactiveSystemModules))
        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60">
            <div
                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/30 border border-dashed border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-gray-200/70 dark:bg-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200">سایر ماژول‌های سیستم که در این
                            CRM فعال نیستند:</h4>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            @foreach($inactiveSystemModules as $inSlug => $inMod)
                                <span
                                    class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-gray-200/80 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    {{ $inMod['title'] }} ({{ $inSlug }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>افزودن و فعال‌سازی ماژول‌ها</span>
                </a>
            </div>
        </div>
    @endif
</div>
