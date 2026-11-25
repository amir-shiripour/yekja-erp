@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'value' => null,
    'placeholder' => 'انتخاب ساعت...',
    'step' => 5,
    'placement' => 'bottom',
    'clearable' => true,
    'disabled' => false,
])

@php
    $stepVal = max(1, min(30, (int) $step));
    $initialRaw = old($name, $value);
    $hasInitial = !empty($initialRaw) && is_string($initialRaw);
    $initHour = 0;
    $initMinute = 0;
    $initialFormatted = '';

    if ($hasInitial) {
        $parts = explode(':', (string) $initialRaw);
        $initHour = isset($parts[0]) && is_numeric($parts[0]) ? max(0, min(23, (int) $parts[0])) : 0;
        $initMinute = isset($parts[1]) && is_numeric($parts[1]) ? max(0, min(59, (int) $parts[1])) : 0;
        $initialFormatted = sprintf('%02d:%02d', $initHour, $initMinute);
    }
@endphp

<div
    x-data="{
        open: false,
        hour: {{ $initHour }},
        minute: {{ $initMinute }},
        time: @js($initialFormatted),
        init() {
            this.syncFromTime(this.time);
            this.$watch('time', (val) => this.syncFromTime(val));
        },
        syncFromTime(val) {
            if (!val || typeof val !== 'string' || val.trim() === '') {
                return;
            }
            const en = val.replace(/[۰-۹]/g, d => '0123456789'['۰۱۲۳۴۵۶۷۸۹'.indexOf(d)]);
            const m = en.match(/^(\d{1,2}):(\d{1,2})/);
            if (m) {
                const h = Math.max(0, Math.min(23, parseInt(m[1], 10)));
                const min = Math.max(0, Math.min(59, parseInt(m[2], 10)));
                this.hour = h;
                this.minute = min;
                const formatted = String(h).padStart(2, '0') + ':' + String(min).padStart(2, '0');
                if (this.time !== formatted) this.time = formatted;
            }
        },
        updateTime() {
            this.time = String(this.hour).padStart(2, '0') + ':' + String(this.minute).padStart(2, '0');
            this.$dispatch('input', this.time);
            this.$dispatch('change', this.time);
        },
        clearTime() {
            this.time = '';
            this.hour = 0;
            this.minute = 0;
            this.open = false;
            this.$dispatch('input', '');
            this.$dispatch('change', '');
        },
        incHour() {
            this.hour = (this.hour + 1) % 24;
            this.updateTime();
        },
        decHour() {
            this.hour = (this.hour + 23) % 24;
            this.updateTime();
        },
        incMinute() {
            this.minute = (this.minute + {{ $stepVal }}) % 60;
            this.updateTime();
        },
        decMinute() {
            this.minute = (this.minute - {{ $stepVal }} + 60) % 60;
            this.updateTime();
        },
        setHour(h) {
            this.hour = h;
            this.updateTime();
        },
        setMinute(m) {
            this.minute = m;
            this.updateTime();
        },
        setNow() {
            const d = new Date();
            this.hour = d.getHours();
            this.minute = d.getMinutes();
            this.updateTime();
        },
        toFa(num) {
            const str = String(num).padStart(2, '0');
            return str.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        },
        get hasValue() {
            return Boolean(this.time && this.time.trim() !== '');
        },
        get displayFa() {
            return this.hasValue ? (this.toFa(this.hour) + ':' + this.toFa(this.minute)) : @js($placeholder);
        }
    }"
    x-modelable="time"
    {{ $attributes->whereDoesntStartWith('class')->except(['id', 'name', 'value', 'label', 'placeholder', 'step', 'placement', 'clearable', 'disabled']) }}
    class="relative {{ $attributes->get('class') }}"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    @if($label)
        <label @if($id) for="{{ $id }}" @endif class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }}
        </label>
    @endif

    @if($name)
        <input type="hidden" @if($id) id="{{ $id }}" @endif name="{{ $name }}" :value="time">
    @endif

    <div
        role="button"
        tabindex="{{ $disabled ? '-1' : '0' }}"
        @if(!$disabled)
            @click="open = !open"
        @keydown.enter.prevent="open = !open"
        @keydown.space.prevent="open = !open"
        @endif
        class="flex items-center justify-between gap-2.5 w-full h-10 px-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-100 hover:border-indigo-300 dark:hover:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-2xs {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
    >
        <span class="flex items-center gap-2 min-w-0">
            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span :dir="hasValue ? 'ltr' : 'rtl'"
                  :class="hasValue ? 'tracking-wider font-black text-gray-900 dark:text-white' : 'font-medium text-gray-400 dark:text-gray-500 text-xs'"
                  class="truncate"
                  x-text="displayFa"></span>
        </span>

        <span class="flex items-center gap-1.5 shrink-0">
            @if($clearable && !$disabled)
                <button
                    type="button"
                    x-show="hasValue"
                    x-cloak
                    @click.stop="clearTime()"
                    title="پاک کردن ساعت"
                    aria-label="پاک کردن ساعت"
                    class="w-5 h-5 rounded-full inline-flex items-center justify-center text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif

            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                 :class="open ? 'rotate-180 text-indigo-500' : ''"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </span>
    </div>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute z-50 w-72 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl space-y-3.5 start-0 {{ $placement === 'top' ? 'bottom-full mb-2' : 'top-full mt-2' }}"
    >
        <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-gray-700/60">
            <span class="text-xs font-black text-gray-700 dark:text-gray-200">انتخاب ساعت (۲۴ ساعته)</span>
            <div class="flex items-center gap-1.5">
                <button type="button"
                        @click="setNow()"
                        class="text-[11px] font-bold px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors cursor-pointer">
                    اکنون
                </button>
                @if($clearable)
                    <button type="button"
                            x-show="hasValue"
                            @click="clearTime()"
                            class="text-[11px] font-bold px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer">
                        پاک کردن
                    </button>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-center gap-3 py-1" dir="ltr">
            <div class="flex flex-col items-center gap-1">
                <button type="button" @click="incHour()" aria-label="افزایش ساعت"
                        class="w-9 h-7 rounded-lg bg-gray-50 dark:bg-gray-900/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                    </svg>
                </button>
                <div
                    class="w-12 h-11 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center text-lg font-black text-indigo-700 dark:text-indigo-300 select-none"
                    x-text="toFa(hour)"></div>
                <button type="button" @click="decHour()" aria-label="کاهش ساعت"
                        class="w-9 h-7 rounded-lg bg-gray-50 dark:bg-gray-900/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <span class="text-xl font-black text-gray-400 dark:text-gray-500 pb-1">:</span>

            <div class="flex flex-col items-center gap-1">
                <button type="button" @click="incMinute()" aria-label="افزایش دقیقه"
                        class="w-9 h-7 rounded-lg bg-gray-50 dark:bg-gray-900/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                    </svg>
                </button>
                <div
                    class="w-12 h-11 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center text-lg font-black text-indigo-700 dark:text-indigo-300 select-none"
                    x-text="toFa(minute)"></div>
                <button type="button" @click="decMinute()" aria-label="کاهش دقیقه"
                        class="w-9 h-7 rounded-lg bg-gray-50 dark:bg-gray-900/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/60" dir="ltr">
            <div class="space-y-1">
                <div class="text-[10px] font-bold text-gray-400 text-center">ساعت</div>
                <div class="max-h-32 overflow-y-auto space-y-0.5 pe-1 custom-scrollbar">
                    @for($h = 0; $h < 24; $h++)
                        <button type="button"
                                @click="setHour({{ $h }})"
                                :class="hasValue && hour === {{ $h }} ? 'bg-indigo-600 text-white font-black shadow-2xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 font-bold'"
                                class="w-full py-1 rounded-lg text-xs text-center transition-colors cursor-pointer">
                            <span x-text="toFa({{ $h }})"></span>
                        </button>
                    @endfor
                </div>
            </div>

            <div class="space-y-1">
                <div class="text-[10px] font-bold text-gray-400 text-center">دقیقه</div>
                <div class="max-h-32 overflow-y-auto space-y-0.5 pe-1 custom-scrollbar">
                    @for($m = 0; $m < 60; $m += $stepVal)
                        <button type="button"
                                @click="setMinute({{ $m }})"
                                :class="hasValue && minute === {{ $m }} ? 'bg-indigo-600 text-white font-black shadow-2xs' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 font-bold'"
                                class="w-full py-1 rounded-lg text-xs text-center transition-colors cursor-pointer">
                            <span x-text="toFa({{ $m }})"></span>
                        </button>
                    @endfor
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center gap-2">
            <button type="button"
                    @click="if (!hasValue) updateTime(); open = false"
                    class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-xs transition-colors cursor-pointer">
                تایید ساعت
            </button>
        </div>
    </div>
</div>
