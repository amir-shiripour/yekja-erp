@props([
    'text' => '',
    'label' => null,
    'size' => 'sm',
])

@php
    $btnSizes = match($size) {
        'md' => 'px-3 py-1.5 text-xs gap-1.5',
        'lg' => 'px-4 py-2 text-sm gap-2',
        default => 'p-1.5 text-xs gap-1',
    };
    $iconSizes = match($size) {
        'lg' => 'w-5 h-5',
        default => 'w-4 h-4',
    };
@endphp

<button
    type="button"
    x-data="{
        copied: false,
        timer: null,
        copy() {
            const val = @js((string) $text);
            if (!val) return;

            const finish = () => {
                this.copied = true;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.copied = false, 1800);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(val).then(finish).catch(() => this.fallbackCopy(val, finish));
            } else {
                this.fallbackCopy(val, finish);
            }
        },
        fallbackCopy(val, cb) {
            const ta = document.createElement('textarea');
            ta.value = val;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { document.execCommand('copy'); cb(); } catch (e) {}
            document.body.removeChild(ta);
        }
    }"
    @click.stop="copy()"
    :title="copied ? 'کپی شد!' : 'کپی در حافظه'"
    {{ $attributes->merge([
        'class' => "inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 dark:hover:border-indigo-800 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 active:scale-95 transition-all duration-200 select-none cursor-pointer {$btnSizes}"
    ]) }}
    :class="copied ? '!border-emerald-300 dark:!border-emerald-700 !bg-emerald-50 dark:!bg-emerald-950/40 !text-emerald-600 dark:!text-emerald-400' : ''"
>
    <span class="relative inline-flex items-center justify-center {{ $iconSizes }}">
        {{-- Copy Icon --}}
        <svg
            x-show="!copied"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="{{ $iconSizes }}"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>

        {{-- Checkmark Icon --}}
        <svg
            x-cloak
            x-show="copied"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-50 -rotate-45"
            x-transition:enter-end="opacity-100 scale-100 rotate-0"
            class="{{ $iconSizes }} text-emerald-600 dark:text-emerald-400"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </span>

    @if($label)
        <span class="font-bold" x-text="copied ? 'کپی شد!' : @js($label)">{{ $label }}</span>
    @endif
</button>
