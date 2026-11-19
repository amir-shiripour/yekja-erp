@props([
    'variant' => 'indigo',
    'dot' => false,
    'pulse' => false,
    'size' => 'sm',
])

@php
    $colors = match($variant) {
        'emerald', 'success' => [
            'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
            'dot'  => 'bg-emerald-500',
        ],
        'amber', 'warning' => [
            'pill' => 'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
            'dot'  => 'bg-amber-500',
        ],
        'rose', 'danger', 'error' => [
            'pill' => 'bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
            'dot'  => 'bg-rose-500',
        ],
        'sky', 'info' => [
            'pill' => 'bg-sky-50 text-sky-700 border-sky-200/80 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800/60',
            'dot'  => 'bg-sky-500',
        ],
        'purple' => [
            'pill' => 'bg-purple-50 text-purple-700 border-purple-200/80 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/60',
            'dot'  => 'bg-purple-500',
        ],
        'slate', 'gray', 'neutral' => [
            'pill' => 'bg-gray-100 text-gray-700 border-gray-200/80 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
            'dot'  => 'bg-gray-500',
        ],
        default => [
            'pill' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/60',
            'dot'  => 'bg-indigo-500',
        ],
    };

    $sizes = match($size) {
        'md' => 'px-3 py-1 text-xs gap-1.5',
        default => 'px-2.5 py-0.5 text-[11px] gap-1.5',
    };
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center font-bold rounded-xl border {$colors['pill']} {$sizes}"
]) }}>
    @if($dot)
        <span class="relative flex h-2 w-2 shrink-0">
            @if($pulse)
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $colors['dot'] }}"></span>
            @endif
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $colors['dot'] }}"></span>
        </span>
    @endif
    <span>{{ $slot }}</span>
</span>
