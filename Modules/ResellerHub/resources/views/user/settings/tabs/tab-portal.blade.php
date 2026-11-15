@php
    $portalHeroEnabled = $settings['portal_hero_enabled'] ?? true;
    $portalHeroBadge = $settings['portal_hero_badge'] ?? 'تعرفه‌ها و پکیج‌های رسمی سامانه';
    $portalHeroTitle = $settings['portal_hero_title'] ?? 'سرمایه‌گذاری روی رشد کسب‌وکار، با تعرفه‌های شفاف';
    $portalHeroSubtitle = $settings['portal_hero_subtitle'] ?? 'شفاف، بدون هزینه‌های پنهان، با قابلیت ارتقاء و تغییر دوره در هر زمان.';
    $portalHeroTrustBadges = $settings['portal_hero_trust_badges'] ?? [
        'بدون هزینه‌های پنهان',
        'فعال‌سازی آنی پس از پرداخت',
        'امکان تغییر دوره یا ارتقاء پکیج',
    ];
    if (is_string($portalHeroTrustBadges)) {
        $portalHeroTrustBadges = json_decode($portalHeroTrustBadges, true) ?: [];
    }

    $portalDefaultPeriod = $settings['portal_default_period'] ?? 'monthly_price';
    $portalAllowedPeriods = $settings['portal_allowed_periods'] ?? array_keys($availablePeriods ?? []);
    if (is_string($portalAllowedPeriods)) {
        $portalAllowedPeriods = json_decode($portalAllowedPeriods, true) ?: [];
    }
    if (empty($portalAllowedPeriods) && !empty($availablePeriods)) {
        $portalAllowedPeriods = array_keys($availablePeriods);
    }

    $portalGridColumns = (string)($settings['portal_grid_columns'] ?? '3');
    $portalPackageSort = $settings['portal_package_sort'] ?? 'features_desc';
    $portalFeaturedPackageId = $settings['portal_featured_package_id'] ?? null;
    $portalShowDailyPrice = $settings['portal_show_daily_price'] ?? true;
    $portalShowSetupFee = $settings['portal_show_setup_fee'] ?? true;
    $portalCtaText = $settings['portal_cta_text'] ?? 'انتخاب و شروع';
    $portalEnableFilters = $settings['portal_enable_filters'] ?? true;
    $portalEnableModuleFilter = $settings['portal_enable_module_filter'] ?? true;
    $portalComparisonMode = $settings['portal_comparison_mode'] ?? 'collapsible';
    $portalFaqEnabled = $settings['portal_faq_enabled'] ?? true;
    $portalFaqItems = $settings['portal_faq_items'] ?? [
        [
            'q' => 'آیا امکان تغییر دوره یا ارتقاء پکیج پس از خرید وجود دارد؟',
            'a' => 'بله، در هر لحظه از پنل کاربری می‌توانید پکیج خود را ارتقاء داده یا دوره اشتراک را تمدید فرمایید.',
        ],
        [
            'q' => 'راه‌اندازی سامانه چقدر زمان می‌برد؟',
            'a' => 'فرآیند ایجاد پایگاه داده و پیکربندی اولیه به صورت ۱۰۰٪ خودکار و آنی بلافاصله پس از پرداخت انجام می‌شود.',
        ],
        [
            'q' => 'آیا امکان اتصال دامنه اختصاصی وجود دارد؟',
            'a' => 'بله، علاوه بر آدرس رایگان ساب‌دامین، می‌توانید دامنه اختصاصی شرکت خود را به سامانه متصل فرمایید.',
        ],
    ];
    if (is_string($portalFaqItems)) {
        $portalFaqItems = json_decode($portalFaqItems, true) ?: [];
    }

    $portalCheckoutFastTrack = !empty($settings['portal_checkout_fast_track']);
@endphp

<div role="tabpanel"
     id="tabpanel-portal"
     aria-labelledby="tab-portal"
     x-show="activeTab === 'portal'"
     x-cloak
     x-data="{
        heroEnabled: {{ $portalHeroEnabled ? 'true' : 'false' }},
        heroBadge: {{ \Illuminate\Support\Js::from($portalHeroBadge) }},
        heroTitle: {{ \Illuminate\Support\Js::from($portalHeroTitle) }},
        heroSubtitle: {{ \Illuminate\Support\Js::from($portalHeroSubtitle) }},
        trustBadges: {{ \Illuminate\Support\Js::from($portalHeroTrustBadges) }},
        newTrustBadge: '',
        addTrustBadge() {
            const val = (this.newTrustBadge || '').trim();
            if (val && !this.trustBadges.includes(val)) {
                this.trustBadges.push(val);
                this.newTrustBadge = '';
            }
        },
        removeTrustBadge(idx) {
            this.trustBadges.splice(idx, 1);
        },

        gridColumns: '{{ $portalGridColumns }}',
        packageSort: '{{ $portalPackageSort }}',
        featuredPackageId: '{{ $portalFeaturedPackageId ?? '' }}',
        showDailyPrice: {{ $portalShowDailyPrice ? 'true' : 'false' }},
        showSetupFee: {{ $portalShowSetupFee ? 'true' : 'false' }},
        ctaText: {{ \Illuminate\Support\Js::from($portalCtaText) }},

        enableFilters: {{ $portalEnableFilters ? 'true' : 'false' }},
        enableModuleFilter: {{ $portalEnableModuleFilter ? 'true' : 'false' }},
        defaultPeriod: '{{ $portalDefaultPeriod }}',
        allowedPeriods: {{ \Illuminate\Support\Js::from($portalAllowedPeriods) }},
        togglePeriod(pKey) {
            if (this.allowedPeriods.includes(pKey)) {
                if (this.allowedPeriods.length > 1) {
                    this.allowedPeriods = this.allowedPeriods.filter(k => k !== pKey);
                    if (this.defaultPeriod === pKey && this.allowedPeriods.length > 0) {
                        this.defaultPeriod = this.allowedPeriods[0];
                    }
                }
            } else {
                this.allowedPeriods.push(pKey);
            }
        },

        comparisonMode: '{{ $portalComparisonMode }}',
        faqEnabled: {{ $portalFaqEnabled ? 'true' : 'false' }},
        faqItems: {{ \Illuminate\Support\Js::from($portalFaqItems) }},
        addFaqItem() {
            this.faqItems.push({ q: '', a: '' });
        },
        removeFaqItem(idx) {
            this.faqItems.splice(idx, 1);
        },

        checkoutFastTrack: {{ $portalCheckoutFastTrack ? 'true' : 'false' }}
     }"
     class="space-y-6 pb-24">

    {{-- Hidden form fields for sync --}}
    <input type="hidden" name="portal_hero_enabled" :value="heroEnabled ? '1' : '0'">
    <input type="hidden" name="portal_hero_trust_badges" :value="JSON.stringify(trustBadges)">
    <input type="hidden" name="portal_allowed_periods" :value="JSON.stringify(allowedPeriods)">
    <input type="hidden" name="portal_grid_columns" :value="gridColumns">
    <input type="hidden" name="portal_package_sort" :value="packageSort">
    <input type="hidden" name="portal_show_daily_price" :value="showDailyPrice ? '1' : '0'">
    <input type="hidden" name="portal_show_setup_fee" :value="showSetupFee ? '1' : '0'">
    <input type="hidden" name="portal_enable_filters" :value="enableFilters ? '1' : '0'">
    <input type="hidden" name="portal_enable_module_filter" :value="enableModuleFilter ? '1' : '0'">
    <input type="hidden" name="portal_comparison_mode" :value="comparisonMode">
    <input type="hidden" name="portal_faq_enabled" :value="faqEnabled ? '1' : '0'">
    <input type="hidden" name="portal_faq_items" :value="JSON.stringify(faqItems)">
    <input type="hidden" name="portal_checkout_fast_track" :value="checkoutFastTrack ? '1' : '0'">

    {{-- Quick Preview & Action Banner --}}
    <x-resellerhub::card class="p-4 sm:p-5 bg-gradient-to-r from-indigo-50/80 via-white to-purple-50/60 dark:from-indigo-950/30 dark:via-gray-900 dark:to-purple-950/20 border border-indigo-100 dark:border-indigo-900/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white truncate">
                        مدیریت پرتال عمومی و صفحه تعرفه‌ها
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        شخصی‌سازی کامل چیدمان، فیلترها، نحوه نمایش پکیج‌ها و مراحل خرید برای کاربران نهایی
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('resellerhub.pricing') }}"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition-all shadow-2xs hover:shadow-xs">
                    <span>مشاهده صفحه تعرفه‌ها</span>
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- SECTION 1: Hero Header & Value Props --}}
    <x-resellerhub::card class="p-4 sm:p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 rounded-xl shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">۱. هدر معرفی و مزایای کلیدی</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تیتر، بج تبلیغاتی و نشانگرهای اعتماد بالای صفحه تعرفه‌ها</p>
                </div>
            </div>

            <label class="flex items-center gap-2 px-3 py-1.5 rounded-xl border cursor-pointer select-none transition-all text-xs font-bold"
                   :class="heroEnabled ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="heroEnabled" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                <span>نمایش بنر هدر</span>
            </label>
        </div>

        <div x-show="heroEnabled" x-transition.opacity class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <div>
                    <x-resellerhub::label>نشان/بج بالای عنوان</x-resellerhub::label>
                    <input type="text"
                           name="portal_hero_badge"
                           x-model="heroBadge"
                           placeholder="مثال: تعرفه‌ها و پکیج‌های رسمی سامانه"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500">
                </div>

                <div class="md:col-span-2">
                    <x-resellerhub::label>تیتر اصلی صفحه تعرفه‌ها</x-resellerhub::label>
                    <input type="text"
                           name="portal_hero_title"
                           x-model="heroTitle"
                           placeholder="مثال: سرمایه‌گذاری روی رشد کسب‌وکار، با تعرفه‌های شفاف"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500">
                </div>
            </div>

            <div>
                <x-resellerhub::label>متن توضیحات زیر عنوان</x-resellerhub::label>
                <textarea name="portal_hero_subtitle"
                          x-model="heroSubtitle"
                          rows="2"
                          placeholder="توضیح کوتاه درباره شفافیت قیمت‌ها و پشتیبانی..."
                          class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500"></textarea>
            </div>

            {{-- Trust Badges Repeater --}}
            <div class="pt-2">
                <x-resellerhub::label>مزایای کلیدی تیک‌دار زیر هدر (Trust Badges)</x-resellerhub::label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    این موارد با آیکون تیک سبز زیر توضیحات هدر نمایش داده می‌شوند تا اعتماد خریدار جلب شود.
                </p>

                <div class="flex flex-wrap gap-2 items-center mb-3">
                    <template x-for="(badge, bIdx) in trustBadges" :key="bIdx">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="badge"></span>
                            <button type="button"
                                    @click="removeTrustBadge(bIdx)"
                                    class="text-emerald-600 hover:text-rose-600 dark:text-emerald-400 dark:hover:text-rose-400 transition-colors cursor-pointer ms-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    </template>
                </div>

                <div class="flex items-center gap-2 max-w-md">
                    <input type="text"
                           x-model="newTrustBadge"
                           @keydown.enter.prevent="addTrustBadge()"
                           placeholder="افزودن مزیت جدید (مثال: پشتیبانی ۲۴ ساعته)"
                           class="flex-1 rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 transition-all dark:text-white dark:placeholder-gray-500">
                    <button type="button"
                            @click="addTrustBadge()"
                            class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-xs shrink-0 cursor-pointer">
                        افزودن
                    </button>
                </div>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- SECTION 2: Package Cards Grid & Sorting --}}
    <x-resellerhub::card class="p-4 sm:p-6 md:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 rounded-xl shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">۲. چیدمان و نحوه نمایش کارت‌های پکیج</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعداد ستون‌ها، ترتیب مرتب‌سازی پکیج‌ها و هایلایت پکیج برگزیده</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Columns Count --}}
            <div>
                <x-resellerhub::label>تعداد ستون‌ها در دسکتاپ</x-resellerhub::label>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <button type="button"
                            @click="gridColumns = '2'"
                            :class="gridColumns === '2' ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200'"
                            class="py-2.5 px-3 rounded-xl text-xs transition-all text-center cursor-pointer">
                        ۲ ستونه
                    </button>
                    <button type="button"
                            @click="gridColumns = '3'"
                            :class="gridColumns === '3' ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200'"
                            class="py-2.5 px-3 rounded-xl text-xs transition-all text-center cursor-pointer">
                        ۳ ستونه (استاندارد)
                    </button>
                    <button type="button"
                            @click="gridColumns = '4'"
                            :class="gridColumns === '4' ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200'"
                            class="py-2.5 px-3 rounded-xl text-xs transition-all text-center cursor-pointer">
                        ۴ ستونه (فشرده)
                    </button>
                </div>
            </div>

            {{-- Sorting Rule --}}
            <div>
                <x-resellerhub::label>نحوه مرتب‌سازی پکیج‌ها</x-resellerhub::label>
                <select name="portal_package_sort"
                        x-model="packageSort"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white">
                    <option value="features_desc">بیشترین امکانات به کمترین (چیدمان هرمی پیوسته)</option>
                    <option value="price_asc">قیمت صعودی (ارزان‌ترین به گران‌ترین)</option>
                    <option value="price_desc">قیمت نزولی (پیشرفته‌ترین به ارزان‌ترین)</option>
                    <option value="sort_order">ترتیب دستی (Sort Order در ویرایش پکیج‌ها)</option>
                </select>
            </div>

            {{-- Featured / Highlighted Package --}}
            <div>
                <x-resellerhub::label>پکیج برگزیده و پیشنهادی (Featured Badge)</x-resellerhub::label>
                <select name="portal_featured_package_id"
                        x-model="featuredPackageId"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white">
                    <option value="">بدون پکیج برگزیده (ساده)</option>
                    @foreach($resellerPackages as $pkg)
                        <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                    این پکیج با بج «پیشنهاد ما» و کادر برجسته رنگ سازمانی مشخص می‌شود.
                </p>
            </div>
        </div>

        {{-- Card Elements Toggles & CTA Text --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2 border-t border-gray-100 dark:border-gray-700/60">
            <div>
                <x-resellerhub::label>متن دکمه اقدام به خرید (CTA)</x-resellerhub::label>
                <input type="text"
                       name="portal_cta_text"
                       x-model="ctaText"
                       placeholder="مثال: انتخاب و شروع"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white">
            </div>

            <div class="flex items-center">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40 cursor-pointer w-full">
                    <input type="checkbox" x-model="showDailyPrice" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                    <div>
                        <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block">نمایش معادل هزینه روزانه</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">باکس معادل روزانه جهت کاهش مقاومت ذهنی خریدار</span>
                    </div>
                </label>
            </div>

            <div class="flex items-center">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40 cursor-pointer w-full">
                    <input type="checkbox" x-model="showSetupFee" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                    <div>
                        <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block">نمایش هزینه اولیه راه‌اندازی</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">نمایش Setup Fee در صورت وجود برای پکیج‌ها</span>
                    </div>
                </label>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- SECTION 3: Billing Periods & Filter Controls --}}
    <x-resellerhub::card class="p-4 sm:p-6 md:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 rounded-xl shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">۳. دوره‌های پرداخت و ابزارهای فیلتر</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">تعیین دوره‌های قابل مشاهده، دوره پیش‌فرض و فیلترهای تعاملی مشتری</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Default Period --}}
            <div>
                <x-resellerhub::label>دوره پرداخت پیش‌فرض در بدو ورود مشتری</x-resellerhub::label>
                <select name="portal_default_period"
                        x-model="defaultPeriod"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-medium text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white">
                    @foreach($availablePeriods ?? [] as $pKey => $pData)
                        <option value="{{ $pKey }}">{{ $pData['label'] ?? $pKey }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Toggles --}}
            <div class="space-y-3">
                <x-resellerhub::label>ابزارهای تعاملی فیلتر و جستجو</x-resellerhub::label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40 cursor-pointer">
                        <input type="checkbox" x-model="enableFilters" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                        <span class="text-xs font-bold text-gray-900 dark:text-white">جستجو و مرتب‌سازی زنده</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40 cursor-pointer">
                        <input type="checkbox" x-model="enableModuleFilter" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                        <span class="text-xs font-bold text-gray-900 dark:text-white">فیلتر بر اساس ماژول‌ها</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Allowed Periods Checkboxes --}}
        <div>
            <x-resellerhub::label>دوره‌های اشتراک مجاز برای نمایش در تب‌های صفحه تعرفه‌ها</x-resellerhub::label>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">دوره‌هایی که تیک نخورند در سوییچر بالای تعرفه‌ها به خریدار عمومی نمایش داده نخواهند شد.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                @foreach($availablePeriods ?? [] as $pKey => $pData)
                    <button type="button"
                            @click="togglePeriod('{{ $pKey }}')"
                            :class="allowedPeriods.includes('{{ $pKey }}') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 text-gray-400 dark:bg-gray-800/50 dark:text-gray-500 border-gray-200 dark:border-gray-700'"
                            class="flex items-center justify-between p-2.5 rounded-xl border text-xs font-bold transition-all cursor-pointer">
                        <span>{{ $pData['label'] ?? $pKey }}</span>
                        <template x-if="allowedPeriods.includes('{{ $pKey }}')">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                    </button>
                @endforeach
            </div>
        </div>
    </x-resellerhub::card>

    {{-- SECTION 4: Comparison Matrix Settings --}}
    <x-resellerhub::card class="p-4 sm:p-6 md:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 rounded-xl shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">۴. ماتریس مقایسه امکانات پکیج‌ها</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">وضعیت نمایش جدول ریز دسترسی‌ها و سهمیه‌های ماژول‌ها</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                   :class="comparisonMode === 'collapsible' ? 'bg-indigo-50/70 border-indigo-200 dark:bg-indigo-950/40 dark:border-indigo-800' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-800/40 dark:border-gray-700'">
                <input type="radio" name="portal_comparison_mode_radio" value="collapsible" x-model="comparisonMode" class="w-4 h-4 text-indigo-600 mt-0.5 cursor-pointer">
                <div>
                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block">آکاردئونی جمع‌شونده (پیش‌فرض)</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">جدول بسته است و خریدار با دکمه می‌تواند آن را باز کند</span>
                </div>
            </label>

            <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                   :class="comparisonMode === 'expanded' ? 'bg-indigo-50/70 border-indigo-200 dark:bg-indigo-950/40 dark:border-indigo-800' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-800/40 dark:border-gray-700'">
                <input type="radio" name="portal_comparison_mode_radio" value="expanded" x-model="comparisonMode" class="w-4 h-4 text-indigo-600 mt-0.5 cursor-pointer">
                <div>
                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block">همیشه باز (Expanded)</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">جدول مقایسه از ابتدا به صورت کامل زیر کارت‌ها لود می‌شود</span>
                </div>
            </label>

            <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                   :class="comparisonMode === 'disabled' ? 'bg-indigo-50/70 border-indigo-200 dark:bg-indigo-950/40 dark:border-indigo-800' : 'bg-gray-50/50 border-gray-200 dark:bg-gray-800/40 dark:border-gray-700'">
                <input type="radio" name="portal_comparison_mode_radio" value="disabled" x-model="comparisonMode" class="w-4 h-4 text-indigo-600 mt-0.5 cursor-pointer">
                <div>
                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block">غیرفعال (مخفی)</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">عدم نمایش بخش مقایسه پکیج‌ها در صفحه عمومی</span>
                </div>
            </label>
        </div>
    </x-resellerhub::card>

    {{-- SECTION 5: FAQ & Questions Repeater --}}
    <x-resellerhub::card class="p-4 sm:p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 rounded-xl shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">۵. سوالات متداول (FAQ)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پاسخ به سوالات پرتکرار خریداران در انتهای صفحه تعرفه‌ها</p>
                </div>
            </div>

            <label class="flex items-center gap-2 px-3 py-1.5 rounded-xl border cursor-pointer select-none transition-all text-xs font-bold"
                   :class="faqEnabled ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700'">
                <input type="checkbox" x-model="faqEnabled" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                <span>نمایش بخش سوالات متداول</span>
            </label>
        </div>

        <div x-show="faqEnabled" x-transition.opacity class="space-y-4">
            <template x-for="(item, idx) in faqItems" :key="idx">
                <div class="p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-850 space-y-3 relative">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                            <span>پرسش و پاسخ</span>
                        </span>
                        <button type="button"
                                @click="removeFaqItem(idx)"
                                class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors cursor-pointer"
                                title="حذف این پرسش">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                    <div>
                        <input type="text"
                               x-model="item.q"
                               placeholder="عنوان سوال (مثال: آیا پس از خرید، فاکتور رسمی صادر می‌شود؟)"
                               class="w-full rounded-xl border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700 px-3 py-2 text-xs font-bold text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 dark:text-white">
                    </div>

                    <div>
                        <textarea x-model="item.a"
                                  rows="2"
                                  placeholder="پاسخ کامل سوال..."
                                  class="w-full rounded-xl border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700 px-3 py-2 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 dark:text-white"></textarea>
                    </div>
                </div>
            </template>

            <button type="button"
                    @click="addFaqItem()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-dashed border-indigo-300 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 text-xs font-bold transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>افزودن سوال جدید</span>
            </button>
        </div>
    </x-resellerhub::card>

    {{-- SECTION 6: Checkout Flow & Onboarding Speed --}}
    <x-resellerhub::card class="p-4 sm:p-6 md:p-8 space-y-6">
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 rounded-xl shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">۶. فرآیند چک‌اوت و ثبت‌نام سریع</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">بهینه‌سازی نرخ تبدیل و سرعت ایجاد خودکار سامانه مشتری</p>
            </div>
        </div>

        <div>
            <label class="flex items-start gap-3.5 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40 cursor-pointer">
                <input type="checkbox" x-model="checkoutFastTrack" class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer mt-0.5">
                <div>
                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block">حالت ثبت‌نام و چک‌اوت سریع (Fast-Track)</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mt-1 leading-relaxed">
                        با فعال‌سازی این گزینه، مراحل اختیاری برندینگ از جریان خرید حذف شده و کاربر بلافاصله پس از انتخاب نام سامانه به درگاه هدایت می‌شود و پس از پرداخت مستقیماً وارد پنل آماده خود خواهد شد.
                    </span>
                </div>
            </label>
        </div>
    </x-resellerhub::card>

</div>
