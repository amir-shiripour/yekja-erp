<div
    x-data="cancelInvoiceModalHandler()"
    x-cloak
    x-show="isOpen"
    @keydown.escape.window="closeModal()"
    class="fixed inset-0 z-[99999] overflow-y-auto"
    aria-labelledby="cancel-invoice-modal-title"
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

        {{-- Modal Dialog Container (Neutral Minimal Border) --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop="$event.stopPropagation()"
             class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-right my-8"
             dir="rtl">

            {{-- Header: Warning Icon + Title + Close Button --}}
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-4">
                    {{-- Red Warning Badge with Trash / Cancel Icon --}}
                    <div
                        class="w-13 h-13 rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-200/80 dark:border-red-800/60 shadow-md shadow-red-500/10 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>

                    <div class="space-y-1.5 min-w-0">
                        <div
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200/80 dark:border-red-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            <span>انصراف از سفارش</span>
                        </div>
                        <h2 id="cancel-invoice-modal-title"
                            class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            تأیید لغو صورتحساب سفارش
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            آیا از لغو این فاکتور و انصراف از سفارش در انتظار پرداخت اطمینان دارید؟
                        </p>
                    </div>
                </div>

                {{-- Close Button --}}
                <button type="button"
                        @click="closeModal()"
                        :disabled="isLoading"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer shrink-0 disabled:opacity-40"
                        title="انصراف و بستن">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Invoice Info Summary Card --}}
            <div
                class="rounded-2xl p-4 bg-gray-50/80 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 space-y-2.5 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">شماره صورتحساب:</span>
                    <span class="font-bold text-gray-900 dark:text-white dir-ltr font-mono"
                          x-text="invoiceNumber ? ('#' + invoiceNumber) : '—'"></span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60"
                     x-show="packageName">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">پلن انتخابی:</span>
                    <span class="font-bold text-gray-900 dark:text-white" x-text="packageName"></span>
                </div>

                <div class="flex items-center justify-between" x-show="amountFormatted">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">مبلغ صورتحساب:</span>
                    <span class="font-black text-rose-600 dark:text-rose-400 text-sm" x-text="amountFormatted"></span>
                </div>
            </div>

            {{-- Explanatory Notice --}}
            <div
                class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3 text-xs leading-relaxed text-amber-800 dark:text-amber-300">
                <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>
                    با تأیید لغو، این فاکتور باطل شده و قفل سفارش موقت برداشته می‌شود. پس از آن می‌توانید مجدداً هر پلن، دوره یا درگاه دلخواه دیگری را برای سامانه انتخاب و فعال‌سازی نمایید.
                </span>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
                <button type="button"
                        @click="closeModal()"
                        :disabled="isLoading"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer disabled:opacity-50">
                    انصراف و بازگشت
                </button>

                <button type="button"
                        @click="submitCancel()"
                        :disabled="isLoading || !invoiceId"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs sm:text-sm font-bold shadow-md shadow-red-600/25 active:scale-95 transition-all inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                    <template x-if="isLoading">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <template x-if="!isLoading">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </template>
                    <span x-text="isLoading ? 'در حال لغو فاکتور...' : 'بله، فاکتور لغو شود'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function cancelInvoiceModalHandler() {
        return {
            isOpen: false,
            isLoading: false,
            invoiceId: null,
            invoiceNumber: '',
            packageName: '',
            amountFormatted: '',

            init() {
                window.openCancelInvoiceModal = (payload, num, pkg, amount) => {
                    if (typeof payload === 'object' && payload !== null) {
                        this.invoiceId = payload.id || payload.invoice_id;
                        this.invoiceNumber = payload.invoice_number || payload.number || '';
                        this.packageName = payload.package_name || payload.packageName || '';
                        this.amountFormatted = payload.invoice_due_formatted || payload.amountFormatted || payload.amount_formatted || '';
                    } else {
                        this.invoiceId = payload;
                        this.invoiceNumber = num || '';
                        this.packageName = pkg || '';
                        this.amountFormatted = amount || '';
                    }
                    this.isLoading = false;
                    this.isOpen = true;
                };

                window.closeCancelInvoiceModal = () => {
                    this.isOpen = false;
                };
            },

            closeModal() {
                if (this.isLoading) return;
                this.isOpen = false;
            },

            async submitCancel() {
                if (!this.invoiceId || this.isLoading) return;
                this.isLoading = true;

                try {
                    const cancelUrl = "{{ route('tenant.subscription.invoices.cancel', ':id') }}".replace(':id', this.invoiceId);
                    const res = await fetch(cancelUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await res.json();
                    this.isLoading = false;

                    if (res.ok && data.success) {
                        this.isOpen = false;
                        if (window.showToast) {
                            window.showToast('success', data.message || 'فاکتور با موفقیت لغو شد.');
                        }
                        if (window.Livewire) {
                            window.Livewire.dispatch('refresh-onboarding');
                            window.Livewire.dispatch('onboarding-tour-updated');
                        }
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        const err = data.message || 'خطا در لغو فاکتور.';
                        if (window.showToast) {
                            window.showToast('error', err);
                        } else {
                            alert(err);
                        }
                    }
                } catch (err) {
                    this.isLoading = false;
                    if (window.showToast) {
                        window.showToast('error', 'خطا در برقراری ارتباط با سرور.');
                    }
                }
            }
        };
    }
</script>
