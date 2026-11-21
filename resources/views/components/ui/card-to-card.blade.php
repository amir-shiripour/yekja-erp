@props([
    'cardNumber' => '',
    'owner' => '',
    'bank' => null,
    'amount' => null,
    'iban' => null,
])

@php
    $rawCard = preg_replace('/\D/', '', (string) $cardNumber);
    $formattedCard = trim(chunk_split($rawCard, 4, ' '));
    $bin = substr($rawCard, 0, 6);

    $binMap = [
        '603799' => ['name' => 'بانک ملی ایران', 'grad' => 'from-slate-900 via-amber-950/60 to-slate-900'],
        '610433' => ['name' => 'بانک ملت', 'grad' => 'from-rose-950 via-slate-900 to-slate-950'],
        '621986' => ['name' => 'بانک سامان', 'grad' => 'from-sky-950 via-blue-950 to-slate-950'],
        '502229' => ['name' => 'بانک پاسارگاد', 'grad' => 'from-amber-950 via-stone-900 to-neutral-950'],
        '603769' => ['name' => 'بانک صادرات', 'grad' => 'from-indigo-950 via-blue-950 to-slate-950'],
        '627353' => ['name' => 'بانک تجارت', 'grad' => 'from-blue-950 via-indigo-950 to-slate-900'],
        '622106' => ['name' => 'بانک پارسیان', 'grad' => 'from-amber-900/90 via-slate-900 to-slate-950'],
        '603770' => ['name' => 'بانک کشاورزی', 'grad' => 'from-emerald-950 via-slate-900 to-slate-950'],
        '628023' => ['name' => 'بانک مسکن', 'grad' => 'from-orange-950 via-slate-900 to-slate-950'],
        '589210' => ['name' => 'بانک سپه', 'grad' => 'from-slate-900 via-indigo-950 to-slate-950'],
        '589463' => ['name' => 'بانک رفاه کارگران', 'grad' => 'from-teal-950 via-slate-900 to-slate-950'],
        '504706' => ['name' => 'بانک شهر', 'grad' => 'from-red-950 via-slate-900 to-slate-950'],
        '636214' => ['name' => 'بانک آینده', 'grad' => 'from-purple-950 via-slate-900 to-slate-950'],
        '639346' => ['name' => 'بانک سینا', 'grad' => 'from-cyan-950 via-slate-900 to-slate-950'],
    ];

    $detected = $binMap[$bin] ?? ['name' => 'کارت بانکی عضو شتاب', 'grad' => 'from-slate-900 via-indigo-950 to-slate-950'];
    $bankName = $bank ?: $detected['name'];
    $gradient = $detected['grad'];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    {{-- Physical Bank Card Visual --}}
    <div
        class="relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $gradient }} text-white p-6 shadow-2xl border border-white/10">
        {{-- Decorative Glow --}}
        <div class="absolute -top-16 -left-16 w-48 h-48 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
        <div
            class="absolute -bottom-16 -right-16 w-48 h-48 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>

        {{-- Top Row: Bank Name & EMV Chip --}}
        <div class="relative flex items-center justify-between mb-6">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-10 h-7 rounded-lg bg-gradient-to-br from-amber-300 via-amber-400 to-yellow-600 opacity-90 shadow-inner border border-amber-200/40 flex items-center justify-center">
                    <div class="w-6 h-4 border border-amber-900/30 rounded-sm"></div>
                </div>
                <span class="text-sm font-black tracking-tight text-white/95">{{ $bankName }}</span>
            </div>
            @if($amount)
                <span
                    class="px-3 py-1 rounded-xl bg-white/10 border border-white/15 text-xs font-bold text-amber-300 backdrop-blur-xs">
                    {{ is_numeric($amount) ? number_format((float) $amount) . ' تومان' : $amount }}
                </span>
            @endif
        </div>

        {{-- Card Number + Instant Copy --}}
        <div
            class="relative py-3.5 px-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-sm flex items-center justify-between gap-3">
            <span dir="ltr" class="text-lg sm:text-xl font-black tracking-[0.18em] text-white select-all">
                {{ $formattedCard ?: '---- ---- ---- ----' }}
            </span>
            @if($rawCard)
                <x-ui.copy-button :text="$rawCard" size="sm"
                                  class="!bg-white/15 !border-white/20 !text-white hover:!bg-white/25"/>
            @endif
        </div>

        {{-- Bottom Row: Owner & Optional IBAN --}}
        <div
            class="relative mt-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-300">
            <div>
                <span class="text-[10px] text-slate-400 block">به نام:</span>
                <span class="font-bold text-white">{{ $owner ?: 'حساب رسمی سامانه' }}</span>
            </div>
            @if($iban)
                <div class="flex items-center gap-2">
                    <span dir="ltr" class="text-[11px] text-slate-200">{{ $iban }}</span>
                    <x-ui.copy-button :text="$iban" size="sm"
                                      class="!bg-white/10 !border-white/15 !text-white hover:!bg-white/20"/>
                </div>
            @endif
        </div>
    </div>

    {{-- Optional Receipt / Tracking Slot --}}
    @if($slot->isNotEmpty())
        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-4">
            {{ $slot }}
        </div>
    @endif
</div>
