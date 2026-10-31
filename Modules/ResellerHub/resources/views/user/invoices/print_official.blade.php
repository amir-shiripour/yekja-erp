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

            .no-print {
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
    </style>
</head>
<body>

{{-- Print floating button (Hidden on print) --}}
<div class="fixed bottom-8 right-8 no-print z-50 flex items-center gap-3">
    <button onclick="window.print()"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105 cursor-pointer">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <span>چاپ (PDF)</span>
    </button>
    @if(!$isFullyPaid && !$isCanceled && $due > 0)
        <a href="{{ route('portal.invoices.pay', $invoice->id) }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105 cursor-pointer no-print text-sm"
           style="text-decoration: none;">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
        <div class="flex justify-between items-start mb-2">
            <div class="w-1/4">
                @if($appLogoDataUri)
                    <img src="{{ $appLogoDataUri }}" alt="{{ $siteName }}"
                         style="max-height: 44px; max-width: 140px; object-fit: contain;">
                @else
                    <h3 class="font-bold text-sm text-gray-800">{{ $siteName }}</h3>
                @endif
            </div>
            <div class="w-2/4 header-title text-center pt-1">
                صورتحساب فروش کالا و خدمات
            </div>
            <div class="w-1/4 border border-official rounded p-1.5 bg-gray-50 text-left space-y-1 text-[9.5px]">
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
                <div class="p-2 grid grid-cols-4 gap-y-1.5 gap-x-3 text-xs">
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
            <div class="p-2 grid grid-cols-4 gap-1.5 text-xs">
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
            <table class="w-full border-collapse border border-official text-xs">
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
                <table class="w-full border-collapse border border-official text-[9.5px]">
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
