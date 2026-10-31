@php use Modules\SmsReseller\App\Models\SmsResellerDailySummary;
 use Modules\SmsReseller\App\Models\SmsResellerPanelPayment;
 use Modules\SmsReseller\App\Models\SmsResellerUserPayment;
 @endphp
@extends('layouts.user')

@section('title', 'تنظیمات و سرفصل‌های پیش‌فرض حسابداری')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white ps-4 pe-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $lastSync = $lastSync ?? SmsResellerUserPayment::latest('updated_at')->value('updated_at');
    $totalPayments = $totalPayments ?? SmsResellerUserPayment::count();
    $totalPanelCharges = $totalPanelCharges ?? SmsResellerPanelPayment::count();
    $totalSummaries = $totalSummaries ?? SmsResellerDailySummary::count();

    $faNum = function ($num) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string)$num);
    };

    $typeLabels = [
        'income' => 'درآمد',
        'expense' => 'هزینه',
        'asset' => 'دارایی',
        'liability' => 'بدهی / مالیات',
        'equity' => 'حقوق صاحبان سهام',
    ];

    $typeBadges = [
        'income' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
        'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
        'liability' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
        'asset' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
    ];

    $mapCategories = function($categories) use ($typeLabels, $typeBadges) {
        return collect($categories ?? [])->map(function($c) use ($typeLabels, $typeBadges) {
            return [
                'id' => (string) $c->id,
                'title' => (string) $c->title,
                'account_code' => (string) ($c->account_code ?? ''),
                'type' => (string) ($c->type ?? 'income'),
                'type_label' => $typeLabels[$c->type ?? 'income'] ?? ($c->type ?? 'سرفصل'),
                'type_badge' => $typeBadges[$c->type ?? 'income'] ?? 'bg-gray-100 text-gray-700',
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
                'type_label' => $isWallet ? 'کیف پول تجمیعی' : ($fundTypes[$type] ?? $type),
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
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Reusable Real-time Toast Notifications --}}
        @include('smsreseller::partials.toast')

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                    <span>تنظیمات و سرفصل‌های حسابداری ماژول پیامک</span>
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 ms-1">
                    مدیریت کلید دسترسی وب‌سرویس لیمو پیامک و تطبیق خودکار سرفصل‌های دوبل و خزانه‌داری
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.smsreseller.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>پیشخوان پیامک</span>
                </a>
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

        <form action="{{ route('user.smsreseller.settings.save') }}" method="POST" class="space-y-8">
            @csrf

            <div
                class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">تنظیمات اتصال به وب‌سرویس
                                لیمو
                                پیامک</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">کلید دسترسی و آدرس سرور جهت
                                همگام‌سازی اطلاعات و استعلام تعرفه‌ها</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="api_key" class="{{ $labelClass }}">
                            کلید دسترسی (API Key) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="api_key" id="api_key"
                               value="{{ old('api_key', $setting->api_key ?? '64a18d30-e471-4b76-83c5-fb4fa7fefb58') }}"
                               class="{{ $inputClass }} dir-ltr font-mono text-start" required>
                        @error('api_key')
                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="base_url" class="{{ $labelClass }}">
                            آدرس پایه وب‌سرویس (Base URL) <span class="text-rose-500">*</span>
                        </label>
                        <input type="url" name="base_url" id="base_url"
                               value="{{ old('base_url', $setting->base_url ?? 'https://api.limosms.com') }}"
                               class="{{ $inputClass }} dir-ltr font-mono text-start" required>
                        @error('base_url')
                        <p class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-visible">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">سرفصل‌های پیش‌فرض حسابداری
                                جهت
                                صدور خودکار سند</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تنظیم سرفصل‌های دوبل برای درآمد،
                                هزینه، مالیات و حساب‌های خزانه‌داری</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.accounting.categories.index') }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 transition-colors self-start sm:self-auto">
                        <span>مدیریت سرفصل‌ها</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- 1. Default Income Category --}}
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
                            if (!found) return 'انتخاب سرفصل درآمد...';
                            return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                        },
                        formatFa(str) {
                            if (!str && str !== 0) return '';
                            const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                            return String(str).replace(/[0-9]/g, w => farsi[+w]);
                        }
                    }"
                         class="space-y-1.5 relative transition-all"
                         :class="{ 'z-50': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">سرفصل پیش‌فرض درآمد (بستانکار سند واریز کاربر)</label>
                        <div class="relative">
                            <input type="hidden" name="default_income_category_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-emerald-500 focus:border-emerald-500 transition-colors">
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </span>
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                </div>
                                <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                    <template x-if="selectedId">
                                        <button type="button" @click.stop="select(null)"
                                                class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>
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
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search" placeholder="جستجو سرفصل درآمد یا کد..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
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
                                    هیچ سرفصلی پیدا نشد
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Default Expense Category --}}
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
                            if (!found) return 'انتخاب سرفصل هزینه...';
                            return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                        },
                        formatFa(str) {
                            if (!str && str !== 0) return '';
                            const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                            return String(str).replace(/[0-9]/g, w => farsi[+w]);
                        }
                    }"
                         class="space-y-1.5 relative transition-all"
                         :class="{ 'z-50': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">سرفصل پیش‌فرض هزینه (بدهکار سند شارژ پنل)</label>
                        <div class="relative">
                            <input type="hidden" name="default_expense_category_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-rose-500 focus:border-rose-500 transition-colors">
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-lg bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                        </svg>
                                    </span>
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                </div>
                                <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                    <template x-if="selectedId">
                                        <button type="button" @click.stop="select(null)"
                                                class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>
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
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search" placeholder="جستجو سرفصل هزینه یا کد..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none">
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
                                    هیچ سرفصلی پیدا نشد
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Default Tax Category --}}
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
                         :class="{ 'z-50': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">سرفصل مالیات بر ارزش افزوده (سند مرکب)</label>
                        <div class="relative">
                            <input type="hidden" name="default_tax_category_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-amber-500 focus:border-amber-500 transition-colors">
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                    </span>
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                </div>
                                <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                    <template x-if="selectedId">
                                        <button type="button" @click.stop="select(null)"
                                                class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>
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
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search" placeholder="جستجو سرفصل مالیات یا کد..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none">
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
                                    هیچ سرفصلی پیدا نشد
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Default Bank / Fund Account --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ $setting->default_fund_account_id ?? ($defaultFundAccount?->id ?? '') }}',
                        options: @js($fundAccountsList),
                        get selectedFund() {
                            return this.options.find(o => String(o.id) === String(this.selectedId));
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
                        select(opt) {
                            this.selectedId = opt ? String(opt.id) : '';
                            this.open = false;
                            this.search = '';
                        },
                        getSelectedTitle() {
                            let found = this.selectedFund;
                            if (!found) return 'انتخاب حساب بانکی یا درگاه...';
                            return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                        },
                        formatFa(str) {
                            if (!str && str !== 0) return '';
                            const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                            return String(str).replace(/[0-9]/g, w => farsi[+w]);
                        }
                    }"
                         class="space-y-1.5 relative transition-all"
                         :class="{ 'z-50': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">حساب بانکی / درگاه پرداخت واریزی‌ها (خزانه)</label>
                        <div class="relative">
                            <input type="hidden" name="default_fund_account_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-blue-500 focus:border-blue-500 transition-colors">
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                    </span>
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                </div>
                                <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                    <template x-if="selectedId">
                                        <button type="button" @click.stop="select(null)"
                                                class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>
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
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search" placeholder="جستجو حساب، درگاه یا بانک..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                </div>
                                <template x-for="opt in filteredOptions" :key="opt.id">
                                    <div @click="select(opt)"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                         :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedId) === String(opt.id) }">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span x-text="opt.name" class="truncate font-medium"></span>
                                            <span x-show="opt.bank_name" class="text-[10px] text-gray-400 font-medium"
                                                  x-text="'(' + opt.bank_name + ')'"></span>
                                            <span x-show="opt.account_number"
                                                  class="text-[10px] text-gray-400 font-medium"
                                                  x-text="formatFa(opt.account_number)"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <template x-if="opt.is_wallet">
                                                <span
                                                    class="text-[10px] text-purple-600 dark:text-purple-400 font-medium">کیف پول</span>
                                            </template>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border font-medium"
                                                  :class="opt.type_badge" x-text="opt.type_label"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredOptions.length === 0"
                                     class="p-3 text-xs text-gray-400 text-center">
                                    هیچ حساب یا درگاهی پیدا نشد
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Default Wallet Fund Account --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ $setting->default_wallet_fund_account_id ?? ($defaultWalletFundAccount?->id ?? '') }}',
                        options: @js($fundAccountsList),
                        get selectedFund() {
                            return this.options.find(o => String(o.id) === String(this.selectedId));
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
                        select(opt) {
                            this.selectedId = opt ? String(opt.id) : '';
                            this.open = false;
                            this.search = '';
                        },
                        getSelectedTitle() {
                            let found = this.selectedFund;
                            if (!found) return 'انتخاب حساب معادل کیف پول...';
                            return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                        },
                        formatFa(str) {
                            if (!str && str !== 0) return '';
                            const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                            return String(str).replace(/[0-9]/g, w => farsi[+w]);
                        }
                    }"
                         class="space-y-1.5 relative transition-all"
                         :class="{ 'z-50': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">حساب معادل کیف پول سامانه پیامک در خزانه‌داری</label>
                        <div class="relative">
                            <input type="hidden" name="default_wallet_fund_account_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-purple-500 focus:border-purple-500 transition-colors">
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-lg bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </span>
                                    <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                </div>
                                <div class="flex items-center gap-1.5 ms-2 shrink-0">
                                    <template x-if="selectedId">
                                        <button type="button" @click.stop="select(null)"
                                                class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>
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
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search" placeholder="جستجو حساب کیف پول یا درگاه..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none">
                                </div>
                                <template x-for="opt in filteredOptions" :key="opt.id">
                                    <div @click="select(opt)"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:text-purple-600 dark:hover:text-purple-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                         :class="{ 'bg-purple-50/70 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300 font-bold': String(selectedId) === String(opt.id) }">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span x-text="opt.name" class="truncate font-medium"></span>
                                            <span x-show="opt.bank_name" class="text-[10px] text-gray-400 font-medium"
                                                  x-text="'(' + opt.bank_name + ')'"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <template x-if="opt.is_wallet">
                                                <span
                                                    class="text-[10px] text-purple-600 dark:text-purple-400 font-bold bg-purple-100/70 dark:bg-purple-900/50 px-1.5 py-0.5 rounded">کیف پول تجمیعی</span>
                                            </template>
                                            <template x-if="!opt.is_wallet">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded border font-medium"
                                                      :class="opt.type_badge" x-text="opt.type_label"></span>
                                            </template>
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
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                        style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important; color: #ffffff !important;"
                        class="inline-flex items-center gap-2.5 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs sm:text-sm font-black shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 active:scale-95 transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>ذخیره تنظیمات و سرفصل‌ها</span>
                </button>
            </div>
        </form>
    </div>

    @include('smsreseller::partials.confirm-modal')
@endsection
