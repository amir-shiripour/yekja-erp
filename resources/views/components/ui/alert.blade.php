@props([
    'variant' => 'info',
    'type' => null,
    'title' => null,
    'dismissible' => false,
])

@php
    $resolvedVariant = $type ?? $variant;

    $styles = match($resolvedVariant) {
        'success' => [
            'box' => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-950 dark:text-emerald-100',
            'titleColor' => 'text-emerald-700 dark:text-emerald-400',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        'warning' => [
            'box' => 'border-amber-500/25 bg-amber-500/10 text-amber-950 dark:text-amber-100',
            'titleColor' => 'text-amber-700 dark:text-amber-400',
            'iconColor' => 'text-amber-600 dark:text-amber-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        ],
        'error', 'destructive', 'danger' => [
            'box' => 'border-rose-500/30 bg-rose-500/10 text-rose-950 dark:text-rose-100',
            'titleColor' => 'text-rose-700 dark:text-rose-400',
            'iconColor' => 'text-rose-600 dark:text-rose-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        default => [
            'box' => 'border-gray-200/90 bg-white/90 dark:border-gray-700 dark:bg-gray-800/80 text-gray-900 dark:text-gray-100',
            'titleColor' => 'text-gray-900 dark:text-white',
            'iconColor' => 'text-gray-700 dark:text-gray-300',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
    };
@endphp

{{-- VibeFarsi Official Alert Component (Minimal inline icon, delicate 10% tint background and 25-30% border) --}}
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    role="{{ in_array($resolvedVariant, ['error', 'destructive', 'danger']) ? 'alert' : 'status' }}"
    {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-2xl border p-4 text-xs leading-relaxed backdrop-blur-xs {$styles['box']}"]) }}
>
    {{-- Inline Icon matching VibeFarsi (mt-0.5 size-4 shrink-0) --}}
    <svg class="mt-0.5 size-4 shrink-0 {{ $styles['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        {!! $styles['icon'] !!}
    </svg>

    <div class="flex-1 min-w-0">
        @if($title)
            <p class="font-bold text-xs mb-0.5 {{ $styles['titleColor'] }}">{{ $title }}</p>
        @endif
        <div class="opacity-90 leading-relaxed font-normal">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            @click="show = false"
            class="-me-1 -mt-1 p-1 rounded-lg opacity-60 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition-all shrink-0 cursor-pointer"
            aria-label="بستن"
        >
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
