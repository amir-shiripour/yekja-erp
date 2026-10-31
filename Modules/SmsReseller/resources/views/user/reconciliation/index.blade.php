@extends('layouts.user')

@section('title', 'سیستم هوشمند رفع مغایرت و تطبیق حسابداری')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white ps-4 pe-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $typeLabels = [
        'asset' => 'دارایی',
        'liability' => 'بدهی',
        'equity' => 'سرمایه',
        'income' => 'درآمد',
        'revenue' => 'درآمد',
        'expense' => 'هزینه',
    ];

    $typeBadges = [
        'asset' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
        'liability' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
        'equity' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
        'income' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        'revenue' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
    ];

    $mapCategories = function($categories) use ($typeLabels, $typeBadges) {
        return collect($categories ?? [])->map(function($c) use ($typeLabels, $typeBadges) {
            $catType = $c->type ?? 'expense';
            return [
                'id' => (string) $c->id,
                'title' => (string) $c->title,
                'account_code' => (string) ($c->account_code ?? ''),
                'type' => (string) $catType,
                'type_label' => $typeLabels[$catType] ?? ($c->type ?? 'سرفصل'),
                'type_badge' => $typeBadges[$catType] ?? 'bg-gray-100 text-gray-700',
            ];
        })->values()->all();
    };

    $fundTypes = [
        'bank' => 'بانک',
        'cash' => 'صندوق',
        'gateway' => 'درگاه پرداخت',
        'petty_cash' => 'تنخواه',
    ];

    $fundBadges = [
        'bank' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
        'cash' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        'gateway' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50',
        'petty_cash' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
    ];

    $mapFunds = function($funds) use ($fundTypes, $fundBadges) {
        return collect($funds ?? [])->map(function($f) use ($fundTypes, $fundBadges) {
            $type = $f->type ?? 'bank';
            $isWallet = (method_exists($f, 'isWalletAccount') && $f->isWalletAccount())
                || str_contains($f->name, 'کیف پول')
                || str_contains($f->notes ?? '', 'wallet');
            return [
                'id' => (string) $f->id,
                'name' => (string) $f->name,
                'bank_name' => (string) ($f->bank_name ?? ''),
                'account_number' => (string) ($f->account_number ?? ''),
                'type' => $type,
                'type_label' => $isWallet ? 'کیف پول' : ($fundTypes[$type] ?? $type),
                'type_badge' => $isWallet
                    ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50'
                    : ($fundBadges[$type] ?? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50'),
                'balance' => (float) ($f->balance ?? 0),
                'is_wallet' => (bool) $isWallet,
            ];
        })->values()->all();
    };

    $incomeCategoriesList = $mapCategories($incomeCategories ?? []);
    $expenseCategoriesList = $mapCategories($expenseCategories ?? []);
    $liabilityCategoriesList = $mapCategories($liabilityCategories ?? []);
    $fundAccountsList = $mapFunds($fundAccounts ?? []);

    if (!isset($userWalletsList)) {
        if (class_exists(\Modules\Wallet\App\Models\Wallet::class)) {
            try {
                $userWalletsList = \Modules\Wallet\App\Models\Wallet::with('holder')
                    ->where('is_active', true)
                    ->get()
                    ->map(function ($w) {
                        $holder = $w->holder;
                        $name = 'نامشخص';
                        $phone = '';
                        $typeLabel = 'کاربر';
                        $typeBadge = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                        $isClient = false;

                        if ($holder) {
                            $name = $holder->name ?? ($holder->full_name ?? ($holder->username ?? 'کاربر'));
                            $phone = $holder->phone ?? ($holder->mobile ?? '');
                            if ($holder instanceof \Modules\Clients\Entities\Client || str_contains(get_class($holder), 'Client')) {
                                $typeLabel = 'مشتری CRM';
                                $typeBadge = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50';
                                $isClient = true;
                            } else {
                                $typeLabel = 'کاربر سیستم';
                                $typeBadge = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50';
                            }
                        }

                        return [
                            'id' => (string) $w->id,
                            'name' => (string) $name,
                            'phone' => (string) $phone,
                            'holder_id' => $w->holder_id,
                            'holder_type' => (string) $w->holder_type,
                            'type_label' => $typeLabel,
                            'type_badge' => $typeBadge,
                            'is_client' => $isClient,
                            'balance' => (float) ($w->balance ?? 0),
                            'currency' => (string) ($w->currency ?? 'IRR'),
                        ];
                    })->values()->all();
            } catch (\Throwable $e) {
                $userWalletsList = [];
            }
        } else {
            $userWalletsList = [];
        }
    }
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ showCumulativeModal: false }">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('smsreseller::partials.toast')


    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span>سیستم هوشمند رفع مغایرت و تطبیق حسابداری</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                کنترل تراز بودن اسناد ثبتی سیستم حسابداری با دریافتی کاربران، هزینه‌های شارژ پنل و موجودی آنلاین کیف پول
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(!$analysis['isBalanced'])
                <button type="button"
                        @click="showCumulativeModal = true"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-black bg-gradient-to-r from-indigo-600 to-violet-600 text-white hover:from-indigo-700 hover:to-violet-700 shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 active:scale-95 transition-all cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>صدور سند تجمیعی رفع مغایرت</span>
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div
            class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div
            class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($analysis['isBalanced'])
        <div
            class="p-6 rounded-3xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-emerald-900 dark:text-emerald-200">وضعیت دفاتر مالی: کاملاً
                        تراز و هماهنگ</h3>
                    <p class="text-xs sm:text-sm text-emerald-700 dark:text-emerald-400 mt-1">
                        تمامی پرداخت‌های کاربران و هزینه‌های شارژ پنل دارای سند حسابداری هستند و هیچ مغایرتی در دفاتر
                        سیستم وجود ندارد.
                    </p>
                </div>
            </div>
            <span
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 self-start sm:self-auto shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                تراز ۱۰۰٪
            </span>
        </div>
    @else
        <div
            class="p-6 rounded-3xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-amber-900 dark:text-amber-200">مغایرت دفتری شناسایی شد</h3>
                    <p class="text-xs sm:text-sm text-amber-700 dark:text-amber-400 mt-1">
                        تعدادی از پرداخت‌های کاربران یا شارژ‌های پنل سند حسابداری ندارند. می‌توانید با کلیک بر روی
                        <strong>«صدور سند تجمیعی رفع مغایرت»</strong>، تمام سوابق گذشته را یکجا تراز کنید.
                    </p>
                </div>
            </div>
            <button type="button"
                    @click="showCumulativeModal = true"
                    class="px-5 py-2.5 rounded-xl bg-amber-500 text-white text-xs font-black hover:bg-amber-600 shadow-md shadow-amber-500/20 active:scale-95 transition-all self-start sm:self-auto shrink-0 cursor-pointer">
                صدور سند تجمیعی
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">کل دریافتی از کاربران (پنل)</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $faNum(number_format($analysis['totalUserIncome'])) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>تعداد تراکنش‌ها:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($analysis['totalUserCount'])) }} واریز</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>سند نخورده:</span>
                <strong class="text-rose-600 dark:text-rose-400 font-black">
                    {{ $faNum(number_format($analysis['unrecordedUserSum'])) }} ریال
                    ({{ $faNum($analysis['unrecordedUserCount']) }} مورد)
                </strong>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">کل شارژ پنل (هزینه‌ها)</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ $faNum(number_format($analysis['totalPanelCost'])) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>تعداد شارژ‌ها:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum(number_format($analysis['totalPanelCount'])) }} بار</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>سند نخورده:</span>
                <strong class="text-rose-600 dark:text-rose-400 font-black">
                    {{ $faNum(number_format($analysis['unrecordedPanelSum'])) }} ریال
                    ({{ $faNum($analysis['unrecordedPanelCount']) }} مورد)
                </strong>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-400">خالص سود کل سامانه (دریافتی -
                        شارژ)</p>
                    <p class="text-2xl font-black @if($analysis['calculatedProfit'] >= 0) text-indigo-600 dark:text-indigo-400 @else text-rose-600 dark:text-rose-400 @endif mt-1">
                        {{ $faNum(number_format($analysis['calculatedProfit'])) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                    <div class="flex items-center gap-1.5 pt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>سود واقعی:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300">بر اساس دیتای پنل</span>
                    </div>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>ثبت شده در حسابداری:</span>
                <strong class="text-gray-800 dark:text-gray-200 font-black">
                    {{ $faNum(number_format($analysis['accountingIncomeTotal'] - $analysis['accountingExpenseTotal'])) }}
                    ریال
                </strong>
            </div>
        </div>
        <div class="{{ $cardClass }} p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs font-bold text-gray-400 dark:text-gray-400">مانده آنلاین کیف پول سامانه</p>
                        <span
                            class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Live</span>
                    </div>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ $faNum(number_format($analysis['onlineWalletBalance'])) }} <span
                            class="text-xs font-bold text-gray-400">ریال</span>
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div
                class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>مانده حساب خزانه‌داری:</span>
                <strong class="text-gray-800 dark:text-gray-200 font-black">
                    {{ $analysis['walletFundBalance'] !== null ? $faNum(number_format($analysis['walletFundBalance'])) . ' ریال' : 'تنظیم نشده' }}
                </strong>
            </div>
        </div>
    </div>
    @if(!empty($analysis['operatorTariffs']) || !empty($analysis['panelOwnerName']))
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8 shadow-sm backdrop-blur-xl space-y-6">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700/50 pb-4">
                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <span>مشخصات زنده پنل اصلی: <strong
                            class="text-indigo-600 dark:text-indigo-400">{{ $analysis['panelOwnerName'] ?: 'نمایندگی پیامک' }}</strong></span>
                </h3>
                @if($analysis['smsRemainingCount'] !== null)
                    <div
                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-200/50 dark:border-indigo-800/50">
                        <span>تعداد کل پیامک باقیمانده (تخمینی):</span>
                        <span
                            class="font-black text-sm">{{ $faNum(number_format($analysis['smsRemainingCount'])) }} عدد</span>
                    </div>
                @endif
            </div>

            @if(!empty($analysis['operatorTariffs']))
                <div>
                    <span class="block text-xs font-bold text-gray-400 dark:text-gray-400 mb-3">نرخ خرید هر پیامک از اپراتورهای مختلف (زنده از وب‌سرویس):</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @php
                            $opLabels = [
                                'op_1000' => 'اپراتور ۱۰۰۰',
                                'op_2000' => 'اپراتور ۲۰۰۰',
                                'op_3000' => 'اپراتور ۳۰۰۰',
                                'op_50002' => 'اپراتور ۵۰۰۰۲',
                                'op_Avanak' => 'آوانک (صوتی)',
                                'op_Simcard' => 'سیم کارت',
                                'op_9000' => 'اپراتور ۹۰۰۰',
                                'op_IdehPayam' => 'ایده پیام',
                                'op_AryanTel' => 'آرین تل',
                                'op_LBS' => 'پیامک زنده (LBS)',
                                'op_Asanak' => 'آسانک',
                                'op_Bale' => 'پیام‌رسان بله',
                            ];
                        @endphp
                        @foreach($analysis['operatorTariffs'] as $opKey => $opPrice)
                            @if($opKey !== 'userId')
                                <div
                                    class="p-3 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between">
                                    <span
                                        class="text-gray-500 dark:text-gray-400 text-xs font-bold">{{ $opLabels[$opKey] ?? $opKey }}</span>
                                    <span class="font-black text-gray-900 dark:text-white mt-1 text-sm">{{ $faNum(number_format($opPrice)) }} <span
                                            class="text-[10px] font-normal text-gray-400">ریال</span></span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8 shadow-sm backdrop-blur-xl space-y-5">
        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/50 pb-4">
            <span
                class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span>نتایج تحلیل هوشمند و پیشنهادات سیستم رفع مغایرت</span>
        </h3>

        @if(!empty($analysis['issues']))
            <div class="space-y-3">
                @foreach($analysis['issues'] as $issue)
                    <div class="p-4 sm:p-5 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all
                        @if($issue['type'] === 'warning') bg-amber-50/70 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-300
                        @else bg-sky-50/70 dark:bg-sky-950/20 border-sky-200 dark:border-sky-800/60 text-sky-900 dark:text-sky-300 @endif">
                        <div class="space-y-1">
                            <h4 class="font-black text-sm flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full @if($issue['type'] === 'warning') bg-amber-500 @else bg-sky-500 @endif"></span>
                                <span>{{ $issue['title'] }}</span>
                            </h4>
                            <p class="text-xs text-gray-600 dark:text-gray-300 ms-4">{{ $issue['message'] }}</p>
                        </div>
                        @if(isset($issue['action_url']))
                            <a href="{{ $issue['action_url'] }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-2xs hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shrink-0">
                                <span>{{ $issue['action_label'] }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-sm font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span>تمامی پارامترها و رکوردهای مالی با موفقیت بررسی شدند؛ سیستم بدون هیچ‌گونه خطا یا ناهماهنگی آماده بهره‌برداری است.</span>
            </div>
        @endif
    </div>
    <div x-show="showCumulativeModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true"
         @keydown.escape.window="showCumulativeModal = false">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showCumulativeModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showCumulativeModal = false"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showCumulativeModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-visible rounded-3xl bg-white dark:bg-gray-800 text-start shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700/80">

                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/70 to-violet-50/50 dark:from-indigo-950/30 dark:to-violet-950/30 flex items-center justify-between rounded-t-3xl">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white" id="modal-title">
                                صدور سند تجمیعی رفع مغایرت
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                هماهنگ‌سازی یکجای تمامی پرداخت‌ها و شارژ‌های تاریخی در اسناد مالی
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showCumulativeModal = false"
                            class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('user.smsreseller.reconciliation.cumulative') }}"
                      class="p-6 space-y-5">
                    @csrf
                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl p-4 space-y-2.5 border border-gray-200/80 dark:border-gray-700 text-xs">
                        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                ۱. درآمد کل دریافتی سند نخورده (بستانکار):
                            </span>
                            <span class="text-sm font-black">{{ $faNum(number_format($analysis['unrecordedUserSum'])) }} ریال <span
                                    class="text-[11px] font-medium opacity-75">({{ $faNum($analysis['unrecordedUserCount']) }} پرداخت)</span></span>
                        </div>
                        <div class="flex items-center justify-between text-rose-600 dark:text-rose-400 font-bold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                ۲. هزینه کل شارژ پنل سند نخورده (بدهکار):
                            </span>
                            <span class="text-sm font-black">{{ $faNum(number_format($analysis['unrecordedPanelSum'])) }} ریال <span
                                    class="text-[11px] font-medium opacity-75">({{ $faNum($analysis['unrecordedPanelCount']) }} شارژ)</span></span>
                        </div>
                        <div
                            class="flex items-center justify-between text-gray-900 dark:text-white font-black pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                ۳. خالص مازاد به حساب بانکی / خزانه:
                            </span>
                            <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $faNum(number_format($analysis['unrecordedUserSum'] - $analysis['unrecordedPanelSum'])) }} ریال</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ $setting->default_income_category_id ?? ($defaultIncomeCat?->id ?? '') }}',
                            options: @js($incomeCategoriesList),
                            get filteredOptions() {
                                if (!this.search.trim()) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o =>
                                    (o.title && o.title.toLowerCase().includes(q)) ||
                                    (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                    (o.type_label && o.type_label.toLowerCase().includes(q))
                                );
                            },
                            select(opt) {
                                this.selectedId = opt ? String(opt.id) : '';
                                this.open = false;
                                this.search = '';
                            },
                            getSelectedTitle() {
                                let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                if (!found) return 'انتخاب سرفصل درآمدی...';
                                return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                            },
                            formatFa(str) {
                                if (!str && str !== 0) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }"
                             class="space-y-1.5 relative transition-all"
                             :class="{ 'z-50': open, 'z-40': !open }">
                            <label class="{{ $labelClass }}">سرفصل درآمد فروش پیامک <span
                                    class="font-normal text-emerald-500 text-xs">(سمت بستانکار سند)</span></label>
                            <div class="relative">
                                <input type="hidden" name="income_category_id" :value="selectedId" required>
                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                    <div class="flex items-center gap-2 truncate">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 font-bold text-xs shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </span>
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                        <template x-if="selectedId">
                                            <button type="button" @click.stop="select(null)"
                                                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                    title="پاک کردن انتخاب">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجو سرفصل درآمدی یا کد..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>
                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-emerald-50/70 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 font-bold': String(selectedId) === String(opt.id) }">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span x-show="opt.account_code"
                                                      class="text-[10px] text-gray-400 font-medium"
                                                      x-text="formatFa(opt.account_code)"></span>
                                                <span x-text="opt.title" class="truncate font-medium"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="opt.type_badge" x-text="opt.type_label"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ سرفصل درآمدی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ $setting->default_expense_category_id ?? ($defaultExpenseCat?->id ?? '') }}',
                            options: @js($expenseCategoriesList),
                            get filteredOptions() {
                                if (!this.search.trim()) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o =>
                                    (o.title && o.title.toLowerCase().includes(q)) ||
                                    (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                    (o.type_label && o.type_label.toLowerCase().includes(q))
                                );
                            },
                            select(opt) {
                                this.selectedId = opt ? String(opt.id) : '';
                                this.open = false;
                                this.search = '';
                            },
                            getSelectedTitle() {
                                let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                if (!found) return 'انتخاب سرفصل هزینه شارژ پنل...';
                                return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                            },
                            formatFa(str) {
                                if (!str && str !== 0) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }"
                             class="space-y-1.5 relative transition-all"
                             :class="{ 'z-40': open, 'z-30': !open }">
                            <label class="{{ $labelClass }}">سرفصل هزینه شارژ پنل پیامک <span
                                    class="font-normal text-rose-500 text-xs">(سمت بدهکار سند)</span></label>
                            <div class="relative">
                                <input type="hidden" name="expense_category_id" :value="selectedId" required>
                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                    <div class="flex items-center gap-2 truncate">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-bold text-xs shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                            </svg>
                                        </span>
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                        <template x-if="selectedId">
                                            <button type="button" @click.stop="select(null)"
                                                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                    title="پاک کردن انتخاب">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجو سرفصل هزینه یا کد..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>
                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 dark:hover:text-rose-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-rose-50/70 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 font-bold': String(selectedId) === String(opt.id) }">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span x-show="opt.account_code"
                                                      class="text-[10px] text-gray-400 font-medium"
                                                      x-text="formatFa(opt.account_code)"></span>
                                                <span x-text="opt.title" class="truncate font-medium"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="opt.type_badge" x-text="opt.type_label"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ سرفصل هزینه‌ای پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ۳. دراپ‌داون سرفصل مالیات بر ارزش افزوده --}}
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ $setting->default_tax_category_id ?? ($defaultTaxCat?->id ?? '') }}',
                            options: @js($liabilityCategoriesList),
                            get filteredOptions() {
                                if (!this.search.trim()) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o =>
                                    (o.title && o.title.toLowerCase().includes(q)) ||
                                    (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                    (o.type_label && o.type_label.toLowerCase().includes(q))
                                );
                            },
                            select(opt) {
                                this.selectedId = opt ? String(opt.id) : '';
                                this.open = false;
                                this.search = '';
                            },
                            getSelectedTitle() {
                                let found = this.options.find(o => String(o.id) === String(this.selectedId));
                                if (!found) return 'انتخاب سرفصل مالیات...';
                                return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                            },
                            formatFa(str) {
                                if (!str && str !== 0) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }"
                             class="space-y-1.5 relative transition-all"
                             :class="{ 'z-30': open, 'z-20': !open }">
                            <label class="{{ $labelClass }}">سرفصل مالیات بر ارزش افزوده <span
                                    class="font-normal text-amber-500 text-xs">(حساب مالیات و عوارض)</span></label>
                            <div class="relative">
                                <input type="hidden" name="tax_category_id" :value="selectedId">
                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                    <div class="flex items-center gap-2 truncate">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                            </svg>
                                        </span>
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                        <template x-if="selectedId">
                                            <button type="button" @click.stop="select(null)"
                                                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                    title="پاک کردن انتخاب">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجو سرفصل مالیاتی یا کد..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>
                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-amber-50/70 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 font-bold': String(selectedId) === String(opt.id) }">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span x-show="opt.account_code"
                                                      class="text-[10px] text-gray-400 font-medium"
                                                      x-text="formatFa(opt.account_code)"></span>
                                                <span x-text="opt.title" class="truncate font-medium"></span>
                                            </div>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                  :class="opt.type_badge" x-text="opt.type_label"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredOptions.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ سرفصل مالیاتی پیدا نشد
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ $setting->default_fund_account_id ?? ($defaultFundAccount?->id ?? '') }}',
                            options: @js($fundAccountsList),

                            walletOpen: false,
                            walletSearch: '',
                            selectedWalletId: '',
                            walletOptions: @js($userWalletsList ?? []),

                            get selectedFund() {
                                return this.options.find(o => String(o.id) === String(this.selectedId));
                            },
                            get isWalletAccount() {
                                return this.selectedFund ? !!this.selectedFund.is_wallet : false;
                            },
                            get filteredOptions() {
                                if (!this.search.trim()) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o =>
                                    (o.name && o.name.toLowerCase().includes(q)) ||
                                    (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                    (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                    (o.type_label && o.type_label.toLowerCase().includes(q))
                                );
                            },
                            get filteredWalletOptions() {
                                if (!this.walletSearch.trim()) return this.walletOptions;
                                const q = this.walletSearch.toLowerCase();
                                return this.walletOptions.filter(w =>
                                    (w.name && w.name.toLowerCase().includes(q)) ||
                                    (w.phone && String(w.phone).toLowerCase().includes(q)) ||
                                    (w.type_label && w.type_label.toLowerCase().includes(q))
                                );
                            },
                            select(opt) {
                                this.selectedId = opt ? String(opt.id) : '';
                                this.open = false;
                                this.search = '';
                                if (!this.isWalletAccount) {
                                    this.selectedWalletId = '';
                                }
                            },
                            selectWallet(w) {
                                this.selectedWalletId = w ? String(w.id) : '';
                                this.walletOpen = false;
                                this.walletSearch = '';
                            },
                            getSelectedTitle() {
                                let found = this.selectedFund;
                                if (!found) return 'انتخاب حساب بانکی یا صندوق...';
                                return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                            },
                            getSelectedWalletTitle() {
                                let found = this.walletOptions.find(w => String(w.id) === String(this.selectedWalletId));
                                if (!found) return 'انتخاب کاربر / مشتری صاحب کیف پول...';
                                return found.name + (found.phone ? ' (' + this.formatFa(found.phone) + ')' : '') + ' - موجودی: ' + this.formatFa(Number(found.balance).toLocaleString('fa-IR')) + ' ریال';
                            },
                            formatFa(str) {
                                if (!str && str !== 0) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }"
                             class="space-y-3 relative transition-all"
                             :class="{ 'z-20': open || walletOpen, 'z-10': !open && !walletOpen }">
                            <div class="space-y-1.5 relative">
                                <label class="{{ $labelClass }}">حساب بانکی / خزانه‌داری طرف حساب <span
                                        class="font-normal text-indigo-500 text-xs">(خزانه/بانک تسویه تفاوت)</span></label>
                                <div class="relative">
                                    <input type="hidden" name="fund_account_id" :value="selectedId" required>
                                    <button type="button" @click="open = !open; walletOpen = false"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </span>
                                            <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedId">
                                                <button type="button" @click.stop="select(null)"
                                                        class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                        title="پاک کردن انتخاب">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search"
                                                   placeholder="جستجو حساب، بانک یا شماره..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>
                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-text="opt.name" class="truncate font-medium"></span>
                                                    <span x-show="opt.bank_name"
                                                          class="text-[11px] text-gray-400 font-medium"
                                                          x-text="'(' + opt.bank_name + ')'"></span>
                                                    <span x-show="opt.account_number"
                                                          class="text-[10px] text-gray-400 font-medium"
                                                          x-text="formatFa(opt.account_number)"></span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <template x-if="opt.is_wallet">
                                                        <span
                                                            class="text-[10px] text-purple-600 dark:text-purple-400 font-medium">کیف پول تجمیعی</span>
                                                    </template>
                                                    <template x-if="!opt.is_wallet && opt.balance > 0">
                                                        <span class="text-[10px] text-gray-400 font-medium"
                                                              x-text="formatFa(Number(opt.balance).toLocaleString('fa-IR')) + ' ریال'"></span>
                                                    </template>
                                                    <span
                                                        class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                        :class="opt.type_badge" x-text="opt.type_label"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">
                                            هیچ حسابی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- User Wallet Sub-Selector --}}
                            <div x-show="isWalletAccount" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                                 class="p-3.5 rounded-2xl bg-gradient-to-br from-purple-50/90 via-indigo-50/40 to-purple-50/70 dark:from-purple-950/30 dark:via-indigo-950/20 dark:to-purple-950/20 border border-purple-200/80 dark:border-purple-800/60 space-y-2 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <label
                                        class="block text-xs font-bold text-purple-900 dark:text-purple-200 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <span>کیف پول کدام کاربر / مشتری؟</span>
                                        <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 font-medium">
                                        تخصیص تسویه به کیف پول اختصاصی
                                    </span>
                                </div>

                                <div class="relative">
                                    <input type="hidden" name="wallet_id" :value="selectedWalletId"
                                           :required="isWalletAccount">
                                    <button type="button" @click="walletOpen = !walletOpen; open = false"
                                            class="w-full rounded-xl border border-purple-200 dark:border-purple-700/80 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-xs hover:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                        <div class="flex items-center gap-2 truncate">
                                            <span
                                                class="flex items-center justify-center w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-300 font-bold text-xs shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </span>
                                            <span x-text="getSelectedWalletTitle()" class="truncate font-medium"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                            <template x-if="selectedWalletId">
                                                <button type="button" @click.stop="selectWallet(null)"
                                                        class="p-1 hover:bg-purple-100 dark:hover:bg-purple-900/50 rounded-md text-purple-400 hover:text-purple-600 dark:hover:text-purple-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <svg class="w-4 h-4 text-purple-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': walletOpen }" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>

                                    <div x-show="walletOpen" @click.outside="walletOpen = false" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                         class="absolute z-[110] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="walletSearch"
                                                   placeholder="جستجو نام کاربر، مشتری یا شماره موبایل..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none">
                                        </div>
                                        <template x-for="w in filteredWalletOptions" :key="w.id">
                                            <div @click="selectWallet(w)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:text-purple-600 dark:hover:text-purple-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 font-bold': String(selectedWalletId) === String(w.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-text="w.name" class="truncate font-medium"></span>
                                                    <span x-show="w.phone" class="text-[10px] text-gray-400 font-medium"
                                                          x-text="'(' + formatFa(w.phone) + ')'"></span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300"
                                                          x-text="formatFa(Number(w.balance).toLocaleString('fa-IR')) + ' ریال'"></span>
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded border font-medium"
                                                          :class="w.type_badge" x-text="w.type_label"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredWalletOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">
                                            هیچ کاربر یا کیف پولی یافت نشد
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between text-[11px] text-purple-700 dark:text-purple-300 pt-0.5">
                                    <span>تسویه در ردیف حسابداری به نام این کاربر درج خواهد شد.</span>
                                    <template x-if="selectedWalletId">
                                        <span
                                            class="font-bold flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            کیف پول متصل شد
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">تاریخ ثبت سند تجمیعی</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="text"
                                       name="document_date"
                                       id="reconciliation_document_date"
                                       data-jdp
                                       data-jdp-only-date
                                       value="{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}"
                                       @click="if(window.jalaliDatepicker){window.jalaliDatepicker.updateOptions({date:true,time:false,hasSecond:false,zIndex:999999});window.jalaliDatepicker.show($el);}"
                                       @focus="if(window.jalaliDatepicker){window.jalaliDatepicker.updateOptions({date:true,time:false,hasSecond:false,zIndex:999999});window.jalaliDatepicker.show($el);}"
                                       class="{{ $inputClass }} pr-11 pl-4 dir-ltr text-center font-bold cursor-pointer"
                                       required
                                       autocomplete="off">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">شرح سند حسابداری</label>
                            <input type="text"
                                   name="description"
                                   value="سند تجمیعی هماهنگ‌سازی و رفع مغایرت سوابق گذشته ریسیلر پیامک"
                                   class="{{ $inputClass }}"
                                   required>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                        <button type="button" @click="showCumulativeModal = false"
                                class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; color: #ffffff !important;"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-95 cursor-pointer">
                            تایید و صدور سند تجمیعی
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    jdp-container {
        z-index: 999999 !important;
    }

    jdp-overlay {
        z-index: 999998 !important;
    }
</style>

@include('smsreseller::partials.confirm-modal')
@include('partials.jalali-date-picker')
<script>
    (function () {
        function initSmsResellerJdp() {
            if (window.jalaliDatepicker) {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp], [data-jdp-only-date]',
                    date: true,
                    time: false,
                    hasSecond: false
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSmsResellerJdp);
        } else {
            initSmsResellerJdp();
        }
        document.addEventListener('focusin', function (e) {
            if (e.target && (e.target.hasAttribute('data-jdp') || e.target.hasAttribute('data-jdp-only-date'))) {
                if (window.jalaliDatepicker) {
                    window.jalaliDatepicker.updateOptions({
                        date: true,
                        time: false,
                        hasSecond: false,
                        zIndex: 999999
                    });
                }
            }
        }, true);
        document.addEventListener('click', function (e) {
            if (e.target && (e.target.hasAttribute('data-jdp') || e.target.hasAttribute('data-jdp-only-date'))) {
                if (window.jalaliDatepicker) {
                    window.jalaliDatepicker.updateOptions({
                        date: true,
                        time: false,
                        hasSecond: false,
                        zIndex: 999999
                    });
                    window.jalaliDatepicker.show(e.target);
                }
            }
        }, true);
    })();
</script>
@endsection
