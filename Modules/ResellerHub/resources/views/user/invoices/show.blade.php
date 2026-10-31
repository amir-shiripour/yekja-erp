@php
    use Carbon\Carbon;
    use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
    use Morilog\Jalali\Jalalian;
@endphp

@extends('layouts.user')
@section('title', 'فاکتور: ' . $invoice->invoice_number)

@php
    $currency      = $currency ?? ($settings['currency'] ?? 'toman');
    $currencyLabel = $currencyLabel ?? reseller_currency_label($currency);
    $remainingAmount = $invoice->remainingAmount();
    $isCanceled      = $invoice->isCancelled();
    $statusLabel     = $invoice->status_label;
    $statusBadge     = $invoice->status_badge;
    $isItemAddition  = !empty($invoice->meta['is_item_addition']);
    $isRenewal       = !$invoice->isUpgrade() && !$isItemAddition && ($invoice->parent_invoice_id || !empty($invoice->meta['is_renewal']));
    $moduleTitles    = $moduleTitles ?? [];
@endphp

@section('content')
    <div x-data="{
    openCancelInvoiceModal: false,
    isCancellingInvoice: false,
    openCancelPaymentModal: false,
    isCancellingPayment: false,
    cancelPaymentActionUrl: '',
    cancelPaymentDetails: { date: '', amount: '', method: '', transaction_id: '' }
}" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('reseller.invoices.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                لیست صورتحساب‌ها
            </a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-gray-900 dark:text-white font-bold truncate max-w-xs tabular-nums">
            {{ $faNum($invoice->invoice_number) }}
        </span>
        </nav>

        {{-- Hero Header (Services Style 1:1) --}}
        <x-resellerhub::card>
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div
                        class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                            فاکتور رسمی
                        </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge['bg'] }}">
                            <span class="w-2 h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                            {{ $statusLabel }}
                        </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $invoice->source_badge }}">
                                @if($invoice->isOnlinePurchase())
                                    <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span>{{ $invoice->source_label }}</span>
                                @else
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ $invoice->source_label }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">
                                {{ $faNum($invoice->invoice_number) }}
                            </h1>
                            @if($invoice->isUpgrade())
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-black bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    فاکتور ارتقای پکیج
                                </span>
                            @elseif($invoice->isManualRenewal())
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-500/20">
                                    فاکتور تمدید (دستی)
                                </span>
                            @elseif($isItemAddition)
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    افزودن ماژول / قابلیت
                                </span>
                            @elseif($invoice->isSystemGenerated() || $isRenewal)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 border border-indigo-100 dark:border-indigo-500/20">
                                    ایجاد سیستمی (تمدید)
                                </span>
                            @endif
                            @php
                                $orderModel = $invoice->getOrderModel();
                                $isOrderCustom = $orderModel ? $orderModel->isCustomPackage() : false;
                                $effectivePackage = null;
                                if (!$isItemAddition && !$isOrderCustom && !($invoice->tenant && empty($invoice->tenant->package_id))) {
                                    $effectivePackage = $invoice->package ?? $invoice->tenant?->package;
                                }
                                $packageItem = $invoice->items->firstWhere('item_type', 'package') ?? $invoice->items->firstWhere('item_type', 'package_upgrade');
                                $pkgItemDiscount = $packageItem ? (int)$packageItem->discount_amount : (int)($invoice->discount_amount ?? 0);
                                $hasInvoicePromo = ($pkgItemDiscount > 0) || !empty($invoice->meta['has_package_discount']);
                            @endphp
                            @if($effectivePackage || $packageItem)
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40">
                                    <span>پکیج: {{ $effectivePackage->name ?? ($packageItem->title ?? 'پلن اشتراک') }}</span>
                                    @if($hasInvoicePromo)
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-black bg-rose-500 text-white shadow-xs"
                                            title="این فاکتور با اعمال تخفیف ویژه دوره زمانی پکیج صادر گردیده است">
                                            <svg class="w-3 h-3 text-rose-100" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round"
                                                                                                stroke-linejoin="round"
                                                                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            <span>خریداری‌شده با تخفیف ویژه دوره @if($pkgItemDiscount > 0)
                                                    ({{ $faNum(number_format($pkgItemDiscount)) }} {{ $currencyLabel }})
                                                @endif</span>
                                        </span>
                                    @elseif($effectivePackage && (float)($effectivePackage->discount_value ?? 0) > 0)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.2 rounded text-xs font-black bg-rose-500 text-white">
                                            {{ $effectivePackage->discount_type === 'percent' ? $faNum($effectivePackage->discount_value) . '٪ تخفیف' : $faNum(number_format($effectivePackage->discount_value)) . ' ' . $currencyLabel . ' تخفیف' }}
                                        </span>
                                    @endif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">
                                    <span>پکیج: سفارشی (Custom)</span>
                                </span>
                            @endif
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            تاریخ صدور: <span
                                class="tabular-nums font-medium">{{ $faNum($toJalali($invoice->issue_date)?->format('Y/m/d') ?? '—') }}</span>
                        </span>
                            @if($invoice->due_date)
                                <span class="flex items-center gap-1.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                تاریخ سررسید: <span
                                        class="tabular-nums font-medium">{{ $faNum($toJalali($invoice->due_date)?->format('Y/m/d') ?? '—') }}</span>
                            </span>
                            @endif
                            @if($invoice->paid_at)
                                <span
                                    class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                تاریخ پرداخت: <span
                                        class="tabular-nums font-medium">{{ $faNum($toJalali($invoice->paid_at)?->format('Y/m/d') ?? '—') }}</span>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    {{-- Register Payment (If Unpaid / Has Remaining) --}}
                    @if(!$isCanceled && $remainingAmount > 0)
                        <a href="{{ route('reseller.invoices.payment', $invoice->id) }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span>{{ $invoice->paid_amount == 0 ? 'ثبت پرداختی' : 'ثبت پرداخت جدید' }}</span>
                        </a>
                    @endif

                    {{-- Cancel Invoice with Modal --}}
                    @if(!$isCanceled && $remainingAmount > 0)
                        <button type="button" @click="openCancelInvoiceModal = true"
                                class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-bold transition-all active:scale-95 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            <span>لغو فاکتور</span>
                        </button>
                    @endif

                    {{-- Download PDF Button with Spinner --}}
                    <a href="{{ route('reseller.invoices.print', $invoice->id) }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:hover:bg-sky-500/20 text-sm font-bold transition-all active:scale-95 cursor-pointer"
                       id="pdf-download-btn" target="_blank">
                        <svg id="pdf-icon-default" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <svg id="pdf-icon-spinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span id="pdf-btn-text">دانلود PDF</span>
                    </a>

                    {{-- Back Button --}}
                    <a href="{{ route('reseller.invoices.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>بازگشت</span>
                    </a>
                </div>
            </div>
        </x-resellerhub::card>

        {{-- Session Alerts --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 text-red-800 dark:text-red-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Connected Order Banner (Services Style) --}}
        @if($invoice->order)
            <div
                class="rounded-3xl border border-emerald-200 dark:border-emerald-800/60 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-500/10 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-600/30 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-sm sm:text-base text-gray-900 dark:text-white flex items-center gap-2 flex-wrap">
                                <span>این صورتحساب به سفارش متصل است:</span>
                                <span
                                    class="text-indigo-600 dark:text-indigo-400 font-black tabular-nums">{{ $invoice->order->order_number }}</span>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-bold {{ $invoice->order->status_badge['bg'] }}">{{ $invoice->order->status_label }}</span>
                            </h3>
                            <div
                                class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex-wrap">
                                <span>دوره اشتراک: <strong
                                        class="text-gray-800 dark:text-gray-200">{{ $invoice->order->billing_cycle_label }}</strong></span>
                                <span>•</span>
                                <span>سررسید تمدید دوره بعد: <strong
                                        class="text-indigo-600 dark:text-indigo-400 tabular-nums">{{ $faNum($toJalali($invoice->order->renewal_date ?? $invoice->order->end_date)?->format('Y/m/d') ?? '—') }}</strong></span>
                                @if($invoice->order->renewal_price)
                                    <span>•</span>
                                    <span>مبلغ تمدید دوره: <strong
                                            class="tabular-nums text-gray-800 dark:text-gray-200">{{ $faNum(number_format($invoice->order->renewal_price)) }} {{ $currencyLabel }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('reseller.orders.show', $invoice->order->id) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black transition shadow-md shadow-emerald-600/25 shrink-0 cursor-pointer">
                        <span>مشاهده سفارش و لایسنس</span>
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        {{-- Parent Invoice / Item Addition Banner --}}
        @if($isItemAddition)
            <div
                class="rounded-3xl border border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/60 dark:bg-emerald-950/30 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </span>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">این صورتحساب جهت افزودن ماژول /
                                قابلیت به سفارش صادر گردیده است</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">این سند بابت افزودن ماژول‌ها و
                                دسترسی‌های جدید به سامانه CRM می‌باشد.</p>
                        </div>
                    </div>
                    @if($invoice->getOrderModel())
                        <a href="{{ route('reseller.orders.show', $invoice->getOrderModel()->id) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold shadow-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/40 transition">
                            <span>مشاهده سفارش مرتبط #{{ $faNum($invoice->getOrderModel()->order_number) }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @elseif($invoice->parentInvoice)
                        <a href="{{ route('reseller.invoices.show', $invoice->parentInvoice->id) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold shadow-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/40 transition">
                            <span>مشاهده فاکتور پایه #{{ $faNum($invoice->parentInvoice->invoice_number) }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        @elseif($invoice->parentInvoice)
            @if($invoice->isUpgrade())
                <div
                    class="rounded-3xl border border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/60 dark:bg-emerald-950/30 p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </span>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">این صورتحساب جهت ارتقای
                                    پکیج صادر گردیده است</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">این سند بابت مابه‌التفاوت
                                    ارتقای پکیج سامانه CRM نسبت به فاکتور قبلی می‌باشد.</p>
                            </div>
                        </div>
                        <a href="{{ route('reseller.invoices.show', $invoice->parentInvoice->id) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold shadow-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/40 transition">
                            <span>مشاهده فاکتور قبلی #{{ $faNum($invoice->parentInvoice->invoice_number) }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                <div
                    class="rounded-3xl border border-cyan-100 dark:border-cyan-900/50 bg-cyan-50/60 dark:bg-cyan-950/30 p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-2xl bg-cyan-100 dark:bg-cyan-900/60 text-cyan-700 dark:text-cyan-300 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </span>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">این صورتحساب جهت تمدید صادر
                                    گردیده است</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">این سند تمدید دوره اشتراک
                                    قبلی
                                    سامانه CRM می‌باشد.</p>
                            </div>
                        </div>
                        <a href="{{ route('reseller.invoices.show', $invoice->parentInvoice->id) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 text-xs font-bold shadow-xs hover:bg-cyan-50 dark:hover:bg-cyan-900/40 transition">
                            <span>مشاهده فاکتور پایه #{{ $faNum($invoice->parentInvoice->invoice_number) }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- Renewals Banner --}}
        @if($invoice->renewals->isNotEmpty())
            <div
                class="rounded-3xl border border-purple-100 dark:border-purple-900/50 bg-purple-50/60 dark:bg-purple-950/30 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">فاکتورهای تمدید دوره‌های بعدی
                                برای
                                این سند:</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                تاکنون {{ $faNum($invoice->renewals->count()) }} دوره تمدید برای این سامانه صادر شده
                                است.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($invoice->renewals as $ren)
                            <a href="{{ route('reseller.invoices.show', $ren->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-xs font-bold shadow-xs hover:bg-purple-50 dark:hover:bg-purple-900/40 transition">
                                <span>فاکتور #{{ $faNum($ren->invoice_number) }}</span>
                                <span
                                    class="text-xs px-1.5 py-0.5 rounded-full {{ $ren->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $ren->status_label }}
                            </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <x-resellerhub::card>
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    <div class="p-2 bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    اطلاعات مشتری و طرفین قرارداد
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-sm">
                <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span
                        class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">نام زیرحساب (خریدار)</span>
                    <span
                        class="text-gray-800 dark:text-gray-200 text-base font-bold break-all">{{ $invoice->sub_account_name }}</span>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span
                        class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">شماره تماس زیرحساب</span>
                    <span
                        class="text-gray-800 dark:text-gray-200 text-base font-bold tabular-nums dir-ltr block text-start">
                    {{ $invoice->sub_account_mobile ? $faNum($invoice->sub_account_mobile) : '—' }}
                </span>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span
                        class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">پست الکترونیک زیرحساب</span>
                    <span
                        class="text-gray-800 dark:text-gray-200 text-sm font-medium dir-ltr block text-start truncate">
                    {{ $invoice->sub_account_email ?: '—' }}
                </span>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">سامانه CRM متصل</span>
                    @if($invoice->tenant)
                        <a href="{{ $invoice->tenant->url ?? ('/' . $invoice->tenant->subdomain) }}" target="_blank"
                           class="text-indigo-600 dark:text-indigo-400 text-sm font-bold hover:underline flex items-center gap-1 dir-ltr">
                            <span>{{ $invoice->tenant->subdomain }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span
                        class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">فروشنده (نمایندگی)</span>
                    <span class="text-gray-800 dark:text-gray-200 text-sm font-bold truncate block">
                        {{ $reseller->display_name }}
                    </span>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span
                        class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">پشتیبانی نمایندگی</span>
                    <span class="text-gray-800 dark:text-gray-200 text-sm tabular-nums dir-ltr block text-start">
                        {{ !empty($reseller->support_phone) ? reseller_fa_num($reseller->support_phone) : ($reseller->support_email ?: '—') }}
                    </span>
                </div>
            </div>
        </x-resellerhub::card>

        {{-- Line Items Table (Services Style 1:1) --}}
        <x-resellerhub::card>
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                    <div
                        class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    اقلام و خدمات فاکتور
                </h3>
                @php
                    $visibleItems = $invoice->items->reject(function ($it) {
                        $desc = trim((string)($it->description ?? ''));
                        $title = trim((string)($it->title ?? ''));
                        return ($it->item_type === 'custom' || $it->item_type === null)
                            && ((int)$it->unit_price === 0)
                            && (str_starts_with($desc, 'راه‌اندازی سامانه CRM اختصاصی') || str_starts_with($title, 'راه‌اندازی سامانه CRM اختصاصی'));
                    });

                    $enableItemized = (bool)($settings['enable_module_itemized_pricing'] ?? false);
                    $hasPackage = $invoice->package_id || $visibleItems->contains(fn($it) => in_array($it->item_type, ['package', 'package_upgrade']));
                    $isItemized = $enableItemized || (!$hasPackage && $visibleItems->contains(fn($it) => in_array($it->item_type, ['module', 'feature']) && (int)$it->unit_price > 0));

                    // Do not display 0-cost bundled module rows on package invoices
                    if ($hasPackage && !$isItemized) {
                        $visibleItems = $visibleItems->reject(fn($it) => $it->item_type === 'module' && ((int)($it->unit_price ?? 0) === 0));
                    }
                @endphp
                <span
                    class="text-sm font-bold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 px-3 py-1.5 rounded-lg border border-violet-100 dark:border-violet-500/20">
                {{ $faNum($visibleItems->count()) }} ردیف
            </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start border-collapse min-w-[850px]">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                    @if(!$isItemized)
                        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/10">
                            <th scope="col" class="text-start font-black px-4 py-3.5">شرح خدمت / پکیج و ماژول‌ها</th>
                            <th scope="col" class="text-center font-black px-3 py-3.5 w-36">نوع قلم</th>
                            <th scope="col" class="text-center font-black px-3 py-3.5 w-28">دوره / تعداد</th>
                            <th scope="col" class="text-center font-black px-4 py-3.5 w-60">وضعیت در فاکتور</th>
                        </tr>
                    @else
                        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/10">
                            <th scope="col" class="text-start font-black px-4 py-3.5 w-[38%]">شرح خدمت</th>
                            <th scope="col" class="text-center font-black px-2 py-3.5 w-16">تعداد</th>
                            <th scope="col" class="text-center font-black px-2 py-3.5 w-32">مبلغ واحد ({{ $currencyLabel }})</th>
                            <th scope="col" class="text-center font-black px-2 py-3.5 w-32">تخفیف ردیف</th>
                            <th scope="col" class="text-center font-black px-2 py-3.5 w-36">مبلغ کل</th>
                        </tr>
                    @endif
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 transition-all">
                    @foreach($visibleItems as $item)
                        @php
                            $isSetupFee = $item->item_type === 'setup_fee';
                            $isPackage = $item->item_type === 'package';
                            $isUpgrade = $item->item_type === 'package_upgrade';
                            $isModule = $item->item_type === 'module';

                            // Clean title and extract limits if embedded like "— [سقف: ...]"
                            $displayTitle = $item->title;
                            $extractedLimits = [];
                            if (preg_match('/—\s*\[سقف:\s*(.+?)\]/u', $displayTitle, $limitMatches)) {
                                $extractedLimits[] = trim($limitMatches[1]);
                                $displayTitle = trim(preg_replace('/—\s*\[سقف:\s*(.+?)\]/u', '', $displayTitle));
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors {{ $item->row_bg_class }}">
                            @if(!$isItemized)
                                {{-- Col 1: Title / Description & Limits (Matching create.blade.php) --}}
                                <td class="px-4 py-3.5 align-middle">
                                    <div class="flex items-start gap-2.5">
                                        <span
                                            class="shrink-0 w-6 h-6 mt-0.5 flex items-center justify-center rounded-lg text-xs font-black {{ $item->icon_badge_class }}">
                                            @if($isSetupFee)
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                            @else
                                                {{ reseller_fa_num($loop->iteration) }}
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200 leading-5">
                                                {{ $displayTitle }}
                                            </p>
                                            @if(!empty($item->description) && $item->description !== $item->title && !str_starts_with($item->title, $item->description))
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                                                    {{ $item->description }}
                                                </p>
                                            @endif
                                            @if(count($extractedLimits) > 0)
                                                <div class="flex items-center gap-1.5 flex-wrap mt-1.5">
                                                    <span
                                                        class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                        </svg>
                                                        سقف‌های مصرف:
                                                    </span>
                                                    @foreach($extractedLimits as $lim)
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40 text-xs font-black">
                                                            {{ $lim }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @elseif($isModule)
                                                <div class="mt-1">
                                                    <span
                                                        class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium">
                                                        <svg class="w-2.5 h-2.5 text-emerald-500" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        سقف مصرف: نامحدود
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Col 2: Item Type Badge --}}
                                <td class="px-3 py-3.5 text-center whitespace-nowrap align-middle">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $item->type_badge_class }}">
                                        {{ $item->type_label }}
                                    </span>
                                </td>

                                {{-- Col 3: Duration / Quantity --}}
                                <td class="px-3 py-3.5 text-center whitespace-nowrap align-middle">
                                    @if($isSetupFee)
                                        <span
                                            class="text-xs font-bold text-amber-700 dark:text-amber-400">یک‌باره</span>
                                    @else
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                                            {{ $item->duration_text }}
                                        </span>
                                        @if(($item->quantity ?? 1) > 1 && ($item->duration_months ?? 0) > 0)
                                            <span class="text-gray-300">×</span>
                                            <span
                                                class="font-black text-gray-900 dark:text-white tabular-nums">{{ reseller_fa_num($item->quantity) }}</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Col 4: Invoice Status --}}
                                <td class="px-4 py-3.5 text-center whitespace-nowrap align-middle">
                                    @if($isPackage || $isUpgrade)
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                محاسبه در خلاصه مالی
                                            </span>
                                            @if($item->discount_amount > 0 || ($effectivePackage && (float)($effectivePackage->discount_value ?? 0) > 0))
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30">
                                                    <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                    </svg>
                                                    تخفیف:
                                                    @if($item->discount_amount > 0)
                                                        {{ $faNum(number_format($item->discount_amount)) }} {{ $currencyLabel }}
                                                    @elseif($effectivePackage && $effectivePackage->discount_type === 'percent')
                                                        {{ $faNum($effectivePackage->discount_value) }}٪
                                                    @elseif($effectivePackage)
                                                        {{ $faNum(number_format($effectivePackage->discount_value)) }} {{ $currencyLabel }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($isModule)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            شامل در پکیج (بدون هزینه مجزا)
                                        </span>
                                    @elseif($isSetupFee)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                            هزینه یکباره راه‌اندازی
                                        </span>
                                    @elseif($isCustomDomain || $isWhiteLabel)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30">
                                            خدمت جانبی اختصاصی
                                        </span>
                                    @else
                                        @if(($item->total_price ?? 0) > 0 || ($item->unit_price ?? 0) > 0)
                                            <span
                                                class="tabular-nums font-bold text-gray-800 dark:text-gray-200 text-xs">
                                                {{ $faNum(number_format($item->total_price ?: $item->unit_price)) }}
                                                <span
                                                    class="text-xs text-gray-400 font-normal">{{ $currencyLabel }}</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs text-gray-400 bg-gray-100 dark:bg-gray-800">
                                                بدون هزینه
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            @else
                                {{-- Itemized Mode Columns --}}
                                <td class="px-4 py-4 align-top text-gray-600 dark:text-gray-400 text-start text-xs leading-relaxed">
                                    <div class="font-bold text-gray-800 dark:text-gray-100">{{ $item->title }}</div>
                                    @if($item->description)
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top text-center">
                                    <div
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 overflow-hidden shadow-sm px-3 py-1.5">
                                        @if($isSetupFee)
                                            <span
                                                class="text-xs font-bold text-amber-700 dark:text-amber-400">یک‌باره</span>
                                        @else
                                            <span
                                                class="font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum($item->duration_months) }}</span>
                                            <span
                                                class="text-xs font-bold text-gray-400 dark:text-gray-500">ماهه</span>
                                            @if(($item->quantity ?? 1) > 1)
                                                <span class="text-gray-300">×</span>
                                                <span
                                                    class="font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum($item->quantity) }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-gray-700 dark:text-gray-300">
                                    {{ $faNum(number_format($item->unit_price)) }}
                                    <span class="text-xs text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                </td>
                                <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-red-500 dark:text-red-400">
                                    @if(($item->discount_amount ?? 0) > 0)
                                        {{ $faNum(number_format($item->discount_amount)) }}
                                        <span class="text-xs text-red-400/80 ms-1">{{ $currencyLabel }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top tabular-nums font-bold text-gray-800 dark:text-gray-100 text-center whitespace-nowrap">
                                    {{ $faNum(number_format($item->total_price)) }}
                                    <span class="text-xs font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if(!$isItemized)
                <div
                    class="p-3.5 bg-gradient-to-r from-indigo-50/70 to-violet-50/70 dark:from-indigo-950/20 dark:to-violet-950/20 border-t border-indigo-100 dark:border-indigo-900/40 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 text-indigo-800 dark:text-indigo-200">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">تعرفه ماژول‌ها در قالب پکیج کلی تجمیع شده است و مبالغ و تخفیف‌ها در بخش «خلاصه مالی فاکتور» تفکیک و محاسبه شده‌اند.</span>
                    </div>
                </div>
            @endif

            {{-- Table Summary Footer (Services Style) --}}
            <div
                class="bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 p-6 flex justify-end">
                <div class="w-full sm:w-96 space-y-4 text-base">
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span class="font-medium">جمع مبالغ اقلام:</span>
                        <span class="tabular-nums font-bold">{{ $faNum(number_format($invoice->subtotal)) }} <span
                                class="text-xs">{{ $currencyLabel }}</span></span>
                    </div>
                    @if($invoice->tax_amount > 0)
                        <div class="flex justify-between items-center text-amber-600 dark:text-amber-400">
                            <span class="font-medium">مالیات و عوارض ({{ $faNum($invoice->tax_percent) }}٪):</span>
                            <span
                                class="tabular-nums font-bold">+ {{ $faNum(number_format($invoice->tax_amount)) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                    @endif
                    @php
                        $packageItem = $visibleItems->firstWhere('item_type', 'package') ?? $visibleItems->firstWhere('item_type', 'package_upgrade');
                        $pkgItemDiscount = $packageItem ? (int)$packageItem->discount_amount : 0;
                    @endphp
                    @if($pkgItemDiscount > 0)
                        <div class="flex justify-between items-center text-rose-500 dark:text-rose-400">
                            <span class="font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span>تخفیف پکیج:</span>
                            </span>
                            <span
                                class="tabular-nums font-bold">− {{ $faNum(number_format($pkgItemDiscount)) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                    @endif
                    @if($invoice->discount_amount > $pkgItemDiscount)
                        <div class="flex justify-between items-center text-rose-500 dark:text-rose-400">
                            <span class="font-medium">تخفیف عمومی فاکتور:</span>
                            <span
                                class="tabular-nums font-bold">− {{ $faNum(number_format($invoice->discount_amount - $pkgItemDiscount)) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                    @elseif($invoice->discount_amount > 0 && $pkgItemDiscount == 0)
                        <div class="flex justify-between items-center text-rose-500 dark:text-rose-400">
                            <span class="font-medium">مجموع تخفیف‌ها:</span>
                            <span
                                class="tabular-nums font-bold">− {{ $faNum(number_format($invoice->discount_amount)) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                    @endif
                    @if(!empty($invoice->meta['rounding']['is_rounded']))
                        @php
                            $roundDiff = (int)($invoice->meta['rounding']['diff'] ?? 0);
                        @endphp
                        <div
                            class="flex justify-between items-center {{ $roundDiff > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }}">
                            <span class="font-medium">تعدیل رندسازی مبلغ:</span>
                            <span
                                class="tabular-nums font-bold">{{ $roundDiff > 0 ? '+ ' : '− ' }}{{ $faNum(number_format(abs($roundDiff))) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span></span>
                        </div>
                    @endif
                    <div
                        class="pt-4 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <span class="font-black text-gray-900 dark:text-white text-lg block">مبلغ نهایی:</span>
                            <span class="text-xs text-gray-400">پس از کسر تخفیف و اعمال مالیات</span>
                        </div>
                        <span class="font-black text-indigo-600 dark:text-indigo-400 text-2xl tabular-nums">
                        {{ $faNum(number_format($invoice->total)) }}
                        <span class="text-sm font-bold text-indigo-400/80">{{ $currencyLabel }}</span>
                    </span>
                    </div>
                </div>
            </div>
        </x-resellerhub::card>

        {{-- Connected Tenant & Package Cards (Side-by-Side in 1 Row) --}}
        @if($invoice->tenant || $effectivePackage)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
                {{-- Connected Tenant Card (Quick Info) --}}
                @if($invoice->tenant)
                    <x-resellerhub::card class="p-6 space-y-4 flex flex-col justify-between">
                        <div>
                            <div
                                class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                                <h4 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <span>سامانه CRM متصل</span>
                                </h4>
                                <span
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $invoice->tenant->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $invoice->tenant->isActive() ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </div>
                            <div class="space-y-3 text-xs mt-4">
                                <div
                                    class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                    <span class="text-gray-400 font-medium">نام سامانه:</span>
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200">{{ $invoice->tenant->name }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                    <span class="text-gray-400 font-medium">زیردامنه:</span>
                                    <span
                                        class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr">{{ $invoice->tenant->subdomain }}</span>
                                </div>
                                @if($invoice->tenant->custom_domain)
                                    <div
                                        class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                        <span class="text-gray-400 font-medium">دامنه اختصاصی:</span>
                                        <span
                                            class="font-bold text-sky-600 dark:text-sky-400 dir-ltr">{{ $invoice->tenant->custom_domain }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-gray-400 font-medium">شناسه سیستم:</span>
                                    <span
                                        class="text-gray-500 dark:text-gray-400 text-xs dir-ltr">#{{ $invoice->tenant->id }}</span>
                                </div>
                            </div>
                        </div>
                        @if($invoice->tenant->url)
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60">
                                <a href="{{ $invoice->tenant->url }}" target="_blank"
                                   class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition">
                                    <span>ورود به محیط سامانه CRM</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </x-resellerhub::card>
                @endif

                {{-- Package & Package Discount Card --}}
                @if($effectivePackage)
                    <x-resellerhub::card class="p-6 space-y-4 flex flex-col justify-between">
                        <div>
                            <div
                                class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                                <h4 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <span>پکیج: {{ $effectivePackage->name }}</span>
                                </h4>
                                <span
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40">
                                    {{ $effectivePackage->price_type === 'subscription' ? 'اشتراکی' : ($effectivePackage->price_type === 'free' ? 'رایگان' : 'یک‌باره') }}
                                </span>
                            </div>
                            <div
                                class="mt-4 rounded-xl p-3.5 border transition-all {{ ((float)($effectivePackage->discount_value ?? 0) > 0 || ($packageItem && $packageItem->discount_amount > 0)) ? 'bg-gradient-to-r from-rose-50/80 to-amber-50/50 dark:from-rose-950/20 dark:to-amber-950/10 border-rose-200/80 dark:border-rose-900/40' : 'bg-gray-50/70 dark:bg-gray-800/40 border-gray-100 dark:border-gray-700/60' }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                          stroke-linejoin="round"
                                                                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        <span>تخفیف خود پکیج:</span>
                                    </span>
                                    @if((float)($effectivePackage->discount_value ?? 0) > 0)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-500 text-white shadow-xs">
                                            {{ $effectivePackage->discount_type === 'percent' ? $faNum($effectivePackage->discount_value) . '٪' : $faNum(number_format($effectivePackage->discount_value)) . ' ' . $currencyLabel }}
                                        </span>
                                    @elseif($packageItem && $packageItem->discount_amount > 0)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-500 text-white shadow-xs">
                                            {{ $faNum(number_format($packageItem->discount_amount)) }} {{ $currencyLabel }}
                                        </span>
                                    @else
                                        <span class="text-xs font-medium text-gray-400">بدون تخفیف</span>
                                    @endif
                                </div>
                                @if((float)($effectivePackage->discount_value ?? 0) > 0)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-normal">
                                        این تخفیف روی تعرفه استاندارد دوره‌های این پکیج به صورت پیش‌فرض فعال و اعمال شده
                                        است.
                                    </p>
                                @endif
                            </div>

                            <div class="space-y-2.5 text-xs mt-4">
                                <div
                                    class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                    <span class="text-gray-400 font-medium">تعداد ماژول‌های پکیج:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $faNum($effectivePackage->modules_count) }} ماژول</span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                    <span class="text-gray-400 font-medium">تعداد امکانات پکیج:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $faNum($effectivePackage->features_count) }} قابلیت</span>
                                </div>
                                @if($effectivePackage->yearly_price > 0)
                                    <div
                                        class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                        <span class="text-gray-400 font-medium">تعرفه سالانه پایه:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">
                                            {{ $faNum(number_format($effectivePackage->yearly_price)) }} <span
                                                class="text-xs text-gray-400 font-normal">{{ $currencyLabel }}</span>
                                        </span>
                                    </div>
                                @endif
                                @if($effectivePackage->setup_fee > 0)
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-400 font-medium">هزینه راه‌اندازی اولیه:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">
                                            {{ $faNum(number_format($effectivePackage->setup_fee)) }} <span
                                                class="text-xs text-gray-400 font-normal">{{ $currencyLabel }}</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <a href="{{ route('reseller.packages.show', $effectivePackage->id) }}" target="_blank"
                               class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-xs font-bold hover:bg-purple-100 dark:hover:bg-purple-900/50 transition">
                                <span>مشاهده جزییات کامل پکیج</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </x-resellerhub::card>
                @else
                    <x-resellerhub::card class="p-6 space-y-4 flex flex-col justify-between">
                        <div>
                            <div
                                class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                                <h4 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                    <span>پکیج: سفارشی (Custom)</span>
                                </h4>
                                <span
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">
                                    طرح اختصاصی
                                </span>
                            </div>

                            <div
                                class="mt-4 rounded-xl p-3.5 border bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/40">
                                <span
                                    class="text-xs font-bold text-emerald-800 dark:text-emerald-300">سفارشی‌سازی دستی</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                    این فاکتور/سفارش دارای پکیج سفارشی است و ماژول‌ها و دسترسی‌های آن به صورت اختصاصی
                                    تنظیم گردیده‌اند.
                                </p>
                            </div>

                            @if($orderModel)
                                <div class="space-y-2.5 text-xs mt-4">
                                    <div
                                        class="flex justify-between items-center py-1 border-b border-gray-50 dark:border-gray-800">
                                        <span class="text-gray-400 font-medium">سفارش مرتبط:</span>
                                        <a href="{{ route('reseller.orders.show', $orderModel->id) }}"
                                           class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            #{{ $faNum($orderModel->order_number) }}
                                        </a>
                                    </div>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-400 font-medium">ماژول‌های فعال سفارش:</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $faNum(count($orderModel->modules_snapshot ?? [])) }} ماژول</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-resellerhub::card>
                @endif
            </div>
        @endif

        {{-- 2-Column Bottom Grid (Sidebar 4 Cols + Content 8 Cols) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- SIDEBAR (4 Cols) --}}
            <div class="lg:col-span-4 xl:col-span-4 space-y-8 order-2 lg:order-1">
                {{-- Summary Status Card --}}
                <x-resellerhub::card>
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-indigo-50 to-transparent dark:from-indigo-500/10">
                        <h3 class="text-lg font-black text-indigo-700 dark:text-indigo-400 flex items-center gap-3">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            </div>
                            خلاصه وضعیت فاکتور
                        </h3>
                    </div>
                    <div class="p-6 space-y-4 text-base">
                        <div
                            class="flex justify-between items-center text-gray-600 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="font-medium text-sm">وضعیت فعلی:</span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge['bg'] }}">
                            <span class="w-2 h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                            {{ $statusLabel }}
                        </span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                            <span class="font-medium">مبلغ کل:</span>
                            <span class="tabular-nums font-bold text-gray-800 dark:text-gray-200">
                            {{ $faNum(number_format($invoice->total)) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span>
                        </span>
                        </div>

                        <div class="flex justify-between items-center text-emerald-600 dark:text-emerald-400">
                            <span class="font-medium">پرداخت شده:</span>
                            <span class="tabular-nums font-bold">
                            {{ $faNum(number_format($invoice->paid_amount)) }} <span
                                    class="text-xs">{{ $currencyLabel }}</span>
                        </span>
                        </div>

                        <div
                            class="pt-4 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <span class="font-black text-gray-900 dark:text-white">مانده بدهی:</span>
                            <span
                                class="font-black {{ $remainingAmount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600' }} text-xl tabular-nums">
                            {{ $faNum(number_format($remainingAmount)) }} <span
                                    class="text-sm font-bold">{{ $currencyLabel }}</span>
                        </span>
                        </div>
                    </div>
                </x-resellerhub::card>
            </div>

            <div class="lg:col-span-8 xl:col-span-8 space-y-8 order-1 lg:order-2">
                {{-- Payments History Table ("اطلاعات پرداخت" - Services Style) --}}
                @if ($invoice->payments->isNotEmpty())
                    <x-resellerhub::card>
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-emerald-50 to-transparent dark:from-emerald-500/10 flex items-center justify-between">
                            <h3 class="text-lg font-black text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
                                <div
                                    class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                اطلاعات پرداخت
                            </h3>
                            @if(!$isCanceled && $remainingAmount > 0)
                                <a href="{{ route('reseller.invoices.payment', $invoice->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-100/80 hover:bg-emerald-200 dark:bg-emerald-500/20 dark:hover:bg-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>ثبت پرداخت جدید</span>
                                </a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-start border-collapse">
                                <thead
                                    class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-bold text-start">تاریخ</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-start">مبلغ</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-start">وضعیت</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-start">روش پرداخت</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-start">کد رهگیری</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-start">یادداشت</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-start"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach ($invoice->payments as $payment)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-4 py-4 tabular-nums text-gray-600 dark:text-gray-400 text-start text-xs whitespace-nowrap">
                                            {{ $faNum($toJalali($payment->paid_at)?->format('Y/m/d') ?? '—') }}
                                        </td>
                                        <td class="px-4 py-4 font-bold text-gray-800 dark:text-gray-200 tabular-nums text-start whitespace-nowrap">
                                            {{ $faNum(number_format($payment->amount)) }} {{ $currencyLabel }}
                                        </td>
                                        <td class="px-4 py-4 text-start whitespace-nowrap">
                                            @if($payment->status === 'canceled')
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                                                لغو شده
                                            </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                                پرداخت شده
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300 text-start text-xs font-medium">
                                            {{ ResellerInvoicePayment::formatMethodName($payment->method, $payment->gateway) }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-500 dark:text-gray-400 tabular-nums text-start text-xs">
                                            {{ $payment->transaction_id ?: '—' }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-500 dark:text-gray-400 text-start text-xs max-w-[180px] truncate">
                                            {{ $payment->notes ?: '—' }}
                                        </td>
                                        <td class="px-4 py-4 text-start whitespace-nowrap">
                                            @if($payment->status !== 'canceled' && !$isCanceled)
                                                <button type="button"
                                                        @click="openCancelPaymentModal = true; cancelPaymentActionUrl = '{{ route("reseller.invoices.payments.cancel", [$invoice->id, $payment->id]) }}'; cancelPaymentDetails = { date: '{{ $faNum($toJalali($payment->paid_at)?->format('Y/m/d') ?? '—') }}', amount: '{{ $faNum(number_format($payment->amount)) }} {{ $currencyLabel }}', method: '{{ ResellerInvoicePayment::formatMethodName($payment->method, $payment->gateway) }}', transaction_id: '{{ $payment->transaction_id ?: '—' }}' }"
                                                        class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 text-xs font-bold cursor-pointer transition">
                                                    لغو
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-resellerhub::card>
                @endif

                {{-- Combined Terms & Notes Card --}}
                @if($invoice->terms || $invoice->notes)
                    <x-resellerhub::card
                        class="p-6 sm:p-8 border-l-4 border-l-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/10"
                    >
                        <div class="flex items-start gap-4 text-indigo-800 dark:text-indigo-300">
                            <svg class="w-8 h-8 shrink-0 mt-0.5 opacity-80" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <strong class="block mb-2 font-black text-lg tracking-tight">شرایط، قوانین و یادداشت
                                    فاکتور</strong>
                                <p class="font-medium text-base leading-loose whitespace-pre-line">{{ trim(($invoice->terms ? $invoice->terms . "\n" : '') . ($invoice->notes ?? '')) }}</p>
                            </div>
                        </div>
                    </x-resellerhub::card>
                @endif
            </div>
        </div>


        <div x-show="openCancelInvoiceModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div @click.outside="openCancelInvoiceModal = false"
                 class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6 text-right"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-800">
                <span
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">تأیید لغو صورتحساب</h3>
                        <p class="text-xs text-gray-400 mt-0.5">فاکتور شماره {{ $faNum($invoice->invoice_number) }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs">
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 leading-relaxed">
                        آیا از لغو این صورتحساب اطمینان دارید؟ وضعیت سند به «لغو شده» تغییر یافته و غیرفعال می‌گردد.
                    </p>

                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/70 space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">خریدار:</span>
                            <span
                                class="font-bold text-gray-900 dark:text-white">{{ $invoice->sub_account_name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">مبلغ کل فاکتور:</span>
                            <span
                                class="font-bold tabular-nums text-gray-900 dark:text-white">{{ $faNum(number_format($invoice->total)) }} {{ $currencyLabel }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">مانده بدهی:</span>
                            <span
                                class="font-black tabular-nums text-rose-600 dark:text-rose-400">{{ $faNum(number_format($remainingAmount)) }} {{ $currencyLabel }}</span>
                        </div>
                    </div>

                    <div
                        class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900/50 flex items-start gap-2.5 text-rose-800 dark:text-rose-300">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-xs leading-relaxed font-bold">
                        توجه: این عملیات غیرقابل بازگشت است. سفارش فعال و لایسنس‌های مرتبط با این صورتحساب نیز به حالت لغوشده منتقل خواهند شد.
                    </span>
                    </div>
                </div>

                <form action="{{ route('reseller.invoices.cancel', $invoice->id) }}" method="POST"
                      @submit="isCancellingInvoice = true" class="pt-2 flex items-center justify-end gap-3">
                    @csrf
                    <button type="button" @click="openCancelInvoiceModal = false"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit" :disabled="isCancellingInvoice"
                            class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white text-xs font-black shadow-lg shadow-rose-600/25 transition cursor-pointer flex items-center gap-2">
                        <template x-if="isCancellingInvoice">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span>بله، فاکتور لغو شود</span>
                    </button>
                </form>
            </div>
        </div>
        <div x-show="openCancelPaymentModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div @click.outside="openCancelPaymentModal = false"
                 class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6 text-right"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-800">
                <span
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </span>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">تأیید لغو ردیف پرداختی</h3>
                        <p class="text-xs text-gray-400 mt-0.5">ابطال ثبت تراکنش پرداخت</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs">
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 leading-relaxed">
                        آیا از لغو این تراکنش پرداختی اطمینان دارید؟ مبلغ پرداختی مجدداً به مانده بدهی فاکتور بازخواهد
                        گشت.
                    </p>

                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/70 space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">مبلغ تراکنش:</span>
                            <span x-text="cancelPaymentDetails.amount"
                                  class="font-black tabular-nums text-emerald-600 dark:text-emerald-400 text-sm"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">تاریخ پرداخت:</span>
                            <span x-text="cancelPaymentDetails.date"
                                  class="font-bold tabular-nums text-gray-800 dark:text-gray-200"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">روش پرداخت:</span>
                            <span x-text="cancelPaymentDetails.method"
                                  class="font-bold text-gray-800 dark:text-gray-200"></span>
                        </div>
                        <template
                            x-if="cancelPaymentDetails.transaction_id && cancelPaymentDetails.transaction_id !== '—'">
                            <div
                                class="flex justify-between items-center pt-2 border-t border-gray-200/60 dark:border-gray-700/60">
                                <span class="text-gray-500 dark:text-gray-400">کد رهگیری:</span>
                                <span x-text="cancelPaymentDetails.transaction_id"
                                      class="font-medium tabular-nums text-gray-600 dark:text-gray-400"></span>
                            </div>
                        </template>
                    </div>

                    <div
                        class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/50 flex items-start gap-2.5 text-amber-800 dark:text-amber-300">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-xs leading-relaxed font-bold">
                        پس از لغو، وضعیت پرداخت به «لغو شده» تغییر یافته و در صورت تسویه کامل قبلی، وضعیت فاکتور به «در انتظار پرداخت» باز خواهد گشت.
                    </span>
                    </div>
                </div>

                <form :action="cancelPaymentActionUrl" method="POST"
                      @submit="isCancellingPayment = true" class="pt-2 flex items-center justify-end gap-3">
                    @csrf
                    <button type="button" @click="openCancelPaymentModal = false"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                        انصراف
                    </button>
                    <button type="submit" :disabled="isCancellingPayment"
                            class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white text-xs font-black shadow-lg shadow-rose-600/25 transition cursor-pointer flex items-center gap-2">
                        <template x-if="isCancellingPayment">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span>بله، پرداخت لغو شود</span>
                    </button>
                </form>
            </div>
        </div>
        @include('resellerhub::partials.toast')
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('pdf-download-btn')?.addEventListener('click', function () {
            const btn = this;
            const iconDef = document.getElementById('pdf-icon-default');
            const iconSpin = document.getElementById('pdf-icon-spinner');
            const btnText = document.getElementById('pdf-btn-text');
            if (iconDef && iconSpin && btnText) {
                iconDef.classList.add('hidden');
                iconSpin.classList.remove('hidden');
                btnText.textContent = 'در حال ساخت PDF...';
                btn.classList.add('opacity-75', 'pointer-events-none');
                setTimeout(() => {
                    iconDef.classList.remove('hidden');
                    iconSpin.classList.add('hidden');
                    btnText.textContent = 'دانلود PDF';
                    btn.classList.remove('opacity-75', 'pointer-events-none');
                }, 30000);
            }
        });
    </script>
@endpush
