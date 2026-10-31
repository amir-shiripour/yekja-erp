@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;

    $availableGateways = ResellerHelper::getAvailableGateways();
    $defaultGateway = ResellerHelper::getDefaultGateway();
    $currencyLabel = reseller_currency_label();
    $payUrlTemplate = route('portal.invoices.pay.process', ['invoice' => '__INVOICE_ID__']);
@endphp

<div
    x-data="invoicePaymentModalHandler(@js($availableGateways), @js($defaultGateway), @js($currencyLabel), @js($payUrlTemplate))"
    x-cloak
    x-show="isOpen"
    @keydown.escape.window="closeModal()"
    @open-invoice-payment-modal.window="openModal($event.detail)"
    class="fixed inset-0 z-[99999] overflow-y-auto"
    aria-labelledby="payment-modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:p-0">
        {{-- Backdrop --}}
        <div x-show="isOpen"
             x-transition:enter="transition-opacity ease-out duration-250"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/75 dark:bg-black/85 backdrop-blur-sm transition-opacity"
             @click="closeModal()"
             aria-hidden="true"></div>

        {{-- Modal Dialog Container --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop="$event.stopPropagation()"
             class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-200/80 dark:border-gray-800 p-6 sm:p-7 space-y-5 text-start my-8">

            {{-- Header: Icon + Title + Close Button --}}
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                        <h2 id="payment-modal-title"
                            class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            پرداخت آنلاین فاکتور سفارش
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            جهت فعال‌سازی آنی امکانات، درگاه بانکی را انتخاب و فرآیند پرداخت را تکمیل فرمایید.
                        </p>
                    </div>
                </div>

                {{-- Close X Button --}}
                <button type="button"
                        @click="closeModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer shrink-0"
                        title="بستن">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Flash Error inside modal if any --}}
            @if(session('error'))
                <div
                    class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Invoice Specs Card --}}
            <div
                class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-800 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">پلن اشتراک:</span>
                    <div class="flex items-center gap-1.5">
                        <strong class="font-bold text-gray-900 dark:text-white"
                                x-text="packageName || 'اشتراک سامانه'"></strong>
                        <template x-if="hasPackageDiscount">
                            <span
                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-black bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                                <svg class="w-2.5 h-2.5 text-rose-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span>تخفیف ویژه دوره</span>
                            </span>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">شماره فاکتور:</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300 dir-ltr"
                          x-text="invoiceNumber ? ('#' + invoiceNumber) : '—'"></span>
                </div>

                <template x-if="hasPackageDiscount && discountAmountFormatted">
                    <div
                        class="space-y-1.5 pt-1.5 border-t border-dashed border-gray-200 dark:border-gray-700/60 text-xs">
                        <template x-if="originalTotalFormatted">
                            <div
                                class="flex items-center justify-between text-gray-400 dark:text-gray-500 line-through">
                                <span>تعرفه اصلی پکیج:</span>
                                <span class="dir-ltr" x-text="originalTotalFormatted"></span>
                            </div>
                        </template>
                        <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>سود شما از تخفیف پکیج:</span>
                            <span class="dir-ltr" x-text="'− ' + discountAmountFormatted"></span>
                        </div>
                    </div>
                </template>

                <div
                    class="flex items-center justify-between text-xs pt-2 border-t border-gray-200/70 dark:border-gray-700/60">
                    <span class="font-bold text-gray-900 dark:text-white">مبلغ نهایی قابل پرداخت:</span>
                    <span class="text-base font-black text-rose-600 dark:text-rose-400 dir-ltr"
                          x-text="amountFormatted"></span>
                </div>
            </div>

            {{-- Form for Gateway Submission --}}
            <form :action="getPayUrl()" method="POST" @submit="submitting = true" class="space-y-4">
                @csrf

                {{-- Gateways Selection --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                        درگاه پرداخت آنلاین شاپرک:
                    </label>

                    @if(empty($availableGateways))
                        <div
                            class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-semibold">
                            در حال حاضر هیچ درگاه پرداخت آنلاینی در تنظیمات سیستم فعال نیست. لطفاً با پشتیبانی تماس حاصل
                            فرمایید.
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-2.5">
                            @foreach($availableGateways as $gwId => $gw)
                                <label
                                    :class="selectedGateway === '{{ $gwId }}' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 ring-1 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="relative flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="gateway" value="{{ $gwId }}"
                                               x-model="selectedGateway"
                                               class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">

                                        {{-- Gateway Badge Icon --}}
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border border-gray-200/70 dark:border-gray-700 bg-white dark:bg-gray-800">
                                            @if($gwId === 'zarinpal')
                                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-2xs"></span>
                                            @elseif($gwId === 'zibal')
                                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-2xs"></span>
                                            @elseif($gwId === 'behpardakht')
                                                <span class="w-2.5 h-2.5 rounded-full bg-rose-600 shadow-2xs"></span>
                                            @elseif($gwId === 'sep')
                                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shadow-2xs"></span>
                                            @else
                                                <span class="w-2.5 h-2.5 rounded-full bg-gray-500 shadow-2xs"></span>
                                            @endif
                                        </div>

                                        <div>
                                            <span class="block text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $gw['name'] ?? $gwId }}
                                            </span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $gw['description'] ?? 'پرداخت امن با کلیه کارت‌های عضو شتاب' }}
                                            </span>
                                        </div>
                                    </div>

                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400"
                                          x-show="selectedGateway === '{{ $gwId }}'">
                                        انتخاب شده
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2.5 pt-2">
                    <button type="submit"
                            :disabled="submitting || !invoiceId || @js(empty($availableGateways))"
                            class="flex-1 h-11 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer transition active:scale-[0.99] shadow-xs">
                        <template x-if="submitting">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <template x-if="!submitting">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </template>
                        <span
                            x-text="submitting ? 'در حال اتصال به درگاه بانکی...' : ('اتصال به ' + getGatewayName(selectedGateway) + ' و پرداخت آنلاین')"></span>
                    </button>

                    <button type="button"
                            @click="const invToCancel = { id: invoiceId, invoice_number: invoiceNumber, package_name: packageName, invoice_due_formatted: amountFormatted }; closeModal(); if (window.openCancelInvoiceModal) { window.openCancelInvoiceModal(invToCancel); }"
                            class="px-3.5 h-11 rounded-xl text-xs sm:text-sm font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-800/70 transition cursor-pointer flex items-center justify-center gap-1.5"
                            title="لغو این فاکتور و انصراف">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>لغو فاکتور</span>
                    </button>

                    <button type="button"
                            @click="closeModal()"
                            class="px-4 h-11 rounded-xl text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition cursor-pointer">
                        انصراف
                    </button>
                </div>
            </form>

            {{-- Trust Note --}}
            <div
                class="flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500 pt-1 border-t border-gray-100 dark:border-gray-800">
                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>اتصال ایمن و مستقیم به سامانه شاپرک بانک مرکزی جمهوری اسلامی ایران</span>
            </div>

        </div>
    </div>
</div>

<script>
    window.faNum = window.faNum || function (str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    };

    function invoicePaymentModalHandler(availableGateways, defaultGatewayKey, currencyLabel, payUrlTemplate) {
        return {
            isOpen: false,
            submitting: false,
            invoiceId: null,
            invoiceNumber: '',
            packageName: '',
            amountFormatted: '',
            currencyLabel: currencyLabel || 'تومان',
            payUrlTemplate: payUrlTemplate || '',
            availableGateways: availableGateways || {},
            selectedGateway: defaultGatewayKey || (Object.keys(availableGateways || {})[0] || ''),
            hasPackageDiscount: false,
            discountAmountFormatted: '',
            originalTotalFormatted: '',

            getPayUrl() {
                if (!this.invoiceId) return '#';
                return this.payUrlTemplate.replace('__INVOICE_ID__', String(this.invoiceId));
            },

            getGatewayName(key) {
                return (this.availableGateways && this.availableGateways[key]?.name) ? this.availableGateways[key].name : 'درگاه بانکی';
            },

            openModal(data) {
                if (!data) return;
                if (Array.isArray(data) && data.length > 0) {
                    data = data[0];
                }
                this.invoiceId = data.invoice_id || data.invoiceId || null;
                const rawInvNum = data.invoice_number_formatted || data.invoice_number || data.invoiceNumber || '';
                this.invoiceNumber = data.invoice_number_formatted ? data.invoice_number_formatted : (window.faNum ? window.faNum(rawInvNum) : rawInvNum);
                this.packageName = data.package_name || data.packageName || '';
                this.amountFormatted = data.invoice_due_formatted || data.amountFormatted || (data.invoice_due ? ((window.faNum ? window.faNum(Number(data.invoice_due).toLocaleString('en-US')) : data.invoice_due) + ' ' + this.currencyLabel) : '');
                this.hasPackageDiscount = Boolean(data.has_package_discount || data.hasPackageDiscount || (Number(data.discount_amount || 0) > 0));
                this.discountAmountFormatted = data.discount_amount_formatted || data.discountAmountFormatted || '';
                this.originalTotalFormatted = data.original_total_formatted || data.originalTotalFormatted || '';
                this.submitting = false;

                if ((!this.selectedGateway || !this.availableGateways[this.selectedGateway]) && this.availableGateways) {
                    this.selectedGateway = defaultGatewayKey || (Object.keys(this.availableGateways)[0] || '');
                }

                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
                this.submitting = false;
            }
        };
    }

    // Global helper accessible anywhere in Alpine or vanilla JS
    window.openInvoicePaymentModal = function (payload) {
        window.dispatchEvent(new CustomEvent('open-invoice-payment-modal', {detail: payload}));
    };
</script>
