@props([
    'label' => 'رمز عبور',
    'name' => null,
    'value' => '',
    'placeholder' => '••••••••',
    'showStrength' => false,
])

<div
    x-data="{
        show: false,
        capsOn: false,
        val: @js((string) $value),
        get score() {
            let s = 0;
            if (!this.val) return 0;
            if (this.val.length >= 6) s++;
            if (this.val.length >= 10) s++;
            if (/[A-Z]/.test(this.val) && /[a-z]/.test(this.val)) s++;
            if (/\d/.test(this.val)) s++;
            if (/[^A-Za-z0-9]/.test(this.val)) s++;
            return Math.min(4, s);
        },
        get strengthMeta() {
            const map = [
                { label: '', color: 'bg-gray-200', text: 'text-gray-400', w: '0%' },
                { label: 'ضعیف', color: 'bg-rose-500', text: 'text-rose-500', w: '25%' },
                { label: 'متوسط', color: 'bg-amber-500', text: 'text-amber-500', w: '50%' },
                { label: 'قوی', color: 'bg-sky-500', text: 'text-sky-500', w: '75%' },
                { label: 'بسیار قوی', color: 'bg-emerald-500', text: 'text-emerald-500', w: '100%' },
            ];
            return map[this.score] || map[0];
        }
    }"
    x-modelable="val"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'value', 'placeholder', 'showStrength']) }}
    class="space-y-1.5 {{ $attributes->get('class') }}"
>
    @if($label)
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ $label }}</label>
            <span x-cloak x-show="capsOn" class="text-[10px] font-bold text-amber-600 dark:text-amber-400">
                Caps Lock روشن است
            </span>
        </div>
    @endif

    <div class="relative flex items-center">
        <input
            :type="show ? 'text' : 'password'"
            dir="ltr"
            x-model="val"
            @if($name) name="{{ $name }}" @endif
            @keydown="capsOn = $event.getModifierState && $event.getModifierState('CapsLock')"
            @keyup="capsOn = $event.getModifierState && $event.getModifierState('CapsLock')"
            placeholder="{{ $placeholder }}"
            class="w-full px-4 py-2.5 pl-11 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-left"
        >

        <button
            type="button"
            @click="show = !show"
            tabindex="-1"
            class="absolute left-2.5 p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer"
            :title="show ? 'مخفی کردن رمز' : 'نمایش رمز'"
        >
            {{-- Eye Open --}}
            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            {{-- Eye Closed --}}
            <svg x-cloak x-show="show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
        </button>
    </div>

    @if($showStrength)
        <div x-cloak x-show="val.length > 0" class="space-y-1 pt-0.5">
            <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300" :class="strengthMeta.color"
                     :style="{ width: strengthMeta.w }"></div>
            </div>
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-gray-400">قدرت رمز عبور:</span>
                <span class="font-bold" :class="strengthMeta.text" x-text="strengthMeta.label"></span>
            </div>
        </div>
    @endif
</div>
