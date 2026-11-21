@props([
    'label' => null,
    'confirmLabel' => 'تایید',
    'cancelLabel' => 'انصراف',
    'pendingLabel' => 'در حال حذف...',
    'doneLabel' => 'حذف شد',
    'timeout' => 4000,
    'size' => 'sm',
])

@php
    $shellSizes = match($size) {
        'lg' => 'h-11 rounded-[22px] p-1 gap-1.5',
        'md' => 'h-10 rounded-[20px] p-1 gap-1',
        default => 'h-8 rounded-full p-0.5 gap-1',
    };

    $tileSizes = match($size) {
        'lg' => 'size-9',
        'md' => 'size-8',
        default => 'size-7',
    };

    $actionSizes = match($size) {
        'lg' => 'size-8',
        'md' => 'size-7',
        default => 'size-6',
    };

    $iconSizes = match($size) {
        'lg' => 'size-5',
        'md' => 'size-4.5',
        default => 'size-3.5',
    };
@endphp

{{-- Devigner UI Official DeleteButton (Exact 1:1 SVG paths, lid hinge rotation, and confirmation swap) --}}
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
        confirmDelete($event) {
            clearTimeout(this.timer);
            this.phase = 'pending';

            // Dispatch authentic VibeFarsi toast
            window.toast?.warning('در حال پردازش درخواست حذف...', 'حذف داده');

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
            'border-rose-300 dark:border-rose-800 ring-2 ring-rose-500/10 shadow-sm': phase === 'armed',
            'border-emerald-300 dark:border-emerald-800': phase === 'done'
        }"
    >
        {{-- Main Tile Button (Trash Tile / Spinner / Checkmark) --}}
        <button
            type="button"
            @click.stop="phase === 'idle' ? arm() : cancel()"
            :title="phase === 'armed' ? '{{ $cancelLabel }}' : 'حذف'"
            aria-label="حذف"
            class="relative grid shrink-0 cursor-pointer place-items-center rounded-full transition-all duration-200 active:scale-90 {{ $tileSizes }}"
            :class="{
                'text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40': phase === 'idle',
                'text-rose-600 bg-rose-50 dark:bg-rose-950/60': phase === 'armed',
                'text-emerald-500': phase === 'done'
            }"
        >
            {{-- Official Devigner Trash Glyph (exact bezier paths & lid hinge -20.57deg) --}}
            <svg
                x-show="phase === 'idle' || phase === 'armed'"
                class="{{ $iconSizes }} overflow-visible"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                {{-- Trash Bin --}}
                <path
                    d="M18.834 8.5l-.46 6.9c-.177 2.654-.266 3.981-1.13 4.79-.866.81-2.196.81-4.857.81h-.773c-2.661 0-3.992 0-4.857-.81-.865-.809-.953-2.136-1.13-4.79l-.46-6.9"/>
                {{-- Inner Ticks --}}
                <path d="M9.5 11l.5 5M14.5 11l-.5 5"/>
                {{-- Swinging Lid Group with exact LID_HINGE (9.244px 7.147px) and LID_OPEN (-20.57deg) --}}
                <g
                    class="transition-transform duration-250 ease-out origin-[9.244px_7.147px]"
                    :style="phase === 'armed' ? 'transform: rotate(-20.57deg) translate(-2px, -1.25px);' : 'transform: none;'"
                >
                    <path d="M3.5 6h17"/>
                    <path
                        d="M6.5 6h.11a2 2 0 0 0 1.83-1.32l.034-.103.097-.291c.083-.249.125-.373.18-.479a1.5 1.5 0 0 1 1.094-.788C9.962 3 10.093 3 10.355 3h3.29c.262 0 .393 0 .51.019a1.5 1.5 0 0 1 1.094.788c.055.106.097.23.18.479l.097.291A2 2 0 0 0 17.5 6"/>
                </g>
            </svg>

            {{-- Pending State Spinner --}}
            <svg
                x-cloak
                x-show="phase === 'pending'"
                class="{{ $iconSizes }} animate-spin text-rose-600"
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

        {{-- Confirmation Pill beside the tile (Devigner UI AnimatePresence) --}}
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

            {{-- Confirm Action Button (Red checkmark circle) --}}
            <button
                type="button"
                @click.stop="confirmDelete($event)"
                title="{{ $confirmLabel }}"
                aria-label="{{ $confirmLabel }}"
                class="{{ $actionSizes }} rounded-full bg-white dark:bg-gray-800 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 shadow-xs flex items-center justify-center transition-all duration-150 active:scale-90 cursor-pointer"
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
