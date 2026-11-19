@props([
    'label' => null,
    'name' => null,
    'length' => 5,
    'value' => '',
])

@php
    $len = max(4, min(8, (int) $length));
@endphp

<div
    x-data="{
        length: {{ $len }},
        code: @js((string) $value),
        digits: Array({{ $len }}).fill(''),
        init() {
            this.syncFromCode(this.code);
            this.$watch('code', (v) => this.syncFromCode(v));
        },
        toEnDigits(str) {
            const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            for (let i = 0; i < 10; i++) {
                str = str.replaceAll(fa[i], i).replaceAll(ar[i], i);
            }
            return str;
        },
        syncFromCode(val) {
            const clean = this.toEnDigits(String(val || '')).replace(/\D/g, '').slice(0, this.length);
            for (let i = 0; i < this.length; i++) {
                this.digits[i] = clean[i] || '';
            }
        },
        updateCode() {
            this.code = this.digits.join('');
        },
        handleInput(idx, e) {
            const clean = this.toEnDigits(e.target.value).replace(/\D/g, '');
            if (clean.length > 1) {
                this.fillFrom(idx, clean);
                return;
            }
            this.digits[idx] = clean;
            e.target.value = clean;
            this.updateCode();
            if (clean && idx < this.length - 1) {
                this.$refs['otp_' + (idx + 1)]?.focus();
            }
        },
        handleBackspace(idx, e) {
            if (!this.digits[idx] && idx > 0) {
                this.$refs['otp_' + (idx - 1)]?.focus();
            } else {
                this.digits[idx] = '';
                this.updateCode();
            }
        },
        handlePaste(idx, e) {
            const pasted = this.toEnDigits((e.clipboardData || window.clipboardData).getData('text')).replace(/\D/g, '');
            if (pasted) {
                this.fillFrom(0, pasted);
            }
        },
        fillFrom(startIdx, str) {
            for (let i = 0; i < str.length && (startIdx + i) < this.length; i++) {
                this.digits[startIdx + i] = str[i];
            }
            this.updateCode();
            const nextIdx = Math.min(startIdx + str.length, this.length - 1);
            this.$refs['otp_' + nextIdx]?.focus();
        }
    }"
    x-modelable="code"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'length', 'value']) }}
    class="space-y-2 {{ $attributes->get('class') }}"
>
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 text-center">{{ $label }}</label>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="code">
    @endif

    <div class="flex items-center justify-center gap-2 sm:gap-3" dir="ltr">
        @for($i = 0; $i < $len; $i++)
            <input
                x-ref="otp_{{ $i }}"
                type="text"
                inputmode="numeric"
                maxlength="1"
                :value="digits[{{ $i }}]"
                @input="handleInput({{ $i }}, $event)"
                @keydown.backspace="handleBackspace({{ $i }}, $event)"
                @paste.prevent="handlePaste({{ $i }}, $event)"
                @focus="$event.target.select()"
                class="w-11 h-12 sm:w-12 sm:h-14 text-center text-lg font-mono font-black bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all"
            >
        @endfor
    </div>
</div>
