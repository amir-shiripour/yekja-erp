<template x-teleport="body">
    <div x-show="upgradeModal" x-cloak class="relative z-[9999]" aria-labelledby="modal-title" role="dialog"
         aria-modal="true">
        {{-- Backdrop (sits behind modal card) --}}
        <div x-show="upgradeModal"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9998]"
             @click="upgradeModal = false"
             aria-hidden="true"></div>

        {{-- Centered Dialog Container --}}
        <div class="fixed inset-0 z-[9999] overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                {{-- Modal Card (isolated from backdrop blur) --}}
                <div x-show="upgradeModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop="$event.stopPropagation()"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8 space-y-6 z-[10000]">
                    <div
                        class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white" id="modal-title">
                                    {{ $hasNoPackage ? __('resellerhub::messages.subscription.views.select_package_btn') : __('resellerhub::messages.subscription.views.upgrade_modal_title') }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ __('resellerhub::messages.subscription.views.upgrade_modal_desc') }}
                                </p>
                            </div>
                        </div>
                        <button @click="upgradeModal = false"
                                class="p-1 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Current Package Banner --}}
                    <div
                        class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 text-xs space-y-2.5">
                        <div class="flex items-center justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-300 font-bold">{{ __('resellerhub::messages.subscription.views.current_plan_badge') }}:</span>
                            <span
                                class="font-black text-sm {{ $hasActivePlan ? 'text-indigo-700 dark:text-indigo-300' : 'text-amber-700 dark:text-amber-300' }}">{{ $packageName }}</span>
                        </div>
                        @if(!empty($currentModuleObjects))
                            <div class="pt-2 border-t border-indigo-100/80 dark:border-indigo-900/60">
                                <div
                                    class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                                    <span>ماژول‌های فعال کنونی سامانه:</span>
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ reseller_fa_num(count($currentModuleObjects)) }} ماژول</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($currentModuleObjects as $cMod)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/60 shadow-2xs">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>{{ $cMod['title'] }}</span>
                                                </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(!empty($availableUpgradePackages) && $availableUpgradePackages->isNotEmpty())
                        <form method="POST" action="{{ route('tenant.subscription.request') }}"
                              @submit.prevent="sendUpgradeRequest($event)" class="space-y-5">
                            @csrf
                            <input type="hidden" name="package_id" :value="selectedPackageId">
                            <input type="hidden" name="duration_months" :value="selectedDurationMonths">
                            <input type="hidden" name="requested_quota"
                                   :value="selectedPackageName ? ('ارتقا به پکیج ' + selectedPackageName) : 'ارتقای کامل پلن'">

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2.5">
                                    {{ $hasNoPackage ? 'انتخاب پکیج مورد نظر:' : 'انتخاب پکیج مقصد جهت ارتقا:' }}
                                </label>
                                <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                    @foreach($availableUpgradePackages as $upPkg)
                                        @php
                                            $hasDiscount = $upPkg->has_active_discount ?? ($upPkg->isSubscription() ? ($upPkg->hasActiveDiscountForMonths(1) || $upPkg->hasActiveDiscountForMonths(12)) : $upPkg->hasActiveDiscount());
                                            $rawMonthly = (int)$upPkg->monthly_price;
                                            $finalMonthly = (int)($upPkg->final_monthly_price ?? $rawMonthly);
                                            $rawYearly = (int)$upPkg->yearly_price;
                                            $finalYearly = (int)($upPkg->final_yearly_price ?? $rawYearly);
                                            $pkgIcon = $upPkg->icon ?: 'cube';
                                            $pkgColorHex = $upPkg->color_hex ?: '#4f46e5';
                                        @endphp
                                        <label @click="selectPackage({{ $upPkg->id }})"
                                               :class="selectedPackageId === {{ $upPkg->id }} ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/40 ring-1 ring-indigo-600' : 'border-gray-200/80 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'"
                                               class="p-4 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 block">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <input type="radio" name="pkg_radio"
                                                       :checked="selectedPackageId === {{ $upPkg->id }}"
                                                       class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 shrink-0">

                                                {{-- Package Distinctive Icon --}}
                                                <div
                                                    class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border border-black/5 dark:border-white/10 shadow-2xs"
                                                    style="background-color: {{ $pkgColorHex }}18; color: {{ $pkgColorHex }};">
                                                    @include('resellerhub::user.packages.partials.icon', ['icon' => $pkgIcon, 'class' => 'w-5 h-5'])
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                                <span
                                                                    class="text-xs font-black text-gray-900 dark:text-white">{{ $upPkg->name }}</span>
                                                        @if($hasDiscount)
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                                        <span
                                                                            class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                                        تخفیف ویژه
                                                                    </span>
                                                        @endif
                                                        @if(!empty($upPkg->pending_invoice))
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/80 shrink-0">
                                                                        <span
                                                                            class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                                        فاکتور در انتظار پرداخت
                                                                    </span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-gray-400 block mt-0.5">
                                                                شامل {{ reseller_fa_num(count($upPkg->modules ?? [])) }} ماژول
                                                                @if(!empty($upPkg->new_modules_count) && $upPkg->new_modules_count > 0)
                                                            <span
                                                                class="text-emerald-600 dark:text-emerald-400 font-bold">({{ reseller_fa_num($upPkg->new_modules_count) }} ماژول جدید)</span>
                                                        @endif
                                                            </span>
                                                </div>
                                            </div>

                                            <div
                                                class="text-left font-black text-xs text-gray-800 dark:text-gray-200 shrink-0">
                                                @if($upPkg->yearly_price > 0)
                                                    @if($hasDiscount && $finalYearly < $rawYearly)
                                                        <div
                                                            class="text-xs text-gray-400 line-through tabular-nums">{{ reseller_fa_num(number_format($rawYearly)) }}
                                                            تومان
                                                        </div>
                                                        <div
                                                            class="text-rose-600 dark:text-rose-400 font-black">
                                                            {{ reseller_fa_num(number_format($finalYearly)) }}
                                                            تومان
                                                            <span
                                                                class="text-xs text-gray-400 font-normal">/ سالانه</span>
                                                        </div>
                                                    @else
                                                        <span>{{ reseller_fa_num(number_format($rawYearly)) }} تومان <span
                                                                class="text-xs text-gray-400 font-normal">/ سالانه</span></span>
                                                    @endif
                                                @elseif($upPkg->monthly_price > 0)
                                                    @if($hasDiscount && $finalMonthly < $rawMonthly)
                                                        <div
                                                            class="text-xs text-gray-400 line-through tabular-nums">{{ reseller_fa_num(number_format($rawMonthly)) }}
                                                            تومان
                                                        </div>
                                                        <div
                                                            class="text-rose-600 dark:text-rose-400 font-black">
                                                            {{ reseller_fa_num(number_format($finalMonthly)) }}
                                                            تومان
                                                            <span
                                                                class="text-xs text-gray-400 font-normal">/ ماهانه</span>
                                                        </div>
                                                    @else
                                                        <span>{{ reseller_fa_num(number_format($rawMonthly)) }} تومان <span
                                                                class="text-xs text-gray-400 font-normal">/ ماهانه</span></span>
                                                    @endif
                                                @endif
                                                @if($hasDiscount && $upPkg->discount_expires_at)
                                                    <div
                                                        class="text-[9px] text-amber-600 dark:text-amber-400 font-medium mt-0.5">
                                                        مهلت: تا {{ $upPkg->discount_expires_at_jalali }}
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Dynamic Duration Selection for Selected Package --}}
                            <template
                                x-if="selectedPackage && selectedPackage.available_periods && selectedPackage.available_periods.length > 0">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        انتخاب دوره اشتراک ارتقا:
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <template x-for="p in selectedPackage.available_periods"
                                                  :key="p.months">
                                            <button type="button"
                                                    @click="selectedDurationMonths = p.months"
                                                    :class="selectedDurationMonths === p.months ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                                    class="p-2.5 rounded-xl border text-center transition cursor-pointer flex flex-col items-center justify-center gap-1">
                                                <span class="text-xs font-black" x-text="p.label"></span>
                                                <template x-if="p.has_discount && p.raw_price_formatted">
                                                            <span
                                                                class="text-xs text-gray-400 line-through tabular-nums"
                                                                x-text="p.raw_price_formatted"></span>
                                                </template>
                                                <span
                                                    class="text-xs font-bold text-gray-900 dark:text-white tabular-nums"
                                                    x-text="(p.price_formatted || p.final_price || 0) + ' تومان'"></span>
                                                <template x-if="p.has_discount">
                                                            <span
                                                                class="text-[9px] text-rose-600 dark:text-rose-400 font-bold">تخفیف‌دار</span>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Dynamic Comparison & Diff Preview --}}
                            <template x-if="selectedPackage">
                                <div
                                    class="rounded-2xl p-4 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/70 space-y-3.5 text-xs">

                                    {{-- Pending Invoice Notice (When an unpaid invoice already exists for this package) --}}
                                    <template x-if="selectedPackage.pending_invoice">
                                        <div
                                            class="p-3.5 rounded-xl bg-amber-50/90 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
                                            <div class="flex items-start gap-2.5">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <strong
                                                            class="text-xs font-black text-amber-900 dark:text-amber-200">فاکتور
                                                            در انتظار پرداخت دارید</strong>
                                                        <span
                                                            class="text-xs font-bold text-amber-700 dark:text-amber-300 dir-ltr"
                                                            x-text="'#' + selectedPackage.pending_invoice.invoice_number"></span>
                                                    </div>
                                                    <p class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5">
                                                        برای این پکیج یک فاکتور فعال با مبلغ <strong
                                                            class="font-bold text-amber-950 dark:text-white"
                                                            x-text="selectedPackage.pending_invoice.total_formatted"></strong>
                                                        صادر شده و نیاز به صدور مجدد نیست.
                                                    </p>
                                                </div>
                                            </div>

                                            <button type="button"
                                                    @click="openPaymentModalForInvoice(selectedPackage.pending_invoice.id, selectedPackage.pending_invoice.invoice_number, selectedPackage.name, selectedPackage.pending_invoice.total, selectedPackage.pending_invoice.total_formatted)"
                                                    class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5 shrink-0 active:scale-95 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                <span>پرداخت همین فاکتور</span>
                                            </button>
                                        </div>
                                    </template>

                                    {{-- Active Discount Notice in Diff Preview --}}
                                    <template x-if="selectedPackage && selectedPackage.has_active_discount">
                                        <div
                                            class="p-3.5 rounded-xl bg-rose-50/90 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 flex items-center justify-between shadow-2xs">
                                            <div class="flex items-center gap-2">
                                                        <span
                                                            class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                                <span
                                                    class="text-xs font-black text-rose-900 dark:text-rose-200">
                                                            این پکیج دارای تخفیف ویژه دوره است!
                                                        </span>
                                            </div>
                                            <template x-if="selectedPackage.discount_expires_at_jalali">
                                                        <span
                                                            class="text-xs text-amber-700 dark:text-amber-300 font-bold"
                                                            x-text="'مهلت تخفیف: تا ' + selectedPackage.discount_expires_at_jalali"></span>
                                            </template>
                                        </div>
                                    </template>

                                    <div
                                        class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                                            <span
                                                class="font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>تفاوت امکانات نسبت به پکیج فعلی شما</span>
                                            </span>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 border border-black/5 dark:border-white/10"
                                                :style="'background-color: ' + (selectedPackage.color_hex || '#4f46e5') + '20; color: ' + (selectedPackage.color_hex || '#4f46e5')"
                                                x-html="renderPkgIcon(selectedPackage.icon, 'w-3.5 h-3.5')">
                                            </div>
                                            <span
                                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                                x-text="selectedPackage.name"></span>
                                        </div>
                                    </div>

                                    {{-- New Modules List --}}
                                    <div>
                                        <div
                                            class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                                            <span>ماژول‌های جدید اضافه شونده:</span>
                                            <span
                                                class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                                                x-text="(selectedPackage.new_modules ? selectedPackage.new_modules.length : 0) + ' ماژول جدید'"></span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5"
                                             x-show="selectedPackage.new_modules && selectedPackage.new_modules.length > 0">
                                            <template x-for="mod in selectedPackage.new_modules"
                                                      :key="mod.slug">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400"
                                                             fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                  clip-rule="evenodd"/>
                                                        </svg>
                                                        <span x-text="mod.title"></span>
                                                    </span>
                                            </template>
                                        </div>
                                        <div
                                            x-show="!selectedPackage.new_modules || selectedPackage.new_modules.length === 0"
                                            class="text-xs text-gray-400">
                                            تمامی ماژول‌های این پکیج با پلن فعلی یکسان بوده و صرفاً سقف سهمیه‌ها
                                            افزایش می‌یابد.
                                        </div>
                                    </div>

                                    {{-- All Modules of Target Package --}}
                                    <template
                                        x-if="selectedPackage && selectedPackage.all_modules && selectedPackage.all_modules.length > 0">
                                        <div class="pt-2 border-t border-gray-200/50 dark:border-gray-700/50">
                                            <div
                                                class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                                                <span>تمام ماژول‌های این پکیج:</span>
                                                <span
                                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                                    x-text="(selectedPackage.all_modules ? selectedPackage.all_modules.length : 0) + ' ماژول'"></span>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="am in selectedPackage.all_modules"
                                                          :key="am.slug">
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700/60">
                                                                <span class="w-1 h-1 rounded-full bg-indigo-500"></span>
                                                                <span x-text="am.title"></span>
                                                            </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Quota Increases --}}
                                    <template
                                        x-if="selectedPackage.quota_diffs && selectedPackage.quota_diffs.length > 0">
                                        <div
                                            class="pt-2 border-t border-gray-200/50 dark:border-gray-700/50 space-y-1.5">
                                            <div class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                                افزایش سقف سهمیه‌ها:
                                            </div>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                <template x-for="q in selectedPackage.quota_diffs" :key="q.key">
                                                    <div
                                                        class="text-xs text-gray-700 dark:text-gray-300 flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                                        <span x-text="q.title"></span>
                                                        <span
                                                            class="font-bold text-emerald-600 dark:text-emerald-400"
                                                            x-text="q.is_unlimited ? 'نامحدود ∞' : (q.to)"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Explicit Enterprise Notice --}}
                                    <div
                                        class="p-2.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60 flex items-start gap-2 text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
                                        <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>
                                                با تأیید شما، <strong>فاکتور ارتقای پکیج</strong> صادر شده و پنجره انتخاب درگاه پرداخت باز می‌شود. تا قبل از تسویه فاکتور هیچ تغییری روی سیستم اعمال نمی‌شود و بلافاصله پس از پرداخت موفقیت‌آمیز، سفارش فعال شده و ماژول‌های جدید در سایدبار دسترس‌پذیر خواهند شد.
                                            </span>
                                    </div>
                                </div>
                            </template>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">یادداشت
                                    تکمیلی شما (اختیاری):</label>
                                <textarea name="message" x-model="upgradeMessage" rows="2"
                                          placeholder="در صورت داشتن هرگونه توضیحات تکمیلی پیرامون فاکتور ارتقا بنویسید..."
                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"></textarea>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" @click="upgradeModal = false" :disabled="submitting"
                                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer disabled:opacity-50 transition-all">
                                    انصراف
                                </button>
                                <button type="submit" :disabled="submitting || !selectedPackageId"
                                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 cursor-pointer disabled:opacity-50 inline-flex items-center gap-2 transition-all">
                                    <template x-if="submitting">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none"
                                             viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <span
                                        x-text="submitting ? 'در حال ارتباط...' : (selectedPackage && selectedPackage.pending_invoice ? 'اتصال به درگاه و پرداخت فاکتور' : ('{{ $hasNoPackage ? 'تأیید و صدور فاکتور انتخاب پکیج' : 'تأیید و صدور فاکتور ارتقا' }}'))"></span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="py-6 text-center space-y-3">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center mx-auto">
                                @if($hasNoPackage)
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                                    </svg>
                                @endif
                            </div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white">
                                {{ $hasNoPackage ? 'پکیجی برای انتخاب یافت نشد' : 'شما از بالاترین پکیج موجود استفاده می‌کنید' }}
                            </h4>
                            <p class="text-xs text-gray-400 leading-relaxed max-w-sm mx-auto">
                                {{ $hasNoPackage ? 'در حال حاضر پکیج فعالی توسط نماینده تعریف نشده است. لطفاً با پشتیبانی تماس حاصل فرمایید.' : 'در حال حاضر پکیج با سطح امکانات بالاتری برای ارتقا تعریف نشده است. جهت سفارش پلن‌های سازمانی اختصاصی با پشتیبانی نماینده تماس بگیرید.' }}
                            </p>
                            <div class="pt-3">
                                <button type="button" @click="upgradeModal = false"
                                        class="px-6 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold text-gray-700 dark:text-gray-200 transition-all">
                                    متوجه شدم
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</template>
<template x-teleport="body">
    <div x-show="pendingInvoiceModal" x-cloak class="relative z-[9999]"
         aria-labelledby="pending-invoice-modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div x-show="pendingInvoiceModal"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9998]"
             @click="pendingInvoiceModal = false"></div>

        {{-- Modal Dialog --}}
        <div class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
            <div x-show="pendingInvoiceModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.outside="pendingInvoiceModal = false"
                 class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden relative"
                 dir="rtl">

                {{-- Top Amber Accent Line --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600"></div>

                {{-- Modal Body --}}
                <div class="p-6 sm:p-7 space-y-5">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200/80 dark:border-amber-800/60 shadow-2xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>

                        <div class="space-y-1 min-w-0 flex-1">
                            <h3 id="pending-invoice-modal-title"
                                class="text-base font-black text-gray-900 dark:text-white">
                                فاکتور در انتظار پرداخت موجود است
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                برای ارتقا به پکیج <strong class="font-bold text-gray-900 dark:text-white"
                                                           x-text="pendingInvoiceData ? ('«' + pendingInvoiceData.package_name + '»') : 'انتخابی'"></strong>
                                از قبل یک فاکتور صادر شده و در وضعیت منتظر پرداخت قرار دارد. سیستم اجازه صدور
                                فاکتور
                                جدید تکراری نمی‌دهد.
                            </p>
                        </div>
                    </div>

                    {{-- Invoice Details Card --}}
                    <template x-if="pendingInvoiceData">
                        <div
                            class="rounded-2xl p-4 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/70 space-y-3 text-xs">
                            <div
                                class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">شماره فاکتور:</span>
                                <span class="font-black text-gray-900 dark:text-white dir-ltr tabular-nums"
                                      x-text="'#' + pendingInvoiceData.invoice_number"></span>
                            </div>

                            <div
                                class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                                        <span
                                            class="text-gray-500 dark:text-gray-400 font-medium">مبلغ قابل پرداخت:</span>
                                <span
                                    class="font-black text-emerald-600 dark:text-emerald-400 text-sm tabular-nums"
                                    x-text="pendingInvoiceData.total_formatted || (pendingInvoiceData.total + ' تومان')"></span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">وضعیت فعلی:</span>
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        در انتظار پرداخت آنلاین
                                    </span>
                            </div>
                        </div>
                    </template>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-2">
                        <button type="button"
                                @click="pendingInvoiceModal = false"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-all">
                            انصراف و بازگشت
                        </button>

                        <button type="button"
                                @click="const pInv = pendingInvoiceData; pendingInvoiceModal = false; if (pInv) confirmCancelInvoice(pInv.id, pInv.invoice_number);"
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-800 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 cursor-pointer transition-all inline-flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>لغو این فاکتور باز</span>
                        </button>

                        <button type="button"
                                @click="proceedToPayPendingInvoice()"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 cursor-pointer inline-flex items-center justify-center gap-2 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>پرداخت آنلاین همین فاکتور</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Cancel Invoice Confirmation Modal --}}
<template x-teleport="body">
    <div x-show="cancelInvoiceModal" x-cloak class="relative z-[9999]"
         aria-labelledby="cancel-invoice-modal-title" role="dialog" aria-modal="true">
        <div x-show="cancelInvoiceModal"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9998]"
             @click="cancelInvoiceModal = false"></div>

        <div class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
            <div x-show="cancelInvoiceModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.outside="cancelInvoiceModal = false"
                 class="w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden relative"
                 dir="rtl">

                <div class="p-6 sm:p-7 space-y-5">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-200/80 dark:border-red-800/60 shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>

                        <div class="space-y-1.5 min-w-0 flex-1">
                            <h3 id="cancel-invoice-modal-title"
                                class="text-base font-black text-gray-900 dark:text-white leading-tight">
                                {{ __('resellerhub::messages.invoices.cancel_modal_title') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                آیا از لغو فاکتور شماره <strong
                                    class="font-black text-red-600 dark:text-red-400 dir-ltr tabular-nums inline-block px-1.5 py-0.5 rounded-md bg-red-50 dark:bg-red-950/40"
                                    x-text="'#' + cancellingInvoiceNumber"></strong> اطمینان دارید؟
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 leading-normal">
                                {{ __('resellerhub::messages.invoices.cancel_modal_desc') }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button"
                                @click="cancelInvoiceModal = false"
                                :disabled="cancelLoading"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer disabled:opacity-50 transition-all">
                            {{ __('resellerhub::messages.invoices.cancel_modal_dismiss') }}
                        </button>

                        <button type="button"
                                @click="submitCancelInvoice()"
                                :disabled="cancelLoading"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 cursor-pointer disabled:opacity-50 inline-flex items-center justify-center gap-2 transition-all">
                            <template x-if="cancelLoading">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span>{{ __('resellerhub::messages.invoices.cancel_modal_confirm') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
