@php
    use Modules\ResellerHub\App\Models\ResellerInvoice;
@endphp

<section class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">
    <div class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/70 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span
                    class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        سامانه‌های در آستانه انقضا
                    </h2>
                    <p class="text-xs text-gray-400 font-medium">نیاز به تمدید لایسنس در ۳۰ روز آینده</p>
                </div>
            </div>

            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-xs font-bold border border-amber-200/60 dark:border-amber-800/50">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                {{ reseller_fa_num($expiringTenants->count()) }} سامانه
            </span>
        </div>
        <div class="flex-1 relative">
            @if($expiringTenants->isEmpty())
                <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center h-full space-y-3">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">وضعیت لایسنس‌ها پایدار است</h3>
                        <p class="text-xs text-gray-400 mt-1">هیچ اشتراکی در ۳۰ روز آینده منقضی نخواهد شد.</p>
                    </div>
                </div>
            @else
                <div class="relative">
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700/60 max-h-80 overflow-y-auto">
                        @foreach($expiringTenants as $expiring)
                            @php
                                $daysLeft = reseller_days_remaining($expiring->plan_expires_at);
                                $isCritical = ($daysLeft !== null && $daysLeft <= 7);
                                $expPackage = $expiring->package;
                                $expPeriods = reseller_tenant_renewal_periods($expiring);
                            @endphp
                            <li class="px-5 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50/70 dark:hover:bg-gray-900/20 transition-colors">
                                <div class="min-w-0">
                                    <a href="{{ route('reseller.tenants.show', $expiring->id) }}"
                                       class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block">
                                        {{ $expiring->name }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5 flex-wrap">
                                        <span
                                            class="dir-ltr text-xs text-gray-500">{{ $expiring->subdomain }}</span>
                                        <span>انقضا: {{ reseller_format_jalali($expiring->plan_expires_at) }}</span>
                                        @if($expPackage)
                                            <span class="inline-flex items-center gap-1 font-bold text-xs"
                                                  style="color: {{ $expPackage->color_hex }};">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                                      style="background-color: {{ $expPackage->color_hex }};"></span>
                                                <span>{{ $expPackage->name }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if($expiring->plan_expires_at)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black {{ $isCritical ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                            @if($isCritical)
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            @endif
                                            {{ reseller_human_remaining($expiring->plan_expires_at) }}
                                        </span>
                                    @endif

                                    <button type="button"
                                            @click="openRenewModalByElement($el)"
                                            data-id="{{ $expiring->id }}"
                                            data-name="{{ e($expiring->name) }}"
                                            data-subdomain="{{ e($expiring->subdomain) }}"
                                            data-package="{{ e($expPackage?->name ?? '') }}"
                                            data-expires="{{ e(reseller_format_jalali($expiring->plan_expires_at)) }}"
                                            data-periods="{{ e(json_encode($expPeriods)) }}"
                                            data-url="{{ route('reseller.tenants.renew', $expiring->id) }}"
                                            class="p-2 rounded-xl text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 transition-colors cursor-pointer"
                                            title="تمدید آنی لایسنس">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($expiringTenants->count() > 3)
                        <div
                            class="pointer-events-none absolute bottom-0 inset-x-0 h-6 bg-gradient-to-t from-white dark:from-gray-800 to-transparent"></div>
                    @endif
                </div>
            @endif
        </div>
        <div
            class="px-5 sm:px-6 py-3 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs">
            <span class="text-gray-400">امکان تمدید دستی یا صدور فاکتور خودکار</span>
            <a href="{{ route('reseller.dashboard', ['sort_by' => 'expires_at_asc']) }}"
               class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                <span>مشاهده بر اساس تاریخ انقضا</span>
                <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl overflow-hidden flex flex-col justify-between">
        <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/70 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                        صورتحساب‌های معوق و در انتظار تسویه
                    </h2>
                    <p class="text-xs text-gray-400 font-medium">فاکتورهای نیازمند پیگیری مالی</p>
                </div>
            </div>

            <a href="{{ route('reseller.invoices.index') }}"
               class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                <span>همه فاکتورها</span>
                <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="flex-1 relative">
            @if($unpaidInvoices->isEmpty())
                <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center h-full space-y-3">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">تسویه کامل حساب‌ها</h3>
                        <p class="text-xs text-gray-400 mt-1">هیچ صورتحساب پرداخت‌نشده‌ای در این بخش وجود ندارد.</p>
                    </div>
                </div>
            @else
                <div class="relative">
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700/60 max-h-80 overflow-y-auto">
                        @foreach($unpaidInvoices as $inv)
                            @php
                                $remaining = max(0, (int)$inv->total - (int)$inv->paid_amount);
                                $tone = reseller_invoice_tone($inv->status);
                            @endphp
                            <li class="px-5 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50/70 dark:hover:bg-gray-900/20 transition-colors">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('reseller.invoices.show', $inv->id) }}"
                                           class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">
                                            {{ $inv->invoice_number }}
                                        </a>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border {{ $inv->status_badge_classes ?? $tone['badge_classes'] }}">
                                            {{ $inv->status_label ?? $tone['status_label'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5 truncate">
                                        <span>مشتری: {{ $inv->sub_account_name ?: '—' }}</span>
                                        @if($inv->due_date)
                                            <span>مهلت: {{ reseller_format_jalali($inv->due_date) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="text-start">
                                        <div
                                            class="text-xs sm:text-sm font-black text-gray-900 dark:text-white tabular-nums">
                                            {{ reseller_price($remaining) }}
                                        </div>
                                        <div class="text-xs font-medium text-gray-400 text-end">مانده تسویه</div>
                                    </div>

                                    <a href="{{ route('reseller.invoices.show', $inv->id) }}"
                                       class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors"
                                       title="مشاهده فاکتور">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($unpaidInvoices->count() > 3)
                        <div
                            class="pointer-events-none absolute bottom-0 inset-x-0 h-6 bg-gradient-to-t from-white dark:from-gray-800 to-transparent"></div>
                    @endif
                </div>
            @endif
        </div>
        <div
            class="px-5 sm:px-6 py-3 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between text-xs">
            <span class="text-gray-500 dark:text-gray-400 font-medium">
                مجموع معوقات: <strong
                    class="text-gray-900 dark:text-white font-black">{{ reseller_price($unpaidInvoicesTotal) }}</strong>
            </span>
            <a href="{{ route('reseller.invoices.create') }}"
               class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                <span>+ صدور صورتحساب جدید</span>
            </a>
        </div>
    </div>

</section>
