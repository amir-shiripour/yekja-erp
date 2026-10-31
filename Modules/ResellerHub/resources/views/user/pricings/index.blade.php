@extends('layouts.user')

@section('title', 'تعرفه و قیمت‌گذاری ماژول‌ها و امکانات')

@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Modules\ResellerHub\App\Enums\BillingCycle;

    $currency = $currency ?? ($settings['currency'] ?? ResellerSetting::getCurrencyForReseller($reseller->id ?? null));
    $currencyLabel = $currencyLabel ?? ResellerSetting::getCurrencyLabelForReseller($reseller->id ?? null);
@endphp

@section('content')
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.4);
            border-radius: 9999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.7);
        }
    </style>

    @include('resellerhub::user.packages.partials.icons-script')

    <script>
        window.pricingCatalogData = {
            catalog: @json($catalog),
            saveUrl: "{{ route('reseller.pricings.store') }}",
            toggleItemizedUrl: "{{ route('reseller.pricings.toggle-itemized') }}",
            enableModuleItemizedPricing: {{ $enableModuleItemizedPricing ? 'true' : 'false' }},
            csrfToken: '{{ csrf_token() }}'
        };

        function pricingManager() {
            const config = window.pricingCatalogData;
            const uiHelpers = window.resellerUiHelpers ? window.resellerUiHelpers() : {};
            return {
                ...uiHelpers,
                toast: {show: false, message: '', type: 'success'},
                pricingModules: config.catalog || [],
                pricingSearch: '',
                pricingFilter: 'all', // 'all', 'active', 'inactive', 'unpriced'
                pricingFilters: [
                    {key: 'all', label: 'همه'},
                    {key: 'active', label: 'فعال'},
                    {key: 'inactive', label: 'غیرفعال'},
                    {key: 'unpriced', label: 'بدون تعرفه'},
                ],
                selectedPricingSlug: (config.catalog && config.catalog.length > 0) ? config.catalog[0].slug : null,
                savingPricings: false,
                enableModuleItemizedPricing: Boolean(config.enableModuleItemizedPricing),
                togglingItemized: false,

                init() {
                    this.$watch('pricingSearch', () => this.ensureSelectionVisible());
                },

                async toggleItemizedPricing() {
                    this.togglingItemized = true;
                    try {
                        const res = await fetch(config.toggleItemizedUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify({
                                enable_module_itemized_pricing: this.enableModuleItemizedPricing ? 1 : 0
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showToast(data.message, 'success');
                        } else {
                            this.showToast(data.message || 'خطا در ثبت تنظیمات.', 'error');
                        }
                    } catch (e) {
                        this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                    } finally {
                        this.togglingItemized = false;
                    }
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },

                selectedPricingModule() {
                    if (!this.pricingModules || this.pricingModules.length === 0) return null;
                    return this.pricingModules.find(m => m.slug === this.selectedPricingSlug) || this.pricingModules[0] || null;
                },

                selectPricingModule(slug) {
                    this.selectedPricingSlug = slug;
                },

                formatPriceInput(v) {
                    if (v === '' || v === null || v === undefined) return '';
                    const clean = this.parsePriceInput ? this.parsePriceInput(v) : (parseInt(String(v).replace(/[^0-9]/g, '')) || 0);
                    if (!clean) return '';
                    return this.faNum(clean.toLocaleString('en-US'));
                },

                calcPeriodPrices(monthly) {
                    const m = parseInt(monthly || 0);
                    if (!m) return {quarterly: 0, semiAnnual: 0, yearly: 0};
                    return {
                        quarterly: m * 3,
                        semiAnnual: Math.round(m * 6 * 0.95),
                        yearly: Math.round(m * 12 * 0.85),
                    };
                },

                // --- Filters ---
                hasModulePrice(m) {
                    return ['base_monthly_price', 'base_quarterly_price', 'base_semi_annual_price', 'base_yearly_price', 'base_setup_fee']
                        .some(k => (parseInt(m[k] || 0) || 0) > 0);
                },

                matchesFilter(m, key) {
                    switch (key) {
                        case 'active':
                            return Boolean(m.is_active);
                        case 'inactive':
                            return !m.is_active;
                        case 'unpriced':
                            return !this.hasModulePrice(m);
                        default:
                            return true;
                    }
                },

                filterCount(key) {
                    return (this.pricingModules || []).filter(m => this.matchesFilter(m, key)).length;
                },

                setPricingFilter(key) {
                    this.pricingFilter = key;
                    this.ensureSelectionVisible();
                },

                resetPricingFilters() {
                    this.pricingSearch = '';
                    this.pricingFilter = 'all';
                },

                ensureSelectionVisible() {
                    const list = this.filteredPricingModules();
                    if (list.length > 0 && !list.some(m => m.slug === this.selectedPricingSlug)) {
                        this.selectedPricingSlug = list[0].slug;
                    }
                },

                filteredPricingModules() {
                    let list = (this.pricingModules || []).filter(m => this.matchesFilter(m, this.pricingFilter));
                    if (!this.pricingSearch.trim()) return list;
                    const s = this.pricingSearch.trim().toLowerCase();
                    return list.filter(m => {
                        if ((m.title || '').toLowerCase().includes(s) || (m.slug || '').toLowerCase().includes(s)) {
                            return true;
                        }
                        if (m.features && m.features.some(f => (f.title || '').toLowerCase().includes(s) || (f.key || '').toLowerCase().includes(s))) {
                            return true;
                        }
                        return false;
                    });
                },

                autoCalculateBasePeriods(m) {
                    const monthly = parseInt(m.base_monthly_price || 0);
                    if (!monthly) {
                        this.showToast('لطفاً ابتدا مبلغ ماهانه را وارد فرمایید.', 'error');
                        return;
                    }
                    const p = this.calcPeriodPrices(monthly);
                    m.base_quarterly_price = p.quarterly;
                    m.base_semi_annual_price = p.semiAnnual;
                    m.base_yearly_price = p.yearly;
                    this.showToast(`تعرفه‌های دوره‌ای ${m.title} به صورت خودکار محاسبه شدند.`, 'success');
                },

                autoCalculateFeaturePeriods(f) {
                    const monthly = parseInt(f.monthly_price || 0);
                    if (!monthly) {
                        this.showToast('لطفاً ابتدا مبلغ ماهانه قابلیت را وارد فرمایید.', 'error');
                        return;
                    }
                    const p = this.calcPeriodPrices(monthly);
                    f.quarterly_price = p.quarterly;
                    f.semi_annual_price = p.semiAnnual;
                    f.yearly_price = p.yearly;
                    this.showToast(`تعرفه‌های دوره‌ای ${f.title} به صورت خودکار محاسبه شدند.`, 'success');
                },

                autoCalculateAllFeatures(m) {
                    if (!m || !m.features || !m.features.length) return;
                    let count = 0;
                    m.features.forEach(f => {
                        const monthly = parseInt(f.monthly_price || 0);
                        if (monthly > 0) {
                            const p = this.calcPeriodPrices(monthly);
                            f.quarterly_price = p.quarterly;
                            f.semi_annual_price = p.semiAnnual;
                            f.yearly_price = p.yearly;
                            count++;
                        }
                    });
                    if (count > 0) {
                        this.showToast(`تعرفه‌های دوره‌ای ${this.faNum(count)} قابلیت به صورت خودکار محاسبه شدند.`, 'success');
                    } else {
                        this.showToast('لطفاً ابتدا مبلغ ماهانه قابلیت‌ها را وارد فرمایید.', 'error');
                    }
                },

                autoCalculateAllModules() {
                    let count = 0;
                    this.pricingModules.forEach(m => {
                        const monthly = parseInt(m.base_monthly_price || 0);
                        if (monthly > 0) {
                            const p = this.calcPeriodPrices(monthly);
                            m.base_quarterly_price = p.quarterly;
                            m.base_semi_annual_price = p.semiAnnual;
                            m.base_yearly_price = p.yearly;
                            count++;
                        }
                        if (m.features && m.features.length) {
                            m.features.forEach(f => {
                                const fMonthly = parseInt(f.monthly_price || 0);
                                if (fMonthly > 0) {
                                    const p = this.calcPeriodPrices(fMonthly);
                                    f.quarterly_price = p.quarterly;
                                    f.semi_annual_price = p.semiAnnual;
                                    f.yearly_price = p.yearly;
                                }
                            });
                        }
                    });
                    if (count > 0) {
                        this.showToast(`تعرفه‌های دوره‌ای ${this.faNum(count)} ماژول و امکانات آن‌ها محاسبه شدند.`, 'success');
                    } else {
                        this.showToast('لطفاً ابتدا مبلغ ماهانه ماژول‌ها را وارد فرمایید.', 'error');
                    }
                },

                toggleAllModuleFeatures(m, status) {
                    if (m.features && m.features.length) {
                        m.features.forEach(f => {
                            f.is_active = status;
                        });
                    }
                },

                async savePricings() {
                    this.savingPricings = true;
                    const payload = [];

                    this.pricingModules.forEach(m => {
                        payload.push({
                            module_slug: m.slug,
                            feature_key: null,
                            feature_title: m.title,
                            monthly_price: parseInt(m.base_monthly_price || 0),
                            quarterly_price: parseInt(m.base_quarterly_price || 0),
                            semi_annual_price: parseInt(m.base_semi_annual_price || 0),
                            yearly_price: parseInt(m.base_yearly_price || 0),
                            setup_fee: parseInt(m.base_setup_fee || 0),
                            is_active: m.is_active !== undefined ? Boolean(m.is_active) : true,
                        });

                        if (m.features && m.features.length) {
                            m.features.forEach(f => {
                                payload.push({
                                    module_slug: m.slug,
                                    feature_key: f.key,
                                    feature_title: f.title,
                                    monthly_price: parseInt(f.monthly_price || 0),
                                    quarterly_price: parseInt(f.quarterly_price || 0),
                                    semi_annual_price: parseInt(f.semi_annual_price || 0),
                                    yearly_price: parseInt(f.yearly_price || 0),
                                    setup_fee: parseInt(f.setup_fee || 0),
                                    is_active: f.is_active !== undefined ? Boolean(f.is_active) : true,
                                });
                            });
                        }
                    });

                    try {
                        const res = await fetch(config.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify({
                                pricings: payload,
                                enable_module_itemized_pricing: this.enableModuleItemizedPricing ? 1 : 0
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showToast(data.message || 'تعرفه‌ها با موفقیت ذخیره شدند.', 'success');
                        } else {
                            this.showToast(data.message || 'خطا در ذخیره تعرفه‌ها.', 'error');
                        }
                    } catch (e) {
                        this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                    } finally {
                        this.savingPricings = false;
                    }
                }
            };
        }
    </script>

    <div x-data="pricingManager()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        {{-- Breadcrumb Navigation --}}
        <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 mb-2" aria-label="Breadcrumb">
            <a href="{{ route('reseller.dashboard') }}" class="hover:text-indigo-600 transition">داشبورد نمایندگی</a>
            <span>/</span>
            <a href="{{ route('reseller.settings.index') }}" class="hover:text-indigo-600 transition">تنظیمات
                نمایندگی</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white">تعرفه ماژول‌ها و امکانات</span>
        </nav>

        {{-- Top Control Card (Exact 1:1 match with settings tab) --}}
        <x-resellerhub::card class="p-5 sm:p-7 space-y-5 w-full max-w-full min-w-0 overflow-hidden">
            <div
                class="flex flex-col xl:flex-row xl:items-center justify-between gap-5 pb-5 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-start gap-4">
                    <span
                        class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 shrink-0 font-black">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">تعرفه ماژول‌ها و
                                امکانات اشتراکی</h1>
                            <span
                                class="px-3 py-0.5 rounded-full text-xs font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50">
                                واحد پولی: {{ $currencyLabel }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 leading-relaxed">
                            مدیریت و تعیین نرخ دوره‌های مختلف (ماهانه، ۳ ماهه، ۶ ماهه، سالانه و راه‌اندازی) برای هر
                            ماژول CRM و قابلیت‌های تفکیک‌پذیر آن.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" @click="autoCalculateAllModules()"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:hover:bg-amber-900/50 dark:text-amber-400 text-xs font-bold border border-amber-200/60 dark:border-amber-800/50 transition cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>محاسبه خودکار کل ماژول‌ها</span>
                    </button>
                </div>
            </div>

            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                <span
                    x-text="faNum(pricingModules.filter(x => x.is_active).length) + ' ماژول فعال از مجموع ' + faNum(pricingModules.length) + ' ماژول سیستمی'"></span>
                <span class="text-xs text-gray-400">نکته: با تغییر مبلغ ماهانه، می‌توانید از دکمه «محاسبه خودکار» برای اعمال تخفیف‌های دوره‌ای استفاده نمایید.</span>
            </div>
        </x-resellerhub::card>

        {{-- INVOICE ITEMIZATION MODE TOGGLE CARD --}}
        <x-resellerhub::card class="p-4 sm:p-5 border transition-all duration-300"
                             x-bind:class="enableModuleItemizedPricing ? 'border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/20 dark:bg-indigo-950/20' : 'border-gray-100 dark:border-gray-700/60'">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                            قیمت‌گذاری مستقل و مجزای هر ماژول در اقلام فاکتور
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black transition-colors"
                              :class="enableModuleItemizedPricing ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300/60' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'"
                              x-text="enableModuleItemizedPricing ? 'فعال (قیمت‌گذاری مجزا)' : 'غیرفعال (پیش‌فرض پکیج)'"></span>
                        <span x-show="togglingItemized"
                              class="inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 font-bold"
                              style="display: none;">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle
                                    class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle><path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>در حال ذخیره...</span>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-4xl">
                        در حالت عادی (غیرفعال)، اقلام فاکتور بدون ستون‌های قیمت و تخفیف فهرست شده و تعرفه پکیج به صورت
                        کلی و شفاف در خلاصه مالی فاکتور محاسبه می‌گردد. با فعال‌کردن این چک‌باکس، ستون‌های «مبلغ واحد» و
                        «تخفیف» در جدول اقلام فاکتور باز شده و تعرفه‌های اختصاصی هر ماژول از این صفحه خوانده و جداگانه
                        محاسبه خواهند شد.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2.5 self-start sm:self-center">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 select-none cursor-pointer"
                          @click="if(!togglingItemized) { enableModuleItemizedPricing = !enableModuleItemizedPricing; toggleItemizedPricing(); }"
                          x-text="enableModuleItemizedPricing ? 'فعال' : 'غیرفعال'"></span>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox"
                               x-model="enableModuleItemizedPricing"
                               @change="toggleItemizedPricing()"
                               :disabled="togglingItemized"
                               class="sr-only">
                        <div class="w-8 h-4.5 rounded-full transition-colors duration-200"
                             :class="enableModuleItemizedPricing ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"></div>
                        <div
                            class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-white rounded-full shadow-xs transition-transform duration-200"
                            :class="enableModuleItemizedPricing ? '-translate-x-3.5' : 'translate-x-0'"></div>
                    </label>
                </div>
            </div>
        </x-resellerhub::card>

        {{-- Master-Detail Workbench (12-Column CSS Grid - mathematically constrained) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start w-full max-w-full min-w-0">
            {{-- SIDEBAR: Master Modules List (4 cols on lg, 3 cols on xl) --}}
            <x-resellerhub::card class="p-4 sm:p-5 space-y-4 lg:col-span-4 xl:col-span-3 w-full min-w-0">
                {{-- Search & Filter Bar --}}
                <div class="space-y-2.5">
                    <div class="relative">
                        <svg
                            class="w-4 h-4 absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="pricingSearch"
                               placeholder="جستجوی سریع ماژول یا قابلیت..."
                               class="w-full bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl pr-9 pl-7 py-2 text-xs font-medium text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        <button type="button" x-show="pricingSearch" @click="pricingSearch = ''"
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            ✕
                        </button>
                    </div>

                    {{-- Filter chips (با شمارنده زنده) --}}
                    <div
                        class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-gray-100 dark:bg-gray-900/60 text-xs font-bold">
                        <template x-for="f in pricingFilters" :key="f.key">
                            <button type="button" @click="setPricingFilter(f.key)"
                                    :class="pricingFilter === f.key ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                                    class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-lg transition cursor-pointer">
                                <span x-text="f.label"></span>
                                <span class="min-w-[18px] px-1 rounded-md text-xs tabular-nums text-center"
                                      :class="pricingFilter === f.key
                                          ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300'
                                          : (f.key === 'unpriced' && filterCount('unpriced') > 0
                                              ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300'
                                              : 'bg-gray-200/70 dark:bg-gray-700 text-gray-500 dark:text-gray-400')"
                                      x-text="faNum(filterCount(f.key))"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Empty Search in Sidebar --}}
                <template x-if="filteredPricingModules().length === 0">
                    <div class="p-6 text-center text-xs text-gray-400 space-y-2">
                        <p class="font-bold text-gray-600 dark:text-gray-300">ماژولی یافت نشد</p>
                        <p class="text-xs">عبارت جستجو یا فیلتر را تغییر دهید.</p>
                        <button type="button" @click="resetPricingFilters()"
                                class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 text-xs font-bold transition cursor-pointer">
                            نمایش همه ماژول‌ها
                        </button>
                    </div>
                </template>

                {{-- Module List Items --}}
                <div class="space-y-2 max-h-[640px] overflow-y-auto custom-scrollbar pe-1">
                    <template x-for="m in filteredPricingModules()" :key="m.slug">
                        <div @click="selectPricingModule(m.slug)"
                             class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 text-start group"
                             :class="selectedPricingSlug === m.slug
                                 ? 'bg-emerald-50/80 dark:bg-emerald-950/50 border-emerald-400 dark:border-emerald-600 ring-2 ring-emerald-500/20 shadow-xs'
                                 : 'bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/60 hover:bg-gray-50 dark:hover:bg-gray-750 hover:border-gray-300 dark:hover:border-gray-600'">

                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div
                                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 font-black text-xs transition-colors shadow-2xs"
                                    :class="selectedPricingSlug === m.slug
                                           ? 'bg-emerald-600 text-white shadow-emerald-500/30'
                                           : (m.is_active ? 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-400')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                         <span class="text-xs font-black truncate block"
                                               :class="selectedPricingSlug === m.slug ? 'text-emerald-900 dark:text-emerald-200' : 'text-gray-800 dark:text-gray-200'"
                                               x-text="m.title"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span class="text-xs px-1.5 py-0.2 rounded font-bold"
                                              :class="(m.features && m.features.length) ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : 'text-gray-400'"
                                              x-text="(m.features && m.features.length) ? (faNum(m.features.length) + ' قابلیت') : 'پایه'"></span>
                                        <span x-show="!hasModulePrice(m)"
                                              class="text-xs px-1.5 py-0.2 rounded font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300">بدون تعرفه</span>
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0" @click.stop="$event.stopPropagation()">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" x-model="m.is_active" class="sr-only">
                                    <div class="w-8 h-4.5 rounded-full transition-colors duration-200"
                                         :class="m.is_active ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                    <div
                                        class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-white rounded-full shadow-xs transition-transform duration-200"
                                        :class="m.is_active ? '-translate-x-3.5' : 'translate-x-0'"></div>
                                </label>
                            </div>
                        </div>
                    </template>
                </div>
            </x-resellerhub::card>

            {{-- DETAIL PANEL: Workbench for Selected Module (8 cols on lg, 9 cols on xl) --}}
            <div class="lg:col-span-8 xl:col-span-9 space-y-6 w-full min-w-0" style="min-width: 0; max-width: 100%;">
                <template x-if="selectedPricingModule()">
                    <div class="space-y-6 w-full max-w-full min-w-0">
                        {{-- Module Header & Overview Card --}}
                        <x-resellerhub::card class="p-5 sm:p-7 space-y-6 w-full max-w-full min-w-0 overflow-hidden">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-700/60">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-13 h-13 rounded-2xl flex items-center justify-center font-black shrink-0 shadow-lg text-white"
                                        :class="selectedPricingModule().is_active ? 'bg-gradient-to-br from-indigo-500 to-indigo-700 shadow-indigo-500/25' : 'bg-gray-400'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <h2 class="text-xl font-black text-gray-900 dark:text-white"
                                                x-text="selectedPricingModule().title"></h2>
                                            <span
                                                class="text-xs px-2.5 py-0.5 rounded-full font-bold dir-ltr bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                                x-text="selectedPricingModule().slug"></span>
                                            <template x-if="selectedPricingModule().is_allowed">
                                                <span
                                                    class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60">مجاز به فروش</span>
                                            </template>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400"
                                           x-text="selectedPricingModule().description || 'تنظیم تعرفه‌های لایسنس پایه و امکانات تفکیک‌پذیر این ماژول'"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <button type="button"
                                            @click="autoCalculateBasePeriods(selectedPricingModule())"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:hover:bg-amber-900/50 dark:text-amber-400 text-xs font-bold border border-amber-200/60 dark:border-amber-800/50 transition cursor-pointer shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span>محاسبه خودکار دوره‌ها</span>
                                    </button>
                                </div>
                            </div>

                            {{-- SECTION 1: BASE PERIOD PRICING (4-COL SPACIOUS GRID) --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        تعرفه‌های اشتراک پایه ماژول:
                                    </h3>
                                    <span class="text-xs text-gray-400">واحد مبالغ: <strong
                                            class="text-gray-700 dark:text-gray-300">{{ $currencyLabel }}</strong></span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                                    {{-- 1 Month --}}
                                    <div
                                        class="p-4 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/40 dark:bg-gray-900/30 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ BillingCycle::MONTHLY->label(true) }}</span>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-md bg-gray-200/70 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold">پایه</span>
                                        </div>
                                        <div
                                            class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500 shadow-2xs transition">
                                            <input type="text"
                                                   :value="formatPriceInput(selectedPricingModule().base_monthly_price)"
                                                   @input="let val = parsePriceInput($event.target.value); selectedPricingModule().base_monthly_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                   placeholder="۰" dir="ltr"
                                                   class="w-full text-sm font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                            <span
                                                class="px-2.5 py-2 text-xs font-bold text-gray-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 select-none">{{ $currencyLabel }}</span>
                                        </div>
                                    </div>

                                    {{-- 3 Months --}}
                                    <div
                                        class="p-4 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/40 dark:bg-gray-900/30 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ BillingCycle::QUARTERLY->label(true) }}</span>
                                        </div>
                                        <div
                                            class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500 shadow-2xs transition">
                                            <input type="text"
                                                   :value="formatPriceInput(selectedPricingModule().base_quarterly_price)"
                                                   @input="let val = parsePriceInput($event.target.value); selectedPricingModule().base_quarterly_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                   placeholder="۰" dir="ltr"
                                                   class="w-full text-sm font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                            <span
                                                class="px-2.5 py-2 text-xs font-bold text-gray-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 select-none">{{ $currencyLabel }}</span>
                                        </div>
                                    </div>

                                    {{-- 6 Months --}}
                                    <div
                                        class="p-4 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/40 dark:bg-gray-900/30 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ BillingCycle::SEMI_ANNUAL->label(true) }}</span>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-bold">۵٪ تخفیف</span>
                                        </div>
                                        <div
                                            class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500 shadow-2xs transition">
                                            <input type="text"
                                                   :value="formatPriceInput(selectedPricingModule().base_semi_annual_price)"
                                                   @input="let val = parsePriceInput($event.target.value); selectedPricingModule().base_semi_annual_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                   placeholder="۰" dir="ltr"
                                                   class="w-full text-sm font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                            <span
                                                class="px-2.5 py-2 text-xs font-bold text-gray-400 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 select-none">{{ $currencyLabel }}</span>
                                        </div>
                                    </div>

                                    {{-- 1 Year --}}
                                    <div
                                        class="p-4 rounded-2xl border-2 border-emerald-400 dark:border-emerald-600 bg-emerald-50/20 dark:bg-emerald-950/20 space-y-2 ring-2 ring-emerald-500/10">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-xs font-black text-emerald-800 dark:text-emerald-300">{{ BillingCycle::ANNUAL->label(true) }}</span>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-md bg-emerald-600 text-white font-bold shadow-xs">۱۵٪ تخفیف (اصلی)</span>
                                        </div>
                                        <div
                                            class="flex items-center rounded-xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500 shadow-2xs transition">
                                            <input type="text"
                                                   :value="formatPriceInput(selectedPricingModule().base_yearly_price)"
                                                   @input="let val = parsePriceInput($event.target.value); selectedPricingModule().base_yearly_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                   placeholder="۰" dir="ltr"
                                                   class="w-full text-sm font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                            <span
                                                class="px-2.5 py-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 border-r border-emerald-200 select-none">{{ $currencyLabel }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Setup Fee --}}
                                <div
                                    class="p-4 rounded-2xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50/30 dark:bg-amber-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <span class="text-xs font-bold text-amber-900 dark:text-amber-200 block">هزینه راه‌اندازی و کانفیگ اولیه (یک‌بار در فاکتور اول)</span>
                                        <span class="text-xs text-amber-700/80 dark:text-amber-400 block mt-0.5">این مبلغ فقط یک‌بار در فاکتور اول لحاظ شده و در دوره‌های تمدید بعدی منظور نخواهد شد.</span>
                                    </div>
                                    <div
                                        class="flex items-center rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-amber-500 w-full sm:w-64 shrink-0 shadow-2xs">
                                        <input type="text"
                                               :value="formatPriceInput(selectedPricingModule().base_setup_fee)"
                                               @input="let val = parsePriceInput($event.target.value); selectedPricingModule().base_setup_fee = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                               placeholder="۰" dir="ltr"
                                               class="w-full text-sm font-black py-2.5 px-3 border-0 bg-transparent text-left tabular-nums focus:ring-0 text-gray-900 dark:text-white">
                                        <span
                                            class="px-3 py-2 text-xs font-bold text-amber-700 bg-amber-50 dark:bg-amber-950 border-r border-amber-200 select-none">{{ $currencyLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-resellerhub::card>

                        {{-- SECTION 2: CAPABILITIES & FEATURES TABLE --}}
                        <x-resellerhub::card class="p-5 sm:p-7 space-y-5 w-full max-w-full min-w-0 overflow-hidden">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                                <div>
                                    <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        امکانات و دسترسی‌های تفکیکی این ماژول
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تعیین هزینه مستقل برای هر
                                        قابلیت زیرمجموعه این ماژول در صورت نیاز به فروش اختصاصی</p>
                                </div>

                                <template
                                    x-if="selectedPricingModule().features && selectedPricingModule().features.length > 0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button"
                                                @click="autoCalculateAllFeatures(selectedPricingModule())"
                                                class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 text-xs font-bold border border-amber-200/60 transition cursor-pointer">
                                            محاسبه خودکار کل دوره‌ها
                                        </button>
                                        <button type="button"
                                                @click="toggleAllModuleFeatures(selectedPricingModule(), true)"
                                                class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 text-xs font-bold transition cursor-pointer">
                                            فعال‌سازی همه
                                        </button>
                                        <button type="button"
                                                @click="toggleAllModuleFeatures(selectedPricingModule(), false)"
                                                class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                                            غیرفعال
                                        </button>
                                    </div>
                                </template>
                            </div>

                            {{-- Empty State when no features --}}
                            <template
                                x-if="!selectedPricingModule().features || selectedPricingModule().features.length === 0">
                                <div
                                    class="p-8 text-center space-y-2 bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">این ماژول فاقد قابلیت
                                        تفکیک‌پذیر است</p>
                                    <p class="text-xs text-gray-400">تمامی امکانات این ماژول تحت لایسنس پایه دوره
                                        فعال و واگذار می‌گردند.</p>
                                </div>
                            </template>

                            {{-- Full-Width Roomy Features Table --}}
                            <template
                                x-if="selectedPricingModule().features && selectedPricingModule().features.length > 0">
                                <div
                                    class="w-full max-w-full overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-2xs custom-scrollbar">
                                    <table class="w-full text-start text-xs border-collapse" style="min-width: 680px;">
                                        <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs font-bold">
                                            <th scope="col" class="py-3 px-3 text-start min-w-[140px]">عنوان و شناسه
                                                قابلیت
                                            </th>
                                            <th scope="col" class="py-3 px-2 text-center w-14 shrink-0">وضعیت</th>
                                            <th scope="col"
                                                class="py-3 px-2 text-center w-28 sm:w-32">{{ BillingCycle::MONTHLY->label() }}</th>
                                            <th scope="col"
                                                class="py-3 px-2 text-center w-28 sm:w-32">{{ BillingCycle::QUARTERLY->label() }}</th>
                                            <th scope="col"
                                                class="py-3 px-2 text-center w-28 sm:w-32">{{ BillingCycle::SEMI_ANNUAL->label() }}
                                                (۵٪-)
                                            </th>
                                            <th scope="col"
                                                class="py-3 px-2 text-center w-28 sm:w-32">{{ BillingCycle::ANNUAL->label() }}
                                                (۱۵٪-)
                                            </th>
                                            <th scope="col" class="py-3 px-2 text-center w-28 sm:w-30">راه‌اندازی</th>
                                            <th scope="col" class="py-3 px-2 text-center w-10 shrink-0"></th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <template x-for="f in selectedPricingModule().features" :key="f.key">
                                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/30 transition-colors">
                                                {{-- Title & Key --}}
                                                <td class="py-3.5 px-3 min-w-[140px]">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span
                                                            class="font-black text-gray-900 dark:text-white block text-xs"
                                                            x-text="f.title"></span>
                                                        <template x-if="f.type === 'quota'">
                                                            <span
                                                                class="text-[9px] px-1.5 py-0.5 rounded font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/60">سهمیه عددی</span>
                                                        </template>
                                                        <template x-if="f.type === 'feature'">
                                                            <span
                                                                class="text-[9px] px-1.5 py-0.5 rounded font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/60">قابلیت</span>
                                                        </template>
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-400 font-medium dir-ltr block text-start mt-0.5"
                                                        x-text="f.key"></span>
                                                </td>

                                                {{-- Toggle Switch --}}
                                                <td class="py-3.5 px-2 text-center w-14 shrink-0">
                                                    <label
                                                        class="relative inline-flex items-center cursor-pointer select-none">
                                                        <input type="checkbox" x-model="f.is_active" class="sr-only">
                                                        <div
                                                            class="w-8 h-4.5 rounded-full transition-colors duration-200"
                                                            :class="f.is_active ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                                        <div
                                                            class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-white rounded-full shadow-xs transition-transform duration-200"
                                                            :class="f.is_active ? '-translate-x-3.5' : 'translate-x-0'"></div>
                                                    </label>
                                                </td>

                                                {{-- Monthly --}}
                                                <td class="py-3 px-1.5 w-28 sm:w-32">
                                                    <input type="text"
                                                           :value="formatPriceInput(f.monthly_price)"
                                                           @input="let val = parsePriceInput($event.target.value); f.monthly_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                           placeholder="۰" dir="ltr"
                                                           class="w-full text-xs font-bold py-2 px-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-left tabular-nums focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-900 dark:text-white transition">
                                                </td>

                                                {{-- Quarterly --}}
                                                <td class="py-3 px-1.5 w-28 sm:w-32">
                                                    <input type="text"
                                                           :value="formatPriceInput(f.quarterly_price)"
                                                           @input="let val = parsePriceInput($event.target.value); f.quarterly_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                           placeholder="۰" dir="ltr"
                                                           class="w-full text-xs font-bold py-2 px-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-left tabular-nums focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-900 dark:text-white transition">
                                                </td>

                                                {{-- Semi-Annual --}}
                                                <td class="py-3 px-1.5 w-28 sm:w-32">
                                                    <input type="text"
                                                           :value="formatPriceInput(f.semi_annual_price)"
                                                           @input="let val = parsePriceInput($event.target.value); f.semi_annual_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                           placeholder="۰" dir="ltr"
                                                           class="w-full text-xs font-bold py-2 px-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-left tabular-nums focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-900 dark:text-white transition">
                                                </td>

                                                {{-- Yearly --}}
                                                <td class="py-3 px-1.5 w-28 sm:w-32">
                                                    <input type="text"
                                                           :value="formatPriceInput(f.yearly_price)"
                                                           @input="let val = parsePriceInput($event.target.value); f.yearly_price = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                           placeholder="۰" dir="ltr"
                                                           class="w-full text-xs font-bold py-2 px-2 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-emerald-50/20 text-left tabular-nums focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-900 dark:text-white transition">
                                                </td>

                                                {{-- Setup Fee --}}
                                                <td class="py-3 px-1.5 w-28 sm:w-30">
                                                    <input type="text"
                                                           :value="formatPriceInput(f.setup_fee)"
                                                           @input="let val = parsePriceInput($event.target.value); f.setup_fee = val; $event.target.value = val ? formatPriceInput(val) : '';"
                                                           placeholder="۰" dir="ltr"
                                                           class="w-full text-xs font-bold py-2 px-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-left tabular-nums focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-gray-900 dark:text-white transition">
                                                </td>

                                                {{-- Row Action --}}
                                                <td class="py-3.5 px-2 text-center w-10 shrink-0">
                                                    <button type="button"
                                                            @click="autoCalculateFeaturePeriods(f)"
                                                            title="محاسبه خودکار دوره‌ها بر اساس قیمت ماهانه"
                                                            class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 transition cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </x-resellerhub::card>
                    </div>
                </template>
            </div>
        </div>

        {{-- Sticky Bottom Floating Action Bar --}}
        <div class="sticky bottom-4 z-30">
            <x-resellerhub::card
                class="p-4 shadow-xl border border-gray-200/80 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300">
                        تغییرات شما تا زمان کلیک بر روی دکمه ذخیره، به صورت موقت در مرورگر نگهداری می‌شوند.
                    </span>
                </div>
                <div class="flex items-center gap-3 justify-end">
                    <a href="{{ route('reseller.settings.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        بازگشت به تنظیمات
                    </a>
                    <button type="button" @click="savePricings()" :disabled="savingPricings"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-lg shadow-emerald-500/25 transition-all active:scale-95 cursor-pointer disabled:opacity-50">
                        <svg x-show="!savingPricings" class="w-4 h-4" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="savingPricings" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                             style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="savingPricings ? 'در حال ذخیره‌سازی...' : 'ذخیره کل تغییرات تعرفه‌ها'"></span>
                    </button>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- Toast Partial --}}
        @include('resellerhub::partials.toast')
    </div>
@endsection
