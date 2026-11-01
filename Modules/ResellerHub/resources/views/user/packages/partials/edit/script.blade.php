<script>
    window.packageEditConfig = {
        allModules: @json($availableModules),
        catalog: @json($catalog),
        currencyLabel: '{{ $currencyLabel }}',
        oldModules: @json(old('modules', $package->modules ?? [])),
        oldFeatures: @json(old('features', $package->features ?? [])),
        oldPricing: {
            price_type: @json(old('price_type', $package->price_type ?? $defaultPriceType)),
            monthly_price: {{ (int)old('monthly_price', $package->monthly_price ?? 0) }},
            quarterly_price: {{ (int)old('quarterly_price', $package->quarterly_price ?? 0) }},
            semi_annual_price: {{ (int)old('semi_annual_price', $package->semi_annual_price ?? 0) }},
            yearly_price: {{ (int)old('yearly_price', $package->yearly_price ?? 0) }},
            one_time_price: {{ (int)old('one_time_price', $package->one_time_price ?? 0) }},
            setup_fee: {{ (int)old('setup_fee', $package->setup_fee ?? 0) }},
            discount_type: @json(old('discount_type', $package->discount_type ?? $defaultDiscountType)),
            discount_value: {{ (float)old('discount_value', $package->discount_value ?? 0) }},
            discount_starts_at: @json(old('discount_starts_at', $package->discount_starts_at_jalali ?? '')),
            discount_expires_at: @json(old('discount_expires_at', $package->discount_expires_at_jalali ?? ''))
        },
        oldActivePeriods: @json(old('active_periods', $defaultActivePeriods)),
        oldPeriodPrices: @json(old('period_prices', $package->period_prices ?? [])),
        oldPeriodDiscounts: @json($formattedPeriodDiscounts),
        oldLimits: @json(old('limits', $package->limits ?? []))
    };

    function packageEditManager(config) {
        config = config || window.packageEditConfig || {};

        const rawActive = Array.isArray(config.oldActivePeriods) && config.oldActivePeriods.length
            ? config.oldActivePeriods
            : [];

        const standardPresetMonths = (window.resellerDurationOptions || []).map(o => o.months);

        const initialMonths = [];
        rawActive.forEach(item => {
            const m = (window.resolvePeriodMonth ? window.resolvePeriodMonth(item) : null) || (parseInt(item, 10) || null);
            if (m && m >= 1 && m <= 1200 && !initialMonths.includes(m)) {
                initialMonths.push(m);
            }
        });

        // Also check keys in oldPeriodPrices
        if (config.oldPeriodPrices && typeof config.oldPeriodPrices === 'object') {
            Object.keys(config.oldPeriodPrices).forEach(k => {
                const m = (window.resolvePeriodMonth ? window.resolvePeriodMonth(k) : null) || (parseInt(k, 10) || null);
                if (m && m >= 1 && m <= 1200 && !initialMonths.includes(m)) {
                    initialMonths.push(m);
                }
            });
        }
        initialMonths.sort((a, b) => a - b);

        const initialPrices = (config.oldPeriodPrices && typeof config.oldPeriodPrices === 'object')
            ? {...config.oldPeriodPrices}
            : {};

        if (!initialPrices['1'] && config.oldPricing.monthly_price) initialPrices['1'] = config.oldPricing.monthly_price;
        if (!initialPrices['3'] && config.oldPricing.quarterly_price) initialPrices['3'] = config.oldPricing.quarterly_price;
        if (!initialPrices['6'] && config.oldPricing.semi_annual_price) initialPrices['6'] = config.oldPricing.semi_annual_price;
        if (!initialPrices['12'] && config.oldPricing.yearly_price) initialPrices['12'] = config.oldPricing.yearly_price;

        return {
            allModules: config.allModules || [],
            catalog: config.catalog || [],
            selectedModules: Array.isArray(config.oldModules) ? [...config.oldModules] : [],
            selectedFeatures: Array.isArray(config.oldFeatures) ? [...config.oldFeatures] : [],
            moduleLimits: config.oldLimits && typeof config.oldLimits === 'object' ? {...config.oldLimits} : {},
            expandedModules: [],

            selectedColor: @json(old('color', $package->color_hex ?? $defaultColor)),
            selectedIcon: @json(old('icon', $package->icon ?? $defaultIcon)),
            packageName: @json(old('name', $package->name ?? '')),
            isActive: @json((bool)old('is_active', $package->isActive())),
            submitting: false,

            standardPresetMonths: standardPresetMonths,
            allPeriodOptions: window.resellerDurationOptions || [],

            // --- Period Rows State (Starts empty, added via button) ---
            periodRows: initialMonths.map(m => ({
                id: Math.random(),
                months: Number(m),
                isCustom: !standardPresetMonths.includes(Number(m))
            })),

            pricing: {
                price_type: config.oldPricing.price_type || '{{ $defaultPriceType }}', // subscription | one_time | free
                period_prices: initialPrices,
                monthly_price: parseInt(config.oldPricing.monthly_price || 0),
                quarterly_price: parseInt(config.oldPricing.quarterly_price || 0),
                semi_annual_price: parseInt(config.oldPricing.semi_annual_price || 0),
                yearly_price: parseInt(config.oldPricing.yearly_price || 0),
                one_time_price: parseInt(config.oldPricing.one_time_price || 0),
                setup_fee: parseInt(config.oldPricing.setup_fee || 0),
                discount_type: '{{ $defaultDiscountType }}', // purely amount (Toman)
                discount_value: parseFloat(config.oldPricing.discount_value || 0),
                discount_starts_at: config.oldPricing.discount_starts_at || '',
                discount_expires_at: config.oldPricing.discount_expires_at || '',
                period_discounts: (config.oldPeriodDiscounts && typeof config.oldPeriodDiscounts === 'object') ? {...config.oldPeriodDiscounts} : {}
            },

            get isFreePackage() {
                if (this.pricing.price_type === 'one_time') {
                    return !(parseInt(this.pricing.one_time_price) > 0);
                }
                if (this.periodRows.length === 0) return true;
                return !this.periodRows.some(row => (parseInt(this.pricing.period_prices[row.months]) || 0) > 0);
            },

            availableColors: window.resellerPackageColors || [],

            availableIcons: window.industryIcons || [],

            init() {
                this.$nextTick(() => {
                    if (window.jalaliDatepicker) {
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp]',
                            minDate: 'attr'
                        });
                    }
                });
            },

            ...(window.resellerUiHelpers ? window.resellerUiHelpers() : {}),

            setColor(val) {
                this.selectedColor = val;
            },

            getSelectedIconLabel() {
                const found = this.availableIcons.find(i => i.key === this.selectedIcon);
                return found ? found.label : 'پکیج عمومی';
            },

            formatPriceNumber(num) {
                if (!num && num !== 0) return '';
                return this.formatAmount ? this.formatAmount(num) : num;
            },

            parseCleanNumber(val) {
                return this.parsePriceInput ? this.parsePriceInput(val) : (parseInt(val, 10) || 0);
            },

            isModuleAllowed(slug) {
                const found = this.allModules.find(m => m.slug === slug);
                return Boolean(found && found.is_allowed);
            },

            isModuleSelected(slug) {
                return this.selectedModules.includes(slug);
            },

            toggleModule(slug) {
                const idx = this.selectedModules.indexOf(slug);
                if (idx > -1) {
                    this.selectedModules.splice(idx, 1);
                } else {
                    this.selectedModules.push(slug);
                    if (this.getSelectedModuleFeaturesCount(slug) === 0) {
                        this.selectAllModuleFeatures(slug);
                    }
                }
            },

            selectAllAllowed() {
                this.selectedModules = this.allModules.filter(m => m.is_allowed).map(m => m.slug);
                this.selectedModules.forEach(slug => {
                    this.selectAllModuleFeatures(slug);
                });
            },

            deselectAll() {
                this.selectedModules = [];
                this.selectedFeatures = [];
            },

            toggleModuleAccordion(slug) {
                const idx = this.expandedModules.indexOf(slug);
                if (idx > -1) {
                    this.expandedModules.splice(idx, 1);
                } else {
                    this.expandedModules.push(slug);
                }
            },

            isModuleExpanded(slug) {
                return this.expandedModules.includes(slug);
            },

            getModuleFeatures(slug) {
                const found = this.catalog.find(c => c.slug === slug);
                return (found && found.features) ? found.features : [];
            },

            getModuleBooleanFeatures(slug) {
                return this.getModuleFeatures(slug).filter(f => f.type === 'feature');
            },

            getModuleQuotas(slug) {
                return this.getModuleFeatures(slug).filter(f => f.type === 'quota');
            },

            getModuleFeaturesCount(slug) {
                return this.getModuleBooleanFeatures(slug).length;
            },

            getSelectedModuleFeaturesCount(slug) {
                const featKeys = this.getModuleBooleanFeatures(slug).map(f => f.key);
                return this.selectedFeatures.filter(k => featKeys.includes(k)).length;
            },

            getModuleSummaryLabel(slug) {
                const boolTotal = this.getModuleBooleanFeatures(slug).length;
                const quotaTotal = this.getModuleQuotas(slug).length;
                if (boolTotal === 0 && quotaTotal === 0) {
                    return 'بدون قابلیت تفکیکی';
                }
                const parts = [];
                if (boolTotal > 0) {
                    parts.push(this.faNum(this.getSelectedModuleFeaturesCount(slug)) + ' از ' + this.faNum(boolTotal) + ' قابلیت فعال');
                }
                if (quotaTotal > 0) {
                    parts.push(this.faNum(quotaTotal) + ' سهمیه عددی');
                }
                return parts.join(' | ');
            },

            isFeatureSelected(featKey) {
                return this.selectedFeatures.includes(featKey);
            },

            toggleFeature(featKey, slug) {
                const idx = this.selectedFeatures.indexOf(featKey);
                if (idx > -1) {
                    this.selectedFeatures.splice(idx, 1);
                } else {
                    this.selectedFeatures.push(featKey);
                    if (!this.selectedModules.includes(slug)) {
                        this.selectedModules.push(slug);
                    }
                }
            },

            selectAllModuleFeatures(slug) {
                const featKeys = this.getModuleBooleanFeatures(slug).map(f => f.key);
                featKeys.forEach(k => {
                    if (!this.selectedFeatures.includes(k)) {
                        this.selectedFeatures.push(k);
                    }
                });
            },

            deselectAllModuleFeatures(slug) {
                const featKeys = this.getModuleBooleanFeatures(slug).map(f => f.key);
                this.selectedFeatures = this.selectedFeatures.filter(k => !featKeys.includes(k));
            },

            setPricingField(field, rawValue) {
                this.pricing[field] = this.parseCleanNumber(rawValue);
            },

            // --- Dynamic Month Period Row Helpers ---
            isMonthTakenElsewhere(month, currentId) {
                return this.periodRows.some(r => r.id !== currentId && Number(r.months) === Number(month));
            },

            isMonthActive(month) {
                return this.periodRows.some(r => Number(r.months) === Number(month));
            },

            addPeriodRow(customMonth = null) {
                if (this.periodRows.length >= 30) return;
                const usedMonths = this.periodRows.map(r => Number(r.months));
                let nextMonth = customMonth ? Number(customMonth) : 1;
                if (!customMonth) {
                    for (let i = 1; i <= 36; i++) {
                        if (!usedMonths.includes(i)) {
                            nextMonth = i;
                            break;
                        }
                    }
                }

                const baseMonthly = parseInt(this.getPeriodPrice(1) || this.pricing.monthly_price || 0);
                if (baseMonthly > 0 && !this.getPeriodPrice(nextMonth)) {
                    let calcPrice = baseMonthly * nextMonth;
                    this.setPeriodPrice(nextMonth, calcPrice);
                }

                const isCustom = !this.standardPresetMonths.includes(nextMonth);

                this.periodRows.push({
                    id: Date.now() + Math.random(),
                    months: nextMonth,
                    isCustom: isCustom
                });
                this.sortPeriodRows();
            },

            removePeriodRow(rowId) {
                this.periodRows = this.periodRows.filter(r => r.id !== rowId);
            },

            handleRowMonthChange(row) {
                row.months = Number(row.months);
                row.isCustom = !this.standardPresetMonths.includes(row.months);
                this.sortPeriodRows();
            },

            toggleCustomMode(row) {
                row.isCustom = !row.isCustom;
                if (!row.isCustom && !this.allPeriodOptions.some(o => o.months === row.months)) {
                    const nearest = this.allPeriodOptions.find(o => !this.isMonthTakenElsewhere(o.months, row.id));
                    if (nearest) {
                        row.months = nearest.months;
                        this.handleRowMonthChange(row);
                    }
                }
            },

            setRowCustomMonths(row, val) {
                let m = this.parseCleanNumber(val);
                if (!m || m < 1) m = 1;
                if (m > 1200) m = 1200;

                const oldMonth = row.months;
                if (oldMonth !== m) {
                    const oldPrice = this.getPeriodPrice(oldMonth);
                    if (oldPrice > 0 && !this.getPeriodPrice(m)) {
                        this.setPeriodPrice(m, oldPrice);
                    }
                }

                row.months = m;
                row.isCustom = true;
                this.sortPeriodRows();
            },

            getPeriodTitle(months) {
                return this.getDurationLabel ? this.getDurationLabel(months) : (months + ' ماهه');
            },

            sortPeriodRows() {
                this.periodRows.sort((a, b) => a.months - b.months);
            },

            selectCommonPeriods() {
                this.periodRows = [1, 3, 6, 12].map(m => ({id: Math.random(), months: m, isCustom: false}));
            },

            selectAllPeriods() {
                const rows = [];
                for (let m = 1; m <= 12; m++) {
                    rows.push({id: Math.random(), months: m, isCustom: false});
                }
                this.periodRows = rows;
            },

            clearAllPeriods() {
                this.periodRows = [];
            },

            resolvePeriodKey(months) {
                return window.resolvePeriodKey ? window.resolvePeriodKey(months) : ('period_' + months + '_price');
            },

            getPeriodPrice(months) {
                months = String(months);
                return this.pricing.period_prices[months] || 0;
            },

            setPeriodPrice(months, rawVal) {
                const num = this.parseCleanNumber(rawVal);
                this.pricing.period_prices[String(months)] = num;
                if (Number(months) === 1) this.pricing.monthly_price = num;
                if (Number(months) === 3) this.pricing.quarterly_price = num;
                if (Number(months) === 6) this.pricing.semi_annual_price = num;
                if (Number(months) === 12) this.pricing.yearly_price = num;
            },

            autoCalcPackagePeriods() {
                const baseMonthly = parseInt(this.getPeriodPrice(1) || this.pricing.monthly_price || 0);
                if (!baseMonthly) return;
                this.periodRows.forEach(r => {
                    const m = r.months;
                    this.setPeriodPrice(m, baseMonthly * m);
                });
            },

            calcDiscountedPrice(rawPrice) {
                const price = parseInt(rawPrice) || 0;
                if (price <= 0) return 0;
                const val = parseFloat(this.pricing.discount_value) || 0;
                if (val <= 0) return price;
                return Math.max(0, Math.round(price - val));
            },

            hasGlobalDiscount() {
                return (parseFloat(this.pricing.discount_value) || 0) > 0;
            },

            getPeriodDiscount(months) {
                months = String(months);
                if (!this.pricing.period_discounts[months]) {
                    this.pricing.period_discounts[months] = {
                        has_custom: false,
                        discount_value: 0,
                        discount_type: 'amount',
                        discount_starts_at: '',
                        discount_expires_at: ''
                    };
                }
                if (this.pricing.period_discounts[months].has_custom === undefined) {
                    this.pricing.period_discounts[months].has_custom = (parseFloat(this.pricing.period_discounts[months].discount_value) || 0) > 0;
                }
                return this.pricing.period_discounts[months];
            },

            setPeriodDiscountValue(months, rawVal) {
                const num = this.parseCleanNumber(rawVal);
                const d = this.getPeriodDiscount(months);
                d.discount_value = num;
            },

            getEffectivePeriodDiscount(months) {
                const d = this.getPeriodDiscount(months);
                if (d.has_custom) {
                    const val = parseFloat(d.discount_value) || 0;
                    return {
                        has_discount: val > 0,
                        value: val,
                        type: d.discount_type || 'amount',
                        is_custom: true
                    };
                }
                // Fallback to global package discount
                const gVal = parseFloat(this.pricing.discount_value) || 0;
                return {
                    has_discount: gVal > 0,
                    value: gVal,
                    type: 'amount',
                    is_custom: false
                };
            },

            calcDiscountedPeriodPrice(months) {
                const rawPrice = this.getPeriodPrice(months);
                if (rawPrice <= 0) return 0;
                const eff = this.getEffectivePeriodDiscount(months);
                if (!eff.has_discount || eff.value <= 0) return rawPrice;
                return Math.max(0, Math.round(rawPrice - eff.value));
            },
        };
    }

    window.packageEditManager = packageEditManager;
    if (window.Alpine) {
        window.Alpine.data('packageEditManager', packageEditManager);
    } else {
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('packageEditManager', packageEditManager);
        });
    }
</script>
