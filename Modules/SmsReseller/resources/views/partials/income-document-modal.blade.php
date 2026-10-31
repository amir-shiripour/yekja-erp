@php
    use Modules\Wallet\App\Models\Wallet;$labelClass = $labelClass ?? "block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5";
    $inputClass = $inputClass ?? "w-full rounded-xl border border-gray-200 bg-white ps-4 pe-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

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
    $liabilityCategoriesList = $mapCategories($liabilityCategories ?? []);
    $fundAccountsList = $mapFunds($fundAccounts ?? []);

    if (!isset($userWalletsList)) {
        if (class_exists(Wallet::class)) {
            try {
                $userWalletsList = Wallet::with('holder')
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
            } catch (Throwable $e) {
                $userWalletsList = [];
            }
        } else {
            $userWalletsList = [];
        }
    }
@endphp

<div x-show="showIncomeModal" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="income-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="showIncomeModal = false">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="showIncomeModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showIncomeModal = false"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="showIncomeModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-visible rounded-3xl bg-white dark:bg-gray-800 text-start shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700/80 space-y-6">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/70 to-indigo-50/50 dark:from-emerald-950/30 dark:to-indigo-950/30 flex items-center justify-between rounded-t-3xl">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white" id="income-modal-title">
                            صدور سند حسابداری درآمد شارژ پیامک
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            کاربر: <strong class="text-emerald-600 dark:text-emerald-400 font-bold"
                                           x-text="userName"></strong>
                            <span class="text-gray-300 dark:text-gray-600 mx-1">|</span>
                            <span>شناسه پرداخت:</span>
                            <span class="font-bold text-gray-700 dark:text-gray-300" x-text="'#' + paymentId"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showIncomeModal = false"
                        class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('user.smsreseller.record_user_income_document') }}"
                  class="p-6 pt-0 space-y-5">
                @csrf
                <input type="hidden" name="payment_id" :value="paymentId">
                <input type="hidden" name="user_name" :value="userName">
                <input type="hidden" name="amount" :value="amount">
                <input type="hidden" name="tax" :value="tax">
                <input type="hidden" name="total_amount" :value="totalAmount">
                <div
                    class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 space-y-2.5 border border-gray-200/80 dark:border-gray-700 text-xs">
                    <div class="flex items-center justify-between text-gray-900 dark:text-white font-black">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            ۱. بدهکار (بانک / درگاه دریافت‌کننده):
                        </span>
                        <span class="text-sm font-black"
                              x-text="Number(totalAmount).toLocaleString('fa-IR') + ' ریال'"></span>
                    </div>
                    <div
                        class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 font-bold pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            ۲. بستانکار (درآمد حاصل از فروش پیامک):
                        </span>
                        <span class="text-sm font-black"
                              x-text="Number(amount).toLocaleString('fa-IR') + ' ریال'"></span>
                    </div>
                    <div x-show="tax > 0"
                         class="flex items-center justify-between text-amber-600 dark:text-amber-400 font-bold pt-1.5">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            ۳. بستانکار (مالیات بر ارزش افزوده):
                        </span>
                        <span class="text-sm font-black" x-text="Number(tax).toLocaleString('fa-IR') + ' ریال'"></span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ $defaultFundAccount?->id ?? '' }}',
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
                            } else {
                                this.tryAutoMatchWallet();
                            }
                        },
                        selectWallet(w) {
                            this.selectedWalletId = w ? String(w.id) : '';
                            this.walletOpen = false;
                            this.walletSearch = '';
                        },
                        getSelectedTitle() {
                            let found = this.selectedFund;
                            if (!found) return 'انتخاب حساب بانکی یا صندوق دریافتی...';
                            return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                        },
                        getSelectedWalletTitle() {
                            let found = this.walletOptions.find(w => String(w.id) === String(this.selectedWalletId));
                            if (!found) return 'انتخاب کاربر / مشتری صاحب کیف پول...';
                            return found.name + (found.phone ? ' (' + this.formatFa(found.phone) + ')' : '') + ' - موجودی: ' + this.formatFa(Number(found.balance).toLocaleString('fa-IR')) + ' ریال';
                        },
                        tryAutoMatchWallet() {
                            if (this.selectedWalletId) return;
                            if (typeof userName !== 'undefined' && userName) {
                                const cleanUser = String(userName).trim().replace(/\D/g, '');
                                const matched = this.walletOptions.find(w => {
                                    const cleanPhone = String(w.phone || '').replace(/\D/g, '');
                                    return (cleanPhone && cleanUser && (cleanPhone.includes(cleanUser) || cleanUser.includes(cleanPhone))) ||
                                           (w.name && String(userName).includes(w.name));
                                });
                                if (matched) {
                                    this.selectedWalletId = String(matched.id);
                                }
                            }
                        },
                        init() {
                            if (this.isWalletAccount) {
                                this.$nextTick(() => this.tryAutoMatchWallet());
                            }
                            this.$watch('showIncomeModal', (val) => {
                                if (val && this.isWalletAccount) {
                                    this.$nextTick(() => this.tryAutoMatchWallet());
                                }
                            });
                        },
                        formatFa(str) {
                            if (!str && str !== 0) return '';
                            const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                            return String(str).replace(/[0-9]/g, w => farsi[+w]);
                        }
                    }"
                         class="space-y-3 relative transition-all"
                         :class="{ 'z-40': open || walletOpen, 'z-10': !open && !walletOpen }">
                        <div class="space-y-1.5 relative">
                            <label class="{{ $labelClass }}">حساب خزانه‌داری / درگاه دریافت‌کننده <span
                                    class="font-normal text-gray-500 text-xs">(سمت بدهکار سند)</span></label>
                            <div class="relative">
                                <input type="hidden" name="fund_account_id" :value="selectedId" required>
                                <button type="button" @click="open = !open; walletOpen = false"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                    <div class="flex items-center gap-2 truncate">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor"
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
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
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
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="search" placeholder="جستجو حساب، بانک یا شماره..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>
                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <div @click="select(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedId) === String(opt.id) }">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span x-text="opt.name" class="truncate font-medium"></span>
                                                <span x-show="opt.bank_name"
                                                      class="text-[10px] text-gray-400 font-medium"
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
                                                <span class="text-[10px] px-1.5 py-0.5 rounded border font-medium"
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
                                    تخصیص سند به کیف پول اختصاصی
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
                                <span>تراکنش در ردیف حسابداری به نام این کاربر درج خواهد شد.</span>
                                <template x-if="selectedWalletId">
                                    <span
                                        class="font-bold flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        کیف پول متصل شد
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ $defaultIncomeCat?->id ?? '' }}',
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
                         :class="{ 'z-30': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">سرفصل درآمد فروش پیامک <span
                                class="font-normal text-gray-500 text-xs">(سمت بستانکار سند)</span></label>
                        <div class="relative">
                            <input type="hidden" name="income_category_id" :value="selectedId" required>
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
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
                                    هیچ سرفصلی پیدا نشد
                                </div>
                            </div>
                        </div>
                    </div>
                    <div x-show="tax > 0"
                         x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ $defaultTaxCat?->id ?? '' }}',
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
                                if (!found) return 'انتخاب سرفصل مالیات (بدهی)...';
                                return (found.account_code ? this.formatFa(found.account_code) + ' - ' : '') + found.title;
                            },
                            formatFa(str) {
                                if (!str && str !== 0) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }"
                         class="space-y-1.5 relative transition-all"
                         :class="{ 'z-20': open, 'z-10': !open }">
                        <label class="{{ $labelClass }}">سرفصل مالیات بر ارزش افزوده <span
                                class="font-normal text-gray-500 text-xs">(سمت بستانکار سند - بدهی جاری)</span></label>
                        <div class="relative">
                            <input type="hidden" name="tax_category_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start shadow-sm hover:border-indigo-500 transition-colors">
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
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
                                    هیچ سرفصلی پیدا نشد
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">تاریخ صدور سند حسابداری</label>
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
                                   id="income_document_date"
                                   data-jdp
                                   data-jdp-only-date
                                   :value="docDate"
                                   @input="docDate = $event.target.value"
                                   @change="docDate = $event.target.value"
                                   @jdp:change="docDate = $event.target.value"
                                   @click="if(window.jalaliDatepicker){window.jalaliDatepicker.updateOptions({date:true,time:false,hasSecond:false,zIndex:999999});window.jalaliDatepicker.show($el);}"
                                   @focus="if(window.jalaliDatepicker){window.jalaliDatepicker.updateOptions({date:true,time:false,hasSecond:false,zIndex:999999});window.jalaliDatepicker.show($el);}"
                                   class="{{ $inputClass }} pr-11 pl-4 dir-ltr text-center font-bold cursor-pointer"
                                   required
                                   autocomplete="off">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">شرح و توضیحات سند</label>
                        <input type="text"
                               name="description"
                               x-model="description"
                               class="{{ $inputClass }}"
                               required>
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" @click="showIncomeModal = false"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit"
                            style="background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; color: #ffffff !important;"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all active:scale-95 cursor-pointer">
                        تایید و صدور سند درآمد
                    </button>
                </div>
            </form>
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

@includeIf('partials.jalali-date-picker')
