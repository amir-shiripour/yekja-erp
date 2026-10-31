@php
    use Modules\ResellerHub\App\Enums\InvoiceStatus;use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
    use Modules\ResellerHub\App\Models\ResellerInvoice;
@endphp
@extends('layouts.user')
@section('title', 'مدیریت فاکتورها')

@php
    $currency      = $currency ?? ($settings['currency'] ?? 'toman');
    $currencyLabel = $currencyLabel ?? reseller_currency_label($currency);
    $faNum         = $faNum ?? fn ($str) => reseller_fa_num($str);
    $grandCount = $grandCount ?? 0;
    $grandTotal = $grandTotal ?? 0;
    $grandPaid  = $grandPaid ?? 0;
    $grandDue   = $grandDue ?? 0;

    if (!function_exists('resellerInvoiceSortUrl')) {
        function resellerInvoiceSortUrl($col) {
            $currentCol = request('sort_by', 'latest');
            $currentOrder = strtolower(request('sort_order', 'desc'));

            if ($currentCol === $col . '_asc') {
                $currentCol = $col;
                $currentOrder = 'asc';
            } elseif ($currentCol === $col . '_desc') {
                $currentCol = $col;
                $currentOrder = 'desc';
            }

            $newOrder = ($currentCol === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort_by' => $col, 'sort_order' => $newOrder]);
        }
    }

    if (!function_exists('resellerInvoiceSortIcon')) {
        function resellerInvoiceSortIcon($col) {
            $currentCol = request('sort_by', 'latest');
            $currentOrder = strtolower(request('sort_order', 'desc'));

            if ($currentCol === $col . '_asc') {
                $currentCol = $col;
                $currentOrder = 'asc';
            } elseif ($currentCol === $col . '_desc') {
                $currentCol = $col;
                $currentOrder = 'desc';
            }

            if ($currentCol === $col) {
                if ($currentOrder === 'asc') {
                    return '<svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>';
                } else {
                    return '<svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>';
                }
            }

            return '<svg class="w-3.5 h-3.5 opacity-30 group-hover:opacity-100 transition-opacity inline shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>';
        }
    }
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                مدیریت فاکتورها
            </h1>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('reseller.pricings.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    تعرفه ماژول‌ها
                </a>
                <a href="{{ route('reseller.invoices.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    صدور فاکتور جدید
                </a>
            </div>
        </div>

        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                {{ session('error') }}
            </div>
        @endif

        {{-- Summary strip (real totals across ALL matching invoices, independent of pagination) --}}
        @php
            @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">کل فاکتورهای صادرشده</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($grandCount)) }}</span>
                        <span class="text-xs font-medium text-gray-400 ms-1">فاکتور</span>
                    </div>
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">جمع کل مبالغ فاکتورها</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($grandTotal)) }}</span>
                        <span class="text-xs font-medium text-gray-400 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-emerald-50/60 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 shadow-sm flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">مبالغ پرداخت‌شده</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums break-all">{{ $faNum(number_format($grandPaid)) }}</span>
                        <span class="text-xs font-medium text-emerald-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
            <div
                class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-amber-50/60 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 shadow-sm flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wide block mb-1 truncate">مانده دریافتی (سررسید)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-amber-700 dark:text-amber-400 tabular-nums break-all">{{ $faNum(number_format($grandDue)) }}</span>
                        <span class="text-xs font-medium text-amber-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Form --}}
        <form method="GET" action="{{ route('reseller.invoices.index') }}"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <div class="relative lg:col-span-4">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: زیرمجموعه، فاکتور، موبایل..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-3 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="lg:col-span-2">
                    <select name="status" aria-label="فیلتر وضعیت"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach(InvoiceStatus::cases() as $st)
                            <option
                                value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <select name="source" aria-label="فیلتر روش ثبت"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه روش‌های ثبت</option>
                        <option value="online" @selected(request('source') === 'online')>خرید آنلاین از سایت</option>
                        <option value="manual" @selected(request('source') === 'manual')> ثبت دستی نماینده</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <select name="sort_by" aria-label="مرتب‌سازی"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>
                            جدیدترین (پیش‌فرض)
                        </option>
                        <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                        <option value="issue_date_desc" @selected(request('sort_by') === 'issue_date_desc')>تاریخ صدور
                            (جدیدترین)
                        </option>
                        <option value="issue_date_asc" @selected(request('sort_by') === 'issue_date_asc')>تاریخ صدور
                            (قدیمی‌ترین)
                        </option>
                        <option value="due_date_asc" @selected(request('sort_by') === 'due_date_asc')>سررسید
                            (نزدیک‌ترین)
                        </option>
                        <option value="due_date_desc" @selected(request('sort_by') === 'due_date_desc')>سررسید
                            (دورترین)
                        </option>
                        <option
                            value="amount_desc" @selected(request('sort_by') === 'amount_desc' || request('sort_by') === 'total_desc')>
                            مبلغ فاکتور (بیشترین)
                        </option>
                        <option
                            value="amount_asc" @selected(request('sort_by') === 'amount_asc' || request('sort_by') === 'total_asc')>
                            مبلغ فاکتور (کمترین)
                        </option>
                        <option value="invoice_number_asc" @selected(request('sort_by') === 'invoice_number_asc')>شماره
                            فاکتور (صعودی)
                        </option>
                        <option value="invoice_number_desc" @selected(request('sort_by') === 'invoice_number_desc')>
                            شماره فاکتور (نزولی)
                        </option>
                    </select>
                </div>

                @if(request('sort_order'))
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif

                <div class="lg:col-span-2 flex gap-1.5 justify-end">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer"
                            title="اعمال فیلتر">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>
                    @if(request()->hasAny(['search', 'type', 'status', 'source', 'sort_by', 'sort_order']))
                        <a href="{{ route('reseller.invoices.index') }}" title="پاک کردن فیلترها"
                           class="inline-flex items-center justify-center p-2.5 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            <a href="{{ resellerInvoiceSortUrl('invoice_number') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>شماره فاکتور</span>
                                {!! resellerInvoiceSortIcon('invoice_number') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            <a href="{{ resellerInvoiceSortUrl('subaccount') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>زیرمجموعه</span>
                                {!! resellerInvoiceSortIcon('subaccount') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ resellerInvoiceSortUrl('total') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>مبلغ کل</span>
                                {!! resellerInvoiceSortIcon('total') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ resellerInvoiceSortUrl('payment_status') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>وضعیت پرداخت</span>
                                {!! resellerInvoiceSortIcon('payment_status') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ resellerInvoiceSortUrl('status') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>وضعیت فاکتور</span>
                                {!! resellerInvoiceSortIcon('status') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ resellerInvoiceSortUrl('issue_date') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>تاریخ صدور</span>
                                {!! resellerInvoiceSortIcon('issue_date') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ resellerInvoiceSortUrl('due_date') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>تاریخ سررسید</span>
                                {!! resellerInvoiceSortIcon('due_date') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($invoices as $invoice)
                        @php
                            $remaining  = $invoice->remainingAmount();
                            $isCanceled = $invoice->isCancelled();
                            $badge      = $invoice->status_badge;
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                                   class="font-bold text-indigo-600 dark:text-indigo-400 text-base tabular-nums hover:underline block">
                                    {{ $faNum($invoice->invoice_number) }}
                                </a>
                                @if($invoice->isUpgrade())
                                    <span
                                        class="inline-flex items-center gap-1 mt-2 text-xs font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-500/30">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        ارتقای پکیج
                                    </span>
                                @elseif($invoice->isManualRenewal())
                                    <span
                                        class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-500/30">
                                        <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        تمدید دوره‌ای دستی
                                    </span>
                                @elseif($invoice->isSystemGenerated() || $invoice->isRenewal())
                                    <span
                                        class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-500/30">
                                        <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        تمدید سیستمی
                                    </span>
                                @endif
                                @if($invoice->items->count() > 0)
                                    <span class="text-xs text-gray-400 block mt-1">
                                        {{ $faNum($invoice->items->count()) }} قلم خدمات
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span
                                        class="font-bold text-gray-900 dark:text-white text-base">{{ $invoice->sub_account_name ?: '—' }}</span>
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold {{ $invoice->source_badge }}">
                                        @if($invoice->isOnlinePurchase())
                                            <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                            خرید از سایت
                                        @else
                                            <svg class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            ثبت دستی
                                        @endif
                                    </span>
                                </div>
                                @if($invoice->sub_account_mobile)
                                    <div
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr mt-1 tabular-nums w-fit">{{ $faNum($invoice->sub_account_mobile) }}</div>
                                @elseif($invoice->sub_account_email)
                                    <div
                                        class="text-xs font-medium text-gray-400 mt-1 dir-ltr w-fit">{{ $invoice->sub_account_email }}</div>
                                @endif
                                @if($invoice->tenant)
                                    <div class="mt-1.5">
                                        <a href="{{ route('reseller.tenants.show', $invoice->tenant_id) }}"
                                           class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <span>{{ $invoice->tenant->name }}</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="font-black text-gray-900 dark:text-gray-100 text-base tabular-nums">{{ $faNum(number_format($invoice->total)) }}</span>
                                <span class="text-xs font-medium text-gray-400 block">{{ $currencyLabel }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isCanceled)
                                    <span class="font-black text-rose-500 dark:text-rose-400 text-base tabular-nums">لغو شده</span>
                                    <span
                                        class="block text-xs font-bold text-gray-400 mt-1">امکان پرداخت ندارد</span>
                                @elseif($remaining <= 0 && $invoice->total > 0)
                                    <span
                                        class="font-black text-emerald-600 dark:text-emerald-400 text-base tabular-nums">{{ $faNum(number_format($invoice->paid_amount)) }} <span
                                            class="text-xs font-medium">{{ $currencyLabel }}</span></span>
                                    <span class="block text-xs font-bold text-emerald-500 mt-1">تسویه کامل</span>
                                @elseif($invoice->paid_amount > 0)
                                    <span class="font-bold text-blue-600 dark:text-blue-400 text-base tabular-nums">پرداختی: {{ $faNum(number_format($invoice->paid_amount)) }} <span
                                            class="text-xs font-medium">{{ $currencyLabel }}</span></span>
                                    <span
                                        class="block text-xs font-bold text-amber-600 dark:text-amber-400 mt-1 tabular-nums">مانده: {{ $faNum(number_format($remaining)) }} <span
                                            class="text-xs font-medium">{{ $currencyLabel }}</span></span>
                                @else
                                    <span
                                        class="font-black text-gray-400 dark:text-gray-500 text-base tabular-nums">۰</span>
                                    <span class="block text-xs font-bold text-gray-400 mt-1">در انتظار پرداخت</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
                                    <span class="w-2 h-2 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $invoice->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr whitespace-nowrap tabular-nums">
                                {{ $faNum($invoice->jalali_issue_date ?: '—') }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr whitespace-nowrap tabular-nums">
                                {{ $faNum($invoice->jalali_due_date ?: '—') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-100 sm:opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                    @if($remaining > 0 && !$isCanceled)
                                        <a href="{{ route('reseller.invoices.payment', $invoice->id) }}"
                                           class="p-2.5 rounded-xl text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-all hover:scale-110"
                                           title="ثبت پرداخت">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                                       class="p-2.5 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110"
                                       title="مشاهده">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('reseller.invoices.print', $invoice->id) }}" target="_blank"
                                       class="p-2.5 rounded-xl text-gray-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition-all hover:scale-110"
                                       title="دانلود / چاپ فاکتور">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-24 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div
                                        class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-6 shadow-inner">
                                        <svg class="w-12 h-12 text-indigo-300 dark:text-indigo-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ فاکتوری یافت
                                        نشد</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 text-center leading-relaxed">
                                        شما هنوز هیچ فاکتوری ثبت نکرده‌اید و یا جستجوی شما نتیجه‌ای نداشت.</p>
                                    <div class="flex items-center gap-4">
                                        <a href="{{ route('reseller.invoices.create') }}"
                                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all hover:-translate-y-1">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 4v16m8-8H4"/>
                                            </svg>
                                            ثبت اولین سفارش
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->total() > 0)
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    {{-- Summary & Per-Page Controls --}}
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-medium">
                        <span>
                            نمایش
                            <strong
                                class="font-bold text-gray-900 dark:text-white">{{ $faNum($invoices->firstItem() ?? 1) }}</strong>
                            تا
                            <strong
                                class="font-bold text-gray-900 dark:text-white">{{ $faNum($invoices->lastItem() ?? $invoices->count()) }}</strong>
                            از مجموع
                            <strong
                                class="font-bold text-gray-900 dark:text-white">{{ $faNum($invoices->total()) }}</strong>
                            فاکتور
                        </span>

                        <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>

                        <form method="GET" action="{{ route('reseller.invoices.index') }}"
                              class="inline-flex items-center gap-1.5 m-0">
                            @foreach(request()->except(['per_page', 'page']) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <span class="hidden sm:inline">تعداد در صفحه:</span>
                            <select name="per_page" aria-label="تعداد در صفحه" onchange="this.form.submit()"
                                    class="rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer shadow-2xs">
                                @foreach([10, 15, 25, 50, 100] as $size)
                                    <option
                                        value="{{ $size }}" @selected(request('per_page', 15) == $size)>{{ $faNum($size) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- Pagination Links / Navigation --}}
                    <div>
                        @if($invoices->hasPages())
                            {{ $invoices->withQueryString()->links('resellerhub::partials.pagination') }}
                        @else
                            <div class="inline-flex items-center gap-1.5">
                                <span
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                                <span
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-md shadow-indigo-500/25 select-none">
                                    {{ $faNum(1) }}
                                </span>
                                <span
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
