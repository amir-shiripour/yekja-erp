@php
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;
@endphp
@extends('layouts.user')
@section('title', 'مدیریت سفارشات')

@php
    $resellerId    = $reseller->id ?? (auth()->user()?->reseller?->id ?? 0);
    $currency      = $currency ?? ($settings['currency'] ?? ResellerSetting::getCurrencyForReseller($resellerId));
    $currencyLabel = $currencyLabel ?? reseller_currency_label($currency);
    $faNum         = $faNum ?? fn ($str) => reseller_fa_num($str);
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-8">

        {{-- Header (Single-row on Mobile & Desktop matching Invoices) --}}
        <div class="flex items-center justify-between gap-1.5 sm:gap-4">
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                <span
                    class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white shadow-sm sm:shadow-md shadow-indigo-500/25 shrink-0">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </span>
                <h1 class="text-[13px] sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight whitespace-nowrap">
                    مدیریت سفارشات
                </h1>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <a href="{{ route('reseller.invoices.index') }}"
                   class="h-8.5 sm:h-11 inline-flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-5 rounded-lg sm:rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold text-[11px] sm:text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-all whitespace-nowrap shrink-0"
                   title="مدیریت فاکتورها">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>فاکتورها</span>
                </a>
                <a href="{{ route('reseller.invoices.create') }}"
                   class="h-8.5 sm:h-11 inline-flex items-center justify-center gap-1 sm:gap-2 px-2.5 sm:px-6 rounded-lg sm:rounded-xl bg-indigo-600 text-white font-bold text-[11px] sm:text-sm shadow-sm sm:shadow-md shadow-indigo-500/25 hover:bg-indigo-700 transition-all active:scale-95 whitespace-nowrap shrink-0"
                   title="سفارش جدید">
                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>سفارش جدید</span>
                </a>
            </div>
        </div>

        {{-- Alerts --}}
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

        {{-- Summary strip (2-Col Mobile / 4-Col Desktop consistent with Invoices) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-6">
            {{-- Card 1: Total Orders --}}
            <div
                class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-3.5 sm:p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1 w-full">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-wide block mb-0.5 sm:mb-1 truncate">کل سفارشات</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-base sm:text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($totalOrders ?? 0)) }}</span>
                        <span class="text-[10px] sm:text-xs font-medium text-gray-400 ms-0.5">سفارش</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Active Orders --}}
            <div
                class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-3.5 sm:p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1 w-full">
                    <span
                        class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-wide block mb-0.5 sm:mb-1 truncate">سفارشات فعال</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-base sm:text-xl xl:text-2xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums break-all">{{ $faNum(number_format($activeOrders ?? 0)) }}</span>
                        <span class="text-[10px] sm:text-xs font-medium text-gray-400 ms-0.5">فعال</span>
                    </div>
                </div>
            </div>

            {{-- Card 3: Pending Orders --}}
            <div
                class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-3.5 sm:p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1 w-full">
                    <span
                        class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-wide block mb-0.5 sm:mb-1 truncate">در انتظار پرداخت</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-base sm:text-xl xl:text-2xl font-black text-amber-600 dark:text-amber-400 tabular-nums break-all">{{ $faNum(number_format($pendingOrders ?? 0)) }}</span>
                        <span class="text-[10px] sm:text-xs font-medium text-gray-400 ms-0.5">در انتظار</span>
                    </div>
                </div>
            </div>

            {{-- Card 4: Total Successful Sales / Revenue --}}
            <div
                class="relative overflow-hidden rounded-2xl sm:rounded-3xl p-3.5 sm:p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/60 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 shrink-0">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1 w-full">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-wide block mb-0.5 sm:mb-1 truncate">گردش مالی موفق</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-base sm:text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($totalSales ?? 0)) }}</span>
                        <span class="text-[10px] sm:text-xs font-medium text-gray-400 ms-0.5">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter bar (Compact 2-Col Grid on Mobile matching Invoices) --}}
        <form method="GET" action="{{ route('reseller.orders.index') }}"
              class="bg-white dark:bg-gray-800/80 p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-sm">
            <div class="grid grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3 items-center">
                <div class="col-span-2 lg:col-span-3 relative">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: کاربر زیرمجموعه، شماره سفارش، سامانه..."
                           class="w-full rounded-xl sm:rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-10 pe-3 py-2.5 text-xs sm:text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="col-span-1 lg:col-span-2">
                    <select name="status" aria-label="فیلتر وضعیت"
                            class="w-full rounded-xl sm:rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}" @selected(request('status') == $st->id)>
                                {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1 lg:col-span-2">
                    <select name="package_id" aria-label="انتخاب طرح و پکیج"
                            class="w-full rounded-xl sm:rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه پکیج‌ها</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" @selected(request('package_id') == $pkg->id)>
                                {{ $pkg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1 lg:col-span-2">
                    <select name="source" aria-label="فیلتر روش ثبت"
                            class="w-full rounded-xl sm:rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه روش‌ها</option>
                        <option value="online" @selected(request('source') === 'online')>خرید از سایت</option>
                        <option value="manual" @selected(request('source') === 'manual')>ثبت دستی</option>
                    </select>
                </div>
                <div class="col-span-1 lg:col-span-2">
                    <select name="sort_by" aria-label="مرتب‌سازی"
                            class="w-full rounded-xl sm:rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3 py-2.5 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>
                            جدیدترین (پیش‌فرض)
                        </option>
                        <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                        <option value="renewal_date_asc" @selected(request('sort_by') === 'renewal_date_asc')>تاریخ
                            تمدید (نزدیک‌ترین)
                        </option>
                        <option value="renewal_date_desc" @selected(request('sort_by') === 'renewal_date_desc')>تاریخ
                            تمدید (دورترین)
                        </option>
                        <option value="issue_date_desc" @selected(request('sort_by') === 'issue_date_desc')>تاریخ صدور
                            (جدیدترین)
                        </option>
                        <option value="issue_date_asc" @selected(request('sort_by') === 'issue_date_asc')>تاریخ صدور
                            (قدیمی‌ترین)
                        </option>
                        <option value="amount_desc" @selected(request('sort_by') === 'amount_desc')>مبلغ نهایی
                            (بیشترین)
                        </option>
                        <option value="amount_asc" @selected(request('sort_by') === 'amount_asc')>مبلغ نهایی (کمترین)
                        </option>
                        <option value="order_number_asc" @selected(request('sort_by') === 'order_number_asc')>شماره
                            سفارش (صعودی)
                        </option>
                        <option value="order_number_desc" @selected(request('sort_by') === 'order_number_desc')>شماره
                            سفارش (نزولی)
                        </option>
                    </select>
                </div>

                @if(request('sort_order'))
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif

                <div class="col-span-2 lg:col-span-1 flex gap-1.5 justify-end">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl sm:rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer"
                            title="اعمال فیلتر">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span class="lg:hidden xl:inline">فیلتر</span>
                    </button>
                    @if(request()->hasAny(['search', 'status', 'package_id', 'source', 'sort_by', 'sort_order']))
                        <a href="{{ route('reseller.orders.index') }}" title="پاک کردن فیلترها"
                           class="inline-flex items-center justify-center p-2.5 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Desktop Table Container --}}
        <div
            class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-5 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            <a href="{{ reseller_sort_url('service') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>سرویس و کاربر زیرمجموعه</span>
                                {!! reseller_sort_icon('service') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ reseller_sort_url('invoice') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>شماره فاکتور</span>
                                {!! reseller_sort_icon('invoice') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ reseller_sort_url('renewal_price') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>دوره و مبلغ تمدید</span>
                                {!! reseller_sort_icon('renewal_price') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ reseller_sort_url('total_amount') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>مبلغ نهایی سرویس</span>
                                {!! reseller_sort_icon('total_amount') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ reseller_sort_url('issue_date') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>تاریخ صدور</span>
                                {!! reseller_sort_icon('issue_date') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ reseller_sort_url('renewal_date') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>تاریخ تمدید</span>
                                {!! reseller_sort_icon('renewal_date') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            <a href="{{ reseller_sort_url('status') }}"
                               class="group inline-flex items-center gap-1.5 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span>وضعیت</span>
                                {!! reseller_sort_icon('status') !!}
                            </a>
                        </th>
                        <th scope="col"
                            class="px-5 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            جزئیات
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($orders as $order)
                        @php
                            $invoice      = $order->invoice;
                            $customerName = $order->sub_account_name ?: ' نامشخص';
                            $startDate    = $order->start_date ?: ($invoice ? $invoice->issue_date : $order->created_at);

                            $statusColor  = $order->status_enum?->hexColor() ?? '#6b7280';
                            $statusName   = $order->status_label;
                            $renewalDate  = $order->renewal_date ?: $order->end_date;

                            $finalAmount  = $invoice ? $invoice->total : $order->total_amount;
                            $renewalPrice = $order->renewal_price > 0 ? $order->renewal_price : $order->total_amount;
                            $orderPkg     = $order->isCustomPackage() ? null : ($order->package ?? $invoice?->package);
                            $packageName  = $orderPkg?->name ?? 'سفارش سامانه';
                        @endphp

                        <tr class="group bg-white dark:bg-gray-800 hover:bg-indigo-50/40 dark:hover:bg-indigo-500/10 transition-colors duration-200">
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center gap-3">
                                    @if($orderPkg)
                                        <div
                                            class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-xs"
                                            style="{{ $orderPkg->bg_style }}">
                                            @include('resellerhub::user.packages.partials.icon', ['icon' => $orderPkg->icon, 'class' => 'w-5 h-5'])
                                        </div>
                                    @else
                                        <div
                                            class="shrink-0 w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-black text-gray-900 dark:text-white text-sm truncate">
                                                {{ $packageName }}
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold {{ $order->source_badge }}">
                                                @if($order->isOnlinePurchase())
                                                    خرید از سایت
                                                @else
                                                    ثبت دستی
                                                @endif
                                            </span>
                                        </div>

                                        @if($order->tenant)
                                            <div
                                                class="mt-1 flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                                <a href="{{ route('reseller.tenants.show', $order->tenant_id) }}"
                                                   class="hover:underline dir-ltr truncate max-w-[180px]">
                                                    {{ $order->tenant->subdomain }} ({{ $order->tenant->name }})
                                                </a>
                                            </div>
                                        @endif

                                        <div
                                            class="mt-1 flex items-center gap-1.5 text-xs font-bold text-gray-500 dark:text-gray-400">
                                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="truncate max-w-[170px]">{{ $customerName }}</span>
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span class="font-bold text-gray-400 uppercase tabular-nums dir-ltr">{{ $faNum($order->order_number) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4 text-center align-middle">
                                @if($invoice)
                                    <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                                       onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-indigo-100 dark:bg-gray-700/50 dark:hover:bg-indigo-500/20 text-xs font-bold text-gray-700 hover:text-indigo-700 dark:text-gray-300 dark:hover:text-indigo-300 px-3 py-1.5 rounded-lg tabular-nums transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $faNum($invoice->invoice_number) }}
                                    </a>
                                @else
                                    <span class="text-xs font-bold text-gray-400">بدون فاکتور</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-middle text-center">
                                @if($order->billing_cycle && $renewalPrice > 0)
                                    <div>
                                        <span class="font-black text-slate-800 dark:text-slate-200 text-sm tabular-nums">{{ $faNum(number_format($renewalPrice)) }}</span>
                                        <span class="text-xs text-gray-500">{{ $currencyLabel }}</span>
                                    </div>
                                    <span
                                        class="inline-block mt-1 text-[11px] font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/20 px-2.5 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-500/20">
                                        {{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-md">بدون تمدید</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                <div class="font-black text-gray-900 dark:text-gray-100 text-sm tabular-nums">{{ $faNum(number_format($finalAmount)) }}</div>
                                <div class="text-[11px] font-bold text-gray-400 mt-0.5">{{ $currencyLabel }}</div>
                            </td>
                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                <span class="font-bold text-gray-700 dark:text-gray-300 text-xs tabular-nums dir-ltr block">
                                    {{ $startDate ? reseller_format_jalali($startDate) : '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                @if($renewalDate)
                                    <div class="font-black text-gray-700 dark:text-gray-300 text-xs tabular-nums dir-ltr">
                                        {{ reseller_format_jalali($renewalDate) }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 font-bold">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border"
                                    style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}40">
                                    {{ $statusName }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-middle text-end">
                                <a href="{{ route('reseller.orders.show', $order->id) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-500/20 transition-all shadow-2xs active:scale-95"
                                   title="جزئیات سفارش">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <span
                                        class="flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-400 mb-4">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </span>
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">سفارشی یافت نشد</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">هنوز سفارشی ثبت نشده است یا با فیلترهای انتخابی شما همخوانی ندارد.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->total() > 0)
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-medium">
                        <span>
                            نمایش
                            <strong class="font-bold text-gray-900 dark:text-white">{{ $faNum($orders->firstItem() ?? 1) }}</strong>
                            تا
                            <strong class="font-bold text-gray-900 dark:text-white">{{ $faNum($orders->lastItem() ?? $orders->count()) }}</strong>
                            از مجموع
                            <strong class="font-bold text-gray-900 dark:text-white">{{ $faNum($orders->total()) }}</strong>
                            سفارش
                        </span>

                        <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>

                        <form method="GET" action="{{ route('reseller.orders.index') }}"
                              class="inline-flex items-center gap-1.5 m-0">
                            @foreach(request()->except(['per_page', 'page']) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <span class="hidden sm:inline">تعداد در صفحه:</span>
                            <select name="per_page" aria-label="تعداد در صفحه" onchange="this.form.submit()"
                                    class="rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer shadow-2xs">
                                @foreach([10, 15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected(request('per_page', 15) == $size)>{{ $faNum($size) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div>
                        @if($orders->hasPages())
                            {{ $orders->withQueryString()->links('resellerhub::partials.pagination') }}
                        @else
                            <div class="inline-flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs shadow-sm select-none">
                                    {{ $faNum(1) }}
                                </span>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Mobile Cards View (Independent Cards matching Invoices Layout) --}}
        <div class="md:hidden space-y-4">
            @forelse($orders as $order)
                @php
                    $invoice      = $order->invoice;
                    $customerName = $order->sub_account_name ?: ' نامشخص';
                    $startDate    = $order->start_date ?: ($invoice ? $invoice->issue_date : $order->created_at);

                    $statusColor  = $order->status_enum?->hexColor() ?? '#6b7280';
                    $statusName   = $order->status_label;
                    $renewalDate  = $order->renewal_date ?: $order->end_date;

                    $finalAmount  = $invoice ? $invoice->total : $order->total_amount;
                    $renewalPrice = $order->renewal_price > 0 ? $order->renewal_price : $order->total_amount;
                    $orderPkg     = $order->isCustomPackage() ? null : ($order->package ?? $invoice?->package);
                    $packageName  = $orderPkg?->name ?? 'سفارش سامانه';
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/90 dark:border-gray-700/70 shadow-sm overflow-hidden transition-all">
                    {{-- 1. Card Header Bar --}}
                    <div class="px-4 py-3 bg-gray-50/90 dark:bg-gray-900/60 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($orderPkg)
                                <span
                                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs"
                                    style="{{ $orderPkg->bg_style }}">
                                    @include('resellerhub::user.packages.partials.icon', ['icon' => $orderPkg->icon, 'class' => 'w-4.5 h-4.5'])
                                </span>
                            @else
                                <span
                                    class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </span>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('reseller.orders.show', $order->id) }}"
                                   class="block font-black text-sm text-gray-900 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $packageName }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-gray-400 font-bold tabular-nums dir-ltr">
                                    <span>{{ $faNum($order->order_number) }}</span>
                                </div>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border shrink-0"
                            style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}40">
                            {{ $statusName }}
                        </span>
                    </div>

                    {{-- 2. Card Body --}}
                    <div class="p-4 space-y-3.5">
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-gray-400 shrink-0 font-medium">کاربر زیرمجموعه:</span>
                                    <span class="font-bold text-gray-900 dark:text-white truncate">{{ $customerName }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $order->source_badge }}">
                                    {{ $order->isOnlinePurchase() ? 'خرید از سایت' : 'ثبت دستی' }}
                                </span>
                            </div>

                            @if($order->tenant)
                                <div class="flex items-center gap-1.5 pt-0.5">
                                    <span class="text-gray-400 shrink-0 font-medium">سامانه:</span>
                                    <a href="{{ route('reseller.tenants.show', $order->tenant_id) }}"
                                       class="inline-flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400 hover:underline truncate">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span class="truncate">{{ $order->tenant->name }} ({{ $order->tenant->subdomain }})</span>
                                    </a>
                                </div>
                            @endif

                            @if($invoice)
                                <div class="flex items-center justify-between gap-2 pt-0.5">
                                    <span class="text-gray-400 shrink-0 font-medium">شماره فاکتور:</span>
                                    <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                                       class="font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded tabular-nums dir-ltr hover:text-indigo-600 transition-colors">
                                        {{ $faNum($invoice->invoice_number) }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Financial & Renewal Highlight Box --}}
                        <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-0.5">مبلغ نهایی سرویس</span>
                                <span class="text-sm font-black text-indigo-700 dark:text-indigo-300 tabular-nums">
                                    {{ $faNum(number_format($finalAmount)) }} {{ $currencyLabel }}
                                </span>
                            </div>
                            <div class="text-left rtl:text-right">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 block mb-0.5">
                                    تمدید {{ $order->billing_cycle ? ($order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle)) : '' }}
                                </span>
                                @if($order->billing_cycle && $renewalPrice > 0)
                                    <span class="text-xs font-black text-gray-900 dark:text-white tabular-nums">
                                        {{ $faNum(number_format($renewalPrice)) }} {{ $currencyLabel }}
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-gray-400">بدون تمدید</span>
                                @endif
                            </div>
                        </div>

                        {{-- Dates Row --}}
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-0.5">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-400">صدور:</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300 dir-ltr tabular-nums">
                                    {{ $startDate ? reseller_format_jalali($startDate) : '—' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-400">تمدید:</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300 dir-ltr tabular-nums">
                                    {{ $renewalDate ? reseller_format_jalali($renewalDate) : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Card Action Footer --}}
                    <div class="px-4 py-2.5 bg-gray-50/70 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/60 flex items-center gap-2">
                        <a href="{{ route('reseller.orders.show', $order->id) }}"
                           class="flex-1 h-9 inline-flex items-center justify-center gap-1.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs active:scale-95">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>مشاهده جزئیات</span>
                        </a>
                        @if($invoice)
                            <a href="{{ route('reseller.invoices.show', $invoice->id) }}"
                               class="h-9 px-3 inline-flex items-center justify-center gap-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold transition border border-gray-200 dark:border-gray-700 shrink-0 shadow-2xs">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>فاکتور</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-8 text-center shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mx-auto mb-4 text-indigo-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">سفارشی یافت نشد</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">هنوز سفارشی ثبت نشده است یا با فیلترهای انتخابی شما همخوانی ندارد.</p>
                    <a href="{{ route('reseller.invoices.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        ثبت سفارش جدید
                    </a>
                </div>
            @endforelse

            {{-- Mobile Pagination Card --}}
            @if($orders->total() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-4 shadow-sm flex flex-col items-center justify-between gap-3 text-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        نمایش
                        <strong class="font-bold text-gray-900 dark:text-white">{{ $faNum($orders->firstItem() ?? 1) }}</strong>
                        تا
                        <strong class="font-bold text-gray-900 dark:text-white">{{ $faNum($orders->lastItem() ?? $orders->count()) }}</strong>
                        از مجموع
                        <strong class="font-bold text-gray-900 dark:text-white">{{ $faNum($orders->total()) }}</strong>
                        سفارش
                    </div>

                    <div>
                        @if($orders->hasPages())
                            {{ $orders->withQueryString()->links('resellerhub::partials.pagination') }}
                        @else
                            <div class="inline-flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-600 text-white font-black text-xs shadow-sm select-none">
                                    {{ $faNum(1) }}
                                </span>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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

    @include('resellerhub::partials.toast')
@endsection
