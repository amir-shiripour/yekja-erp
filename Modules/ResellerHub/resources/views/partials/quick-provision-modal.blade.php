@php
    $mode = $mode ?? 'package';
    $currencyLabel = $currencyLabel ?? 'تومان';
@endphp

<div x-show="quickProvisionModalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-md overflow-y-auto"
     @keydown.escape.window="if (typeof quickSubModalOpen !== 'undefined' && quickSubModalOpen) { quickSubModalOpen = false; } else { closeQuickProvision(); }">

    <div @click.away="if (typeof quickSubModalOpen === 'undefined' || !quickSubModalOpen) closeQuickProvision()"
         class="relative bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-2xl max-w-3xl w-full my-6 overflow-hidden flex flex-col max-h-[92vh]">
        <div
            class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between shrink-0 bg-gray-50/50 dark:bg-gray-900/40">
            <div class="flex items-center gap-3.5 min-w-0">
                <span
                    class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-md transition-all duration-300"
                    :style="'background-color: ' + (getColorHex(selectedPackage?.color_hex || selectedPackage?.color) || '#4f46e5') + '; color: #ffffff;'"
                    x-html="renderIcon(selectedPackage?.icon || 'cube', 'w-6 h-6 text-white')"></span>
                <div class="min-w-0">
                    @if($mode === 'subaccount')
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white truncate">
                                ساخت سریع CRM برای این خریدار
                            </h3>
                            <span
                                class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                {{ $subAccount->name ?? '' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 truncate">
                            سامانه اختصاصی این خریدار در چند ثانیه همراه با فاکتور و لایسنس فعال خواهد شد.
                        </p>
                    @else
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white truncate">
                                راه‌اندازی سریع CRM با این پکیج
                            </h3>
                            <span
                                class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-indigo-600 text-white shadow-xs transition-colors"
                                :style="'background-color: ' + (getColorHex(selectedPackage?.color_hex || selectedPackage?.color) || '#4f46e5') + '; color: #ffffff;'"
                                x-text="selectedPackage?.name"></span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 truncate">
                            سامانه با <span class="font-bold text-indigo-600 dark:text-indigo-400"
                                            x-text="faNum((selectedPackage?.modules || []).length) + ' ماژول'"></span>
                            و <span class="font-bold text-indigo-600 dark:text-indigo-400"
                                    x-text="faNum((selectedPackage?.features || []).length) + ' قابلیت'"></span>
                            این صنف در چند ثانیه فعال می‌شود.
                        </p>
                    @endif
                </div>
            </div>

            <button type="button" @click="closeQuickProvision()" :disabled="submitting"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer shrink-0 disabled:opacity-40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
            <template x-if="generalError">
                <div
                    class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-300 text-xs sm:text-sm space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="generalError"></span>
                    </div>
                    <template x-if="Object.keys(formErrors).length > 0">
                        <ul class="list-disc list-inside space-y-0.5 ms-4 text-xs">
                            <template x-for="(errList, field) in formErrors" :key="field">
                                <li x-text="errList[0]"></li>
                            </template>
                        </ul>
                    </template>
                </div>
            </template>
            <template x-if="createdTenant">
                <div class="py-6 text-center space-y-6">
                    <div
                        class="w-20 h-20 rounded-3xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20 ring-8 ring-emerald-500/10">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h4 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">
                            تبریک! سامانه CRM با موفقیت راه‌اندازی شد
                        </h4>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            سامانه اختصاصی <span class="font-bold text-gray-900 dark:text-white"
                                                 x-text="'«' + createdTenant.name + '»'"></span> آماده استفاده است.
                        </p>
                    </div>
                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-start space-y-2">
                        <x-resellerhub::label class="!text-gray-400 !mb-0">آدرس اختصاصی ورود به سامانه:
                        </x-resellerhub::label>
                        <div class="flex items-center justify-between gap-2">
                            <a :href="createdTenant.sso_login_url || createdTenant.url" target="_blank"
                               class="text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline truncate dir-ltr"
                               x-text="createdTenant.direct_url || createdTenant.url"></a>
                            <button type="button"
                                    @click="copyToClipboard(createdTenant.direct_url || createdTenant.url, 'url')"
                                    class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shrink-0 cursor-pointer">
                                <span x-text="copiedUrl ? 'کپی شد!' : 'کپی آدرس'"></span>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-start">
                        <div
                            class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 space-y-1">
                            <span class="text-xs text-gray-400 block">خریدار سامانه:</span>
                            <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block"
                                  x-text="(typeof selectedSubAccount !== 'undefined' && selectedSubAccount ? selectedSubAccount.name : '') || (createdTenant.sub_account_name || '—')"></span>
                        </div>
                        <div
                            class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 space-y-1">
                            <span class="text-xs text-gray-400 block">مدت اعتبار اشتراک:</span>
                            <span class="text-xs sm:text-sm font-black text-indigo-600 dark:text-indigo-400 block"
                                  x-text="durationLabel"></span>
                        </div>
                    </div>
                    <template x-if="createdInvoice || createdOrder">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-start">
                            <template x-if="createdInvoice">
                                <div
                                    class="p-3.5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div
                                            class="flex items-center gap-1.5 text-xs font-black text-emerald-900 dark:text-emerald-200">
                                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>فاکتور صادر شده</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md text-xs font-black"
                                              :class="createdInvoice.status === 'paid' ? 'bg-emerald-200/80 text-emerald-800 dark:bg-emerald-900/80 dark:text-emerald-200' : 'bg-amber-200/80 text-amber-800 dark:bg-amber-900/80 dark:text-amber-200'"
                                              x-text="createdInvoice.status_label || (createdInvoice.status === 'paid' ? 'پرداخت شده' : 'در انتظار پرداخت')"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500 dark:text-gray-400">شماره فاکتور:</span>
                                        <span class="font-bold text-gray-900 dark:text-white"
                                              x-text="createdInvoice.invoice_number"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500 dark:text-gray-400">مبلغ کل:</span>
                                        <span class="font-black text-emerald-600 dark:text-emerald-400"
                                              x-text="(createdInvoice.formatted_total || formatPrice(createdInvoice.total || createdInvoice.total_amount)) + ' ' + currencyLabel"></span>
                                    </div>
                                    <div
                                        class="pt-2 border-t border-emerald-100 dark:border-emerald-900/40 flex items-center gap-2">
                                        <a :href="createdInvoice.show_url" target="_blank"
                                           class="flex-1 text-center py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">مشاهده
                                            فاکتور</a>
                                        <a :href="createdInvoice.print_url" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-50 text-emerald-700 dark:text-emerald-300 text-xs font-bold transition">چاپ</a>
                                    </div>
                                </div>
                            </template>

                            <template x-if="createdOrder">
                                <div
                                    class="p-3.5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div
                                            class="flex items-center gap-1.5 text-xs font-black text-indigo-900 dark:text-indigo-200">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                            <span>سفارش ثبت شده</span>
                                        </div>
                                        <span
                                            class="px-2 py-0.5 rounded-md text-xs font-black bg-indigo-200/80 text-indigo-800 dark:bg-indigo-900/80 dark:text-indigo-200">فعال و تحویل‌شده</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500 dark:text-gray-400">شماره سفارش:</span>
                                        <span class="font-bold text-gray-900 dark:text-white"
                                              x-text="createdOrder.order_number"></span>
                                    </div>
                                    <div class="pt-2 border-t border-indigo-100 dark:border-indigo-900/40">
                                        <a :href="createdOrder.show_url" target="_blank"
                                           class="block w-full text-center py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">مشاهده
                                            جزئیات سفارش</a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Next Actions --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                        <a :href="createdTenant.sso_login_url || createdTenant.url" target="_blank"
                           class="w-full sm:w-auto px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md transition flex items-center justify-center gap-2">
                            <span>ورود مستقیم به پنل CRM جدید</span>
                            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>

                        <template x-if="createdInvoice">
                            <a :href="createdInvoice.show_url" target="_blank"
                               class="w-full sm:w-auto px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <span>مشاهده فاکتور</span>
                            </a>
                        </template>

                        <template x-if="createdTenant.redirect_url">
                            <a :href="createdTenant.redirect_url"
                               class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <span>مشاهده در مدیریت سامانه‌ها</span>
                            </a>
                        </template>

                        <button type="button" @click="closeQuickProvision()"
                                class="w-full sm:w-auto px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold transition cursor-pointer">
                            بستن
                        </button>
                    </div>
                </div>
            </template>
            <template
                x-if="!createdTenant">
                <form @submit.prevent="submitQuickProvision()" class="space-y-6">

                    @if($mode === 'subaccount')
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                <span
                                    class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center">۱</span>
                                <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                    خریدار سامانه (حساب زیرمجموعه)
                                </h4>
                            </div>

                            <div
                                class="p-3.5 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span
                                        class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-black flex items-center justify-center shrink-0 text-sm shadow-xs">
                                        {{ isset($subAccount) ? mb_substr($subAccount->name, 0, 1, 'UTF-8') : 'U' }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-black text-gray-900 dark:text-white truncate">
                                            {{ $subAccount->name ?? '' }}
                                            @if(!empty($subAccount->company_name))
                                                <span class="text-gray-500 dark:text-gray-400 font-normal ms-1">({{ $subAccount->company_name }})</span>
                                            @endif
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-3 flex-wrap">
                                            <span>شماره همراه: <strong
                                                    class="text-gray-800 dark:text-gray-200">{{ isset($subAccount->mobile) ? reseller_fa_num($subAccount->mobile) : '-' }}</strong></span>
                                            @if(!empty($subAccount->email))
                                                <span class="truncate">ایمیل: <span
                                                        class="dir-ltr inline-block">{{ $subAccount->email }}</span></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 self-start sm:self-auto shrink-0">
                                    خریدار ثبت‌شده
                                </span>
                            </div>
                        </div>

                        {{-- SECTION 2: Select Software Package --}}
                        <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center">۲</span>
                                    <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                        انتخاب پکیج نرم‌افزاری <span class="text-rose-500">*</span>
                                    </h4>
                                </div>
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                      x-text="selectedPackage ? selectedPackage.name : 'انتخاب نشده'"></span>
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-52 overflow-y-auto custom-scrollbar p-1">
                                <template x-for="pkg in packagesList" :key="pkg.id">
                                    <div @click="selectPackage(pkg)"
                                         class="p-3 rounded-2xl border-2 transition-all cursor-pointer flex items-center gap-3 select-none"
                                         :class="selectedPackage?.id === pkg.id
                                            ? 'border-indigo-600 bg-indigo-50/60 dark:bg-indigo-950/40 shadow-sm ring-2 ring-indigo-500/20'
                                            : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 bg-white dark:bg-gray-800/60'">

                                        <span
                                            class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-xs"
                                            :style="`background-color: ${getColorHex(pkg.color)}; color: #ffffff;`"
                                            x-html="renderIcon(pkg.icon, 'w-5 h-5')"></span>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-black text-xs text-gray-900 dark:text-white truncate"
                                                      x-text="pkg.name"></span>
                                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                                      x-text="faNum(pkg.modules_count || pkg.modules?.length || 0) + ' ماژول'"></span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                                <template x-if="pkg.price_type === 'one_time'">
                                                    <span><span x-text="formatPrice(pkg.one_time_price)"></span> {{ $currencyLabel }} یک‌باره</span>
                                                </template>
                                                <template x-if="pkg.price_type === 'free'">
                                                    <span class="text-emerald-600 font-bold">رایگان</span>
                                                </template>
                                                <template x-if="!pkg.price_type || pkg.price_type === 'subscription'">
                                                    <span><span x-text="formatPrice(pkg.yearly_price)"></span> {{ $currencyLabel }} سالانه</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @else
                        {{-- SECTION 1: Buyer (Sub-Account) Selection in Package Mode --}}
                        <div class="space-y-3">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center">۱</span>
                                        <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                            انتخاب خریدار سامانه (حساب زیرمجموعه) <span class="text-rose-500">*</span>
                                        </h4>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5 ms-8">
                                        خریدار این سامانه CRM را از لیست زیرمجموعه‌های فعال خود انتخاب کنید یا سریعاً یک
                                        خریدار جدید ثبت نمایید.
                                    </p>
                                </div>
                                <button type="button" @click="openQuickSubModal()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition shadow-2xs border border-indigo-100 dark:border-indigo-900/50 cursor-pointer shrink-0 self-start sm:self-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>تعریف خریدار جدید</span>
                                </button>
                            </div>

                            {{-- Searchable SubAccount Dropdown --}}
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer flex items-center justify-between text-start"
                                        :class="formErrors.sub_account_id ? 'border-rose-400 dark:border-rose-600 ring-2 ring-rose-500/20' : ''">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div
                                            class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0">
                                            <span
                                                x-text="selectedSubAccount ? (selectedSubAccount.name ? selectedSubAccount.name.charAt(0) : 'U') : '؟'"></span>
                                        </div>
                                        <template x-if="selectedSubAccount">
                                            <div
                                                class="truncate text-xs sm:text-sm font-bold text-gray-900 dark:text-white">
                                                <span x-text="selectedSubAccount.name"></span>
                                                <template x-if="selectedSubAccount.company_name">
                                                    <span class="text-gray-500 dark:text-gray-400 font-medium ms-1"
                                                          x-text="'(' + selectedSubAccount.company_name + ')'"></span>
                                                </template>
                                                <span class="text-gray-400 font-normal ms-2 text-xs"
                                                      x-text="'— ' + faNum(selectedSubAccount.mobile)"></span>
                                            </div>
                                        </template>
                                        <template x-if="!selectedSubAccount">
                                            <span class="text-gray-400 text-xs sm:text-sm">انتخاب یا جستجوی حساب خریدار (زیرمجموعه)...</span>
                                        </template>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                         :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- Dropdown options list --}}
                                <div x-show="open" x-cloak
                                     class="absolute z-50 left-0 right-0 top-full mt-2 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-60 overflow-y-auto custom-scrollbar">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <x-resellerhub::input type="text" x-model="subAccountSearchQuery"
                                                              placeholder="جستجوی خریدار بر اساس نام، شماره موبایل یا نام شرکت..."
                                                              class="!text-xs !p-2"/>
                                    </div>

                                    <template x-for="sub in filteredSubAccounts()" :key="sub.id">
                                        <div @click="selectSubAccount(sub); open = false;"
                                             class="px-3 py-2 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50/80 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': selectedSubAccount && selectedSubAccount.id === sub.id }">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div
                                                    class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0">
                                                    <span x-text="sub.name ? sub.name.charAt(0) : 'U'"></span>
                                                </div>
                                                <div class="truncate">
                                                    <span x-text="sub.name" class="font-medium"></span>
                                                    <template x-if="sub.company_name">
                                                        <span class="text-gray-400 ms-1 text-xs"
                                                              x-text="'(' + sub.company_name + ')'"></span>
                                                    </template>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400 shrink-0 tabular-nums"
                                                  x-text="faNum(sub.mobile)"></span>
                                        </div>
                                    </template>

                                    <div x-show="filteredSubAccounts().length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ خریدار یا زیرمجموعه‌ای یافت نشد
                                    </div>
                                </div>
                            </div>
                            <p x-show="formErrors.sub_account_id"
                               x-text="(formErrors.sub_account_id && formErrors.sub_account_id[0]) || ''"
                               class="text-rose-500 text-xs mt-1 font-bold ms-1"></p>

                            {{-- Selected Buyer Info Card --}}
                            <template x-if="selectedSubAccount">
                                <div
                                    class="p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span
                                            class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-black flex items-center justify-center shrink-0 text-xs shadow-xs"
                                            x-text="selectedSubAccount.name ? selectedSubAccount.name.charAt(0) : 'U'"></span>
                                        <div class="min-w-0">
                                            <div class="font-bold text-gray-900 dark:text-white truncate"
                                                 x-text="selectedSubAccount.name + (selectedSubAccount.company_name ? ' (' + selectedSubAccount.company_name + ')' : '')"></div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-3 flex-wrap">
                                                <span>شماره همراه: <strong class="text-gray-800 dark:text-gray-200"
                                                                           x-text="faNum(selectedSubAccount.mobile)"></strong></span>
                                                <template x-if="selectedSubAccount.email">
                                                    <span class="truncate">ایمیل: <span class="dir-ltr inline-block"
                                                                                        x-text="selectedSubAccount.email"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="clearSubAccount()"
                                            class="text-xs font-bold text-rose-500 hover:text-rose-700 hover:underline cursor-pointer shrink-0">
                                        تغییر خریدار
                                    </button>
                                </div>
                            </template>
                        </div>
                    @endif

                    {{-- SECTION: Organization Name & Subdomain --}}
                    <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center">
                                {{ $mode === 'subaccount' ? '۳' : '۲' }}
                            </span>
                            <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">نام و نشانی سامانه
                                CRM</h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-resellerhub::label>نام کسب‌وکار یا سازمان</x-resellerhub::label>
                                <x-resellerhub::input type="text" x-model="form.name" @input="autoSuggestSubdomain()"
                                                      placeholder="مثلاً: کلینیک تخصصی دکتر کاظمی"/>
                                <p class="text-xs text-gray-400 mt-1 ms-1">در صورت خالی ماندن، نام خریدار یا نام
                                    شرکت وی استفاده می‌شود.</p>
                                <p x-show="formErrors.name" x-text="(formErrors.name && formErrors.name[0]) || ''"
                                   class="text-rose-500 text-xs mt-1 font-bold ms-1"></p>
                            </div>

                            <div>
                                <x-resellerhub::label>شناسه و مسیر اختصاصی سامانه <span class="text-rose-500">*</span>
                                </x-resellerhub::label>
                                <div dir="ltr"
                                     class="relative flex items-stretch rounded-xl bg-gray-50/70 dark:bg-gray-900/50 border overflow-hidden transition-all focus-within:ring-4 focus-within:ring-indigo-500/15 focus-within:border-indigo-500"
                                     :class="subdomainStatus === 'available' ? 'border-emerald-400 dark:border-emerald-600' : (subdomainStatus === 'unavailable' ? 'border-rose-400 dark:border-rose-600' : 'border-gray-200 dark:border-gray-700')">
                                    <span
                                        class="px-3 flex items-center bg-gray-100 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 text-xs font-black text-gray-500 dark:text-gray-400 whitespace-nowrap select-none dir-ltr"
                                        x-text="(typeof cleanBaseDomain === 'function' ? cleanBaseDomain() : (baseDomain || (typeof window !== 'undefined' ? window.location.hostname : '')).replace(/:\d+$/, '')) + '/'"></span>
                                    <input type="text" x-model="form.subdomain" @input="onSubdomainInput()" required
                                           placeholder="kazemi-clinic" dir="ltr"
                                           class="flex-1 min-w-0 bg-transparent px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none text-left border-0">
                                </div>
                                <div class="flex items-center gap-1.5 mt-1 ms-1 text-xs min-h-[16px]">
                                    <template x-if="subdomainChecking">
                                        <span class="text-gray-400 flex items-center gap-1">
                                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                      d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>بررسی دسترسی...</span>
                                        </span>
                                    </template>
                                    <template x-if="!subdomainChecking && subdomainStatus === 'available'">
                                        <span
                                            class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span x-text="subdomainMsg"></span>
                                        </span>
                                    </template>
                                    <template x-if="!subdomainChecking && subdomainStatus === 'unavailable'">
                                        <span
                                            class="text-rose-600 dark:text-rose-400 font-bold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span x-text="subdomainMsg"></span>
                                        </span>
                                    </template>
                                    <template x-if="subdomainStatus === 'idle'">
                                        <span class="text-gray-400">فقط حروف انگلیسی، اعداد و خط تیره (-)</span>
                                    </template>
                                </div>
                                <p x-show="formErrors.subdomain"
                                   x-text="(formErrors.subdomain && formErrors.subdomain[0]) || ''"
                                   class="text-rose-500 text-xs mt-1 font-bold ms-1"></p>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: Duration & Cycle Selection --}}
                    <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-xs flex items-center justify-center">
                                    {{ $mode === 'subaccount' ? '۴' : '۳' }}
                                </span>
                                <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">دوره و مدت
                                    اعتبار اولیه سامانه</h4>
                            </div>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                  x-text="'انتخاب شده: ' + durationLabel"></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <template x-for="opt in durationOptions" :key="opt.value">
                                <button type="button" @click="selectPackageDuration(opt.value)"
                                        class="p-2.5 rounded-2xl border text-center transition-all duration-200 cursor-pointer flex flex-col items-center justify-center gap-1 select-none"
                                        :class="String(form.expires_in_months) === String(opt.value)
                                            ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 ring-2 ring-indigo-500/20 text-indigo-950 dark:text-white font-black shadow-xs'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 font-bold'">
                                    <span class="text-xs" x-text="opt.label"></span>
                                    <span class="text-xs"
                                          :class="String(form.expires_in_months) === String(opt.value) ? 'text-indigo-600 dark:text-indigo-300 font-black' : 'text-gray-400 font-bold'"
                                          x-text="formatPrice(getPackagePriceForDuration(opt.value)) + ' ' + currencyLabel"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- SECTION: Package Pricing & Modules Summary --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-stretch">
                        <div
                            class="p-3.5 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 shadow-2xs space-y-2 flex flex-col justify-between">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-xl bg-indigo-100 dark:bg-indigo-900/80 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-xs font-black text-gray-900 dark:text-white block">تعرفه این پکیج</span>
                                            <span class="text-xs text-gray-400 block"
                                                  x-text="durationLabel"></span>
                                        </div>
                                    </div>
                                    <span
                                        class="text-base sm:text-lg font-black text-indigo-600 dark:text-indigo-400 block"
                                        x-text="formatPrice(getPackagePriceForDuration()) + ' ' + currencyLabel"></span>
                                </div>

                                <div
                                    class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs"
                                    x-show="getPackageSetupFee() > 0">
                                        <span
                                            class="text-gray-500 dark:text-gray-400">هزینه راه‌اندازی (یک‌باره):</span>
                                    <span class="font-bold text-amber-600 dark:text-amber-400"
                                          x-text="formatPrice(getPackageSetupFee()) + ' ' + currencyLabel"></span>
                                </div>

                                <div
                                    class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs"
                                    x-show="getActivePackageDiscount() > 0">
                                    <span class="text-rose-600 dark:text-rose-400 font-bold">تخفیف پکیج:</span>
                                    <span class="font-bold text-rose-600 dark:text-rose-400"
                                          x-text="'- ' + formatPrice(getActivePackageDiscount()) + ' ' + currencyLabel"></span>
                                </div>
                            </div>

                            <div
                                class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs mt-auto">
                                <span class="font-black text-gray-800 dark:text-gray-200">مجموع اشتراک:</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm"
                                      x-text="formatPrice(getPackageTotalAmount()) + ' ' + currencyLabel"></span>
                            </div>
                        </div>

                        <div
                            class="p-3.5 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 shadow-2xs flex flex-col justify-between">
                            <div
                                class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-900/80 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black text-gray-900 dark:text-white block">ماژول‌های فعال این صنف</span>
                                        <span class="text-xs text-gray-400 block"
                                              x-text="faNum((selectedPackage?.modules || []).length) + ' ماژول پیش‌فرض'"></span>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                                    فعال و آماده
                                </span>
                            </div>

                            <div
                                class="flex-1 mt-2.5 overflow-y-auto max-h-28 custom-scrollbar flex flex-wrap content-start gap-1.5 p-0.5">
                                <template x-for="slug in (selectedPackage?.modules || [])" :key="slug">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-gray-50 dark:bg-gray-900/70 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 shadow-2xs">
                                        <svg class="w-2.5 h-2.5 text-emerald-500 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span x-text="getModuleTitle(slug)"></span>
                                    </span>
                                </template>
                                <template x-if="!(selectedPackage?.modules || []).length">
                                        <span
                                            class="text-xs text-gray-400 py-2">ماژولی برای این پکیج ثبت نشده است.</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: Invoice Integration --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                        <div
                            class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40">
                            <div class="flex items-center gap-3">
                                <span
                                    class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-xl shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">صدور
                                        فاکتور
                                        برای این سامانه</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        شماره‌گذاری، مالیات و شرایط پرداخت بر اساس تنظیمات مالی سامانه نمایندگی
                                    </p>
                                </div>
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                                <span class="text-xs sm:text-sm font-bold transition-colors"
                                      :class="form.issue_invoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                                      x-text="form.issue_invoice ? 'فاکتور صادر شود' : 'بدون فاکتور'"></span>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" x-model="form.issue_invoice" class="sr-only peer">
                                    <div
                                        class="w-13 h-7 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                                    <div
                                        class="absolute right-1 top-1 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                        <svg
                                            class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Expanded Invoice Controls --}}
                        <div x-show="form.issue_invoice" x-transition class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <x-resellerhub::label class="!mb-0">شماره فاکتور <span
                                                class="text-rose-500">*</span></x-resellerhub::label>
                                        <button type="button" @click="toggleInvoiceNumberAuto()"
                                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-black border transition-all cursor-pointer"
                                                :class="invoiceNumberAuto
                                                    ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400'
                                                    : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'">
                                            <svg x-show="invoiceNumberAuto" class="w-3 h-3" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span
                                                x-text="invoiceNumberAuto ? 'تولید خودکار (از تنظیمات)' : 'ورود دستی'"></span>
                                        </button>
                                    </div>
                                    <x-resellerhub::input class="text-start font-medium tabular-nums transition-all"
                                                          type="text" dir="ltr"
                                                          x-bind:class="invoiceNumberAuto ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400' : ''"
                                                          placeholder="INV-14050001"
                                                          x-model="invoiceNumberAuto ? invoiceNumberPreview : invoiceNumberOverride"
                                                          x-bind:readonly="invoiceNumberAuto"/>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <x-resellerhub::label class="!mb-0">نرخ مالیات بر ارزش افزوده (کل فاکتور)
                                        </x-resellerhub::label>
                                        <button type="button" @click="toggleTaxRateLock()"
                                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-black border transition-all cursor-pointer"
                                                :class="taxRateLocked
                                                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400'
                                                    : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <span
                                                x-text="taxRateLocked ? 'قفل روی پیش‌فرض تنظیمات' : 'ویرایش دستی نرخ'"></span>
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <x-resellerhub::input
                                            class="text-center font-black tabular-nums transition-all"
                                            type="number" min="0" max="100" step="0.5"
                                            x-model="invoiceTaxRate" @input="updateInvoiceTotals()"
                                            x-bind:readonly="taxRateLocked"
                                            x-bind:class="taxRateLocked ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400' : ''"
                                            placeholder="10"/>
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">درصد (٪)</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Invoice Items Table --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <x-resellerhub::label class="!mb-0">اقلام و ردیف‌های صورتحساب مالی
                                    </x-resellerhub::label>
                                    <span class="text-xs font-bold text-gray-400"
                                          x-text="faNum((this.quickInvoiceItems || this.invoiceItems || []).length) + ' ردیف مالی'"></span>
                                </div>

                                <div
                                    class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700">
                                    <table class="w-full text-xs text-start">
                                        <thead
                                            class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th scope="col" class="px-4 py-2.5 text-start font-bold">شرح ردیف</th>
                                            <th scope="col" class="px-4 py-2.5 text-center font-bold">نوع قلم</th>
                                            <th scope="col" class="px-4 py-2.5 text-center font-bold">مدت / تعداد</th>
                                            <th scope="col" class="px-4 py-2.5 text-center font-bold">مبلغ واحد
                                                ({{ $currencyLabel }})
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-800/50">
                                        <template
                                            x-for="(item, idx) in (this.quickInvoiceItems || this.invoiceItems || [])"
                                            :key="idx">
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full"
                                                              :class="item.item_type === 'package' ? 'bg-indigo-600' : (item.item_type === 'setup_fee' ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                                                        <span x-text="item.title"></span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                                                          :class="item.item_type === 'package' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400' : (item.item_type === 'setup_fee' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400')"
                                                          x-text="item.item_type === 'package' ? 'پکیج اصلی' : (item.item_type === 'setup_fee' ? 'راه‌اندازی' : 'ماژول')"></span>
                                                </td>
                                                <td class="px-4 py-3 text-center tabular-nums text-gray-600 dark:text-gray-300">
                                                    <span
                                                        x-text="item.duration_months > 0 ? (getDurationLabel ? getDurationLabel(item.duration_months) : faNum(item.duration_months) + ' ماهه') : (faNum(item.quantity || 1) + ' عدد')"></span>
                                                </td>
                                                <td class="px-4 py-3 text-center font-black tabular-nums"
                                                    :class="item.unit_price > 0 ? 'text-gray-900 dark:text-white' : 'text-emerald-600 dark:text-emerald-400'"
                                                    x-text="item.unit_price > 0 ? formatPrice(item.unit_price) : 'رایگان / شامل در پکیج'"></td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Invoice Terms & Notes --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <x-resellerhub::label class="!mb-0">شرایط و قوانین فاکتور</x-resellerhub::label>
                                    <button type="button" @click="invoiceTermsLocked = !invoiceTermsLocked"
                                            class="text-xs font-black px-2 py-0.5 rounded-lg border transition-all cursor-pointer"
                                            :class="invoiceTermsLocked
                                                ? 'bg-indigo-50 border-indigo-200 text-indigo-600 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400'
                                                : 'bg-gray-100 border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'"
                                            x-text="invoiceTermsLocked ? 'از تنظیمات' : 'ویرایش دستی'"></button>
                                </div>
                                <x-resellerhub::textarea class="!text-xs leading-5 resize-y transition-all"
                                                         x-model="invoiceTerms" rows="2"
                                                         x-bind:readonly="invoiceTermsLocked"
                                                         x-bind:class="invoiceTermsLocked ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400' : ''"
                                                         placeholder="شرایط، قوانین و یادداشت فاکتور..."></x-resellerhub::textarea>
                            </div>

                            {{-- Financial Summary Card --}}
                            <div
                                class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 space-y-2.5 text-xs shadow-2xs">
                                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                    <span>جمع اقلام فاکتور:</span>
                                    <span class="font-black text-gray-800 dark:text-gray-200 tabular-nums"
                                          x-text="formatPrice(getInvoiceSubtotal()) + ' ' + currencyLabel"></span>
                                </div>

                                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                    <span>مالیات بر ارزش افزوده (<span
                                            x-text="faNum(parseFloat(invoiceTaxRate || 0)) + '٪'"></span>):</span>
                                    <span class="font-black text-amber-600 dark:text-amber-400 tabular-nums"
                                          x-text="formatPrice(getInvoiceTax()) + ' ' + currencyLabel"></span>
                                </div>

                                <div
                                    class="flex items-center justify-between gap-3 pt-1 border-t border-gray-100 dark:border-gray-700/60">
                                    <span class="text-rose-600 dark:text-rose-400 font-bold">تخفیف کل فاکتور:</span>
                                    <div class="flex items-center gap-1.5">
                                        <x-resellerhub::input type="text" inputmode="numeric"
                                                              x-model="invoiceDiscountDisplay"
                                                              @input="handleInvoiceDiscountInput($event)"
                                                              @change="updateInvoiceDiscount()"
                                                              class="!w-28 border-rose-200 dark:border-rose-500/30 !px-2 !py-1 text-center !text-xs !font-black focus:border-rose-500 tabular-nums"
                                                              placeholder="۰"/>
                                        <span
                                            class="text-xs text-rose-400 font-bold">{{ $currencyLabel }}</span>
                                    </div>
                                </div>

                                <template x-if="getActivePackageDiscount() > 0">
                                    <div
                                        class="flex items-center justify-between text-indigo-600 dark:text-indigo-400 pt-1">
                                        <span class="font-bold">تخفیف اختصاصی پکیج:</span>
                                        <span class="font-black tabular-nums"
                                              x-text="'− ' + formatPrice(getActivePackageDiscount()) + ' ' + currencyLabel"></span>
                                    </div>
                                </template>

                                <div
                                    class="pt-3 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm">
                                    <span
                                        class="font-black text-gray-900 dark:text-white">مبلغ نهایی قابل پرداخت:</span>
                                    <span
                                        class="font-black text-emerald-600 dark:text-emerald-400 text-base sm:text-lg tabular-nums"
                                        x-text="formatPrice(getInvoiceTotal()) + ' ' + currencyLabel"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Footer --}}
                    <div
                        class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between sm:justify-end gap-3">
                        <button type="button" @click="closeQuickProvision()" :disabled="submitting"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer disabled:opacity-40">
                            انصراف
                        </button>

                        <button type="submit"
                                :disabled="submitting || subdomainStatus === 'unavailable' || subdomainChecking || ({{ $mode === 'subaccount' ? '!selectedPackage' : '!form.sub_account_id' }})"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 disabled:opacity-50 text-white text-xs font-black shadow-lg shadow-indigo-500/25 transition cursor-pointer flex items-center gap-2">
                            <template x-if="submitting">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!submitting">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </template>
                            <span>راه‌اندازی فوری CRM</span>
                            <span class="opacity-90 text-xs font-normal"
                                  x-text="'(' + formatPrice(form.issue_invoice ? getInvoiceTotal() : getPackageTotalAmount()) + ' ' + currencyLabel + ')'"></span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    @if($mode === 'package')
        {{-- Quick Sub-Account Creation Modal (Nested) --}}
        <div x-show="quickSubModalOpen" x-cloak @click.stop="$event.stopPropagation()"
             class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="quickSubModalOpen = false" @click.stop="$event.stopPropagation()"
                 class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl max-w-md w-full overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white">تعریف سریع حساب زیرمجموعه</h4>
                            <p class="text-xs text-gray-400 mt-0.5">افزودن خریدار جدید به عنوان زیرمجموعه</p>
                        </div>
                    </div>
                    <button type="button" @click="quickSubModalOpen = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitQuickSubAccount()" class="p-5 space-y-4">
                    <div
                        class="p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 text-xs text-indigo-900 dark:text-indigo-300 leading-relaxed">
                        فقط نام و شماره همراه زیرمجموعه را وارد کنید؛ اطلاعات ورود به سامانه به صورت خودکار ایجاد و
                        پیامک خواهد
                        شد.
                    </div>

                    <div>
                        <x-resellerhub::label>نام و نام خانوادگی مدیر زیرمجموعه <span class="text-rose-500">*</span>
                        </x-resellerhub::label>
                        <x-resellerhub::input type="text" x-model="quickSubForm.name" required
                                              placeholder="مثال: علی رضایی"/>
                        <template x-if="quickSubErrors.name">
                            <p class="text-xs text-rose-500 mt-1 font-bold" x-text="quickSubErrors.name[0]"></p>
                        </template>
                    </div>

                    <div>
                        <x-resellerhub::label>شماره تلفن همراه <span class="text-rose-500">*</span>
                        </x-resellerhub::label>
                        <x-resellerhub::input class="text-left" type="text" x-model="quickSubForm.mobile" required
                                              dir="ltr" inputmode="numeric" placeholder="09123456789"/>
                        <p class="text-xs text-gray-400 mt-1">شماره همراه ۱۱ رقمی مدیر زیرمجموعه</p>
                        <template x-if="quickSubErrors.mobile">
                            <p class="text-xs text-rose-500 mt-1 font-bold" x-text="quickSubErrors.mobile[0]"></p>
                        </template>
                    </div>

                    <div
                        class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700/60">
                        <button type="button" @click="quickSubModalOpen = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit" :disabled="quickSubSaving"
                                class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black shadow-md transition cursor-pointer flex items-center gap-2">
                            <template x-if="quickSubSaving">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span>ثبت و انتخاب آنی زیرمجموعه</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
