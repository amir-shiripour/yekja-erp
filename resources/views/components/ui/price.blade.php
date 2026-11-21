@props([
    'amount' => 0,
    'original' => null,
    'originalAmount' => null,
    'unit' => null,
    'currency' => 'تومان',
    'freeLabel' => 'رایگان',
    'size' => 'md', // sm | md | lg
])

@php
    $num = (float) $amount;
    $origVal = $original ?? $originalAmount;
    $orig = $origVal !== null ? (float) $origVal : null;
    $unitLabel = $unit ?? $currency;
    $hasDiscount = $orig !== null && $orig > $num && $orig > 0;
    $discountPercent = $hasDiscount ? (int) round((($orig - $num) / $orig) * 100) : 0;

    $textSizes = match($size) {
        'sm' => 'text-xs',
        'lg' => 'text-lg sm:text-2xl',
        default => 'text-sm sm:text-base',
    };
@endphp

{{-- VibeFarsi Price Component (RTL with Persian numerals, discount pill and currency unit) --}}
<div {{ $attributes->merge(['class' => 'inline-flex flex-col items-start gap-0.5 select-none']) }}>
    @if($hasDiscount)
        <div class="flex items-center gap-1.5">
            <span class="text-[11px] text-gray-400 dark:text-gray-500 line-through tabular-nums" dir="ltr">
                {{ number_format($orig) }}
            </span>
            <span class="px-1.5 py-px rounded-full bg-rose-600 text-white text-[10px] font-bold">
                {{ $discountPercent }}٪ تخفیف
            </span>
        </div>
    @endif

    <div class="inline-flex items-baseline gap-1 font-bold text-gray-900 dark:text-white {{ $textSizes }}">
        @if($num <= 0 && $freeLabel)
            <span class="text-emerald-600 dark:text-emerald-400">{{ $freeLabel }}</span>
        @else
            <span class="tracking-tight tabular-nums" dir="ltr">{{ number_format($num) }}</span>
            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $unitLabel }}</span>
        @endif
    </div>
</div>
