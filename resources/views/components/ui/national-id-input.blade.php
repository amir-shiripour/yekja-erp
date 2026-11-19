@props([
    'label' => 'کد ملی',
    'name' => null,
    'value' => '',
    'placeholder' => 'کد ملی ۱۰ رقمی',
])

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
                'border-emerald-400 dark:border-emerald-600 focus:border-emerald-500 focus:ring-emerald-500/20': status === 'valid',
                'border-rose-400 dark:border-rose-600 focus:border-rose-500 focus:ring-rose-500/20': status === 'invalid',
                'border-gray-200 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/20': status === 'idle'
            }"
            class="w-full px-4 py-2.5 pl-10 bg-white dark:bg-gray-900 border rounded-xl text-sm font-mono font-bold tracking-widest text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 transition-all text-left"
        >

        {{-- Live Validation Icon --}}
        <span class="absolute left-3 flex items-center pointer-events-none">
            <svg x-cloak x-show="status === 'valid'" class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg x-cloak x-show="status === 'invalid'" class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
    </div>

    <p x-cloak x-show="status === 'invalid'" class="text-[11px] font-bold text-rose-600 dark:text-rose-400">
        کد ملی وارد شده از نظر ساختار ریاضی معتبر نیست.
    </p>
</div>
