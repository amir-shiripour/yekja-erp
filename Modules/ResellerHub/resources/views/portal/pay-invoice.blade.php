@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerSetting;

    $reseller = $invoice->reseller ?? ($invoice->tenant?->reseller ?? auth()->user()?->reseller);
    $resellerId = $reseller?->id ?? ($invoice->reseller_id ?? null);
    $currency = ResellerSetting::getCurrencyForReseller($resellerId);
    $currencyLabel = ResellerSetting::getCurrencyLabelForReseller($resellerId);

    $packageItem = $invoice->items->firstWhere('item_type', 'package') ?? $invoice->items->firstWhere('item_type', 'package_upgrade') ?? $invoice->items->first();
    $pkgDiscount = (int)($invoice->items->sum('discount_amount') ?: ($invoice->discount_amount ?? 0));
    $hasPackageDiscount = $pkgDiscount > 0 || !empty($invoice->meta['has_package_discount']);
    $rawBasePrice = $packageItem && $packageItem->unit_price > 0 ? (int)$packageItem->unit_price : ((int)$invoice->subtotal + $pkgDiscount);

    $brandTitle = $reseller?->brand_title ?: ($reseller?->company_name ?: config('app.name', 'سامانه'));
    $brandLogo = $reseller?->logo_url ?? null;
    $defaultGatewayKey = $defaultGateway ?: array_key_first($availableGateways);

    $tenantUrl = '';
    if ($invoice->tenant) {
        $tenantUrl = $invoice->tenant->domain ?: ($invoice->tenant->subdomain ? $invoice->tenant->subdomain . '.' . ResellerHelper::getBaseDomain() : '');
    }
@endphp

<x-guest-layout>
    <div
        class="min-h-screen bg-slate-50/70 dark:bg-gray-950 font-sans antialiased py-6 sm:py-12 px-4 sm:px-6 lg:px-8 flex flex-col justify-between items-center"
        dir="rtl">
        <div class="w-full max-w-xl mx-auto space-y-4 my-auto">

            {{-- Top Navigation & Brand Header --}}
            <div class="flex items-center justify-between px-1">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.dashboard') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>بازگشت</span>
                </a>

                @if(Route::has('reseller.invoices.print'))
                    <a href="{{ route('reseller.invoices.print', $invoice->id) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span class="hidden sm:inline">مشاهده فاکتور رسمی</span>
                        <span class="sm:hidden">فاکتور رسمی</span>
                    </a>
                @endif
            </div>

            {{-- Main Payment Card --}}
            <div
                class="relative overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-5 sm:p-8 shadow-xl shadow-gray-200/50 dark:shadow-none backdrop-blur-xl space-y-6"
                x-data="{ selected: '{{ $defaultGatewayKey }}', loading: false }">

                {{-- Header with Icon & Status --}}
                <div class="flex items-start gap-3.5 sm:gap-4 pb-5 border-b border-gray-100 dark:border-gray-800">
                    <div
                        class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-md shadow-indigo-500/25 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>

                    <div class="space-y-1 min-w-0 flex-1 text-right">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60">
                                <span>در انتظار پرداخت آنلاین</span>
                            </span>

                            <span class="text-xs font-bold text-gray-400 dark:text-gray-500 dir-ltr">
                                #{{ ResellerHelper::toPersianDigits($invoice->invoice_number) }}
                            </span>
                        </div>

                        <h1 class="text-base sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                            پرداخت فاکتور سفارش
                        </h1>
                        <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            جهت فعال‌سازی و تمدید آنی سرویس، درگاه بانکی را انتخاب فرمایید.
                        </p>
                    </div>
                </div>

                {{-- Order & Financial Breakdown Card --}}
                <div
                    class="rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 p-4 sm:p-5 space-y-3.5 text-xs sm:text-sm">
                    {{-- Tenant / Direct Domain Details if available --}}
                    @if($invoice->tenant)
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pb-3 border-b border-gray-200/70 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">سامانه متصل:</span>
                            <div class="flex items-center gap-2 flex-wrap sm:justify-end">
                                <span
                                    class="font-bold text-gray-900 dark:text-white">{{ $invoice->tenant->name }}</span>
                                @if($tenantUrl)
                                    <span
                                        class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 dir-ltr bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200/60 dark:border-indigo-800/60">
                                        {{ $tenantUrl }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Package / Service Title --}}
                    <div
                        class="flex justify-between items-center pb-3 border-b border-gray-200/70 dark:border-gray-700/60">
                        <span class="text-gray-500 dark:text-gray-400 shrink-0">پلن و سرویس انتخابی:</span>
                        <div class="flex items-center gap-2">
                            <strong class="font-black text-gray-900 dark:text-white">
                                {{ $invoice->package?->name ?? ($packageItem?->description ?? ($invoice->meta['package_name'] ?? 'اشتراک نرم‌افزار')) }}
                            </strong>
                            @if($hasPackageDiscount)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                                    <svg class="w-3 h-3 text-rose-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    <span>تخفیف ویژه</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Discount Breakdown if Applicable --}}
                    @if($hasPackageDiscount && $pkgDiscount > 0)
                        <div class="space-y-2 py-1 text-xs">
                            <div class="flex items-center justify-between text-gray-400 dark:text-gray-500">
                                <span>تعرفه مصوب پکیج:</span>
                                <span class="line-through tabular-nums dir-ltr">
                                    {{ ResellerHelper::formatPrice($rawBasePrice, $currencyLabel) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-rose-600 dark:text-rose-400 font-bold">
                                <span>سود شما از تخفیف:</span>
                                <span class="tabular-nums dir-ltr">
                                    − {{ ResellerHelper::formatPrice($pkgDiscount, $currencyLabel) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Tax Amount if Applicable --}}
                    @if($invoice->tax_amount > 0)
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-1">
                            <span>مالیات بر ارزش افزوده:</span>
                            <span class="tabular-nums dir-ltr font-bold text-gray-700 dark:text-gray-300">
                                + {{ ResellerHelper::formatPrice($invoice->tax_amount, $currencyLabel) }}
                            </span>
                        </div>
                    @endif

                    {{-- Final Total Highlight Row --}}
                    <div
                        class="flex items-center justify-between pt-3.5 border-t border-gray-200/80 dark:border-gray-700/60">
                        <span class="font-bold text-gray-900 dark:text-white">مبلغ کل قابل پرداخت:</span>
                        <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 tabular-nums dir-ltr">
                                {{ ResellerHelper::toPersianDigits(number_format((float)$invoice->total)) }}
                            </span>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                </div>

                {{-- Alert Error Notification --}}
                @if(session('error'))
                    <div
                        class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-2.5 shadow-xs">
                        <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Payment Gateways & Submission Form --}}
                <form action="{{ route('portal.invoices.pay.process', $invoice->id) }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="space-y-2.5">
                        <label class="block text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">
                            انتخاب درگاه پرداخت آنلاین:
                        </label>

                        @if(!empty($availableGateways))
                            <div class="grid grid-cols-1 gap-2.5 select-none">
                                @foreach($availableGateways as $gwId => $gw)
                                    <label
                                        :class="selected === '{{ $gwId }}' ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/20 dark:bg-indigo-950/20 shadow-sm' : 'border-gray-200 dark:border-gray-700/80 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800/80'"
                                        class="relative flex cursor-pointer items-center justify-between p-3.5 sm:p-4 rounded-2xl border transition-all duration-200 group">
                                        <input type="radio" name="gateway" value="{{ $gwId }}"
                                               x-model="selected"
                                               class="sr-only">

                                        <div class="flex items-center gap-3 min-w-0">
                                            {{-- Radio Circle --}}
                                            <div
                                                class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all"
                                                :class="selected === '{{ $gwId }}' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-600 group-hover:border-indigo-400'">
                                                <div x-show="selected === '{{ $gwId }}'"
                                                     class="w-2 h-2 rounded-full bg-white"></div>
                                            </div>

                                            {{-- Gateway Icon --}}
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                                :class="selected === '{{ $gwId }}' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                            </div>

                                            {{-- Gateway Details --}}
                                            <div class="flex flex-col text-right min-w-0">
                                                <span
                                                    class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">
                                                    {{ $gw['name'] ?? $gwId }}
                                                </span>
                                                <span
                                                    class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">
                                                    {{ $gw['description'] ?? 'پرداخت با کلیه کارت‌های عضو شتاب' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Selected Pill Indicator --}}
                                        <div x-show="selected === '{{ $gwId }}'"
                                             class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="hidden sm:inline">انتخاب شده</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300 space-y-1">
                                <div class="flex items-center gap-2 font-bold">
                                    <svg class="w-4 h-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>درگاه پرداخت آنلاینی در دسترس نیست</span>
                                </div>
                                <p class="text-[11px] leading-relaxed text-amber-700 dark:text-amber-400">
                                    در حال حاضر هیچ درگاه پرداخت آنلاینی با اطلاعات احراز هویت معتبر در ماژول تنظیمات
                                    فعال نیست. لطفاً با مدیر سیستم تماس بگیرید.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Submit CTA Button --}}
                    <div class="pt-2">
                        @if(!empty($availableGateways))
                            <button type="submit"
                                    :disabled="loading"
                                    @click="loading = true"
                                    class="w-full h-12 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 active:from-emerald-800 text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2.5 shadow-lg shadow-emerald-600/25 transition-all active:scale-[0.99] cursor-pointer disabled:opacity-70">
                                <span x-show="!loading" class="flex items-center gap-2">
                                    <span>اتصال به درگاه بانکی و پرداخت آنلاین</span>
                                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </span>
                                <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>در حال انتقال به درگاه بانکی...</span>
                                </span>
                            </button>
                        @else
                            <button disabled type="button"
                                    class="w-full h-12 rounded-2xl bg-gray-200 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold text-xs sm:text-sm flex items-center justify-center cursor-not-allowed">
                                امکان پرداخت آنلاین وجود ندارد (درگاه فعال نیست)
                            </button>
                        @endif
                    </div>
                </form>
                {{-- Cancel Invoice Link (Tenant User Only) --}}
                @auth
                    @if(!$invoice->isPaid() && !$invoice->isCancelled() && (auth()->user()->tenant_id == $invoice->tenant_id || app(\App\Tenancy\TenantManager::class)->getTenantId() == $invoice->tenant_id))
                        <form method="POST" action="{{ route('tenant.subscription.invoices.cancel', $invoice->id) }}"
                              onsubmit="return confirm('{{ __('resellerhub::messages.invoices.cancel_modal_desc') }}');"
                              class="pt-1 text-center">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-rose-500 hover:text-rose-700 underline font-medium cursor-pointer transition">
                                {{ __('resellerhub::messages.invoices.cancel_invoice_button') }}
                            </button>
                        </form>
                    @endif
                @endauth
            </div>

            {{-- Page Footer Note --}}
            <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                {{ $brandTitle }} کلیه حقوق محفوظ است.
            </p>
        </div>
    </div>
</x-guest-layout>
