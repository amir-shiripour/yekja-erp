@props([
    'position' => 'bottom-left', // bottom-left | bottom-right | top-left | top-right
])

@php
    $posClasses = match($position) {
        'bottom-right' => 'bottom-5 right-5',
        'top-left'     => 'top-5 left-5',
        'top-right'    => 'top-5 right-5',
        default        => 'bottom-5 left-5',
    };
@endphp

<div
    x-data="{
        toasts: [],
        add(detail) {
            const d = (typeof detail === 'string') ? { message: detail } : (detail || {});
            const id = Date.now() + Math.random();
            const duration = d.duration || 4500;
            const rawType = String(d.type || 'info').toLowerCase();
            const type = rawType === 'danger' ? 'error' : rawType;

            this.toasts.push({
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
        }
    }"
    @toast.window="add($event.detail)"
    @notify.window="add($event.detail)"
    class="fixed {{ $posClasses }} z-[9998] flex flex-col gap-2.5 w-80 sm:w-96 max-w-[calc(100vw-2.5rem)] pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto relative overflow-hidden bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700/80 rounded-2xl shadow-2xl p-3.5 flex items-start gap-3 text-xs"
        >
            {{-- Status Icon --}}
            <div
                :class="{
                    'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400': toast.type === 'success',
                    'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400': toast.type === 'error',
                    'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400': toast.type === 'warning',
                    'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400': !['success','error','warning'].includes(toast.type)
                }"
                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
            >
                <svg x-show="toast.type === 'success'" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <svg x-show="toast.type === 'warning'" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <svg x-show="!['success','error','warning'].includes(toast.type)" class="w-4 h-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            {{-- Message --}}
            <div class="flex-1 min-w-0 pt-0.5">
                <p x-show="toast.title" class="font-black text-gray-900 dark:text-white mb-0.5"
                   x-text="toast.title"></p>
                <p class="font-medium text-gray-600 dark:text-gray-300 leading-relaxed" x-text="toast.message"></p>
            </div>

            {{-- Close --}}
            <button
                type="button"
                @click="remove(toast.id)"
                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition-colors shrink-0 cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
