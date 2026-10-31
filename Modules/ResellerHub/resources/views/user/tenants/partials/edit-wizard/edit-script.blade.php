@php use Modules\ResellerHub\App\Helpers\ResellerHelper;use Modules\ResellerHub\App\Services\DomainVerificationService; @endphp
<script>
    window.tenantEditorConfig = {
        tenant: {
            id: {{ $tenant->id }},
            name: @json($tenant->name),
            subdomain: @json($tenant->subdomain),
            custom_domain: @json($tenant->custom_domain ?? ''),
            status: @json($tenant->status),
            sub_account_id: {{ $tenant->sub_account_id ?? ($subAccount?->id ?? 'null') }},
            admin_name: @json($subAccount?->name ?? ($subAccount?->user?->name ?? '')),
            admin_email: @json($subAccount?->email ?? ($subAccount?->user?->email ?? '')),
            admin_mobile: @json($subAccount?->mobile ?? ($subAccount?->user?->mobile ?? '')),
            active_modules: @json($initialActiveModules)
        },
        subAccounts: {!! json_encode($subAccounts ?? [], JSON_UNESCAPED_UNICODE) !!},
        currentSubAccountId: {{ $tenant->sub_account_id ?? ($subAccount?->id ?? 'null') }},
        subAccountStoreUrl: "{{ route('reseller.sub-accounts.quick-store') }}",
        branding: {
            is_custom: {{ $isCustom ? 'true' : 'false' }},
            white_label_type: '{{ $isCustom ? 'custom' : 'default' }}',
            brand_title: @json($brandTitle),
            brand_primary_color: @json($brandPrimaryColor),
            brand_logo_url: @json($brandLogoUrl),
            brand_favicon_url: @json($brandFaviconUrl),
            brand_footer_text: @json($brandFooterText),
            support_phone: @json($supportPhone),
            support_email: @json($supportEmail),
        },
        allowedModules: @json($availableModules->where('is_allowed', true)->pluck('slug')->values()),
        invoiceNumbering: {!! json_encode($invoiceNumbering ?? [], JSON_UNESCAPED_UNICODE) !!},
        modulePricings: {!! json_encode($modulePricings ?? [], JSON_UNESCAPED_UNICODE) !!},
        catalog: {!! json_encode($catalog ?? collect([]), JSON_UNESCAPED_UNICODE) !!},
        tenantLimits: {!! json_encode($tenantLimits ?? [], JSON_UNESCAPED_UNICODE) !!},
        initialSelectedFeatures: {!! json_encode($initialSelectedFeatures ?? [], JSON_UNESCAPED_UNICODE) !!},
        currencyLabel: '{{ $currencyLabel }}',
        colorPresets: {!! json_encode(ResellerHelper::whiteLabelColorPresets(), JSON_UNESCAPED_UNICODE) !!},
        durationOptions: {!! json_encode(ResellerHelper::durationOptions(), JSON_UNESCAPED_UNICODE) !!}
    };

    function tenantEditor(config) {
        const initialTenant = (config && config.tenant) ? config.tenant : {};
        const initialBranding = (config && config.branding) ? config.branding : {};

        return {
            currentStep: 1,
            maxSteps: 3,
            submitting: false,
            updatedTenant: null,
            redirectUrl: '',
            copied: false,
            generalError: '',
            errors: {},
            toast: {show: false, message: '', type: 'success'},

            // White-Label Branding State & Handlers
            whiteLabelType: initialBranding.white_label_type || 'default',
            whiteLabelBrandTitle: initialBranding.brand_title || '',
            whiteLabelPrimaryColor: initialBranding.brand_primary_color || '{{ $resellerPrimaryColor ?? "#4f46e5" }}',
            whiteLabelLogoPreview: initialBranding.brand_logo_url || null,
            whiteLabelFaviconPreview: initialBranding.brand_favicon_url || null,
            removeLogo: false,
            removeFavicon: false,
            whiteLabelFooterText: initialBranding.brand_footer_text || '',
            whiteLabelSupportPhone: initialBranding.support_phone || '',
            whiteLabelSupportEmail: initialBranding.support_email || '',
            whiteLabelColorPresets: (config && config.colorPresets) ? config.colorPresets : (window.resellerColorPresets || []),

            isDraggingLogo: false,
            isDraggingFavicon: false,
            logoFileName: '',
            logoFileSize: '',
            faviconFileName: '',
            faviconFileSize: '',

            formatFileSize(bytes) {
                if (!bytes || bytes === 0) return '۰ بایت';
                const k = 1024;
                const sizes = ['بایت', 'کیلوبایت', 'مگابایت'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return this.faNum(parseFloat((bytes / Math.pow(k, i)).toFixed(1))) + ' ' + sizes[i];
            },

            handleFileSelect(event, target) {
                const file = event.target.files ? event.target.files[0] : null;
                if (file) {
                    this.processFile(file, target);
                }
            },

            handleDrop(event, target) {
                if (target === 'logo') this.isDraggingLogo = false;
                if (target === 'favicon') this.isDraggingFavicon = false;

                const files = event.dataTransfer ? event.dataTransfer.files : null;
                if (!files || files.length === 0) return;

                const file = files[0];
                const inputRef = target === 'logo' ? this.$refs.logoInput : this.$refs.faviconInput;
                if (inputRef) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    inputRef.files = dt.files;
                }
                this.processFile(file, target);
            },

            processFile(file, target) {
                if (target === 'logo') {
                    this.logoFileName = file.name;
                    this.logoFileSize = this.formatFileSize(file.size);
                    this.removeLogo = false;
                } else {
                    this.faviconFileName = file.name;
                    this.faviconFileSize = this.formatFileSize(file.size);
                    this.removeFavicon = false;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (target === 'logo') {
                        this.whiteLabelLogoPreview = e.target.result;
                    } else if (target === 'favicon') {
                        this.whiteLabelFaviconPreview = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            },

            removeFile(target) {
                if (target === 'logo') {
                    this.whiteLabelLogoPreview = null;
                    this.logoFileName = '';
                    this.logoFileSize = '';
                    this.removeLogo = true;
                    if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                } else {
                    this.whiteLabelFaviconPreview = null;
                    this.faviconFileName = '';
                    this.faviconFileSize = '';
                    this.removeFavicon = true;
                    if (this.$refs.faviconInput) this.$refs.faviconInput.value = '';
                }
            },
            selectedModules: (initialTenant && initialTenant.active_modules) ? [...initialTenant.active_modules] : [],
            allowedModules: (config && config.allowedModules) ? config.allowedModules : [],
            catalog: (config && config.catalog) ? config.catalog : [],
            moduleLimits: (config && config.tenantLimits && typeof config.tenantLimits === 'object') ? {...config.tenantLimits} : {},
            selectedFeatures: (config && config.initialSelectedFeatures && typeof config.initialSelectedFeatures === 'object') ? {...config.initialSelectedFeatures} : {},
            expandedModules: [],

            toggleModuleExpand(slug) {
                const s = String(slug || '').toLowerCase();
                if (this.expandedModules.includes(s)) {
                    this.expandedModules = this.expandedModules.filter(m => m !== s);
                } else {
                    this.expandedModules.push(s);
                }
            },

            isModuleExpanded(slug) {
                return this.expandedModules.includes(String(slug || '').toLowerCase());
            },

            getModuleFeatures(slug) {
                const s = String(slug || '').toLowerCase();
                const found = (this.catalog || []).find(m => String(m.slug || '').toLowerCase() === s);
                return (found && Array.isArray(found.features)) ? found.features : [];
            },

            getModuleFeaturesCount(slug) {
                return this.getModuleFeatures(slug).length;
            },

            getSelectedFeaturesCount(slug) {
                const s = String(slug || '').toLowerCase();
                return (this.selectedFeatures[s] && Array.isArray(this.selectedFeatures[s])) ? this.selectedFeatures[s].length : 0;
            },

            isFeatureSelected(slug, featKey) {
                const s = String(slug || '').toLowerCase();
                return Boolean(this.selectedFeatures[s] && this.selectedFeatures[s].includes(featKey));
            },

            toggleFeature(slug, featKey) {
                const s = String(slug || '').toLowerCase();
                if (!this.selectedFeatures[s]) this.selectedFeatures[s] = [];
                if (this.selectedFeatures[s].includes(featKey)) {
                    this.selectedFeatures[s] = this.selectedFeatures[s].filter(k => k !== featKey);
                } else {
                    this.selectedFeatures[s].push(featKey);
                }
            },

            selectAllFeatures(slug) {
                const s = String(slug || '').toLowerCase();
                const feats = this.getModuleFeatures(s);
                this.selectedFeatures[s] = feats.map(f => f.key);
            },

            deselectAllFeatures(slug) {
                const s = String(slug || '').toLowerCase();
                this.selectedFeatures[s] = [];
            },

            selectAllAllowedModules() {
                this.selectedModules = [...this.allowedModules];
                this.selectedModules.forEach(slug => {
                    const s = String(slug || '').toLowerCase();
                    if (!this.selectedFeatures[s] || this.selectedFeatures[s].length === 0) {
                        this.selectAllFeatures(s);
                    }
                });
            },

            deselectAllModules() {
                this.selectedModules = [];
            },
            issueInvoice: false,
            invoiceStatus: 'unpaid',
            invoiceNumbering: (config && config.invoiceNumbering) ? config.invoiceNumbering : {},
            invoiceNumberAuto: Boolean(config && config.invoiceNumbering && config.invoiceNumbering.auto),
            invoiceNumberOverride: '',
            invoiceIssueDate: '',
            invoiceDueDate: '',
            invoiceTaxRate: (config && config.invoiceNumbering && config.invoiceNumbering.tax_rate !== undefined)
                ? parseFloat(config.invoiceNumbering.tax_rate) : 10,
            invoiceTaxMode: (config && config.invoiceNumbering && config.invoiceNumbering.tax_mode)
                ? config.invoiceNumbering.tax_mode : 'invoice',
            taxRateLocked: true,
            taxModeLocked: true,
            invoiceItems: [],
            invoiceDiscountAmount: 0,
            invoiceDiscountDisplay: '',
            invoiceTerms: (config && config.invoiceNumbering && config.invoiceNumbering.terms) || '',
            invoiceTermsLocked: true,
            invoiceNotes: (config && config.invoiceNumbering && config.invoiceNumbering.footer) || '',
            invoiceNotesLocked: true,
            modulePricings: (config && config.modulePricings) ? config.modulePricings : {},
            catalogData: (config && config.catalog) ? config.catalog : [],
            currencyLabel: (config && config.currencyLabel) ? config.currencyLabel : 'تومان',

            faNum(str) {
                return window.faNum ? window.faNum(str) : (str !== null && str !== undefined ? String(str) : '');
            },

            originalSubdomain: initialTenant.subdomain || '',
            subdomainChecking: false,
            subdomainStatus: 'available',
            subdomainMsg: 'سابدامین فعلی این سامانه',

            domainChecking: false,
            domainStatus: initialTenant.custom_domain ? 'checking' : 'idle',
            domainMsg: '',
            dnsInfo: null,
            dnsGuideOpen: false,
            cnameTarget: @json(app(DomainVerificationService::class)->getTargetCname()),
            allowsCustomDomains: {{ $reseller->allowsCustomDomains() ? 'true' : 'false' }},

            // Sub-account management state
            subAccounts: (config && config.subAccounts) ? config.subAccounts : [],
            selectedSubAccount: null,
            subAccountSearchQuery: '',
            quickSubModalOpen: false,
            quickSubForm: {name: '', mobile: '', company_name: ''},
            quickSubSaving: false,
            quickSubErrors: {},

            filteredSubAccounts() {
                if (!this.subAccountSearchQuery || !this.subAccountSearchQuery.trim()) {
                    return this.subAccounts;
                }
                const q = this.subAccountSearchQuery.toLowerCase().trim();
                return this.subAccounts.filter(sa => {
                    return (sa.name && sa.name.toLowerCase().includes(q)) ||
                        (sa.mobile && sa.mobile.includes(q)) ||
                        (sa.company_name && sa.company_name.toLowerCase().includes(q));
                });
            },

            selectSubAccount(sub) {
                this.selectedSubAccount = sub;
                this.form.sub_account_id = sub ? sub.id : '';
                this.form.admin_name = sub ? sub.name : '';
                this.form.admin_email = sub ? sub.email : '';
                this.form.admin_mobile = sub ? sub.mobile : '';
                if (this.errors.sub_account_id) {
                    delete this.errors.sub_account_id;
                }
            },

            clearSubAccount() {
                this.selectedSubAccount = null;
                this.form.sub_account_id = '';
                this.form.admin_name = '';
                this.form.admin_email = '';
                this.form.admin_mobile = '';
            },

            openQuickSubModal() {
                this.quickSubForm = {name: '', mobile: '', company_name: ''};
                this.quickSubErrors = {};
                this.quickSubModalOpen = true;
            },

            async submitQuickSubAccount() {
                this.quickSubErrors = {};
                if (!this.quickSubForm.name || this.quickSubForm.name.trim().length < 2) {
                    this.quickSubErrors.name = ['نام و نام خانوادگی الزامی است (حداقل ۲ کاراکتر).'];
                    return;
                }
                if (!this.quickSubForm.mobile || !this.quickSubForm.mobile.trim()) {
                    this.quickSubErrors.mobile = ['شماره موبایل الزامی است.'];
                    return;
                }
                this.quickSubSaving = true;
                try {
                    const res = await axios.post((config && config.subAccountStoreUrl) || "{{ route('reseller.sub-accounts.quick-store') }}", this.quickSubForm);
                    if (res.data && res.data.success) {
                        const newSub = res.data.sub_account;
                        this.subAccounts.unshift(newSub);
                        this.selectSubAccount(newSub);
                        this.quickSubModalOpen = false;
                        this.showToast('حساب زیرمجموعه جدید با موفقیت ایجاد و برای این سامانه انتخاب شد.', 'success');
                    }
                } catch (e) {
                    if (e.response && e.response.data && e.response.data.errors) {
                        this.quickSubErrors = e.response.data.errors;
                    } else {
                        const msg = (e.response && e.response.data && e.response.data.message) ? e.response.data.message : 'خطا در ثبت زیرمجموعه.';
                        this.showToast(msg, 'error');
                    }
                } finally {
                    this.quickSubSaving = false;
                }
            },

            init() {
                if (this.form.sub_account_id) {
                    const match = this.subAccounts.find(s => String(s.id) === String(this.form.sub_account_id));
                    if (match) {
                        this.selectedSubAccount = match;
                    }
                }
                if (this.form.custom_domain) {
                    this.checkCustomDomain();
                }
            },

            copyToClipboard(text, msg = 'کپی شد') {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
                this.showToast(msg, 'success');
            },

            showPassword: false,
            showConfirmPassword: false,

            form: {
                name: initialTenant.name || '',
                subdomain: initialTenant.subdomain || '',
                custom_domain: initialTenant.custom_domain || '',
                status: initialTenant.status || 'active',
                sub_account_id: initialTenant.sub_account_id || (config && config.currentSubAccountId) || '',
                admin_name: initialTenant.admin_name || '',
                admin_email: initialTenant.admin_email || '',
                admin_mobile: initialTenant.admin_mobile || '',
                password: '',
                password_confirmation: '',
            },
            formatAmount(num) {
                const n = Math.max(0, parseInt(num) || 0);
                return this.faNum(n.toLocaleString('en-US'));
            },

            parsePriceInput(value) {
                return window.parsePriceInput ? window.parsePriceInput(value) : (parseInt(String(value || '').replace(/[^0-9]/g, ''), 10) || 0);
            },

            formatPriceInput(value) {
                if (!value && value !== 0) return '';
                const num = this.parsePriceInput(value);
                if (!num) return '';
                return this.faNum(num.toLocaleString('en-US'));
            },

            getItemRowTotal(item) {
                const qty = Math.max(1, parseFloat(item.quantity) || 1);
                const price = Math.max(0, parseInt(item.unit_price) || 0);
                const disc = Math.max(0, parseInt(item.discount_amount) || 0);
                return Math.max(0, (qty * price) - disc);
            },

            get invoiceNumberPreview() {
                const cfg = this.invoiceNumbering || {};
                const pad = parseInt(cfg.padding) || 4;
                const num = String(1).padStart(pad, '0');
                const mid = cfg.middle ? (cfg.middle + '-') : '';
                return (cfg.prefix || '') + mid + num + (cfg.suffix || '');
            },

            get effectiveInvoiceNumber() {
                return this.invoiceNumberAuto
                    ? this.invoiceNumberPreview
                    : (this.invoiceNumberOverride || '').trim();
            },

            toggleInvoiceNumberAuto() {
                this.invoiceNumberAuto = !this.invoiceNumberAuto;
                if (!this.invoiceNumberAuto) {
                    this.invoiceNumberOverride = this.invoiceNumberPreview;
                }
            },

            toggleTaxRateLock() {
                this.taxRateLocked = !this.taxRateLocked;
                if (this.taxRateLocked) {
                    this.invoiceTaxRate = parseFloat(this.invoiceNumbering.tax_rate) || 0;
                    this.applyTaxToItems();
                }
            },

            toggleTaxModeLock() {
                if (this.taxModeLocked) {
                    this.taxModeLocked = false;
                } else {
                    this.taxModeLocked = true;
                    this.invoiceTaxMode = this.invoiceNumbering.tax_mode || 'invoice';
                    this.applyTaxToItems();
                }
            },

            applyTaxToItems() {
                const rate = parseFloat(this.invoiceTaxRate) || 0;
                this.invoiceItems.forEach(it => {
                    it.tax_rate = rate;
                });
            },

            setTodayIssueDate() {
                const formatter = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                    year: 'numeric', month: '2-digit', day: '2-digit'
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
                    year: 'numeric', month: '2-digit', day: '2-digit'
                });
                const parts = formatter.format(d).split('/');
                return (parts.length === 3) ? (parts[0] + '/' + parts[1] + '/' + parts[2]) : '';
            },

            updateItemQty(item) {
                item.quantity = Math.max(1, this.parsePriceInput(item.quantity) || 1);
            },

            updateItemPrice(item) {
                const v = this.parsePriceInput(item.display_price);
                item.unit_price = v;
                item.display_price = v > 0 ? this.formatPriceInput(v) : '';
            },

            updateItemDiscount(item) {
                const v = this.parsePriceInput(item.display_discount);
                item.discount_amount = Math.max(0, v);
                item.display_discount = v > 0 ? this.formatPriceInput(v) : '';
            },

            updateInvoiceDiscount() {
                const v = this.parsePriceInput(this.invoiceDiscountDisplay);
                this.invoiceDiscountAmount = Math.min(Math.max(0, v), this.getInvoiceSubtotal());
                this.invoiceDiscountDisplay = this.invoiceDiscountAmount > 0 ? this.formatPriceInput(this.invoiceDiscountAmount) : '';
            },

            removeInvoiceItem(index) {
                this.invoiceItems.splice(index, 1);
            },

            addCustomInvoiceItem() {
                this.invoiceItems.push({
                    description: '',
                    item_type: 'custom',
                    item_id: '',
                    duration_months: 12,
                    unit_price: 0,
                    quantity: 1,
                    discount_amount: 0,
                    tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                    display_price: '',
                    display_discount: ''
                });
            },

            getInvoiceSubtotal() {
                return this.invoiceItems.reduce((sum, it) => sum + this.getItemRowTotal(it), 0);
            },

            getInvoiceDiscount() {
                const subtotal = this.getInvoiceSubtotal();
                return Math.min(Math.max(0, parseInt(this.invoiceDiscountAmount) || 0), subtotal);
            },

            /**
             * Spread the invoice-level discount over the line items,
             * proportionally, using the largest-remainder method so the shares
             * add up to exactly the discount. Mirrors
             * BillingCalculator::allocateDiscount() on the server.
             */
            allocateInvoiceDiscount(discountAmount) {
                const amounts = this.invoiceItems.map(it => Math.max(0, this.getItemRowTotal(it)));
                const count = amounts.length;
                if (!count) return [];

                const total = amounts.reduce((a, b) => a + b, 0);
                const discount = Math.max(0, Math.min(discountAmount, total));
                if (!discount || !total) return new Array(count).fill(0);

                const shares = [];
                const remainders = [];
                let assigned = 0;

                amounts.forEach((amount, i) => {
                    const exact = (amount * discount) / total;
                    const floor = Math.floor(exact);
                    shares[i] = floor;
                    remainders[i] = exact - floor;
                    assigned += floor;
                });

                let leftover = discount - assigned;
                if (leftover > 0) {
                    remainders
                        .map((r, i) => ({r, i}))
                        .sort((a, b) => b.r - a.r)
                        .forEach(({i}) => {
                            if (leftover <= 0) return;
                            shares[i]++;
                            leftover--;
                        });
                }

                return shares;
            },

            /**
             * Tax is charged on the NET base (subtotal minus discount).
             * In per-line mode each row is taxed on its own net amount after
             * its proportional share of the invoice discount.
             */
            getInvoiceTax() {
                const subtotal = this.getInvoiceSubtotal();
                const discount = this.getInvoiceDiscount();
                const taxable = Math.max(0, subtotal - discount);
                const rate = parseFloat(this.invoiceTaxRate) || 0;

                if (this.invoiceTaxMode === 'item') {
                    const shares = this.allocateInvoiceDiscount(discount);
                    return this.invoiceItems.reduce((sum, it, i) => {
                        const lineTaxable = Math.max(0, this.getItemRowTotal(it) - (shares[i] || 0));
                        if (lineTaxable <= 0 || rate <= 0) return sum;
                        return sum + Math.round((lineTaxable * rate) / 100);
                    }, 0);
                }

                if (taxable <= 0 || rate <= 0) return 0;
                return Math.round((taxable * rate) / 100);
            },

            getInvoiceTotal() {
                const subtotal = this.getInvoiceSubtotal();
                const discount = this.getInvoiceDiscount();
                return Math.max(0, (subtotal - discount) + this.getInvoiceTax());
            },

            syncInvoiceItemsFromEditModules() {
                const months = 12;
                const items = [];
                this.selectedModules.forEach(slug => {
                    const sLower = String(slug).toLowerCase();
                    const pricing = this.modulePricings[slug] || this.modulePricings[sLower] || null;
                    const catMod = (this.catalogData || []).find(c => String(c.slug || '').toLowerCase() === sLower) || null;
                    const title = (catMod && catMod.title) ? catMod.title : slug;

                    let unitPrice = (pricing && parseInt(pricing.yearly_price) > 0) ? parseInt(pricing.yearly_price)
                        : ((catMod && parseInt(catMod.base_yearly_price) > 0) ? parseInt(catMod.base_yearly_price) : 0);
                    if (!unitPrice) {
                        const m = (pricing && parseInt(pricing.monthly_price) > 0) ? parseInt(pricing.monthly_price)
                            : ((catMod && parseInt(catMod.base_monthly_price) > 0) ? parseInt(catMod.base_monthly_price) : 0);
                        unitPrice = m * months;
                    }
                    const setupFee = (pricing && parseInt(pricing.setup_fee) > 0) ? parseInt(pricing.setup_fee)
                        : ((catMod && parseInt(catMod.base_setup_fee) > 0) ? parseInt(catMod.base_setup_fee) : 0);

                    if (unitPrice > 0) {
                        items.push({
                            description: 'اشتراک یک ساله ماژول ' + title + ' (بروزرسانی سامانه «' + (initialTenant.name || '') + '»)',
                            item_type: 'module',
                            item_id: slug,
                            duration_months: months,
                            unit_price: unitPrice,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                            display_price: this.formatPriceInput(unitPrice),
                            display_discount: ''
                        });
                    }
                    if (setupFee > 0) {
                        items.push({
                            description: 'هزینه راه‌اندازی و کانفیگ ماژول ' + title,
                            item_type: 'setup_fee',
                            item_id: slug + '_setup',
                            duration_months: 0,
                            unit_price: setupFee,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: parseFloat(this.invoiceTaxRate) || 0,
                            display_price: this.formatPriceInput(setupFee),
                            display_discount: ''
                        });
                    }
                });
                this.invoiceItems.filter(it => it.item_type === 'custom' && (it.description || '').trim() !== '')
                    .forEach(c => items.push(c));
                this.invoiceItems = items;
            },

            onIssueInvoiceToggle() {
                if (this.issueInvoice) {
                    if (!this.invoiceIssueDate) this.setTodayIssueDate();
                    if (this.invoiceItems.length === 0) this.syncInvoiceItemsFromEditModules();
                    this.applyTaxToItems();
                }
            },

            generatePassword() {
                const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
                let pwd = '';
                for (let i = 0; i < 10; i++) {
                    pwd += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                this.form.password = pwd;
                this.form.password_confirmation = pwd;
                this.showPassword = true;
                this.showConfirmPassword = true;
                this.showToast('رمز عبور جدید و امن ایجاد شد.', 'success');
            },

            showToast(msg, type = 'success') {
                this.toast.message = msg;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            validateStep1() {
                this.errors = {};
                if (!this.form.name || !this.form.name.trim()) {
                    this.errors.name = ['لطفاً نام کسب‌وکار یا کلینیک را وارد نمایید.'];
                    this.showToast('لطفاً نام کسب‌وکار یا کلینیک را وارد کنید.', 'error');
                    return false;
                }
                if (!this.form.subdomain || !this.form.subdomain.trim()) {
                    this.errors.subdomain = ['لطفاً آدرس سابدامین را وارد نمایید.'];
                    this.showToast('لطفاً آدرس سابدامین را وارد کنید.', 'error');
                    return false;
                }
                if (this.subdomainStatus === 'unavailable') {
                    this.errors.subdomain = ['این سابدامین قابل استفاده نیست. لطفاً عنوان دیگری انتخاب کنید.'];
                    this.showToast('این سابدامین آزاد نیست.', 'error');
                    return false;
                }
                return true;
            },

            validateStep2() {
                this.errors = {};
                if (!this.form.sub_account_id) {
                    this.errors.sub_account_id = ['انتخاب حساب زیرمجموعه (مالک / خریدار سامانه) الزامی است.'];
                    this.showToast('لطفاً حساب زیرمجموعه خریدار را انتخاب فرمایید.', 'error');
                    return false;
                }
                return true;
            },

            goToStep(targetStep) {
                if (targetStep === this.currentStep) return;
                if (targetStep < this.currentStep) {
                    this.currentStep = targetStep;
                    return;
                }
                if (this.currentStep === 1) {
                    if (!this.validateStep1()) return;
                    if (targetStep === 3) {
                        if (!this.validateStep2()) {
                            this.currentStep = 2;
                            return;
                        }
                    }
                } else if (this.currentStep === 2) {
                    if (!this.validateStep2()) return;
                }
                this.currentStep = targetStep;
            },

            nextStep() {
                if (this.currentStep === 1) {
                    if (this.validateStep1()) {
                        this.currentStep = 2;
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    }
                } else if (this.currentStep === 2) {
                    if (this.validateStep2()) {
                        this.currentStep = 3;
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    }
                }
            },

            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    window.scrollTo({top: 0, behavior: 'smooth'});
                }
            },

            async checkSubdomain() {
                const sub = (this.form.subdomain || '').trim().toLowerCase();
                if (!sub) {
                    this.subdomainStatus = 'idle';
                    this.subdomainMsg = '';
                    return;
                }

                if (sub === (this.originalSubdomain || '').toLowerCase()) {
                    this.subdomainStatus = 'available';
                    this.subdomainMsg = 'سابدامین فعلی همین سامانه است.';
                    this.subdomainChecking = false;
                    return;
                }

                this.subdomainChecking = true;
                try {
                    const res = await fetch(`{{ route('reseller.tenants.check-subdomain') }}?subdomain=${encodeURIComponent(sub)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (data.available) {
                        this.subdomainStatus = 'available';
                        this.subdomainMsg = data.message || 'آدرس آزاد و قابل استفاده است.';
                    } else {
                        this.subdomainStatus = 'unavailable';
                        this.subdomainMsg = data.message || 'آدرس سابدامین معتبر یا آزاد نیست.';
                    }
                } catch (e) {
                    this.subdomainStatus = 'idle';
                } finally {
                    this.subdomainChecking = false;
                }
            },

            async checkCustomDomain() {
                const dom = (this.form.custom_domain || '').trim();
                if (!dom) {
                    this.domainStatus = 'idle';
                    this.domainMsg = '';
                    this.dnsInfo = null;
                    return;
                }

                this.domainChecking = true;
                this.domainStatus = 'checking';
                this.domainMsg = 'در حال اعتبارسنجی و استعلام رکوردهای DNS...';

                try {
                    const res = await fetch(`{{ route('reseller.tenants.check-domain') }}?domain=${encodeURIComponent(dom)}&tenant_id={{ $tenant->id }}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();

                    if (!data.valid || !data.available) {
                        this.domainStatus = 'unavailable';
                        this.domainMsg = data.message;
                        this.dnsInfo = null;
                    } else {
                        this.dnsInfo = data.dns || null;
                        if (data.dns && data.dns.status === 'verified') {
                            this.domainStatus = 'verified';
                            this.domainMsg = 'دامنه اختصاصی با موفقیت متصل و رکوردهای DNS فعال هستند.';
                        } else if (data.dns && data.dns.status === 'pending') {
                            this.domainStatus = 'pending';
                            this.domainMsg = data.dns.message;
                        } else {
                            this.domainStatus = 'available';
                            this.domainMsg = 'نام دامنه معتبر است (در انتظار تنظیم رکورد DNS).';
                        }
                    }
                } catch (e) {
                    this.domainStatus = 'error';
                    this.domainMsg = 'خطا در ارتباط با سرور جهت بررسی دامنه.';
                } finally {
                    this.domainChecking = false;
                }
            },

            async submitForm(formEl) {
                if (this.submitting) return;
                if (!this.validateStep1() || !this.validateStep2()) {
                    return;
                }

                this.submitting = true;
                this.errors = {};
                this.generalError = '';

                const formData = new FormData(formEl);
                formData.set('white_label_type', this.whiteLabelType);
                if (this.whiteLabelType === 'custom') {
                    formData.set('branding[brand_title]', (this.whiteLabelBrandTitle || this.form.name || '').trim());
                    formData.set('branding[brand_primary_color]', this.whiteLabelPrimaryColor || '#4f46e5');
                    formData.set('branding[brand_footer_text]', (this.whiteLabelFooterText || '').trim());
                    formData.set('branding[support_phone]', (this.whiteLabelSupportPhone || '').trim());
                    formData.set('branding[support_email]', (this.whiteLabelSupportEmail || '').trim());
                    if (this.removeLogo) formData.set('remove_tenant_brand_logo', '1');
                    if (this.removeFavicon) formData.set('remove_tenant_brand_favicon', '1');
                }

                // Append selected features for each active module
                this.selectedModules.forEach(slug => {
                    const feats = this.selectedFeatures[slug] || [];
                    feats.forEach(fKey => {
                        formData.append(`features[${slug}][]`, fKey);
                    });
                });

                // Append configured limits
                if (this.moduleLimits && typeof this.moduleLimits === 'object') {
                    Object.keys(this.moduleLimits).forEach(key => {
                        const val = this.moduleLimits[key];
                        formData.set(`limits[${key}]`, (val !== null && val !== undefined) ? val : '');
                    });
                }

                formData.set('issue_invoice', this.issueInvoice ? '1' : '0');
                if (this.issueInvoice) {
                    formData.set('invoice_status', 'unpaid');
                    formData.set('invoice_issue_date', this.invoiceIssueDate || '');
                    formData.set('invoice_due_date', this.invoiceDueDate || '');
                    formData.set('invoice_tax_rate', this.invoiceTaxRate || 0);
                    formData.set('invoice_tax_mode', this.invoiceTaxMode || 'invoice');
                    formData.set('invoice_discount_amount', this.invoiceDiscountAmount || 0);
                    formData.set('invoice_terms', this.invoiceTerms || '');
                    formData.set('invoice_notes', this.invoiceNotes || '');
                    formData.set('invoice_auto', this.invoiceNumberAuto ? '1' : '0');
                    formData.set('invoice_number', this.invoiceNumberAuto ? '' : (this.invoiceNumberOverride || '').trim());
                    formData.set('tax_from_settings', (this.taxRateLocked && this.taxModeLocked) ? '1' : '0');

                    this.invoiceItems
                        .filter(it => (it.item_type === 'custom') ? ((it.description || '').trim() !== '') : true)
                        .forEach((it, idx) => {
                            formData.set(`invoice_items[${idx}][description]`, it.description || '');
                            formData.set(`invoice_items[${idx}][title]`, (it.description || '').slice(0, 150));
                            formData.set(`invoice_items[${idx}][item_type]`, it.item_type || 'module');
                            formData.set(`invoice_items[${idx}][item_id]`, it.item_id || '');
                            formData.set(`invoice_items[${idx}][duration_months]`, it.duration_months || 12);
                            formData.set(`invoice_items[${idx}][unit_price]`, it.unit_price || 0);
                            formData.set(`invoice_items[${idx}][quantity]`, it.quantity || 1);
                            formData.set(`invoice_items[${idx}][discount_amount]`, it.discount_amount || 0);
                            formData.set(`invoice_items[${idx}][tax_rate]`, it.tax_rate || 0);
                        });
                }

                try {
                    const res = await fetch(`{{ route('reseller.tenants.update', $tenant->id) }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        this.updatedTenant = data.tenant;
                        this.redirectUrl = data.redirect_url;
                        this.showToast(data.message || 'مشخصات سامانه با موفقیت بروزرسانی شد.', 'success');
                    } else if (res.status === 422) {
                        this.errors = data.errors || {};
                        this.generalError = data.message || 'لطفاً مقادیر وارد شده را بررسی فرمایید.';
                        this.showToast(this.generalError, 'error');
                        if (this.errors.name || this.errors.subdomain || this.errors.custom_domain) {
                            this.currentStep = 1;
                        } else if (this.errors.admin_name || this.errors.admin_email || this.errors.admin_mobile || this.errors.status || this.errors.password || this.errors.password_confirmation) {
                            this.currentStep = 2;
                        }
                    } else {
                        this.generalError = data.message || 'خطا در بروزرسانی سامانه.';
                        this.showToast(this.generalError, 'error');
                    }
                } catch (err) {
                    this.generalError = 'خطای غیرمنتظره در ارتباط با سرور.';
                    this.showToast(this.generalError, 'error');
                } finally {
                    this.submitting = false;
                }
            },

            copyUrl(url) {
                if (!url) return;
                navigator.clipboard.writeText(url).then(() => {
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 3000);
                });
            },

            continueEditing() {
                this.updatedTenant = null;
                this.currentStep = 1;
                this.errors = {};
                this.generalError = '';
            }
        };
    }

    if (window.Alpine) {
        Alpine.data('tenantEditor', tenantEditor);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('tenantEditor', tenantEditor);
        });
    }
</script>
