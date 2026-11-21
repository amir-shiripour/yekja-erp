@props([
    'label' => null,
    'name' => null,
    'value' => '#4f46e5',
    'swatches' => [
        '#4f46e5', '#2563eb', '#0ea5e9', '#0d9488', '#10b981',
        '#16a34a', '#d97706', '#ea580c', '#e11d48', '#9333ea',
        '#475569', '#0f172a',
    ],
])

<div
    x-data="{
        open: false,
        color: @js($value),
        init() {
            if (!this.color || !/^#[0-9A-Fa-f]{3,6}$/.test(this.color)) {
                this.color = '#4f46e5';
            }
        },
        selectColor(hex) {
            this.color = hex;
        }
    }"
    x-modelable="color"
    {{ $attributes->whereDoesntStartWith('class')->except(['name', 'value', 'label', 'swatches']) }}
    class="relative {{ $attributes->get('class') }}"
    @click.outside="open = false"
>
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $label }}</label>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="color">
    @endif

    {{-- Trigger Bar --}}
    <div class="flex items-center gap-2">
        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-2.5 w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-700 dark:text-gray-200 hover:border-indigo-300 dark:hover:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all cursor-pointer"
        >
            <span
                class="w-6 h-6 rounded-lg border border-black/10 dark:border-white/10 shadow-xs shrink-0 transition-colors"
                :style="{ backgroundColor: color }"
            ></span>
            <span dir="ltr" class="font-bold tracking-wider uppercase flex-1 text-start" x-text="color"></span>
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    {{-- Popover Panel --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute z-50 mt-2 w-72 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl space-y-4 start-0"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-700 dark:text-gray-200">پالت رنگ پیشنهادی</span>
            <span class="text-[11px] text-gray-400 uppercase font-bold" dir="ltr" x-text="color"></span>
        </div>

        {{-- Swatches Grid --}}
        <div class="grid grid-cols-6 gap-2">
            @foreach($swatches as $swatch)
                <button
                    type="button"
                    @click="selectColor('{{ $swatch }}')"
                    class="w-8 h-8 rounded-xl border border-black/10 dark:border-white/10 transition-transform hover:scale-110 active:scale-95 flex items-center justify-center cursor-pointer"
                    style="background-color: {{ $swatch }}"
                    :class="color.toLowerCase() === '{{ strtolower($swatch) }}' ? 'ring-2 ring-offset-2 ring-indigo-500 dark:ring-offset-gray-800 scale-105' : ''"
                >
                    <svg x-show="color.toLowerCase() === '{{ strtolower($swatch) }}'"
                         class="w-4 h-4 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            @endforeach
        </div>

        {{-- Custom Hex & Native Picker --}}
        <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2">
            <input
                type="color"
                x-model="color"
                class="w-9 h-9 p-0.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-transparent cursor-pointer shrink-0"
            >
            <input
                type="text"
                dir="ltr"
                x-model.lazy="color"
                maxlength="7"
                placeholder="#4F46E5"
                class="w-full px-3 py-2 text-xs uppercase bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-800 dark:text-gray-200 focus:outline-none focus:border-indigo-500 font-bold"
            >
        </div>
    </div>
</div>
