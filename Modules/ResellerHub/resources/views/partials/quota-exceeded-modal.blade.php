@php
    if (class_exists(\Nwidart\Modules\Facades\Module::class) && !\Nwidart\Modules\Facades\Module::isEnabled('ResellerHub')) {
        return;
    }
    use Modules\ResellerHub\App\Services\TenantQuotaService;
    try {
        $initialPayload = session('reseller_quota_exceeded');
        $guardedRoutes = app(TenantQuotaService::class)->getGuardedRoutesForFrontend();
        $lockedModules = app(TenantQuotaService::class)->getLockedModulesForFrontend();
        $defaultCurrencyLabel = reseller_currency_label();
    } catch (Throwable $e) {
        return;
    }
@endphp

<div
    x-data="resellerQuotaModalHandler(@js($initialPayload), @js($guardedRoutes), @js($lockedModules), @js($defaultCurrencyLabel))"
    x-cloak
    x-show="isOpen"
    @keydown.escape.window="closeModal()"
    class="fixed inset-0 z-[9999] overflow-y-auto"
    style="display: none;"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">

    <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-950/70 dark:bg-black/85 backdrop-blur-sm transition-opacity"
             @click="closeModal()"></div>
        <div
            class="relative bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-200 dark:border-gray-800 p-6 sm:p-7 space-y-5 text-start my-8">
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-start gap-3.5">
                    <div
                        :class="payload?.is_order_pending ? 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800' : 'bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-950 dark:text-rose-400 dark:border-rose-800'"
                        class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 mt-0.5">
                        <template x-if="payload?.is_order_pending">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <template x-if="!payload?.is_order_pending">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </template>
                    </div>

                    <div class="space-y-1">
                        <div
                            :class="payload?.is_order_pending ? 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800' : 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-800'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold border">
                            <span class="w-1.5 h-1.5 rounded-full"
                                  :class="payload?.is_order_pending ? 'bg-amber-500' : 'bg-rose-500'"></span>
                            <span
                                x-text="payload?.is_order_pending ? 'سفارش در انتظار پرداخت و فعال‌سازی' : 'سقف سهمیه تکمیل شده'"></span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight"
                            x-text="payload?.is_order_pending ? (payload.title + ' هنوز فعال نشده است') : (payload?.title ? ('سقف ' + payload.title + ' تکمیل شده است') : 'سقف مجاز اشتراک تکمیل شده است')">
                        </h3>
                    </div>
                </div>
                <button type="button"
                        @click="closeModal()"
                        class="text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-800 p-1.5 rounded-lg transition cursor-pointer shrink-0"
                        title="بستن">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div
                class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs bg-gray-100 dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                <div class="flex items-center gap-1.5">
                    <span class="text-gray-500 dark:text-gray-400">سامانه:</span>
                    <strong class="font-bold text-gray-900 dark:text-white"
                            x-text="payload?.tenant_name || 'سامانه ابری'"></strong>
                </div>
                <span class="text-gray-400 dark:text-gray-500">•</span>
                <div class="flex items-center gap-1.5">
                    <span class="text-gray-500 dark:text-gray-400">پلن اشتراک:</span>
                    <span class="font-bold text-gray-900 dark:text-white"
                          x-text="payload?.package_name || 'استاندارد'"></span>
                </div>
            </div>
            <template x-if="payload?.is_order_pending">
                <div
                    class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-200">وضعیت سفارش:</span>
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/60 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>در انتظار پرداخت و تایید نهایی</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-start pt-1">
                        <div
                            class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-amber-100 dark:border-gray-800">
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 block font-medium">شماره فاکتور:</span>
                            <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white mt-1 inline-block"
                                  x-text="faNum(payload?.invoice_number) || '—'"></span>
                        </div>
                        <div
                            class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-amber-100 dark:border-gray-800">
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 block font-medium">مبلغ قابل پرداخت:</span>
                            <span
                                class="text-xs sm:text-sm font-black text-rose-600 dark:text-rose-400 mt-1 inline-block"
                                x-text="payload?.invoice_due_formatted || formatPrice(payload?.invoice_due ?? payload?.invoice_total ?? 0, payload?.currency_label)"></span>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!payload?.is_order_pending && !payload?.is_feature">
                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-3.5">
                    <div class="grid grid-cols-3 gap-2.5 text-center">
                        <div
                            class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-600 dark:text-gray-300 font-medium block">سقف اشتراک</span>
                            <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white mt-1 inline-block"
                                  x-text="(payload?.limit !== null && payload?.limit !== undefined ? (faNum(payload.limit) + ' ' + (payload?.unit || '')) : 'نامحدود')"></span>
                        </div>
                        <div
                            class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900">
                            <span class="text-xs text-rose-700 dark:text-rose-300 font-medium block">مصرف شده</span>
                            <span
                                class="text-xs sm:text-sm font-black text-rose-700 dark:text-rose-300 mt-1 inline-block"
                                x-text="faNum(payload?.usage ?? 0) + ' ' + (payload?.unit || '')"></span>
                        </div>
                        <div
                            class="p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-600 dark:text-gray-300 font-medium block">باقیمانده</span>
                            <span
                                class="text-xs sm:text-sm font-black text-rose-600 dark:text-rose-400 mt-1 inline-block"
                                x-text="faNum(payload?.remaining ?? 0) + ' ' + (payload?.unit || '')"></span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-rose-600 dark:text-rose-400"
                                  x-text="faNum(100) + '٪ ظرفیت تکمیل شده است'"></span>
                            <span class="text-gray-600 dark:text-gray-400 font-medium">حداکثر سهمیه دوره</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full bg-rose-500 rounded-full w-full"></div>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!payload?.is_order_pending && payload?.is_feature">
                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <span
                            class="text-xs font-bold text-gray-900 dark:text-white block">قابلیت غیرفعال در پلن جاری</span>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 leading-relaxed">
                            این قابلیت در پکیج اشتراک فعال شما تعریف نشده است و با ارتقای پلن فعال خواهد شد.
                        </p>
                    </div>
                </div>
            </template>
            <div
                class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm text-gray-800 dark:text-gray-200 leading-relaxed font-medium"
                x-text="payload?.message || 'شما به حداکثر سقف مجاز این قابلیت در دوره جاری رسیده‌اید. برای صدور و ثبت موارد جدید، پلن اشتراک خود را ارتقا دهید.'">
            </div>
            <div
                class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-7 h-7 rounded-lg bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium block">پشتیبانی و تمدید اشتراک:</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white"
                                  x-text="payload?.reseller_name || 'نماینده رسمی نرم‌افزار'"></span>
                        </div>
                    </div>

                    <template x-if="payload?.support_phone">
                        <a :href="'tel:' + payload.support_phone"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition cursor-pointer shrink-0 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span>تماس تلفنی</span>
                        </a>
                    </template>
                </div>

                <template x-if="payload?.support_phone || payload?.support_email">
                    <div
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-700 dark:text-gray-300 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <template x-if="payload?.support_phone">
                            <span class="flex items-center gap-1">
                                <span class="text-gray-500 dark:text-gray-400">تلفن:</span>
                                <strong dir="ltr" class="font-bold text-gray-900 dark:text-white"
                                        x-text="faNum(payload.support_phone)"></strong>
                            </span>
                        </template>
                        <template x-if="payload?.support_email">
                            <span class="flex items-center gap-1">
                                <span class="text-gray-500 dark:text-gray-400">ایمیل:</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                      x-text="payload.support_email"></span>
                            </span>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Pending Order Online Pay CTA Button --}}
            <template x-if="payload?.is_order_pending && (payload?.invoice_id || payload?.payment_url)">
                <div class="space-y-2.5 pt-1">
                    <button type="button"
                            @click="closeModal(); if (window.openInvoicePaymentModal) { window.openInvoicePaymentModal(payload); } else if (payload.payment_url) { window.location.href = payload.payment_url; }"
                            class="w-full h-11 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer transition active:scale-[0.99] shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span>پرداخت آنلاین فاکتور و فعال‌سازی آنی</span>
                    </button>
                </div>
            </template>

            {{-- One-Click Solid Upgrade Request CTA (Only for Quotas & Features) --}}
            <template x-if="!payload?.is_order_pending">
                <div class="space-y-2.5 pt-1">
                    <template x-if="!requestSent">
                        <div>
                            <button type="button"
                                    @click="sendUpgradeRequest()"
                                    :disabled="submitting"
                                    class="w-full h-11 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer transition active:scale-[0.99] disabled:opacity-50 shadow-xs">
                                <template x-if="submitting">
                                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!submitting">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </template>
                                <span
                                    x-text="submitting ? 'در حال ارسال درخواست...' : 'ارسال درخواست افزایش سقف به نماینده'"></span>
                            </button>
                        </div>
                    </template>

                    {{-- Success State after sending upgrade request --}}
                    <template x-if="requestSent">
                        <div
                            class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100 text-xs font-bold flex items-center gap-3">
                            <div
                                class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <span class="block font-bold text-xs text-emerald-900 dark:text-emerald-100">درخواست شما ارسال شد</span>
                                <span class="text-xs text-emerald-700 dark:text-emerald-300 font-normal mt-0.5 block">پیام شما به نماینده فروش ارسال گردید و به زودی جهت ارتقای سهمیه با شما تماس خواهند گرفت.</span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Footer Action Buttons --}}
            <div class="flex items-center justify-between gap-3 pt-3.5 border-t border-gray-200 dark:border-gray-800">
                @if(Route::has('tenant.subscription'))
                    <a href="{{ route('tenant.subscription') }}"
                       class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 text-xs font-bold transition cursor-pointer">
                        مشاهده جزئیات پلن
                    </a>
                @else
                    <span></span>
                @endif

                <button type="button"
                        @click="closeModal()"
                        class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-900 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100 text-xs font-bold transition cursor-pointer">
                    متوجه شدم و بازگشت
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.faNum = window.faNum || function (str) {
        return (str !== null && str !== undefined)
            ? String(str).replace(/\d/g, d => String.fromCharCode(d.charCodeAt(0) + 1728))
            : '';
    };

    function resellerQuotaModalHandler(initialPayload = null, guardedRoutes = [], lockedModules = [], defaultCurrencyLabel = '{{ reseller_currency_label() }}') {
        return {
            isOpen: Boolean(initialPayload && (initialPayload.key || initialPayload.is_order_pending)),
            payload: initialPayload || {},
            guardedRoutes: guardedRoutes || [],
            lockedModules: lockedModules || [],
            defaultCurrencyLabel: defaultCurrencyLabel || '{{ reseller_currency_label() }}',
            cachedQuotas: {},
            submitting: false,
            requestSent: false,

            init() {
                // Register global window helper (accepts string key or full payload object)
                window.showQuotaModal = async (keyOrPayload) => {
                    if (typeof keyOrPayload === 'string') {
                        const data = await this.fetchQuotaCheck(keyOrPayload);
                        if (data && data.payload) {
                            this.payload = data.payload;
                            this.isOpen = true;
                            this.requestSent = false;
                            this.submitting = false;
                            return;
                        }
                    }

                    this.payload = (typeof keyOrPayload === 'object' && keyOrPayload) ? keyOrPayload : {};
                    this.isOpen = true;
                    this.requestSent = false;
                    this.submitting = false;
                };

                // Listen for custom events dispatched anywhere in the app
                window.addEventListener('show-quota-modal', (e) => {
                    window.showQuotaModal(e.detail);
                });

                // Auto-decorate locked modules across any layout (Desktop/Mobile sidebar, dashboard, quick search)
                this.decorateLockedElements();
                this.setupDomObserver();
                if (window.Alpine) {
                    Alpine.nextTick(() => this.decorateLockedElements());
                }
                setTimeout(() => this.decorateLockedElements(), 300);
                setTimeout(() => this.decorateLockedElements(), 1200);

                // Modern 2026 Delegated Event Interceptor: Pure dynamic route resolution, zero regex
                document.addEventListener('click', async (e) => {
                    const explicitBtn = e.target.closest('[data-quota-check]');
                    const explicitLocked = e.target.closest('[data-module-locked="true"]');
                    const link = e.target.closest('a[href]');
                    const clickedBtn = e.target.closest('button');

                    //Explicit locked module button/link click
                    if (explicitLocked) {
                        if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey) return;
                        e.preventDefault();
                        e.stopPropagation();
                        const modSlug = explicitLocked.getAttribute('data-module-slug');
                        const locked = (this.lockedModules || []).find(m => m.slug === modSlug || m.name === modSlug) || (this.lockedModules && this.lockedModules[0]);
                        if (locked) {
                            window.showQuotaModal(locked.payload);
                        }
                        return;
                    }

                    //Check if a clicked link leads to any locked module
                    if (link) {
                        const parsed = this.parseCleanPath(link.getAttribute('href') || link.href);
                        if (parsed) {
                            const lockedMod = (this.lockedModules || []).find(m => this.matchesLockedModule(parsed, m));
                            if (lockedMod) {
                                if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey) return;
                                e.preventDefault();
                                e.stopPropagation();
                                window.showQuotaModal(lockedMod.payload);
                                return;
                            }
                        }
                    }

                    // Check if clicked button is a group header whose child links belong to a locked module
                    if (clickedBtn && this.lockedModules && this.lockedModules.length > 0) {
                        const container = clickedBtn.closest('.group\\/groupblock, .group\\/blockgroup, [class*="groupblock"], [class*="blockgroup"], .relative.mt-1');
                        if (container) {
                            const childLinks = Array.from(container.querySelectorAll('a[href]'));
                            if (childLinks.length > 0) {
                                const matched = this.lockedModules.find(m => {
                                    return childLinks.some(l => {
                                        const parsed = this.parseCleanPath(l.getAttribute('href') || l.href);
                                        return parsed && this.matchesLockedModule(parsed, m);
                                    });
                                });
                                if (matched) {
                                    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey) return;
                                    e.preventDefault();
                                    e.stopPropagation();
                                    window.showQuotaModal(matched.payload);
                                    return;
                                }
                            }
                        }
                    }

                    let quotaKey = null;
                    let targetHref = null;

                    if (explicitBtn) {
                        quotaKey = explicitBtn.getAttribute('data-quota-check');
                        targetHref = explicitBtn.getAttribute('href');
                    } else if (link) {
                        const href = link.getAttribute('href');
                        if (!href || href === '#' || href.startsWith('javascript:')) return;

                        let parsedUrl;
                        try {
                            parsedUrl = new URL(link.href, window.location.origin);
                        } catch {
                            return;
                        }

                        // Guard only same-origin navigations
                        if (parsedUrl.origin !== window.location.origin) return;

                        const cleanPath = parsedUrl.pathname.replace(/\/+$/, '');

                        // Match against backend-provided dynamic routes
                        const matched = this.guardedRoutes.find(r => r.path === cleanPath);
                        if (matched) {
                            quotaKey = matched.key;
                            targetHref = link.href;
                        }
                    }

                    if (!quotaKey) return;

                    // Respect browser shortcuts (Cmd/Ctrl/Shift/Middle click)
                    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey) return;

                    e.preventDefault();

                    try {
                        const data = await this.fetchQuotaCheck(quotaKey);
                        if (data && data.has_remaining === false) {
                            window.showQuotaModal(data.payload);
                        } else {
                            if (targetHref && targetHref !== '#') {
                                window.location.href = targetHref;
                            }
                        }
                    } catch {
                        if (targetHref && targetHref !== '#') {
                            window.location.href = targetHref;
                        }
                    }
                });
            },

            async fetchQuotaCheck(key) {
                if (this.cachedQuotas[key] !== undefined) {
                    return this.cachedQuotas[key];
                }

                try {
                    const checkUrl = "{{ route('tenant.quota.check') }}?key=" + encodeURIComponent(key);
                    const res = await fetch(checkUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    this.cachedQuotas[key] = data;
                    return data;
                } catch {
                    return null;
                }
            },

            parseCleanPath(rawHref) {
                if (!rawHref || rawHref === '#' || rawHref.startsWith('javascript:')) return null;
                try {
                    const url = new URL(rawHref, window.location.origin);
                    if (url.origin !== window.location.origin) return null;
                    const clean = url.pathname.replace(/\/+$/, '');
                    return {
                        cleanPath: clean,
                        strippedPath: clean.replace(/^\/[^\/]+(?=\/)/, '')
                    };
                } catch {
                    return null;
                }
            },

            matchesLockedModule(pathObj, lockedMod) {
                if (!pathObj || !lockedMod || !lockedMod.prefixes) return false;
                return lockedMod.prefixes.some(pfx => {
                    const cleanPfx = pfx.replace(/\/+$/, '');
                    return pathObj.cleanPath === cleanPfx || pathObj.cleanPath.startsWith(cleanPfx + '/')
                        || pathObj.strippedPath === cleanPfx || pathObj.strippedPath.startsWith(cleanPfx + '/');
                });
            },

            decorateLockedElements() {
                if (!this.lockedModules || this.lockedModules.length === 0) return;

                const makeLockBadge = () => {
                    const span = document.createElement('span');
                    span.className = 'reseller-lock-badge inline-flex items-center text-amber-500 dark:text-amber-400 ms-auto text-xs shrink-0 select-none mr-1.5 transition-transform hover:scale-110';
                    span.title = 'قفل - نیاز به پرداخت و فعال‌سازی اشتراک';
                    span.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>';
                    return span;
                };

                const links = document.querySelectorAll('a[href]');
                links.forEach(link => {
                    const parsed = this.parseCleanPath(link.getAttribute('href') || link.href);
                    if (!parsed) return;

                    const lockedMod = this.lockedModules.find(m => this.matchesLockedModule(parsed, m));
                    if (lockedMod) {
                        link.setAttribute('data-module-locked', 'true');
                        link.setAttribute('data-module-slug', lockedMod.slug);

                        if (!link.querySelector('.reseller-lock-badge')) {
                            const titleSpan = link.querySelector('span.truncate, span:not(:empty)');
                            if (titleSpan && titleSpan.parentNode) {
                                titleSpan.parentNode.insertBefore(makeLockBadge(), titleSpan.nextSibling);
                            } else {
                                link.appendChild(makeLockBadge());
                            }
                        }

                        // Also decorate parent group header button if present
                        const groupContainer = link.closest('.group\\/groupblock, .group\\/blockgroup, [class*="groupblock"], [class*="blockgroup"], .relative.mt-1');
                        if (groupContainer) {
                            const headerBtn = groupContainer.querySelector('button');
                            if (headerBtn && !headerBtn.querySelector('.reseller-lock-badge')) {
                                headerBtn.setAttribute('data-module-locked', 'true');
                                headerBtn.setAttribute('data-module-slug', lockedMod.slug);
                                const chevronOrIcon = headerBtn.querySelector('svg:last-of-type, template');
                                if (chevronOrIcon) {
                                    headerBtn.insertBefore(makeLockBadge(), chevronOrIcon);
                                } else {
                                    headerBtn.appendChild(makeLockBadge());
                                }
                            }
                        }
                    }
                });
            },

            setupDomObserver() {
                if (window.MutationObserver && !this._observerAttached) {
                    this._observerAttached = true;
                    let timer;
                    const observer = new MutationObserver(() => {
                        clearTimeout(timer);
                        timer = setTimeout(() => this.decorateLockedElements(), 200);
                    });
                    const target = document.querySelector('aside') || document.body;
                    if (target) {
                        observer.observe(target, {childList: true, subtree: true});
                    }
                }
            },

            closeModal() {
                this.isOpen = false;
            },

            faNum(num) {
                return window.faNum ? window.faNum(num) : (num ?? '');
            },

            formatPrice(amount, currency = null) {
                if (amount === null || amount === undefined) return '';
                const clean = String(amount).replace(/[^0-9.-]+/g, '');
                const num = Number(clean || 0);
                const formatted = num.toLocaleString();
                const cur = currency || this.payload?.currency_label || this.defaultCurrencyLabel;
                return this.faNum(formatted) + (cur ? (' ' + cur) : '');
            },

            async sendUpgradeRequest() {
                if (this.submitting || this.requestSent) return;
                this.submitting = true;

                try {
                    const reqQuota = this.payload?.title || 'افزایش سهمیه';
                    const currentLimit = (this.payload?.limit !== null && this.payload?.limit !== undefined)
                        ? (this.faNum(this.payload.limit) + ' ' + (this.payload?.unit || ''))
                        : 'نامحدود';
                    const msg = 'درخواست خودکار افزایش سقف برای ' + reqQuota + ' (سقف فعلی: ' + currentLimit + ')';

                    const formData = new FormData();
                    formData.append('requested_quota', reqQuota);
                    formData.append('message', msg);

                    const res = await fetch("{{ route('tenant.subscription.request') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await res.json();
                    this.submitting = false;

                    if (res.ok && data.success) {
                        this.requestSent = true;
                        if (window.showToast) {
                            window.showToast('success', data.message || 'درخواست شما برای نماینده ارسال شد.');
                        }
                    } else {
                        const errMsg = data.message || 'خطایی در ارسال پیام رخ داد.';
                        if (typeof window.showResellerNotice === 'function') {
                            window.showResellerNotice({title: 'خطا', message: errMsg, type: 'error'});
                        } else if (window.showToast) {
                            window.showToast('error', errMsg);
                        }
                    }
                } catch (err) {
                    this.submitting = false;
                    const networkErr = 'خطا در برقراری ارتباط با سرور.';
                    if (typeof window.showResellerNotice === 'function') {
                        window.showResellerNotice({title: 'خطا', message: networkErr, type: 'error'});
                    } else if (window.showToast) {
                        window.showToast('error', networkErr);
                    }
                }
            }
        };
    }
</script>

@include('resellerhub::partials.notice-modal')
