@props([
    'amount' => 0,
    'originalAmount' => null,
    'currency' => 'تومان',
    'freeLabel' => 'رایگان',
    'size' => 'md', // sm | md | lg
])

@php
    $num = (float) $amount;
    $orig = $originalAmount !== null ? (float) $originalAmount : null;
    $hasDiscount = $orig !== null && $orig > $num && $orig > 0;
    $discountPercent = $hasDiscount ? (int) round((($orig - $num) / $orig) * 100) : 0;

    $textSizes = match($size) {
        'sm' => 'text-xs',
        'lg' => 'text-lg sm:text-xl',
        default => 'text-sm sm:text-base',
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex flex-col items-start gap-0.5']) }}>
    @if($hasDiscount)
        <div class="flex items-center gap-1.5">
            <span class="text-[11px] font-mono text-gray-400 line-through" dir="ltr">
                {{ number_format($orig) }}
            </span>
            <span class="px-1.5 py-0.5 rounded-md bg-rose-500 text-white text-[10px] font-black">
                {{ $discountPercent }}%
            </span>
        </div>
    @endif

    <div class="inline-flex items-baseline gap-1 font-black text-gray-900 dark:text-white {{ $textSizes }}">
        @if($num <= 0 && $freeLabel)
            <span class="text-emerald-600 dark:text-emerald-400">{{ $freeLabel }}</span>
        @else
            <span class="font-mono tracking-tight" dir="ltr">{{ number_format($num) }}</span>
            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">{{ $currency }}</span>
        @endif
    </div>
</div>
