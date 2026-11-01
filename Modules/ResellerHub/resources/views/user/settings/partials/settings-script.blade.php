@php
    use Modules\ResellerHub\App\Enums\OnboardingInternalRoute;
    use Modules\ResellerHub\App\Enums\OnboardingStepIcon;
    use Modules\ResellerHub\App\Enums\OnboardingStepPreset;
    use Modules\ResellerHub\App\Models\ResellerSetting;
@endphp
<script>
    window.faNum = window.faNum || function (str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    };

    window.formatNumber = window.formatNumber || function (num) {
        if (num === null || num === undefined || isNaN(num)) return '۰';
        return window.faNum(Number(num).toLocaleString('en-US'));
    };

    window.resellerSettingsConfig = {
        initialTab: @json($initialTab),
        defaultDuration: @json((string)$defaultDuration),
        subdomainPattern: @json($settings['subdomain_pattern'] ?? 'crm-{rand}'),
        logoUrl: @json($reseller->brand_logo ? asset('storage/' . $reseller->brand_logo) : ''),
        faviconUrl: @json($reseller->brand_favicon ? asset('storage/' . $reseller->brand_favicon) : ''),
        sealUrl: @json(!empty($settings['brand_seal_signature']) ? asset('storage/' . $settings['brand_seal_signature']) : ''),
        catalog: @json($catalog ?? []),
        savePricingsUrl: @json(route('reseller.pricings.store')),
        currency: @json($currency ?? ($settings['currency'] ?? 'toman')),
        currencyLabel: @json(reseller_currency_label($currency ?? ($settings['currency'] ?? 'toman'))),
        isRial: @json(reseller_is_rial($currency ?? ($settings['currency'] ?? 'toman'))),
        defaultTaxRate: @json((float)($settings['default_tax_rate'] ?? 10)),
        resellerRoundingMode: @json($settings['reseller_rounding_mode'] ?? 'none'),
        resellerRoundingFactor: @json((int)($settings['reseller_rounding_factor'] ?? (($settings['currency'] ?? 'toman') === 'rial' ? 10000 : 1000))),
        invoicePrefix: @json($settings['invoice_prefix'] ?? 'INV-'),
        invoiceMiddle: @json($settings['invoice_middle_prefix'] ?? (string)reseller_jalali_year()),
        invoiceSuffix: @json($settings['invoice_suffix'] ?? ''),
        invoicePadding: @json((int)($settings['invoice_padding'] ?? 4)),
        invoiceAuto: @json(!empty($settings['invoice_auto'])),
        allowCustomDomains: @json($allowCustomDomain),
        maxAdvanceRenewalCycles: @json((string)($settings['max_advance_renewal_cycles'] ?? '1')),
        enableDefaultPackageRegistration: @json($enableDefaultPackageRegistration),
        defaultPackageId: @json($defaultPackageId),
        customDomainYearlyPrice: @json($customDomainYearlyPrice),
        customDomainMonthlyPrice: @json($customDomainMonthlyPrice),
        customDomainSetupFee: @json($customDomainSetupFee),
        cnameTarget: @json($cnameTarget),
        brandPrimaryColor: @json($brandPrimaryColor),
        whiteLabelYearlyPrice: @json($whiteLabelYearlyPrice),
        whiteLabelMonthlyPrice: @json($whiteLabelMonthlyPrice),
        whiteLabelSetupFee: @json($whiteLabelSetupFee),
        colorPresets: @json(reseller_package_color_presets()),
        durationOptions: @json(reseller_standard_duration_list()),
        onboardingTourEnabled: @json(isset($settings['onboarding_tour_enabled']) ? (bool)$settings['onboarding_tour_enabled'] : true),
        onboardingTourDisplayMode: @json($settings['onboarding_tour_display_mode'] ?? 'bento'),
        onboardingTourEnforcement: @json($settings['onboarding_tour_enforcement'] ?? 'soft'),
        onboardingTourGracePeriodDays: @json((int)($settings['onboarding_tour_grace_period_days'] ?? 3)),
        onboardingTourCelebrationTitle: @json($settings['onboarding_tour_celebration_title'] ?? __('resellerhub::messages.onboarding.celebration_default_title')),
        onboardingTourCelebrationMessage: @json($settings['onboarding_tour_celebration_message'] ?? __('resellerhub::messages.onboarding.celebration_default_message')),
        onboardingTourSteps: @json(ResellerSetting::getOnboardingTourSteps($reseller->id)),
        defaultTourSteps: @json(ResellerSetting::getDefaultOnboardingSteps()),
        customStepPresets: @json(OnboardingStepPreset::toFrontendArray()),
        internalRouteOptions: @json(OnboardingInternalRoute::toFrontendArray()),
        iconCatalog: @json(OnboardingStepIcon::toFrontendArray()),
        messages: {
            onboarding: @json(__('resellerhub::messages.onboarding')),
            pricing: @json(__('resellerhub::messages.pricing')),
            common: @json(__('resellerhub::messages.common')),
            settings: @json(__('resellerhub::messages.settings')),
        },
    };

    window.resellerSettingsManager = function (config) {
        config = config || window.resellerSettingsConfig || {};
        const validTabs = ['general', 'tour', 'branding', 'finance', 'kyc', 'automation', 'tenancy'];
        let initialTab = config.initialTab || localStorage.getItem('resellerSettingsTab') || 'general';
        if (!validTabs.includes(initialTab)) {
            initialTab = 'general';
        }

        return {
            activeTab: initialTab,
            saving: false,
            confirmSeedModalOpen: false,
            toast: {show: false, message: '', type: 'success'},
            errors: {},
            durationDropdownOpen: false,
            defaultDuration: config.defaultDuration || '12',
            renewalCyclesDropdownOpen: false,
            defaultPackageDropdownOpen: false,
            maxAdvanceRenewalCycles: config.maxAdvanceRenewalCycles !== undefined && config.maxAdvanceRenewalCycles !== null ? String(config.maxAdvanceRenewalCycles) : '1',
            renewalCyclesOptions: [
                {value: '1', label: '۱ دوره آتی (پیش‌فرض)'},
                {value: '2', label: '۲ دوره آتی'},
                {value: '3', label: '۳ دوره آتی'},
                {value: '6', label: '۶ دوره آتی'},
                {value: '12', label: '۱۲ دوره آتی'},
                {value: '0', label: 'نامحدود (بدون محدودیت)'},
            ],
            subdomainPattern: config.subdomainPattern || 'crm-{rand}',
            subdomainPreview: '',
            allowCustomDomains: config.allowCustomDomains !== undefined ? Boolean(config.allowCustomDomains) : true,
            enableDefaultPackageRegistration: config.enableDefaultPackageRegistration !== undefined ? Boolean(config.enableDefaultPackageRegistration) : false,
            defaultPackageId: config.defaultPackageId ? String(config.defaultPackageId) : '',
            customDomainYearlyPrice: config.customDomainYearlyPrice || '',
            customDomainMonthlyPrice: config.customDomainMonthlyPrice || '',
            customDomainSetupFee: config.customDomainSetupFee || '',
            customDomainYearlyDisplay: config.customDomainYearlyPrice ? Number(config.customDomainYearlyPrice).toLocaleString('en-US') : '',
            customDomainMonthlyDisplay: config.customDomainMonthlyPrice ? Number(config.customDomainMonthlyPrice).toLocaleString('en-US') : '',
            customDomainSetupDisplay: config.customDomainSetupFee ? Number(config.customDomainSetupFee).toLocaleString('en-US') : '',
            cnameTarget: config.cnameTarget || window.location.hostname,
            logoPreview: config.logoUrl || null,
            faviconPreview: config.faviconUrl || null,
            sealPreview: config.sealUrl || null,
            isDraggingLogo: false,
            isDraggingFavicon: false,
            isDraggingSeal: false,
            logoFileName: '',
            logoFileSize: '',
            faviconFileName: '',
            faviconFileSize: '',
            sealFileName: '',
            sealFileSize: '',
            removeLogoFlag: false,
            removeFaviconFlag: false,
            removeSealFlag: false,
            companyName: @json($reseller->company_name ?: ($reseller->name ?: '')),
            brandTitle: @json($reseller->brand_title ?: ($reseller->company_name ?: ($reseller->name ?: ''))),
            brandPrimaryColor: config.brandPrimaryColor || '#4f46e5',
            brandFooterText: @json($reseller->brand_footer_text ?: ''),
            supportPhone: @json(reseller_fa_num((string) ($reseller->support_phone ?? ''))),
            whiteLabelYearlyPrice: config.whiteLabelYearlyPrice || '',
            whiteLabelMonthlyPrice: config.whiteLabelMonthlyPrice || '',
            whiteLabelSetupFee: config.whiteLabelSetupFee || '',
            whiteLabelYearlyDisplay: config.whiteLabelYearlyPrice ? Number(config.whiteLabelYearlyPrice).toLocaleString('en-US') : '',
            whiteLabelMonthlyDisplay: config.whiteLabelMonthlyPrice ? Number(config.whiteLabelMonthlyPrice).toLocaleString('en-US') : '',
            whiteLabelSetupDisplay: config.whiteLabelSetupFee ? Number(config.whiteLabelSetupFee).toLocaleString('en-US') : '',
            colorPresets: (Array.isArray(config.colorPresets) && config.colorPresets.length)
                ? config.colorPresets
                : (window.resellerColorPresets || []),
            currency: @json($settings['currency'] ?? 'toman'),
            taxMode: 'invoice',
            defaultTaxRate: @json($settings['default_tax_rate'] ?? 10),

            // Numbering Builders
            invoicePrefix: config.invoicePrefix ?? 'INV-',
            invoiceMiddle: config.invoiceMiddle ?? '',
            invoiceSuffix: config.invoiceSuffix ?? '',
            invoicePadding: parseInt(config.invoicePadding) || 4,
            invoiceAuto: Boolean(config.invoiceAuto),
            invoicePreview: '',

            useGlobalPayment: @json(filter_var($settings['reseller_use_global_payment_settings'] ?? true, FILTER_VALIDATE_BOOLEAN)),
            invoiceTerms: @json($settings['invoice_terms'] ?? ($settings['invoice_footer_note'] ?? 'کلیه خدمات و دسترسی‌ها پس از تسویه فاکتور فعال می‌گردند. از حسن انتخاب و اعتماد شما سپاسگزاریم.')),
            invoiceFooterNote: '',
            durationOptions: config.durationOptions || window.resellerStandardDurations || [],

            ...(window.resellerUiHelpers ? window.resellerUiHelpers() : {}),

            // Onboarding Tour State
            tourEnabled: config.onboardingTourEnabled !== undefined ? Boolean(config.onboardingTourEnabled) : true,
            tourDisplayMode: config.onboardingTourDisplayMode || 'bento',
            tourEnforcement: config.onboardingTourEnforcement || 'soft',
            tourGracePeriodDays: parseInt(config.onboardingTourGracePeriodDays) || 3,
            tourCelebrationTitle: config.onboardingTourCelebrationTitle || '',
            tourCelebrationMessage: config.onboardingTourCelebrationMessage || '',
            tourSteps: (config.onboardingTourSteps && Array.isArray(config.onboardingTourSteps)) ? JSON.parse(JSON.stringify(config.onboardingTourSteps)).map(s => ({
                ...s,
                depends_on: s.depends_on || '',
            })) : [],
            tourSortableInstance: null,

            editStepModalOpen: false,
            editingStep: {},
            editingIndex: -1,
            addCustomStepModalOpen: false,
            deleteStepModalOpen: false,
            stepToDeleteIndex: -1,
            stepToDeleteTitle: '',
            newStepError: '',
            alertModalOpen: false,
            alertModalTitle: '',
            alertModalMessage: '',
            alertModalType: 'warning',
            newStep: {
                title: '',
                description: '',
                badge: '',
                icon: 'sparkles',
                action_type: 'internal',
                internal_route: 'user/clients/create',
                custom_internal_route: '',
                action_url: '/user/clients/create',
                is_required: false,
                depends_on_mode: 'previous',
                specific_depends_on: null,
            },

            customStepPresets: (config.customStepPresets && Array.isArray(config.customStepPresets))
                ? config.customStepPresets
                : [],

            internalRouteOptions: (config.internalRouteOptions && Array.isArray(config.internalRouteOptions))
                ? config.internalRouteOptions
                : [],

            iconCatalog: (config.iconCatalog && Array.isArray(config.iconCatalog))
                ? config.iconCatalog
                : [],

            getStepIconSvg(iconId) {
                const found = (this.iconCatalog || []).find(i => i.id === iconId);
                if (found) return found.svg;
                return '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>';
            },

            applyCustomStepPreset(preset) {
                this.newStep.title = preset.title;
                this.newStep.description = preset.description;
                this.newStep.badge = preset.badge;
                this.newStep.icon = preset.icon;
                this.newStep.action_type = preset.action_type;
                this.newStep.internal_route = preset.internal_route;
                this.newStep.action_url = preset.action_url;
                this.newStep.is_required = Boolean(preset.is_required);
                this.newStep.can_manual_complete = Boolean(preset.can_manual_complete);
                this.newStepError = '';
                this.showToast(this.t('preset_applied', {title: preset.title}), 'info');
            },

            t(key, replacements = {}) {
                let msg = key.split('.').reduce((obj, i) => (obj ? obj[i] : null), config.messages);
                if (!msg && config.messages && config.messages.onboarding) {
                    msg = config.messages.onboarding[key];
                }
                msg = msg || key;
                for (const [k, v] of Object.entries(replacements)) {
                    msg = msg.replace(new RegExp(`:${k}`, 'g'), v);
                }
                return msg;
            },

            getPrecedingSteps(index) {
                if (index <= 0) return [];
                return this.tourSteps.slice(0, index);
            },

            wouldCreateCycle(stepSlug, targetPrereqSlug) {
                if (!targetPrereqSlug || stepSlug === targetPrereqSlug) return true;
                let curr = targetPrereqSlug;
                const visited = new Set();
                while (curr) {
                    if (curr === stepSlug) return true;
                    if (visited.has(curr)) return true;
                    visited.add(curr);
                    const parentStep = (this.tourSteps || []).find(s => s.slug === curr);
                    curr = parentStep ? parentStep.depends_on : null;
                }
                return false;
            },

            getStepAndDescendantSlugs(stepSlug) {
                const result = [stepSlug];
                const queue = [stepSlug];
                while (queue.length > 0) {
                    const parentSlug = queue.shift();
                    (this.tourSteps || []).forEach(s => {
                        if (s.depends_on === parentSlug && !result.includes(s.slug)) {
                            result.push(s.slug);
                            queue.push(s.slug);
                        }
                    });
                }
                return result;
            },

            getPrerequisiteCandidates(stepSlug) {
                return (this.tourSteps || []).filter(s => {
                    if (s.slug === stepSlug) return false;
                    if (this.wouldCreateCycle(stepSlug, s.slug)) return false;
                    return true;
                });
            },

            moveStepAndDescendantsAfter(stepSlug, prereqSlug) {
                if (!prereqSlug || stepSlug === prereqSlug) return;

                const clusterSlugs = this.getStepAndDescendantSlugs(stepSlug);
                const clusterSteps = [];
                const remainingSteps = [];

                (this.tourSteps || []).forEach(s => {
                    if (clusterSlugs.includes(s.slug)) {
                        clusterSteps.push(s);
                    } else {
                        remainingSteps.push(s);
                    }
                });

                const targetIdx = remainingSteps.findIndex(s => s.slug === prereqSlug);
                if (targetIdx === -1) return;

                remainingSteps.splice(targetIdx + 1, 0, ...clusterSteps);
                this.tourSteps = remainingSteps;
            },

            setStepDependency(stepSlug, prereqSlug) {
                const step = (this.tourSteps || []).find(s => s.slug === stepSlug);
                if (!step) return;

                prereqSlug = prereqSlug || '';

                if (!prereqSlug || prereqSlug === 'null' || prereqSlug === '') {
                    step.depends_on = '';
                    this.reindexTourSteps();
                    this.$nextTick(() => this.initTourSortable());
                    this.showToast(this.t('step_no_prereq', {step: step.title}), 'info');
                    return;
                }

                if (stepSlug === prereqSlug) {
                    this.showAlert(this.t('prereq_self_error_title'), this.t('prereq_self_error'), 'warning');
                    return;
                }

                if (this.wouldCreateCycle(stepSlug, prereqSlug)) {
                    const targetTitle = this.getStepTitleBySlug(prereqSlug);
                    this.showAlert(
                        this.t('cycle_detected_title'),
                        this.t('cycle_detected_desc', {target: targetTitle}),
                        'warning'
                    );
                    return;
                }

                step.depends_on = prereqSlug;
                this.moveStepAndDescendantsAfter(stepSlug, prereqSlug);
                this.reindexTourSteps();
                this.$nextTick(() => this.initTourSortable());

                const targetTitle = this.getStepTitleBySlug(prereqSlug);
                this.showToast(this.t('step_moved_after', {step: step.title, target: targetTitle}), 'success');
            },

            changeStepPosition(stepSlug, newIndex) {
                newIndex = parseInt(newIndex, 10);
                const currIdx = (this.tourSteps || []).findIndex(s => s.slug === stepSlug);
                if (currIdx === -1 || isNaN(newIndex) || newIndex < 0 || newIndex >= this.tourSteps.length || currIdx === newIndex) {
                    return;
                }

                // Move step to newIndex
                const step = this.tourSteps.splice(currIdx, 1)[0];
                this.tourSteps.splice(newIndex, 0, step);

                // Synchronize dependency with new position
                if (newIndex === 0) {
                    step.depends_on = '';
                } else {
                    step.depends_on = this.tourSteps[newIndex - 1].slug;
                }

                this.reindexTourSteps();
                this.$nextTick(() => this.initTourSortable());

                const prevTitle = newIndex > 0 ? this.getStepTitleBySlug(step.depends_on) : null;
                const msg = newIndex === 0
                    ? this.t('step_moved_to_start', {step: step.title})
                    : this.t('step_moved_to_pos', {step: step.title, index: this.faNum(newIndex + 1), prev: prevTitle});
                this.showToast(msg, 'success');
            },

            chainTourStepsSequentially() {
                if (!this.tourSteps || this.tourSteps.length === 0) return;
                this.tourSteps[0].depends_on = '';
                for (let i = 1; i < this.tourSteps.length; i++) {
                    this.tourSteps[i].depends_on = this.tourSteps[i - 1].slug;
                }
                this.reindexTourSteps();
                this.$nextTick(() => this.initTourSortable());
                this.showToast(this.t('chain_steps_success'), 'success');
            },

            unlockAllTourSteps() {
                if (!this.tourSteps || this.tourSteps.length === 0) return;
                this.tourSteps.forEach(s => {
                    s.depends_on = '';
                });
                this.reindexTourSteps();
                this.$nextTick(() => this.initTourSortable());
                this.showToast(this.t('unlock_steps_success'), 'success');
            },

            get activeEnabledSteps() {
                return (this.tourSteps || []).filter(s => Boolean(s.enabled));
            },

            moveStepUp(index) {
                if (index <= 0) return;
                this.changeStepPosition(this.tourSteps[index].slug, index - 1);
            },

            moveStepDown(index) {
                if (index >= this.tourSteps.length - 1) return;
                this.changeStepPosition(this.tourSteps[index].slug, index + 1);
            },

            reindexTourSteps() {
                const slugIndexMap = new Map();
                (this.tourSteps || []).forEach((s, idx) => {
                    s.sort_order = idx + 1;
                    s.depends_on = s.depends_on || '';
                    slugIndexMap.set(s.slug, idx);
                });

                if (this.tourSteps.length > 0 && this.tourSteps[0].depends_on) {
                    this.tourSteps[0].depends_on = '';
                }

                // Check dependencies: if dependency points to a non-existent step or a step at/after current index
                (this.tourSteps || []).forEach((step, idx) => {
                    if (step.depends_on) {
                        const targetIdx = slugIndexMap.get(step.depends_on);
                        if (targetIdx === undefined || targetIdx >= idx) {
                            step.depends_on = idx > 0 ? this.tourSteps[idx - 1].slug : '';
                        }
                    }
                });

                this.tourSteps = [...this.tourSteps];
                this.broadcastTourUpdate();
            },

            toggleStepRequired(step) {
                if (step.is_core) {
                    return;
                }
                step.is_required = !step.is_required;
                this.reindexTourSteps();
            },

            toggleStepEnabled(step) {
                step.enabled = !step.enabled;
                this.reindexTourSteps();
            },

            broadcastTourUpdate() {
                try {
                    window.dispatchEvent(new CustomEvent('onboarding-tour-updated', {
                        detail: {
                            steps: this.tourSteps,
                            enabled: this.tourEnabled,
                            allowCustomDomains: this.allowCustomDomains
                        }
                    }));
                    if (window.BroadcastChannel) {
                        const ch = new BroadcastChannel('crm_onboarding_sync');
                        ch.postMessage({
                            type: 'tour-updated',
                            steps: this.tourSteps,
                            enabled: this.tourEnabled,
                            allowCustomDomains: this.allowCustomDomains,
                            timestamp: Date.now()
                        });
                    }
                } catch (e) {
                }
            },

            ensureSortableLoaded() {
                return window.Sortable || null;
            },

            initTourSortable() {
                const SortableLib = this.ensureSortableLoaded();
                const el = this.$refs.tourStepsContainer;
                if (!el || !SortableLib) return;

                if (this.tourSortableInstance) {
                    try {
                        this.tourSortableInstance.destroy();
                    } catch (e) {
                    }
                }

                this.tourSortableInstance = new SortableLib(el, {
                    animation: 250,
                    handle: '.drag-handle',
                    draggable: '.tour-step-card',
                    ghostClass: 'tour-sortable-ghost',
                    chosenClass: 'tour-sortable-chosen',
                    dragClass: 'tour-sortable-drag',
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    onEnd: (evt) => {
                        const cards = Array.from(el.querySelectorAll('.tour-step-card'));
                        const newSlugs = cards.map(c => c.getAttribute('data-slug')).filter(Boolean);
                        if (!newSlugs.length) return;

                        const stepMap = new Map(this.tourSteps.map(s => [s.slug, s]));
                        const reordered = [];
                        newSlugs.forEach(slug => {
                            if (stepMap.has(slug)) {
                                reordered.push(stepMap.get(slug));
                                stepMap.delete(slug);
                            }
                        });
                        stepMap.forEach(s => reordered.push(s));

                        this.tourSteps = reordered;

                        // Synchronize dependency with the dropped position
                        if (evt && evt.newIndex !== undefined && evt.newIndex >= 0 && evt.newIndex < this.tourSteps.length) {
                            const movedStep = this.tourSteps[evt.newIndex];
                            if (evt.newIndex === 0) {
                                movedStep.depends_on = '';
                            } else {
                                movedStep.depends_on = this.tourSteps[evt.newIndex - 1].slug;
                            }
                        }

                        this.reindexTourSteps();
                        this.$nextTick(() => this.initTourSortable());
                        this.showToast(this.t('steps_reordered'), 'success');
                    }
                });
            },

            confirmDeleteStep(index) {
                this.stepToDeleteIndex = index;
                this.stepToDeleteTitle = this.tourSteps[index]?.title || 'این گام';
                this.deleteStepModalOpen = true;
            },

            executeDeleteStep() {
                if (this.stepToDeleteIndex < 0 || this.stepToDeleteIndex >= this.tourSteps.length) {
                    this.deleteStepModalOpen = false;
                    return;
                }
                const deleted = this.tourSteps[this.stepToDeleteIndex];
                this.tourSteps.splice(this.stepToDeleteIndex, 1);
                this.tourSteps.forEach(s => {
                    if (s.depends_on === deleted.slug) {
                        s.depends_on = null;
                    }
                });
                this.reindexTourSteps();
                this.$nextTick(() => this.initTourSortable());
                this.deleteStepModalOpen = false;
                this.stepToDeleteIndex = -1;
                this.stepToDeleteTitle = '';
                this.showToast(this.t('step_deleted'), 'success');
            },

            showAlert(title, message, type = 'warning') {
                this.alertModalTitle = title;
                this.alertModalMessage = message;
                this.alertModalType = type;
                this.alertModalOpen = true;
            },

            openEditStepModal(index) {
                this.editingIndex = index;
                const step = JSON.parse(JSON.stringify(this.tourSteps[index]));
                step.depends_on = step.depends_on || '';
                if (!step.action_type) step.action_type = 'internal';
                if (!step.icon) step.icon = 'sparkles';
                if (!step.internal_route) {
                    step.internal_route = (step.action_url && step.action_url.startsWith('/'))
                        ? step.action_url.replace(/^\//, '')
                        : 'user/clients/create';
                }
                step.custom_internal_route = step.action_url || '';
                this.editingStep = step;
                this.editStepModalOpen = true;
            },

            saveEditingStep() {
                if (this.editingIndex >= 0 && this.editingStep) {
                    if (!this.editingStep.title || !this.editingStep.title.trim()) {
                        this.showAlert(this.t('duplicate_step_title'), this.t('validation_title_required'), 'warning');
                        return;
                    }
                    if (!this.editingStep.is_core) {
                        if (this.editingStep.action_type === 'internal') {
                            const r = (this.editingStep.internal_route === 'custom')
                                ? (this.editingStep.custom_internal_route || '')
                                : this.editingStep.internal_route;
                            this.editingStep.action_url = r ? (r.startsWith('/') ? r : '/' + r) : null;
                            this.editingStep.can_manual_complete = Boolean(this.editingStep.can_manual_complete);
                        } else if (this.editingStep.action_type === 'external') {
                            this.editingStep.can_manual_complete = true;
                        } else if (this.editingStep.action_type === 'manual') {
                            this.editingStep.action_url = null;
                            this.editingStep.can_manual_complete = true;
                        }
                    }
                    const originalStep = this.tourSteps[this.editingIndex];
                    const prereqChanged = originalStep && originalStep.depends_on !== this.editingStep.depends_on;
                    this.tourSteps[this.editingIndex] = JSON.parse(JSON.stringify(this.editingStep));
                    if (prereqChanged && this.editingStep.depends_on) {
                        this.moveStepAndDescendantsAfter(this.editingStep.slug, this.editingStep.depends_on);
                    }
                    this.reindexTourSteps();
                    this.$nextTick(() => this.initTourSortable());
                    this.editStepModalOpen = false;
                    this.editingStep = null;
                    this.editingIndex = -1;
                    this.showToast(this.t('step_updated', {title: this.editingStep?.title || ''}), 'success');
                }
            },

            openAddCustomStepModal() {
                this.newStepError = '';
                const lastStep = this.tourSteps.length > 0 ? this.tourSteps[this.tourSteps.length - 1] : null;
                this.newStep = {
                    title: '',
                    description: '',
                    badge: 'گام ' + (this.tourSteps.length + 1),
                    icon: 'sparkles',
                    action_type: 'internal',
                    internal_route: 'user/clients/create',
                    custom_internal_route: '',
                    action_url: '/user/clients/create',
                    is_required: false,
                    depends_on_mode: lastStep ? 'previous' : 'none',
                    specific_depends_on: lastStep ? lastStep.slug : null,
                };
                this.addCustomStepModalOpen = true;
            },

            createCustomStep() {
                this.newStepError = '';
                if (!this.newStep.title || !this.newStep.title.trim()) {
                    this.newStepError = this.t('validation_title_required');
                    this.showAlert(this.t('duplicate_step_title'), this.t('validation_title_required'), 'warning');
                    return;
                }

                let finalActionUrl = null;
                let canManualComplete = false;

                if (this.newStep.action_type === 'internal') {
                    const r = (this.newStep.internal_route === 'custom')
                        ? (this.newStep.custom_internal_route || '')
                        : this.newStep.internal_route;
                    finalActionUrl = r ? (r.startsWith('/') ? r : '/' + r) : null;
                    canManualComplete = Boolean(this.newStep.can_manual_complete);
                } else if (this.newStep.action_type === 'external') {
                    finalActionUrl = (this.newStep.action_url || '').trim();
                    canManualComplete = true;
                } else if (this.newStep.action_type === 'manual') {
                    finalActionUrl = null;
                    canManualComplete = true;
                }

                let dependsOn = null;
                const lastStep = this.tourSteps.length > 0 ? this.tourSteps[this.tourSteps.length - 1] : null;
                if (this.newStep.depends_on_mode === 'previous' && lastStep) {
                    dependsOn = lastStep.slug;
                } else if (this.newStep.depends_on_mode === 'specific') {
                    dependsOn = this.newStep.specific_depends_on || null;
                }

                const slug = 'custom_' + Date.now();
                const newStepObj = {
                    slug: slug,
                    is_core: false,
                    enabled: true,
                    is_required: Boolean(this.newStep.is_required),
                    sort_order: 0,
                    title: this.newStep.title.trim(),
                    description: (this.newStep.description || '').trim(),
                    badge: (this.newStep.badge || 'ویژه').trim(),
                    icon: (this.newStep.icon || 'sparkles').trim(),
                    depends_on: dependsOn,
                    action_type: this.newStep.action_type || 'internal',
                    action_target: null,
                    action_url: finalActionUrl,
                    can_manual_complete: canManualComplete || (this.newStep.action_type === 'manual'),
                };

                if (dependsOn) {
                    const pIdx = this.tourSteps.findIndex(s => s.slug === dependsOn);
                    if (pIdx !== -1) {
                        this.tourSteps.splice(pIdx + 1, 0, newStepObj);
                    } else {
                        this.tourSteps.push(newStepObj);
                    }
                } else {
                    this.tourSteps.push(newStepObj);
                }

                this.reindexTourSteps();
                this.addCustomStepModalOpen = false;
                this.newStepError = '';
                this.$nextTick(() => this.initTourSortable());
                this.showToast(this.t('step_added', {title: this.newStep.title}), 'success');
            },

            resetTourStepsToDefault() {
                this.tourSteps = JSON.parse(JSON.stringify(config.defaultTourSteps || []));
                this.reindexTourSteps();
                this.$nextTick(() => this.initTourSortable());
                this.showToast(this.t('reset_to_default_success'), 'success');
            },

            getStepTitleBySlug(slug) {
                if (!slug) return '';
                const found = this.tourSteps.find(s => s.slug === slug);
                return found ? found.title : slug;
            },


            // Pricing Matrix State (Master-Detail Workbench)
            pricingModules: config.catalog || [],
            pricingSearch: '',
            pricingFilter: 'all', // 'all', 'active', 'allowed'
            selectedPricingSlug: (config.catalog && config.catalog.length > 0) ? config.catalog[0].slug : null,
            savingPricings: false,
            pricingExpandedModules: {},

            selectedPricingModule() {
                if (!this.pricingModules || this.pricingModules.length === 0) return null;
                return this.pricingModules.find(m => m.slug === this.selectedPricingSlug) || this.pricingModules[0] || null;
            },

            selectPricingModule(slug) {
                this.selectedPricingSlug = slug;
            },

            formatPriceInput(v) {
                if (v === '' || v === null || v === undefined) return '';
                const clean = window.parsePriceInput ? window.parsePriceInput(v) : (parseInt(String(v).replace(/[^0-9]/g, '')) || 0);
                if (!clean) return '';
                return this.faNum(clean.toLocaleString('en-US'));
            },

            parsePriceInput(v) {
                return window.parsePriceInput ? window.parsePriceInput(v) : (parseInt(String(v).replace(/[^0-9]/g, '')) || 0);
            },

            calcPeriodPrices(monthly) {
                const m = parseInt(monthly || 0);
                if (!m) return {quarterly: 0, semiAnnual: 0, yearly: 0};
                return {
                    quarterly: m * 3,
                    semiAnnual: m * 6,
                    yearly: m * 12,
                };
            },

            togglePricingExpand(slug) {
                this.pricingExpandedModules[slug] = !this.pricingExpandedModules[slug];
            },

            isPricingExpanded(slug) {
                return !!this.pricingExpandedModules[slug];
            },

            expandAllPricings() {
                this.pricingModules.forEach(m => {
                    this.pricingExpandedModules[m.slug] = true;
                });
            },

            collapseAllPricings() {
                this.pricingExpandedModules = {};
            },

            filteredPricingModules() {
                let list = this.pricingModules || [];
                if (this.pricingFilter === 'active') {
                    list = list.filter(m => m.is_active);
                } else if (this.pricingFilter === 'allowed') {
                    list = list.filter(m => m.is_allowed);
                }
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
                    this.showToast(this.t('pricing.monthly_required'), 'error');
                    return;
                }
                const p = this.calcPeriodPrices(monthly);
                m.base_quarterly_price = p.quarterly;
                m.base_semi_annual_price = p.semiAnnual;
                m.base_yearly_price = p.yearly;
                this.showToast(this.t('pricing.module_calculated', {title: m.title}), 'success');
            },

            autoCalculateFeaturePeriods(f) {
                const monthly = parseInt(f.monthly_price || 0);
                if (!monthly) {
                    this.showToast(this.t('pricing.feature_monthly_required'), 'error');
                    return;
                }
                const p = this.calcPeriodPrices(monthly);
                f.quarterly_price = p.quarterly;
                f.semi_annual_price = p.semiAnnual;
                f.yearly_price = p.yearly;
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
                    this.showToast(this.t('pricing.features_calculated', {count: this.faNum(count)}), 'success');
                } else {
                    this.showToast(this.t('pricing.features_monthly_required'), 'error');
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
                    this.showToast(this.t('pricing.modules_calculated', {count: this.faNum(count)}), 'success');
                } else {
                    this.showToast(this.t('pricing.modules_monthly_required'), 'error');
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
                    const res = await fetch(config.savePricingsUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({pricings: payload})
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.showToast(data.message || this.t('pricing.saved_success', {count: ''}), 'success');
                    } else {
                        this.showToast(data.message || this.t('pricing.save_error'), 'error');
                    }
                } catch (e) {
                    this.showToast(this.t('common.server_connection_error'), 'error');
                } finally {
                    this.savingPricings = false;
                }
            },

            init() {
                if (!validTabs.includes(this.activeTab)) {
                    this.activeTab = 'general';
                }
                this.updateInvoicePreview();
                this.updateSubdomainPreview();
                if (this.activeTab === 'tour') {
                    this.$nextTick(() => this.initTourSortable());
                }
                this.$watch('activeTab', val => {
                    localStorage.setItem('resellerSettingsTab', val);
                    const url = new URL(window.location);
                    url.searchParams.set('tab', val);
                    window.history.replaceState({}, '', url);
                    if (val === 'tour') {
                        this.$nextTick(() => this.initTourSortable());
                    }
                });

                this.$watch('tourEnabled', () => {
                    this.broadcastTourUpdate();
                });

                if (window.BroadcastChannel) {
                    try {
                        const ch = new BroadcastChannel('crm_onboarding_sync');
                        ch.onmessage = (e) => {
                            if (e.data && e.data.type === 'tour-updated' && e.data.steps) {
                                this.tourSteps = JSON.parse(JSON.stringify(e.data.steps)).map(s => ({
                                    ...s,
                                    depends_on: s.depends_on || ''
                                }));
                                if (e.data.enabled !== undefined) {
                                    this.tourEnabled = Boolean(e.data.enabled);
                                }
                                this.tourSteps = [...this.tourSteps];
                                this.$nextTick(() => this.initTourSortable());
                            }
                        };
                    } catch (e) {
                    }
                }
            },

            updateInvoicePreview() {
                const pad = parseInt(this.invoicePadding) || 4;
                const num = String(1).padStart(pad, '0');
                const mid = this.invoiceMiddle ? (this.invoiceMiddle + '-') : '';
                this.invoicePreview = (this.invoicePrefix || '') + mid + num + (this.invoiceSuffix || '');
            },

            updateSubdomainPreview() {
                let pat = (this.subdomainPattern || 'crm-{rand}').trim();
                // Replace {name}
                pat = pat.replace(/\{name\}/gi, 'ali');
                // Replace {rand} or {rand:N}
                pat = pat.replace(/\{rand(?::(\d+))?\}/gi, (match, p1) => {
                    const len = parseInt(p1) > 0 ? parseInt(p1) : 6;
                    return '583921'.slice(0, len).padEnd(len, '7');
                });
                // Replace {alpha} or {alpha:N}
                pat = pat.replace(/\{alpha(?::(\d+))?\}/gi, (match, p1) => {
                    const len = parseInt(p1) > 0 ? parseInt(p1) : 5;
                    return 'appxy'.slice(0, len).padEnd(len, 'z');
                });
                // Replace {mobile_tail}
                pat = pat.replace(/\{mobile_tail\}/gi, '45678');
                // Replace {date}
                pat = pat.replace(/\{date\}/gi, '1405');
                // Clean characters: lowercase, alphanumeric and hyphens
                let cleaned = pat.toLowerCase().replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '');
                if (!cleaned) {
                    cleaned = 'ali';
                }
                const host = window.location.host;
                // Path-based tenant URL format: domain.test/subdomain
                this.subdomainPreview = host + '/' + cleaned;
            },

            insertSubdomainTag(tag) {
                if (!this.subdomainPattern) {
                    this.subdomainPattern = tag;
                } else if (!this.subdomainPattern.includes(tag)) {
                    this.subdomainPattern = (this.subdomainPattern.trim() + '-' + tag).replace(/--+/g, '-');
                }
                this.updateSubdomainPreview();
            },

            setSubdomainPatternPreset(preset) {
                this.subdomainPattern = preset;
                this.updateSubdomainPreview();
            },

            faNum: (str) => window.faNum(str),
            formatNumber: (num) => window.formatNumber(num),

            setTaxRate(rate) {
                this.defaultTaxRate = rate;
            },

            setPrefixPreset(p) {
                this.invoicePrefix = p;
                this.updateInvoicePreview();
            },

            isRial() {
                const c = (this.currency || '').toLowerCase();
                return c === 'rial' || c === 'irr' || c === 'ریال' || (this.currency === config.currency && Boolean(config.isRial));
            },

            currencyLabel() {
                return this.isRial() ? 'ریال' : 'تومان';
            },

            getSampleSubtotal() {
                return this.isRial() ? 100000000 : 10000000;
            },

            getSampleDiscount() {
                return Math.round(this.getSampleSubtotal() * 0.10);
            },

            getSampleTaxable() {
                return this.getSampleSubtotal() - this.getSampleDiscount();
            },

            getSampleTax() {
                const rate = parseFloat(this.defaultTaxRate) || 0;
                return Math.round(this.getSampleTaxable() * (rate / 100));
            },

            getSampleTotal() {
                return this.getSampleTaxable() + this.getSampleTax();
            },

            insertTermsPreset(text) {
                if (this.invoiceTerms && !this.invoiceTerms.includes(text)) {
                    this.invoiceTerms = (this.invoiceTerms.trim() + '\n• ' + text).trim();
                } else if (!this.invoiceTerms) {
                    this.invoiceTerms = '• ' + text;
                }
            },

            insertFooterPreset(text) {
                if (this.invoiceFooterNote && !this.invoiceFooterNote.includes(text)) {
                    this.invoiceFooterNote = (this.invoiceFooterNote.trim() + ' ' + text).trim();
                } else if (!this.invoiceFooterNote) {
                    this.invoiceFooterNote = text;
                }
            },

            get durationLabel() {
                const opt = (this.durationOptions || []).find(o => String(o.value) === String(this.defaultDuration));
                return opt ? opt.label : (this.faNum(this.defaultDuration) + ' ماهه');
            },

            get renewalCyclesLabel() {
                const opt = (this.renewalCyclesOptions || []).find(o => String(o.value) === String(this.maxAdvanceRenewalCycles));
                if (opt) return opt.label;
                if (String(this.maxAdvanceRenewalCycles) === '0') return 'نامحدود (بدون محدودیت)';
                return (this.faNum ? this.faNum(this.maxAdvanceRenewalCycles) : this.maxAdvanceRenewalCycles) + ' دوره آتی';
            },

            showToast(msg, type = 'success') {
                this.toast.message = msg;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            copyToClipboard(text, msg = null) {
                if (!text) return;
                msg = msg || this.t('common.copied');
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
                        this.showToast(this.t('common.copy_error'), 'error');
                    }
                    textArea.remove();
                }
            },

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
                const cap = target.charAt(0).toUpperCase() + target.slice(1);
                this['isDragging' + cap] = false;

                const files = event.dataTransfer ? event.dataTransfer.files : null;
                if (!files || files.length === 0) return;

                const file = files[0];
                const inputRef = this.$refs[target + 'Input'];
                if (inputRef) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    inputRef.files = dt.files;
                }
                this.processFile(file, target);
            },

            processFile(file, target) {
                const cap = target.charAt(0).toUpperCase() + target.slice(1);
                this[target + 'FileName'] = file.name;
                this[target + 'FileSize'] = this.formatFileSize(file.size);
                this['remove' + cap + 'Flag'] = false;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this[target + 'Preview'] = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            removeFile(target) {
                const cap = target.charAt(0).toUpperCase() + target.slice(1);
                this[target + 'Preview'] = null;
                this[target + 'FileName'] = '';
                this[target + 'FileSize'] = '';
                this['remove' + cap + 'Flag'] = true;
                const inputRef = this.$refs[target + 'Input'];
                if (inputRef) inputRef.value = '';
            },

            previewImage(event, target) {
                this.handleFileSelect(event, target);
            },

            async saveSettings(formEl) {
                if (this.saving) return;
                this.saving = true;
                this.errors = {};

                const formData = new FormData(formEl);
                formData.set('active_tab', this.activeTab);

                try {
                    const res = await fetch(`{{ route('reseller.settings.update') }}`, {
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
                        this.logoPreview = data.brand_logo_url || null;
                        this.faviconPreview = data.brand_favicon_url || null;
                        if (data.brand_seal_signature_url !== undefined) {
                            this.sealPreview = data.brand_seal_signature_url || null;
                        }
                        this.removeLogoFlag = false;
                        this.removeFaviconFlag = false;
                        this.removeSealFlag = false;
                        this.logoFileName = '';
                        this.faviconFileName = '';
                        this.sealFileName = '';

                        // Real-time synchronization for Onboarding Tour state without page reload
                        if (data.onboarding_tour_steps) {
                            this.tourSteps = JSON.parse(JSON.stringify(data.onboarding_tour_steps)).map(s => ({
                                ...s,
                                depends_on: s.depends_on || ''
                            }));
                            this.reindexTourSteps();
                            this.$nextTick(() => this.initTourSortable());
                        }
                        if (data.onboarding_tour_enabled !== undefined) {
                            this.tourEnabled = Boolean(data.onboarding_tour_enabled);
                        }
                        if (data.onboarding_tour_display_mode) {
                            this.tourDisplayMode = data.onboarding_tour_display_mode;
                        }
                        if (data.onboarding_tour_enforcement) {
                            this.tourEnforcement = data.onboarding_tour_enforcement;
                        }
                        if (data.onboarding_tour_grace_period_days !== undefined) {
                            this.tourGracePeriodDays = data.onboarding_tour_grace_period_days;
                        }
                        if (data.onboarding_tour_celebration_title !== undefined) {
                            this.tourCelebrationTitle = data.onboarding_tour_celebration_title;
                        }
                        if (data.onboarding_tour_celebration_message !== undefined) {
                            this.tourCelebrationMessage = data.onboarding_tour_celebration_message;
                        }
                        if (data.allow_custom_domains !== undefined) {
                            this.allowCustomDomains = Boolean(data.allow_custom_domains);
                        }

                        this.broadcastTourUpdate();
                        this.showToast(data.message, 'success');
                    } else if (res.status === 422) {
                        this.errors = data.errors || {};
                        const firstError = Object.values(this.errors)[0];
                        this.showToast(firstError ? firstError[0] : this.t('common.validation_error'), 'error');
                    } else {
                        this.showToast(data.message || this.t('common.save_error'), 'error');
                    }
                } catch (e) {
                    this.showToast(this.t('common.server_connection_error'), 'error');
                } finally {
                    this.saving = false;
                    if (formEl) {
                        formEl.dataset.isSubmitting = 'false';
                    }
                }
            }
        };
    };

    window.stampSizePreview = function (stdW, stdH, offW, offH) {
        return {
            stdWidth: stdW || '',
            stdHeight: stdH || '',
            offWidth: offW || '',
            offHeight: offH || '',

            setStandard(w, h) {
                this.stdWidth = w;
                this.stdHeight = h;
            },
            setOfficial(w, h) {
                this.offWidth = w;
                this.offHeight = h;
            },
            resetStandard() {
                this.stdWidth = '';
                this.stdHeight = '';
            },
            resetOfficial() {
                this.offWidth = '';
                this.offHeight = '';
            },

            buildImgStyle(width, height, defaultHeight) {
                const w = width ? `width: ${width}px;` : 'width: auto;';
                const h = height ? `height: ${height}px; max-height: ${height}px;` : `height: ${defaultHeight}px; max-height: ${defaultHeight}px;`;
                return `object-fit: contain; mix-blend-mode: multiply; max-width: 100%; transition: all 0.2s ease-in-out; ${w} ${h}`;
            },

            get stdImgStyle() {
                return this.buildImgStyle(this.stdWidth, this.stdHeight, 45);
            },

            get offImgStyle() {
                return this.buildImgStyle(this.offWidth, this.offHeight, 55);
            }
        };
    };

    window.resellerRoundingPreview = function (initialMode, initialFactor, initialCurrency) {
        const toEnDigits = function (str) {
            if (!str) return '';
            return String(str)
                .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
        };
        const addSep = function (n) {
            if (n === null || n === undefined || isNaN(n)) return '';
            return Number(n).toLocaleString('en-US');
        };

        return {
            mode: initialMode || 'none',
            factor: parseInt(initialFactor) || (initialCurrency === 'rial' ? 10000 : 1000),
            factorFormatted: '',
            currency: initialCurrency || 'toman',
            sampleAmount: (initialCurrency === 'rial') ? 3452000 : 345200,
            result: 0,

            init() {
                this.factorFormatted = this.factor ? addSep(this.factor) : '';
                this.updatePreview();
            },

            onFactorInput(e) {
                const raw = toEnDigits(e.target.value || '').replace(/[^\d]/g, '');
                const num = raw ? parseInt(raw, 10) : 0;
                this.factor = num;
                this.factorFormatted = num > 0 ? addSep(num) : '';
                this.updatePreview();
            },

            onCurrencyChanged(newCurrency) {
                if (!newCurrency || this.currency === newCurrency) return;
                const oldCurrency = this.currency;
                this.currency = newCurrency;

                if (oldCurrency === 'toman' && newCurrency === 'rial') {
                    this.factor = Math.max(1, (this.factor || 1000) * 10);
                    this.sampleAmount = this.sampleAmount * 10;
                } else if (oldCurrency === 'rial' && newCurrency === 'toman') {
                    this.factor = Math.max(1, Math.round((this.factor || 10000) / 10));
                    this.sampleAmount = Math.max(1, Math.round(this.sampleAmount / 10));
                }
                this.factorFormatted = this.factor > 0 ? addSep(this.factor) : '';
                this.updatePreview();
            },

            get currencyLabel() {
                return this.currency === 'rial' ? 'ریال' : 'تومان';
            },

            get placeholderFactor() {
                return this.currency === 'rial' ? '10,000' : '1,000';
            },

            get currentPresets() {
                if (this.currency === 'rial') {
                    return [5000, 10000, 50000, 100000, 500000];
                }
                return [500, 1000, 5000, 10000, 50000];
            },

            get modeDescription() {
                if (this.mode === 'none') return 'بدون اعمال ضریب';
                if (this.mode === 'nearest') return 'گرد شده به نزدیک‌ترین ضریب';
                if (this.mode === 'up') return 'گرد شده به سقف بالاتر';
                if (this.mode === 'down') return 'گرد شده به کف پایین‌تر';
                return '';
            },

            get colorClass() {
                if (this.mode === 'none') return 'text-gray-900 dark:text-white';
                if (this.diff > 0) return 'text-emerald-600 dark:text-emerald-400';
                if (this.diff < 0) return 'text-rose-600 dark:text-rose-400';
                return 'text-gray-900 dark:text-white';
            },

            setFactor(val) {
                this.factor = parseInt(val) || 0;
                this.factorFormatted = this.factor > 0 ? addSep(this.factor) : '';
                this.updatePreview();
            },

            updatePreview() {
                const factor = parseInt(this.factor) || 0;
                const sample = Number(this.sampleAmount) || 0;
                if (!this.mode || this.mode === 'none' || factor <= 0) {
                    this.result = Math.round(sample);
                } else if (this.mode === 'up') {
                    this.result = Math.ceil(sample / factor) * factor;
                } else if (this.mode === 'down') {
                    this.result = Math.floor(sample / factor) * factor;
                } else if (this.mode === 'nearest') {
                    this.result = Math.round(sample / factor) * factor;
                } else {
                    this.result = Math.round(sample);
                }
            },

            get diff() {
                if (this.mode === 'none' || !this.factor) return 0;
                return this.result - (Number(this.sampleAmount) || 0);
            },

            formatNumber(n) {
                if (n === null || n === undefined || isNaN(n)) return '۰';
                return window.formatNumber ? window.formatNumber(Math.round(Number(n))) : addSep(Math.round(Number(n)));
            },

            formatMoney(n) {
                return this.formatNumber(n) + ' ' + this.currencyLabel;
            },

            get formattedDiff() {
                const d = this.diff;
                if (d > 0) return '+' + this.formatNumber(d) + ' ' + this.currencyLabel;
                if (d < 0) return '-' + this.formatNumber(Math.abs(d)) + ' ' + this.currencyLabel;
                return '۰ ' + this.currencyLabel;
            }
        };
    };

    if (window.Alpine) {
        Alpine.data('resellerSettingsManager', window.resellerSettingsManager);
        Alpine.data('stampSizePreview', window.stampSizePreview);
        Alpine.data('resellerRoundingPreview', window.resellerRoundingPreview);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('resellerSettingsManager', window.resellerSettingsManager);
            Alpine.data('stampSizePreview', window.stampSizePreview);
            Alpine.data('resellerRoundingPreview', window.resellerRoundingPreview);
        });
    }
</script>
