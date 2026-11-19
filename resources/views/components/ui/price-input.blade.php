@props([
    'label' => null,
    'name' => null,
    'value' => '',
    'currency' => 'تومان',
    'placeholder' => 'مثال: 1,500,000',
    'showWords' => true,
])

<div
    x-data="{
        raw: @js((string) $value),
        display: '',
        words: '',
        init() {
            this.raw = this.toEnDigits(String(this.raw || '')).replace(/\D/g, '');
            this.updateDisplay();
            this.$watch('raw', (val) => {
                const clean = this.toEnDigits(String(val || '')).replace(/\D/g, '');
                if (clean !== val) {
                    this.raw = clean;
                    return;
                }
                this.updateDisplay();
            });
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
            const clean = this.toEnDigits(e.target.value).replace(/\D/g, '');
            this.raw = clean;
            this.updateDisplay();
        },
        updateDisplay() {
            if (!this.raw) {
                this.display = '';
                this.words = '';
                return;
            }
            this.display = this.raw.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            this.words = this.numberToPersianWords(this.raw);
        },
        numberToPersianWords(numStr) {
            const num = BigInt(numStr);
            if (num === 0n) return 'صفر';
            const ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
            const teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
            const tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
            const hundreds = ['', 'یکصد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
            const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تیلیارد'];

            const convertGroup = (n) => {
                const parts = [];
                const h = Math.floor(n / 100);
                const rem = n % 100;
                if (h > 0) parts.push(hundreds[h]);
                if (rem >= 10 && rem < 20) {
                    parts.push(teens[rem - 10]);
                } else {
                    const t = Math.floor(rem / 10);
                    const o = rem % 10;
                    if (t > 0) parts.push(tens[t]);
                    if (o > 0) parts.push(ones[o]);
                }
                return parts.join(' و ');
            };

            const chunks = [];
            let temp = num;
            while (temp > 0n) {
                chunks.push(Number(temp % 1000n));
                temp = temp / 1000n;
            }

            const result = [];
            for (let i = chunks.length - 1; i >= 0; i--) {
                if (chunks[i] > 0) {
                    const text = convertGroup(chunks[i]);
                    const scale = scales[i] || '';
                    result.push(scale ? (text + ' ' + scale) : text);
                }
            }
            return result.join(' و ');
        }
    }"
    x-modelable="raw"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'value', 'currency', 'placeholder', 'showWords']) }}
    class="space-y-1.5 {{ $attributes->get('class') }}"
>
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="raw">
    @endif

    <div class="relative flex items-center">
        <input
            type="text"
            inputmode="numeric"
            dir="ltr"
            :value="display"
            @input="onInput($event)"
            placeholder="{{ $placeholder }}"
            class="w-full px-4 py-2.5 pl-16 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-left"
        >
        <span
            class="absolute left-3 px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-[11px] font-bold text-gray-500 dark:text-gray-400 pointer-events-none select-none">
            {{ $currency }}
        </span>
    </div>

    @if($showWords)
        <p x-cloak x-show="words" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 pr-1"
           x-text="words + ' {{ $currency }}'"></p>
    @endif
</div>
