@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    $currencyLabel = function_exists('reseller_currency_label') ? reseller_currency_label() : 'تومان';
    $formattedPrice = ResellerHelper::formatPrice($invoice->total, 'toman');
    $packageItem = $invoice->items->firstWhere('item_type', 'package') ?? $invoice->items->firstWhere('item_type', 'package_upgrade') ?? $invoice->items->first();
    $pkgDiscount = (int)($invoice->items->sum('discount_amount') ?: ($invoice->discount_amount ?? 0));
    $hasPackageDiscount = $pkgDiscount > 0 || !empty($invoice->meta['has_package_discount']);
    $rawBasePrice = $packageItem && $packageItem->unit_price > 0 ? (int)$packageItem->unit_price : ((int)$invoice->subtotal + $pkgDiscount);
@endphp

<x-guest-layout>

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-4 sm:p-6 dark:bg-gray-950 font-sans"
         dir="rtl">
        <div class="w-full max-w-lg space-y-5 my-8">
            <div class="flex items-center justify-between px-1">
                <a href="{{ route('user.dashboard') }}"
                   class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    <span>بازگشت به پیشخوان</span>
                </a>

                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">
                    پرداخت امن بانکی
                </span>
            </div>
            <div
                class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 sm:p-7 shadow-xs space-y-6">
                <div class="flex items-start gap-4 pb-5 border-b border-gray-100 dark:border-gray-800">
                    <div
                        class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>

                    <div class="space-y-1">
                        <div
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>در انتظار پرداخت</span>
                        </div>
                        <h1 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            پرداخت آنلاین فاکتور سفارش
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            جهت فعال‌سازی آنی اشتراک، درگاه بانکی مورد نظر را انتخاب نمایید.
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-800 p-4 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">پلن اشتراک:</span>
                        <div class="flex items-center gap-2">
                            <strong class="font-bold text-gray-900 dark:text-white">
                                {{ $invoice->package?->name ?? ($invoice->meta['package_name'] ?? 'اشتراک نرم‌افزار') }}
                            </strong>
                            @if($hasPackageDiscount)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                                    <svg class="w-3 h-3 text-rose-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span>خرید با تخفیف ویژه دوره</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">شماره فاکتور:</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 dir-ltr">
                            #{{ ResellerHelper::toPersianDigits($invoice->invoice_number) }}
                        </span>
                    </div>

                    @if($hasPackageDiscount && $pkgDiscount > 0)
                        <div
                            class="space-y-1.5 pt-2 border-t border-dashed border-gray-200 dark:border-gray-700/60 text-xs">
                            <div
                                class="flex items-center justify-between text-gray-400 dark:text-gray-500 line-through">
                                <span>تعرفه اصلی پکیج:</span>
                                <span class="dir-ltr">{{ ResellerHelper::formatPrice($rawBasePrice, 'toman') }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                                <span>سود شما از تخفیف پکیج:</span>
                                <span class="dir-ltr">− {{ ResellerHelper::formatPrice($pkgDiscount, 'toman') }}</span>
                            </div>
                        </div>
                    @endif

                    <div
                        class="flex items-center justify-between text-xs pt-2 border-t border-gray-200/70 dark:border-gray-700/60">
                        <span class="font-bold text-gray-900 dark:text-white">مبلغ نهایی قابل پرداخت:</span>
                        <span class="text-base font-black text-rose-600 dark:text-rose-400 dir-ltr">
                            {{ $formattedPrice }}
                        </span>
                    </div>
                </div>
                @if(session('error'))
                    <div
                        class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                <form action="{{ route('portal.invoices.pay.process', $invoice->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2.5">
                            انتخاب درگاه پرداخت آنلاین:
                        </label>

                        <div class="grid grid-cols-1 gap-2.5"
                             x-data="{ selected: '{{ $defaultGateway ?: array_key_first($availableGateways) }}' }">
                            @foreach($availableGateways as $gwId => $gw)
                                <label
                                    :class="selected === '{{ $gwId }}' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 ring-1 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="relative flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="gateway" value="{{ $gwId }}"
                                               x-model="selected"
                                               class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                        <div>
                                            <span class="block text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $gw['name'] ?? $gwId }}
                                            </span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $gw['description'] ?? 'پرداخت با کلیه کارت‌های عضو شتاب' }}
                                            </span>
                                        </div>
                                    </div>

                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400"
                                          x-show="selected === '{{ $gwId }}'">
                                        انتخاب شده
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full h-11 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer transition active:scale-[0.99] shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span>اتصال به درگاه بانکی و پرداخت آنلاین</span>
                        </button>
                    </div>
                </form>

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
                <div class="flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500 pt-1">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>پرداخت امن از طریق شبکه شاپرک و درگاه‌های رسمی بانکی</span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
