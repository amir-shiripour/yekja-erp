@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'showPercent' => true,
    'color' => 'indigo',
    'size' => 'md',
])

@php
    $maxVal = max(1, (float) $max);
    $percent = min(100, max(0, round(((float) $value / $maxVal) * 100)));

    $barColor = match($color) {
        'emerald', 'green' => 'bg-emerald-500',
        'amber', 'yellow'  => 'bg-amber-500',
        'rose', 'red'      => 'bg-rose-500',
        'sky', 'blue'      => 'bg-sky-500',
        default            => 'bg-indigo-600',
    };

    $heightClass = match($size) {
        'sm' => 'h-1.5',
        'lg' => 'h-3.5',
        default => 'h-2.5',
    };
@endphp

<div {{ $attributes->merge(['class' => 'w-full space-y-1.5']) }}>
    @if($label || $showPercent)
        <div class="flex items-center justify-between text-xs">
            @if($label)
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $label }}</span>
            @else
                <span></span>
            @endif
            @if($showPercent)
                <span class="font-mono font-bold text-gray-600 dark:text-gray-400" dir="ltr">{{ $percent }}%</span>
            @endif
        </div>
    @endif

    <div
        role="progressbar"
        aria-valuenow="{{ $percent }}"
        aria-valuemin="0"
        aria-valuemax="100"
        class="w-full {{ $heightClass }} bg-gray-200/80 dark:bg-gray-700/80 rounded-full overflow-hidden"
    >
        <div
            class="h-full {{ $barColor }} rounded-full transition-all duration-500 ease-out"
            style="width: {{ $percent }}%"
        ></div>
    </div>
</div>
