@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Enums\PackageIcon;
@endphp
<script>
    window.resellerDurationOptions = {!! json_encode(ResellerHelper::durationOptions(), JSON_UNESCAPED_UNICODE) !!};
    window.resellerStandardDurations = {!! json_encode(ResellerHelper::standardDurationList(), JSON_UNESCAPED_UNICODE) !!};
    window.resellerColorPresets = {!! json_encode(ResellerHelper::whiteLabelColorPresets(), JSON_UNESCAPED_UNICODE) !!};
    window.resellerPackageColors = {!! json_encode(ResellerHelper::packageColorPresets(), JSON_UNESCAPED_UNICODE) !!};
    window.resellerPackageColorConfigs = {!! json_encode(ResellerHelper::packageColors(), JSON_UNESCAPED_UNICODE) !!};

    window.industryIcons = {!! json_encode(PackageIcon::list(), JSON_UNESCAPED_UNICODE) !!};
    window.industryIconSvgs = {!! json_encode(PackageIcon::svgMap(), JSON_UNESCAPED_UNICODE) !!};

    window.getColorHex = function (color) {
        if (!color) return '#4f46e5';
        if (color.startsWith('#')) return color;
        const cfg = (window.resellerPackageColorConfigs || {})[color];
        if (cfg && cfg.hex) return cfg.hex;
        return color.length === 6 ? '#' + color : '#4f46e5';
    };

    window.getColorClass = function (color) {
        const cfg = (window.resellerPackageColorConfigs || {})[color];
        return (cfg && cfg.bg) ? cfg.bg : 'bg-indigo-600';
    };

    window.getColorLightClass = function (color) {
        const cfg = (window.resellerPackageColorConfigs || {})[color];
        return (cfg && cfg.light) ? cfg.light : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800';
    };

    window.faNum = window.faNum || function (num) {
        if (num === null || num === undefined) return '';
        return num.toString().replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    };

    window.formatAmount = function (amount) {
        if (!amount && amount !== 0) return '۰';
        return window.faNum(new Intl.NumberFormat('fa-IR').format(amount));
    };

    window.parsePriceInput = function (val) {
        if (!val) return 0;
        const clean = val.toString()
            .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
            .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
            .replace(/[^0-9]/g, '');
        return parseInt(clean, 10) || 0;
    };

    window.renderIndustryIcon = function (key, sizeClass) {
        sizeClass = sizeClass || 'w-5 h-5';
        const path = (window.industryIconSvgs || {})[key] || (window.industryIconSvgs || {})['cube'] || '';
        return '<svg class="' + sizeClass + '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">' + path + '</svg>';
    };

    window.suggestSubdomain = function (name) {
        if (!name) return '';
        const map = {
            'ا': 'a', 'ب': 'b', 'پ': 'p', 'ت': 't', 'ث': 's',
            'ج': 'j', 'چ': 'ch', 'ح': 'h', 'خ': 'kh', 'د': 'd',
            'ذ': 'z', 'ر': 'r', 'ز': 'z', 'ژ': 'zh', 'س': 's',
            'ش': 'sh', 'ص': 's', 'ض': 'z', 'ط': 't', 'ظ': 'z',
            'ع': 'a', 'غ': 'gh', 'ف': 'f', 'ق': 'gh', 'ک': 'k',
            'گ': 'g', 'ل': 'l', 'م': 'm', 'ن': 'n', 'و': 'v',
            'ه': 'h', 'ی': 'y', 'آ': 'a', 'ي': 'y', 'ك': 'k', ' ': '-'
        };
        let slug = name.split('').map(c => map[c] ?? c).join('');
        slug = slug.toLowerCase().replace(/[^a-z0-9-]+/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, '');
        return slug.slice(0, 30);
    };

    window.resolvePackageDurationOptions = function (pkg, options) {
        options = options || {};
        const fallback = options.fallback || window.resellerStandardDurations || [];
        if (!pkg) return fallback;

        const priceType = pkg.price_type || 'subscription';
        if (priceType === 'free') {
            return [{value: '12', months: 12, label: 'رایگان (اعتبار ۱۲ ماهه)'}];
        }
        if (priceType === 'one_time') {
            const val = options.oneTimeValue !== undefined ? String(options.oneTimeValue) : '0';
            const lbl = options.oneTimeLabel || 'مادام‌العمر (یک‌بار پرداخت)';
            return [{value: val, months: 0, label: lbl}];
        }

        const subYearOptions = (window.resellerDurationOptions || []).filter(item => item.months && item.months <= 12);
        const active = Array.isArray(pkg.active_periods) ? pkg.active_periods : [];
        const periodPrices = (pkg.period_prices && typeof pkg.period_prices === 'object') ? pkg.period_prices : {};
        const opts = [];

        subYearOptions.forEach(item => {
            const mStr = String(item.months);
            const isAct = active.includes(item.key)
                || active.includes(mStr)
                || active.includes(item.months)
                || (parseInt(periodPrices[mStr] || periodPrices[item.months]) > 0);
            if (isAct) {
                opts.push({value: mStr, months: item.months, key: item.key, label: item.label});
            }
        });

        // Add any custom months found in active_periods or period_prices
        const handled = new Set(opts.map(o => String(o.value)));
        const checkAddMonth = (m) => {
            const mNum = parseInt(m, 10);
            if (mNum > 0 && !handled.has(String(mNum))) {
                handled.add(String(mNum));
                const optMatch = (window.resellerDurationOptions || []).find(o => o.months === mNum);
                const label = optMatch ? optMatch.label : (window.faNum ? window.faNum(mNum) : mNum) + ' ماهه';
                opts.push({value: String(mNum), months: mNum, label: label});
            }
        };

        active.forEach(k => {
            if (typeof k === 'string') {
                const match = k.match(/^(?:period_)?(\d+)(?:_price)?$/);
                if (match) checkAddMonth(match[1]);
            } else if (typeof k === 'number') {
                checkAddMonth(k);
            }
        });

        Object.keys(periodPrices).forEach(k => {
            if (parseInt(periodPrices[k]) > 0) {
                checkAddMonth(k);
            }
        });

        if ((active.includes('yearly_price') || active.includes('12')) && !handled.has('24')) {
            const opt24 = (window.resellerDurationOptions || []).find(o => o.months === 24);
            opts.push({value: '24', months: 24, label: opt24 ? opt24.label : '۲ ساله (۲۴ ماهه)'});
            handled.add('24');
        }
        if ((active.includes('yearly_price') || active.includes('12')) && !handled.has('36')) {
            const opt36 = (window.resellerDurationOptions || []).find(o => o.months === 36);
            opts.push({value: '36', months: 36, label: opt36 ? opt36.label : '۳ ساله (۳۶ ماهه)'});
            handled.add('36');
        }

        opts.sort((a, b) => parseInt(a.value) - parseInt(b.value));
        return opts.length > 0 ? opts : fallback;
    };

    window.getDurationLabel = function (months) {
        if (String(months) === '1200' || String(months) === '0') {
            return 'دائمی (خرید یک‌باره - بدون انقضا)';
        }
        const opt = (window.resellerDurationOptions || []).find(o => String(o.months) === String(months) || String(o.value) === String(months));
        return opt ? opt.label : ((window.faNum ? window.faNum(months) : months) + ' ماهه');
    };

    window.getDurationSubtitle = function (months) {
        if (String(months) === '1200' || String(months) === '0') {
            return 'دسترسی همیشگی و بدون نیاز به تمدید';
        }
        if (String(months) === '12') {
            return 'اشتراک ۱ ساله کامل';
        }
        const opt = (window.resellerDurationOptions || []).find(o => String(o.months) === String(months) || String(o.value) === String(months));
        if (opt && opt.label) {
            return 'اشتراک ' + opt.label;
        }
        return 'اشتراک ' + (window.faNum ? window.faNum(months) : months) + ' ماهه';
    };

    window.resolvePeriodKey = function (months) {
        months = parseInt(months, 10);
        const map = {
            1: 'monthly_price',
            3: 'quarterly_price',
            6: 'semi_annual_price',
            12: 'yearly_price',
            18: 'period_18_price',
            24: 'period_24_price',
            36: 'period_36_price'
        };
        return map[months] || ('period_' + months + '_price');
    };

    window.resolvePeriodMonth = function (key) {
        if (!key && key !== 0) return null;
        if (typeof key === 'number') return key;
        const str = String(key);
        const map = {
            'monthly_price': 1, 'quarterly_price': 3, 'semi_annual_price': 6, 'yearly_price': 12,
            'period_18_price': 18, 'biennial_price': 24, 'period_24_price': 24, 'triennial_price': 36, 'period_36_price': 36
        };
        if (map[str]) return map[str];
        const match = str.match(/\d+/);
        return match ? parseInt(match[0], 10) : null;
    };

    window.resellerUiHelpers = function () {
        return {
            faNum: window.faNum,
            formatAmount: window.formatAmount,
            parsePriceInput: window.parsePriceInput,
            getColorHex: window.getColorHex,
            getColorClass: window.getColorClass,
            getColorLightClass: window.getColorLightClass,
            renderIcon: window.renderIndustryIcon,
            suggestSubdomain: window.suggestSubdomain,
            resolvePackageDurationOptions: window.resolvePackageDurationOptions,
            resolvePeriodKey: window.resolvePeriodKey,
            resolvePeriodMonth: window.resolvePeriodMonth,
            standardDurationList: window.resellerStandardDurations,
            durationOptions: window.resellerDurationOptions,
            getDurationLabel: window.getDurationLabel,
            getDurationSubtitle: window.getDurationSubtitle,
        };
    };
</script>
