@extends('layouts.user')

@section('title', 'جزئیات پکیج: ' . $package->name)

@php
    use Morilog\Jalali\Jalalian;
    use Modules\ResellerHub\App\Models\ResellerSetting;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Enums\PackagePriceType;
    use Modules\ResellerHub\App\Enums\PackageDiscountType;
    use Modules\ResellerHub\App\Enums\PackageColor;
    use Modules\ResellerHub\App\Enums\PackageIcon;
    use Modules\ResellerHub\App\Enums\BillingCycle;
    use Modules\ResellerHub\App\Enums\InvoiceStatus;

    $colorConf = ResellerHelper::packageColorConfig($package->color);
    $packageModules = $package->modules ?? [];
    $packageFeatures = $package->features ?? [];
    $catalogMap = collect($catalog ?? [])->keyBy('slug');

    $resellerSettings = ResellerSetting::allForReseller($reseller->id);
    $invoiceNumbering = [
        'prefix'   => (string)($resellerSettings['invoice_prefix'] ?? 'INV-'),
        'middle'   => (string)($resellerSettings['invoice_middle_prefix'] ?? Jalalian::now()->getYear()),
        'suffix'   => (string)($resellerSettings['invoice_suffix'] ?? ''),
        'padding'  => (int)($resellerSettings['invoice_padding'] ?? 4),
        'auto'     => !empty($resellerSettings['invoice_auto']),
        'tax_mode' => (string)($resellerSettings['tax_mode'] ?? 'invoice'),
        'tax_rate' => (float)($resellerSettings['default_tax_rate'] ?? 10),
        'terms'    => (string)($resellerSettings['invoice_terms'] ?? ''),
        'footer'   => (string)($resellerSettings['invoice_footer_note'] ?? ''),
    ];

    $currency = $currency ?? ResellerSetting::getCurrencyForReseller($reseller->id);
    $currencyLabel = $currencyLabel ?? ResellerSetting::getCurrencyLabelForReseller($reseller->id);
    $catalogForJs = collect($catalog ?? [])->values();

    $resellerData = [
        'id' => $reseller->id,
        'name' => $reseller->company_name ?: ($reseller->user?->name ?? 'نماینده #' . $reseller->id),
    ];

    $isFree = (bool)$package->isFree();
    $priceType = $isFree ? PackagePriceType::FREE->value : ($package->price_type ?? PackagePriceType::SUBSCRIPTION->value);

    $rawActivePeriods = $package->active_periods ?? [];
    if (is_string($rawActivePeriods)) {
        $rawActivePeriods = json_decode($rawActivePeriods, true) ?: [];
    }
    $activePeriods = is_array($rawActivePeriods) ? array_values($rawActivePeriods) : [];

    if (empty($activePeriods) && $priceType === PackagePriceType::SUBSCRIPTION->value) {
        $periodFallbackMap = [
            'monthly_price' => (int)$package->monthly_price,
            'quarterly_price' => (int)$package->quarterly_price,
            'semi_annual_price' => (int)$package->semi_annual_price,
            'yearly_price' => (int)$package->yearly_price,
        ];
        $activePeriods = collect($periodFallbackMap)->filter(fn($v) => $v > 0)->keys()->values()->toArray();
    }

    $discountType = $package->discount_type ?? PackageDiscountType::AMOUNT->value;
    $discountValue = (float)($package->discount_value ?? 0);
    $hasDiscount = !$isFree && $package->hasActiveDiscount();
    $discountExpiresAtJalali = $package->discount_expires_at_jalali;
    $discountLabel = $package->getDiscountTypeEnum()->formatValue($discountValue, $currencyLabel) . ' تخفیف پکیج';
    if ($hasDiscount && $discountExpiresAtJalali) {
        $discountLabel .= ' (معتبر تا ' . reseller_fa_num($discountExpiresAtJalali) . ')';
    }

    $priceTypeLabels = reseller_package_meta('price_type', 'labels');

    // Base price used for hero/KPI summary card
    $basePriceLabel = 'تعرفه پایه پکیج';
    $basePriceValue = 0;
    $basePriceUnit = $currencyLabel;
    if ($isFree || $priceType === PackagePriceType::FREE->value) {
        $basePriceLabel = 'نوع تعرفه پکیج';
        $basePriceValue = 0;
        $basePriceUnit = 'رایگان';
    } elseif ($priceType === PackagePriceType::ONE_TIME->value) {
        $basePriceLabel = 'قیمت یک‌باره پکیج';
        $basePriceValue = (int)($package->one_time_price ?? 0);
        $basePriceUnit = $currencyLabel . ' (یک‌باره)';
    } elseif ($priceType === PackagePriceType::SUBSCRIPTION->value) {
        if (in_array('yearly_price', $activePeriods, true) && $package->yearly_price > 0) {
            $basePriceLabel = 'تعرفه سالانه (اصلی)';
            $basePriceValue = (int)$package->yearly_price;
            $basePriceUnit = $currencyLabel . '/سال';
        } elseif (in_array('monthly_price', $activePeriods, true) && $package->monthly_price > 0) {
            $basePriceLabel = 'تعرفه ماهانه (اصلی)';
            $basePriceValue = (int)$package->monthly_price;
            $basePriceUnit = $currencyLabel . '/ماه';
        } elseif (!empty($activePeriods)) {
            $firstKey = $activePeriods[0];
            $basePriceValue = (int)($package->{$firstKey} ?? 0);
            $basePriceUnit = $currencyLabel;
        }
    }

    $packageJson = [
        'id' => $package->id,
        'name' => $package->name,
        'slug' => $package->slug,
        'description' => $package->description ?? '',
        'icon' => $package->icon ?: PackageIcon::CUBE->value,
        'color' => $package->color ?: PackageColor::INDIGO->value,
        'color_hex' => $package->color_hex,
        'modules' => $packageModules,
        'modules_count' => count($packageModules),
        'features' => $packageFeatures,
        'features_count' => count($packageFeatures),
        'limits' => $package->limits ?? [],
        'tenants_count' => (int)($package->tenants_count ?? $package->tenants()->count()),
        'is_active' => (bool)$package->isActive(),
        'is_free' => $isFree,
        'price_type' => $priceType,
        'period_prices' => $package->period_prices ?? [],
        'active_periods' => $activePeriods,
        'monthly_price' => (int)($package->monthly_price ?? 0),
        'quarterly_price' => (int)($package->quarterly_price ?? 0),
        'semi_annual_price' => (int)($package->semi_annual_price ?? 0),
        'yearly_price' => (int)($package->yearly_price ?? 0),
        'one_time_price' => (int)($package->one_time_price ?? 0),
        'setup_fee' => (int)($package->setup_fee ?? 0),
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
        'discount_expires_at' => $discountExpiresAtJalali ?? '',
        'modules_titles' => collect($packageModules)->map(fn($s) => $modulesMap[$s]->title ?? $s)->values(),
    ];

    $modulesListForJs = $availableModules->map(function ($m) {
        return ['slug' => $m->slug, 'name' => $m->name, 'title' => $m->title, 'is_allowed' => $m->is_allowed];
    })->values();
    $subAccountsJson = collect($subAccounts ?? [])->values();
@endphp
@section('content')
    @include('resellerhub::user.packages.partials.icons-script')
    @include('partials.jalali-date-picker')

    <script>
        window.packageShowConfig = {
            reseller: {!! json_encode($resellerData) !!},
            package: {!! json_encode($packageJson, JSON_UNESCAPED_UNICODE) !!},
            modules: {!! json_encode($modulesListForJs, JSON_UNESCAPED_UNICODE) !!},
            catalog: {!! json_encode($catalogForJs, JSON_UNESCAPED_UNICODE) !!},
            subAccounts: {!! json_encode($subAccountsJson, JSON_UNESCAPED_UNICODE) !!},
            quickSubAccountUrl: "{{ route('reseller.sub-accounts.quick-store') }}",
            baseDomain: '{{ $baseDomain ?? request()->getHost() }}',
            defaultDuration: '{{ $defaultDuration ?? 12 }}',
            isSuperAdmin: {{ ($isSuperAdmin ?? false) ? 'true' : 'false' }},
            resellersList: {!! json_encode($resellersList ?? [], JSON_UNESCAPED_UNICODE) !!},
            storeTenantUrl: "{{ route('reseller.tenants.store') }}",
            checkSubdomainUrl: "{{ route('reseller.tenants.check-subdomain') }}",
            destroyPackageUrl: "{{ route('reseller.packages.destroy', $package->id) }}",
            invoiceNumbering: {!! json_encode($invoiceNumbering) !!},
            currencyLabel: '{{ $currencyLabel }}',
            csrfToken: '{{ csrf_token() }}'
        };

        function packageShowManager() {
            const config = window.packageShowConfig || {};
            const uiHelpers = window.resellerUiHelpers ? window.resellerUiHelpers() : {};
            return {
                ...uiHelpers,
                reseller: config.reseller || {},
                packageData: config.package || {},
                selectedPackage: config.package || {},
                packageToDelete: config.package || {},
                packages: [config.package || {}],
                modules: config.modules || [],
                catalog: config.catalog || [],
                baseDomain: (config.baseDomain || (typeof window !== 'undefined' ? window.location.hostname : '')).replace(/:\d+$/, ''),
                defaultDuration: config.defaultDuration || '12',
                isSuperAdmin: Boolean(config.isSuperAdmin),
                resellersList: config.resellersList || [],

                cleanBaseDomain() {
                    let d = this.baseDomain || (typeof window !== 'undefined' ? window.location.hostname : '');
                    return d ? d.replace(/:\d+$/, '') : '';
                },
                storeTenantUrl: config.storeTenantUrl || '',
                checkSubdomainUrl: config.checkSubdomainUrl || '',
                destroyPackageUrl: config.destroyPackageUrl || '',
                deleteActionUrl: config.destroyPackageUrl || '',
                csrfToken: config.csrfToken || '',
                invoiceNumbering: config.invoiceNumbering || {},
                currencyLabel: config.currencyLabel || @json($currencyLabel),
                invoiceNumberAuto: Boolean(config.invoiceNumbering && config.invoiceNumbering.auto),
                invoiceNumberOverride: '',
                taxRateLocked: true,
                invoiceTaxRate: parseFloat((config.invoiceNumbering && config.invoiceNumbering.tax_rate) || 0),
                invoiceTaxMode: (config.invoiceNumbering && config.invoiceNumbering.tax_mode) || 'invoice',
                invoiceTerms: (config.invoiceNumbering && config.invoiceNumbering.terms) || '',
                invoiceTermsLocked: true,
                invoiceNotes: (config.invoiceNumbering && config.invoiceNumbering.footer) || '',
                invoiceDiscountAmount: 0,
                invoiceDiscountDisplay: '',
                invoiceItems: [],
                quickInvoiceItems: [],
                invoiceIssueDate: '',
                invoiceDueDate: '',
                deleteModalOpen: false,
                isDeleting: false,
                deleteErrorMsg: '',

                openDeleteModal(pkg = null) {
                    if (pkg) this.packageToDelete = pkg;
                    this.deleteActionUrl = this.destroyPackageUrl;
                    this.deleteErrorMsg = '';
                    this.isDeleting = false;
                    this.deleteModalOpen = true;
                },
                closeDeleteModal() {
                    if (this.isDeleting) return;
                    this.deleteModalOpen = false;
                    this.deleteErrorMsg = '';
                },
                async confirmDelete() {
                    if (!this.packageToDelete) return;
                    this.isDeleting = true;
                    this.deleteErrorMsg = '';

                    try {
                        const response = await fetch(this.deleteActionUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                _method: 'DELETE'
                            })
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || !data.success) {
                            this.isDeleting = false;
                            this.deleteErrorMsg = data.message || 'امکان حذف این پکیج وجود ندارد.';
                            return;
                        }

                        window.location.href = "{{ route('reseller.packages.index') }}";
                    } catch (e) {
                        this.isDeleting = false;
                        this.deleteErrorMsg = 'خطا در برقراری ارتباط با سرور. لطفاً مجدداً تلاش فرمایید.';
                    }
                },

                // Toggle Status Modal State & Actions
                statusModalOpen: false,
                packageToToggle: null,
                isTogglingStatus: false,
                statusErrorMsg: '',

                openStatusModal(pkg = null) {
                    this.packageToToggle = pkg || this.packageData;
                    this.statusErrorMsg = '';
                    this.isTogglingStatus = false;
                    this.statusModalOpen = true;
                },
                closeStatusModal() {
                    if (this.isTogglingStatus) return;
                    this.statusModalOpen = false;
                    this.statusErrorMsg = '';
                },
                async confirmToggleStatus() {
                    if (!this.packageToToggle) return;
                    this.isTogglingStatus = true;
                    this.statusErrorMsg = '';

                    try {
                        const url = `/reseller/packages/${this.packageToToggle.id}/toggle-status`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || !data.success) {
                            this.isTogglingStatus = false;
                            this.statusErrorMsg = data.message || 'خطا در تغییر وضعیت پکیج.';
                            return;
                        }

                        window.location.reload();
                    } catch (e) {
                        this.isTogglingStatus = false;
                        this.statusErrorMsg = 'خطا در برقراری ارتباط با سرور. لطفاً مجدداً تلاش فرمایید.';
                    }
                },

                // Quick Provision CRM Modal State & Sub-Account Management
                quickProvisionModalOpen: false,
                submitting: false,
                createdTenant: null,
                createdInvoice: null,
                createdOrder: null,
                copiedUrl: false,
                copiedCreds: false,
                generalError: '',
                formErrors: {},
                showPassword: false,

                // Sub-Accounts (Buyer) Selection
                subAccounts: config.subAccounts || [],
                selectedSubAccount: null,
                subAccountSearchQuery: '',
                quickSubModalOpen: false,
                quickSubSaving: false,
                quickSubErrors: {},
                quickSubForm: {name: '', mobile: '', company_name: ''},
                quickSubAccountUrl: config.quickSubAccountUrl || '',

                subdomainChecking: false,
                subdomainStatus: 'idle',
                subdomainMsg: '',
                subdomainTimer: null,
                subdomainManuallyEdited: false,

                form: {
                    name: '',
                    subdomain: '',
                    reseller_id: '',
                    sub_account_id: '',
                    admin_name: '',
                    admin_mobile: '',
                    admin_email: '',
                    password: '',
                    password_confirmation: '',
                    expires_in_months: '12',
                    modules: [],
                    issue_invoice: true,
                    invoice_status: '{{ InvoiceStatus::UNPAID->value }}'
                },

                // Sub-Account Methods
                filteredSubAccounts() {
                    const q = (this.subAccountSearchQuery || '').trim().toLowerCase();
                    if (!q) return this.subAccounts;
                    return this.subAccounts.filter(s => {
                        const name = (s.name || '').toLowerCase();
                        const mobile = (s.mobile || '').toLowerCase();
                        const comp = (s.company_name || '').toLowerCase();
                        return name.includes(q) || mobile.includes(q) || comp.includes(q);
                    });
                },

                selectSubAccount(sub) {
                    this.selectedSubAccount = sub;
                    this.form.sub_account_id = sub ? sub.id : '';
                    this.form.admin_name = sub ? (sub.name || '') : '';
                    this.form.admin_mobile = sub ? (sub.mobile || '') : '';
                    this.form.admin_email = sub ? (sub.email || '') : '';
                    if (!this.form.name) {
                        this.form.name = (sub.company_name || sub.name || '');
                        this.autoSuggestSubdomain();
                    }
                    if (this.formErrors && this.formErrors.sub_account_id) {
                        delete this.formErrors.sub_account_id;
                    }
                },

                clearSubAccount() {
                    this.selectedSubAccount = null;
                    this.form.sub_account_id = '';
                    this.form.admin_name = '';
                    this.form.admin_mobile = '';
                    this.form.admin_email = '';
                },

                openQuickSubModal() {
                    this.quickSubForm = {name: '', mobile: '', company_name: ''};
                    this.quickSubErrors = {};
                    this.quickSubSaving = false;
                    this.quickSubModalOpen = true;
                },

                async submitQuickSubAccount() {
                    if (this.quickSubSaving) return;
                    this.quickSubSaving = true;
                    this.quickSubErrors = {};

                    try {
                        const res = await fetch(this.quickSubAccountUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(this.quickSubForm)
                        });

                        const data = await res.json();
                        this.quickSubSaving = false;

                        if (!res.ok || !data.success) {
                            if (data.errors) {
                                this.quickSubErrors = data.errors;
                            } else if (data.message) {
                                this.quickSubErrors = {name: [data.message]};
                            }
                            return;
                        }

                        const newSub = data.sub_account;
                        this.subAccounts.unshift(newSub);
                        this.selectSubAccount(newSub);
                        this.quickSubModalOpen = false;
                    } catch (e) {
                        this.quickSubSaving = false;
                        this.quickSubErrors = {name: ['خطا در برقراری ارتباط با سرور']};
                    }
                },

                get durationOptions() {
                    const pkg = this.selectedPackage || this.packageData;
                    const fn = this.resolvePackageDurationOptions || window.resolvePackageDurationOptions;
                    if (fn) {
                        return fn(pkg, {
                            fallback: [{
                                value: '12',
                                key: 'yearly_price',
                                label: '{{ BillingCycle::ANNUAL->label(true) }}'
                            }],
                            oneTimeValue: '1200',
                            oneTimeLabel: '{{ BillingCycle::LIFETIME->label(true) }}',
                        });
                    }
                    return [{value: '12', key: 'yearly_price', label: '{{ BillingCycle::ANNUAL->label(true) }}'}];
                },

                openQuickProvision(pkg = null) {
                    const target = pkg || this.selectedPackage || this.packageData;
                    this.selectedPackage = target;
                    this.createdTenant = null;
                    this.createdInvoice = null;
                    this.createdOrder = null;
                    this.generalError = '';
                    this.formErrors = {};
                    this.copiedUrl = false;
                    this.copiedCreds = false;
                    this.submitting = false;
                    this.subdomainManuallyEdited = false;

                    this.selectedSubAccount = null;
                    this.subAccountSearchQuery = '';

                    this.form.name = '';
                    this.form.subdomain = '';
                    this.form.sub_account_id = '';
                    this.form.reseller_id = (this.reseller && this.reseller.id) ? this.reseller.id : '';
                    this.form.admin_name = '';
                    this.form.admin_mobile = '';
                    this.form.admin_email = '';
                    this.form.modules = (target && target.modules) ? [...target.modules] : [];

                    const priceType = (target && target.price_type) || '{{ PackagePriceType::SUBSCRIPTION->value }}';
                    if (priceType === '{{ PackagePriceType::FREE->value }}') {
                        this.form.issue_invoice = false;
                        this.form.invoice_status = '{{ InvoiceStatus::UNPAID->value }}';
                        this.form.expires_in_months = null;
                    } else if (priceType === '{{ PackagePriceType::ONE_TIME->value }}') {
                        this.form.issue_invoice = true;
                        this.form.invoice_status = '{{ InvoiceStatus::UNPAID->value }}';
                        this.form.expires_in_months = null;
                    } else {
                        this.form.issue_invoice = true;
                        this.form.invoice_status = '{{ InvoiceStatus::UNPAID->value }}';
                        const opts = this.durationOptions;
                        const preferred = opts.find(o => String(o.value) === String(this.defaultDuration));
                        this.form.expires_in_months = preferred ? preferred.value : (opts[0] ? opts[0].value : '12');
                    }

                    this.invoiceNumberAuto = Boolean(this.invoiceNumbering && this.invoiceNumbering.auto);
                    this.invoiceNumberOverride = '';
                    this.taxRateLocked = true;
                    this.invoiceTaxRate = parseFloat((this.invoiceNumbering && this.invoiceNumbering.tax_rate) || 0);
                    this.invoiceTermsLocked = true;
                    this.invoiceTerms = (this.invoiceNumbering && this.invoiceNumbering.terms) || '';
                    this.invoiceNotes = (this.invoiceNumbering && this.invoiceNumbering.footer) || '';
                    this.invoiceDiscountAmount = 0;
                    this.invoiceDiscountDisplay = '';
                    this.invoiceItems = [];

                    this.setTodayIssueDate();
                    this.invoiceDueDate = this.addDaysToJalaliToday(30);

                    this.syncQuickInvoiceItems();

                    this.subdomainStatus = 'idle';
                    this.subdomainMsg = '';

                    this.quickProvisionModalOpen = true;

                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            window.jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                            });
                        }
                    });
                },

                closeQuickProvision() {
                    if (this.submitting) return;
                    this.quickProvisionModalOpen = false;
                    this.createdTenant = null;
                    this.createdInvoice = null;
                    this.createdOrder = null;
                },

                selectPackageDuration(months) {
                    this.form.expires_in_months = String(months);
                    this.syncQuickInvoiceItems();
                },

                autoSuggestSubdomain() {
                    if (this.subdomainManuallyEdited) return;
                    const name = (this.form.name || '').trim();
                    if (!name) {
                        this.form.subdomain = '';
                        this.subdomainStatus = 'idle';
                        this.subdomainMsg = '';
                        return;
                    }
                    const suggest = this.suggestSubdomain || window.suggestSubdomain;
                    this.form.subdomain = suggest ? suggest(name) : '';
                    this.onSubdomainInput();
                },

                checkSubdomain() {
                    this.onSubdomainInput();
                },

                onSubdomainInput() {
                    this.subdomainManuallyEdited = true;
                    let val = this.form.subdomain.toLowerCase().trim();
                    val = val.replace(/[^a-z0-9-]/g, '');
                    this.form.subdomain = val;

                    clearTimeout(this.subdomainTimer);
                    if (!val) {
                        this.subdomainStatus = 'idle';
                        this.subdomainMsg = '';
                        return;
                    }
                    if (val.length < 3) {
                        this.subdomainStatus = 'unavailable';
                        this.subdomainMsg = 'حداقل ۳ کاراکتر الزامی است.';
                        return;
                    }

                    this.subdomainChecking = true;
                    this.subdomainTimer = setTimeout(() => this.checkSubdomainAvailability(val), 400);
                },

                async checkSubdomainAvailability(subdomain) {
                    try {
                        const res = await fetch(`${this.checkSubdomainUrl}?subdomain=${encodeURIComponent(subdomain)}`, {
                            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                        });
                        const data = await res.json();
                        this.subdomainChecking = false;
                        if (data.available) {
                            this.subdomainStatus = 'available';
                            this.subdomainMsg = 'زیردامنه آزاد و قابل استفاده است.';
                        } else {
                            this.subdomainStatus = 'unavailable';
                            this.subdomainMsg = data.message || 'این زیردامنه قبلاً ثبت شده است.';
                        }
                    } catch (e) {
                        this.subdomainChecking = false;
                        this.subdomainStatus = 'idle';
                    }
                },

                getPackageDurationPrice(pkg, months) {
                    if (!pkg) return 0;
                    const priceType = pkg.price_type || '{{ PackagePriceType::SUBSCRIPTION->value }}';
                    if (priceType === '{{ PackagePriceType::FREE->value }}') return 0;
                    if (priceType === '{{ PackagePriceType::ONE_TIME->value }}') return parseInt(pkg.one_time_price) || 0;

                    months = parseInt(months, 10) || 12;

                    if (pkg.period_prices && typeof pkg.period_prices === 'object') {
                        const directPrice = parseInt(pkg.period_prices[months] || pkg.period_prices[String(months)]) || 0;
                        if (directPrice > 0) {
                            return directPrice;
                        }
                    }

                    const mPrice = parseInt(pkg.monthly_price) || 0;
                    const qPrice = parseInt(pkg.quarterly_price) || 0;
                    const sPrice = parseInt(pkg.semi_annual_price) || 0;
                    const yPrice = parseInt(pkg.yearly_price) || 0;
                    const yearlyBase = yPrice > 0 ? yPrice : (mPrice * 12);

                    if (months === 1) return mPrice;
                    if (months === 3) return qPrice > 0 ? qPrice : Math.round((yearlyBase / 12) * 3);
                    if (months === 6) return sPrice > 0 ? sPrice : Math.round((yearlyBase / 12) * 6);
                    if (months === 12) return yearlyBase;
                    if (months === 24) return yearlyBase * 2;
                    if (months === 36) return yearlyBase * 3;
                    return Math.round((yearlyBase / 12) * months);
                },

                getPackagePriceForDuration(durationMonths = null) {
                    const pkg = this.selectedPackage || this.packageData;
                    const months = (durationMonths !== null && durationMonths !== undefined)
                        ? durationMonths : this.form.expires_in_months;
                    return this.getPackageDurationPrice(pkg, months);
                },

                getPackageSetupFee() {
                    const pkg = this.selectedPackage || this.packageData;
                    if (pkg && pkg.price_type === '{{ PackagePriceType::FREE->value }}') return 0;
                    return parseInt(pkg && pkg.setup_fee) || 0;
                },

                getActivePackageDiscount() {
                    const pkg = this.selectedPackage || this.packageData;
                    if (!pkg || pkg.price_type === '{{ PackagePriceType::FREE->value }}') return 0;
                    const basePrice = this.getPackagePriceForDuration();
                    const type = pkg.discount_type || '{{ PackageDiscountType::AMOUNT->value }}';
                    const value = parseFloat(pkg.discount_value) || 0;
                    if (value <= 0 || basePrice <= 0) return 0;
                    if (type === '{{ PackageDiscountType::AMOUNT->value }}') return Math.min(basePrice, Math.round(value));
                    return Math.round(basePrice * (Math.min(value, 100) / 100));
                },

                getPackageDiscountAmount(baseAmount) {
                    return this.getActivePackageDiscount();
                },

                getPackageTotalAmount() {
                    const base = this.getPackagePriceForDuration() + this.getPackageSetupFee();
                    const discount = this.getActivePackageDiscount();
                    return Math.max(0, base - discount);
                },

                get durationLabel() {
                    return this.getDurationLabel(this.form.expires_in_months);
                },

                getDurationTitle(months) {
                    return this.getDurationSubtitle ? this.getDurationSubtitle(months) : this.getDurationLabel(months);
                },

                // Custom Invoice Items Management
                addCustomInvoiceItem() {
                    this.invoiceItems.push({
                        description: '',
                        item_type: 'custom',
                        item_id: 'custom_' + Date.now(),
                        duration_months: 0,
                        unit_price: 0,
                        quantity: 1,
                        discount_amount: 0,
                        tax_rate: 0
                    });
                },

                removeInvoiceItem(idx) {
                    if (this.invoiceItems[idx] && this.invoiceItems[idx].item_type === 'custom') {
                        this.invoiceItems.splice(idx, 1);
                    }
                },

                formatPriceInput(val) {
                    if (val === null || val === undefined || val === '') return '';
                    const n = parseInt(String(val).replace(/[^0-9]/g, '')) || 0;
                    return n.toLocaleString('en-US');
                },

                syncQuickInvoiceItems() {
                    const pkg = this.selectedPackage || this.packageData;
                    if (!pkg) return;

                    const customItems = (this.invoiceItems || []).filter(it => it.item_type === 'custom');

                    const items = [];
                    const isOneTime = pkg.price_type === '{{ PackagePriceType::ONE_TIME->value }}';
                    const months = isOneTime ? 0 : (parseInt(this.form.expires_in_months, 10) || 12);
                    const basePrice = this.getPackagePriceForDuration(months);
                    const discountAmount = this.getActivePackageDiscount();
                    const durationText = isOneTime ? 'مادام‌العمر' : this.getDurationLabel(months);
                    const pkgMods = pkg.modules || [];
                    const pkgFeats = pkg.features || [];

                    const desc = isOneTime
                        ? `خرید مادام‌العمر پکیج «${pkg.name || ''}» (شامل ${this.faNum(pkgMods.length)} ماژول و ${this.faNum(pkgFeats.length)} قابلیت)`
                        : `اشتراک ${durationText} پکیج «${pkg.name || ''}» (شامل ${this.faNum(pkgMods.length)} ماژول و ${this.faNum(pkgFeats.length)} قابلیت)`;

                    items.push({
                        description: desc,
                        item_type: 'package',
                        item_id: 'package_' + pkg.id,
                        duration_months: months,
                        unit_price: basePrice,
                        quantity: 1,
                        discount_amount: discountAmount,
                        tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                        is_bundled: true,
                        is_extra: false,
                    });

                    const setupFee = this.getPackageSetupFee();
                    if (setupFee > 0) {
                        items.push({
                            description: `هزینه راه‌اندازی و کانفیگ اولیه پکیج «${pkg.name || ''}»`,
                            item_type: 'setup_fee',
                            item_id: 'package_' + pkg.id + '_setup',
                            duration_months: 0,
                            unit_price: setupFee,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                            is_bundled: true,
                            is_extra: false,
                        });
                    }
                    this.invoiceItems = [...items, ...customItems];
                },

                getItemRowTotal(item) {
                    const qty = Math.max(1, parseFloat(item.quantity) || 1);
                    const price = Math.max(0, parseInt(item.unit_price) || 0);
                    const disc = Math.max(0, parseInt(item.discount_amount) || 0);
                    return Math.max(0, (qty * price) - disc);
                },

                getInvoiceSubtotal() {
                    return (this.invoiceItems || []).reduce((sum, it) => sum + this.getItemRowTotal(it), 0);
                },

                getInvoiceTax() {
                    const subtotal = this.getInvoiceSubtotal();
                    const rate = parseFloat(this.invoiceTaxRate) || 0;
                    return Math.round((subtotal * rate) / 100);
                },

                getInvoiceDiscount() {
                    return Math.max(0, parseInt(this.invoiceDiscountAmount) || 0);
                },

                getInvoiceTotal() {
                    const sub = this.getInvoiceSubtotal();
                    const tax = this.getInvoiceTax();
                    const disc = this.getInvoiceDiscount();
                    return Math.max(0, (sub + tax) - disc);
                },

                handleInvoiceDiscountInput(e) {
                    const parsed = this.parsePriceInput(e.target.value);
                    this.invoiceDiscountAmount = parsed;
                    this.invoiceDiscountDisplay = parsed > 0 ? this.formatPriceInput(parsed) : '';
                },

                updateInvoiceDiscount() {
                    const maxAllowed = this.getInvoiceSubtotal() + this.getInvoiceTax();
                    this.invoiceDiscountAmount = Math.min(Math.max(0, this.invoiceDiscountAmount), maxAllowed);
                    this.invoiceDiscountDisplay = this.invoiceDiscountAmount > 0 ? this.formatPriceInput(this.invoiceDiscountAmount) : '';
                },

                toggleInvoiceNumberAuto() {
                    this.invoiceNumberAuto = !this.invoiceNumberAuto;
                },

                get invoiceNumberPreview() {
                    const cfg = this.invoiceNumbering || {};
                    const pad = parseInt(cfg.padding) || 4;
                    const num = String(1).padStart(pad, '0');
                    const mid = cfg.middle ? (cfg.middle + '-') : '';
                    return (cfg.prefix || '') + mid + num + (cfg.suffix || '');
                },

                toggleTaxRateLock() {
                    this.taxRateLocked = !this.taxRateLocked;
                    if (this.taxRateLocked) {
                        this.invoiceTaxRate = parseFloat((this.invoiceNumbering && this.invoiceNumbering.tax_rate) || 0);
                    }
                },

                setTodayIssueDate() {
                    const formatter = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                    const parts = formatter.format(new Date()).split('/');
                    if (parts.length === 3) this.invoiceIssueDate = parts[0] + '/' + parts[1] + '/' + parts[2];
                },

                addDaysToJalaliToday(days) {
                    const d = new Date();
                    d.setDate(d.getDate() + days);
                    const formatter = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                    const parts = formatter.format(d).split('/');
                    return (parts.length === 3) ? (parts[0] + '/' + parts[1] + '/' + parts[2]) : '';
                },

                buildInvoicePayload() {
                    if (!this.form.issue_invoice) return {issue_invoice: false};
                    return {
                        issue_invoice: true,
                        invoice_auto: this.invoiceNumberAuto,
                        invoice_number: this.invoiceNumberAuto ? '' : (this.invoiceNumberOverride || '').trim(),
                        tax_from_settings: Boolean(this.taxRateLocked),
                        invoice_tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                        invoice_tax_mode: 'invoice',
                        invoice_issue_date: this.invoiceIssueDate,
                        invoice_due_date: this.invoiceDueDate,
                        invoice_status: this.form.invoice_status || '{{ InvoiceStatus::UNPAID->value }}',
                        invoice_discount_amount: this.invoiceDiscountAmount,
                        invoice_terms: this.invoiceTerms,
                        invoice_notes: this.invoiceNotes,
                        invoice_items: (this.invoiceItems || [])
                            .filter(it => (it.item_type === 'custom') ? ((it.description || '').trim() !== '') : true)
                            .map(it => ({
                                description: (it.description || '').trim(),
                                item_type: it.item_type,
                                item_id: it.item_id || '',
                                duration_months: parseInt(it.duration_months) || 0,
                                unit_price: parseInt(it.unit_price) || 0,
                                quantity: Math.max(1, parseInt(it.quantity) || 1),
                                discount_amount: Math.max(0, parseInt(it.discount_amount) || 0),
                                tax_rate: parseFloat(it.tax_rate) || 0,
                            })),
                    };
                },

                setFieldError(field, msg) {
                    this.formErrors[field] = [msg];
                    if (!this.generalError) this.generalError = 'لطفاً موارد مشخص‌شده در فرم را اصلاح کنید.';
                },

                async submitQuickProvision() {
                    if (this.submitting) return;
                    this.generalError = '';
                    this.formErrors = {};

                    if (this.isSuperAdmin && this.resellersList.length > 0 && !this.form.reseller_id) {
                        this.setFieldError('reseller_id', 'انتخاب نماینده الزامی است.');
                        return;
                    }
                    if (!this.form.sub_account_id) {
                        this.setFieldError('sub_account_id', 'انتخاب حساب خریدار (زیرمجموعه) الزامی است.');
                        return;
                    }
                    if (this.subdomainStatus === 'unavailable' || this.subdomainChecking) {
                        this.setFieldError('subdomain', 'لطفاً یک سابدامین معتبر و آزاد انتخاب فرمایید.');
                        return;
                    }

                    this.submitting = true;
                    try {
                        const pkg = this.selectedPackage || this.packageData;
                        const invoicePayload = this.buildInvoicePayload();

                        const payload = {
                            name: this.form.name.trim(),
                            subdomain: this.form.subdomain.trim(),
                            sub_account_id: this.form.sub_account_id,
                            reseller_id: this.form.reseller_id,
                            admin_name: this.form.admin_name.trim(),
                            admin_mobile: this.normalizeDigits(this.form.admin_mobile),
                            admin_email: this.form.admin_email ? this.form.admin_email.trim() : null,
                            expires_in_months: this.form.expires_in_months,
                            package_id: (pkg && pkg.id) ? pkg.id : null,
                            modules: (pkg && pkg.modules) ? pkg.modules : [],
                            features: (pkg && pkg.features) ? pkg.features : [],
                            ...invoicePayload,
                            _token: this.csrfToken
                        };

                        const res = await fetch(this.storeTenantUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            this.submitting = false;
                            if (data.errors) this.formErrors = data.errors;
                            this.generalError = data.message || 'خطا در ثبت سامانه. لطفاً فیلدها را بررسی فرمایید.';
                            return;
                        }

                        this.submitting = false;
                        this.createdTenant = data.tenant || null;

                        if (this.createdTenant) {
                            this.createdTenant.url = (data.tenant && data.tenant.url)
                                ? data.tenant.url
                                : ('https://' + this.form.subdomain + '.' + this.cleanBaseDomain());
                            this.createdTenant.redirect_url = data.redirect_url || '';
                        }
                        this.createdInvoice = data.invoice || null;
                        this.createdOrder = data.order || null;

                    } catch (err) {
                        this.submitting = false;
                        this.generalError = 'ارتباط با سرور برقرار نشد. لطفاً مجدداً تلاش نمایید.';
                    }
                },

                copyToClipboard(text, type) {
                    if (!text) return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text);
                    } else {
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.style.position = 'fixed';
                        ta.style.opacity = '0';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                    }
                    if (type === 'url') {
                        this.copiedUrl = true;
                        setTimeout(() => this.copiedUrl = false, 2500);
                    } else if (type === 'creds') {
                        this.copiedCreds = true;
                        setTimeout(() => this.copiedCreds = false, 2500);
                    }
                },

                get adminCredsText() {
                    return 'نام کسب‌وکار: ' + (this.form.name || '') +
                        '\nآدرس: ' + ((this.createdTenant && this.createdTenant.url) || '') +
                        '\nنام کاربری: ' + (this.form.admin_mobile || '') +
                        '\nرمز عبور: ' + (this.form.password || '');
                },

                getModuleTitle(slug) {
                    const found = (this.modules || []).find(m => m.slug === slug);
                    return found ? (found.title || found.name || slug) : slug;
                },

                getModuleFeatureCount(slug) {
                    const s = String(slug).toLowerCase();
                    const cat = (this.catalog || []).find(c => (c.slug || '').toLowerCase() === s);
                    if (!cat || !Array.isArray(cat.features)) return 0;
                    const pkgFeatures = (this.selectedPackage && this.selectedPackage.features) || [];
                    return cat.features.filter(f => pkgFeatures.includes(f.key)).length;
                },

                formatPrice(num) {
                    return this.formatAmount ? this.formatAmount(num) : (window.formatAmount ? window.formatAmount(num) : this.faNum(num));
                }
            };
        }
    </script>

    <div x-data="packageShowManager()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        {{-- Breadcrumb --}}
        <div class="flex items-center justify-between">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-500">
                <a href="{{ route('reseller.dashboard') }}" class="hover:text-indigo-600 transition">داشبورد
                    نمایندگی</a>
                <span>/</span>
                <a href="{{ route('reseller.packages.index') }}" class="hover:text-indigo-600 transition">پکیج‌های
                    اصناف</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-black">{{ $package->name }}</span>
            </nav>

            <a href="{{ route('reseller.packages.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-indigo-600 transition">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>بازگشت به لیست پکیج‌ها</span>
            </a>
        </div>

        {{-- HERO HEADER CARD --}}
        <x-resellerhub::card class="overflow-hidden">
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">

                {{-- Left Side: Themed Icon + Title + Meta Info --}}
                <div class="flex items-start sm:items-center gap-4 sm:gap-5 min-w-0">
                    <div
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl text-white flex items-center justify-center font-black shadow-lg shrink-0"
                        style="{{ $package->bg_style }}">
                        @include('resellerhub::user.packages.partials.icon', ['icon' => $package->icon, 'class' => 'w-8 h-8 sm:w-10 sm:h-10 text-white'])
                    </div>

                    <div class="min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-xl sm:text-3xl font-black text-gray-900 dark:text-white">
                                {{ $package->name }}
                            </h1>
                            <span class="px-3 py-0.5 rounded-full text-xs font-black border"
                                  style="{{ $package->light_bg_style }}">
                                رنگ پکیج: {{ $package->color_hex }}
                            </span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-black bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                نوع فروش: {{ $priceTypeLabels[$priceType] ?? 'اشتراکی' }}
                            </span>
                            @if($package->isActive())
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    فعال و در دسترس سفارش جدید
                                </span>
                            @else
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    غیرفعال (پنهان از خریداران جدید)
                                </span>
                            @endif
                            @if($hasDiscount)
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    <span>{{ $discountLabel }}</span>
                                </span>
                            @else
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                    بدون تخفیف
                                </span>
                            @endif
                        </div>

                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            شناسه پکیج: #{{ reseller_fa_num($package->id) }}
                            <span class="mx-1.5 text-gray-300 dark:text-gray-600">&bull;</span>
                            تاریخ
                            ثبت: {{ $package->created_at ? reseller_fa_num(Jalalian::fromCarbon($package->created_at)->format('Y/m/d')) : '-' }}
                            <span class="mx-1.5 text-gray-300 dark:text-gray-600">&bull;</span>
                            ترکیب: <strong
                                class="text-gray-800 dark:text-gray-200">{{ reseller_fa_num(count($packageModules)) }}
                                ماژول</strong> و <strong
                                class="text-indigo-600 dark:text-indigo-400">{{ reseller_fa_num(count($packageFeatures)) }}
                                قابلیت</strong>
                        </p>

                        @if(!empty($package->description))
                            <p class="text-xs text-gray-600 dark:text-gray-300 pt-1 leading-relaxed max-w-3xl">
                                {{ $package->description }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Right Side: Action Buttons --}}
                <div class="flex items-center gap-3 flex-wrap shrink-0">
                    <button type="button" @click="openQuickProvision()"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black text-xs sm:text-sm shadow-lg shadow-indigo-600/30 transition-all active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-white-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span>ساخت فوری CRM با این پکیج</span>
                    </button>

                    <button type="button" @click="openStatusModal(packageData)"
                            class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl {{ $package->isActive() ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-900/50' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-900/50' }} text-xs sm:text-sm font-bold transition cursor-pointer"
                            title="{{ $package->isActive() ? 'غیرفعال‌سازی این پکیج' : 'فعال‌سازی مجدد این پکیج' }}">
                        @if($package->isActive())
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>غیرفعال‌سازی پکیج</span>
                        @else
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>فعال‌سازی پکیج</span>
                        @endif
                    </button>

                    <a href="{{ route('reseller.packages.edit', $package->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span>ویرایش پکیج</span>
                    </a>

                    <button type="button" @click="openDeleteModal()"
                            class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-900/50 transition cursor-pointer"
                            title="حذف پکیج">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

            </div>
        </x-resellerhub::card>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-resellerhub::card class="p-5 flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-gray-400 font-bold block">ماژول‌های فعال پکیج</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span
                            class="text-2xl font-black text-gray-900 dark:text-white">{{ reseller_fa_num(count($packageModules)) }}</span>
                        <span class="text-xs text-gray-400">ماژول اصلی</span>
                    </div>
                </div>
            </x-resellerhub::card>
            <x-resellerhub::card class="p-5 flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-gray-400 font-bold block">قابلیت‌های تفکیکی</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span
                            class="text-2xl font-black text-teal-600 dark:text-teal-400">{{ reseller_fa_num(count($packageFeatures)) }}</span>
                        <span class="text-xs text-gray-400">قابلیت فعال</span>
                    </div>
                </div>
            </x-resellerhub::card>

            {{-- Base Price (dynamic based on price_type) --}}
            <x-resellerhub::card class="p-5 flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-gray-400 font-bold block">{{ $basePriceLabel }}</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        @if($priceType === PackagePriceType::FREE->value)
                            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">رایگان</span>
                        @else
                            <span class="text-xl font-black text-gray-900 dark:text-white">
                                {{ $basePriceValue > 0 ? reseller_fa_num(number_format($basePriceValue)) : '۰' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $basePriceUnit }}</span>
                        @endif
                    </div>
                    @if($hasDiscount)
                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400 block mt-0.5">
                            {{ $discountLabel }}
                        </span>
                    @endif
                </div>
            </x-resellerhub::card>

            {{-- Tenants Count --}}
            <x-resellerhub::card class="p-5 flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs text-gray-400 font-bold block">سامانه‌های فعال</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span
                            class="text-2xl font-black text-gray-900 dark:text-white">{{ reseller_fa_num($tenants->count()) }}</span>
                        <span class="text-xs text-gray-400">سامانه CRM</span>
                    </div>
                </div>
            </x-resellerhub::card>
        </div>

        {{-- PRICING MATRIX CARD --}}
        <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-black text-sm flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تعرفه‌ها و چرخه‌های مالی
                            پکیج</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            نوع فروش: <strong
                                class="text-gray-700 dark:text-gray-300">{{ $priceTypeLabels[$priceType] ?? 'اشتراکی' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($hasDiscount)
                        <span
                            class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-black flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>{{ $discountLabel }}</span>
                        </span>
                    @else
                        <span
                            class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs font-bold">
                            بدون تخفیف
                        </span>
                    @endif
                    <a href="{{ route('reseller.packages.edit', $package->id) }}"
                       class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <span>ویرایش قیمت‌ها</span>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            @if($priceType === PackagePriceType::FREE->value)
                <div
                    class="p-5 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-3">
                    <svg class="w-8 h-8 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="block text-sm font-black text-emerald-700 dark:text-emerald-400">این پکیج به
                            صورت رایگان در اختیار مشتریان قرار می‌گیرد</span>
                        <span class="block text-xs text-emerald-600/80 dark:text-emerald-500/70 mt-0.5">هیچ
                            هزینه‌ای بابت اشتراک یا راه‌اندازی از مشتری دریافت نمی‌شود.</span>
                    </div>
                </div>
            @elseif($priceType === PackagePriceType::ONE_TIME->value)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div
                        class="p-4 rounded-2xl border-2 border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/30 dark:bg-indigo-950/20 space-y-2 shadow-xs ring-1 ring-indigo-500/20">
                        <span class="text-xs font-black text-indigo-900 dark:text-indigo-300 block">قیمت یک‌باره
                            (مادام‌العمر)</span>
                        <div class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                            {{ $package->one_time_price > 0 ? reseller_fa_num(number_format($package->one_time_price)) . ' ' . $currencyLabel : 'تعریف نشده' }}
                        </div>
                        <span class="text-xs text-indigo-600/70 dark:text-indigo-400/70 font-bold block">یک
                            پرداخت، دسترسی همیشگی</span>
                    </div>

                    <div
                        class="p-4 rounded-2xl border {{ $hasDiscount ? 'border-rose-200/80 dark:border-rose-900/40 bg-rose-50/30 dark:bg-rose-950/15' : 'border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/20' }} space-y-2">
                        <span
                            class="text-xs font-bold {{ $hasDiscount ? 'text-rose-900 dark:text-rose-300' : 'text-gray-400 dark:text-gray-500' }} block">تخفیف پکیج</span>
                        <div
                            class="text-base font-black {{ $hasDiscount ? 'text-rose-600 dark:text-rose-400' : 'text-gray-500 dark:text-gray-400' }}">
                            @if($hasDiscount)
                                {{ reseller_fa_num(number_format($discountValue)) . ' ' . $currencyLabel }}
                            @else
                                بدون تخفیف
                            @endif
                        </div>
                        <span
                            class="text-xs {{ $hasDiscount ? 'text-rose-600/70 dark:text-rose-400/70 font-bold' : 'text-gray-400' }} block">
                            {{ $hasDiscount ? 'کسر خودکار در صدور فاکتور' : 'تخفیف پیش‌فرضی تنظیم نشده' }}
                        </span>
                    </div>

                    <div
                        class="p-4 rounded-2xl border border-amber-200/80 dark:border-amber-900/40 bg-amber-50/30 dark:bg-amber-950/15 space-y-2">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300 block">راه‌اندازی
                            اولیه</span>
                        <div class="text-base font-black text-amber-700 dark:text-amber-400">
                            {{ $package->setup_fee > 0 ? reseller_fa_num(number_format($package->setup_fee)) . ' ' . $currencyLabel : 'رایگان' }}
                        </div>
                        <span class="text-xs text-amber-600/70 dark:text-amber-400/70 block">کانفیگ و ستاپ
                            اختصاصی</span>
                    </div>
                </div>
            @else
                @php
                    $activeMonths = $package->getActiveMonths();
                    if (empty($activeMonths) && $priceType === PackagePriceType::SUBSCRIPTION->value) {
                        $activeMonths = [1, 3, 6, 12];
                    }
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    @forelse($activeMonths as $m)
                        @php
                            $pPrice = $package->getPriceForMonths($m);
                            $pHasDiscount = $package->hasActiveDiscountForMonths($m);
                            $pFinalPrice = $pHasDiscount ? $package->getFinalPriceForMonths($m) : $pPrice;
                            $pIsCustomDiscount = $package->hasCustomDiscountForMonths($m);
                            $cycle = BillingCycle::fromMonths($m);
                            $isYearly = ($cycle === BillingCycle::ANNUAL);
                            $label = ResellerHelper::durationLabel($m, true);
                            $note = match($cycle) {
                                BillingCycle::MONTHLY => 'پایه محاسبه دوره‌ای',
                                BillingCycle::QUARTERLY => 'اشتراک فصلی',
                                BillingCycle::SEMI_ANNUAL => 'اشتراک نیم‌سال',
                                BillingCycle::ANNUAL => 'پلن یک‌ساله استاندارد',
                                BillingCycle::BIENNIAL => 'اشتراک ۲ ساله',
                                BillingCycle::TRIENNIAL => 'اشتراک ۳ ساله',
                                default => 'دوره ' . reseller_fa_num($m) . ' ماهه اختصاصی',
                            };
                        @endphp
                        @if($isYearly)
                            <div
                                class="p-4 rounded-2xl border-2 border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/30 dark:bg-indigo-950/20 space-y-2 shadow-xs ring-1 ring-indigo-500/20">
                                <div class="flex items-center justify-between gap-1">
                                    <span
                                        class="text-xs font-black text-indigo-900 dark:text-indigo-300">{{ $label }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded-md bg-indigo-600 text-white text-xs font-black">پیشنهادی</span>
                                </div>
                                @if($pHasDiscount && $pFinalPrice < $pPrice)
                                    <div class="text-xs text-gray-400 line-through tabular-nums">
                                        {{ reseller_fa_num(number_format($pPrice)) }} {{ $currencyLabel }}
                                    </div>
                                    <div class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                                        {{ reseller_fa_num(number_format($pFinalPrice)) }} {{ $currencyLabel }}
                                    </div>
                                @else
                                    <div class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                                        {{ $pPrice > 0 ? reseller_fa_num(number_format($pPrice)) . ' ' . $currencyLabel : 'تعریف نشده' }}
                                    </div>
                                @endif
                                <span
                                    class="text-xs text-indigo-600/70 dark:text-indigo-400/70 font-bold block">
                                    {{ $pIsCustomDiscount ? 'دارای تخفیف اختصاصی دوره' : $note }}
                                </span>
                            </div>
                        @else
                            <div
                                class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-2">
                                <div class="flex items-center justify-between gap-1">
                                    <span
                                        class="text-xs font-bold text-gray-500 dark:text-gray-400 block">{{ $label }}</span>
                                    @if($pIsCustomDiscount && $pHasDiscount)
                                        <span
                                            class="px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-300 text-[9px] font-black">تخفیف اختصاصی</span>
                                    @endif
                                </div>
                                @if($pHasDiscount && $pFinalPrice < $pPrice)
                                    <div class="text-xs text-gray-400 line-through tabular-nums">
                                        {{ reseller_fa_num(number_format($pPrice)) }} {{ $currencyLabel }}
                                    </div>
                                    <div class="text-base font-black text-emerald-600 dark:text-emerald-400">
                                        {{ reseller_fa_num(number_format($pFinalPrice)) }} {{ $currencyLabel }}
                                    </div>
                                @else
                                    <div class="text-base font-black text-gray-900 dark:text-white">
                                        {{ $pPrice > 0 ? reseller_fa_num(number_format($pPrice)) . ' ' . $currencyLabel : 'تعریف نشده' }}
                                    </div>
                                @endif
                                <span class="text-xs text-gray-400 block">{{ $note }}</span>
                            </div>
                        @endif
                    @empty
                        <div
                            class="col-span-full p-5 text-center text-xs text-amber-600 dark:text-amber-400 font-bold bg-amber-50 dark:bg-amber-950/30 rounded-2xl border border-amber-200 dark:border-amber-800/60">
                            هیچ دوره اشتراکی برای این پکیج فعال نشده است.
                        </div>
                    @endforelse

                    <div
                        class="p-4 rounded-2xl border {{ $hasDiscount ? 'border-rose-200/80 dark:border-rose-900/40 bg-rose-50/30 dark:bg-rose-950/15' : 'border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/20' }} space-y-2">
                        <span
                            class="text-xs font-bold {{ $hasDiscount ? 'text-rose-900 dark:text-rose-300' : 'text-gray-400 dark:text-gray-500' }} block">تخفیف پکیج</span>
                        <div
                            class="text-base font-black {{ $hasDiscount ? 'text-rose-600 dark:text-rose-400' : 'text-gray-500 dark:text-gray-400' }}">
                            @if($hasDiscount)
                                {{ reseller_fa_num(number_format($discountValue)) . ' ' . $currencyLabel }}
                            @else
                                بدون تخفیف
                            @endif
                        </div>
                        <span
                            class="text-xs {{ $hasDiscount ? 'text-rose-600/70 dark:text-rose-400/70 font-bold' : 'text-gray-400' }} block">
                            {{ $hasDiscount ? 'کسر خودکار در فاکتور' : 'تنظیم نشده' }}
                        </span>
                    </div>

                    <div
                        class="p-4 rounded-2xl border border-amber-200/80 dark:border-amber-900/40 bg-amber-50/30 dark:bg-amber-950/15 space-y-2">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300 block">راه‌اندازی
                            اولیه</span>
                        <div class="text-base font-black text-amber-700 dark:text-amber-400">
                            {{ $package->setup_fee > 0 ? reseller_fa_num(number_format($package->setup_fee)) . ' ' . $currencyLabel : 'رایگان' }}
                        </div>
                        <span
                            class="text-xs text-amber-600/70 dark:text-amber-400/70 block">کانفیگ و ستاپ اختصاصی</span>
                    </div>
                </div>
            @endif

            {{-- DISCOUNT DETAILS & INVOICING BANNER --}}
            @if($priceType !== PackagePriceType::FREE->value)
                <div
                    class="p-4 rounded-2xl {{ $hasDiscount ? 'bg-gradient-to-r from-rose-50/90 via-rose-50/40 to-transparent dark:from-rose-950/40 dark:via-rose-950/15 dark:to-transparent border border-rose-200/80 dark:border-rose-800/50' : 'bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-10 h-10 rounded-xl {{ $hasDiscount ? 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-300' : 'bg-gray-200/70 dark:bg-gray-800 text-gray-500' }} flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <div
                                class="text-xs sm:text-sm font-black {{ $hasDiscount ? 'text-rose-900 dark:text-rose-200' : 'text-gray-800 dark:text-gray-200' }}">
                                @if($hasDiscount)
                                    تخفیف پکیج: {{ $discountLabel }}
                                @else
                                    وضعیت تخفیف پکیج: بدون تخفیف
                                @endif
                            </div>
                            <p class="text-xs {{ $hasDiscount ? 'text-rose-700/80 dark:text-rose-300/70' : 'text-gray-500 dark:text-gray-400' }} mt-0.5">
                                @if($hasDiscount)
                                    مبلغ این تخفیف به صورت پیش‌فرض در صورتحساب و فاکتور اولیه ساخت فوری این پکیج کسر
                                    می‌شود.
                                @else
                                    هیچ تخفیف پیش‌فرضی برای این پکیج تنظیم نشده است. در صورت نیاز به اعمال تخفیف
                                    می‌توانید از بخش ویرایش پکیج آن را مشخص کنید.
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(!$hasDiscount)
                        <a href="{{ route('reseller.packages.edit', $package->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0 group">
                            <span>افزودن تخفیف به پکیج</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
        </x-resellerhub::card>

        {{-- ACTIVE MODULES & CAPABILITIES (COMPACT 3-COLUMN CARD GRID) --}}
        <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </span>
                        <span>ماژول‌ها و دسترسی‌های فعال این پکیج</span>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                            {{ reseller_fa_num(count($packageModules)) }} ماژول • {{ reseller_fa_num(count($packageFeatures)) }} قابلیت
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        سامانه‌هایی که با این پکیج فعال شوند، به این ماژول‌ها و دسترسی‌های تفکیکی زیرمجموعه دسترسی
                        خواهند داشت.
                    </p>
                </div>

                <a href="{{ route('reseller.packages.edit', $package->id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>ویرایش ماژول‌ها و قابلیت‌ها</span>
                </a>
            </div>

            @if(empty($packageModules))
                <div class="p-8 text-center text-gray-400 text-xs sm:text-sm">
                    هیچ ماژولی در این پکیج تعریف نشده است.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
                    @foreach($packageModules as $slug)
                        @php
                            $mod = $modulesMap[$slug] ?? null;
                            $catMod = $catalogMap[$slug] ?? null;
                            $title = $mod ? $mod->title : $slug;
                            $isAllowed = $mod ? $mod->is_allowed : false;

                            $allModFeatures = $catMod ? collect($catMod['features'] ?? []) : collect();
                            $booleanFeatures = $allModFeatures->where('type', 'feature');
                            $quotaFeatures = $allModFeatures->where('type', 'quota');
                            $activeModFeatures = $booleanFeatures->filter(function($f) use ($packageFeatures) {
                                return in_array($f['key'], $packageFeatures, true);
                            })->values();
                        @endphp
                        <div
                            class="rounded-2xl border border-indigo-100 dark:border-indigo-900/60 bg-white dark:bg-gray-800 shadow-xs overflow-hidden transition-all duration-200 hover:shadow-md">

                            {{-- Card Header --}}
                            <div
                                class="p-4 bg-gray-50/70 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700/60 flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-sm font-black text-gray-900 dark:text-white truncate">{{ $title }}</span>
                                        <span
                                            class="px-1.5 py-0.2 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 shrink-0">فعال</span>
                                    </div>
                                    <span
                                        class="text-xs text-gray-400 block mt-0.5 dir-ltr text-right truncate">{{ $slug }}</span>
                                    @if(!$isAllowed)
                                        <span
                                            class="inline-flex items-center gap-1 text-xs text-red-500 font-bold mt-1">
                                            (خارج از سهمیه فعلی نماینده)
                                        </span>
                                    @endif
                                </div>

                            </div>

                            {{-- Sub-bar: Capabilities count --}}
                            <div
                                class="px-3.5 py-2 bg-indigo-50/30 dark:bg-indigo-950/20 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                <span class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    <span>قابلیت‌های پکیج:</span>
                                </span>
                                <span
                                    class="px-2 py-0.5 rounded-md bg-white dark:bg-gray-800 text-xs font-bold text-indigo-600 dark:text-indigo-400 border border-gray-100 dark:border-gray-700">
                                    {{ reseller_fa_num($activeModFeatures->count()) }} از {{ reseller_fa_num($booleanFeatures->count()) }}
                                </span>
                            </div>

                            {{-- Capabilities Chips Body --}}
                            <div class="p-3.5 space-y-2">
                                @if($activeModFeatures->isNotEmpty())
                                    <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1.5 pr-0.5">
                                        @foreach($activeModFeatures as $feat)
                                            <div
                                                class="flex items-center justify-between gap-2 p-2 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 text-xs">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span
                                                        class="truncate font-medium text-gray-800 dark:text-gray-200">{{ $feat['title'] }}</span>
                                                </div>
                                                <span
                                                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 shrink-0 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md">
                                                    فعال در پکیج
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($booleanFeatures->isEmpty())
                                    <div class="py-3 text-center text-xs text-gray-400 italic">
                                        تمام امکانات این ماژول به صورت کامل فعال است.
                                    </div>
                                @else
                                    <div class="py-3 text-center text-xs text-amber-500 font-bold">
                                        هیچ قابلیت تفکیکی برای این ماژول انتخاب نشده است.
                                    </div>
                                @endif
                            </div>

                            {{-- Quotas & Numeric Limits Footer --}}
                            @if($quotaFeatures->isNotEmpty())
                                <div
                                    class="px-3.5 py-2.5 bg-gray-50/80 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700/60 space-y-1.5">
                                    @foreach($quotaFeatures as $q)
                                        @php
                                            $qVal = $package->getLimit($q['key']);
                                        @endphp
                                        <div class="flex items-center justify-between text-xs gap-2">
                                            <span class="text-gray-600 dark:text-gray-400 font-medium truncate">{{ $q['title'] }}:</span>
                                            @if(!is_null($qVal))
                                                <span
                                                    class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200/50 shrink-0">
                                                    حداکثر {{ reseller_fa_num($qVal) }} {{ $q['unit'] ?? 'عدد' }}
                                                </span>
                                            @else
                                                <span
                                                    class="text-xs text-gray-400 font-bold shrink-0">نامحدود</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(!is_null($package->getLimit($slug)))
                                <div
                                    class="px-3.5 py-2 bg-gray-50/80 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                    <span class="text-gray-500 font-bold flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <span>سقف مصرف (Limit):</span>
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-xs font-black text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                        حداکثر {{ reseller_fa_num($package->getLimit($slug)) }} عدد
                                    </span>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif
        </x-resellerhub::card>

        {{-- TENANTS CREATED WITH THIS PACKAGE --}}
        <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span
                            class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </span>
                        <span>سامانه‌های CRM دارای این پکیج ({{ reseller_fa_num($tenants->count()) }})</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        لیست مشتریان و کلینیک‌هایی که با ترکیب ماژول‌های این پکیج راه‌اندازی شده‌اند.
                    </p>
                </div>

                <button type="button" @click="openQuickProvision()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-500/20 transition cursor-pointer self-start sm:self-auto">
                    <svg class="w-3.5 h-3.5 text-white-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span>ساخت فوری CRM با این پکیج</span>
                </button>
            </div>

            @if($tenants->isEmpty())
                <div class="py-12 text-center space-y-4">
                    <div
                        class="w-16 h-16 rounded-3xl bg-gray-50 dark:bg-gray-900/60 text-gray-400 flex items-center justify-center mx-auto border border-gray-100 dark:border-gray-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="space-y-1 max-w-md mx-auto">
                        <h3 class="text-sm font-black text-gray-800 dark:text-gray-200">هنوز هیچ سامانه‌ای با این پکیج
                            ایجاد نشده است</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            می‌توانید با استفاده از دکمه زیر، در عرض چند ثانیه سامانه مشتری جدید را با این پکیج
                            راه‌اندازی نمایید.
                        </p>
                    </div>
                    <button type="button" @click="openQuickProvision()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-300 hover:text-white dark:hover:text-white text-xs font-black transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>راه‌اندازی فوری اولین CRM این پکیج</span>
                    </button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs sm:text-sm">
                        <thead class="bg-gray-50/75 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs">
                        <tr>
                            <th scope="col" class="py-3.5 px-4 font-bold">نام کسب‌وکار / سازمان</th>
                            <th scope="col" class="py-3.5 px-4 font-bold">آدرس اختصاصی سامانه</th>
                            <th scope="col" class="py-3.5 px-4 font-bold">مدیر ارشد سامانه</th>
                            <th scope="col" class="py-3.5 px-4 font-bold text-center">وضعیت</th>
                            <th scope="col" class="py-3.5 px-4 font-bold text-center">اعتبار لایسنس</th>
                            <th scope="col" class="py-3.5 px-4 font-bold text-left">عملیات</th>
                        </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-300">
                        @foreach($tenants as $t)
                            @php
                                $tenantManager = app(\App\Tenancy\TenantManager::class);
                                $tenantUrl = route('reseller.tenants.login-as', $t->id);
                                $displayUrl = $tenantManager->getTenantDisplayUrl($t);
                                $isExpired = $t->isExpired();
                                $isActive = $t->status === 'active' && !$isExpired;
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/20 transition">
                                <td class="py-4 px-4">
                                    <div class="font-black text-gray-900 dark:text-white">
                                        <a href="{{ route('reseller.tenants.show', $t->id) }}"
                                           class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                            {{ $t->name }}
                                        </a>
                                    </div>
                                    <span class="text-xs text-gray-400 mt-0.5 block">
                                        شناسه: #{{ reseller_fa_num($t->id) }} &bull; ایجاد شده در: {{ $t->created_at ? reseller_fa_num(Jalalian::fromCarbon($t->created_at)->format('Y/m/d')) : '-' }}
                                    </span>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="inline-flex items-center gap-1.5">
                                        <form method="POST" action="{{ $tenantUrl }}" target="_blank" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline dir-ltr truncate max-w-[200px]"
                                                    title="ورود مستقیم به پنل CRM">
                                                {{ $displayUrl }}
                                            </button>
                                        </form>
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-xs">
                                    <div class="font-bold text-gray-800 dark:text-gray-200">
                                        {{ $t->subAccount?->name ?? '—' }}
                                    </div>
                                    <div class="text-gray-400 text-xs mt-0.5">
                                        {{ $t->subAccount?->mobile ? reseller_fa_num($t->subAccount->mobile) : ($t->subAccount?->email ?? '—') }}
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    @if($isActive)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            فعال
                                        </span>
                                    @elseif($isExpired)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            منقضی
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            معلق
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-center text-xs">
                                    @if($t->plan_expires_at)
                                        <span class="font-bold text-gray-700 dark:text-gray-300 block">
                                            {{ reseller_fa_num(Jalalian::fromCarbon($t->plan_expires_at)->format('Y/m/d')) }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            ({{ reseller_fa_num($t->plan_expires_at->diffForHumans()) }})
                                        </span>
                                    @else
                                        <span class="text-gray-400">نامحدود</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-left">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ $tenantUrl }}" target="_blank"
                                           class="p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-xl transition cursor-pointer"
                                           title="ورود مستقیم به پنل سامانه">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('reseller.tenants.show', $t->id) }}"
                                           class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition cursor-pointer"
                                           title="مشاهده جزئیات سامانه">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('reseller.tenants.edit', $t->id) }}"
                                           class="p-2 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition cursor-pointer"
                                           title="ویرایش ماژول‌ها و اشتراک سامانه">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-resellerhub::card>

        {{-- OTHER SYSTEM MODULES NOT INCLUDED --}}
        @php
            $excludedModules = $availableModules->filter(function($m) use ($packageModules) {
                return !in_array($m->slug, $packageModules);
            });
        @endphp

        @if($excludedModules->count() > 0)
            <x-resellerhub::card class="p-6 sm:p-8 space-y-6" x-data="{ expanded: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                            <span>سایر ماژول‌های سیستم که در این پکیج غیرفعال هستند ({{ reseller_fa_num($excludedModules->count()) }})</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            این ماژول‌ها همراه با این پکیج فعال نیستند و در صورت نیاز در زمان ساخت به صورت دستی قابل
                            افزودن خواهند بود.
                        </p>
                    </div>

                    <button type="button" @click="expanded = !expanded"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 cursor-pointer">
                        <span x-text="expanded ? 'بستن لیست' : 'نمایش لیست'"></span>
                    </button>
                </div>

                <div x-show="expanded" x-cloak
                     class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                    @foreach($excludedModules as $m)
                        <div
                            class="p-3.5 rounded-2xl border bg-gray-50/40 dark:bg-gray-900/20 border-gray-100 dark:border-gray-800 flex items-center justify-between opacity-70">
                            <div class="min-w-0 pr-1">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block truncate">
                                    {{ $m->title }}
                                </span>
                                <span class="text-xs text-gray-400 block mt-0.5 dir-ltr text-right truncate">
                                    {{ $m->slug }}
                                </span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 shrink-0">غیرفعال</span>
                        </div>
                    @endforeach
                </div>
            </x-resellerhub::card>
        @endif

        {{-- Modals --}}
        @include('resellerhub::user.packages.partials.status-modal')
        @include('resellerhub::user.packages.partials.delete-modal')
        @include('resellerhub::user.packages.partials.quick-provision-modal')
        @include('resellerhub::partials.toast')

    </div>
@endsection
