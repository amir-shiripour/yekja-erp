@props([
    'label' => 'روش پرداخت را انتخاب کنید',
    'name' => null,
    'value' => 'gateway',
    'walletBalance' => null,
    'methods' => ['gateway', 'card_to_card', 'cheque', 'wallet'],
])

@php
    $catalog = [
        'gateway' => [
            'title' => 'درگاه پرداخت اینترنتی',
            'desc' => 'پرداخت آنلاین با تمام کارت‌های عضو شتاب',
            'badge' => 'آنی',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />',
        ],
        'card_to_card' => [
            'title' => 'کارت به کارت',
            'desc' => 'انتقال وجه به شماره کارت و ثبت تصویر فیش',
            'badge' => 'تایید دستی',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />',
        ],
        'cheque' => [
            'title' => 'چک صیادی',
            'desc' => 'ثبت شناسه ۱۶ رقمی چک صیادی و تاریخ سررسید',
            'badge' => 'حسابداری',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        ],
        'wallet' => [
            'title' => 'کیف پول / اعتبار حساب',
            'desc' => $walletBalance !== null ? ('موجودی فعلی: ' . number_format((float) $walletBalance) . ' تومان') : 'کسر مستقیم از موجودی کیف پول کاربری',
            'badge' => 'سریع',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />',
        ],
    ];
@endphp

<div
    x-data="{ selected: @js((string) $value) }"
    x-modelable="selected"
    {{ $attributes->whereDoesntStartWith('class')->except(['label', 'name', 'value', 'walletBalance', 'methods']) }}
    class="space-y-3 {{ $attributes->get('class') }}"
>
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="selected">
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($methods as $methodKey)
            @if(isset($catalog[$methodKey]))
                @php $item = $catalog[$methodKey]; @endphp
                <button
                    type="button"
                    @click="selected = '{{ $methodKey }}'"
                    :class="selected === '{{ $methodKey }}'
                        ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 shadow-sm'
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-700'"
                    class="relative p-4 rounded-2xl border text-start transition-all duration-200 flex items-start gap-3 cursor-pointer group"
                >
                    <div
                        :class="selected === '{{ $methodKey }}'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 group-hover:text-indigo-600'"
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            {!! $item['icon'] !!}
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-black text-gray-900 dark:text-white">{{ $item['title'] }}</span>
                            <span
                                class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ $item['badge'] }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                    </div>
                </button>
            @endif
        @endforeach
    </div>
</div>
