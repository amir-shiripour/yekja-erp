@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $styles = match($type) {
        'success' => [
            'wrap' => 'bg-emerald-50/90 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300',
            'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        'warning' => [
            'wrap' => 'bg-amber-50/90 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300',
            'iconBg' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        ],
        'error', 'danger' => [
            'wrap' => 'bg-rose-50/90 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300',
            'iconBg' => 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        default => [
            'wrap' => 'bg-indigo-50/90 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800/60 text-indigo-800 dark:text-indigo-300',
            'iconBg' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    role="alert"
    {{ $attributes->merge(['class' => "relative flex items-start gap-3 p-4 rounded-2xl border text-xs leading-relaxed shadow-xs {$styles['wrap']}"]) }}
>
    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $styles['iconBg'] }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            {!! $styles['icon'] !!}
        </svg>
    </div>

    <div class="flex-1 min-w-0 pt-0.5">
        @if($title)
            <h4 class="font-black text-xs mb-1">{{ $title }}</h4>
        @endif
        <div class="font-medium opacity-95">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            @click="show = false"
            class="p-1 rounded-lg opacity-60 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition-all shrink-0 cursor-pointer"
            aria-label="بستن"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
