@php
    use App\Tenancy\TenantManager;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Services\TenantQuotaService;

    $tenantManager = app(TenantManager::class);
    $bannerTenant = $currentTenant ?? ($tenantManager->hasTenant() ? $tenantManager->getTenant() : auth()->user()?->tenant);
    $unpaidPayload = null;

    if ($bannerTenant && !$bannerTenant->hasActiveOrder()) {
        try {
            $quotaService = app(TenantQuotaService::class);
            $unpaidPayload = $quotaService->getPendingOrderPayload($bannerTenant);
        } catch (Throwable $e) {
            Log::warning('[unpaid-order-banner] Error loading pending order payload: ' . $e->getMessage());
        }
    }
@endphp

@if($unpaidPayload && !empty($unpaidPayload['is_order_pending']))
    <div
        class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700/80 border-r-4 border-r-amber-500 dark:border-r-amber-400 bg-white dark:bg-gray-800 p-5 sm:p-6 shadow-xs transition-all mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            {{-- Info Section --}}
            <div class="flex items-start gap-4">
                {{-- Clean Status Icon Box --}}
                <div
                    class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                            در انتظار پرداخت و فعال‌سازی
                        </span>

                        @if(!empty($unpaidPayload['invoice_number']))
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 dir-ltr">
                                فاکتور #{{ ResellerHelper::toPersianDigits($unpaidPayload['invoice_number']) }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">
                        اشتراک شما برای پلن «{{ $unpaidPayload['package_name'] ?? 'سازمانی' }}» ثبت شده است
                    </h3>

                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed max-w-2xl">
                        جهت فعال‌سازی آنی ماژول‌ها و استفاده کامل از امکانات سامانه، فاکتور مربوطه را تسویه نمایید. تا
                        زمان پرداخت، دسترسی به ماژول‌های این پلن در حالت قفل موقت قرار دارد.
                    </p>

                    @if(!empty($unpaidPayload['invoice_due_formatted']))
                        <div class="pt-1">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/60 text-xs text-gray-600 dark:text-gray-400 font-medium">
                                <span>مبلغ قابل پرداخت:</span>
                                <span class="font-black text-rose-600 dark:text-rose-400 text-sm">
                                    {{ $unpaidPayload['invoice_due_formatted'] }}
                                </span>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div
                            class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-xs font-bold text-rose-800 dark:text-rose-300 flex items-center gap-2 mt-2">
                            <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions Section --}}
            <div
                class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 shrink-0 self-stretch sm:self-auto pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-100 dark:border-gray-700/60">
                @if(!empty($unpaidPayload['invoice_id']) || !empty($unpaidPayload['payment_url']))
                    <button type="button"
                            onclick="if (window.openInvoicePaymentModal) { window.openInvoicePaymentModal(@js($unpaidPayload)); } else { window.location.href = '{{ $unpaidPayload['payment_url'] ?? '#' }}'; }"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span>پرداخت آنلاین فاکتور</span>
                    </button>
                @endif

                <button type="button"
                        onclick="if (window.showQuotaModal) { window.showQuotaModal(@js($unpaidPayload)); }"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/70 border border-gray-200 dark:border-gray-700 shadow-xs active:scale-[0.98] transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>مشاهده جزئیات سفارش</span>
                </button>

                @if(!empty($unpaidPayload['invoice_id']))
                    <button type="button"
                            onclick="if (window.openCancelInvoiceModal) { window.openCancelInvoiceModal(@js($unpaidPayload)); }"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-rose-600 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800/60 shadow-xs active:scale-[0.98] transition-all cursor-pointer"
                            title="لغو این فاکتور و انصراف از سفارش">
                        <svg class="w-4 h-4 shrink-0 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>لغو فاکتور</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
