@props([
    'text' => '',
    'targetId' => null,
    'label' => null,
    'copiedLabel' => 'کپی شد!',
    'iconOnly' => false,
    'size' => 'sm',
])

@php
    $textToCopy = (string) $text;
    $isIconOnly = $iconOnly || empty($label);

    $sizeClasses = match($size) {
        'lg' => $isIconOnly ? 'size-10 rounded-full' : 'px-4 py-2 text-sm rounded-full gap-2',
        'md' => $isIconOnly ? 'size-9 rounded-full' : 'px-3.5 py-1.5 text-xs rounded-full gap-1.5',
        default => $isIconOnly ? 'size-7 sm:size-8 rounded-full' : 'px-3 py-1 text-xs rounded-full gap-1.5',
    };

    $iconSize = match($size) {
        'lg' => 'size-5',
        'md' => 'size-4',
        default => 'size-3.5',
    };
@endphp

{{-- Devigner UI Copy Button (Pill button with morphing checkmark and retyping label) --}}
<button
    type="button"
    x-data="{
        copied: false,
        timer: null,
        targetId: @js($targetId),
        staticText: @js($textToCopy),
        copy() {
            let val = this.staticText;
            if (this.targetId) {
                const el = document.getElementById(this.targetId);
                if (el) val = el.value || el.innerText || '';
            }
            if (!val) {
                if (window.toast) window.toast.warning('مقداری برای کپی کردن وجود ندارد.');
                return;
            }

            this.copied = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.copied = false, 1400);

            if (window.copyToClipboard) {
                window.copyToClipboard(val, 'متن با موفقیت در کلیپ‌بورد کپی شد.');
            } else {
                this.fallback(val);
            }
        },
        fallback(val) {
            const ta = document.createElement('textarea');
            ta.value = val;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                if (window.toast) window.toast.success('متن با موفقیت کپی شد.');
            } catch (e) {}
            document.body.removeChild(ta);
        }
    }"
    @click.stop="copy()"
    :title="copied ? '{{ $copiedLabel }}' : 'کپی در کلیپ‌بورد'"
    aria-label="کپی در حافظه"
    {{ $attributes->merge([
        'class' => "inline-flex items-center justify-center font-bold transition-all duration-300 active:scale-95 cursor-pointer shadow-2xs {$sizeClasses}"
    ]) }}
    :class="copied
        ? '!bg-emerald-600 !text-white !border-emerald-600 shadow-md shadow-emerald-600/30'
        : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 dark:border-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400'"
>
    {{-- Animated Icon (Copy Icon -> Check Icon) --}}
    <span class="relative inline-flex items-center justify-center {{ $iconSize }}">
        {{-- Copy Icon --}}
        <svg
            x-show="!copied"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="{{ $iconSize }}"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>

        {{-- Check Icon (rotates and scales in) --}}
        <svg
            x-cloak
            x-show="copied"
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250"
            x-transition:enter-start="opacity-0 scale-50 -rotate-45"
            x-transition:enter-end="opacity-100 scale-100 rotate-0"
            class="{{ $iconSize }} text-white"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </span>

    @if(!$isIconOnly)
        <span
            class="transition-all duration-200"
            x-text="copied ? '{{ $copiedLabel }}' : '{{ $label }}'"
        >
            {{ $label }}
        </span>
    @endif
</button>
