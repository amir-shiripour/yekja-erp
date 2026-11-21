@props([
    'label' => 'کد ملی',
    'name' => null,
    'value' => '',
    'placeholder' => 'کد ملی ۱۰ رقمی',
])

{{-- VibeFarsi National ID Input with Persian Digits Normalization and Luhn Validation --}}
<div
    x-data="{
        nid: @js((string) $value),
        status: 'idle', // idle | valid | invalid
        init() {
            this.nid = this.toEnDigits(String(this.nid || '')).replace(/\D/g, '').slice(0, 10);
            this.validate();
            this.$watch('nid', () => this.validate());
        },
        toEnDigits(str) {
            const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            for (let i = 0; i < 10; i++) {
                str = str.replaceAll(fa[i], i).replaceAll(ar[i], i);
            }
            return str;
        },
        onInput(e) {
            this.nid = this.toEnDigits(e.target.value).replace(/\D/g, '').slice(0, 10);
            e.target.value = this.nid;
            this.validate();
        },
        validate() {
            if (!this.nid || this.nid.length < 10) {
                this.status = 'idle';
                return;
            }
            if (/^(\d)\1{9}$/.test(this.nid)) {
                this.status = 'invalid';
                return;
            }
            const check = parseInt(this.nid[9], 10);
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                sum += parseInt(this.nid[i], 10) * (10 - i);
            }
            const rem = sum % 11;
            const isValid = (rem < 2 && check === rem) || (rem >= 2 && check === (11 - rem));
            this.status = isValid ? 'valid' : 'invalid';
        }
    }"
    x-modelable="nid"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'value', 'placeholder']) }}
    class="space-y-1.5 {{ $attributes->get('class') }}"
>
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="nid">
    @endif

    <div class="relative flex items-center">
        <input
            type="text"
            inputmode="numeric"
            dir="ltr"
            maxlength="10"
            :value="nid"
            @input="onInput($event)"
            placeholder="{{ $placeholder }}"
            :class="{
                'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500/20': status === 'valid',
                'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20': status === 'invalid',
                'border-gray-200 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/20': status === 'idle'
            }"
            class="w-full px-4 py-2.5 pl-11 bg-gray-50/80 dark:bg-gray-900/80 border rounded-2xl text-xs font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 transition-all text-left shadow-2xs"
        >

        {{-- Icon container on the left (ID card icon in idle state, Validation icons when evaluated) --}}
        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
            {{-- Default National ID Card Icon --}}
            <svg x-show="status === 'idle'" class="size-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="14" x="3" y="5" rx="2"/>
                <circle cx="9" cy="11" r="2"/>
                <path d="M15 9h2M15 13h2M7 16h10"/>
            </svg>

            {{-- Valid Checkmark --}}
            <svg x-cloak x-show="status === 'valid'" class="size-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>

            {{-- Invalid Cross --}}
            <svg x-cloak x-show="status === 'invalid'" class="size-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="18" x2="18" y2="6" />
            </svg>
        </span>
    </div>

    <p x-cloak x-show="status === 'invalid'" class="text-[11px] font-bold text-rose-600 dark:text-rose-400">
        کد ملی وارد شده از نظر الگوریتم ده رقمی معتبر نیست.
    </p>
</div>
