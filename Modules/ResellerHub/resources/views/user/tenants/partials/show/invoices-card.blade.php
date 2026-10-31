@php
    use Illuminate\Support\Carbon;
    use Morilog\Jalali\Jalalian;

    $invoicesTotalAmount = (int) $tenantInvoices->sum('total');
    $invoicesPaidAmount = (int) $tenantInvoices->sum(fn($i) => (int)$i->paid_amount);
    $invoicesOutstanding = (int) $tenantInvoices->sum(fn($i) => $i->remainingAmount());
@endphp

{{-- Card: Tenant Invoices --}}
<div
    class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-6 backdrop-blur-xl">

    {{-- Header --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
            <span
                class="p-2.5 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100/80 dark:border-amber-800/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">فاکتورها و سوابق مالی سامانه</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    نمایش {{ reseller_fa_num($tenantInvoices->count()) }} فاکتور اخیر — وضعیت‌ها و مبالغ دقیقاً مطابق با
                    بخش مدیریت فاکتورها
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('reseller.invoices.create') }}?tenant_id={{ $tenant->id }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-xl transition border border-emerald-200/60 dark:border-emerald-800/40">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>صدور فاکتور جدید</span>
            </a>

            <a href="{{ route('reseller.invoices.index') }}?tenant_id={{ $tenant->id }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-xl transition border border-indigo-100/80 dark:border-indigo-800/40">
                <span>مشاهده همه در مدیریت فاکتورها</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Top KPI Summary Stats --}}
    @if($tenantInvoices->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            {{-- 1. Total Invoices Amount --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
                <span
                    class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100/80 dark:border-indigo-800/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 block">جمع کل مبالغ فاکتورها</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums whitespace-nowrap">
                        {{ reseller_fa_num(number_format($invoicesTotalAmount)) }}
                        <span class="text-xs font-bold text-gray-400">{{ $currencyLabel }}</span>
                    </span>
                </div>
            </div>

            {{-- 2. Total Paid Amount --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
                <span
                    class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100/80 dark:border-emerald-800/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 block">مجموع پرداخت‌شده</span>
                    <span
                        class="text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                        {{ reseller_fa_num(number_format($invoicesPaidAmount)) }}
                        <span class="text-xs font-bold text-emerald-500/70">{{ $currencyLabel }}</span>
                    </span>
                </div>
            </div>

            {{-- 3. Outstanding Remaining Balance --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
                <span
                    class="w-10 h-10 rounded-xl {{ $invoicesOutstanding > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 block">مانده بدهی در انتظار پرداخت</span>
                    @if($invoicesOutstanding > 0)
                        <span
                            class="text-sm font-black text-amber-600 dark:text-amber-400 tabular-nums whitespace-nowrap">
                            {{ reseller_fa_num(number_format($invoicesOutstanding)) }}
                            <span class="text-xs font-bold text-amber-500/70">{{ $currencyLabel }}</span>
                        </span>
                    @else
                        <span
                            class="text-sm font-black text-emerald-600 dark:text-emerald-400">تسویه کامل (بدون بدهی)</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Invoices List --}}
    <div class="space-y-3">
        @forelse($tenantInvoices as $inv)
            @php
                $badge = $inv->status_badge;
                $statusLabel = $inv->status_label;
                $remaining = $inv->remainingAmount();
                $isCanceled = $inv->isCancelled();
                $isPaid = $inv->isPaid();

                $barColor = $inv->bar_color;
                $iconBoxClass = $inv->icon_box_class;

                $invDueLabel = $inv->jalali_due_date;
                $invIssueLabel = $inv->jalali_issue_date;
                $invPaidLabel = $inv->jalali_paid_at;

                $invSubtotal = $inv->subtotal ?? null;
                $invDiscount = $inv->discount_amount ?? null;
                $invTax = $inv->tax_amount ?? null;
                $invHasDiscount = $invDiscount !== null && (float) $invDiscount > 0;
                $invHasTax = $invTax !== null && (float) $invTax > 0;
                $invHasSubtotal = $invSubtotal !== null && (float) $invSubtotal > 0 && ((float) $invSubtotal != (float) $inv->total || $invHasDiscount || $invHasTax);
                $invHasBreakdown = $invHasSubtotal || $invHasDiscount || $invHasTax;
                $invMonths = $inv->months ?? $inv->period_months ?? null;
                $invItemsCount = $inv->items?->count();
                $invNotes = $inv->notes ?? $inv->terms ?? null;
            @endphp

            <div
                class="group relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700/70 transition-all duration-200">

                {{-- Colored Indicator Edge --}}
                <span class="absolute inset-y-0 start-0 w-[3.5px] {{ $barColor }}"></span>

                {{-- Left Content: Invoice Info & Badges --}}
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <span
                        class="w-11 h-11 rounded-2xl {{ $iconBoxClass }} flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        {{-- Top Badge Row (1:1 with invoices/index.blade.php) --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-gray-400">فاکتور</span>
                            <a href="{{ route('reseller.invoices.show', $inv->id) }}"
                               class="text-sm font-black text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 tabular-nums dir-ltr transition-colors">
                                {{ $inv->invoice_number }}
                            </a>

                            {{-- Official Invoice Status Badge (1:1 with invoices/index) --}}
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
                                <span class="w-2 h-2 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $statusLabel }}
                            </span>

                            @if($inv->isUpgrade())
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/40">
                                    <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    ارتقای پکیج
                                </span>
                            @elseif($inv->isManualRenewal())
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/40">
                                    <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    تمدید دوره‌ای دستی
                                </span>
                            @elseif($inv->isSystemGenerated() || $inv->isRenewal())
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800/40">
                                    <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    تمدید سیستمی
                                </span>
                            @endif
                        </div>

                        {{-- Dates Row --}}
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                            <span class="inline-flex items-center gap-1 text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                صدور:
                                <span class="font-bold text-gray-700 dark:text-gray-300">
                                    {{ $invIssueLabel ?: '—' }}
                                </span>
                            </span>

                            @if($invDueLabel)
                                <span class="inline-flex items-center gap-1 text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    سررسید:
                                    <span
                                        class="font-bold {{ $inv->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $invDueLabel }}
                                    </span>
                                </span>
                            @endif

                            @if($invPaidLabel)
                                <span class="inline-flex items-center gap-1 text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    تاریخ پرداخت:
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $invPaidLabel }}
                                    </span>
                                </span>
                            @endif
                        </div>

                        {{-- Metadata pills (Months & Items Count) --}}
                        @if($invMonths || $invItemsCount)
                            <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs font-bold">
                                @if($invMonths)
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40">
                                        دوره {{ reseller_fa_num($invMonths) }} ماهه
                                    </span>
                                @endif
                                @if($invItemsCount)
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200/60 dark:border-gray-700/60">
                                        {{ reseller_fa_num($invItemsCount) }} قلم خدمات
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($invNotes)
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 line-clamp-1 leading-relaxed"
                               title="{{ $invNotes }}">
                                {{ \Illuminate\Support\Str::limit($invNotes, 90) }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Right Content: Payment Status Column & Amount (1:1 with invoices/index.blade.php) --}}
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between lg:justify-end gap-5 w-full lg:w-auto lg:shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-200/70 dark:border-gray-700/60">

                    {{-- 1. Payment Status Box (1:1 with invoices/index table column) --}}
                    <div class="text-start sm:text-center min-w-[130px]">
                        <span class="text-xs font-bold text-gray-400 block mb-0.5">وضعیت پرداخت:</span>
                        @if($isCanceled)
                            <span class="font-black text-rose-500 dark:text-rose-400 text-xs">لغو شده</span>
                            <span class="block text-xs font-bold text-gray-400 mt-0.5">امکان پرداخت ندارد</span>
                        @elseif($remaining <= 0 && $inv->total > 0)
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs tabular-nums">
                                {{ reseller_fa_num(number_format($inv->paid_amount)) }}
                                <span class="text-xs font-medium">{{ $currencyLabel }}</span>
                            </span>
                            <span class="block text-xs font-black text-emerald-500 mt-0.5">پرداخت شده</span>
                        @elseif($inv->paid_amount > 0)
                            <span class="font-bold text-blue-600 dark:text-blue-400 text-xs tabular-nums block">
                                پرداختی: {{ reseller_fa_num(number_format($inv->paid_amount)) }}
                            </span>
                            <span
                                class="block text-xs font-bold text-amber-600 dark:text-amber-400 mt-0.5 tabular-nums">
                                مانده: {{ reseller_fa_num(number_format($remaining)) }} {{ $currencyLabel }}
                            </span>
                        @else
                            <span
                                class="font-black text-gray-400 dark:text-gray-500 text-xs tabular-nums block">۰</span>
                            <span class="block text-xs font-black text-amber-600 dark:text-amber-400 mt-0.5">در انتظار پرداخت</span>
                        @endif
                    </div>

                    {{-- 2. Financial Amount Breakdown --}}
                    <div class="min-w-[170px] space-y-1 text-end">
                        @if($invHasSubtotal)
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-gray-400">جمع اقلام:</span>
                                <span
                                    class="font-bold text-gray-600 dark:text-gray-300 tabular-nums">{{ reseller_fa_num(number_format($invSubtotal)) }}</span>
                            </div>
                        @endif
                        @if($invHasDiscount)
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-gray-400">تخفیف:</span>
                                <span
                                    class="font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ reseller_fa_num(number_format($invDiscount)) }}</span>
                            </div>
                        @endif
                        @if($invHasTax)
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-gray-400">مالیات:</span>
                                <span
                                    class="font-bold text-gray-600 dark:text-gray-300 tabular-nums">{{ reseller_fa_num(number_format($invTax)) }}</span>
                            </div>
                        @endif

                        <div
                            class="flex items-center justify-between gap-3 {{ $invHasBreakdown ? 'pt-1.5 mt-1 border-t border-dashed border-gray-200 dark:border-gray-700/70' : '' }}">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">مبلغ نهایی:</span>
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white tabular-nums whitespace-nowrap">
                                {{ reseller_fa_num(number_format($inv->total)) }}
                                <span class="text-xs font-bold text-gray-400">{{ $currencyLabel }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- 3. Action Buttons --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if(!$isPaid && !$isCanceled && $remaining > 0)
                            <a href="{{ route('reseller.invoices.show', $inv->id) }}"
                               class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>ثبت پرداخت / تسویه</span>
                            </a>
                        @endif

                        <a href="{{ route('reseller.invoices.show', $inv->id) }}"
                           title="مشاهده فاکتور"
                           class="w-10 h-10 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:bg-indigo-600 hover:border-indigo-600 transition-all duration-200 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center gap-2.5 py-10 text-center">
                <span
                    class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 flex items-center justify-center text-gray-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <p class="text-sm font-bold text-gray-500 dark:text-gray-400">هنوز فاکتوری برای این سامانه صادر نشده
                    است</p>
                <p class="text-xs text-gray-400">با تمدید لایسنس یا خرید سرویس جدید، فاکتورها در این بخش نمایش داده
                    می‌شوند</p>
            </div>
        @endforelse
    </div>
</div>
