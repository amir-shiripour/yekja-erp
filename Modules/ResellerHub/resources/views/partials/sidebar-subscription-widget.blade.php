@if(isset($currentTenant) && $currentTenant && $currentTenant->reseller_id)
    @php
        try {
            $currentPkg = $currentTenant->package;
            $hasActivePlan = $currentPkg && (method_exists($currentTenant, 'hasActivePaidPlan') ? $currentTenant->hasActivePaidPlan() : true);
            $pkgName = $hasActivePlan ? $currentPkg->name : ($currentPkg?->name ?? 'بدون پکیج');
            $upgradeUrl = route('profile.show', ['active_tab' => 'subscription']);
            $buttonLabel = $hasActivePlan ? 'ارتقای پکیج' : 'انتخاب و فعال‌سازی پکیج';
        } catch (Throwable $e) {
            $hasActivePlan = false;
            $pkgName = 'بدون پکیج';
            $upgradeUrl = '#';
            $buttonLabel = 'پکیج';
        }
    @endphp

    {{-- Expanded view --}}
    <div x-show="!sidebarCollapsed"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="p-3 shrink-0 border-t border-gray-100 dark:border-gray-800/80">
        <div
            class="p-3.5 rounded-2xl @if($hasActivePlan) bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/60 @else bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 @endif shadow-xs relative overflow-hidden group hover:border-indigo-200 dark:hover:border-indigo-700/70 transition-all duration-300">
            <div class="flex items-center justify-between gap-2 mb-2.5">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span
                        class="w-2 h-2 rounded-full @if($hasActivePlan) bg-emerald-500 animate-pulse @else bg-amber-500 @endif shrink-0"></span>
                    <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 truncate">
                        {{ $hasActivePlan ? 'پلن جاری سامانه:' : 'وضعیت پلن:' }}
                    </span>
                </div>
                <span
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black @if($hasActivePlan) bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60 @else bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 @endif truncate max-w-[120px] shrink-0 shadow-2xs">
                    {{ $hasActivePlan ? $pkgName : 'فاقد پکیج فعال' }}
                </span>
            </div>

            <a href="{{ $upgradeUrl }}"
               class="relative w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl @if($hasActivePlan) bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/20 hover:shadow-indigo-600/35 @else bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20 hover:shadow-emerald-600/35 @endif active:scale-95 text-white text-xs font-black shadow-md hover:-translate-y-0.5 transition-all duration-300 overflow-hidden group/btn cursor-pointer select-none">
                {{-- Smooth Shimmer Light Sheen on hover --}}
                <span
                    class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700 ease-out pointer-events-none"></span>

                <svg
                    class="w-4 h-4 transition-transform duration-300 group-hover/btn:-translate-y-0.5 group-hover/btn:scale-110 shrink-0"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3"
                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span class="tracking-tight">{{ $buttonLabel }}</span>
            </a>
        </div>
    </div>

    {{-- Collapsed view (icon button with tooltip) --}}
    <div x-show="sidebarCollapsed"
         class="p-2.5 shrink-0 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-center">
        <a href="{{ $upgradeUrl }}"
           class="w-10 h-10 rounded-xl @if($hasActivePlan) bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/20 hover:shadow-indigo-600/35 @else bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20 hover:shadow-emerald-600/35 @endif active:scale-95 text-white flex items-center justify-center shadow-md hover:scale-105 transition-all duration-300"
           title="{{ $buttonLabel }} ({{ $hasActivePlan ? $pkgName : 'فاقد پکیج' }})">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3"
                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </a>
    </div>
@endif
