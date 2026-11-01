<script>
    window.tenantCreatorConfig = @js($tenantCreatorConfigPayload);

    function tenantCreator(config) {
        return {
            currentStep: 1,
            maxSteps: 3,
            createdInvoice: null,
            submitting: false,
            createdTenant: null,
            redirectUrl: '',
            copied: false,
            generalError: '',
            errors: {},
            toast: {show: false, message: '', type: 'success'},

            packages: (config && config.packages) ? config.packages : [],
            activePackageId: (config && config.selectedPackageId) ? config.selectedPackageId : null,
            moduleLimits: (config && config.oldLimits && typeof config.oldLimits === 'object') ? {...config.oldLimits} : {},
            savePackageModalOpen: false,
            newPackageName: '',
            newPackageDescription: '',
            newPackageColor: 'indigo',
            newPackageIcon: 'tooth',
            availableColors: window.resellerPackageColors || [],
            availableIcons: window.industryIcons || [],
            savingPackage: false,
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
                    const res = await axios.post("{{ route('reseller.sub-accounts.quick-store') }}", this.quickSubForm);
                    if (res.data && res.data.success) {
                        const newSub = res.data.sub_account;
                        this.subAccounts = [newSub, ...(this.subAccounts || [])];
                        this.selectSubAccount(newSub);
                        this.quickSubModalOpen = false;
                        this.showToast('حساب زیرمجموعه جدید با موفقیت ایجاد و انتخاب گردید.', 'success');
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

            getActivePackage() {
                if (!this.activePackageId) return null;
                return this.packages.find(p => p.id === this.activePackageId) || null;
            },

            getPackageDurationPrice(pkg, months) {
                if (!pkg) return 0;
                const priceType = pkg.price_type || 'subscription';
                if (priceType === 'free') return 0;
                if (priceType === 'one_time') return parseInt(pkg.one_time_price) || 0;

                months = parseInt(months, 10) || 12;

                // Dynamic period_prices support
                if (pkg.period_prices && typeof pkg.period_prices === 'object') {
                    const directPrice = parseInt(pkg.period_prices[months] || pkg.period_prices[String(months)]) || 0;
                    if (directPrice > 0) {
                        return directPrice;
                    }
                }

                const active = Array.isArray(pkg.active_periods) ? pkg.active_periods : [];
                if (active.length > 0) {
                    const isAct = active.includes(String(months))
                        || active.includes(months)
                        || (months === 1 && active.includes('monthly_price'))
                        || (months === 3 && active.includes('quarterly_price'))
                        || (months === 6 && active.includes('semi_annual_price'))
                        || (months === 12 && active.includes('yearly_price'));
                    if (!isAct) return 0;
                }

                const yearlyBase = (parseInt(pkg.yearly_price) || 0) > 0
                    ? parseInt(pkg.yearly_price)
                    : ((parseInt(pkg.monthly_price) || 0) * 12);
                if (months === 1) return parseInt(pkg.monthly_price) || 0;
                if (months === 3) return parseInt(pkg.quarterly_price) || Math.round((yearlyBase / 12) * 3);
                if (months === 6) return parseInt(pkg.semi_annual_price) || Math.round((yearlyBase / 12) * 6);
                if (months === 12) return yearlyBase;
                if (months === 24) return yearlyBase * 2;
                return Math.round((yearlyBase / 12) * months);
            },

            getPackageDurationDiscount(pkg, months) {
                if (!pkg) return 0;
                const rawPrice = this.getPackageDurationPrice(pkg, months);
                if (rawPrice <= 0) return 0;

                months = parseInt(months, 10) || 12;

                // Check for custom period discount first
                if (pkg.period_discounts && typeof pkg.period_discounts === 'object') {
                    const custom = pkg.period_discounts[months] || pkg.period_discounts[String(months)];
                    if (custom && custom.has_custom && parseFloat(custom.discount_value) > 0) {
                        const customVal = parseFloat(custom.discount_value);
                        const customType = custom.discount_type || 'amount';
                        if (customType === 'amount') {
                            return Math.min(rawPrice, Math.round(customVal));
                        } else {
                            return Math.round(rawPrice * (Math.min(100, customVal) / 100));
                        }
                    }
                }

                // Fallback to global package discount
                const discVal = parseFloat(pkg.discount_value) || 0;
                if (discVal <= 0) return 0;
                if (pkg.discount_type === 'amount') {
                    return Math.min(rawPrice, Math.round(discVal));
                } else {
                    return Math.round(rawPrice * (Math.min(100, discVal) / 100));
                }
            },

            getActivePackageDiscount() {
                const pkg = this.getActivePackage();
                if (!pkg) return 0;
                const months = parseInt(this.form.expires_in_months, 10) || 12;
                return this.getPackageDurationDiscount(pkg, months);
            },

            selectPackageDuration(months) {
                this.form.expires_in_months = String(months);
                this.syncInvoiceItemsFromModules();
            },

            getColorHex(color) {
                return window.getColorHex ? window.getColorHex(color) : '#4f46e5';
            },

            getColorClass(color) {
                return window.getColorClass ? window.getColorClass(color) : 'bg-indigo-600';
            },

            getPackageIconClass(color, isSelected) {
                if (isSelected) {
                    return 'text-white shadow-sm';
                }
                return 'ring-1 ring-black/5 dark:ring-white/10 group-hover:opacity-90';
            },

            getPackageIconStyle(color, isSelected) {
                const hex = this.getColorHex(color);
                if (isSelected) {
                    return `background-color: ${hex} !important; color: #ffffff !important; box-shadow: 0 4px 12px ${hex}40 !important;`;
                }
                return `background-color: ${hex}18 !important; color: ${hex} !important; border: 1px solid ${hex}35 !important;`;
            },

            getPackageCardStyle(pkg) {
                if (this.activePackageId === pkg.id) {
                    const hex = this.getColorHex(pkg.color);
                    return 'border-color: ' + hex + '; box-shadow: 0 0 0 2px ' + hex + '33; background-color: ' + hex + '0d;';
                }
                return '';
            },

            getPackageRibbonStyle(pkg) {
                const hex = this.getColorHex(pkg.color);
                return 'background-color: ' + hex + '; color: #ffffff;';
            },

            getPackageCheckStyle(pkg) {
                if (this.activePackageId === pkg.id) {
                    const hex = this.getColorHex(pkg.color);
                    return 'background-color: ' + hex + '; color: #ffffff;';
                }
                return '';
            },

            getPackageLimitsCount(pkg) {
                if (!pkg || !pkg.limits || typeof pkg.limits !== 'object' || Array.isArray(pkg.limits)) {
                    return 0;
                }
                try {
                    return Object.keys(pkg.limits).filter(k => pkg.limits[k] !== '' && pkg.limits[k] !== null && pkg.limits[k] !== undefined).length;
                } catch (e) {
                    return 0;
                }
            },

            getActiveLimitsCount() {
                if (!this.moduleLimits || typeof this.moduleLimits !== 'object') return 0;
                try {
                    return Object.keys(this.moduleLimits).filter(k => {
                        const v = this.moduleLimits[k];
                        return v !== '' && v !== null && v !== undefined;
                    }).length;
                } catch (e) {
                    return 0;
                }
            },

            getLimitHumanLabel(key) {
                if (!key) return '';
                const keyLower = String(key).toLowerCase();
                const mod = this.allModules.find(m => (m.slug || '').toLowerCase() === keyLower);
                if (mod) return 'سقف ماژول ' + (mod.title || mod.slug);

                for (let m of this.allModules) {
                    const feats = this.getModuleFeatures(m.slug);
                    const feat = feats.find(f => f.key === key);
                    if (feat) return 'سقف ' + (feat.title || feat.key);
                }

                return 'سقف ' + key;
            },

            getItemLimits(item) {
                if (!item) return [];
                const list = [];
                if (!this.moduleLimits || typeof this.moduleLimits !== 'object') return list;

                if (item.item_type === 'package') {
                    const activePkg = this.getActivePackage();
                    if (activePkg && activePkg.limits && typeof activePkg.limits === 'object') {
                        Object.keys(activePkg.limits).forEach(k => {
                            const val = (this.moduleLimits[k] !== undefined && this.moduleLimits[k] !== '') ? this.moduleLimits[k] : activePkg.limits[k];
                            if (val !== '' && val !== null && val !== undefined) {
                                list.push({key: k, label: this.getLimitHumanLabel(k), value: val});
                            }
                        });
                    }
                } else if (item.item_type === 'module') {
                    const slug = item.item_id;
                    if (!slug) return list;

                    // Feature-level limits
                    const feats = this.getModuleFeatures(slug);
                    feats.forEach(f => {
                        const fVal = this.moduleLimits[f.key];
                        if (fVal !== '' && fVal !== null && fVal !== undefined) {
                            list.push({key: f.key, label: 'سقف ' + (f.title || f.key), value: fVal});
                        }
                    });
                }

                return list;
            },

            getModuleLimitsDescription(slug) {
                if (!slug || !this.moduleLimits) return '';
                const parts = [];

                const feats = this.getModuleFeatures(slug);
                feats.forEach(f => {
                    const fVal = this.moduleLimits[f.key];
                    if (fVal !== '' && fVal !== null && fVal !== undefined) {
                        parts.push((f.title || f.key) + ': ' + this.faNum(fVal));
                    }
                });

                if (parts.length > 0) {
                    return ' — [سقف: ' + parts.join('، ') + ']';
                }
                return '';
            },

            renderIcon(key, sizeClass) {
                return window.renderIndustryIcon(key, sizeClass);
            },

            currentReseller: (config && config.currentReseller) ? config.currentReseller : null,
            allModules: (config && config.allModules) ? config.allModules : [],
            selectedModules: [],
            isSuperAdmin: Boolean(config && config.isSuperAdmin),

            showPassword: false,
            showConfirmPassword: false,

            durationOptions: (config && config.durationOptions) ? config.durationOptions : (window.resellerDurationOptions || []),

            get availableDurationOptions() {
                const pkg = this.getActivePackage();
                if (!pkg) {
                    return this.durationOptions;
                }
                const fn = window.resolvePackageDurationOptions || this.resolvePackageDurationOptions;
                if (fn) {
                    return fn(pkg, {
                        fallback: [],
                        oneTimeValue: '',
                        oneTimeLabel: 'مادام‌العمر (یک‌باره)',
                    });
                }
                return [];
            },

            form: {
                name: '',
                subdomain: '',
                custom_domain: '',
                reseller_id: (config && config.currentReseller) ? config.currentReseller.id : '',
                sub_account_id: '',
                admin_name: '',
                admin_mobile: '',
                admin_email: '',
                password: '',
                password_confirmation: '',
                expires_in_months: (config && config.defaultDuration) ? String(config.defaultDuration) : '12',
            },

            domainType: 'subdomain', // 'subdomain' or 'custom'

            subdomainChecking: false,
            subdomainStatus: 'idle', // idle, available, unavailable
            subdomainMsg: '',

            allowsCustomDomains: Boolean(config && config.allowsCustomDomains),
            cnameTarget: (config && config.cnameTarget) ? config.cnameTarget : window.location.hostname,
            domainChecking: false,
            domainStatus: 'idle', // idle, verified, pending, unavailable
            domainMsg: '',
            dnsInfo: null,
            dnsGuideOpen: false,

            catalog: (config && config.catalog) ? config.catalog : [],
            modulePricings: (config && config.modulePricings) ? config.modulePricings : {},
            financialSettings: (config && config.financialSettings) ? config.financialSettings : {},
            currencyLabel: (config && config.currencyLabel) ? config.currencyLabel : "{{ $currencyLabel }}",

            // White-Label State & Methods
            whiteLabelType: 'default', // 'default' or 'custom'
            whiteLabelBrandTitle: '',
            whiteLabelPrimaryColor: (config && config.financialSettings && config.financialSettings.brand_primary_color) || '#4f46e5',
            whiteLabelLogoPreview: null,
            whiteLabelFaviconPreview: null,
            whiteLabelFooterText: '',
            whiteLabelSupportPhone: '',
            whiteLabelSupportEmail: '',
            whiteLabelColorPresets: (config && config.colorPresets) ? config.colorPresets : (window.resellerColorPresets || []),

            // Drag and drop & file details for logo & favicon (matching settings)
            isDraggingLogo: false,
            isDraggingFavicon: false,
            logoFileName: '',
            logoFileSize: '',
            faviconFileName: '',
            faviconFileSize: '',
            removeLogoFlag: false,
            removeFaviconFlag: false,

            // White-Label Pricing (Editable by reseller in this creation flow, initialized from settings)
            whiteLabelYearlyPrice: (config && config.financialSettings && config.financialSettings.white_label_yearly_price) ? parseInt(config.financialSettings.white_label_yearly_price) : 0,
            whiteLabelMonthlyPrice: (config && config.financialSettings && config.financialSettings.white_label_monthly_price) ? parseInt(config.financialSettings.white_label_monthly_price) : 0,
            whiteLabelSetupFee: (config && config.financialSettings && config.financialSettings.white_label_setup_fee) ? parseInt(config.financialSettings.white_label_setup_fee) : 0,
            whiteLabelYearlyPriceDisplay: '',
            whiteLabelMonthlyPriceDisplay: '',
            whiteLabelSetupFeeDisplay: '',

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
                    this.removeLogoFlag = false;
                } else {
                    this.faviconFileName = file.name;
                    this.faviconFileSize = this.formatFileSize(file.size);
                    this.removeFaviconFlag = false;
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
                    this.removeLogoFlag = true;
                    if (this.$refs.logoInput) this.$refs.logoInput.value = '';
                } else {
                    this.whiteLabelFaviconPreview = null;
                    this.faviconFileName = '';
                    this.faviconFileSize = '';
                    this.removeFaviconFlag = true;
                    if (this.$refs.faviconInput) this.$refs.faviconInput.value = '';
                }
            },

            updateWhiteLabelPrice(field, displayField) {
                const val = this.parsePriceInput(this[displayField]);
                this[field] = val;
                this[displayField] = val > 0 ? this.formatPriceInput(val) : '';
                this.syncInvoiceItemsFromModules();
            },

            // Capability Accordion & Selection State
            expandedModules: [],
            selectedFeatures: {},
            issueInvoice: true,
            invoiceStatus: 'unpaid',
            invoiceIssueDate: '',
            invoiceDueDate: '',
            invoiceTaxRate: (config && config.defaultTaxRate !== undefined) ? config.defaultTaxRate : 10,
            invoiceTaxMode: (config && config.defaultTaxMode) ? config.defaultTaxMode : 'invoice',
            invoiceDiscountAmount: 0,
            invoiceDiscountDisplay: '',
            invoiceDiscountUserModified: false,
            showModulesGrid: true,
            invoiceTerms: (config && config.invoiceNumbering && config.invoiceNumbering.terms) || '',
            invoiceNotes: (config && config.invoiceNumbering && config.invoiceNumbering.footer) || '',
            invoiceItems: [],
            enableModuleItemizedPricing: Boolean(config && (config.enableModuleItemizedPricing !== undefined ? config.enableModuleItemizedPricing : (config.financialSettings && config.financialSettings.enable_module_itemized_pricing))),
            invoiceNumbering: (config && config.invoiceNumbering) ? config.invoiceNumbering : {},
            invoiceNumberAuto: Boolean(config && config.invoiceNumbering && config.invoiceNumbering.auto),
            invoiceNumberOverride: '',
            taxRateLocked: true,
            taxModeLocked: true,
            invoiceTermsLocked: true,
            invoiceNotesLocked: true,

            toggleModuleAccordion(slug) {
                const s = slug.toLowerCase();
                if (this.expandedModules.includes(s)) {
                    this.expandedModules = this.expandedModules.filter(item => item !== s);
                } else {
                    this.expandedModules.push(s);
                }
            },
            formatAmount(num) {
                const n = Math.max(0, parseInt(num) || 0);
                return this.faNum(n.toLocaleString('en-US'));
            },

            getItemRowTotal(item) {
                const qty = Math.max(1, parseFloat(item.quantity) || 1);
                const price = Math.max(0, parseInt(item.unit_price) || 0);
                const disc = Math.max(0, parseInt(item.discount_amount) || 0);
                return Math.max(0, (qty * price) - disc);
            },

            getItemRowTotalLabel(item) {
                return this.formatAmount(this.getItemRowTotal(item)) + ' ' + this.currencyLabel;
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

            handleInvoiceDiscountInput(event) {
                this.invoiceDiscountUserModified = true;
                const v = this.parsePriceInput(event.target.value);
                // A discount can never exceed the tax-free subtotal: tax is
                // charged on what is left after the discount.
                const maxAllowed = this.getInvoiceSubtotal();
                this.invoiceDiscountAmount = Math.min(Math.max(0, v), maxAllowed);
                this.invoiceDiscountDisplay = this.invoiceDiscountAmount > 0 ? this.formatPriceInput(this.invoiceDiscountAmount) : '';
            },

            updateInvoiceDiscount() {
                this.invoiceDiscountUserModified = true;
                const v = this.parsePriceInput(this.invoiceDiscountDisplay);
                const maxAllowed = this.getInvoiceSubtotal();
                this.invoiceDiscountAmount = Math.min(Math.max(0, v), maxAllowed);
                this.invoiceDiscountDisplay = this.invoiceDiscountAmount > 0 ? this.formatPriceInput(this.invoiceDiscountAmount) : '';
            },

            buildInvoicePayload() {
                if (!this.issueInvoice) {
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
                    invoice_status: this.invoiceStatus,
                    invoice_discount_amount: this.invoiceDiscountAmount,
                    invoice_terms: this.invoiceTerms,
                    invoice_notes: this.invoiceNotes,
                    invoice_items: this.invoiceItems
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

            isModuleExpanded(slug) {
                return this.expandedModules.includes(slug.toLowerCase());
            },

            getModuleFeatures(slug) {
                const found = this.catalog.find(m => m.slug.toLowerCase() === slug.toLowerCase());
                return (found && found.features) ? found.features : [];
            },

            getModuleFeaturesCount(slug) {
                return this.getModuleFeatures(slug).length;
            },

            getSelectedFeaturesCount(slug) {
                const s = slug.toLowerCase();
                return (this.selectedFeatures[s] && Array.isArray(this.selectedFeatures[s])) ? this.selectedFeatures[s].length : 0;
            },

            isFeatureSelected(slug, featKey) {
                const s = slug.toLowerCase();
                return Boolean(this.selectedFeatures[s] && this.selectedFeatures[s].includes(featKey));
            },

            toggleFeature(slug, featKey) {
                const s = slug.toLowerCase();
                if (!this.selectedFeatures[s]) {
                    this.selectedFeatures[s] = [];
                }
                const idx = this.selectedFeatures[s].indexOf(featKey);
                if (idx > -1) {
                    this.selectedFeatures[s].splice(idx, 1);
                } else {
                    this.selectedFeatures[s].push(featKey);
                }
            },

            selectAllFeatures(slug) {
                const s = slug.toLowerCase();
                const feats = this.getModuleFeatures(s);
                this.selectedFeatures[s] = feats.map(f => f.key);
            },

            deselectAllFeatures(slug) {
                const s = slug.toLowerCase();
                this.selectedFeatures[s] = [];
            },

            get durationLabel() {
                const pkg = this.getActivePackage();
                if (pkg && pkg.price_type === 'one_time') {
                    return 'مادام‌العمر (یک‌بار پرداخت)';
                }
                if (pkg && pkg.price_type === 'free') {
                    return 'رایگان';
                }
                const opt = this.availableDurationOptions.find(o => String(o.value) === String(this.form.expires_in_months));
                if (opt) return opt.label;
                if (!this.form.expires_in_months || String(this.form.expires_in_months) === '1200' || String(this.form.expires_in_months) === '0') {
                    return (pkg && pkg.price_type === 'free') ? 'رایگان' : 'مادام‌العمر (یک‌بار پرداخت)';
                }
                return this.form.expires_in_months ? (this.faNum(this.form.expires_in_months) + ' ماهه') : 'انتخاب نشده';
            },

            formatPriceInput(value) {
                if (!value && value !== 0) return '';
                const num = parseInt(String(value).replace(/[^0-9]/g, ''), 10);
                if (isNaN(num)) return '';
                return this.faNum(num.toLocaleString('en-US'));
            },

            parsePriceInput(value) {
                return window.parsePriceInput ? window.parsePriceInput(value) : (parseInt(String(value).replace(/[^0-9]/g, ''), 10) || 0);
            },

            syncInvoiceItemsFromModules() {
                let activePkg = null;
                if (this.activePackageId) {
                    activePkg = this.packages.find(p => p.id === this.activePackageId) || null;
                }

                const isOneTime = activePkg && activePkg.price_type === 'one_time';
                const isInherentlyFree = activePkg && activePkg.price_type === 'free';
                const isPermanent = isOneTime || isInherentlyFree;
                const months = isPermanent ? null : (parseInt(this.form.expires_in_months, 10) || 12);
                const durationTitle = isOneTime ? 'مادام‌العمر (یک‌بار پرداخت)' : (isInherentlyFree ? 'رایگان' : (months === 12 ? 'یک ساله' : (months === 1 ? 'یک ماهه' : (months === 3 ? 'سه ماهه (فصلی)' : (months === 6 ? 'شش ماهه' : (this.faNum(months) + ' ماهه'))))));
                const items = [];

                if (activePkg) {
                    const packageUnitPrice = this.getPackageDurationPrice(activePkg, months || 12);
                    const packageDiscount = this.getPackageDurationDiscount(activePkg, months || 12);
                    const pkgSetupFee = parseInt(activePkg.setup_fee) || 0;
                    const pkgModules = Array.isArray(activePkg.modules) ? activePkg.modules : [];
                    const extraModules = this.selectedModules.filter(slug => !pkgModules.includes(slug));

                    let totalFeats = 0;
                    this.selectedModules.forEach(s => {
                        totalFeats += this.getSelectedFeaturesCount(s);
                    });

                    if (!this.enableModuleItemizedPricing) {
                        const pkgLimitsCount = this.getActiveLimitsCount();
                        const pkgLimitsNote = pkgLimitsCount > 0 ? (' (شامل ' + this.faNum(pkgLimitsCount) + ' سقف/محدودیت مصرف)') : '';
                        const pkgTitle = extraModules.length > 0
                            ? ('پکیج سفارشی (بر پایه «' + activePkg.name + '» + ' + this.faNum(extraModules.length) + ' ماژول الحاقی)' + pkgLimitsNote)
                            : ('پکیج «' + activePkg.name + '»' + pkgLimitsNote);

                        if (packageDiscount > 0 && !this.invoiceDiscountUserModified) {
                            this.invoiceDiscountAmount = packageDiscount;
                            this.invoiceDiscountDisplay = this.formatPriceInput(packageDiscount);
                        } else if (packageDiscount === 0 && !this.invoiceDiscountUserModified) {
                            this.invoiceDiscountAmount = 0;
                            this.invoiceDiscountDisplay = '';
                        }

                        const prefix = isOneTime ? 'لایسنس مادام‌العمر ' : (isInherentlyFree ? 'لایسنس رایگان ' : ('اشتراک ' + durationTitle + ' '));
                        items.push({
                            description: prefix + pkgTitle + ' (شامل ' + this.faNum(this.selectedModules.length) + ' ماژول و ' + this.faNum(totalFeats) + ' قابلیت)',
                            item_type: 'package',
                            item_id: 'package_' + activePkg.id,
                            duration_months: isPermanent ? 0 : months,
                            unit_price: packageUnitPrice,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: this.invoiceTaxRate,
                            _priceUnlocked: false,
                            _discountUnlocked: false,
                        });

                        if (pkgSetupFee > 0) {
                            items.push({
                                description: 'هزینه راه‌اندازی و کانفیگ اولیه پکیج «' + activePkg.name + '»',
                                item_type: 'setup_fee',
                                item_id: 'package_' + activePkg.id + '_setup',
                                duration_months: 0,
                                unit_price: pkgSetupFee,
                                quantity: 1,
                                discount_amount: 0,
                                tax_rate: this.invoiceTaxRate,
                                _priceUnlocked: false,
                                _discountUnlocked: false,
                            });
                        }
                    } else {
                        if (pkgSetupFee > 0) {
                            items.push({
                                description: 'هزینه راه‌اندازی و کانفیگ اولیه پکیج «' + activePkg.name + '»',
                                item_type: 'setup_fee',
                                item_id: 'package_' + activePkg.id + '_setup',
                                duration_months: 0,
                                unit_price: pkgSetupFee,
                                quantity: 1,
                                discount_amount: 0,
                                tax_rate: this.invoiceTaxRate,
                                _priceUnlocked: false,
                                _discountUnlocked: false,
                            });
                        }

                        this.selectedModules.forEach(slug => {
                            const sLower = slug.toLowerCase();
                            const mod = this.allModules.find(m => (m.slug || '').toLowerCase() === sLower) || {
                                slug: slug,
                                title: slug
                            };
                            const pricing = this.modulePricings[slug] || this.modulePricings[sLower] || null;
                            const catMod = this.catalog.find(c => (c.slug || '').toLowerCase() === sLower) || null;

                            let unitPrice = 0;
                            let setupFee = 0;
                            if (pricing && pricing.setup_fee) {
                                setupFee = parseInt(pricing.setup_fee) || 0;
                            } else if (catMod && catMod.base_setup_fee) {
                                setupFee = parseInt(catMod.base_setup_fee) || 0;
                            }

                            const mP = (pricing && parseInt(pricing.monthly_price) > 0) ? parseInt(pricing.monthly_price) : ((catMod && parseInt(catMod.base_monthly_price) > 0) ? parseInt(catMod.base_monthly_price) : 0);
                            const qP = (pricing && parseInt(pricing.quarterly_price) > 0) ? parseInt(pricing.quarterly_price) : ((catMod && parseInt(catMod.base_quarterly_price) > 0) ? parseInt(catMod.base_quarterly_price) : 0);
                            const sP = (pricing && parseInt(pricing.semi_annual_price) > 0) ? parseInt(pricing.semi_annual_price) : ((catMod && parseInt(catMod.base_semi_annual_price) > 0) ? parseInt(catMod.base_semi_annual_price) : 0);
                            const yP = (pricing && parseInt(pricing.yearly_price) > 0) ? parseInt(pricing.yearly_price) : ((catMod && parseInt(catMod.base_yearly_price) > 0) ? parseInt(catMod.base_yearly_price) : 0);
                            const yearlyBaseMod = yP > 0 ? yP : (mP * 12);

                            if (months === 1) {
                                unitPrice = mP;
                            } else if (months === 3) {
                                unitPrice = qP > 0 ? qP : Math.round((yearlyBaseMod / 12) * 3);
                            } else if (months === 6) {
                                unitPrice = sP > 0 ? sP : Math.round((yearlyBaseMod / 12) * 6);
                            } else if (months === 12) {
                                unitPrice = yearlyBaseMod;
                            } else if (months > 12) {
                                unitPrice = Math.round((yearlyBaseMod / 12) * months);
                            } else {
                                unitPrice = mP * months;
                            }

                            // Add features pricing
                            const selectedFeatKeys = this.selectedFeatures[slug] || [];
                            let featuresTotal = 0;
                            if (catMod && catMod.features && selectedFeatKeys.length > 0) {
                                catMod.features.forEach(feat => {
                                    if (selectedFeatKeys.includes(feat.key)) {
                                        let featPrice = 0;
                                        if (months === 1) featPrice = parseInt(feat.monthly_price || 0);
                                        else if (months === 3) featPrice = parseInt(feat.quarterly_price || 0) || (parseInt(feat.monthly_price || 0) * 3);
                                        else if (months === 6) featPrice = parseInt(feat.semi_annual_price || 0) || (parseInt(feat.monthly_price || 0) * 6);
                                        else if (months === 12) featPrice = parseInt(feat.yearly_price || 0) || (parseInt(feat.monthly_price || 0) * 12);
                                        else featPrice = Math.round(((parseInt(feat.yearly_price || 0) || (parseInt(feat.monthly_price || 0) * 12)) / 12) * months);
                                        featuresTotal += featPrice;
                                        setupFee += parseInt(feat.setup_fee || 0);
                                    }
                                });
                            }
                            unitPrice += featuresTotal;

                            const isExtra = extraModules.includes(slug);
                            const featCount = this.getSelectedFeaturesCount(slug);
                            const featLabel = featCount > 0 ? (' (شامل ' + this.faNum(featCount) + ' قابلیت)') : '';
                            const limitLabel = this.getModuleLimitsDescription(slug);

                            items.push({
                                description: 'اشتراک ' + durationTitle + ' ماژول «' + mod.title + '»' + (isExtra ? ' (الحاقی)' : '') + featLabel + limitLabel,
                                item_type: 'module',
                                item_id: slug,
                                duration_months: months,
                                unit_price: unitPrice,
                                quantity: 1,
                                discount_amount: 0,
                                tax_rate: this.invoiceTaxRate,
                                is_bundled: !isExtra,
                                is_extra: isExtra,
                                _priceUnlocked: false,
                                _discountUnlocked: false,
                            });

                            if (setupFee > 0) {
                                items.push({
                                    description: 'هزینه راه‌اندازی ماژول «' + mod.title + '»',
                                    item_type: 'setup_fee',
                                    item_id: slug + '_setup',
                                    duration_months: 0,
                                    unit_price: setupFee,
                                    quantity: 1,
                                    discount_amount: 0,
                                    tax_rate: this.invoiceTaxRate,
                                    _priceUnlocked: false,
                                    _discountUnlocked: false,
                                });
                            }
                        });
                    }

                } else {
                    this.selectedModules.forEach(slug => {
                        const mod = this.allModules.find(m => m.slug === slug) || {slug: slug, title: slug};
                        const pricing = this.modulePricings[slug] || null;
                        const catMod = this.catalog.find(c => c.slug === slug) || null;

                        let unitPrice = 0;
                        let setupFee = 0;

                        if (pricing && pricing.setup_fee) {
                            setupFee = parseInt(pricing.setup_fee);
                        } else if (catMod && catMod.base_setup_fee) {
                            setupFee = parseInt(catMod.base_setup_fee);
                        }

                        // 1. Resolve base module price according to cycle:
                        if (months === 1) {
                            unitPrice = (pricing && pricing.monthly_price) ? parseInt(pricing.monthly_price) : ((catMod && catMod.base_monthly_price) ? parseInt(catMod.base_monthly_price) : 0);
                        } else if (months === 3) {
                            const yearlyBaseStd3 = (pricing && parseInt(pricing.yearly_price) > 0)
                                ? parseInt(pricing.yearly_price)
                                : ((catMod && parseInt(catMod.base_yearly_price) > 0)
                                    ? parseInt(catMod.base_yearly_price)
                                    : (((pricing && pricing.monthly_price) ? parseInt(pricing.monthly_price) : ((catMod && catMod.base_monthly_price) ? parseInt(catMod.base_monthly_price) : 0)) * 12));
                            unitPrice = (pricing && pricing.quarterly_price && parseInt(pricing.quarterly_price) > 0)
                                ? parseInt(pricing.quarterly_price)
                                : ((catMod && catMod.base_quarterly_price && parseInt(catMod.base_quarterly_price) > 0)
                                    ? parseInt(catMod.base_quarterly_price)
                                    : Math.round((yearlyBaseStd3 / 12) * 3));
                        } else if (months === 6) {
                            const yearlyBaseStd6 = (pricing && parseInt(pricing.yearly_price) > 0)
                                ? parseInt(pricing.yearly_price)
                                : ((catMod && parseInt(catMod.base_yearly_price) > 0)
                                    ? parseInt(catMod.base_yearly_price)
                                    : (((pricing && pricing.monthly_price) ? parseInt(pricing.monthly_price) : ((catMod && catMod.base_monthly_price) ? parseInt(catMod.base_monthly_price) : 0)) * 12));
                            unitPrice = (pricing && pricing.semi_annual_price && parseInt(pricing.semi_annual_price) > 0)
                                ? parseInt(pricing.semi_annual_price)
                                : ((catMod && catMod.base_semi_annual_price && parseInt(catMod.base_semi_annual_price) > 0)
                                    ? parseInt(catMod.base_semi_annual_price)
                                    : Math.round((yearlyBaseStd6 / 12) * 6));
                        } else if (months === 12) {
                            unitPrice = (pricing && pricing.yearly_price && parseInt(pricing.yearly_price) > 0)
                                ? parseInt(pricing.yearly_price)
                                : ((catMod && catMod.base_yearly_price && parseInt(catMod.base_yearly_price) > 0)
                                    ? parseInt(catMod.base_yearly_price)
                                    : (((pricing && pricing.monthly_price) ? parseInt(pricing.monthly_price) : ((catMod && catMod.base_monthly_price) ? parseInt(catMod.base_monthly_price) : 0)) * 12));
                        } else if (months > 12) {
                            const yearlyBase = (pricing && pricing.yearly_price && parseInt(pricing.yearly_price) > 0)
                                ? parseInt(pricing.yearly_price)
                                : ((catMod && catMod.base_yearly_price && parseInt(catMod.base_yearly_price) > 0)
                                    ? parseInt(catMod.base_yearly_price)
                                    : (((pricing && pricing.monthly_price) ? parseInt(pricing.monthly_price) : ((catMod && catMod.base_monthly_price) ? parseInt(catMod.base_monthly_price) : 0)) * 12));
                            unitPrice = Math.round((yearlyBase / 12) * months);
                        } else {
                            const monthlyBase = (pricing && pricing.monthly_price) ? parseInt(pricing.monthly_price) : ((catMod && catMod.base_monthly_price) ? parseInt(catMod.base_monthly_price) : 0);
                            unitPrice = monthlyBase * months;
                        }

                        // 2. Add selected sub-features pricing
                        const selectedFeatKeys = this.selectedFeatures[slug] || [];
                        let featuresTotal = 0;
                        if (catMod && catMod.features && selectedFeatKeys.length > 0) {
                            catMod.features.forEach(feat => {
                                if (selectedFeatKeys.includes(feat.key)) {
                                    let featPrice = 0;
                                    if (months === 1) {
                                        featPrice = parseInt(feat.monthly_price || 0);
                                    } else if (months === 3) {
                                        featPrice = parseInt(feat.quarterly_price || 0) || (parseInt(feat.monthly_price || 0) * 3);
                                    } else if (months === 6) {
                                        featPrice = parseInt(feat.semi_annual_price || 0) || (parseInt(feat.monthly_price || 0) * 6);
                                    } else if (months === 12) {
                                        featPrice = parseInt(feat.yearly_price || 0) || (parseInt(feat.monthly_price || 0) * 12);
                                    } else {
                                        featPrice = Math.round(((parseInt(feat.yearly_price || 0) || (parseInt(feat.monthly_price || 0) * 12)) / 12) * months);
                                    }
                                    featuresTotal += featPrice;
                                    setupFee += parseInt(feat.setup_fee || 0);
                                }
                            });
                        }
                        unitPrice += featuresTotal;

                        const featCount = this.getSelectedFeaturesCount(slug);
                        const featLabel = featCount > 0 ? (' (شامل ' + this.faNum(featCount) + ' قابلیت)') : '';
                        const limitLabel = this.getModuleLimitsDescription(slug);

                        items.push({
                            description: 'اشتراک ' + durationTitle + ' ماژول ' + mod.title + featLabel + limitLabel,
                            item_type: 'module',
                            item_id: slug,
                            duration_months: months,
                            unit_price: unitPrice,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: this.invoiceTaxRate,
                            _priceUnlocked: false,
                            _discountUnlocked: false,
                        });

                        if (setupFee > 0) {
                            items.push({
                                description: 'هزینه راه‌اندازی و کانفیگ اولیه ماژول ' + mod.title,
                                item_type: 'setup_fee',
                                item_id: slug + '_setup',
                                duration_months: 0,
                                unit_price: setupFee,
                                quantity: 1,
                                discount_amount: 0,
                                tax_rate: this.invoiceTaxRate,
                                _priceUnlocked: false,
                                _discountUnlocked: false,
                            });
                        }
                    });
                }

                // Custom Domain Pricing Integration
                if (this.domainType === 'custom' || (this.form.custom_domain && this.form.custom_domain.trim())) {
                    const yearly = parseInt(this.financialSettings.custom_domain_yearly_price || 0);
                    const monthly = parseInt(this.financialSettings.custom_domain_monthly_price || 0);
                    const setup = parseInt(this.financialSettings.custom_domain_setup_fee || 0);

                    let domainPrice = 0;
                    if (months === 12 && yearly > 0) {
                        domainPrice = yearly;
                    } else if (yearly > 0) {
                        domainPrice = Math.round((yearly / 12) * months);
                    } else if (monthly > 0) {
                        domainPrice = monthly * months;
                    }

                    if (domainPrice > 0) {
                        items.push({
                            description: 'لایسنس و اتصال دامنه اختصاصی (' + (this.form.custom_domain || 'سفارشی') + ') برای ' + durationTitle,
                            item_type: 'custom_domain',
                            item_id: 'custom_domain',
                            duration_months: months,
                            unit_price: domainPrice,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: this.invoiceTaxRate,
                            _priceUnlocked: false,
                            _discountUnlocked: false,
                        });
                    }

                    if (setup > 0) {
                        items.push({
                            description: 'هزینه کانفیگ و راه‌اندازی اولیه دامنه اختصاصی',
                            item_type: 'setup_fee',
                            item_id: 'custom_domain_setup',
                            duration_months: 0,
                            unit_price: setup,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: this.invoiceTaxRate,
                            _priceUnlocked: false,
                            _discountUnlocked: false,
                        });
                    }
                }

                // White-Label Branding Pricing Integration
                if (this.whiteLabelType === 'custom') {
                    const wlYearly = parseInt(this.whiteLabelYearlyPrice || 0);
                    const wlMonthly = parseInt(this.whiteLabelMonthlyPrice || 0);
                    const wlSetup = parseInt(this.whiteLabelSetupFee || 0);

                    let wlPrice = 0;
                    if (months === 12 && wlYearly > 0) {
                        wlPrice = wlYearly;
                    } else if (wlYearly > 0) {
                        wlPrice = Math.round((wlYearly / 12) * months);
                    } else if (wlMonthly > 0) {
                        wlPrice = wlMonthly * months;
                    }

                    if (wlPrice > 0) {
                        items.push({
                            description: 'لایسنس برندینگ اختصاصی وایت‌لیبل (' + (this.whiteLabelBrandTitle || this.form.name || 'سفارشی') + ') برای ' + durationTitle,
                            item_type: 'white_label',
                            item_id: 'white_label',
                            duration_months: months,
                            unit_price: wlPrice,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: this.invoiceTaxRate,
                            _priceUnlocked: false,
                            _discountUnlocked: false,
                        });
                    }

                    if (wlSetup > 0) {
                        items.push({
                            description: 'هزینه کانفیگ و پیاده‌سازی تم و برندینگ اختصاصی (وایت‌لیبل)',
                            item_type: 'setup_fee',
                            item_id: 'white_label_setup',
                            duration_months: 0,
                            unit_price: wlSetup,
                            quantity: 1,
                            discount_amount: 0,
                            tax_rate: this.invoiceTaxRate,
                            _priceUnlocked: false,
                            _discountUnlocked: false,
                        });
                    }
                }

                const keptCustomItems = (this.invoiceItems || []).filter(it =>
                    it.item_type === 'custom' &&
                    !(it.description && it.description.startsWith('راه‌اندازی سامانه CRM اختصاصی'))
                );
                keptCustomItems.forEach(c => items.push(c));
                this.invoiceItems = items;
            },

            addCustomInvoiceItem() {
                this.invoiceItems.push({
                    description: '',
                    item_type: 'custom',
                    item_id: '',
                    unit_price: 0,
                    quantity: 1,
                    discount_amount: 0,
                    tax_rate: this.invoiceTaxRate,
                    _priceUnlocked: true,
                    _discountUnlocked: true,
                });
            },

            removeInvoiceItem(index) {
                if (this.invoiceItems.length > 1) {
                    this.invoiceItems.splice(index, 1);
                }
            },

            getInvoiceSubtotal() {
                return this.invoiceItems.reduce((sum, it) => {
                    const qty = Math.max(1, parseFloat(it.quantity) || 1);
                    const price = Math.max(0, parseInt(it.unit_price) || 0);
                    const disc = Math.max(0, parseInt(it.discount_amount) || 0);
                    return sum + Math.max(0, (qty * price) - disc);
                }, 0);
            },

            getInvoiceDiscount() {
                const subtotal = this.getInvoiceSubtotal();
                return Math.min(Math.max(0, parseInt(this.invoiceDiscountAmount) || 0), subtotal);
            },

            /**
             * Spread the invoice-level discount over the line items,
             * proportionally, using the largest-remainder method so the
             * shares add up to exactly the discount. Mirrors
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
             * Tax is charged on the NET base (subtotal minus discount),
             * never on the pre-discount amount.
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

            setQuickDueDate(days) {
                const now = new Date();
                now.setDate(now.getDate() + days);
                const formatter = new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
                const parts = formatter.format(now).split('/');
                if (parts.length === 3) {
                    const formatted = parts[0] + '/' + parts[1] + '/' + parts[2];
                    this.invoiceDueDate = formatted;
                    const dueInput = document.querySelector('input[name="invoice_due_date"]');
                    if (dueInput) dueInput.value = formatted;
                }
            },

            init() {
                if (this.whiteLabelYearlyPrice > 0) {
                    this.whiteLabelYearlyPriceDisplay = this.formatPriceInput(this.whiteLabelYearlyPrice);
                }
                if (this.whiteLabelMonthlyPrice > 0) {
                    this.whiteLabelMonthlyPriceDisplay = this.formatPriceInput(this.whiteLabelMonthlyPrice);
                }
                if (this.whiteLabelSetupFee > 0) {
                    this.whiteLabelSetupFeeDisplay = this.formatPriceInput(this.whiteLabelSetupFee);
                }
                if (this.activePackageId) {
                    const found = this.packages.find(p => p.id === this.activePackageId);
                    if (found) {
                        this.applyPackage(found);
                        return;
                    }
                }
                // Default: no modules checked
                this.selectedModules = [];
                this.selectedFeatures = {};
            },

            togglePackage(pkg) {
                if (this.activePackageId === pkg.id) {
                    this.activePackageId = null;
                    if (!this.invoiceDiscountUserModified) {
                        this.invoiceDiscountAmount = 0;
                        this.invoiceDiscountDisplay = '';
                    }
                    this.showToast('انتخاب پکیج لغو شد.');
                    this.syncInvoiceItemsFromModules();
                } else {
                    this.applyPackage(pkg);
                }
            },

            applyPackage(pkg) {
                this.activePackageId = pkg.id;
                this.invoiceDiscountUserModified = false;
                const r = this.currentReseller;
                const pkgModules = Array.isArray(pkg.modules) ? pkg.modules : [];

                if (!r || !r.allowed_slugs || r.allowed_slugs.length === 0) {
                    this.selectedModules = [...pkgModules];
                } else {
                    const allowedSet = r.allowed_slugs.map(s => s.toLowerCase());
                    this.selectedModules = pkgModules.filter(slug => allowedSet.includes(slug.toLowerCase()));
                }

                // Apply bundled features defined in the package
                const pkgFeatures = Array.isArray(pkg.features) ? pkg.features : [];
                if (pkgFeatures.length > 0) {
                    this.selectedModules.forEach(slug => {
                        const modFeatures = this.getModuleFeatures(slug);
                        const modFeatKeys = modFeatures.map(f => f.key);
                        this.selectedFeatures[slug] = pkgFeatures.filter(k => modFeatKeys.includes(k));
                    });
                } else {
                    this.selectedModules.forEach(slug => {
                        const modFeatures = this.getModuleFeatures(slug);
                        this.selectedFeatures[slug] = modFeatures.map(f => f.key);
                    });
                }

                // Apply package limits
                if (pkg.limits && typeof pkg.limits === 'object') {
                    this.moduleLimits = JSON.parse(JSON.stringify(pkg.limits));
                } else {
                    this.moduleLimits = {};
                }

                let totalFeats = 0;
                this.selectedModules.forEach(s => {
                    totalFeats += (this.selectedFeatures[s] ? this.selectedFeatures[s].length : 0);
                });

                this.$nextTick(() => {
                    const priceType = pkg.price_type || 'subscription';
                    if (priceType === 'one_time' || priceType === 'free') {
                        this.form.expires_in_months = null;
                    } else {
                        const available = this.availableDurationOptions;
                        if (available && available.length > 0) {
                            const hasCurrent = available.some(o => String(o.value) === String(this.form.expires_in_months));
                            if (!hasCurrent) {
                                const preferYearly = available.find(o => String(o.value) === '12');
                                this.form.expires_in_months = preferYearly ? '12' : String(available[0].value);
                            }
                        } else {
                            this.form.expires_in_months = '';
                        }
                    }
                    this.syncInvoiceItemsFromModules();
                });

                this.showToast(`پکیج «${pkg.name}» اعمال شد (${this.faNum(this.selectedModules.length)} ماژول، ${this.faNum(totalFeats)} قابلیت)`);
            },

            async saveCurrentAsPackage() {
                if (!this.newPackageName.trim()) {
                    this.showToast('لطفاً نام پکیج را وارد نمایید.', 'error');
                    return;
                }
                if (this.selectedModules.length === 0) {
                    this.showToast('حداقل باید یک ماژول برای ذخیره در پکیج انتخاب کنید.', 'error');
                    return;
                }

                this.savingPackage = true;
                try {
                    const allSelectedFeatures = [];
                    this.selectedModules.forEach(slug => {
                        if (this.selectedFeatures[slug] && Array.isArray(this.selectedFeatures[slug])) {
                            allSelectedFeatures.push(...this.selectedFeatures[slug]);
                        }
                    });

                    const res = await axios.post(config.packageStoreUrl, {
                        name: this.newPackageName.trim(),
                        description: this.newPackageDescription.trim(),
                        color: this.newPackageColor,
                        icon: this.newPackageIcon,
                        modules: this.selectedModules,
                        features: allSelectedFeatures,
                        limits: this.moduleLimits,
                    }, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    if (res.data && res.data.success && res.data.package) {
                        this.packages.push(res.data.package);
                        this.activePackageId = res.data.package.id;
                        this.savePackageModalOpen = false;
                        this.newPackageName = '';
                        this.newPackageDescription = '';
                        this.showToast(res.data.message || 'پکیج جدید با موفقیت ذخیره شد.', 'success');
                    }
                } catch (e) {
                    const msg = e.response?.data?.message || 'خطا در ذخیره پکیج.';
                    this.showToast(msg, 'error');
                } finally {
                    this.savingPackage = false;
                }
            },

            isModuleAllowed(slug) {
                const r = this.currentReseller;
                if (!r) return true;
                if (!r.allowed_slugs || r.allowed_slugs.length === 0) {
                    return true;
                }
                return r.allowed_slugs.map(s => s.toLowerCase()).includes(slug.toLowerCase());
            },

            allowedModulesCount() {
                const r = this.currentReseller;
                if (!r) return this.allModules.length;
                if (!r.allowed_slugs || r.allowed_slugs.length === 0) {
                    return this.allModules.length;
                }
                return r.allowed_slugs.length;
            },

            syncModulesForReseller(r) {
                if (!r || !r.allowed_slugs || r.allowed_slugs.length === 0) {
                    this.selectedModules = this.allModules.map(m => m.slug);
                } else {
                    const allowedSet = r.allowed_slugs.map(s => s.toLowerCase());
                    this.selectedModules = this.allModules
                        .filter(m => allowedSet.includes(m.slug.toLowerCase()))
                        .map(m => m.slug);
                }
            },

            selectAllAllowedModules() {
                const r = this.currentReseller;
                if (!r || !r.allowed_slugs || r.allowed_slugs.length === 0) {
                    this.selectedModules = this.allModules.map(m => m.slug);
                } else {
                    const allowedSet = r.allowed_slugs.map(s => s.toLowerCase());
                    this.selectedModules = this.allModules
                        .filter(m => allowedSet.includes(m.slug.toLowerCase()))
                        .map(m => m.slug);
                }
                this.syncInvoiceItemsFromModules();
            },

            deselectAllModules() {
                this.selectedModules = [];
                this.syncInvoiceItemsFromModules();
            },

            isModuleInActivePackage(slug) {
                if (!this.activePackageId) return false;
                const pkg = this.packages.find(p => p.id === this.activePackageId);
                if (!pkg || !Array.isArray(pkg.modules)) return false;
                return pkg.modules.includes(slug);
            },

            isModuleLockedByPackage(slug) {
                return false;
            },

            isCustomPackage() {
                if (!this.activePackageId) return false;
                const pkg = this.packages.find(p => p.id === this.activePackageId);
                if (!pkg) return false;
                const pkgModules = Array.isArray(pkg.modules) ? pkg.modules : [];

                // Check if any module was added or removed compared to pkg.modules
                if (this.selectedModules.length !== pkgModules.length) return true;
                if (this.selectedModules.some(s => !pkgModules.includes(s))) return true;
                if (pkgModules.some(s => !this.selectedModules.includes(s))) return true;

                // Check if any feature was added or removed for any selected module
                const pkgFeatures = Array.isArray(pkg.features) ? pkg.features : [];
                for (const slug of this.selectedModules) {
                    const modFeatures = this.getModuleFeatures(slug);
                    const modFeatKeys = modFeatures.map(f => f.key);
                    const expectedFeats = pkgFeatures.length > 0
                        ? pkgFeatures.filter(k => modFeatKeys.includes(k))
                        : modFeatKeys;
                    const currentFeats = this.selectedFeatures[slug] || [];
                    if (currentFeats.length !== expectedFeats.length) return true;
                    if (expectedFeats.some(k => !currentFeats.includes(k))) return true;
                    if (currentFeats.some(k => !expectedFeats.includes(k))) return true;
                }

                return false;
            },

            getExtraModulesCount() {
                if (!this.activePackageId) return 0;
                const pkg = this.packages.find(p => p.id === this.activePackageId);
                if (!pkg) return 0;
                const pkgModules = Array.isArray(pkg.modules) ? pkg.modules : [];
                return this.selectedModules.filter(s => !pkgModules.includes(s)).length;
            },

            getPackageName() {
                if (!this.activePackageId) return 'سفارشی';
                const pkg = this.packages.find(p => p.id === this.activePackageId);
                if (!pkg) return 'سفارشی';
                if (this.isCustomPackage()) {
                    return 'پکیج سفارشی (بر پایه ' + pkg.name + ')';
                }
                return pkg.name;
            },

            faNum(str) {
                return window.faNum ? window.faNum(str) : String(str);
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
                this.showToast('رمز عبور قوی و تصادفی ایجاد شد.', 'success');
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
                if (this.domainType === 'subdomain') {
                    if (!this.form.subdomain || !this.form.subdomain.trim()) {
                        this.errors.subdomain = ['لطفاً شناسه سابدامین را وارد نمایید.'];
                        this.showToast('لطفاً شناسه سابدامین را وارد کنید.', 'error');
                        return false;
                    }
                    if (this.subdomainStatus === 'unavailable') {
                        this.errors.subdomain = ['این سابدامین قابل استفاده نیست. لطفاً عنوان دیگری انتخاب کنید.'];
                        this.showToast('این سابدامین آزاد نیست.', 'error');
                        return false;
                    }
                } else if (this.domainType === 'custom') {
                    if (!this.form.custom_domain || !this.form.custom_domain.trim()) {
                        this.errors.custom_domain = ['لطفاً نشانی دامنه اختصاصی خریدار را وارد نمایید.'];
                        this.showToast('لطفاً نشانی دامنه اختصاصی را وارد کنید.', 'error');
                        return false;
                    }
                    if (this.domainStatus === 'unavailable') {
                        this.errors.custom_domain = ['دامنه اختصاصی وارد شده معتبر یا آزاد نیست.'];
                        this.showToast('دامنه اختصاصی وارد شده معتبر یا آزاد نیست.', 'error');
                        return false;
                    }
                }
                return true;
            },

            validateStep2() {
                this.errors = {};
                if (!this.form.sub_account_id) {
                    this.errors.sub_account_id = ['لطفاً حساب خریدار (زیرمجموعه) را انتخاب کنید.'];
                    this.showToast('لطفاً حساب خریدار (زیرمجموعه) را انتخاب فرمایید.', 'error');
                    return false;
                }
                return true;
            },

            validateStep3() {
                if (this.selectedModules.length === 0) {
                    this.showToast('لطفاً حداقل یک ماژول مجاز را برای فعال‌سازی در سامانه انتخاب فرمایید.', 'error');
                    return false;
                }
                const pkg = this.getActivePackage();
                if (pkg && pkg.price_type !== 'one_time' && pkg.price_type !== 'free') {
                    if (this.availableDurationOptions.length === 0) {
                        this.showToast('پکیج انتخابی فاقد هرگونه دوره زمانی فعال است. لطفاً دوره دیگری انتخاب فرمایید یا پکیج را در بخش پکیج‌ها ویرایش کنید.', 'error');
                        return false;
                    }
                    if (!this.form.expires_in_months) {
                        this.showToast('لطفاً مدت زمان اعتبار اولیه لایسنس را انتخاب فرمایید.', 'error');
                        return false;
                    }
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
                    if (targetStep >= 3 && !this.validateStep2()) {
                        this.currentStep = 2;
                        return;
                    }
                } else if (this.currentStep === 2) {
                    if (!this.validateStep2()) return;
                }
                this.currentStep = targetStep;
                if (this.currentStep === 3) {
                    this.syncInvoiceItemsFromModules();
                    if (!this.invoiceIssueDate) this.setTodayIssueDate();
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            window.jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                            });
                        }
                    });
                }
                window.scrollTo({top: 0, behavior: 'smooth'});
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
                        this.syncInvoiceItemsFromModules();
                        if (!this.invoiceIssueDate) this.setTodayIssueDate();
                        this.$nextTick(() => {
                            if (window.jalaliDatepicker) {
                                window.jalaliDatepicker.startWatch({
                                    selector: '[data-jdp]',
                                    minDate: 'attr',
                                });
                            }
                        });
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
                try {
                    const res = await fetch(`{{ route('reseller.tenants.check-domain') }}?domain=${encodeURIComponent(dom)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (!data.allowed) {
                        this.domainStatus = 'unavailable';
                        this.domainMsg = data.message || 'قابلیت اتصال دامنه اختصاصی غیرفعال است.';
                    } else if (!data.valid || !data.available) {
                        this.domainStatus = 'unavailable';
                        this.domainMsg = data.message || 'دامنه نامعتبر است یا قبلاً ثبت شده است.';
                    } else if (data.dns) {
                        this.dnsInfo = data.dns;
                        if (data.dns.status === 'verified') {
                            this.domainStatus = 'verified';
                            this.domainMsg = data.dns.message;
                        } else {
                            this.domainStatus = 'pending';
                            this.domainMsg = data.dns.message;
                        }
                    }
                } catch (e) {
                    this.domainStatus = 'idle';
                } finally {
                    this.domainChecking = false;
                }
            },

            copyToClipboard(text, msg = 'کپی شد') {
                if (!text) return;
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.showToast(msg, 'success');
                    });
                } else {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        this.showToast(msg, 'success');
                    } catch (err) {
                        this.showToast('خطا در کپی', 'error');
                    }
                    textArea.remove();
                }
            },

            async submitForm(formEl) {
                if (this.submitting) return;
                if (!this.validateStep1() || !this.validateStep2() || !this.validateStep3()) {
                    return;
                }

                this.submitting = true;
                this.errors = {};
                this.generalError = '';

                const formData = new FormData(formEl);
                formData.set('domain_type', this.domainType);
                if (this.form.sub_account_id) {
                    formData.set('sub_account_id', this.form.sub_account_id);
                }
                if (this.form.admin_name) {
                    formData.set('admin_name', this.form.admin_name);
                }
                if (this.form.admin_email) {
                    formData.set('admin_email', this.form.admin_email);
                }
                if (this.form.admin_mobile) {
                    formData.set('admin_mobile', this.form.admin_mobile);
                }
                if (this.domainType === 'subdomain') {
                    formData.set('custom_domain', '');
                }

                const derivedName = (this.selectedSubAccount ? (this.selectedSubAccount.company_name || this.selectedSubAccount.name) : '') || (this.whiteLabelBrandTitle || '').trim() || this.form.subdomain || 'سامانه جدید';
                formData.set('name', (this.form.name || derivedName).trim());

                // White-Label Branding Data
                formData.set('white_label_type', this.whiteLabelType);
                if (this.whiteLabelType === 'custom') {
                    formData.set('branding[brand_title]', (this.whiteLabelBrandTitle || derivedName).trim());
                    formData.set('branding[brand_primary_color]', this.whiteLabelPrimaryColor || '#4f46e5');
                    formData.set('branding[brand_footer_text]', (this.whiteLabelFooterText || '').trim());
                    formData.set('branding[support_phone]', (this.whiteLabelSupportPhone || '').trim());
                    formData.set('branding[support_email]', (this.whiteLabelSupportEmail || '').trim());
                    if (this.removeLogoFlag) {
                        formData.set('remove_tenant_brand_logo', '1');
                    }
                    if (this.removeFaviconFlag) {
                        formData.set('remove_tenant_brand_favicon', '1');
                    }
                }

                formData.set('issue_invoice', this.issueInvoice ? '1' : '0');
                if (this.activePackageId) {
                    formData.set('package_id', this.activePackageId);
                }
                if (this.issueInvoice) {
                    formData.set('invoice_status', 'unpaid');
                    formData.set('invoice_issue_date', this.invoiceIssueDate || '');
                    formData.set('invoice_due_date', this.invoiceDueDate || '');
                    formData.set('invoice_tax_rate', this.invoiceTaxRate || 0);
                    formData.set('invoice_tax_mode', 'invoice');
                    formData.set('invoice_discount_amount', this.invoiceDiscountAmount || 0);
                    formData.set('invoice_terms', this.invoiceTerms || '');
                    formData.set('invoice_notes', this.invoiceNotes || '');
                    formData.set('invoice_auto', this.invoiceNumberAuto ? '1' : '0');
                    formData.set('invoice_number', this.invoiceNumberAuto ? '' : (this.invoiceNumberOverride || '').trim());
                    formData.set('tax_from_settings', this.taxRateLocked ? '1' : '0');
                }
                if (this.issueInvoice) {
                    this.invoiceItems.forEach((it, idx) => {
                        formData.set(`invoice_items[${idx}][description]`, it.description || '');
                        formData.set(`invoice_items[${idx}][title]`, it.description || '');
                        formData.set(`invoice_items[${idx}][item_type]`, it.item_type || 'module');
                        formData.set(`invoice_items[${idx}][item_id]`, it.item_id || '');
                        formData.set(`invoice_items[${idx}][duration_months]`, it.duration_months || this.form.expires_in_months || 12);
                        formData.set(`invoice_items[${idx}][unit_price]`, it.unit_price || 0);
                        formData.set(`invoice_items[${idx}][quantity]`, it.quantity || 1);
                        formData.set(`invoice_items[${idx}][discount_amount]`, it.discount_amount || 0);
                        formData.set(`invoice_items[${idx}][tax_rate]`, it.tax_rate || 0);
                    });
                }


                // Append selected features for each module
                Object.keys(this.selectedFeatures).forEach(slug => {
                    if (this.selectedModules.includes(slug)) {
                        (this.selectedFeatures[slug] || []).forEach(fKey => {
                            formData.append(`features[${slug}][]`, fKey);
                        });
                    }
                });

                // Append configured limits
                if (this.moduleLimits && typeof this.moduleLimits === 'object') {
                    Object.keys(this.moduleLimits).forEach(key => {
                        const val = this.moduleLimits[key];
                        formData.set(`limits[${key}]`, (val !== null && val !== undefined) ? val : '');
                    });
                }

                try {
                    const res = await fetch(`{{ route('reseller.tenants.store') }}`, {
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
                        this.createdTenant = data.tenant;
                        this.createdInvoice = data.invoice || null;
                        this.redirectUrl = data.redirect_url;
                        this.showToast(data.message, 'success');
                    } else if (res.status === 422) {
                        this.errors = data.errors || {};
                        this.generalError = data.message || 'لطفاً مقادیر وارد شده را بررسی فرمایید.';
                        this.showToast(this.generalError, 'error');
                        if (this.errors.name || this.errors.subdomain || this.errors.custom_domain) {
                            this.currentStep = 1;
                        } else if (this.errors.reseller_id || this.errors.admin_name || this.errors.password || this.errors.password_confirmation || this.errors.admin_mobile || this.errors.admin_email) {
                            this.currentStep = 2;
                        }
                    } else {
                        this.generalError = data.message || 'خطا در ایجاد سامانه.';
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

            resetForm() {
                this.createdTenant = null;
                this.currentStep = 1;
                this.redirectUrl = '';
                this.errors = {};
                this.generalError = '';
                this.domainType = 'subdomain';
                this.subdomainStatus = 'idle';
                this.subdomainMsg = '';
                this.domainStatus = 'idle';
                this.domainMsg = '';
                this.dnsInfo = null;
                this.dnsGuideOpen = false;
                this.whiteLabelType = 'default';
                this.whiteLabelBrandTitle = '';
                this.whiteLabelPrimaryColor = (config && config.financialSettings && config.financialSettings.brand_primary_color) || '#4f46e5';
                this.whiteLabelLogoPreview = null;
                this.whiteLabelFaviconPreview = null;
                this.whiteLabelFooterText = '';
                this.whiteLabelSupportPhone = '';
                this.whiteLabelSupportEmail = '';
                this.showPassword = false;
                this.showConfirmPassword = false;
                this.form = {
                    name: '',
                    subdomain: '',
                    custom_domain: '',
                    reseller_id: this.currentReseller ? this.currentReseller.id : '',
                    admin_name: '',
                    admin_mobile: '',
                    admin_email: '',
                    password: '',
                    password_confirmation: '',
                    expires_in_months: (config && config.defaultDuration) ? String(config.defaultDuration) : '12',
                };
                this.selectedModules = [];
                this.selectedFeatures = {};
            }
        };
    }

    if (window.Alpine) {
        Alpine.data('tenantCreator', tenantCreator);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('tenantCreator', tenantCreator);
        });
    }
</script>
