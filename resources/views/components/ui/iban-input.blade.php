@props([
    'label' => 'شماره شبا (IBAN)',
    'name' => null,
    'value' => '',
])

<div
    x-data="{
        iban: @js((string) $value),
        digits: '',
        display: '',
        status: 'idle', // idle | valid | invalid
        bankName: '',
        bankCodes: {
            '010': 'بانک مرکزی', '011': 'صنعت و معدن', '012': 'ملت', '013': 'رفاه کارگران',
            '014': 'مسکن', '015': 'سپه', '016': 'کشاورزی', '017': 'ملی ایران',
            '018': 'تجارت', '019': 'صادرات ایران', '020': 'توسعه صادرات', '021': 'پست بانک',
            '022': 'توسعه تعاون', '051': 'موسسه اعتباری توسعه', '053': 'کارآفرین', '054': 'پارسیان',
            '055': 'اقتصاد نوین', '056': 'سامان', '057': 'پاسارگاد', '058': 'سرمایه',
            '059': 'سینا', '060': 'قرض‌الحسنه مهر ایران', '061': 'شهر', '062': 'آینده',
            '063': 'انصار', '064': 'گردشگری', '065': 'حکمت ایرانیان', '066': 'دی',
            '069': 'ایران زمین', '070': 'رسالت', '078': 'خاورمیانه', '090': 'بلوبانک (سامان)',
            '095': 'ایران و ونزوئلا'
        },
        init() {
            this.syncFromValue(this.iban);
            this.$watch('iban', (v) => this.syncFromValue(v));
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
            const clean = this.toEnDigits(String(val || '')).toUpperCase().replace(/^IR/, '').replace(/\D/g, '').slice(0, 24);
            this.digits = clean;
            this.display = clean.replace(/(.{4})/g, '$1 ').trim();
            this.detectBank();
            this.validate();
        },
        onInput(e) {
            const clean = this.toEnDigits(e.target.value).toUpperCase().replace(/^IR/, '').replace(/\D/g, '').slice(0, 24);
            this.digits = clean;
            this.iban = clean ? ('IR' + clean) : '';
            this.display = clean.replace(/(.{4})/g, '$1 ').trim();
            this.detectBank();
            this.validate();
        },
        detectBank() {
            if (this.digits.length >= 5) {
                const code = this.digits.slice(2, 5);
                this.bankName = this.bankCodes[code] || '';
            } else {
                this.bankName = '';
            }
        },
        validate() {
            if (this.digits.length < 24) {
                this.status = 'idle';
                return;
            }
            // ISO 13616 Mod 97: move 'IR' + 2 check digits to end -> IR = 1827
            const rearranged = this.digits.slice(2) + '1827' + this.digits.slice(0, 2);
            let remainder = 0;
            for (let i = 0; i < rearranged.length; i++) {
                remainder = (remainder * 10 + parseInt(rearranged[i], 10)) % 97;
            }
            this.status = (remainder === 1) ? 'valid' : 'invalid';
        }
    }"
    x-modelable="iban"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'value']) }}
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
        <input type="hidden" name="{{ $name }}" :value="iban">
    @endif

    <div class="relative flex items-center" dir="ltr">
        <span
            class="inline-flex items-center justify-center px-3.5 py-2.5 bg-gray-100 dark:bg-gray-800 border border-r-0 border-gray-200 dark:border-gray-700 rounded-l-xl text-sm font-black text-gray-700 dark:text-gray-200 select-none">
            IR
        </span>
        <input
            type="text"
            inputmode="numeric"
            maxlength="29"
            :value="display"
            @input="onInput($event)"
            placeholder="06 0170 0000 0010 0324 0000 01"
            :class="{
                'border-emerald-400 dark:border-emerald-600 focus:border-emerald-500 focus:ring-emerald-500/20': status === 'valid',
                'border-rose-400 dark:border-rose-600 focus:border-rose-500 focus:ring-rose-500/20': status === 'invalid',
                'border-gray-200 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500/20': status === 'idle'
            }"
            class="w-full px-3.5 py-2.5 pr-10 bg-white dark:bg-gray-900 border rounded-r-xl text-sm font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 transition-all text-left"
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
        شماره شبای وارد شده صحیح نیست.
    </p>
</div>
