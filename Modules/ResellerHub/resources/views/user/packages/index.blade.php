@php
    use Modules\ResellerHub\App\Enums\QuotaKey;use Modules\ResellerHub\App\Models\ResellerSetting;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Morilog\Jalali\Jalalian;
@endphp
@extends('layouts.user')

@section('title', 'مدیریت پکیج‌های ماژول')

@php
    $resellerId = $reseller->id ?? (auth()->user()?->reseller?->id ?? 0);
    $currency = $currency ?? ResellerSetting::getCurrencyForReseller($resellerId);
    $currencyLabel = $currencyLabel ?? ResellerSetting::getCurrencyLabelForReseller($resellerId);
    $priceTypeLabels = reseller_package_meta('price_type', 'badges');

    $modulesMap = $availableModules->keyBy('slug');

    $resellerData = [
        'id' => $reseller->id,
        'name' => $reseller->company_name ?: ($reseller->user?->name ?? 'نماینده #' . $reseller->id),
    ];

    $packagesJson = $packages->map(function ($p) use ($modulesMap) {
        $pMods = $p->modules ?? [];
        $pFeats = $p->features ?? [];
        $activePeriods = (is_array($p->active_periods) && !empty($p->active_periods))
            ? $p->active_periods
            : ['monthly_price', 'quarterly_price', 'semi_annual_price', 'yearly_price'];

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug ?? null,
            'description' => $p->description ?? '',
            'icon' => $p->icon ?: 'cube',
            'color' => $p->color ?: 'indigo',
            'color_hex' => $p->color_hex,
            'modules' => $pMods,
            'modules_count' => count($pMods),
            'features' => $pFeats,
            'features_count' => count($pFeats),
            'limits' => $p->limits ?? [],
            'tenants_count' => (int)($p->tenants_count ?? $p->tenants()->count()),
            'is_active' => (bool)$p->isActive(),

            'price_type' => $p->isFree() ? 'free' : ($p->price_type ?? 'subscription'),
            'is_free' => (bool)$p->isFree(),
            'period_prices' => $p->period_prices ?? [],
            'active_periods' => $activePeriods,

            'monthly_price' => (int)($p->monthly_price ?? 0),
            'quarterly_price' => (int)($p->quarterly_price ?? 0),
            'semi_annual_price' => (int)($p->semi_annual_price ?? 0),
            'yearly_price' => (int)($p->yearly_price ?? 0),
            'one_time_price' => (int)($p->one_time_price ?? 0),
            'setup_fee' => (int)($p->setup_fee ?? 0),

            'final_monthly_price' => (int)($p->final_monthly_price ?? 0),
            'final_quarterly_price' => (int)($p->final_quarterly_price ?? 0),
            'final_semi_annual_price' => (int)($p->final_semi_annual_price ?? 0),
            'final_yearly_price' => (int)($p->final_yearly_price ?? 0),
            'final_one_time_price' => (int)($p->final_one_time_price ?? 0),
            'final_setup_fee' => (int)($p->final_setup_fee ?? 0),

            'discount_type' => 'amount',
            'discount_value' => (int)($p->discount_value ?? 0),

            'modules_titles' => collect($pMods)->map(fn($s) => $modulesMap[$s]->title ?? $s)->values(),
        ];
    })->values();

    $modulesListForJs = $availableModules->map(function ($m) {
        return [
            'slug' => $m->slug,
            'name' => $m->name,
            'title' => $m->title,
            'is_allowed' => $m->is_allowed,
        ];
    })->values();

    $resellerSettings = ResellerSetting::allForReseller($resellerId);
    $financialSettings = $financialSettings ?? $resellerSettings;
    $subAccountsJson = collect($subAccounts ?? [])->values();
    $invoiceNumbering = $invoiceNumbering ?? [
        'prefix'   => (string)($financialSettings['invoice_prefix'] ?? 'INV-'),
        'middle'   => (string)($financialSettings['invoice_middle_prefix'] ?? Jalalian::now()->getYear()),
        'suffix'   => (string)($financialSettings['invoice_suffix'] ?? ''),
        'padding'  => (int)($financialSettings['invoice_padding'] ?? 4),
        'auto'     => !empty($financialSettings['invoice_auto']),
        'tax_mode' => (string)($financialSettings['tax_mode'] ?? 'invoice'),
        'tax_rate' => (float)($financialSettings['default_tax_rate'] ?? 10),
        'terms'    => (string)($financialSettings['invoice_terms'] ?? ''),
        'footer'   => (string)($financialSettings['invoice_footer_note'] ?? ''),
    ];
@endphp

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')
    @include('partials.jalali-date-picker')

    <script>
        window.packagesIndexConfig = {
            reseller: {!! json_encode($resellerData) !!},
            packages: {!! json_encode($packagesJson, JSON_UNESCAPED_UNICODE) !!},
            modules: {!! json_encode($modulesListForJs, JSON_UNESCAPED_UNICODE) !!},
            catalog: {!! json_encode($catalog ?? [], JSON_UNESCAPED_UNICODE) !!},
            subAccounts: {!! json_encode($subAccountsJson, JSON_UNESCAPED_UNICODE) !!},
            quickSubAccountUrl: "{{ route('reseller.sub-accounts.quick-store') }}",
            invoiceNumbering: {!! json_encode($invoiceNumbering) !!},
            currencyLabel: '{{ $currencyLabel }}',
            baseDomain: '{{ $baseDomain ?? request()->getHost() }}',
            defaultDuration: '{{ $defaultDuration ?? 12 }}',
            isSuperAdmin: {{ ($isSuperAdmin ?? false) ? 'true' : 'false' }},
            resellersList: {!! json_encode($resellersList ?? []) !!},
            storeTenantUrl: "{{ route('reseller.tenants.store') }}",
            checkSubdomainUrl: "{{ route('reseller.tenants.check-subdomain') }}",
            destroyPackageUrl: "{{ route('reseller.packages.destroy', ':id') }}",
            csrfToken: '{{ csrf_token() }}'
        };

        function packagesIndexManager() {
            const config = window.packagesIndexConfig || {};
            const uiHelpers = window.resellerUiHelpers ? window.resellerUiHelpers() : {};
            return {
                ...uiHelpers,
                reseller: config.reseller || {},
                packages: config.packages || [],
                modules: config.modules || [],
                catalog: config.catalog || [],
                baseDomain: (config.baseDomain || (typeof window !== 'undefined' ? window.location.hostname : '')).replace(/:\d+$/, ''),
                defaultDuration: config.defaultDuration || '12',
                isSuperAdmin: Boolean(config.isSuperAdmin),
                resellersList: config.resellersList || [],
                storeTenantUrl: config.storeTenantUrl || '',
                checkSubdomainUrl: config.checkSubdomainUrl || '',
                destroyPackageUrl: config.destroyPackageUrl || '',
                csrfToken: config.csrfToken || '',

                cleanBaseDomain() {
                    let d = this.baseDomain || (typeof window !== 'undefined' ? window.location.hostname : '');
                    return d ? d.replace(/:\d+$/, '') : '';
                },

                // Delete Modal State
                deleteModalOpen: false,
                packageToDelete: null,
                isDeleting: false,
                deleteActionUrl: '',
                deleteErrorMsg: '',

                openDeleteModalById(pkgId) {
                    const pkg = (this.packages || []).find(p => p.id === pkgId);
                    if (pkg) {
                        this.openDeleteModal(pkg);
                    }
                },

                openDeleteModal(pkg) {
                    this.packageToDelete = pkg;
                    this.deleteActionUrl = this.destroyPackageUrl.replace(':id', pkg.id);
                    this.deleteErrorMsg = '';
                    this.isDeleting = false;
                    this.deleteModalOpen = true;
                },

                closeDeleteModal() {
                    if (this.isDeleting) return;
                    this.deleteModalOpen = false;
                    this.packageToDelete = null;
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

                        window.location.reload();
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
                    if (pkg) this.packageToToggle = pkg;
                    this.statusErrorMsg = '';
                    this.isTogglingStatus = false;
                    this.statusModalOpen = true;
                },
                openStatusModalById(id) {
                    const found = this.packages.find(p => p.id === id);
                    if (found) {
                        this.openStatusModal(found);
                    }
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
                selectedPackage: null,
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
                    invoice_status: 'unpaid'
                },

                // Subdomain validation
                subdomainChecking: false,
                subdomainStatus: 'idle', // idle, checking, available, unavailable
                subdomainMsg: '',
                subdomainTimer: null,

                // Invoice Management State
                invoiceNumbering: config.invoiceNumbering || {},
                currencyLabel: config.currencyLabel || '{{ $currencyLabel }}',
                invoiceNumberAuto: Boolean(config.invoiceNumbering && config.invoiceNumbering.auto),
                invoiceNumberOverride: '',
                taxRateLocked: true,
                invoiceTaxRate: parseFloat((config.invoiceNumbering && config.invoiceNumbering.tax_rate) || 0),
                invoiceIssueDate: '',
                invoiceDueDate: '',
                invoiceTerms: (config.invoiceNumbering && config.invoiceNumbering.terms) || '',
                invoiceTermsLocked: true,
                invoiceNotes: (config.invoiceNumbering && config.invoiceNumbering.footer) || '',
                invoiceDiscountAmount: 0,
                invoiceDiscountDisplay: '',
                invoiceItems: [],
                quickInvoiceItems: [],

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

                // Duration Getters & Actions
                get durationOptions() {
                    const fn = this.resolvePackageDurationOptions || window.resolvePackageDurationOptions;
                    if (fn) {
                        return fn(this.selectedPackage, {
                            fallback: this.durationList,
                            oneTimeValue: '1200',
                            oneTimeLabel: 'مادام‌العمر (یک‌باره)',
                        });
                    }
                    return this.durationList;
                },

                get durationLabel() {
                    const opt = this.durationOptions.find(o => String(o.value) === String(this.form.expires_in_months));
                    return opt ? opt.label : (this.faNum(this.form.expires_in_months) + ' ماهه');
                },

                selectPackageDuration(val) {
                    this.form.expires_in_months = String(val);
                    this.syncQuickInvoiceItems();
                },

                // Invoice Number & Tax Helpers
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
                    if (parts.length === 3) {
                        this.invoiceIssueDate = parts[0] + '/' + parts[1] + '/' + parts[2];
                    }
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

                parsePriceInput(val) {
                    if (!val) return 0;
                    const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    const a = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                    let str = String(val);
                    for (let i = 0; i < 10; i++) str = str.replaceAll(p[i], i).replaceAll(a[i], i);
                    return parseInt(str.replace(/[^0-9]/g, '')) || 0;
                },

                syncQuickInvoiceItems() {
                    const pkg = this.selectedPackage;
                    if (!pkg) return;

                    const customItems = (this.invoiceItems || []).filter(it => it.item_type === 'custom');

                    const items = [];
                    const isOneTime = pkg.price_type === 'one_time';
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

                buildInvoicePayload() {
                    if (!this.form.issue_invoice) {
                        return {issue_invoice: false};
                    }
                    return {
                        issue_invoice: true,
                        invoice_auto: this.invoiceNumberAuto,
                        invoice_number: this.invoiceNumberAuto ? '' : (this.invoiceNumberOverride || '').trim(),
                        tax_from_settings: Boolean(this.taxRateLocked),
                        invoice_tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                        invoice_tax_mode: 'invoice',
                        invoice_issue_date: this.invoiceIssueDate,
                        invoice_due_date: this.invoiceDueDate,
                        invoice_status: this.form.invoice_status || 'unpaid',
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

                // Opening & Submitting Provision
                openQuickProvisionById(pkgId) {
                    const pkg = (this.packages || []).find(p => p.id === pkgId);
                    if (pkg) {
                        this.openQuickProvision(pkg);
                    }
                },

                openQuickProvision(pkg) {
                    this.selectedPackage = pkg;
                    this.createdTenant = null;
                    this.createdInvoice = null;
                    this.createdOrder = null;
                    this.generalError = '';
                    this.formErrors = {};
                    this.copiedUrl = false;
                    this.copiedCreds = false;
                    this.submitting = false;

                    // Reset Buyer Selection
                    this.selectedSubAccount = null;
                    this.subAccountSearchQuery = '';

                    this.form.name = '';
                    this.form.subdomain = '';
                    this.form.sub_account_id = '';
                    this.form.reseller_id = (this.reseller && this.reseller.id) ? this.reseller.id : '';
                    this.form.admin_name = '';
                    this.form.admin_mobile = '';
                    this.form.admin_email = '';
                    this.form.modules = (pkg && pkg.modules) ? [...pkg.modules] : [];

                    const priceType = (pkg && pkg.price_type) ? pkg.price_type : 'subscription';

                    if (priceType === 'free') {
                        this.form.issue_invoice = false;
                        this.form.invoice_status = 'unpaid';
                        this.form.expires_in_months = '12';
                    } else if (priceType === 'one_time') {
                        this.form.issue_invoice = true;
                        this.form.invoice_status = 'unpaid';
                        this.form.expires_in_months = '1200';
                    } else {
                        this.form.issue_invoice = true;
                        this.form.invoice_status = 'unpaid';
                        const availableDurations = this.durationOptions;
                        const preferred = availableDurations.find(d => String(d.value) === String(this.defaultDuration));
                        this.form.expires_in_months = preferred
                            ? preferred.value
                            : (availableDurations[0] ? availableDurations[0].value : '12');
                    }

                    this.subdomainStatus = 'idle';
                    this.subdomainMsg = '';

                    // Initialize Invoice
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
                    this.selectedPackage = null;
                    this.createdTenant = null;
                    this.createdInvoice = null;
                    this.createdOrder = null;
                },

                autoSuggestSubdomain() {
                    if (this.createdTenant) return;
                    const name = (this.form.name || '').trim();
                    if (!name) return;

                    const suggest = this.suggestSubdomain || window.suggestSubdomain;
                    const slug = suggest ? suggest(name) : '';

                    if (slug && (!this.form.subdomain || this.form.subdomain.length < 3)) {
                        this.form.subdomain = slug.slice(0, 30);
                        this.checkSubdomain();
                    }
                },

                onSubdomainInput() {
                    this.checkSubdomain();
                },

                checkSubdomain() {
                    clearTimeout(this.subdomainTimer);
                    const sub = (this.form.subdomain || '').toLowerCase().trim();

                    if (!sub || sub.length < 3) {
                        this.subdomainStatus = 'idle';
                        this.subdomainMsg = '';
                        return;
                    }

                    if (!/^[a-z0-9]+(-[a-z0-9]+)*$/.test(sub)) {
                        this.subdomainStatus = 'unavailable';
                        this.subdomainMsg = 'فقط حروف انگلیسی، اعداد و خط تیره مجاز است';
                        return;
                    }

                    this.subdomainChecking = true;
                    this.subdomainStatus = 'checking';

                    this.subdomainTimer = setTimeout(async () => {
                        try {
                            const res = await fetch(`${this.checkSubdomainUrl}?subdomain=${encodeURIComponent(sub)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const data = await res.json();
                            this.subdomainChecking = false;
                            if (data.available) {
                                this.subdomainStatus = 'available';
                                this.subdomainMsg = data.message || 'سابدامین آزاد است';
                            } else {
                                this.subdomainStatus = 'unavailable';
                                this.subdomainMsg = data.message || 'این سابدامین غیرقابل استفاده است';
                            }
                        } catch (e) {
                            this.subdomainChecking = false;
                            this.subdomainStatus = 'idle';
                        }
                    }, 350);
                },

                async submitQuickProvision() {
                    if (this.submitting) return;

                    this.generalError = '';
                    this.formErrors = {};

                    if (!this.form.sub_account_id) {
                        this.formErrors.sub_account_id = ['انتخاب حساب خریدار (زیرمجموعه) الزامی است.'];
                        this.generalError = 'لطفاً ابتدا خریدار این سامانه را انتخاب کنید یا خریدار جدید ثبت نمایید.';
                        return;
                    }

                    if (this.subdomainStatus === 'unavailable' || this.subdomainChecking) {
                        this.generalError = 'لطفاً یک سابدامین معتبر و آزاد انتخاب فرمایید.';
                        return;
                    }

                    this.submitting = true;

                    try {
                        const invoicePayload = this.buildInvoicePayload();

                        const payload = {
                            name: this.form.name,
                            subdomain: this.form.subdomain,
                            sub_account_id: this.form.sub_account_id,
                            reseller_id: (this.reseller && this.reseller.id) ? this.reseller.id : this.form.reseller_id,
                            package_id: (this.selectedPackage && this.selectedPackage.id) ? this.selectedPackage.id : null,
                            admin_name: this.form.admin_name,
                            admin_mobile: this.normalizeDigits(this.form.admin_mobile),
                            admin_email: this.form.admin_email,
                            expires_in_months: this.form.expires_in_months,
                            modules: this.form.modules,
                            features: (this.selectedPackage && this.selectedPackage.features) ? this.selectedPackage.features : [],
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
                            if (data.errors) {
                                this.formErrors = data.errors;
                            }
                            this.generalError = data.message || 'خطا در ثبت سامانه. لطفاً فیلدها را بررسی فرمایید.';
                            return;
                        }

                        // Success!
                        this.submitting = false;
                        this.createdTenant = data.tenant;
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

                renderIcon(key, sizeClass) {
                    return window.renderIndustryIcon ? window.renderIndustryIcon(key, sizeClass) : '';
                },

                getColorHex(color) {
                    return window.getColorHex ? window.getColorHex(color) : '#4f46e5';
                },

                getColorClass(color) {
                    return window.getColorClass ? window.getColorClass(color) : 'bg-indigo-600';
                },

                getColorLightClass(color) {
                    return window.getColorLightClass ? window.getColorLightClass(color) : (window.getColorClass ? window.getColorClass('indigo') : '');
                },

                faNum(str) {
                    return window.faNum ? window.faNum(str) : (str ?? '');
                },

                durationList: window.resellerStandardDurations || [],

                formatPrice(num) {
                    return window.formatAmount ? window.formatAmount(num) : this.faNum(parseInt(num || 0).toLocaleString('en-US'));
                },

                getPackagePriceForDuration(durationMonths = null) {
                    const pkg = this.selectedPackage;
                    if (!pkg) return 0;

                    if (pkg.price_type === 'free') {
                        return 0;
                    }

                    if (pkg.price_type === 'one_time') {
                        return parseInt(pkg.final_one_time_price) || 0;
                    }

                    const months = parseInt(durationMonths !== null ? durationMonths : (this.form.expires_in_months || 12), 10);

                    // Dynamic period_prices support
                    if (pkg.period_prices && typeof pkg.period_prices === 'object') {
                        const directPrice = parseInt(pkg.period_prices[months] || pkg.period_prices[String(months)]) || 0;
                        if (directPrice > 0) {
                            return directPrice;
                        }
                    }

                    const monthly = parseInt(pkg.final_monthly_price) || 0;
                    const quarterly = parseInt(pkg.final_quarterly_price) || 0;
                    const semiAnnual = parseInt(pkg.final_semi_annual_price) || 0;
                    const yearly = parseInt(pkg.final_yearly_price) || 0;

                    if (months === 1) {
                        return monthly > 0 ? monthly : (yearly > 0 ? Math.round(yearly / 12) : 0);
                    } else if (months === 3) {
                        if (quarterly > 0) return quarterly;
                        if (monthly > 0) return monthly * 3;
                        return yearly > 0 ? Math.round((yearly / 12) * 3) : 0;
                    } else if (months === 6) {
                        if (semiAnnual > 0) return semiAnnual;
                        if (monthly > 0) return Math.round(monthly * 6 * 0.95);
                        return yearly > 0 ? Math.round(yearly / 2) : 0;
                    } else if (months === 12) {
                        if (yearly > 0) return yearly;
                        if (monthly > 0) return Math.round(monthly * 12 * 0.85);
                        return 0;
                    } else if (months === 24) {
                        const y = yearly > 0 ? yearly : (monthly * 12);
                        return Math.round(y * 2 * 0.9);
                    } else if (months === 36) {
                        const y = yearly > 0 ? yearly : (monthly * 12);
                        return Math.round(y * 3 * 0.85);
                    } else {
                        if (monthly > 0) return monthly * months;
                        if (yearly > 0) return Math.round((yearly / 12) * months);
                        return 0;
                    }
                },

                getPackageSetupFee() {
                    if (!this.selectedPackage) return 0;
                    if (this.selectedPackage.price_type === 'free') return 0;
                    return parseInt(this.selectedPackage.final_setup_fee) || 0;
                },

                getActivePackageDiscount() {
                    const pkg = this.selectedPackage;
                    if (!pkg || pkg.price_type === 'free') return 0;
                    const basePrice = this.getPackagePriceForDuration();
                    const val = parseFloat(pkg.discount_value) || 0;
                    if (val <= 0 || basePrice <= 0) return 0;
                    return Math.min(basePrice, Math.round(val));
                },

                getPackageTotalAmount() {
                    const base = this.getPackagePriceForDuration() + this.getPackageSetupFee();
                    const discount = this.getActivePackageDiscount();
                    return Math.max(0, base - discount);
                },

                getDurationLabel(months) {
                    return window.getDurationLabel ? window.getDurationLabel(months) : ((this.faNum(months) || months) + ' ماهه');
                },

                getDurationSubtitle(months) {
                    return window.getDurationSubtitle ? window.getDurationSubtitle(months) : 'اشتراک دوره‌ای';
                }
            };
        }
    </script>

    <div x-data="packagesIndexManager()" class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        {{-- Success/Error Alerts --}}
        @if(session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-bold flex items-center gap-2 shadow-xs">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div
                class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-300 text-xs sm:text-sm font-bold flex items-center gap-2 shadow-xs">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                    <span>مدیریت پکیج‌های ماژول</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    ماژول‌های مورد نیاز هر صنف یا مشتری (مانند دندانپزشکان، املاک یا شرکت‌ها) را با آیکون و رنگ اختصاصی
                    پکیج
                    کنید تا هنگام ساخت CRM صرفاً با یک کلیک فعال شوند.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('reseller.packages.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-95 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ساخت پکیج جدید</span>
                </a>

                <a href="{{ route('reseller.tenants.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <span>ساخت آزاد CRM</span>
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Packages Content Area --}}
        @if(($totalCount ?? 0) === 0)
            <x-resellerhub::card class="p-12 text-center space-y-5">
                <div
                    class="w-20 h-20 rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="max-w-md mx-auto space-y-2">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">هنوز هیچ پکیجی نساخته‌اید</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        برای سرعت بخشیدن به راه‌اندازی سامانه‌ها، می‌توانید ماژول‌های مخصوص یک صنف (مثلاً نوبت‌دهی و
                        پرونده برای دندانپزشکان) را به عنوان پکیج با رنگ و آیکون دلخواه تعریف کنید.
                    </p>
                </div>
                <a href="{{ route('reseller.packages.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ساخت اولین پکیج (مثلاً پکیج دندانپزشکی)</span>
                </a>
            </x-resellerhub::card>
        @else
            {{-- Summary Strip (KPI Stat Cards consistent with invoices and orders) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Card 1: Total Packages --}}
                <div
                    class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center gap-4">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">کل پکیج‌های ماژول</span>
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span
                                class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ reseller_fa_num($totalCount ?? count($packages)) }}</span>
                            <span class="text-xs font-medium text-gray-400 ms-1">پکیج</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Active Packages --}}
                <div
                    class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-emerald-50/60 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 shadow-sm flex items-center gap-4">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <span
                            class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">پکیج‌های فعال (در دسترس)</span>
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span
                                class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums break-all">{{ reseller_fa_num($activeCount ?? 0) }}</span>
                            <span class="text-xs font-medium text-emerald-600/80 ms-1">آماده سفارش</span>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Inactive Packages --}}
                <div
                    class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-amber-50/60 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 shadow-sm flex items-center gap-4">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <span
                            class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wide block mb-1 truncate">پکیج‌های غیرفعال</span>
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span
                                class="text-xl xl:text-2xl font-black text-amber-700 dark:text-amber-400 tabular-nums break-all">{{ reseller_fa_num($inactiveCount ?? 0) }}</span>
                            <span class="text-xs font-medium text-amber-500/80 ms-1">مخفی از خرید جدید</span>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Connected Tenants --}}
                <div
                    class="relative overflow-hidden rounded-3xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center gap-4">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">سامانه‌های فعال متصل</span>
                        <div class="flex flex-wrap items-baseline gap-1">
                            <span
                                class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ reseller_fa_num($connectedTenantsCount ?? 0) }}</span>
                            <span class="text-xs font-medium text-gray-400 ms-1">سامانه CRM</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Form (100% Consistent with invoices, orders, and subaccounts) --}}
            <form method="GET" action="{{ route('reseller.packages.index') }}"
                  class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                    {{-- Search Input --}}
                    <div class="relative lg:col-span-4">
                        <div
                            class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="جستجو در نام و توضیحات پکیج..."
                               class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                    </div>

                    {{-- Status Filter --}}
                    <div class="lg:col-span-2">
                        <select name="status" aria-label="فیلتر وضعیت"
                                class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-3 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                            <option value="">همه وضعیت‌ها ({{ reseller_fa_num($totalCount ?? count($packages)) }})
                            </option>
                            <option value="active" @selected(request('status') === 'active')>فقط فعال‌ها
                                ({{ reseller_fa_num($activeCount ?? 0) }})
                            </option>
                            <option value="inactive" @selected(request('status') === 'inactive')>فقط غیرفعال‌ها
                                ({{ reseller_fa_num($inactiveCount ?? 0) }})
                            </option>
                        </select>
                    </div>

                    {{-- Price Type Filter --}}
                    <div class="lg:col-span-2">
                        <select name="price_type" aria-label="انتخاب price_type"
                                class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-3 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                            <option value="">همه مدل‌های تعرفه</option>
                            <option value="subscription" @selected(request('price_type') === 'subscription')>اشتراکی /
                                دوره‌ای
                            </option>
                            <option value="one_time" @selected(request('price_type') === 'one_time')>یک‌بار پرداخت
                            </option>
                            <option value="free" @selected(request('price_type') === 'free')>رایگان</option>
                        </select>
                    </div>

                    {{-- Sort Filter --}}
                    <div class="lg:col-span-2">
                        <select name="sort_by" aria-label="مرتب‌سازی"
                                class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-3 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                            <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>
                                جدیدترین (پیش‌فرض)
                            </option>
                            <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                            <option value="name_asc" @selected(request('sort_by') === 'name_asc')>نام پکیج (الفبا
                                صعودی)
                            </option>
                            <option value="name_desc" @selected(request('sort_by') === 'name_desc')>نام پکیج (الفبا
                                نزولی)
                            </option>
                            <option value="price_desc" @selected(request('sort_by') === 'price_desc')>تعرفه ماهانه
                                (بیشترین)
                            </option>
                            <option value="price_asc" @selected(request('sort_by') === 'price_asc')>تعرفه ماهانه
                                (کمترین)
                            </option>
                        </select>
                    </div>

                    @if(request('sort_order'))
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif

                    {{-- Submit & Reset Buttons --}}
                    <div class="flex items-center gap-1.5 lg:col-span-2 justify-end">
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>اعمال فیلتر</span>
                        </button>

                        @if(request()->hasAny(['search', 'status', 'price_type', 'sort_by', 'sort_order']))
                            <a href="{{ route('reseller.packages.index') }}"
                               class="inline-flex items-center justify-center p-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors shrink-0"
                               title="پاکسازی فیلترها">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Packages Grid or No-Results Card --}}
            @if($packages->isEmpty())
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-12 text-center space-y-4 backdrop-blur-xl">
                    <div
                        class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ پکیجی با مشخصات انتخابی یافت
                            نشد</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">عبارت جستجو یا فیلترهای انتخابی را تغییر
                            دهید یا پاک کنید.</p>
                    </div>
                    <div>
                        <a href="{{ route('reseller.packages.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold transition cursor-pointer">
                            <span>پاکسازی فیلترها و مشاهده همه</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                    @foreach($packages as $pkg)
                        @php
                            $pkgModules = $pkg->modules ?? [];
                            $pkgFeatCount = count($pkg->features ?? []);
                            $isFree = $pkg->isFree();
                            $pkgPriceType = $isFree ? 'free' : ($pkg->price_type ?? 'subscription');
                            $ptLabel = $priceTypeLabels[$pkgPriceType] ?? $priceTypeLabels['subscription'];
                            $activePeriods = (is_array($pkg->active_periods) && !empty($pkg->active_periods))
                                ? $pkg->active_periods
                                : ['monthly_price', 'quarterly_price', 'semi_annual_price', 'yearly_price'];

                            $modsShown = array_slice($pkgModules, 0, 4);
                            $modsRest = max(0, count($pkgModules) - 4);
                            $limitsAll = is_array($pkg->limits ?? null) ? $pkg->limits : [];
                            $limitsShown = array_slice($limitsAll, 0, 2, true);
                            $limitsRest = max(0, count($limitsAll) - 2);
                            $price = null; // [label, final, old, colorClass]
                            $discountAmount = 0;
                            $discountPercent = 0;
                            if ($pkgPriceType === 'one_time') {
                                $price = ['قیمت یک‌باره', $pkg->final_one_time_price, $pkg->one_time_price, 'text-purple-600 dark:text-purple-400'];
                            } elseif ($pkgPriceType !== 'free') {
                                if (in_array('yearly_price', $activePeriods) && $pkg->yearly_price > 0) {
                                    $price = ['تعرفه سالانه', $pkg->final_yearly_price, $pkg->yearly_price, 'text-emerald-600 dark:text-emerald-400'];
                                } elseif (in_array('monthly_price', $activePeriods) && $pkg->monthly_price > 0) {
                                    $price = ['تعرفه ماهانه', $pkg->final_monthly_price, $pkg->monthly_price, 'text-indigo-600 dark:text-indigo-400'];
                                } elseif (in_array('quarterly_price', $activePeriods) && $pkg->quarterly_price > 0) {
                                    $price = ['تعرفه ۳ ماهه', $pkg->final_quarterly_price, $pkg->quarterly_price, 'text-indigo-600 dark:text-indigo-400'];
                                } elseif (in_array('semi_annual_price', $activePeriods) && $pkg->semi_annual_price > 0) {
                                    $price = ['تعرفه ۶ ماهه', $pkg->final_semi_annual_price, $pkg->semi_annual_price, 'text-indigo-600 dark:text-indigo-400'];
                                }
                            }
                            if ($price && $price[2] > $price[1]) {
                                $discountAmount = (int)($price[2] - $price[1]);
                                $discountPercent = (int)round(($discountAmount / $price[2]) * 100);
                            }
                        @endphp

                        <x-resellerhub::card
                            class="group h-full flex flex-col hover:shadow-lg hover:-translate-y-0.5 hover:border-indigo-300 dark:hover:border-indigo-700">

                            <div class="p-6 flex flex-col flex-1 gap-4">

                                {{-- Header --}}
                                <div class="flex items-start justify-between gap-3">
                                    <a href="{{ route('reseller.packages.show', $pkg->id) }}"
                                       class="flex items-center gap-3 min-w-0">
                                    <span
                                        class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-md"
                                        style="{{ $pkg->bg_style }}">
                                        @include('resellerhub::user.packages.partials.icon', ['icon' => $pkg->icon, 'class' => 'w-5 h-5'])
                                    </span>
                                        <div class="min-w-0">
                                            <h3 class="font-black text-sm sm:text-base text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition"
                                                title="{{ $pkg->name }}">
                                                {{ $pkg->name }}
                                            </h3>
                                            <div class="flex items-center gap-1.5 flex-wrap mt-1">
                                            <span
                                                class="inline-flex px-2 py-0.5 rounded-lg text-xs font-black {{ $ptLabel['class'] }}">
                                                {{ $ptLabel['label'] }}
                                            </span>
                                                @if($pkg->isActive())
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    فعال
                                                </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    غیرفعال
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>

                                    <div class="flex items-center gap-0.5 shrink-0">
                                        <button type="button" @click="openStatusModalById({{ $pkg->id }})"
                                                class="p-2 {{ $pkg->isActive() ? 'text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' : 'text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }} rounded-xl transition cursor-pointer"
                                                title="{{ $pkg->isActive() ? 'غیرفعال‌سازی این پکیج' : 'فعال‌سازی مجدد این پکیج' }}">
                                            @if($pkg->isActive())
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                        <a href="{{ route('reseller.packages.show', $pkg->id) }}"
                                           class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition cursor-pointer"
                                           title="مشاهده جزئیات پکیج">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('reseller.packages.edit', $pkg->id) }}"
                                           class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition cursor-pointer"
                                           title="ویرایش پکیج">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>
                                        <button type="button" @click="openDeleteModalById({{ $pkg->id }})"
                                                class="p-2 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition cursor-pointer"
                                                title="حذف پکیج">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-5 min-h-[2.5rem]">
                                    {{ $pkg->description ?: 'بدون توضیحات ثبت شده' }}
                                </p>

                                {{-- Stats --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        </svg>
                                    </span>
                                        <div class="leading-tight">
                                            <div
                                                class="text-sm font-black text-gray-900 dark:text-white">{{ reseller_fa_num(count($pkgModules)) }}</div>
                                            <div class="text-xs text-gray-400 font-bold">ماژول</div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                        <div class="leading-tight">
                                            <div
                                                class="text-sm font-black text-gray-900 dark:text-white">{{ reseller_fa_num($pkgFeatCount) }}</div>
                                            <div class="text-xs text-gray-400 font-bold">قابلیت</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 space-y-3">
                                    <div>
                                        <div class="text-xs font-black text-gray-500 dark:text-gray-400 mb-1.5">
                                            ماژول‌های پکیج
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($modsShown as $slug)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300">
                                                {{ $modulesMap[$slug]->title ?? $slug }}
                                            </span>
                                            @empty
                                                <span class="text-xs text-gray-400">ماژولی انتخاب نشده</span>
                                            @endforelse
                                            @if($modsRest > 0)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60"
                                                    title="{{ collect(array_slice($pkgModules, 4))->map(fn($s) => $modulesMap[$s]->title ?? $s)->implode('، ') }}">
                                                +{{ reseller_fa_num($modsRest) }} مورد دیگر
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!empty($limitsShown))
                                        <div>
                                            <div class="text-xs font-black text-gray-500 dark:text-gray-400 mb-1.5">سقف
                                                مصرف
                                            </div>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($limitsShown as $lSlug => $lVal)
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800/50">
                                                    @php
                                                        $qEnum = QuotaKey::tryFrom($lSlug);
                                                        $limitTitle = $qEnum ? $qEnum->shortTitle() : ($modulesMap[$lSlug]->title ?? $lSlug);
                                                        $limitFormatted = $qEnum ? $qEnum->formatLimit((int)$lVal) : ($lVal == -1 ? 'نامحدود' : reseller_fa_num($lVal));
                                                    @endphp
                                                    <span>{{ $limitTitle }}:</span>
                                                    <span class="font-black">{{ $limitFormatted }}</span>
                                                </span>
                                                @endforeach
                                                @if($limitsRest > 0)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-black bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800/50">
                                                    +{{ reseller_fa_num($limitsRest) }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Pricing box --}}
                                <div
                                    class="rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 overflow-hidden">
                                    <div class="px-4 py-3 flex items-center justify-between gap-3 min-h-[64px]">
                                        <div class="min-w-0">
                                            @if($pkgPriceType === 'free')
                                                <div class="text-xs text-gray-400 font-bold">نوع تعرفه</div>
                                                <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                                    رایگان
                                                </div>
                                            @elseif($price)
                                                <div class="text-xs text-gray-400 font-bold">{{ $price[0] }}</div>
                                                <div class="text-sm font-black {{ $price[3] }}">
                                                    {{ reseller_fa_num(number_format($price[1])) }}
                                                    <span
                                                        class="text-xs font-medium text-gray-400">{{ $currencyLabel }}</span>
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-400 font-bold">قیمت‌گذاری انعطاف‌پذیر
                                                </div>
                                            @endif
                                        </div>

                                        @if($pkg->setup_fee > 0)
                                            <div
                                                class="text-end shrink-0 ps-3 border-s border-gray-200 dark:border-gray-700">
                                                <div class="text-xs text-gray-400 font-bold">راه‌اندازی</div>
                                                <div class="text-xs font-black text-amber-600 dark:text-amber-400">
                                                    {{ reseller_fa_num(number_format($pkg->setup_fee)) }}
                                                    <span class="text-xs font-medium">{{ $currencyLabel }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($discountAmount > 0)
                                        <div
                                            class="px-4 py-2 flex items-center justify-between gap-2 bg-emerald-50 dark:bg-emerald-950/30 border-t border-emerald-100 dark:border-emerald-900/50">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-700 dark:text-emerald-300">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            شامل تخفیف
                                            @if($discountPercent > 0)
                                                <span
                                                    class="px-1.5 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/60 text-xs">{{ reseller_fa_num($discountPercent) }}٪</span>
                                            @endif
                                        </span>
                                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">
                                            {{ reseller_fa_num(number_format($discountAmount)) }}
                                            <span class="text-xs font-medium">{{ $currencyLabel }}</span>
                                        </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Fast CRM Provision --}}
                                <button type="button"
                                        @click="openQuickProvisionById({{ $pkg->id }})"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-300 hover:text-white dark:hover:text-white text-xs font-black transition-all cursor-pointer shadow-2xs group/btn">
                                    <svg
                                        class="w-3.5 h-3.5 text-indigo-500 group-hover/btn:text-white transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span>ساخت سریع CRM با این پکیج</span>
                                </button>
                            </div>
                        </x-resellerhub::card>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Separated Modals Included Here --}}
        @include('resellerhub::user.packages.partials.status-modal')
        @include('resellerhub::user.packages.partials.delete-modal')
        @include('resellerhub::user.packages.partials.quick-provision-modal')
        @include('resellerhub::partials.toast')

    </div>
@endsection
