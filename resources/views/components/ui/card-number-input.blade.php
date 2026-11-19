@props([
    'label' => 'شماره کارت بانکی',
    'name' => null,
    'value' => '',
    'placeholder' => '6037 9971 0000 0000',
])

<div
    x-data="{
        raw: @js((string) $value),
        display: '',
        status: 'idle', // idle | valid | invalid
        bankName: '',
        bins: {
            '603799': 'بانک ملی ایران',
            '610433': 'بانک ملت',
            '621986': 'بانک سامان',
            '502229': 'بانک پاسارگاد',
            '603769': 'بانک صادرات',
            '627353': 'بانک تجارت',
            '585983': 'بانک تجارت',
            '622106': 'بانک پارسیان',
            '639194': 'بانک پارسیان',
            '603770': 'بانک کشاورزی',
            '628023': 'بانک مسکن',
            '589210': 'بانک سپه',
            '589463': 'بانک رفاه کارگران',
            '504706': 'بانک شهر',
            '502806': 'بانک شهر',
            '636214': 'بانک آینده',
            '639346': 'بانک سینا',
            '627412': 'بانک اقتصاد نوین',
            '627760': 'پست بانک ایران',
            '606373': 'قرض‌الحسنه مهر ایران',
            '636949': 'بانک حکمت ایرانیان',
            '505785': 'بانک ایران زمین',
            '504172': 'بانک رسالت',
            '62198619': 'بلوبانک (سامان)'
        },
        init() {
            this.syncFromValue(this.raw);
            this.$watch('raw', (v) => this.syncFromValue(v));
        },
        toEnDigits(str) {
            const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            for (let i = 0; i < 10; i++) {
                str = str.replaceAll(fa[i], i).replaceAll(ar[i], i);
            }
            return str;
        },
        syncFromValue(val) {
            const clean = this.toEnDigits(String(val || '')).replace(/\D/g, '').slice(0, 16);
            if (clean !== this.raw) {
                this.raw = clean;
            }
            this.display = clean.replace(/(.{4})/g, '$1 ').trim();
            this.detectBank();
            this.validate();
        },
        onInput(e) {
            const clean = this.toEnDigits(e.target.value).replace(/\D/g, '').slice(0, 16);
            this.raw = clean;
            this.display = clean.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = this.display;
            this.detectBank();
            this.validate();
        },
        detectBank() {
            if (this.raw.length >= 8 && this.bins[this.raw.slice(0, 8)]) {
                this.bankName = this.bins[this.raw.slice(0, 8)];
            } else if (this.raw.length >= 6) {
                this.bankName = this.bins[this.raw.slice(0, 6)] || '';
            } else {
                this.bankName = '';
            }
        },
        validate() {
            if (this.raw.length < 16) {
                this.status = 'idle';
                return;
            }
            // Luhn check
            let sum = 0;
            for (let i = 0; i < 16; i++) {
                let d = parseInt(this.raw[i], 10);
                if (i % 2 === 0) {
                    d *= 2;
                    if (d > 9) d -= 9;
                }
                sum += d;
            }
            this.status = (sum % 10 === 0) ? 'valid' : 'invalid';
        }
    }"
    x-modelable="raw"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'value', 'placeholder']) }}
    class="space-y-1.5 {{ $attributes->get('class') }}"
>
    @if($label)
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ $label }}</label>
            <span x-cloak x-show="bankName" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400"
                  x-text="bankName"></span>
        </div>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="raw">
    @endif

    <div class="relative flex items-center" dir="ltr">
        <span class="absolute left-3 text-gray-400 pointer-events-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </span>

        <input
            type="text"
            inputmode="numeric"
            maxlength="19"
            :value="display"
            @input="onInput($event)"
            placeholder="{{ $placeholder }}"
            :class="{
                'border-emerald-400 dark:border-emerald-600 focus:border-emerald-500 focus:ring-emerald-500/20': status === 'valid',
                'border-rose-400 dark:border-rose-600 focus:border-rose-500 focus:ring-rose-500/20': status === 'invalid',
                'border-gray-200 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/20': status === 'idle'
            }"
            class="w-full px-10 py-2.5 bg-white dark:bg-gray-900 border rounded-xl text-sm font-mono font-bold tracking-widest text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 transition-all text-left"
        >

        <span class="absolute right-3 flex items-center pointer-events-none">
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
        شماره کارت ۱۶ رقمی وارد شده معتبر نیست (خطای الگوریتم Luhn).
    </p>
</div>
