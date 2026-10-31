<div role="tabpanel" id="tabpanel-details" aria-labelledby="tab-details" x-show="activeTab === 'details'" class="space-y-8">
    <div
        class="grid grid-cols-1 md:grid-cols-2 {{ $order->billing_cycle ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-6">
        <div
            class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="p-2 bg-sky-50 dark:bg-sky-500/10 text-sky-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-gray-400 uppercase">زیرمجموعه</span>
            </div>
            <div class="font-black text-gray-900 dark:text-white text-lg truncate">{{ $clientName }}</div>
            @if($clientPhone)
                <div
                    class="text-sm font-bold text-gray-500 mt-2 dir-ltr tabular-nums truncate text-right">{{ reseller_fa_num($clientPhone) }}</div>
            @endif
            @if($clientEmail)
                <div class="text-xs text-gray-400 mt-1 truncate">{{ $clientEmail }}</div>
            @endif
            @if($clientCompany)
                <div
                    class="mt-2 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 px-2 py-0.5 rounded-md inline-block">
                    {{ $clientCompany }}
                </div>
            @endif
        </div>

        {{-- Card 2: Order Status with Interactive Dropdown (Violet) --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 relative z-20">
            <div class="flex items-center gap-2 mb-4">
                <div class="p-2 bg-violet-50 dark:bg-violet-500/10 text-violet-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-gray-400 uppercase">وضعیت سفارش</span>
            </div>
            <div class="mt-2 relative">
                <button type="button" @click="statusDropdownOpen = !statusDropdownOpen"
                        @click.away="statusDropdownOpen = false"
                        class="w-full inline-flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl text-sm font-black border transition-all hover:opacity-80 focus:ring-4 focus:outline-none cursor-pointer"
                        style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}40; --tw-ring-color: {{ $statusColor }}30">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full animate-pulse"
                              style="background: {{ $statusColor }}"></span>
                        {{ $statusLabel }}
                        </span>
                    <svg class="w-4 h-4 transition-transform duration-200"
                         :class="statusDropdownOpen ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="statusDropdownOpen" x-transition.opacity.duration.200ms
                     style="display: none;"
                     class="absolute z-50 top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden max-h-60 overflow-y-auto ring-1 ring-black ring-opacity-5">
                    @foreach($allStatuses as $st)
                        @php
                            $isCurrent = ($order->status === $st->id) || ($st->id === 'active' && $order->status === 'completed') || ($st->id === 'inactive' && $order->status === 'suspended');
                        @endphp
                        <button type="button"
                                @click="statusDropdownOpen = false; promptStatusChange('{{ $st->id }}', '{{ $st->name }}', '{{ $st->color }}', {{ $isCurrent ? 'true' : 'false' }})"
                                class="w-full text-start px-4 py-3 text-sm font-bold transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between border-b border-gray-50 dark:border-gray-700/50 last:border-0 cursor-pointer"
                                style="color: {{ $st->color }}">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" style="background: {{ $st->color }}"></span>
                                    {{ $st->name }}
                                </span>
                            @if($isCurrent)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div
            class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-gray-400 uppercase">مبلغ نهایی سفارش</span>
            </div>
            <div class="font-black text-gray-900 dark:text-white text-xl tabular-nums">
                {{ reseller_fa_num(number_format($finalServicePrice)) }}
                <span class="text-sm text-gray-400 font-normal">{{ $currencyLabel }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-2">ثابت و غیرقابل تغییر</p>
        </div>

        {{-- Card 4: Renewal Price & Billing Cycle (Amber) --}}
        @if($order->billing_cycle)
            <div
                class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-50 dark:bg-amber-500/10 text-amber-500 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0-0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-black text-gray-400 uppercase">مبلغ تمدید</span>
                    </div>
                    <span
                        class="text-xs font-bold px-2 py-0.5 rounded-md {{ $isRenewalManual ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400' }}">
                            {{ $isRenewalManual ? 'دستی' : 'خودکار' }}
                        </span>
                </div>
                <div class="font-black text-gray-900 dark:text-white text-xl tabular-nums">
                    {{ reseller_fa_num(number_format($calculatedRenewalPrice)) }}
                    <span class="text-sm text-gray-400 font-normal">{{ $currencyLabel }}</span>
                </div>
                <span
                    class="inline-block mt-2 text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-500/20 px-2.5 py-1 rounded-md">
                        دوره {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}
                    </span>
            </div>
        @endif
    </div>

    {{-- Related Invoices Card (Services 1:1) --}}
    @if($relatedInvoices->isNotEmpty())
        <x-resellerhub::card>
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-amber-50 to-transparent dark:from-amber-500/10 flex items-center justify-between flex-wrap gap-3">
                <h3 class="text-lg font-black text-amber-700 dark:text-amber-400 flex items-center gap-3">
                    <div
                        class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    فاکتورهای مرتبط
                </h3>
                <span
                    class="text-sm font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-3 py-1.5 rounded-lg border border-amber-100 dark:border-amber-500/20">
                        {{ reseller_fa_num($relatedInvoices->count()) }} فاکتور
                    </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start border-collapse min-w-[900px]">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-bold text-start">شماره فاکتور</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center">وضعیت</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center">تاریخ صدور</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center">سررسید</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center">مبلغ کل</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center">پرداخت‌شده</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center">مانده</th>
                        <th scope="col" class="px-4 py-3 font-bold text-center"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($relatedInvoices as $inv)
                        @php
                            $invBadge = $inv->status_badge;
                            $invRemaining = $inv->remainingAmount();
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-4 font-black text-gray-900 dark:text-white tabular-nums text-start">
                                {{ reseller_fa_num($inv->invoice_number) }}
                                @if($inv->isUpgrade())
                                    <span
                                        class="block mt-1 text-xs font-bold text-amber-700 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-500/20 w-max">
                                            ارتقای پکیج
                                        </span>
                                @elseif($inv->isItemAddition())
                                    <span
                                        class="block mt-1 text-xs font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-500/20 w-max">
                                            افزودن ماژول / قابلیت
                                        </span>
                                @elseif($inv->isManualRenewal())
                                    <span
                                        class="block mt-1 text-xs font-bold text-cyan-600 bg-cyan-50 dark:bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-100 dark:border-cyan-500/20 w-max">
                                            تمدید دستی
                                        </span>
                                @elseif($inv->isSystemGenerated())
                                    <span
                                        class="block mt-1 text-xs font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-100 dark:border-indigo-500/20 w-max">
                                            سیستمی
                                        </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $invBadge['bg'] }}">
                                        <span class="w-2 h-2 rounded-full {{ $invBadge['dot'] }}"></span>
                                        {{ $inv->status_label }}
                                    </span>
                            </td>
                            <td class="px-4 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400 dir-ltr">
                                {{ $inv->issue_date ? reseller_format_jalali($inv->issue_date) : '—' }}
                            </td>
                            <td class="px-4 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400 dir-ltr">
                                {{ $inv->due_date ? reseller_format_jalali($inv->due_date) : '—' }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold tabular-nums text-gray-800 dark:text-gray-200">
                                {{ reseller_fa_num(number_format($inv->total)) }}
                                <span class="text-xs text-gray-400 ms-1">{{ $currencyLabel }}</span>
                            </td>
                            <td class="px-4 py-4 text-center font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                                {{ reseller_fa_num(number_format($inv->paid_amount)) }}
                                <span class="text-xs text-emerald-400/80 ms-1">{{ $currencyLabel }}</span>
                            </td>
                            <td class="px-4 py-4 text-center font-black tabular-nums {{ $invRemaining > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                                @if($invRemaining > 0)
                                    {{ reseller_fa_num(number_format($invRemaining)) }}
                                    <span class="text-xs font-normal ms-1">{{ $currencyLabel }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('reseller.invoices.show', $inv->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 font-bold text-xs hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors">
                                    مشاهده فاکتور
                                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($relatedInvoices->count() > 1)
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 divide-y sm:divide-y-0 sm:divide-x sm:divide-x-reverse divide-gray-200 dark:divide-gray-700">
                    <div class="flex flex-col items-center px-4">
                        <span
                            class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1">جمع کل فاکتورها</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span
                                class="text-lg font-black text-gray-900 dark:text-white tabular-nums">{{ reseller_fa_num(number_format($totalInvoicesAmount)) }}</span>
                            <span
                                class="text-xs font-medium text-gray-900 dark:text-gray-300">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-center px-4 pt-4 sm:pt-0">
                            <span
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1">مجموع پرداختی</span>
                        <div class="flex items-baseline gap-1 mt-1">
                                <span
                                    class="text-lg font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ reseller_fa_num(number_format($totalInvoicesPaid)) }}</span>
                            <span
                                class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-center px-4 pt-4 sm:pt-0">
                            <span
                                class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1">مجموع مانده بدهی</span>
                        <div class="flex items-baseline gap-1 mt-1">
                                <span
                                    class="text-lg font-black text-rose-700 dark:text-rose-400 tabular-nums">{{ reseller_fa_num(number_format($totalInvoicesRemaining)) }}</span>
                            <span
                                class="text-xs font-medium text-rose-700 dark:text-rose-400">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </x-resellerhub::card>
    @else
        {{-- Empty State (Services 1:1) --}}
        <x-resellerhub::card>
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-amber-50/50 to-transparent dark:from-amber-500/10 flex items-center justify-between flex-wrap gap-3">
                <h3 class="text-lg font-black text-amber-700 dark:text-amber-400 flex items-center gap-3">
                    <div
                        class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    فاکتورهای مرتبط
                </h3>
            </div>

            <div class="p-8 md:p-12 flex flex-col items-center justify-center text-center">
                <div
                    class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-500 dark:text-amber-400 flex items-center justify-center mb-4 border border-amber-100 dark:border-amber-500/20 shadow-xs">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/>
                    </svg>
                </div>
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-amber-50/80 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/40 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        فاکتوری برای این سفارش موجود نیست
                    </span>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                    هیچ فاکتور مرتبطی به این سفارش متصل نشده است.
                </p>
            </div>
        </x-resellerhub::card>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-8">
            <x-resellerhub::card>
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        اطلاعات اشتراک و پکیج نمایندگی
                    </h3>
                    @if($availablePackages->count() > 1)
                        <button type="button"
                                @click="activeTab = 'package_upgrade'"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black text-amber-700 dark:text-amber-300 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 transition shadow-sm cursor-pointer">
                            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            ارتقای پکیج (Upgrade)
                        </button>
                    @endif
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div
                            class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <span class="block text-xs font-bold text-gray-400 mb-2">پکیج / طرح فعال</span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-black text-gray-900 dark:text-white text-lg">
                                    {{ $package?->name ?? 'پکیج سفارشی (انتخابی)' }}
                                    </span>
                                @if($package)
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 px-2.5 py-1 rounded-lg">
                                            پکیج نمایندگی
                                        </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-2.5 py-1 rounded-lg">
                                            طرح سفارشی
                                        </span>
                                @endif
                            </div>
                        </div>

                        <div
                            class="bg-amber-50/50 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-800/30">
                            <span class="block text-xs font-bold text-amber-500 dark:text-amber-400 mb-2">مبلغ پایه اشتراک</span>
                            <div class="font-black text-gray-900 dark:text-white text-xl tabular-nums mb-2">
                                {{ reseller_fa_num(number_format($order->total_amount)) }}
                                <span class="text-xs text-gray-500 font-normal">{{ $currencyLabel }}</span>
                            </div>
                            <p class="text-[10.5px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                این مبلغ برای
                                دوره {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}
                                محاسبه گردیده است.
                            </p>
                        </div>
                    </div>

                    {{-- Modules Snapshot --}}
                    @php
                        $modulesList = $order->modules_snapshot ?? [];
                        $titlesMap = $moduleTitles ?? [];
                        if (empty($modulesList)) {
                            $pkg = $order->tenant?->package ?: ($order->invoice?->package ?: null);
                            $fallbackMods = $order->tenant?->modules ?: ($pkg?->modules ?? []);
                            if (!empty($fallbackMods) && is_array($fallbackMods)) {
                                $modulesList = collect($fallbackMods)->map(function($mSlug) use ($titlesMap) {
                                    $t = $titlesMap[$mSlug] ?? ($titlesMap[strtolower($mSlug)] ?? $mSlug);
                                    return [
                                        'type' => 'module',
                                        'slug' => $mSlug,
                                        'title' => $t,
                                        'duration_months' => 12,
                                    ];
                                })->all();
                            }
                        }
                    @endphp
                    @if(!empty($modulesList))
                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    ماژول‌ها و دسترسی‌های فعال شده
                                </h4>
                                <span
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-100 dark:border-indigo-500/20">
                                        {{ reseller_fa_num(count($modulesList)) }} ماژول و قابلیت
                                    </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($modulesList as $item)
                                    @php
                                        $slug = is_array($item) ? ($item['slug'] ?? $item['key'] ?? '') : (string)$item;
                                        $rawTitle = is_array($item) ? ($item['title'] ?? '') : '';
                                        $itemTitle = ($rawTitle && $rawTitle !== $slug)
                                            ? $rawTitle
                                            : ($titlesMap[$slug] ?? ($titlesMap[strtolower($slug)] ?? ($rawTitle ?: $slug)));
                                        $itemTypeLabel = is_array($item) && ($item['type'] ?? '') === 'feature' ? 'قابلیت اختصاصی' : 'ماژول فعال در اشتراک';
                                    @endphp
                                    <div
                                        class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                            <span
                                                class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                        <div class="truncate">
                                                <span
                                                    class="text-xs font-black text-gray-900 dark:text-white block truncate">{{ $itemTitle }}</span>
                                            <span
                                                class="text-xs text-gray-400 block truncate">{{ $itemTypeLabel }} @if($slug)
                                                    ({{ $slug }})
                                                @endif</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50">
                        <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            مشخصات سامانه اختصاصی متصل (CRM)
                        </h4>
                        @if($tenant)
                            <div
                                class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">نام سامانه:</span>
                                    <span
                                        class="font-black text-sm text-gray-900 dark:text-white">{{ $tenant->name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">دامنه / ساب‌دامین:</span>
                                    <span
                                        class="font-bold text-xs text-indigo-600 dark:text-indigo-400 dir-ltr select-all">
                                            {{ $tenant->custom_domain ?: (request()->getHttpHost() . '/' . $tenant->subdomain) }}
                                        </span>
                                </div>
                                <div
                                    class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <span class="text-xs text-gray-400">وضعیت تحویل:</span>
                                    @if($order->isProvisioned())
                                        <span
                                            class="text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-500/20">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>{{ $order->provisioning_label }}</span>
                                            </span>
                                    @else
                                        <div class="flex items-center gap-2">
                                                <span
                                                    class="font-bold text-xs {{ $order->provisioning_status === 'failed' ? 'text-rose-500' : 'text-amber-500' }}">
                                                    {{ $order->provisioning_label }}
                                                </span>
                                            <form action="{{ route('reseller.orders.provision', $order->id) }}"
                                                  method="POST" class="m-0">
                                                @csrf
                                                <button type="submit"
                                                        class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                                    تحویل فوری سفارش
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div
                                class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 text-center text-xs text-gray-400">
                                سامانه اختصاصی هنوز به این سفارش متصل نشده است.
                            </div>
                        @endif
                    </div>
                </div>
            </x-resellerhub::card>
        </div>

        <div class="space-y-8">
            @if($order->billing_cycle)
                <form action="{{ route('reseller.orders.update', $order->id) }}" method="POST"
                      class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl transition-all duration-300">
                    @csrf
                    @method('PUT')
                    <div class="p-6 sm:p-8 space-y-8">
                        <div
                            class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                                <div
                                    class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                مدیریت مالی و تمدید
                            </h3>
                            <div
                                class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer select-none"
                                @click="editMode = !editMode">
                                    <span class="text-sm font-black text-gray-600 dark:text-gray-300"
                                          :class="editMode ? 'text-indigo-600 dark:text-indigo-400' : ''">حالت ویرایش</span>
                                <button type="button"
                                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 pointer-events-none"
                                        :class="editMode ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            :class="editMode ? '-translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        تاریخ تمدید (سررسید)
                                    </label>
                                    <input type="text" name="renewal_date"
                                           value="{{ $order->renewal_date ? reseller_format_jalali($order->renewal_date, 'Y/m/d', false) : ($order->end_date ? reseller_format_jalali($order->end_date, 'Y/m/d', false) : '') }}"
                                           :data-jdp="editMode ? '' : null" :data-jdp-only-date="editMode ? '' : null"
                                           :readonly="!editMode"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 dir-ltr text-left"
                                           :class="!editMode ? 'bg-gray-100 dark:bg-gray-800/80 cursor-not-allowed text-gray-500' : 'cursor-pointer'"
                                           placeholder="انتخاب تاریخ">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        دوره صورتحساب
                                    </label>
                                    <select name="billing_cycle" aria-label="دوره پرداخت" :disabled="!editMode"
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 text-sm font-bold"
                                            :class="!editMode ? 'bg-gray-100 dark:bg-gray-800/80 cursor-not-allowed text-gray-500' : ''">
                                        <option
                                            value="monthly" {{ $order->billing_cycle === 'monthly' ? 'selected' : '' }}>
                                            ماهانه (۱ ماهه)
                                        </option>
                                        <option
                                            value="quarterly" {{ $order->billing_cycle === 'quarterly' ? 'selected' : '' }}>
                                            فصلی (۳ ماهه)
                                        </option>
                                        <option
                                            value="semi_annual" {{ $order->billing_cycle === 'semi_annual' ? 'selected' : '' }}>
                                            شش‌ماهه (۶ ماهه)
                                        </option>
                                        <option
                                            value="yearly" {{ $order->billing_cycle === 'yearly' || empty($order->billing_cycle) ? 'selected' : '' }}>
                                            سالانه (۱۲ ماهه)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">
                                            مبلغ تمدید دوره‌ای
                                        </label>
                                        <input type="hidden" name="renewal_price_type" :value="renewalPriceType">
                                        <button type="button" x-show="editMode"
                                                @click="renewalPriceType = (renewalPriceType === 'auto' ? 'manual' : 'auto')"
                                                class="px-2.5 py-1 text-xs font-bold rounded-lg transition-colors border cursor-pointer"
                                                :class="renewalPriceType === 'manual' ? 'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30' : 'bg-indigo-100 text-indigo-700 border-indigo-300 dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/30'"
                                                x-text="renewalPriceType === 'manual' ? 'حالت: دستی' : 'حالت: خودکار'"></button>
                                        <span x-show="!editMode" class="text-xs font-bold px-2 py-0.5 rounded-md"
                                              :class="renewalPriceType === 'manual' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300'"
                                              x-text="renewalPriceType === 'manual' ? 'دستی' : 'خودکار'"></span>
                                    </div>
                                    <div class="relative">
                                        <x-resellerhub::input class="font-bold tabular-nums dir-ltr pl-14 text-left"
                                                              type="text" x-model="renewalPrice"
                                                              @input="formatRenewalPrice($event)"
                                                              name="renewal_price"
                                                              x-bind:readonly="!editMode || renewalPriceType === 'auto'"
                                                              x-bind:class="(!editMode || renewalPriceType === 'auto') ? 'bg-gray-100 dark:bg-gray-800/80 cursor-not-allowed text-gray-500' : ''"
                                                              dir="ltr" placeholder="مبلغ..."/>
                                        <span
                                            class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500 text-sm font-bold">
                                            {{ $currencyLabel }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    یادداشت‌های سفارش
                                </label>
                                <x-resellerhub::textarea class="text-sm font-medium" name="notes" rows="2"
                                                         placeholder="یادداشت‌های داخلی سفارش..."
                                                         x-bind:readonly="!editMode"
                                                         x-bind:class="!editMode ? 'bg-gray-100 dark:bg-gray-800/80 cursor-not-allowed text-gray-500' : ''">{{ $order->notes }}</x-resellerhub::textarea>
                            </div>

                            <div x-show="editMode" x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="pt-6 border-t border-gray-100 dark:border-gray-700">
                                <button type="submit"
                                        class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    ذخیره تغییرات
                                </button>
                            </div>
                        </div>
                </form>

                <x-resellerhub::card
                    class="border border-indigo-100 dark:border-indigo-500/20 shadow-md relative overflow-hidden"
                >
                    <div
                        class="absolute -top-12 -left-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div
                        class="p-6 sm:p-7 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-indigo-50/70 via-transparent to-transparent dark:from-indigo-500/10 flex items-center justify-between flex-wrap gap-3">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-600 text-white rounded-xl shadow-md shadow-indigo-500/30">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            صدور دستی فاکتور تمدید
                        </h3>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400">
                                دوره {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}
                            </span>
                    </div>
                    <div class="p-6 sm:p-7 space-y-6">
                        <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 leading-relaxed">
                            با کلیک بر روی دکمه زیر، بلافاصله یک فاکتور تمدید مجزا با تاریخ سررسید فعلی صادر می‌شود
                            و تاریخ سررسید تمدید این سفارش به صورت خودکار به دوره بعدی منتقل می‌گردد تا از صدور مجدد
                            فاکتور توسط کرون‌جاب خودکار جلوگیری شود.
                        </p>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div>
                                <span class="text-xs font-bold text-gray-400 block mb-1">تاریخ صدور:</span>
                                <span
                                    class="font-black text-gray-900 dark:text-white text-sm tabular-nums dir-ltr text-right inline-block">
                                        {{ $order->renewal_date ? reseller_format_jalali($order->renewal_date) : 'امروز' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-400 block mb-1">سررسید فاکتور:</span>
                                    <span
                                        class="font-black text-emerald-600 dark:text-emerald-400 text-sm tabular-nums dir-ltr text-right inline-block">
                                        {{ $nextRenewalDateCalculated ? reseller_fa_num($nextRenewalDateCalculated) : '—' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-400 block mb-1">مبلغ تمدید:</span>
                                    <span
                                        class="font-black text-indigo-600 dark:text-indigo-400 text-base tabular-nums">
                                        {{ reseller_fa_num(number_format($calculatedRenewalPrice)) }}
                                        <span
                                            class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                                    </span>
                                </div>
                            </div>

                            <button type="button"
                                    @click="openRenewalModal = true"
                                    class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black text-sm shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                صدور فوری فاکتور تمدید
                            </button>
                        </div>
                </x-resellerhub::card>
            @endif



            @if($invoice?->notes || $order->notes)
                <x-resellerhub::card class="p-6 border-l-4 border-l-amber-500 bg-amber-50/50 dark:bg-amber-900/10">
                    <div class="flex items-start gap-4 text-amber-800 dark:text-amber-300">
                        <svg class="w-8 h-8 shrink-0 mt-0.5 opacity-80" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <div>
                            <strong class="block mb-2 font-black text-lg tracking-tight">یادداشت سفارش</strong>
                            <p class="font-medium text-base leading-loose">{{ $order->notes ?: $invoice?->notes }}</p>
                        </div>
                    </div>
                </x-resellerhub::card>
            @endif
        </div>
    </div>
</div>
