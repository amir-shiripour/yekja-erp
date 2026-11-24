@props([
    'label' => null,
    'confirmLabel' => 'تایید',
    'cancelLabel' => 'انصراف',
    'pendingLabel' => 'در حال ثبت...',
    'doneLabel' => 'انجام شد',
    'timeout' => 4000,
    'size' => 'sm',
])

@php
    $shellSizes = match($size) {
        'lg' => 'h-11 rounded-[22px] p-1 gap-1.5',
        'md' => 'h-10 rounded-[20px] p-1 gap-1',
        'base' => 'h-9 rounded-full p-1 gap-1.5',
        default => 'h-8 rounded-full p-0.5 gap-1',
    };

    $tileSizes = match($size) {
        'lg' => 'size-9',
        'md' => 'size-8',
        'base' => 'size-7',
        default => 'size-7',
    };

    $actionSizes = match($size) {
        'lg' => 'size-8',
        'md' => 'size-7',
        'base' => 'size-6',
        default => 'size-6',
    };

    $iconSizes = match($size) {
        'lg' => 'size-5',
        'md' => 'size-4.5',
        'base' => 'size-4',
        default => 'size-3.5',
    };
@endphp

{{-- Devigner UI Official CompleteButton (Exact same two-stage interactive mechanism as DeleteButton with emerald theme) --}}
<div
    x-data="{
        phase: 'idle', // idle | armed | pending | done
        timer: null,
        arm() {
            if (this.phase !== 'idle') return;
            this.phase = 'armed';
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.cancel(), {{ (int) $timeout }});
        },
        cancel() {
            clearTimeout(this.timer);
            this.phase = 'idle';
        },
        confirmComplete($event) {
            clearTimeout(this.timer);
            this.phase = 'pending';

            window.toast?.success('در حال ثبت تغییر وضعیت به انجام‌شده...', 'تکمیل وظیفه');

            const form = $el.closest('form');
            const hasWireClick = $el.getAttribute('wire:click') || $el.closest('[wire\\:click]');

            if (form && !hasWireClick) {
                setTimeout(() => {
                    this.phase = 'done';
                    setTimeout(() => form.submit(), 250);
                }, 300);
            } else if (hasWireClick) {
                setTimeout(() => {
                    this.phase = 'done';
                    setTimeout(() => this.phase = 'idle', 1400);
                }, 800);
            } else {
                this.$dispatch('completed');
                setTimeout(() => {
                    this.phase = 'done';
                    setTimeout(() => this.phase = 'idle', 1400);
                }, 400);
            }
        }
    }"
    @click.outside="cancel()"
    @keydown.escape.window="cancel()"
    class="relative inline-flex items-center select-none"
>
    {{-- Outer Shell Container matching Devigner UI motion.div --}}
    <div
        class="relative inline-flex w-fit items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs transition-all duration-200 {{ $shellSizes }}"
        :class="{
            'border-emerald-300 dark:border-emerald-800 ring-2 ring-emerald-500/10 shadow-sm': phase === 'armed',
            'border-emerald-400 dark:border-emerald-700': phase === 'done'
        }"
    >
        {{-- Main Tile Button --}}
        <button
            type="button"
            @click.stop="phase === 'idle' ? arm() : cancel()"
            :title="phase === 'armed' ? '{{ $cancelLabel }}' : 'علامت‌گذاری به عنوان انجام شده'"
            aria-label="علامت‌گذاری به عنوان انجام شده"
            class="relative grid shrink-0 cursor-pointer place-items-center rounded-full transition-all duration-200 active:scale-90 {{ $tileSizes }}"
            :class="{
                'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/40': phase === 'idle',
                'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60': phase === 'armed',
                'text-emerald-500': phase === 'done'
            }"
        >
            {{-- Checkmark Icon --}}
            <svg
                x-show="phase === 'idle' || phase === 'armed'"
                class="{{ $iconSizes }} transition-transform duration-200"
                :class="phase === 'armed' ? 'scale-110 text-emerald-600' : ''"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M5 13l4 4L19 7"/>
            </svg>

            {{-- Pending State Spinner --}}
            <svg
                x-cloak
                x-show="phase === 'pending'"
                class="{{ $iconSizes }} animate-spin text-emerald-600"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>

            {{-- Done State Checkmark --}}
            <svg
                x-cloak
                x-show="phase === 'done'"
                class="{{ $iconSizes }} text-emerald-500"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M5.5 12.6 9.7 17 18.5 7"/>
            </svg>
        </button>

        {{-- Confirmation Pill beside the tile --}}
        <div
            x-cloak
            x-show="phase === 'armed'"
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250"
            x-transition:enter-start="opacity-0 scale-90 -translate-x-1"
            x-transition:enter-end="opacity-100 scale-100 translate-x-0"
            x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-x-0"
            x-transition:leave-end="opacity-0 scale-90 -translate-x-1"
            class="relative flex items-center gap-1 bg-gray-100 dark:bg-gray-700/80 px-1.5 py-0.5 rounded-full"
        >
            {{-- Little pointer triangle facing the tile --}}
            <span
                aria-hidden="true"
                class="pointer-events-none absolute top-1/2 -right-1 h-2.5 w-1.5 -translate-y-1/2 bg-gray-100 dark:bg-gray-700/80 [clip-path:polygon(100%_0,0_50%,100%_100%)]"
            ></span>

            {{-- Confirm Action Button (Green checkmark circle) --}}
            <button
                type="button"
                @click.stop="confirmComplete($event)"
                title="{{ $confirmLabel }}"
                aria-label="{{ $confirmLabel }}"
                class="{{ $actionSizes }} rounded-full bg-white dark:bg-gray-800 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 shadow-xs flex items-center justify-center transition-all duration-150 active:scale-90 cursor-pointer"
            >
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5.5 12.6 9.7 17 18.5 7"/>
                </svg>
            </button>

            {{-- Cancel Action Button (Neutral cross circle) --}}
            <button
                type="button"
                @click.stop="cancel()"
                title="{{ $cancelLabel }}"
                aria-label="{{ $cancelLabel }}"
                class="{{ $actionSizes }} rounded-full bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-xs flex items-center justify-center transition-all duration-150 active:scale-90 cursor-pointer"
            >
                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7.7 7.7 16.3 16.3M16.3 7.7 7.7 16.3"/>
                </svg>
            </button>
        </div>

        @if($label)
            <span class="px-1 text-xs font-bold text-gray-600 dark:text-gray-300">{{ $label }}</span>
        @endif
    </div>
</div>
