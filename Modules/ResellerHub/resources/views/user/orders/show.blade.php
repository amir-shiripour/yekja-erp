@php
    use App\Models\ActivityLog;
    use App\Models\Tenant;
    use Carbon\Carbon;
    use Modules\ResellerHub\App\Enums\OrderStatus;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerInvoicePayment;
    use Modules\ResellerHub\App\Models\ResellerModulePricing;
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Morilog\Jalali\Jalalian;
    use Modules\ResellerHub\App\Models\ResellerOrder;
    use Modules\ResellerHub\App\Models\ResellerInvoice;

    if (!isset($settings)) {
        $settings = [];
    }
@endphp
@extends('layouts.user')
@section('title', 'جزئیات سفارش: ' . reseller_fa_num($order->order_number))

@include('partials.jalali-date-picker')

@php
    $resellerId = $order->reseller_id ?? ($reseller->id ?? (auth()->user()?->reseller?->id ?? 0));
    $currency      = $currency ?? ($settings['currency'] ?? ResellerSetting::getCurrencyForReseller($resellerId));
    $currencyLabel = $currencyLabel ?? reseller_currency_label($currency);

    $relatedInvoices = $relatedInvoices ?? collect();
    if ($relatedInvoices->isEmpty()) {
        try {
            $relatedInvoices = $order->resolveRelatedInvoices();
        } catch (Throwable $e) {
            $relatedInvoices = collect();
        }
    }

    $invoice = $order->invoice ?? $relatedInvoices->first();

    $statusColor = $order->status_enum?->hexColor() ?? '#6b7280';
    $statusLabel = $order->status_label;
    $nonCanceledInvoices = $relatedInvoices->filter(fn($inv) => !$inv->isCancelled());
    $totalInvoicesAmount = $nonCanceledInvoices->sum('total');
    $totalInvoicesPaid = $nonCanceledInvoices->sum('paid_amount');
    $totalInvoicesRemaining = $nonCanceledInvoices->sum(fn($inv) => $inv->remainingAmount());

    $subAccount = $order->getSubAccountModel();
    $clientName = $subAccount?->name ?? ($order->sub_account_name ?? '—');
    $clientPhone = $subAccount?->mobile ?? ($order->sub_account_mobile ?? ($invoice?->sub_account_mobile ?? null));
    $clientEmail = $subAccount?->email ?? ($order->sub_account_email ?? ($invoice?->sub_account_email ?? null));
    $clientCompany = $subAccount?->company_name;

    $package = $order->isCustomPackage() ? null : ($order->package ?? $order->invoice?->package);
    $tenant = $order->tenant;

    $renewalDate = $order->renewal_date ?? $order->end_date;
    $daysDiff = $renewalDate ? ResellerHelper::daysRemaining($renewalDate) : null;
    $isExpired = $daysDiff !== null && $daysDiff < 0;
    $isNear = $daysDiff !== null && $daysDiff >= 0 && $daysDiff <= 7;


    $finalServicePrice = $order->invoice?->total ?? $order->total_amount;
    $isRenewalManual = ($order->renewal_price_type ?? 'auto') === 'manual';
    $calculatedRenewalPrice = $order->renewal_price ?: $order->total_amount;

    $orderCycle = $order->billing_cycle ?? 'yearly';
    $orderMonths = ResellerOrder::cycleToMonths($orderCycle);
    $baseRenewalPrice = (int)($isRenewalManual ? ($order->renewal_price ?? 0) : ($order->renewal_price ?: $order->total_amount));
    $monthlyRate = $orderMonths > 0 ? ($baseRenewalPrice / $orderMonths) : $baseRenewalPrice;

    $cyclePrices = [
        'monthly' => (int)($orderCycle === 'monthly' ? $baseRenewalPrice : round($monthlyRate * 1)),
        'quarterly' => (int)($orderCycle === 'quarterly' ? $baseRenewalPrice : round($monthlyRate * 3)),
        'semi_annual' => (int)($orderCycle === 'semi_annual' ? $baseRenewalPrice : round($monthlyRate * 6)),
        'yearly' => (int)(($orderCycle === 'yearly' || $orderCycle === 'annual') ? $baseRenewalPrice : round($monthlyRate * 12)),
    ];

    if (!$isRenewalManual && $order->invoice && $order->invoice->items->isNotEmpty()) {
        $modPrices = ['monthly' => 0, 'quarterly' => 0, 'semi_annual' => 0, 'yearly' => 0];
        $hasModulePricing = false;
        foreach ($order->invoice->items as $it) {
            if ($it->item_type === 'setup_fee') continue;
            $pr = null;
            if ($it->item_type === 'module' && !empty($it->item_key)) {
                $pr = ResellerModulePricing::where('reseller_id', $order->reseller_id)
                    ->where('module_slug', $it->item_key)
                    ->whereNull('feature_key')
                    ->first();
            } elseif ($it->item_type === 'feature' && !empty($it->item_key)) {
                $pr = ResellerModulePricing::where('reseller_id', $order->reseller_id)
                    ->where('feature_key', $it->item_key)
                    ->first();
            }
            if ($pr) {
                $hasModulePricing = true;
                $qty = max(1, (int)$it->quantity);
                $mp = (int)$pr->monthly_price;
                $modPrices['monthly'] += $qty * $mp;
                $modPrices['quarterly'] += $qty * ($pr->quarterly_price ?: ($mp * 3));
                $modPrices['semi_annual'] += $qty * ($pr->semi_annual_price ?: ($mp * 6));
                $modPrices['yearly'] += $qty * ($pr->yearly_price ?: ($mp * 12));
            }
        }
        if ($hasModulePricing && $modPrices['yearly'] > 0) {
            $cyclePrices = $modPrices;
        }
    }

    $cycleNextDates = [];
    $baseDate = $renewalDate ?: now();
    try {
        $baseJalali = reseller_to_jalali($baseDate) ?: Jalalian::now();
        $cycleNextDates = [
            'monthly' => reseller_format_jalali((clone $baseJalali)->addMonths(1), 'Y/m/d'),
            'quarterly' => reseller_format_jalali((clone $baseJalali)->addMonths(3), 'Y/m/d'),
            'semi_annual' => reseller_format_jalali((clone $baseJalali)->addMonths(6), 'Y/m/d'),
            'yearly' => reseller_format_jalali((clone $baseJalali)->addYears(1), 'Y/m/d'),
        ];
    } catch (Throwable $e) {
        $cycleNextDates = [
            'monthly' => '۱ ماه بعد',
            'quarterly' => '۳ ماه بعد',
            'semi_annual' => '۶ ماه بعد',
            'yearly' => '۱ سال بعد',
        ];
    }

    $nextRenewalDateCalculated = $cycleNextDates[$orderCycle] ?? ($cycleNextDates['yearly'] ?? null);

    $allStatuses = $statuses ?? OrderStatus::allForSelect();

    $availablePackages = $availablePackages ?? collect();
    $currentPackageId = (int)($package?->id ?? ($tenant?->package_id ?? 0));

    $packagesData = $availablePackages->map(function($p) use ($orderMonths) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'color' => $p->color ?: '#6366f1',
            'modules_count' => is_array($p->modules) ? count($p->modules) : 0,
            'modules' => $p->modules ?? [],
            'monthly_price' => (int)$p->monthly_price,
            'quarterly_price' => (int)($p->quarterly_price ?: ($p->monthly_price * 3)),
            'semi_annual_price' => (int)($p->semi_annual_price ?: ($p->monthly_price * 6)),
            'yearly_price' => (int)($p->yearly_price ?: ($p->monthly_price * 12)),
            'price_for_order_cycle' => $p->getPriceForMonths($orderMonths),
        ];
    });

    $allModulesInPackages = $availablePackages
        ->flatMap(fn($p) => (array)($p->modules ?? []))
        ->merge((array)($package?->modules ?? []))
        ->merge(collect($order->modules_snapshot ?? [])->pluck('slug'))
        ->filter()
        ->unique()
        ->values();
    $moduleTitlesMap = [];
    foreach ($allModulesInPackages as $modSlug) {
        $mSlugStr = is_array($modSlug) ? ($modSlug['slug'] ?? $modSlug['key'] ?? '') : (string)$modSlug;
        if ($mSlugStr !== '') {
            $moduleTitlesMap[$mSlugStr] = ResellerHelper::getModuleTitle($mSlugStr);
        }
    }

    $nowDate = Carbon::now()->startOfDay();
    $endDateObj = $order->end_date ? Carbon::parse($order->end_date)->startOfDay() : ($order->renewal_date ? Carbon::parse($order->renewal_date)->startOfDay() : null);
    $startDateObj = $order->start_date ? Carbon::parse($order->start_date)->startOfDay() : null;

    $remainingDaysCount = ($endDateObj && $endDateObj->greaterThan($nowDate)) ? $nowDate->diffInDays($endDateObj) : 0;
    $totalCycleDaysCount = ($startDateObj && $endDateObj && $endDateObj->greaterThan($startDateObj)) ? max(1, $startDateObj->diffInDays($endDateObj)) : ($orderMonths * 30);
    $currentPackageCyclePrice = $package ? $package->getPriceForMonths($orderMonths) : (int)$order->total_amount;
    $renewalPeriod      = $order->renewalPeriod();
    $isCustomPeriod     = $renewalPeriod['is_custom'];
    $renewalCycleKey    = $renewalPeriod['cycle'];
    $renewalDurationLbl = $renewalPeriod['label'];

    $renewalDisplayPrice = $isCustomPeriod
        ? (int) $calculatedRenewalPrice
        : (int) ($cyclePrices[$renewalCycleKey] ?? $calculatedRenewalPrice);

    try {
        $baseJalaliObj = reseller_to_jalali($baseDate);
        $renewalDisplayNextDate = ($isCustomPeriod && $renewalPeriod['days'])
            ? ($baseJalaliObj ? reseller_format_jalali((clone $baseJalaliObj)->addDays($renewalPeriod['days']), 'Y/m/d') : null)
            : ($cycleNextDates[$renewalCycleKey] ?? null);
    } catch (Throwable $e) {
        $renewalDisplayNextDate = null;
    }

    // Collect all payments across related invoices for history timeline
    $allOrderPayments = collect();
    foreach ($relatedInvoices as $relInv) {
        if ($relInv->relationLoaded('payments') && $relInv->payments->isNotEmpty()) {
            foreach ($relInv->payments as $pmt) {
                $allOrderPayments->push([
                    'payment' => $pmt,
                    'invoice' => $relInv,
                ]);
            }
        } elseif (method_exists($relInv, 'payments')) {
            try {
                foreach ($relInv->payments()->get() as $pmt) {
                    $allOrderPayments->push([
                        'payment' => $pmt,
                        'invoice' => $relInv,
                    ]);
                }
            } catch (Throwable $e) {}
        }
    }
    $allOrderPayments = $allOrderPayments->sortByDesc(fn($p) => $p['payment']->paid_at ?? $p['payment']->created_at);

    // Lifecycle indicators
    $isOrderCreated = true;
    $hasInvoices = $relatedInvoices->isNotEmpty();
    $areAllInvoicesPaid = $hasInvoices && $totalInvoicesRemaining <= 0;
    $isPartiallyPaid = $hasInvoices && $totalInvoicesPaid > 0 && $totalInvoicesRemaining > 0;
    $isTenantProvisioned = $order->isProvisioned();
    $isOrderActive = $order->isActive();

    // Prepare full chronological timeline
    $timelineEvents = collect();

    // 1. Order Creation / Online Purchase Log
    $isOnlineOrder = $order->isOnlinePurchase();
    $subAccountModel = $order->getSubAccountModel();
    $acquiredDate = $subAccountModel?->settings['acquired_at'] ?? null;
    $onlinePurchaseDate = $acquiredDate ? Carbon::parse($acquiredDate) : $order->created_at;

    if ($isOnlineOrder) {
        $timelineEvents->push([
            'type' => 'online_purchase',
            'title' => 'خرید آنلاین از وب‌سایت',
            'subtitle' => 'ثبت‌نام و خرید آنلاین پکیج «' . ($package?->name ?? 'طرح انتخابی') . '» از وب‌سایت توسط ' . $clientName,
            'badge' => 'خرید از وب‌سایت',
            'badge_class' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80',
            'icon_bg' => 'bg-gradient-to-tr from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-500/25',
            'date' => $onlinePurchaseDate,
            'user' => $clientName,
            'details' => array_filter([
                'کانال ثبت' => 'خرید آنلاین از وب‌سایت (پرتال مشتریان)',
                'مشتری / خریدار' => $clientName,
                'شماره تماس' => $clientPhone ? reseller_fa_num($clientPhone) : null,
                'پکیج خریداری‌شده' => $package?->name ?? 'طرح اختصاصی',
                'دوره اشتراک' => $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle),
                'مبلغ سفارش' => reseller_price($order->total_amount, $currencyLabel),
                'دامنه / ساب‌دامین' => $tenant ? ($tenant->custom_domain ?: (request()->getHttpHost() . '/' . $tenant->subdomain)) : null,
            ]),
        ]);
    } else {
        $timelineEvents->push([
            'type' => 'created',
            'title' => 'ثبت اولیه سفارش در سامانه',
            'subtitle' => 'ایجاد سفارش #' . reseller_fa_num($order->order_number) . ' برای ' . $clientName,
            'badge' => 'ثبت دستی',
            'badge_class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
            'icon_bg' => 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25',
            'date' => $order->created_at,
            'user' => $order->creator?->name ?? 'نماینده',
            'details' => [
                'کانال ثبت' => 'ثبت دستی توسط نماینده',
                'مبلغ پایه سفارش' => reseller_price($order->total_amount, $currencyLabel),
                'دوره صورتحساب' => $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle),
                'پکیج انتخابی' => $package?->name ?? 'طرح اختصاصی',
            ],
        ]);
    }

    // 2. Invoices
    foreach ($relatedInvoices as $relInv) {
        $isUpgrade = $relInv->isUpgrade();
        $isItemAddition = $relInv->isItemAddition();
        $isRenewal = !$isUpgrade && !$isItemAddition && (($relInv->id != $order->invoice_id && $relInv->isManualRenewal()) || $relInv->parent_invoice_id);
        $isOnlineInvoice = $relInv->isOnlinePurchase();
        $invBadge = $relInv->status_badge;

        if ($isUpgrade) {
            $fromName = $relInv->meta['from_package_name'] ?? 'طرح قبلی';
            $toName = $relInv->meta['to_package_name'] ?? ($relInv->package?->name ?? 'طرح جدید');
            $timelineEvents->push([
                'type' => 'upgrade_invoice',
                'title' => 'صدور فاکتور ارتقای پکیج #' . reseller_fa_num($relInv->invoice_number),
                'subtitle' => "ارتقا از «{$fromName}» به «{$toName}» (مبلغ: " . reseller_price($relInv->total, $currencyLabel) . ')',
                'badge' => 'ارتقای پکیج (' . $relInv->status_label . ')',
                'badge_class' => $invBadge['bg'],
                'icon_bg' => 'bg-amber-500 text-white shadow-md shadow-amber-500/25',
                'date' => $relInv->created_at ?? $relInv->issue_date,
                'user' => $relInv->creator?->name,
                'details' => array_filter([
                    'پکیج مبدأ' => $fromName,
                    'پکیج مقصد' => $toName,
                    'وضعیت فاکتور' => $relInv->status_label,
                    'مبلغ کل' => reseller_price($relInv->total, $currencyLabel),
                    'پرداخت‌شده' => reseller_price($relInv->paid_amount, $currencyLabel),
                    'مانده' => reseller_price($relInv->remainingAmount(), $currencyLabel),
                    'روش محاسبه' => match ($relInv->meta['calculation_mode'] ?? null) {
                        'prorated' => 'روزشمار باقیمانده دوره (' . reseller_fa_num($relInv->meta['remaining_days'] ?? 0) . ' روز)',
                        'full' => 'مابه‌التفاوت کامل دوره',
                        'custom' => 'مبلغ دلخواه تعیین‌شده',
                        default => null,
                    },
                ]),
                'link' => route('reseller.invoices.show', $relInv->id),
                'link_text' => 'مشاهده فاکتور #' . reseller_fa_num($relInv->invoice_number),
            ]);

            $timelineEvents->push([
                'type' => 'package_upgraded',
                'title' => "ارتقای پکیج سامانه به «{$toName}»",
                'subtitle' => "اعمال پکیج جدید «{$toName}» و به‌روزرسانی دسترسی‌های سامانه CRM",
                'badge' => 'ارتقا یافته',
                'badge_class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'icon_bg' => 'bg-amber-600 text-white shadow-md shadow-amber-500/25',
                'date' => $relInv->created_at,
                'user' => $relInv->creator?->name,
                'details' => [
                    'پکیج قبلی' => $fromName,
                    'پکیج جدید' => $toName,
                    'فاکتور مرتبط' => '#' . reseller_fa_num($relInv->invoice_number),
                ],
            ]);
        } elseif ($isItemAddition) {
            $timelineEvents->push([
                'type' => 'item_addition_invoice',
                'title' => 'صدور فاکتور افزودن ماژول / قابلیت #' . reseller_fa_num($relInv->invoice_number),
                'subtitle' => 'مبلغ کل: ' . reseller_price($relInv->total, $currencyLabel),
                'badge' => 'افزودن ماژول (' . $relInv->status_label . ')',
                'badge_class' => $invBadge['bg'],
                'icon_bg' => 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25',
                'date' => $relInv->created_at ?? $relInv->issue_date,
                'user' => $relInv->creator?->name,
                'details' => [
                    'سررسید پرداخت' => $relInv->due_date ? reseller_format_jalali($relInv->due_date) : '—',
                    'پرداخت‌شده' => reseller_price($relInv->paid_amount, $currencyLabel),
                    'مانده فاکتور' => reseller_price($relInv->remainingAmount(), $currencyLabel),
                ],
                'link' => route('reseller.invoices.show', $relInv->id),
                'link_text' => 'مشاهده فاکتور #' . reseller_fa_num($relInv->invoice_number),
            ]);
        } else {
            $invTitle = $isRenewal
                ? ('صدور فاکتور تمدید دوره‌ای #' . reseller_fa_num($relInv->invoice_number))
                : ($isOnlineInvoice ? ('صدور صورتحساب خرید از وب‌سایت #' . reseller_fa_num($relInv->invoice_number)) : ('صدور صورتحساب سفارش #' . reseller_fa_num($relInv->invoice_number)));

            $invSubtitle = 'مبلغ کل: ' . reseller_price($relInv->total, $currencyLabel) . ($isOnlineInvoice ? ' (صادره در پرتال خرید وب‌سایت)' : '');

            $timelineEvents->push([
                'type' => $isRenewal ? 'renewal_invoice' : 'invoice',
                'title' => $invTitle,
                'subtitle' => $invSubtitle,
                'badge' => $relInv->status_label,
                'badge_class' => $invBadge['bg'],
                'icon_bg' => $isRenewal ? 'bg-cyan-600 text-white shadow-md shadow-cyan-500/25' : ($isOnlineInvoice ? 'bg-purple-600 text-white shadow-md shadow-purple-500/25' : 'bg-purple-600 text-white shadow-md shadow-purple-500/25'),
                'date' => $relInv->created_at ?? $relInv->issue_date,
                'user' => $relInv->creator?->name ?? ($isOnlineInvoice ? 'سیستم (پرتال وب‌سایت)' : null),
                'details' => array_filter([
                    'کانال صدور' => $isOnlineInvoice ? 'پرتال خرید آنلاین وب‌سایت' : 'ثبت در سیستم توسط نماینده',
                    'وضعیت فاکتور' => $relInv->status_label,
                    'سررسید پرداخت' => $relInv->due_date ? reseller_format_jalali($relInv->due_date) : '—',
                    'پرداخت‌شده' => reseller_price($relInv->paid_amount, $currencyLabel),
                    'مانده فاکتور' => reseller_price($relInv->remainingAmount(), $currencyLabel),
                    'روش تسویه' => $relInv->isPaid()
                        ? ($isOnlineInvoice ? 'پرداخت آنلاین از درگاه بانکی سایت' : 'تسویه در داشبورد')
                        : 'در انتظار پرداخت (امکان پرداخت آنلاین یا از داشبورد)',
                ]),
                'link' => route('reseller.invoices.show', $relInv->id),
                'link_text' => 'مشاهده فاکتور #' . reseller_fa_num($relInv->invoice_number),
            ]);
        }
    }

    // 3. Payments
    $recordedPaymentInvoiceIds = [];
    foreach ($allOrderPayments as $item) {
        $pmt = $item['payment'];
        $pmtInv = $item['invoice'];
        $recordedPaymentInvoiceIds[] = $pmtInv->id;
        $methodLabel = ResellerInvoicePayment::formatMethodName($pmt->method, $pmt->gateway);

        // Determine payment origin / channel
        $rawMethod = strtolower((string)$pmt->method);
        $isOnlinePayment = in_array($rawMethod, ['online', 'gateway', 'zarinpal', 'zibal', 'behpardakht', 'mellat', 'saman', 'sep', 'payir', 'idpay', 'nextpay', 'asanpardakht', 'parsian', 'pasargad', 'sadad'], true)
            || !empty($pmt->gateway)
            || ($pmtInv->isOnlinePurchase() && (str_contains((string)$pmt->notes, 'درگاه') || str_contains((string)$pmt->notes, 'سایت') || ($pmt->created_at && $pmtInv->created_at && $pmt->created_at->diffInMinutes($pmtInv->created_at) < 60)));

        $channelLabel = $isOnlinePayment ? 'درگاه پرداخت آنلاین وب‌سایت' : 'داشبورد مدیریت نماینده';
        $channelBadge = $isOnlinePayment ? 'پرداخت آنلاین از سایت' : 'پرداخت در داشبورد';
        $channelBadgeClass = $isOnlinePayment
            ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80'
            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200';
        $channelIconBg = $isOnlinePayment
            ? 'bg-gradient-to-tr from-purple-600 to-emerald-600 text-white shadow-md shadow-purple-500/25'
            : 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25';

        $timelineEvents->push([
            'type' => $isOnlinePayment ? 'online_payment' : 'payment',
            'title' => ($isOnlinePayment ? 'پرداخت آنلاین از درگاه وب‌سایت: ' : 'ثبت پرداخت فاکتور در داشبورد: ') . reseller_price($pmt->amount, $currencyLabel),
            'subtitle' => 'فاکتور مرتبط #' . reseller_fa_num($pmtInv->invoice_number) . ' — ' . $channelLabel,
            'badge' => $pmt->status === 'canceled' ? 'لغو شده' : $channelBadge,
            'badge_class' => $pmt->status === 'canceled' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200' : $channelBadgeClass,
            'icon_bg' => $pmt->status === 'canceled' ? 'bg-rose-600 text-white shadow-md shadow-rose-500/25' : $channelIconBg,
            'date' => $pmt->paid_at ?? $pmt->created_at,
            'user' => $pmt->user?->name ?? ($isOnlinePayment ? 'مشتری (پرداخت آنلاین)' : null),
            'details' => array_filter([
                'محل / بستر پرداخت' => $channelLabel,
                'روش پرداخت' => $methodLabel,
                'مبلغ پرداختی' => reseller_price($pmt->amount, $currencyLabel),
                'کد رهگیری بانکی' => $pmt->transaction_id ? reseller_fa_num($pmt->transaction_id) : '—',
                'فاکتور مرتبط' => '#' . reseller_fa_num($pmtInv->invoice_number),
                'ثبت‌کننده' => $pmt->user?->name ?? ($isOnlinePayment ? 'پرداخت آنلاین مشتری' : 'داشبورد'),
                'یادداشت پرداخت' => $pmt->notes ?: null,
            ]),
            'link' => route('reseller.invoices.show', $pmtInv->id),
            'link_text' => 'مشاهده فاکتور #' . reseller_fa_num($pmtInv->invoice_number),
        ]);
    }

    // 3.1 Synthetic payment event for paid invoices without explicit payment records
    foreach ($relatedInvoices as $relInv) {
        if ($relInv->isPaid() && !in_array($relInv->id, $recordedPaymentInvoiceIds)) {
            $isOnlinePaid = $relInv->isOnlinePurchase();
            $channelLabel = $isOnlinePaid ? 'درگاه پرداخت آنلاین وب‌سایت' : 'داشبورد مدیریت نماینده';
            $channelBadge = $isOnlinePaid ? 'پرداخت آنلاین از سایت' : 'تسویه در داشبورد';

            $timelineEvents->push([
                'type' => $isOnlinePaid ? 'online_payment' : 'payment',
                'title' => ($isOnlinePaid ? 'تسویه و پرداخت آنلاین از سایت: ' : 'تسویه کامل فاکتور در داشبورد: ') . reseller_price($relInv->total, $currencyLabel),
                'subtitle' => 'فاکتور مرتبط #' . reseller_fa_num($relInv->invoice_number) . ' — ' . $channelLabel,
                'badge' => $channelBadge,
                'badge_class' => $isOnlinePaid
                    ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80'
                    : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200',
                'icon_bg' => $isOnlinePaid
                    ? 'bg-gradient-to-tr from-purple-600 to-emerald-600 text-white shadow-md shadow-purple-500/25'
                    : 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25',
                'date' => $relInv->paid_at ?? $relInv->updated_at ?? $relInv->created_at,
                'user' => $relInv->creator?->name ?? ($isOnlinePaid ? 'مشتری (پرداخت آنلاین)' : null),
                'details' => array_filter([
                    'محل / بستر پرداخت' => $channelLabel,
                    'وضعیت' => 'تسویه موفق فاکتور',
                    'مبلغ تسویه‌شده' => reseller_price($relInv->total, $currencyLabel),
                    'فاکتور مرتبط' => '#' . reseller_fa_num($relInv->invoice_number),
                    'کد پیگیری' => $relInv->transaction_ref ? reseller_fa_num($relInv->transaction_ref) : null,
                ]),
                'link' => route('reseller.invoices.show', $relInv->id),
                'link_text' => 'مشاهده فاکتور #' . reseller_fa_num($relInv->invoice_number),
            ]);
        }
    }

    // 4. Provisioning
    if ($order->isProvisioned()) {
        $timelineEvents->push([
            'type' => 'provisioned',
            'title' => 'تحویل و فعال‌سازی سامانه CRM',
            'subtitle' => 'استقرار و راه‌اندازی موفقیت‌آمیز سامانه مشتری',
            'badge' => 'تحویل داده شده',
            'badge_class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200',
            'icon_bg' => 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25',
            'date' => $tenant?->created_at ?? $order->updated_at,
            'details' => [
                'نام سامانه' => $tenant?->name ?? 'سامانه CRM',
                'دامنه / ساب‌دامین' => $tenant ? ($tenant->custom_domain ?: (request()->getHttpHost() . '/' . $tenant->subdomain)) : '—',
                'ماژول‌های فعال' => reseller_fa_num(count($order->modules_snapshot ?? [])) . ' ماژول',
            ],
        ]);
    } elseif ($order->provisioning_status === 'failed') {
        $timelineEvents->push([
            'type' => 'provision_failed',
            'title' => 'خطا در تحویل خودکار سامانه CRM',
            'subtitle' => 'فرایند استقرار با خطا مواجه شده است',
            'badge' => 'خطای استقرار',
            'badge_class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200',
            'icon_bg' => 'bg-rose-600 text-white shadow-md shadow-rose-500/25',
            'date' => $order->updated_at,
            'details' => [
                'وضعیت' => 'نیاز به بررسی اتصال دیتابیس یا اجرای دستی تحویل',
            ],
        ]);
    }

    // 5. Updates
    if ($order->updated_at && $order->updated_at->diffInMinutes($order->created_at) > 5) {
        $timelineEvents->push([
            'type' => 'updated',
            'title' => 'آخرین به‌روزرسانی و تغییر مشخصات',
            'subtitle' => 'تغییر وضعیت یا مشخصات سفارش',
            'badge' => $statusLabel,
            'badge_class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200',
            'icon_bg' => 'bg-amber-600 text-white shadow-md shadow-amber-500/25',
            'date' => $order->updated_at,
            'details' => [
                'وضعیت نهایی' => $statusLabel,
                'سررسید تمدید فعلی' => $renewalDate ? reseller_format_jalali($renewalDate) : '—',
            ],
        ]);
    }

    // 6. Activity Logs & Removal/Addition Operations
    if (class_exists(ActivityLog::class)) {
        try {
            $logs = ActivityLog::where(function ($q) use ($order, $relatedInvoices) {
                $q->where('subject_type', get_class($order))->where('subject_id', $order->id);
                if ($order->tenant_id) {
                    $q->orWhere(function ($tq) use ($order) {
                        $tq->where('subject_type', Tenant::class)->where('subject_id', $order->tenant_id);
                    });
                }
                if ($relatedInvoices->isNotEmpty()) {
                    $q->orWhere(function ($iq) use ($relatedInvoices) {
                        $iq->where('subject_type', ResellerInvoice::class)->whereIn('subject_id', $relatedInvoices->pluck('id'));
                    });
                }
            })->with('user')->get();

            foreach ($logs as $log) {
                $isRemoval = in_array($log->activity_type, ['order_items_removed', 'module_removed', 'feature_removed']) || str_contains($log->description, 'حذف') || str_contains($log->description, 'کاهش');
                $isAdd = in_array($log->activity_type, ['order_items_added', 'module_added', 'feature_added']) || str_contains($log->description, 'افزودن');

                $rawItems = $log->properties['removed_detailed'] ?? ($log->properties['removed_names'] ?? ($log->properties['items'] ?? []));
                $itemsList = [];
                if (is_array($rawItems)) {
                    $itemsList = array_map(function($it) {
                        if (is_string($it)) return $it;
                        if (is_array($it)) {
                            $prefix = ($it['type'] ?? '') === 'module' ? 'ماژول ' : 'قابلیت ';
                            return $prefix . '«' . ($it['title'] ?? $it['slug'] ?? '') . '»';
                        }
                        return (string)$it;
                    }, $rawItems);
                }

                $timelineEvents->push([
                    'type' => $isRemoval ? 'order_items_removed' : ($isAdd ? 'order_items_added' : 'activity_log'),
                    'title' => $log->description,
                    'subtitle' => $isRemoval ? 'کاهش/حذف از سفارش و لغو دسترسی‌های متصل' : ($isAdd ? 'افزودن ماژول/قابلیت و فعال‌سازی دسترسی‌ها' : 'عملیات مدیریتی سفارش'),
                    'badge' => $isRemoval ? 'حذف اقلام' : ($isAdd ? 'افزودن اقلام' : 'ثبت رویداد'),
                    'badge_class' => $isRemoval ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200' : ($isAdd ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-200'),
                    'icon_bg' => $isRemoval ? 'bg-rose-600 text-white shadow-md shadow-rose-500/25' : ($isAdd ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25' : 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25'),
                    'date' => $log->created_at,
                    'user' => $log->user?->name,
                    'items_list' => $itemsList,
                    'details' => array_filter([
                        'نوع رویداد' => $isRemoval ? 'حذف ماژول / قابلیت' : ($isAdd ? 'الحاق ماژول / قابلیت' : 'لاگ سیستم'),
                        'کاربر ثبت‌کننده' => $log->user?->name,
                        'آی‌پی' => $log->ip_address,
                    ]),
                ]);
            }
        } catch (Throwable $e) {}
    }

    // 7. Parse historical notes (for operations performed previously or logged directly in order notes)
    if (!empty($order->notes)) {
        $hasRemovalActivityLogs = $timelineEvents->contains(function ($ev) {
            return ($ev['type'] ?? '') === 'order_items_removed';
        });
        $hasAddActivityLogs = $timelineEvents->contains(function ($ev) {
            return ($ev['type'] ?? '') === 'order_items_added';
        });

        $notesLines = array_filter(explode("\n", (string)$order->notes));
        foreach ($notesLines as $line) {
            $line = trim($line);
            if (str_contains($line, 'کاهش/حذف') || str_contains($line, 'حذف گروهی') || str_contains($line, 'افزودن ماژول/فیچر')) {
                $isRemoval = str_contains($line, 'کاهش') || str_contains($line, 'حذف');

                if ($isRemoval && $hasRemovalActivityLogs) {
                    continue;
                }
                if (!$isRemoval && $hasAddActivityLogs) {
                    continue;
                }

                $logDate = null;
                if (preg_match('/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $line, $matches)) {
                    try {
                        $logDate = Carbon::parse($matches[1]);
                    } catch (Throwable) {}
                }

                // Avoid duplicating if an activity_log entry exists for this exact time
                $alreadyExists = $timelineEvents->contains(function ($ev) use ($logDate) {
                    if ($logDate && $ev['date'] && abs(Carbon::parse($ev['date'])->diffInSeconds($logDate)) < 120) {
                        return true;
                    }
                    return false;
                });

                if (!$alreadyExists) {
                    $noteItems = [];
                    if (str_contains($line, 'شامل:')) {
                        $parts = explode('شامل:', $line, 2);
                        if (!empty($parts[1])) {
                            $noteItems = array_filter(array_map('trim', explode('،', $parts[1])));
                        }
                    }

                    $timelineEvents->push([
                        'type' => $isRemoval ? 'order_items_removed' : 'order_items_added',
                        'title' => $line,
                        'subtitle' => $isRemoval ? 'کاهش/حذف از سفارش و لغو دسترسی‌های متصل' : 'افزودن و الحاق اقلام به سفارش',
                        'badge' => $isRemoval ? 'حذف اقلام' : 'افزودن اقلام',
                        'badge_class' => $isRemoval ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200',
                        'icon_bg' => $isRemoval ? 'bg-rose-600 text-white shadow-md shadow-rose-500/25' : 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25',
                        'date' => $logDate ?: $order->updated_at,
                        'user' => $order->creator?->name,
                        'items_list' => $noteItems,
                        'details' => [
                            'شرح عملیات' => $line,
                            'ثبت‌شده توسط' => $order->creator?->name ?? 'مدیر سیستم',
                        ],
                    ]);
                }
            }
        }
    }

    // Format any embedded Gregorian dates in all timeline events to Jalali
    $sortedTimelineEvents = $timelineEvents->map(function ($ev) {
        if (!empty($ev['title'])) {
            $ev['title'] = reseller_format_text_dates($ev['title']);
        }
        if (!empty($ev['subtitle'])) {
            $ev['subtitle'] = reseller_format_text_dates($ev['subtitle']);
        }
        if (!empty($ev['details']) && is_array($ev['details'])) {
            foreach ($ev['details'] as $k => $v) {
                $ev['details'][$k] = reseller_format_text_dates($v);
            }
        }
        return $ev;
    })->sortByDesc('date')->values();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="orderManager()">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('reseller.orders.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">لیست
                سفارشات</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 rtl:rotate-180" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span
                class="text-gray-900 dark:text-white font-bold truncate tabular-nums">{{ reseller_fa_num($order->order_number) }}</span>
        </nav>

        {{-- Header (Services 1:1) --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight flex-wrap">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span>جزئیات سفارش</span>
                <span
                    class="text-indigo-600 dark:text-indigo-400 dir-ltr font-black">{{ reseller_fa_num($order->order_number) }}</span>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $order->source_badge }}">
                    @if($order->isOnlinePurchase())
                        <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>{{ $order->source_label }}</span>
                    @else
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ $order->source_label }}</span>
                    @endif
                </span>
            </h1>
            <div class="flex items-center flex-wrap gap-3">
                @if($availablePackages->count() > 1)
                    <button type="button"
                            @click="activeTab = 'package_upgrade'"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-sm font-black text-white shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 transition-all active:scale-[0.98] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        ارتقای پکیج (Upgrade)
                    </button>
                @endif
                <a href="{{ route('reseller.orders.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    بازگشت
                </a>
            </div>
        </div>

        {{-- Session Alerts --}}
        @if(session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 p-1.5 rounded-full dark:bg-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-800 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-rose-100 text-rose-600 p-1.5 rounded-full dark:bg-rose-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                {{ session('error') }}
            </div>
        @endif

        {{-- Tabs Navigation Bar --}}
        <div role="tablist" aria-label="بخش‌های جزئیات سفارش" class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-700/60 pb-3 overflow-x-auto">
            <button type="button" role="tab" id="tab-details" aria-controls="tabpanel-details" :aria-selected="activeTab === 'details' ? 'true' : 'false'" @click="activeTab = 'details'"
                    class="flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs sm:text-sm font-black transition-all cursor-pointer shrink-0"
                    :class="activeTab === 'details' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 ring-2 ring-indigo-500/30' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>مشخصات و گردش کار سفارش</span>
            </button>

            <button type="button" role="tab" id="tab-package_upgrade" aria-controls="tabpanel-package_upgrade" :aria-selected="activeTab === 'package_upgrade' ? 'true' : 'false'" @click="activeTab = 'package_upgrade'"
                    class="flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs sm:text-sm font-black transition-all cursor-pointer relative shrink-0"
                    :class="activeTab === 'package_upgrade' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25 ring-2 ring-amber-500/30' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>پکیج، ماژول‌های فعال & ارتقای پکیج</span>
                @if($package)
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="activeTab === 'package_upgrade' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300'">
                        {{ $package->name }}
                    </span>
                @else
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="activeTab === 'package_upgrade' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300'">
                        پکیج سفارشی
                    </span>
                @endif
            </button>

            <button type="button" role="tab" id="tab-history" aria-controls="tabpanel-history" :aria-selected="activeTab === 'history' ? 'true' : 'false'" @click="activeTab = 'history'"
                    class="flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs sm:text-sm font-black transition-all cursor-pointer relative shrink-0"
                    :class="activeTab === 'history' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/25 ring-2 ring-violet-500/30' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>تاریخچه و وضعیت سفارش</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1.5"
                      :class="activeTab === 'history' ? 'bg-white/20 text-white' : 'bg-violet-100 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300'">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: {{ $statusColor }}"></span>
                    {{ $statusLabel }}
                </span>
            </button>

            @if($addableModules->count() > 0)
                <button type="button" role="tab" id="tab-add_items" aria-controls="tabpanel-add_items" :aria-selected="activeTab === 'add_items' ? 'true' : 'false'" @click="activeTab = 'add_items'"
                        class="flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs sm:text-sm font-black transition-all cursor-pointer relative shrink-0"
                        :class="activeTab === 'add_items' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 ring-2 ring-indigo-500/30' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>مدیریت ماژول‌ها و قابلیت‌ها</span>
                </button>
            @endif
        </div>
        @include('resellerhub::user.orders.tabs.tab-details')
        @include('resellerhub::user.orders.tabs.tab-upgrade')
        @include('resellerhub::user.orders.tabs.tab-history')
        @include('resellerhub::user.orders.tabs.tab-add-items')
        @include('resellerhub::user.orders.modals.modal-renew')
        @include('resellerhub::user.orders.modals.modal-upgrade')
        @include('resellerhub::partials.toast')
    </div>

    @push('scripts')
        @include('resellerhub::user.orders.partials.order-script')
    @endpush
@endsection
