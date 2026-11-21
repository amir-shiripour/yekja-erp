@php
    use App\Tenancy\TenantManager;$isImpersonating = session()->has('impersonator_id');
    $currentTenant = $currentTenant ?? app(TenantManager::class)->getTenant() ?? auth()->user()?->tenant;
    $notchTitle = $currentTenant ? ($currentTenant->getBranding('brand_title') ?: $currentTenant->name) : config('app.name', 'CRM');
@endphp

<div
    x-data="{
        expanded: false,
        online: navigator.onLine,
        init() {
            window.addEventListener('online', () => this.online = true);
            window.addEventListener('offline', () => this.online = false);
        }
    }"
    @click.outside="expanded = false"
    class="hidden md:flex justify-center pointer-events-none fixed top-2.5 inset-x-0 z-40"
>
    <div
        class="pointer-events-auto transition-all duration-300 ease-out shadow-lg backdrop-blur-xl border {{ $isImpersonating
            ? 'bg-amber-500/95 text-white border-amber-400 shadow-amber-500/25'
            : 'bg-gray-900/90 dark:bg-gray-950/95 text-white border-white/10 shadow-black/20' }}"
        :class="expanded ? 'rounded-3xl px-5 py-3.5 w-80 sm:w-96' : 'rounded-full px-4 py-1.5'"
    >
        {{-- Compact Pill View --}}
        <div class="flex items-center justify-between gap-3 text-xs cursor-pointer select-none"
             @click="expanded = !expanded">
            <div class="flex items-center gap-2 min-w-0">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                          :class="online ? '{{ $isImpersonating ? 'bg-white' : 'bg-emerald-400' }}' : 'bg-rose-400'"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2"
                          :class="online ? '{{ $isImpersonating ? 'bg-white' : 'bg-emerald-500' }}' : 'bg-rose-500'"></span>
                </span>
                <span class="font-bold truncate max-w-[160px]">
                    @if($isImpersonating)
                        در حال مدیریت: {{ $notchTitle }}
                    @else
                        {{ $notchTitle }}
                    @endif
                </span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($isImpersonating)
                    <a href="{{ url('/user/reseller/exit-impersonation') }}"
                       @click.stop
                       class="px-2.5 py-0.5 rounded-full bg-white text-amber-700 font-black text-[10px] hover:bg-amber-50 transition-colors">
                        بازگشت به نمایندگی
                    </a>
                @else
                    <button
                        type="button"
                        @click.stop="$dispatch('open-command-palette')"
                        class="px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-[10px] text-gray-200 transition-colors"
                        title="جستجوی سریع (Ctrl+K)"
                    >
                        Ctrl+K
                    </button>
                @endif
            </div>
        </div>

        {{-- Expanded Dynamic Island Panel --}}
        <div
            x-cloak
            x-show="expanded"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-3 pt-3 border-t border-white/15 space-y-2.5 text-xs"
        >
            <div class="flex items-center justify-between text-[11px] text-white/80">
                <span>وضعیت شبکه:</span>
                <span class="font-bold" x-text="online ? 'متصل (آنلاین)' : 'قطع اتصال اینترنت'"></span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-white/80">
                <span>کاربر فعال:</span>
                <span class="font-bold">{{ auth()->user()?->name }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1">
                <button
                    type="button"
                    @click="expanded = false; $dispatch('open-command-palette')"
                    class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] transition-colors text-center"
                >
                     دستورات
                </button>
                <a
                    href="{{ route('user.dashboard') }}"
                    class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] transition-colors text-center"
                >
                    پیشخوان اصلی
                </a>
            </div>
        </div>
    </div>
</div>
