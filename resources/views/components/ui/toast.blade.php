@props([
    'position' => 'top-center', // top-center | top-left | top-right | bottom-left | bottom-right
])

@php
    $posClasses = match($position) {
        'top-center'   => 'top-4 left-1/2 -translate-x-1/2 items-center',
        'top-left'     => 'top-4 left-4 items-start',
        'top-right'    => 'top-4 right-4 items-end',
        'bottom-right' => 'bottom-4 right-4 items-end',
        'bottom-center'=> 'bottom-4 left-1/2 -translate-x-1/2 items-center',
        default        => 'top-4 left-1/2 -translate-x-1/2 items-center',
    };
@endphp

{{-- VibeFarsi Official Toast System (Exact 1:1 circular icon pill, popover surface, and typography) --}}
<div
    x-data="{
        toasts: [],
        init() {
            window.toast = {
                success: (message, title = 'موفقیت‌آمیز', duration = 3500) => {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message, title, duration } }));
                },
                error: (message, title = 'خطا', duration = 4500) => {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message, title, duration } }));
                },
                warning: (message, title = 'هشدار', duration = 4000) => {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', message, title, duration } }));
                },
                info: (message, title = 'اطلاع', duration = 3500) => {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message, title, duration } }));
                }
            };
        },
        add(detail) {
            const d = (typeof detail === 'string') ? { message: detail } : (detail || {});
            const id = Date.now() + Math.random();
            const duration = d.duration || 3800;
            const rawType = String(d.type || 'info').toLowerCase();
            const type = (rawType === 'danger' || rawType === 'destructive') ? 'error' : rawType;

            this.toasts.unshift({
                id,
                type,
                title: d.title || '',
                message: d.message || d.text || '',
                duration,
            });

            setTimeout(() => this.remove(id), duration);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        dismissAll() {
            this.toasts = [];
        }
    }"
    @toast.window="add($event.detail)"
    @notify.window="add($event.detail)"
    class="fixed {{ $posClasses }} z-[9999] flex flex-col gap-2 w-[min(360px,calc(100vw-2rem))] pointer-events-none select-none"
    role="region"
    aria-label="اعلان‌ها"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            class="pointer-events-auto relative flex items-start gap-3 rounded-2xl border border-gray-200/90 dark:border-gray-800 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl px-3.5 py-3 text-gray-900 dark:text-gray-100 shadow-xl w-full"
            style="box-shadow: 0 12px 32px -4px rgba(0,0,0,0.16);"
            role="status"
        >
            {{-- VibeFarsi Icon Badge (size-5 rounded-full with size-3 icon) --}}
            <span
                class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-emerald-600 text-white': t.type === 'success',
                    'bg-rose-600 text-white': t.type === 'error',
                    'bg-amber-500 text-white': t.type === 'warning',
                    'bg-gray-900 text-white dark:bg-white dark:text-gray-900': !['success','error','warning'].includes(t.type)
                }"
            >
                <template x-if="t.type === 'success'">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </template>
                <template x-if="t.type === 'error'">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="18" x2="18" y2="6"/>
                    </svg>
                </template>
                <template x-if="t.type === 'warning'">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </template>
                <template x-if="!['success','error','warning'].includes(t.type)">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                </template>
            </span>

            {{-- Text Body --}}
            <div class="min-w-0 flex-1 text-xs">
                <template x-if="t.title">
                    <p class="font-bold leading-5 text-gray-900 dark:text-white" x-text="t.title"></p>
                </template>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed" x-text="t.message"></p>
            </div>

            {{-- Close Button --}}
            <button
                type="button"
                @click="remove(t.id)"
                class="-me-1 -mt-0.5 p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg transition-colors shrink-0 cursor-pointer"
                title="بستن"
            >
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </template>
</div>
