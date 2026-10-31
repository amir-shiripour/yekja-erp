@php
    $tenantInvoicesList = $tenantInvoices ?? collect();
    $invoicesTotalSum = $invoicesTotalSum ?? (int)$tenantInvoicesList->sum('total');
    $invoicesPaidSum = $invoicesPaidSum ?? (int)$tenantInvoicesList->sum(fn($i) => (int)$i->paid_amount);
    $invoicesUnpaidSum = $invoicesUnpaidSum ?? (int)$tenantInvoicesList->sum(fn($i) => $i->remainingAmount());
    $invoicesCountVal = $invoicesTotalCount ?? (method_exists($tenantInvoicesList, 'total') ? $tenantInvoicesList->total() : $tenantInvoicesList->count());
    $currLabel = $currencyLabel ?? 'تومان';
@endphp

<div class="space-y-4 pt-2">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div class="flex items-center gap-3">
                <span
                    class="p-2.5 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100/80 dark:border-amber-800/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            <div>
                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span>{{ __('resellerhub::messages.subscription.views.total_invoices_section_title') }}</span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">لیست کلیه فاکتورهای صادره از طرف نماینده، ارتقاها و
                    پرداخت‌های آنلاین</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700">
                    <span>{{ __('resellerhub::messages.subscription.views.total_invoices_count_label') }}</span>
                    <strong
                        class="font-black text-gray-900 dark:text-white">{{ reseller_fa_num($invoicesCountVal) }}</strong>
                </span>

            {{-- Invoices Section Toggle Button --}}
            <button type="button"
                    @click="toggleSection('invoices')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200/70 dark:border-gray-700 transition-all cursor-pointer group shadow-2xs">
                <span
                    x-text="sectionInvoicesOpen ? '{{ __('resellerhub::messages.subscription.views.close_section') }}' : '{{ __('resellerhub::messages.subscription.views.open_section') }}'"></span>
                <svg class="w-4 h-4 transition-transform duration-300 ease-out group-hover:scale-110"
                     :class="sectionInvoicesOpen ? 'rotate-180 text-indigo-600 dark:text-indigo-400' : 'text-gray-400'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="sectionInvoicesOpen"
         x-transition:enter="transition ease-out duration-300 origin-top"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-y-95 max-h-0"
         x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave="transition ease-in duration-200 origin-top"
         x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-y-95"
         class="space-y-4">

        {{-- KPI Summary Stats Cards --}}
        @if($tenantInvoicesList->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {{-- 1. Total Invoices --}}
                <div
                    class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3 shadow-2xs">
                    <span
                        class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100/80 dark:border-indigo-800/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <span
                            class="text-xs font-bold text-gray-400 block">{{ __('resellerhub::messages.subscription.views.sum_total_invoices') }}</span>
                        <span
                            class="text-sm font-black text-gray-900 dark:text-white tabular-nums whitespace-nowrap">
                            {{ reseller_fa_num(number_format($invoicesTotalSum)) }}
                            <span class="text-xs font-bold text-gray-400">{{ $currLabel }}</span>
                        </span>
                    </div>
                </div>

                {{-- 2. Total Paid --}}
                <div
                    class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3 shadow-2xs">
                    <span
                        class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100/80 dark:border-emerald-800/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <span
                            class="text-xs font-bold text-gray-400 block">{{ __('resellerhub::messages.subscription.views.sum_paid_invoices') }}</span>
                        <span
                            class="text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                            {{ reseller_fa_num(number_format($invoicesPaidSum)) }}
                            <span class="text-xs font-bold text-emerald-500/70">{{ $currLabel }}</span>
                        </span>
                    </div>
                </div>

                {{-- 3. Outstanding Balance --}}
                <div
                    class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 flex items-center gap-3 shadow-2xs">
                    <span
                        class="w-10 h-10 rounded-xl {{ $invoicesUnpaidSum > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <span
                            class="text-xs font-bold text-gray-400 block">{{ __('resellerhub::messages.subscription.views.sum_unpaid_invoices') }}</span>
                        @if($invoicesUnpaidSum > 0)
                            <span
                                class="text-sm font-black text-amber-600 dark:text-amber-400 tabular-nums whitespace-nowrap">
                                {{ reseller_fa_num(number_format($invoicesUnpaidSum)) }}
                                <span class="text-xs font-bold text-amber-500/70">{{ $currLabel }}</span>
                            </span>
                        @else
                            <span
                                class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ __('resellerhub::messages.invoices.already_settled') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Invoice Cards List --}}
        <div class="space-y-3">
            @forelse($tenantInvoicesList as $inv)
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
                    $invNotes = $inv->notes ?? $inv->terms ?? null;
                    $invMonths = $inv->months ?? ($inv->meta['duration_months'] ?? null);
                    $invPkgName = $inv->package?->name ?? ($inv->meta['to_package_name'] ?? ($inv->meta['package_name'] ?? null));
                @endphp

                <div
                    class="group relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700/70 transition-all duration-200 shadow-2xs">
                    {{-- Edge Status Indicator --}}
                    <span class="absolute inset-y-0 start-0 w-1 {{ $barColor }}"></span>

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
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-gray-400">{{ __('resellerhub::messages.invoices.invoice_number') }}:</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums dir-ltr">
                                    #{{ $inv->invoice_number }}
                                </span>

                                {{-- Status Badge --}}
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
                                    <span class="w-2 h-2 rounded-full {{ $badge['dot'] }}"></span>
                                    <span>{{ $statusLabel }}</span>
                                </span>

                                {{-- Type Badge --}}
                                @if($inv->isUpgrade())
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/40">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        <span>ارتقای پکیج</span>
                                    </span>
                                @elseif($inv->isRenewal() || $inv->isManualRenewal())
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800/40">
                                        <span>تمدید دوره</span>
                                    </span>
                                @endif

                                @if($invPkgName)
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">
                                        {{ $invPkgName }}
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
                                    <span>{{ __('resellerhub::messages.invoices.issue_date') }}:</span>
                                    <strong
                                        class="font-bold text-gray-700 dark:text-gray-300">{{ $invIssueLabel ?: '—' }}</strong>
                                </span>

                                @if($invDueLabel)
                                    <span class="inline-flex items-center gap-1 text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ __('resellerhub::messages.invoices.due_date') }}:</span>
                                        <strong
                                            class="font-bold {{ $inv->isOverdue() ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $invDueLabel }}</strong>
                                    </span>
                                @endif

                                @if($invPaidLabel)
                                    <span class="inline-flex items-center gap-1 text-gray-400">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>پرداخت:</span>
                                        <strong
                                            class="font-bold text-emerald-600 dark:text-emerald-400">{{ $invPaidLabel }}</strong>
                                    </span>
                                @endif

                                @if($invMonths)
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                        دوره {{ reseller_fa_num($invMonths) }} ماهه
                                    </span>
                                @endif
                            </div>

                            @if($invNotes)
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 line-clamp-1 leading-relaxed">
                                    {{ $invNotes }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Right Content: Price & In-Place Pay CTA --}}
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between lg:justify-end gap-5 w-full lg:w-auto lg:shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-100 dark:border-gray-700/60">
                        {{-- Amount & Balance --}}
                        <div class="min-w-[130px] text-start sm:text-end">
                            <span class="text-xs font-bold text-gray-400 block">مبلغ قابل پرداخت:</span>
                            <span
                                class="text-base font-black text-gray-900 dark:text-white tabular-nums whitespace-nowrap">
                                {{ reseller_fa_num(number_format($inv->total)) }}
                                <span class="text-xs font-bold text-gray-400">{{ $currLabel }}</span>
                            </span>
                            @if($isPaid)
                                <span
                                    class="block text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">تسویه شده</span>
                            @elseif($isCanceled)
                                <span class="block text-xs font-bold text-rose-500 mt-0.5">لغو شده</span>
                            @else
                                <span class="block text-xs font-bold text-amber-600 dark:text-amber-400 mt-0.5">در انتظار پرداخت</span>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if(!$isPaid && !$isCanceled && $remaining > 0)
                                <button type="button"
                                        @click="openPaymentModalForInvoice({{ $inv->id }}, '{{ $inv->invoice_number }}', '{{ $invPkgName ?: 'اشتراک سامانه' }}', {{ (int)$remaining }}, '{{ reseller_fa_num(number_format($remaining)) }} {{ $currLabel }}')"
                                        class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>{{ __('resellerhub::messages.invoices.pay') }}</span>
                                </button>

                                <button type="button"
                                        @click="confirmCancelInvoice({{ $inv->id }}, '{{ $inv->invoice_number }}', '{{ $invPkgName ?: 'اشتراک سامانه' }}', '{{ reseller_fa_num(number_format($remaining)) }} {{ $currLabel }}')"
                                        title="{{ __('resellerhub::messages.invoices.cancel_invoice_button') }}"
                                        class="px-3 py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 border border-rose-200/70 dark:border-rose-800/60 rounded-xl text-xs font-bold transition flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span
                                        class="hidden sm:inline">{{ __('resellerhub::messages.invoices.cancel_invoice_button') }}</span>
                                </button>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                    <span>تسویه شده</span>
                                </span>
                            @endif

                            <a href="{{ route('reseller.invoices.print', $inv->id) }}"
                               target="_blank"
                               title="صفحه پرداخت / چاپ فاکتور"
                               class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="flex flex-col items-center justify-center gap-2.5 py-10 text-center rounded-2xl bg-gray-50/60 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800">
                    <span
                        class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-500 dark:text-indigo-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">هنوز فاکتوری برای این سامانه ثبت
                        نشده
                        است</p>
                    <p class="text-xs text-gray-400">با انجام ارتقای پکیج یا تمدید دوره، فاکتورهای مربوطه در این بخش
                        نمایش داده می‌شوند.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($tenantInvoices, 'hasPages') && $tenantInvoices->hasPages())
            <div
                class="px-5 py-3.5 mt-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                        نمایش صفحه {{ reseller_fa_num($tenantInvoices->currentPage()) }} از {{ reseller_fa_num($tenantInvoices->lastPage()) }}
                        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ reseller_fa_num($tenantInvoices->total()) }} فاکتور ثبت‌شده)</span>
                    </span>
                <div>
                    {{ $tenantInvoices->withQueryString()->links('resellerhub::partials.pagination') }}
                </div>
            </div>
        @endif
    </div>
</div>
