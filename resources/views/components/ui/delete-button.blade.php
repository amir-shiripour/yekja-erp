@props([
    'label' => null,
    'confirmLabel' => 'تایید حذف؟',
    'timeout' => 3000,
    'size' => 'sm',
])

@php
    $sizeClasses = match($size) {
        'md' => 'px-3 py-1.5 text-xs gap-1.5',
        'lg' => 'px-4 py-2 text-sm gap-2',
        default => 'px-2.5 py-1.5 text-xs gap-1.5',
    };
@endphp

<button
    type="button"
    x-data="{
        confirming: false,
        timer: null,
        handleClick(e) {
            if (!this.confirming) {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.confirming = true;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.confirming = false;
                }, {{ (int) $timeout }});
            } else {
                clearTimeout(this.timer);
                this.confirming = false;
            }
        }
    }"
    @click.capture="handleClick($event)"
    @click.outside="confirming = false; clearTimeout(timer)"
    :class="confirming
        ? '!bg-rose-600 !text-white !border-rose-600 shadow-md shadow-rose-600/25 ring-2 ring-rose-500/30 animate-pulse'
        : 'bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 border-gray-200 dark:border-gray-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 hover:border-rose-200 dark:hover:border-rose-800'"
    {{ $attributes->merge([
        'class' => "inline-flex items-center justify-center rounded-xl border font-bold transition-all duration-200 active:scale-95 select-none cursor-pointer {$sizeClasses}"
    ]) }}
>
    {{-- Trash Icon --}}
    <svg x-show="!confirming" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>

    {{-- Warning Icon --}}
    <svg x-cloak x-show="confirming" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>

    @if($label)
        <span x-text="confirming ? @js($confirmLabel) : @js($label)">{{ $label }}</span>
    @else
        <span x-cloak x-show="confirming">{{ $confirmLabel }}</span>
    @endif
</button>
