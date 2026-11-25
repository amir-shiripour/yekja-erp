@props([
    'name' => 'mobile',
    'value' => '',
    'id' => null,
    'label' => null,
    'required' => false,
    'placeholder' => '912 345 6789',
    'hint' => null,
    'mode' => 'mobile', // mobile | line
])

@php
    $inputId = $id ?? 'phone_input_' . str_replace('.', '_', uniqid('', true));
    $initialValue = (string) old($name, $value ?? '');
    $isLineMode = $mode === 'line';
@endphp

{{-- Authentic VibeFarsi Phone Input Component --}}
<div
    class="space-y-1.5"
    x-data="{
        raw: @js($initialValue),
        digits: '',
        valid: false,
        operator: null,
        isLine: @js($isLineMode),

        toEnglishDigits(str) {
            if (!str) return '';
            const fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            const ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            return str.toString()
                .replace(/[۰-۹]/g, d => fa.indexOf(d))
                .replace(/[٠-٩]/g, d => ar.indexOf(d));
        },

        normalize(val) {
            let d = this.toEnglishDigits(val).replace(/\D/g, '');
            if (this.isLine) {
                return d.slice(0, 20);
            }
            if (d.startsWith('98')) d = d.slice(2);
            if (d.startsWith('0')) d = d.slice(1);
            return d.slice(0, 10);
        },

        format(d) {
            if (!d) return '';
            if (this.isLine) return d;
            if (d.length <= 3) return d;
            if (d.length <= 6) return d.slice(0, 3) + ' ' + d.slice(3);
            return d.slice(0, 3) + ' ' + d.slice(3, 6) + ' ' + d.slice(6);
        },

        detectOperator(d) {
            if (this.isLine || !d || d.length < 3) return null;
            const prefix = d.slice(0, 3);
            if (/^(910|911|912|913|914|915|916|917|918|919|990|991|992|993|994|996)$/.test(prefix)) {
                return 'همراه اول';
            }
            if (/^(930|933|935|936|937|938|939|901|902|903|904|905)$/.test(prefix)) {
                return 'ایرانسل';
            }
            if (/^(920|921|922|923)$/.test(prefix)) {
                return 'رایتل';
            }
            if (/^(998)$/.test(prefix)) {
                return 'شاتل موبایل';
            }
            return null;
        },

        update(val) {
            this.digits = this.normalize(val);
            this.valid = this.isLine ? this.digits.length >= 4 : /^9\d{9}$/.test(this.digits);
            this.operator = this.detectOperator(this.digits);
        },

        init() {
            this.update(this.raw);
        }
    }"
>
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    {{-- کانتینر فیلد شماره با سبک اصیل VibeFarsi --}}
    <div
        class="flex h-11 items-center gap-2 rounded-xl border bg-gray-50/70 dark:bg-gray-900/60 px-3.5 transition-colors focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-gray-800"
        :class="!isLine && digits.length === 10 && !valid ? 'border-rose-400 dark:border-rose-600' : (valid ? 'border-emerald-400 dark:border-emerald-600' : 'border-gray-200 dark:border-gray-700')"
        dir="ltr"
    >
        @if($isLineMode)
            <svg class="size-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        @else
            {{-- پیش‌شماره ثابت ایران --}}
            <span class="text-xs font-black text-gray-400 dark:text-gray-500 select-none tracking-wider">
                +98
            </span>
        @endif

        <span class="h-4 w-px bg-gray-200 dark:bg-gray-700"></span>

        {{-- ورودی فرمت‌شده شماره --}}
        <input
            id="{{ $inputId }}"
            type="tel"
            inputmode="tel"
            autocomplete="tel-national"
            :value="format(digits)"
            @input="update($event.target.value)"
            placeholder="{{ $placeholder }}"
            class="h-full min-w-0 flex-1 bg-transparent text-xs font-bold text-gray-900 dark:text-gray-100 outline-none placeholder:text-gray-400 dark:placeholder:text-gray-600 tracking-wider"
            @if($required) required @endif
            {{ $attributes->whereDoesntStartWith('class')->except(['mode']) }}
        />

        {{-- نشانگر سبز تایید اصالت شماره در وایب‌فارسی --}}
        <template x-if="valid">
            <span
                class="flex items-center justify-center size-5 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 shrink-0">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
        </template>
    </div>

    {{-- فیلد مخفی برای ارسال مقدار استاندارد به لاراول --}}
    <input
        type="hidden"
        name="{{ $name }}"
        :value="!isLine && digits.length === 10 ? '0' + digits : digits"
    />

    @if(!$isLineMode)
        {{-- برچسب راهنما و نمایش نام اپراتور طبق VibeFarsi --}}
        <div class="flex items-center justify-between text-[11px] pt-0.5">
            <template x-if="operator">
                <span class="inline-flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400">
                    <span class="size-1.5 rounded-full bg-indigo-500"></span>
                    <span>اپراتور: <strong x-text="operator"></strong></span>
                </span>
            </template>

            <template x-if="!operator && digits.length === 10 && !valid">
                <span class="text-rose-600 dark:text-rose-400 font-bold">
                    شماره باید ۱۰ رقمی بوده و با ۹ شروع شود.
                </span>
            </template>

            <template x-if="!operator && digits.length !== 10">
                <span class="text-gray-400 dark:text-gray-500">
                    {{ $hint ?? 'شماره را بدون صفر اول (مثلاً ۹۱۲۳۴۵۶۷۸۹) وارد کنید' }}
                </span>
            </template>
        </div>
    @endif
</div>
