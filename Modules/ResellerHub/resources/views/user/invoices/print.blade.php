@php
    use Carbon\Carbon;
    use Modules\ResellerHub\App\Enums\InvoiceStatus;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Support\AssetInliner;

    $currencyLabel = $currencyLabel ?? reseller_currency_label($invoice->currency ?? ($currency ?? null));

    $faNum = fn ($str) => reseller_fa_num($str);
    $toJalali = fn ($date) => $date ? reseller_format_jalali($date) : '---';
    $getPaymentMethodName = fn ($method, $gateway = null) => reseller_payment_method_label($method, $gateway);

    $statusName = $invoice->status_label;
    $statusColor = $invoice->status_enum?->hexColor() ?? '#64748b';

    $isCanceled = ($invoice->status_enum === InvoiceStatus::CANCELLED) || ($invoice->status === 'canceled') || str_contains($statusName, 'لغو');

    $subtotal = (float) ($invoice->subtotal ?? 0);
    $total = (float) ($invoice->total_amount ?? $invoice->total ?? 0);
    $paid = (float) ($invoice->paid_amount ?? 0);
    if ($paid <= 0 && $invoice->status === InvoiceStatus::PAID->value) {
        $paid = $total;
    }
    $due = max(0, $total - $paid);
    $isFullyPaid = $due <= 0.01 && $total > 0 && !$isCanceled;

    $sellerInfo = $sellerInfo ?? [
        'name' => $reseller->company_name ?: ($reseller->brand_title ?: config('app.name')),
        'brand_title' => $reseller->brand_title ?: $reseller->company_name,
        'economic_number' => $settings['reseller_economic_number'] ?? '',
        'national_id' => $settings['reseller_national_id'] ?? '',
        'registration_number' => $settings['reseller_registration_number'] ?? '',
        'phone_fax' => $reseller->support_phone ?? '',
        'email' => $reseller->support_email ?? '',
        'address' => $settings['reseller_address'] ?? '',
        'postal_code' => $settings['reseller_postal_code'] ?? '',
        'stamp_signature_image' => $settings['brand_seal_signature'] ?? null,
    ];

    $siteName = $siteName ?? ($sellerInfo['brand_title'] ?: $sellerInfo['name']);
    $appLogo = $appLogo ?? $reseller->brand_logo;

    // Assets are inlined through AssetInliner so the file is read (and encoded)
    // once per build instead of on every print request.
    $toDataUri = fn (?string $path) => AssetInliner::imageDataUri($path);

    $appLogoDataUri = $toDataUri($appLogo);
    $stampSignatureDataUri = $toDataUri($sellerInfo['stamp_signature_image'] ?? null);

    $stampStandardWidth = !empty($settings['reseller_stamp_standard_width']) ? (int) $settings['reseller_stamp_standard_width'] : null;
    $stampStandardHeight = !empty($settings['reseller_stamp_standard_height']) ? (int) $settings['reseller_stamp_standard_height'] : null;

    $stampStandardImgStyle = 'max-width: 100%; object-fit: contain; mix-blend-mode: multiply;';
    if ($stampStandardWidth && $stampStandardHeight) {
        $stampStandardImgStyle .= " width: {$stampStandardWidth}px; height: {$stampStandardHeight}px;";
    } elseif ($stampStandardWidth) {
        $stampStandardImgStyle .= " width: {$stampStandardWidth}px; height: auto;";
    } elseif ($stampStandardHeight) {
        $stampStandardImgStyle .= " height: {$stampStandardHeight}px; width: auto;";
    } else {
        $stampStandardImgStyle .= " max-height: 45px;";
    }

    $inlineAppCss = AssetInliner::appCss();

    $buyerName = $invoice->tenant?->company_name ?: ($invoice->sub_account_name ?: 'خریدار / مشتری محترم');
    $buyerOwner = $invoice->sub_account_name ?? '';
    $buyerPhone = $invoice->sub_account_mobile ?: '';
    $buyerEmail = $invoice->sub_account_email ?: '';
    $buyerDomain = $invoice->tenant?->full_domain ?: ($invoice->tenant?->domain ?? '');
    $buyerPackage = $invoice->package?->name ?: ($invoice->tenant?->package?->name ?? '');

    $fontBase64 = AssetInliner::fontBase64(resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf'));
@endphp

    <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صورتحساب {{ $faNum($invoice->invoice_number ?: $invoice->id) }}</title>
    <style>{!! $inlineAppCss !!}</style>
    <style>
        @if($fontBase64)
        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ $fontBase64 }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ $fontBase64 }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @endif

        body {
            font-family: 'IRANYekanX', Tahoma, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-container {
            margin: 20px auto;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .print-fab {
            display: none;
        }

        @media screen {
            .print-fab {
                display: flex;
                position: fixed;
                bottom: 1.5rem;
                right: 1.5rem;
                background-color: #4f46e5;
                color: white;
                padding: 0.75rem 1.25rem;
                border-radius: 9999px;
                box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
                align-items: center;
                gap: 0.5rem;
                font-weight: 700;
                cursor: pointer;
                transition: transform 0.2s;
                z-index: 50;
            }

            .print-fab:hover {
                transform: scale(1.05);
                background-color: #4338ca;
            }
        }

        .page-standard {
            width: 210mm;
            max-width: 100%;
            padding: 10mm 12mm;
            font-size: 10.5px;
            box-sizing: border-box;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-header .brand-logo img {
            max-height: 52px;
            max-width: 190px;
            object-fit: contain;
        }

        .invoice-header .invoice-meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            text-align: right;
            min-width: 220px;
        }

        .invoice-meta-header {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 6px;
        }

        .invoice-meta-header h1.invoice-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .status-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            border: 1px solid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-meta-dates {
            font-size: 10px;
            color: #64748b;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            border-top: 1px dashed #cbd5e1;
            padding-top: 5px;
            margin-top: 4px;
        }

        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 14px;
        }

        .party-block h2 {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 4px;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }

        .party-block p {
            font-size: 10.5px;
            color: #475569;
            line-height: 1.6;
            margin: 0;
        }

        .party-block p.party-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .section-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 4px;
            margin-bottom: 8px;
            border-bottom: 2px solid #4f46e5;
            display: inline-block;
        }

        .items-section-title {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-top: 0;
            margin-bottom: 8px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 24px;
        }

        .items-table thead tr {
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table th {
            padding: 8px 12px;
            font-size: 10.5px;
            font-weight: 700;
            color: #374151;
            border: none;
        }

        .items-table th.col-row {
            text-align: center;
            width: 40px;
        }

        .items-table th.col-desc {
            text-align: right;
        }

        .items-table th.col-qty {
            text-align: center;
            width: 70px;
        }

        .items-table th.col-discount {
            text-align: center;
            width: 85px;
        }

        .items-table th.col-amount {
            text-align: left;
            width: 140px;
        }

        .items-table td {
            padding: 8px 12px;
            font-size: 10.5px;
            vertical-align: middle;
            border: none;
        }

        .items-table td.col-row {
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .items-table td.col-desc {
            text-align: right;
            color: #1f2937;
        }

        .items-table td.col-qty {
            text-align: center;
            color: #1f2937;
        }

        .items-table td.col-discount {
            text-align: center;
            color: #1f2937;
        }

        .items-table td.col-amount {
            text-align: left;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
        }

        .items-table td.col-summary-label {
            text-align: left;
            padding-left: 24px;
            font-weight: 700;
            color: #374151;
        }

        .items-table tr.row-white {
            background-color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table tr.row-alt {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table .item-title {
            font-weight: 700;
            color: #111827;
        }

        .items-table .item-desc {
            color: #4b5563;
        }

        .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payments-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
        }

        .payments-table tbody td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }

        .invoice-footer {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            gap: 20px;
            page-break-before: avoid !important;
            break-before: avoid !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .signature-block {
            text-align: center;
            width: 170px;
            flex-shrink: 0;
            page-break-before: avoid !important;
            break-before: avoid !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .signature-block p.label {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 2px;
        }

        .signature-box {
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-block .seller-name {
            border-top: 1px dashed #cbd5e1;
            font-size: 9.5px;
            padding-top: 2px;
            margin-top: 2px;
            color: #0f172a;
            font-weight: 700;
        }

        @media print {
            body {
                background: white;
                margin: 0;
            }

            .print-container {
                margin: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
                min-height: auto !important;
                padding: 4mm 6mm !important;
            }

            .print-fab, .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 3mm;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .invoice-footer, .signature-block {
                page-break-before: avoid !important;
                break-before: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .items-table td, .items-table th {
                padding: 4px 6px !important;
            }

            .payments-table td, .payments-table th {
                padding: 3px 5px !important;
                font-size: 8.5px !important;
            }

            .section-title {
                margin-top: 6px !important;
                margin-bottom: 4px !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print"
     style="position: fixed; bottom: 1.5rem; right: 1.5rem; display: flex; align-items: center; gap: 0.75rem; z-index: 9999;">
    <button onclick="window.print()"
            class="print-fab"
            style="position: static !important; display: flex !important; margin: 0 !important;">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             style="width: 1.25rem; height: 1.25rem;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <span>چاپ / ذخیره PDF</span>
    </button>
    @if(!$isFullyPaid && !$isCanceled && $due > 0)
        <a href="{{ route('portal.invoices.pay', $invoice->id) }}"
           class="print-fab"
           style="position: static !important; display: flex !important; margin: 0 !important; background-color: #059669 !important; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.4) !important;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                 style="width: 1.25rem; height: 1.25rem;">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span>پرداخت آنلاین ({{ $faNum(number_format($due)) }} {{ $currencyLabel }})</span>
        </a>
    @endif
</div>

<div class="print-container page-standard flex flex-col relative">

    <div class="relative z-10 flex flex-col flex-1">

        {{-- Header --}}
        <header class="invoice-header">
            <div class="brand-logo">
                @if($appLogoDataUri)
                    <img src="{{ $appLogoDataUri }}" alt="{{ $siteName }}">
                @else
                    <h2 class="text-xl font-black text-gray-900">{{ $siteName }}</h2>
                @endif
            </div>
            <div class="invoice-meta-card">
                <div class="invoice-meta-header">
                    <h1 class="invoice-title">صورتحساب #{{ $faNum($invoice->invoice_number ?: $invoice->id) }}</h1>
                    <span class="status-tag"
                          style="background-color: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}44;">
                        {{ $statusName }}
                    </span>
                </div>
                <div class="invoice-meta-dates">
                    @if($invoice->issue_date || $invoice->created_at)
                        <span>تاریخ صدور: {{ $toJalali($invoice->issue_date ?: $invoice->created_at) }}</span>
                    @endif
                    @if($invoice->due_date)
                        <span>تاریخ سررسید: {{ $toJalali($invoice->due_date) }}</span>
                    @endif
                </div>
            </div>
        </header>

        {{-- Parties Grid --}}
        <div class="parties-grid">
            <div class="party-block">
                <h2>اطلاعات فروشنده</h2>
                <p class="party-name">{{ $sellerInfo['name'] ?: '---' }}</p>
                @if(!empty($sellerInfo['address']))
                    <p>آدرس: {{ $sellerInfo['address'] }}</p>
                @endif
                @if(!empty($sellerInfo['phone_fax']))
                    <p>شماره تماس: <span dir="ltr">{{ $faNum($sellerInfo['phone_fax']) }}</span></p>
                @endif
                @if(!empty($sellerInfo['email']))
                    <p>ایمیل: <span dir="ltr">{{ $sellerInfo['email'] }}</span></p>
                @endif
            </div>
            <div class="party-block">
                <h2>اطلاعات خریدار</h2>
                <p class="party-name">{{ $buyerName }}</p>
                @if($buyerOwner && $buyerOwner !== $buyerName)
                    <p>مسئول / رابط: {{ $buyerOwner }}</p>
                @endif
                @if($buyerPhone)
                    <p>شماره تماس: <span dir="ltr">{{ $faNum($buyerPhone) }}</span></p>
                @endif
                @if($buyerEmail)
                    <p>پست الکترونیک: <span dir="ltr">{{ $buyerEmail }}</span></p>
                @endif
                @if($buyerDomain)
                    <p>دامنه سامانه: <span dir="ltr">{{ $buyerDomain }}</span></p>
                @endif
                @if($buyerPackage)
                    <p>پلن سامانه: {{ $buyerPackage }}</p>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <h3 class="items-section-title">اقلام صورت حساب</h3>
        <table class="items-table mb-8">
            <thead>
            <tr>
                <th scope="col" class="col-row">ردیف</th>
                <th scope="col" class="col-desc">توضیحات خدمت / ماژول</th>
                <th scope="col" class="col-qty">مدت / تعداد</th>
                <th scope="col" class="col-discount">تخفیف</th>
                <th scope="col" class="col-amount">مجموع</th>
            </tr>
            </thead>
            <tbody>
            @php $zebraIdx = 0; @endphp
            @forelse($invoice->items as $index => $item)
                @php
                    $itemQty = (float) ($item->quantity ?? 1);
                    $displayQty = fmod($itemQty, 1.0) === 0.0 ? (int) $itemQty : $itemQty;
                    $rowBasePrice = $item->unit_price;
                    $rowDiscount = (float) ($item->discount_amount ?? 0);
                    $rowTotal = (float) ($item->total_price ?? ($rowBasePrice * $itemQty - $rowDiscount));

                    $durationLabel = !empty($item->duration_months) ? reseller_duration_label($item->duration_months) : '';
                @endphp
                <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                    <td class="col-row">{{ $faNum($index + 1) }}</td>
                    <td class="col-desc">
                        <span class="item-title">{{ $item->title ?: 'آیتم فاکتور' }}</span>
                        @if($item->description)
                            <span class="item-desc"> - {{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="col-qty">
                        @if($durationLabel)
                            {{ $durationLabel }}
                        @else
                            {{ $faNum($displayQty) }} عدد
                        @endif
                    </td>
                    <td class="col-discount">
                        @if($rowDiscount > 0)
                            <span style="color: #dc2626;">{{ $faNum(number_format($rowDiscount)) }}</span>
                        @else
                            ۰
                        @endif
                    </td>
                    <td class="col-amount">{{ $faNum(number_format($rowTotal)) }} {{ $currencyLabel }}</td>
                </tr>
            @empty
                <tr class="avoid-break row-white">
                    <td colspan="5" class="text-center py-4 text-gray-500">هیچ ردیف خدمتی ثبت نشده است.</td>
                </tr>
            @endforelse

            {{-- Summary Rows --}}
            @php
                $hasTaxOrDiscount = ($invoice->tax_amount > 0) || ($invoice->discount_amount > 0);
            @endphp

            @if($hasTaxOrDiscount)
                <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                    <td colspan="4" class="col-summary-label">جمع مبالغ پایه</td>
                    <td class="col-amount">{{ $faNum(number_format($subtotal ?: $invoice->items->sum('total_price'))) }} {{ $currencyLabel }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                        <td colspan="4" class="col-summary-label">
                            مالیات @if((float) ($invoice->tax_percent ?? 0) > 0)
                                ({{ $faNum((float) $invoice->tax_percent) }}٪)
                            @endif
                        </td>
                        <td class="col-amount">
                            + {{ $faNum(number_format($invoice->tax_amount)) }} {{ $currencyLabel }}</td>
                    </tr>
                @endif
                @if($invoice->discount_amount > 0)
                    <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                        <td colspan="4" class="col-summary-label" style="color: #dc2626;">مجموع تخفیف‌ها</td>
                        <td class="col-amount" style="color: #dc2626;">
                            − {{ $faNum(number_format($invoice->discount_amount)) }} {{ $currencyLabel }}</td>
                    </tr>
                @endif
                @if(!empty($invoice->meta['rounding']['is_rounded']))
                    @php
                        $roundDiff = (int)($invoice->meta['rounding']['diff'] ?? 0);
                    @endphp
                    <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                        <td colspan="4" class="col-summary-label"
                            style="{{ $roundDiff > 0 ? 'color: #059669;' : 'color: #dc2626;' }}">تعدیل رندسازی
                        </td>
                        <td class="col-amount" style="{{ $roundDiff > 0 ? 'color: #059669;' : 'color: #dc2626;' }}">
                            {{ $roundDiff > 0 ? '+ ' : '− ' }}{{ $faNum(number_format(abs($roundDiff))) }} {{ $currencyLabel }}</td>
                    </tr>
                @endif
            @endif

            @if($paid > 0)
                <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                    <td colspan="4" class="col-summary-label" style="color: #059669;">پرداخت شده</td>
                    <td class="col-amount"
                        style="color: #059669;">{{ $faNum(number_format($paid)) }} {{ $currencyLabel }}</td>
                </tr>
            @endif

            <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                <td colspan="4" class="col-summary-label">جمع کل فاکتور</td>
                <td class="col-amount">{{ $faNum(number_format($total)) }} {{ $currencyLabel }}</td>
            </tr>

            <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                <td colspan="4" class="col-summary-label">
                    @if($due > 0)
                        مانده قابل پرداخت
                    @else
                        تسویه شده
                    @endif
                </td>
                <td class="col-amount">{{ $faNum(number_format($due)) }} {{ $currencyLabel }}</td>
            </tr>
            </tbody>
        </table>

        {{-- Payment History --}}
        @php
            $validPayments = $invoice->payments ? $invoice->payments->reject(fn($p) => ($p->status ?? '') === 'canceled') : collect([]);
        @endphp

        @if($validPayments->isNotEmpty())
            <h3 class="section-title mt-3 mb-1">تاریخچه پرداخت‌ها</h3>
            <table class="payments-table mb-3">
                <thead>
                <tr>
                    <th scope="col" class="text-right">تاریخ</th>
                    <th scope="col" class="text-center">مبلغ</th>
                    <th scope="col" class="text-center">روش پرداخت</th>
                    <th scope="col" class="text-center">کد رهگیری / شناسه تراکنش</th>
                </tr>
                </thead>
                <tbody>
                @foreach($validPayments as $payment)
                    <tr>
                        <td class="text-right">{{ $toJalali($payment->paid_at ?: $payment->created_at) }}</td>
                        <td class="text-center font-bold"
                            style="color:#059669;">{{ $faNum(number_format($payment->amount)) }} {{ $currencyLabel }}</td>
                        <td class="text-center">{{ $getPaymentMethodName($payment->payment_method ?? $payment->method, $payment->gateway ?? null) }}</td>
                        <td class="text-center" dir="ltr">{{ $faNum($payment->transaction_id) ?: '---' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        {{-- Terms & Notes --}}
        @php
            $termsText = $settings['invoice_terms'] ?? ($settings['invoice_footer_note'] ?? '');
        @endphp
        @if(!empty(trim($termsText)))
            <div class="mt-4 text-xs text-gray-600 avoid-break border-t border-gray-200 pt-4">
                <strong class="text-gray-800">شرایط و قوانین:</strong><br>
                <div class="mt-1 leading-relaxed">{!! nl2br(e($termsText)) !!}</div>
            </div>
        @endif

        {{-- Signature & Stamp Block --}}
        <div class="invoice-footer mt-3 pt-2 avoid-break">
            <div class="signature-block"
                 style="{{ $stampStandardWidth ? 'min-width: ' . max(170, $stampStandardWidth + 10) . 'px; width: auto;' : '' }}">
                <p class="label">مهر و امضا:</p>
                <div class="signature-box"
                     style="{{ $stampStandardHeight ? 'min-height: ' . $stampStandardHeight . 'px; height: auto;' : '' }}">
                    @if($stampSignatureDataUri)
                        <img src="{{ $stampSignatureDataUri }}" alt="مهر و امضا"
                             style="{{ $stampStandardImgStyle }}">
                    @endif
                </div>
                <p class="seller-name">{{ $sellerInfo['name'] ?? '' }}</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>
