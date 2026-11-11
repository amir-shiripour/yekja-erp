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

    $stampOfficialWidth = !empty($settings['reseller_stamp_official_width']) ? (int) $settings['reseller_stamp_official_width'] : null;
    $stampOfficialHeight = !empty($settings['reseller_stamp_official_height']) ? (int) $settings['reseller_stamp_official_height'] : null;

    $stampOfficialImgStyle = 'max-width: 100%; object-fit: contain; mix-blend-mode: multiply;';
    if ($stampOfficialWidth && $stampOfficialHeight) {
        $stampOfficialImgStyle .= " width: {$stampOfficialWidth}px; height: {$stampOfficialHeight}px;";
    } elseif ($stampOfficialWidth) {
        $stampOfficialImgStyle .= " width: {$stampOfficialWidth}px; height: auto;";
    } elseif ($stampOfficialHeight) {
        $stampOfficialImgStyle .= " height: {$stampOfficialHeight}px; width: auto;";
    } else {
        $stampOfficialImgStyle .= " max-height: 45px;";
    }

    $sellerFields = array_values(array_filter([
        ['label' => 'نام شخص حقیقی/حقوقی', 'value' => $sellerInfo['name'] ?? '', 'span' => 4, 'numeric' => false],
        ['label' => 'شماره اقتصادی', 'value' => $sellerInfo['economic_number'] ?? '', 'span' => 2, 'numeric' => true],
        ['label' => 'شناسه ملی / کد ملی', 'value' => $sellerInfo['national_id'] ?? '', 'span' => 2, 'numeric' => true],
        ['label' => 'شماره ثبت', 'value' => $sellerInfo['registration_number'] ?? '', 'span' => 2, 'numeric' => true],
        ['label' => 'کد پستی', 'value' => $sellerInfo['postal_code'] ?? '', 'span' => 2, 'numeric' => true],
        ['label' => 'تلفن/نمابر', 'value' => $sellerInfo['phone_fax'] ?? '', 'span' => 2, 'numeric' => true],
        ['label' => 'نشانی کامل', 'value' => $sellerInfo['address'] ?? '', 'span' => 4, 'numeric' => false],
    ], fn ($f) => !empty(trim((string) $f['value']))));

    $hasSellerBlock = !empty($sellerFields);

    $buyerName = $invoice->tenant?->company_name ?: ($invoice->sub_account_name ?: 'خریدار / مشتری محترم');
    $buyerOwner = $invoice->sub_account_name ?? '';
    $buyerPhone = $invoice->sub_account_mobile ?: '';
    $buyerEmail = $invoice->sub_account_email ?: '';
    $buyerDomain = $invoice->tenant?->full_domain ?: ($invoice->tenant?->domain ?? '');
    $buyerPackage = $invoice->package?->name ?: ($invoice->tenant?->package?->name ?? '');

    $orientation = $settings['reseller_official_invoice_orientation'] ?? 'portrait';
    $containerWidth = $orientation === 'landscape' ? '297mm' : '210mm';
    $containerHeight = $orientation === 'landscape' ? '210mm' : '297mm';
    $pageSize = $orientation === 'landscape' ? 'A4 landscape' : 'A4 portrait';

    $inlineAppCss = AssetInliner::appCss();

    $fontBase64 = AssetInliner::fontBase64(resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf'));
@endphp

    <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صورتحساب رسمی فروش کالا و خدمات
        شماره {{ $faNum($invoice->invoice_number ?: $invoice->id) }}</title>
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
            background-color: #f3f4f6;
            color: #000;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .a4-container {
            width: {{ $containerWidth }};
            max-width: 100%;
            padding: {{ $orientation === 'landscape' ? '6mm 8mm' : '8mm 12mm' }};
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        @media print {
            @page {
                size: {{ $pageSize }};
                margin: 3mm;
            }

            body {
                background: white;
                margin: 0;
            }

            .a4-container {
                margin: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
                min-height: auto !important;
                padding: {{ $orientation === 'landscape' ? '4mm 6mm' : '5mm 8mm' }}      !important;
            }

            .no-print, .print-actions-bar, .mobile-only-view {
                display: none !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .official-signature-block {
                page-break-before: avoid !important;
                break-before: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .table-cell-border {
                padding: {{ $orientation === 'landscape' ? '1.5px 3px' : '2px 4px' }}      !important;
            }
        }

        .border-official {
            border-color: #000;
        }

        .table-cell-border {
            border: 1px solid #000;
            padding: {{ $orientation === 'landscape' ? '2px 4px' : '3.5px 5px' }};
            text-align: center;
        }

        .header-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        thead {
            display: table-header-group;
        }

        .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .status-official-tag {
            display: inline-block;
            padding: 1.5px 7px;
            border-radius: 3px;
            font-size: 9.5px;
            font-weight: bold;
            border: 1px solid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .mobile-only-view {
            display: none;
        }

        @media screen {
            .print-actions-bar {
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                z-index: 50;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .print-fab {
                background-color: #4f46e5;
                color: white;
                font-weight: 700;
                padding: 0.75rem 1.5rem;
                border-radius: 9999px;
                box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                cursor: pointer;
                border: none;
                text-decoration: none;
                transition: transform 0.2s, background-color 0.2s;
                font-size: 13px;
                white-space: nowrap;
            }

            .print-fab:hover {
                transform: scale(1.05);
                background-color: #4338ca;
            }

            .print-fab--pay {
                background-color: #059669;
                font-size: 13px;
                box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.35);
            }

            .print-fab--pay:hover {
                background-color: #047857;
            }
        }

        @media screen and (max-width: 639px) {
            .a4-container {
                width: calc(100% - 16px) !important;
                margin: 10px auto 84px !important;
                padding: 12px !important;
                border-radius: 12px;
                font-size: 11.5px;
            }

            .print-actions-bar {
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                padding: 10px 12px !important;
                background: rgba(255, 255, 255, 0.94);
                backdrop-filter: blur(8px);
                border-top: 1px solid #cbd5e1;
                box-shadow: 0 -4px 18px rgba(15, 23, 42, 0.08);
                gap: 8px !important;
            }

            .print-fab {
                flex: 1;
                padding: 10px 12px !important;
                border-radius: 10px !important;
                font-size: 12px !important;
                box-shadow: none !important;
            }

            .print-fab:hover {
                transform: none;
            }

            .official-top-header {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
                margin-bottom: 10px !important;
            }

            .official-logo-col,
            .official-title-col,
            .official-meta-col {
                width: 100% !important;
            }

            .official-logo-col {
                display: flex;
                justify-content: center;
            }

            .official-title-col {
                font-size: 14px !important;
                padding-top: 0 !important;
                margin-bottom: 0 !important;
            }

            .official-meta-col {
                padding: 8px 10px !important;
                font-size: 11px !important;
            }

            .official-info-grid {
                grid-template-columns: 1fr !important;
                gap: 6px !important;
                font-size: 11.5px !important;
            }

            .official-info-grid > div {
                grid-column: span 1 / span 1 !important;
                word-break: break-word;
            }

            .official-desktop-table {
                display: none !important;
            }

            .mobile-only-view {
                display: block !important;
            }

            .m-official-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-bottom: 10px;
            }

            .m-official-card {
                border: 1px solid #000;
                border-radius: 6px;
                padding: 9px 10px;
                background: #ffffff;
                font-size: 11.5px;
            }

            .m-official-card-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 8px;
                padding-bottom: 6px;
                margin-bottom: 6px;
                border-bottom: 1px dashed #cbd5e1;
            }

            .m-official-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 5px 10px;
                font-size: 11px;
            }

            .m-official-summary {
                border: 1px solid #000;
                border-radius: 6px;
                background: #f8fafc;
                padding: 9px 10px;
                display: flex;
                flex-direction: column;
                gap: 6px;
                font-size: 11.5px;
            }

            .m-official-sum-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }

            .m-official-sum-row--final {
                padding-top: 6px;
                border-top: 1px solid #94a3b8;
                font-weight: 700;
                color: #4f46e5;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

{{-- Print floating button (Hidden on print) --}}
<div class="no-print print-actions-bar">
    <button type="button" onclick="window.print()" class="print-fab">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             style="width: 1.15rem; height: 1.15rem; flex-shrink: 0;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <span>چاپ (PDF)</span>
    </button>
    @if(!$isFullyPaid && !$isCanceled && $due > 0)
        <a href="{{ route('portal.invoices.pay', $invoice->id) }}"
           class="print-fab print-fab--pay no-print">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                 style="width: 1.15rem; height: 1.15rem; flex-shrink: 0;">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span>پرداخت آنلاین ({{ $faNum(number_format($due)) }} {{ $currencyLabel }})</span>
        </a>
    @endif
</div>

<div class="a4-container relative">

    <div class="relative z-10">

        {{-- Official Top Header --}}
        <div class="flex justify-between items-start mb-2 official-top-header">
            <div class="w-1/4 official-logo-col">
                @if($appLogoDataUri)
                    <img src="{{ $appLogoDataUri }}" alt="{{ $siteName }}"
                         style="max-height: 44px; max-width: 140px; object-fit: contain;">
                @else
                    <h3 class="font-bold text-sm text-gray-800">{{ $siteName }}</h3>
                @endif
            </div>
            <div class="w-2/4 header-title text-center pt-1 official-title-col">
                صورتحساب فروش کالا و خدمات
            </div>
            <div class="w-1/4 border border-official rounded p-1.5 bg-gray-50 text-left space-y-1 text-[9.5px] official-meta-col">
                <div class="flex items-center justify-between border-b border-gray-200 pb-1 mb-1">
                    <span class="text-gray-500 font-medium">شماره سریال:</span>
                    <span
                        class="text-xs font-bold">{{ $faNum($invoice->invoice_number ?: $invoice->id) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 font-medium">تاریخ صدور:</span>
                    <span class="font-bold">{{ $toJalali($invoice->issue_date ?: $invoice->created_at) }}</span>
                </div>
                @if($invoice->due_date)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-medium">تاریخ سررسید:</span>
                        <span class="font-bold">{{ $toJalali($invoice->due_date) }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between pt-1 border-t border-gray-200 mt-1">
                    <span class="text-gray-500 font-medium">وضعیت:</span>
                    <span class="status-official-tag"
                          style="background-color: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}55;">
                        {{ $statusName }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Seller Info Block --}}
        @if($hasSellerBlock)
            <div class="border border-official rounded mb-2">
                <div class="bg-gray-100 border-b border-official text-center font-bold py-1 text-[10.5px]">مشخصات
                    فروشنده
                </div>
                <div class="p-2 grid grid-cols-4 gap-y-1.5 gap-x-3 text-xs official-info-grid">
                    @foreach($sellerFields as $field)
                        <div class="col-span-{{ $field['span'] }}">
                            <span class="text-gray-600">{{ $field['label'] }}:</span>
                            <span
                                class="font-bold">{{ $field['numeric'] ? $faNum($field['value']) : $field['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Buyer Info Block --}}
        <div class="border border-official rounded mb-2">
            <div class="bg-gray-100 border-b border-official text-center font-bold py-1 text-[10.5px]">مشخصات خریدار
            </div>
            <div class="p-2 grid grid-cols-4 gap-1.5 text-xs official-info-grid">
                <div class="col-span-2">
                    <span class="text-gray-600">نام شخص حقیقی/حقوقی:</span>
                    <span class="font-bold">{{ $buyerName }}</span>
                </div>
                @if($buyerOwner && $buyerOwner !== $buyerName)
                    <div class="col-span-2">
                        <span class="text-gray-600">نام مسئول:</span>
                        <span class="font-bold">{{ $buyerOwner }}</span>
                    </div>
                @endif
                @if($buyerPhone)
                    <div class="col-span-2">
                        <span class="text-gray-600">شماره تماس:</span>
                        <span class="font-bold" dir="ltr">{{ $faNum($buyerPhone) }}</span>
                    </div>
                @endif
                @if($buyerEmail)
                    <div class="col-span-2">
                        <span class="text-gray-600">پست الکترونیک:</span>
                        <span class="font-bold" dir="ltr">{{ $buyerEmail }}</span>
                    </div>
                @endif
                @if($buyerDomain)
                    <div class="col-span-2">
                        <span class="text-gray-600">آدرس سامانه:</span>
                        <span class="font-bold" dir="ltr">{{ $buyerDomain }}</span>
                    </div>
                @endif
                @if($buyerPackage)
                    <div class="col-span-2">
                        <span class="text-gray-600">طرح / پکیج:</span>
                        <span class="font-bold">{{ $buyerPackage }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Official Table --}}
        <div class="mb-4">
            {{-- Mobile Official Items View (Hidden on >= 640px and print) --}}
            <div class="mobile-only-view">
                <div class="m-official-list">
                    @forelse($invoice->items as $index => $item)
                        @php
                            $itemQty = (float) ($item->quantity ?? 1);
                            $displayQty = fmod($itemQty, 1.0) === 0.0 ? (int) $itemQty : $itemQty;
                            $rowBasePrice = $item->unit_price;
                            $rowDiscount = (float) ($item->discount_amount ?? 0);
                            $rowTotal = (float) ($item->total_price ?? ($rowBasePrice * $itemQty - $rowDiscount));
                            $durationLabel = !empty($item->duration_months) ? reseller_duration_label($item->duration_months) : '';
                        @endphp
                        <div class="m-official-card">
                            <div class="m-official-card-head">
                                <div>
                                    <span style="font-weight: 700;">{{ $faNum($index + 1) }}. {{ $item->title ?: 'ردیف فاکتور' }}</span>
                                    @if($item->description)
                                        <span class="text-gray-600" style="display: block; font-size: 11px; margin-top: 2px;">{{ $item->description }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="m-official-grid">
                                <div>
                                    <span class="text-gray-500">مقدار / مدت:</span>
                                    <strong>{{ $durationLabel ?: $faNum($displayQty) }} ({{ $durationLabel ? 'دوره' : 'عدد' }})</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500">مبلغ واحد:</span>
                                    <strong>{{ $faNum(number_format($rowBasePrice)) }}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500">تخفیف:</span>
                                    <strong class="text-red-600">{{ $rowDiscount > 0 ? $faNum(number_format($rowDiscount)) : '۰' }}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500">مبلغ کل:</span>
                                    <strong>{{ $faNum(number_format($rowTotal)) }} {{ $currencyLabel }}</strong>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="m-official-card" style="text-align: center; color: #64748b;">هیچ ردیفی ثبت نشده است.</div>
                    @endforelse
                </div>

                <div class="m-official-summary">
                    <div class="m-official-sum-row">
                        <span>جمع مبالغ پایه:</span>
                        <strong>{{ $faNum(number_format($subtotal ?: $invoice->items->sum('total_price'))) }} {{ $currencyLabel }}</strong>
                    </div>
                    @if($invoice->tax_amount > 0)
                        <div class="m-official-sum-row text-green-600">
                            <span>مالیات بر ارزش افزوده @if((float) ($invoice->tax_percent ?? 0) > 0)({{ $faNum((float) $invoice->tax_percent) }}٪)@endif:</span>
                            <strong>{{ $faNum(number_format($invoice->tax_amount)) }} {{ $currencyLabel }}</strong>
                        </div>
                    @endif
                    @if($invoice->discount_amount > 0)
                        <div class="m-official-sum-row text-red-600">
                            <span>مجموع تخفیف‌ها:</span>
                            <strong>{{ $faNum(number_format($invoice->discount_amount)) }} {{ $currencyLabel }}</strong>
                        </div>
                    @endif
                    @if(!empty($invoice->meta['rounding']['is_rounded']))
                        @php $roundDiff = (int)($invoice->meta['rounding']['diff'] ?? 0); @endphp
                        <div class="m-official-sum-row {{ $roundDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                            <span>تعدیل رندسازی:</span>
                            <strong>{{ $roundDiff > 0 ? '+ ' : '− ' }}{{ $faNum(number_format(abs($roundDiff))) }} {{ $currencyLabel }}</strong>
                        </div>
                    @endif
                    <div class="m-official-sum-row m-official-sum-row--final">
                        <span>مبلغ نهایی فاکتور:</span>
                        <strong>{{ $faNum(number_format($total)) }} {{ $currencyLabel }}</strong>
                    </div>
                </div>
            </div>

            <table class="w-full border-collapse border border-official text-xs official-desktop-table">
                <thead class="bg-gray-100 font-bold">
                <tr>
                    <th scope="col" class="table-cell-border w-8">ردیف</th>
                    <th scope="col" class="table-cell-border">شرح کالا یا خدمات</th>
                    <th scope="col" class="table-cell-border w-16">تعداد / مدت</th>
                    <th scope="col" class="table-cell-border w-12">واحد</th>
                    <th scope="col" class="table-cell-border w-24">مبلغ واحد<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                    </th>
                    <th scope="col" class="table-cell-border w-20">تخفیف<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                    </th>
                    <th scope="col" class="table-cell-border w-28">مبلغ کل<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoice->items as $index => $item)
                    @php
                        $itemQty = (float) ($item->quantity ?? 1);
                        $displayQty = fmod($itemQty, 1.0) === 0.0 ? (int) $itemQty : $itemQty;
                        $rowBasePrice = $item->unit_price;
                        $rowDiscount = (float) ($item->discount_amount ?? 0);
                        $rowTotal = (float) ($item->total_price ?? ($rowBasePrice * $itemQty - $rowDiscount));

                        $durationLabel = !empty($item->duration_months) ? reseller_duration_label($item->duration_months) : '';
                    @endphp
                    <tr>
                        <td class="table-cell-border">{{ $faNum($index + 1) }}</td>
                        <td class="table-cell-border text-right">
                            <span class="font-bold">{{ $item->title ?: 'ردیف فاکتور' }}</span>
                            @if($item->description)
                                <span class="text-gray-600"> - {{ $item->description }}</span>
                            @endif
                        </td>
                        <td class="table-cell-border">{{ $durationLabel ?: $faNum($displayQty) }}</td>
                        <td class="table-cell-border">{{ $durationLabel ? 'دوره' : 'عدد' }}</td>
                        <td class="table-cell-border">{{ $faNum(number_format($rowBasePrice)) }}</td>
                        <td class="table-cell-border text-red-600">{{ $rowDiscount > 0 ? $faNum(number_format($rowDiscount)) : '۰' }}</td>
                        <td class="table-cell-border font-bold">{{ $faNum(number_format($rowTotal)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-cell-border text-center py-3 text-gray-500">هیچ ردیفی ثبت نشده
                            است.
                        </td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="6" class="table-cell-border text-left">جمع مبالغ پایه:</td>
                    <td class="table-cell-border">{{ $faNum(number_format($subtotal ?: $invoice->items->sum('total_price'))) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="6" class="table-cell-border text-left text-green-600">
                            مالیات بر ارزش افزوده @if((float) ($invoice->tax_percent ?? 0) > 0)
                                ({{ $faNum((float) $invoice->tax_percent) }}٪)
                            @endif:
                        </td>
                        <td class="table-cell-border text-green-600">{{ $faNum(number_format($invoice->tax_amount)) }}</td>
                    </tr>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="6" class="table-cell-border text-left">مبلغ با احتساب مالیات:</td>
                        <td class="table-cell-border">{{ $faNum(number_format(($subtotal ?: $invoice->items->sum('total_price')) + $invoice->tax_amount)) }}</td>
                    </tr>
                @endif
                @if($invoice->discount_amount > 0)
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="6" class="table-cell-border text-left text-red-600">مجموع تخفیف‌ها:</td>
                        <td class="table-cell-border text-red-600">{{ $faNum(number_format($invoice->discount_amount)) }}</td>
                    </tr>
                @endif
                @if(!empty($invoice->meta['rounding']['is_rounded']))
                    @php
                        $roundDiff = (int)($invoice->meta['rounding']['diff'] ?? 0);
                    @endphp
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="6"
                            class="table-cell-border text-left {{ $roundDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                            تعدیل رندسازی:
                        </td>
                        <td class="table-cell-border {{ $roundDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $roundDiff > 0 ? '+ ' : '− ' }}{{ $faNum(number_format(abs($roundDiff))) }}
                        </td>
                    </tr>
                @endif
                <tr class="bg-gray-100 font-bold text-xs">
                    <td colspan="6" class="table-cell-border text-left text-indigo-600">مبلغ نهایی فاکتور
                        ({{ $currencyLabel }}):
                    </td>
                    <td class="table-cell-border text-indigo-600">{{ $faNum(number_format($total)) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>

        {{-- Payment History --}}
        @php
            $validPayments = $invoice->payments ? $invoice->payments->reject(fn($p) => ($p->status ?? '') === 'canceled') : collect([]);
        @endphp

        @if($validPayments->isNotEmpty())
            <div class="mb-2">
                <div class="mobile-only-view">
                    <div class="m-official-list">
                        @foreach($validPayments as $payment)
                            <div class="m-official-card">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span class="text-gray-600">{{ $toJalali($payment->paid_at ?: $payment->created_at) }}</span>
                                    <strong class="text-emerald-600">{{ $faNum(number_format($payment->amount)) }} {{ $currencyLabel }}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 11px;">
                                    <span>{{ $getPaymentMethodName($payment->payment_method ?? $payment->method, $payment->gateway ?? null) }}</span>
                                    <span dir="ltr" class="text-gray-600">{{ $faNum($payment->transaction_id) ?: '---' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <table class="w-full border-collapse border border-official text-[9.5px] official-desktop-table">
                    <thead class="bg-gray-100 font-bold">
                    <tr>
                        <th scope="col" class="table-cell-border">تاریخ پرداخت</th>
                        <th scope="col" class="table-cell-border">مبلغ</th>
                        <th scope="col" class="table-cell-border">روش پرداخت</th>
                        <th scope="col" class="table-cell-border">کد رهگیری / شناسه تراکنش</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($validPayments as $payment)
                        <tr>
                            <td class="table-cell-border">{{ $toJalali($payment->paid_at ?: $payment->created_at) }}</td>
                            <td class="table-cell-border font-bold text-emerald-600">{{ $faNum(number_format($payment->amount)) }}</td>
                            <td class="table-cell-border">{{ $getPaymentMethodName($payment->payment_method ?? $payment->method, $payment->gateway ?? null) }}</td>
                            <td class="table-cell-border" dir="ltr">{{ $faNum($payment->transaction_id) ?: '---' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="text-xs mt-1 text-left font-bold">
                    پرداخت شده: <span
                        class="text-emerald-600">{{ $faNum(number_format($paid)) }} {{ $currencyLabel }}</span> &nbsp;|&nbsp;
                    مانده بدهی: <span
                        class="text-rose-600">{{ $faNum(number_format($due)) }} {{ $currencyLabel }}</span>
                </div>
            </div>
        @endif

        {{-- Notes & Terms --}}
        @php
            $termsText = $settings['invoice_terms'] ?? ($settings['invoice_footer_note'] ?? '');
        @endphp
        @if(!empty(trim($termsText)))
            <div class="mt-2 border border-official rounded p-2 text-xs bg-gray-50">
                <strong class="text-gray-800">شرایط و قوانین:</strong>
                <div class="mt-1 leading-relaxed">{!! nl2br(e($termsText)) !!}</div>
            </div>
        @endif

        {{-- Official Signatures & Stamp Block --}}
        @php
            $officialBoxMinHeight = $stampOfficialHeight ? max(40, $stampOfficialHeight) : 40;
            $officialRowMinHeight = max(65, $officialBoxMinHeight + 25);
        @endphp
        <div class="border border-official rounded p-2 mt-2 official-signature-block avoid-break">
            <div class="flex justify-between items-stretch text-center"
                 style="min-height: {{ $officialRowMinHeight }}px;">
                <div class="w-1/2 flex flex-col justify-between items-center px-2">
                    <p class="font-bold text-[10.5px] border-b border-gray-200 pb-1 w-full">مهر و امضای فروشنده</p>
                    <div class="flex-1 flex items-center justify-center py-1 w-full"
                         style="min-height: {{ $officialBoxMinHeight }}px;">
                        @if($stampSignatureDataUri)
                            <img src="{{ $stampSignatureDataUri }}" alt="مهر و امضا"
                                 style="{{ $stampOfficialImgStyle }}">
                        @endif
                    </div>
                    <p class="text-[9.5px] font-bold text-gray-700 pt-0.5 border-t border-dashed border-gray-300 w-full">{{ $sellerInfo['name'] ?? '' }}</p>
                </div>
                <div
                    class="w-1/2 flex flex-col justify-between items-center px-2 border-r border-dashed border-gray-400">
                    <p class="font-bold text-[10.5px] border-b border-gray-200 pb-1 w-full">مهر و امضای خریدار</p>
                    <div class="flex-1 w-full flex items-center justify-center text-gray-400 italic text-[9px]"
                         style="min-height: {{ $officialBoxMinHeight }}px;">
                        محل امضای خریدار
                    </div>
                    <p class="text-[9.5px] font-bold text-gray-700 pt-0.5 border-t border-dashed border-gray-300 w-full">
                        تأیید و امضاء</p>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
