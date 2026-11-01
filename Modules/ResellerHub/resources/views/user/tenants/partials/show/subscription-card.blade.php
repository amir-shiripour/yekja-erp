<div
    class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between backdrop-blur-xl h-full">
    <h2 class="text-base font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700/60 min-h-[44px] flex items-center justify-between">
        <span>مشخصات فنی و اشتراک</span>
    </h2>

    <div class="space-y-4 text-sm flex-1 pt-4">
        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">آدرس اختصاصی (مسیر):</span>
            <div class="inline-flex items-center gap-2">
                <span class="text-indigo-600 dark:text-indigo-400 font-bold dir-ltr">{{ $displayUrl }}</span>
                <button type="button"
                        @click="copyText('{{ $displayUrl }}', 'آدرس سامانه کپی شد')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded transition"
                        title="کپی آدرس سامانه">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Custom Domain Section --}}
        <div x-show="!customDomain"
             class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-gray-900/60 border border-indigo-100/90 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-indigo-100/80 dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-xs border border-transparent dark:border-gray-700/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-gray-800 dark:text-gray-200">دامنه اختصاصی مشتری</span>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full bg-gray-200/70 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold">تنظیم نشده</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5">اتصال دامنه یا سابدامین برند مشتری
                        (مانند crm.company.com)</p>
                </div>
            </div>
            <button type="button"
                    @click="openDomainModal()"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-sm shadow-indigo-600/20 flex items-center justify-center gap-1.5 cursor-pointer shrink-0 active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>اتصال دامنه اختصاصی</span>
            </button>
        </div>

        <div x-show="customDomain"
             class="p-4 rounded-2xl bg-indigo-50/40 dark:bg-none dark:bg-gray-900/60 border border-indigo-200/70 dark:border-gray-700/60 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-xs border border-transparent dark:border-gray-700/50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs text-gray-400 font-bold block">دامنه اختصاصی متصل شده:</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <a :href="'https://' + customDomain" target="_blank" rel="noopener noreferrer"
                               class="text-sm font-black text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 dir-ltr truncate flex items-center gap-1">
                                <span x-text="customDomain"></span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                            <button type="button"
                                    x-data="{ copied: false }"
                                    @click="copyText(customDomain, 'دامنه اختصاصی کپی شد'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded transition cursor-pointer"
                                    :title="copied ? 'کپی شد' : 'کپی دامنه'">
                                <template x-if="copied">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="!copied">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button"
                        @click="openDomainModal()"
                        class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/60 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>ویرایش / تغییر دامنه</span>
                </button>
            </div>
            <div
                class="flex flex-wrap items-center justify-between gap-2 pt-2.5 border-t border-indigo-100/70 dark:border-gray-700/60 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 font-bold">وضعیت اتصال DNS:</span>
                    <template x-if="dnsChecking">
                        <span class="text-gray-400 flex items-center gap-1 font-bold">
                            <svg class="w-3.5 h-3.5 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            در حال استعلام DNS...
                        </span>
                    </template>
                    <template x-if="!dnsChecking && dnsStatus === 'verified'">
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            متصل و تایید شده
                        </span>
                    </template>
                    <template x-if="!dnsChecking && dnsStatus === 'pending'">
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 font-bold border border-amber-200/60"
                            :title="dnsMessage">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            در انتظار انتشار DNS
                        </span>
                    </template>
                    <template
                        x-if="!dnsChecking && (dnsStatus === 'unresolved' || dnsStatus === 'error' || dnsStatus === 'unavailable')">
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 font-bold border border-red-200/60"
                            :title="dnsMessage">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            عدم اتصال به سرور
                        </span>
                    </template>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="checkDns()" :disabled="dnsChecking"
                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-bold text-xs transition cursor-pointer flex items-center gap-1"
                            title="بررسی مجدد اتصال رکورد DNS">
                        <svg class="w-3 h-3" :class="dnsChecking ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>بررسی مجدد</span>
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button type="button" @click="dnsModalOpen = true"
                            class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-bold text-xs transition cursor-pointer">
                        راهنمای DNS
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">برندینگ و تم سامانه:</span>
            @if($isCustomBranding)
                <span class="font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1.5 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs"
                          style="background-color: {{ $brandPrimaryColor }};"></span>
                    وایت‌ لیبل اختصاصی ({{ $brandTitle ?: $tenant->name }})
                </span>
            @else
                <span class="font-medium text-gray-700 dark:text-gray-300 text-xs">برندینگ پیش‌فرض نماینده</span>
            @endif
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">تاریخ ساخت:</span>
            <span>{{ $tenant->created_at ? reseller_format_jalali($tenant->created_at, 'Y/m/d - H:i') : '—' }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">تاریخ انقضای اشتراک:</span>
            @if($tenant->package && !$tenant->package->getPriceTypeEnum()->hasRenewal())
                <span class="font-bold text-xs {{ $tenant->package->getPriceTypeEnum()->textClass() }}">
                    {{ $tenant->package->getPriceTypeEnum()->licenseBadgeLabel() }}
                </span>
            @else
                <span class="font-bold text-amber-600 dark:text-amber-400" x-text="faNum(expiresAt)"></span>
            @endif
        </div>
    </div>
    <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 mt-auto">
        @if($tenant->isPendingPayment())
            <div
                class="w-full h-12 px-5 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-bold rounded-xl flex items-center justify-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span>سامانه در انتظار پرداخت و تسویه فاکتور است</span>
            </div>
        @elseif($tenant->package && !$tenant->package->getPriceTypeEnum()->hasRenewal())
            <div
                class="w-full h-12 px-5 {{ $tenant->package->getPriceTypeEnum()->badgeClass() }} text-xs font-bold rounded-xl flex items-center justify-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ $tenant->package->getPriceTypeEnum()->licenseSubtitle() }}</span>
            </div>
        @else
            <button type="button"
                    @click="openRenewModal()"
                    class="w-full h-12 px-5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 cursor-pointer active:scale-95 box-border border border-transparent">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>تمدید لایسنس و افزایش مدت اعتبار</span>
            </button>
        @endif
    </div>
</div>
