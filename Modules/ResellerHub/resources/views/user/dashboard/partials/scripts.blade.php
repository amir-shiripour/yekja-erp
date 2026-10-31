<script>
    // Universal Persian digits formatter
    window.toFaDigits = function(n) {
        if (n === null || n === undefined) return '';
        const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return String(n).replace(/[0-9]/g, d => fa[d]);
    };
    window.faNum = window.toFaDigits;

    // Alpine Dashboard & Modals Controller
    function resellerDashboard(config) {
        config = config || {};
        return {
            activeTenants: Number(config.activeTenants || 0),
            suspendedTenants: Number(config.suspendedTenants || 0),
            totalTenants: Number(config.totalTenants || 0),
            expiredTenants: Number(config.expiredTenants || 0),
            selectedPeriod: config.selectedPeriod || '6m',
            selectedPeriodLabel: config.selectedPeriodLabel || '۶ ماه اخیر',
            periodRevenue: Number(config.periodRevenue || 0),
            periodRevenueFormatted: config.periodRevenueFormatted || '',
            periodTenantsCreated: Number(config.periodTenantsCreated || 0),
            revenueGrowthPercent: config.revenueGrowthPercent,
            tenantsGrowthPercent: config.tenantsGrowthPercent,
            chartLoading: false,
            currencyLabel: config.currencyLabel || '{{ $currencyLabel }}',
            todayJalali: config.todayJalali || '{{ reseller_format_jalali(now(), 'Y/m/d', false) }}',
            dashboardUrl: config.dashboardUrl || '{{ route('reseller.dashboard') }}',
            activeTab: (window.location.hash && ['fleet', 'analytics', 'intelligence'].includes(window.location.hash.substring(1))) ? window.location.hash.substring(1) : 'fleet',

            get activeRatio() {
                return this.totalTenants > 0 ? Math.round((this.activeTenants / this.totalTenants) * 100) : 0;
            },
            get suspendedRatio() {
                return this.totalTenants > 0 ? Math.round((this.suspendedTenants / this.totalTenants) * 100) : 0;
            },
            get expiredRatio() {
                return this.totalTenants > 0 ? Math.round((this.expiredTenants / this.totalTenants) * 100) : 0;
            },

            // Suspend / Unsuspend Modal State
            suspendModalOpen: false,
            selectedTenant: {
                id: null,
                name: '',
                subdomain: '',
                owner_name: '',
                suspended: false,
                toggle_url: ''
            },
            loading: false,

            // License Renewal Modal State
            renewModalOpen: false,
            selectedMonths: 12,
            isCustomMonths: false,
            customMonthsInput: '',
            issueInvoice: false,
            renewNotes: '',
            renewLoading: false,
            selectedRenewTenant: {
                id: null,
                name: '',
                subdomain: '',
                package_name: '',
                current_expires_at: '',
                periods: [],
                currency: config.currencyLabel || '{{ $currencyLabel }}',
                renew_url: ''
            },

            toast: {show: false, message: '', type: 'success'},

            init() {
                window.resellerDashboardInstance = this;
                window.addEventListener('hashchange', () => {
                    const hash = window.location.hash.substring(1);
                    if (['fleet', 'operations', 'analytics', 'intelligence'].includes(hash)) {
                        this.setTab(hash, false);
                    }
                });

                // Micro count-up animation for initial load
                if (this.periodRevenue > 0) {
                    const finalRev = this.periodRevenue;
                    this.periodRevenue = 0;
                    this.periodRevenueFormatted = this.formatPrice(0);
                    setTimeout(() => {
                        this.animateNumber(finalRev, 'periodRevenue', true, 700);
                    }, 120);
                }
                if (this.periodTenantsCreated > 0) {
                    const finalTenants = this.periodTenantsCreated;
                    this.periodTenantsCreated = 0;
                    setTimeout(() => {
                        this.animateNumber(finalTenants, 'periodTenantsCreated', false, 650);
                    }, 120);
                }
            },

            animateNumber(targetVal, key, isPrice = false, duration = 600) {
                const start = Number(this[key]) || 0;
                const end = Number(targetVal) || 0;
                if (start === end) {
                    if (isPrice && key === 'periodRevenue') {
                        this.periodRevenueFormatted = this.formatPrice(end);
                    }
                    return;
                }
                const startTime = performance.now();
                const step = (now) => {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease-out cubic: 1 - (1 - t)^3
                    const ease = 1 - Math.pow(1 - progress, 3);
                    const current = Math.round(start + (end - start) * ease);
                    this[key] = current;
                    if (isPrice && key === 'periodRevenue') {
                        this.periodRevenueFormatted = this.formatPrice(current);
                    }
                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        this[key] = end;
                        if (isPrice && key === 'periodRevenue') {
                            this.periodRevenueFormatted = this.formatPrice(end);
                        }
                    }
                };
                requestAnimationFrame(step);
            },

            setTab(tab, updateHash = true) {
                if (!['fleet', 'operations', 'analytics', 'intelligence'].includes(tab)) return;
                this.activeTab = tab;
                if (updateHash && history.replaceState) {
                    history.replaceState(null, null, '#' + tab);
                }
                if (tab === 'analytics') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (window.resellerGrowthChartInstance) {
                                window.resellerGrowthChartInstance.resize();
                                window.resellerGrowthChartInstance.update();
                            }
                            if (window.resellerStatusChartInstance) {
                                window.resellerStatusChartInstance.resize();
                                window.resellerStatusChartInstance.update();
                            }
                            if (window.resellerPipelineChartInstance) {
                                window.resellerPipelineChartInstance.resize();
                                window.resellerPipelineChartInstance.update();
                            }
                            if (window.resellerRevenueCompChartInstance) {
                                window.resellerRevenueCompChartInstance.resize();
                                window.resellerRevenueCompChartInstance.update();
                            } else if (typeof initResellerCharts === 'function') {
                                initResellerCharts();
                            }
                        }, 50);
                    });
                } else if (tab === 'intelligence') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (typeof initResellerIntelligenceCharts === 'function') {
                                initResellerIntelligenceCharts();
                            }
                            if (window.resellerCapabilitiesChartInstance) {
                                window.resellerCapabilitiesChartInstance.resize();
                                window.resellerCapabilitiesChartInstance.update();
                            }
                            if (window.resellerPackagesChartInstance) {
                                window.resellerPackagesChartInstance.resize();
                                window.resellerPackagesChartInstance.update();
                            }
                            if (window.resellerRetentionChartInstance) {
                                window.resellerRetentionChartInstance.resize();
                                window.resellerRetentionChartInstance.update();
                            }
                        }, 50);
                    });
                }
            },

            showToast(msg, type = 'success') {
                if (typeof window.showToast === 'function') {
                    try {
                        window.showToast(msg, type);
                        return;
                    } catch (_) {
                    }
                }
                this.toast.message = msg;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            faNum(str) {
                return window.toFaDigits(str);
            },

            async copyText(text, msg = 'آدرس اختصاصی سامانه کپی شد') {
                if (!text) return false;
                let success = false;

                // 1. Modern Clipboard API (works on HTTPS and localhost)
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    try {
                        await navigator.clipboard.writeText(text);
                        success = true;
                    } catch (_) {
                        success = false;
                    }
                }

                // 2. Cross-browser robust fallback for HTTP (e.g. crm-laravel.test) or non-secure origins
                if (!success) {
                    try {
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.setAttribute('readonly', '');
                        ta.style.position = 'fixed';
                        ta.style.top = '0';
                        ta.style.left = '0';
                        ta.style.width = '2em';
                        ta.style.height = '2em';
                        ta.style.padding = '0';
                        ta.style.border = 'none';
                        ta.style.outline = 'none';
                        ta.style.boxShadow = 'none';
                        ta.style.background = 'transparent';
                        ta.style.opacity = '0';
                        ta.style.zIndex = '-9999';
                        document.body.appendChild(ta);
                        ta.focus();
                        ta.select();
                        ta.setSelectionRange(0, ta.value.length);
                        success = document.execCommand('copy');
                        document.body.removeChild(ta);
                    } catch (_) {
                        success = false;
                    }
                }

                if (success) {
                    this.showToast(msg, 'success');
                } else {
                    this.showToast('امکان کپی خودکار فراهم نشد. لطفاً به‌صورت دستی کپی نمایید.', 'error');
                }

                return success;
            },

            formatPrice(amount) {
                if (amount === null || amount === undefined) return '';
                return window.toFaDigits(Number(amount).toLocaleString('en-US')) + ' ' + this.currencyLabel;
            },

            async switchPeriod(pKey) {
                if (this.chartLoading || this.selectedPeriod === pKey) return;
                this.chartLoading = true;
                this.selectedPeriod = pKey;

                const periodLabels = {
                    '30d': '۳۰ روز اخیر',
                    '3m': '۳ ماه اخیر',
                    '6m': '۶ ماه اخیر',
                    '12m': '۱ سال اخیر',
                    'ytd': 'سال جاری (از ابتدای فروردین)'
                };
                if (periodLabels[pKey]) {
                    this.selectedPeriodLabel = periodLabels[pKey];
                }

                try {
                    const baseUrl = this.dashboardUrl || (window.location.origin + window.location.pathname);
                    const url = new URL(baseUrl);
                    url.searchParams.set('period', pKey);
                    url.searchParams.set('format', 'json');

                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (data.periodRevenue !== undefined) {
                            this.animateNumber(data.periodRevenue, 'periodRevenue', true, 600);
                        }
                        if (data.periodTenantsCreated !== undefined) {
                            this.animateNumber(data.periodTenantsCreated, 'periodTenantsCreated', false, 600);
                        }
                        if (data.selectedPeriodLabel || data.periodLabel) {
                            this.selectedPeriodLabel = data.selectedPeriodLabel || data.periodLabel;
                        }
                        this.revenueGrowthPercent = data.revenueGrowthPercent ?? null;
                        this.tenantsGrowthPercent = data.tenantsGrowthPercent ?? null;

                        // Support both format keys seamlessly
                        const rawLabels = data.chartLabels || data.labels || [];
                        const rawTenants = data.tenantsCreatedSeries || data.tenants || [];
                        const rawRevenue = data.revenueSeries || data.revenue || [];

                        window.currentResellerGrowthLabels = Array.isArray(rawLabels) ? rawLabels : Object.values(rawLabels);
                        window.currentResellerTenantsSeries = (Array.isArray(rawTenants) ? rawTenants : Object.values(rawTenants)).map(v => Number(v) || 0);
                        window.currentResellerRevenueSeries = (Array.isArray(rawRevenue) ? rawRevenue : Object.values(rawRevenue)).map(v => Number(v) || 0);

                        if (window.resellerGrowthChartInstance) {
                            window.resellerGrowthChartInstance.data.labels = window.currentResellerGrowthLabels;
                            window.resellerGrowthChartInstance.data.datasets[0].data = window.currentResellerRevenueSeries;
                            window.resellerGrowthChartInstance.data.datasets[1].data = window.currentResellerTenantsSeries;
                            window.resellerGrowthChartInstance.update();
                        } else if (typeof initResellerCharts === 'function') {
                            initResellerCharts();
                        }

                        if (data.onlineOrdersCount !== undefined && window.currentResellerOrderSourceData) {
                            window.currentResellerOrderSourceData.online = data.onlineOrdersCount;
                            window.currentResellerOrderSourceData.manual = data.manualOrdersCount;
                            window.currentResellerOrderSourceData.onlineRevenue = data.onlineOrdersRevenue;
                            window.currentResellerOrderSourceData.manualRevenue = data.manualOrdersRevenue;
                            window.currentResellerOrderSourceData.total = data.totalOrdersCount;
                            if (typeof window.updateResellerStatusChart === 'function') {
                                window.updateResellerStatusChart();
                            }
                        }

                        if (data.featuresStats !== undefined) {
                            window.currentResellerFeaturesStats = data.featuresStats;
                        }
                        if (data.limitsStats !== undefined) {
                            window.currentResellerLimitsStats = data.limitsStats;
                        }
                        if (typeof window.renderCapabilitiesChart === 'function' && window.currentCapabilitiesMode) {
                            window.renderCapabilitiesChart(window.currentCapabilitiesMode);
                        }
                    }
                } catch (err) {
                    console.error('Error switching period:', err);
                } finally {
                    this.chartLoading = false;
                }
            },

            openRenewModalByElement(el) {
                if (!el) return;
                let periods = [];
                try {
                    const raw = el.getAttribute('data-periods');
                    if (raw) periods = JSON.parse(raw);
                } catch (e) {
                    periods = [];
                }
                this.openRenewModal({
                    id: parseInt(el.getAttribute('data-id'), 10),
                    name: el.getAttribute('data-name') || '',
                    subdomain: el.getAttribute('data-subdomain') || '',
                    package_name: el.getAttribute('data-package') || '',
                    current_expires_at: el.getAttribute('data-expires') || 'نامحدود',
                    periods: Array.isArray(periods) ? periods : [],
                    renew_url: el.getAttribute('data-url') || ''
                });
            },

            openRenewModal(tenant) {
                if (!tenant) return;
                this.selectedRenewTenant = {
                    id: tenant.id,
                    name: tenant.name || '',
                    subdomain: tenant.subdomain || '',
                    package_name: tenant.package_name || '',
                    current_expires_at: tenant.current_expires_at || 'نامحدود',
                    periods: Array.isArray(tenant.periods) ? tenant.periods : [],
                    currency: tenant.currency || this.currencyLabel,
                    renew_url: tenant.renew_url || ''
                };

                const periods = this.selectedRenewTenant.periods;
                const has12 = periods.find(p => Number(p.months) === 12);
                if (has12) {
                    this.selectedMonths = 12;
                } else if (periods.length > 0) {
                    this.selectedMonths = Number(periods[0].months) || 12;
                } else {
                    this.selectedMonths = 12;
                }

                this.isCustomMonths = false;
                this.customMonthsInput = '';
                this.issueInvoice = false;
                this.renewNotes = '';
                this.renewLoading = false;
                this.renewModalOpen = true;
            },

            closeRenewModal() {
                this.renewModalOpen = false;
                this.renewLoading = false;
            },

            calculateNewExpiry(currentJalali, months) {
                const mCount = parseInt(months, 10);
                if (isNaN(mCount) || mCount <= 0) {
                    return currentJalali ? this.faNum(currentJalali) : '—';
                }

                let cleaned = '';
                if (currentJalali && typeof currentJalali === 'string') {
                    cleaned = currentJalali.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).trim();
                }

                let y, m, d;
                const parts = cleaned.split('/');
                if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1]) && !isNaN(parts[2])) {
                    y = parseInt(parts[0], 10);
                    m = parseInt(parts[1], 10);
                    d = parseInt(parts[2], 10);
                }

                const todayStr = this.todayJalali || '1405/01/01';
                const todayParts = todayStr.split('/').map(Number);

                if (!y || !m || !d) {
                    y = todayParts[0] || 1405;
                    m = todayParts[1] || 1;
                    d = todayParts[2] || 1;
                } else {
                    if (y < todayParts[0] || (y === todayParts[0] && m < todayParts[1]) || (y === todayParts[0] && m === todayParts[1] && d < todayParts[2])) {
                        y = todayParts[0];
                        m = todayParts[1];
                        d = todayParts[2];
                    }
                }

                const totalMonths = (m - 1) + mCount;
                const newY = y + Math.floor(totalMonths / 12);
                const newM = (totalMonths % 12) + 1;

                let maxDays = 30;
                if (newM <= 6) {
                    maxDays = 31;
                } else if (newM === 12) {
                    maxDays = 29;
                }
                const newD = Math.min(d, maxDays);

                const pad = (n) => String(n).padStart(2, '0');
                const result = `${newY}/${pad(newM)}/${pad(newD)}`;
                return this.faNum(result);
            },

            async confirmRenew() {
                if (!this.selectedRenewTenant || this.renewLoading) return;

                const months = this.isCustomMonths ? parseInt(this.customMonthsInput, 10) : parseInt(this.selectedMonths, 10);
                if (!months || months < 1 || months > 120) {
                    this.showToast('لطفاً تعداد ماه معتبر (بین ۱ تا ۱۲۰ ماه) را وارد نمایید.', 'error');
                    return;
                }

                this.renewLoading = true;

                try {
                    const res = await fetch(this.selectedRenewTenant.renew_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            months: months,
                            issue_invoice: this.issueInvoice ? 1 : 0,
                            notes: this.renewNotes || ''
                        })
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        if (data.issued_invoice && data.redirect_url) {
                            this.showToast(data.message || 'فاکتور تمدید صادر گردید. در حال انتقال به درگاه...', 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1200);
                            return;
                        }

                        const tenantId = this.selectedRenewTenant.id;

                        // Update Table Row Expiration
                        const expiresCell = document.getElementById(`tenant-expires-${tenantId}`);
                        if (expiresCell && data.jalali_expires_at) {
                            expiresCell.innerHTML = `
                                <div class="font-bold text-gray-800 dark:text-gray-200">
                                    ${this.faNum(data.jalali_expires_at)}
                                </div>
                                <div class="text-xs font-bold mt-0.5 text-emerald-600 dark:text-emerald-400">
                                    تمدید شده
                                </div>
                            `;
                        }

                        // Update Table Row Status Badge if it was expired
                        const statusCell = document.getElementById(`tenant-status-${tenantId}`);
                        if (statusCell) {
                            statusCell.innerHTML = `
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border border-emerald-200/60 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    فعال
                                </span>
                            `;
                        }

                        // Synchronize counters & donut chart with animation
                        if (this.expiredTenants > 0) {
                            this.animateNumber(Math.max(0, this.expiredTenants - 1), 'expiredTenants', false, 400);
                            this.animateNumber(this.activeTenants + 1, 'activeTenants', false, 400);
                            if (window.currentResellerStatusData) {
                                window.currentResellerStatusData.active = this.activeTenants;
                                window.currentResellerStatusData.other = this.expiredTenants;
                            }
                            if (typeof window.updateResellerStatusChart === 'function') {
                                window.updateResellerStatusChart();
                            }
                        }

                        this.showToast(data.message || 'لایسنس سامانه با موفقیت تمدید شد.', 'success');
                        this.closeRenewModal();
                    } else {
                        this.showToast(data.message || 'خطا در تمدید لایسنس.', 'error');
                        this.renewLoading = false;
                    }
                } catch (e) {
                    this.showToast('خطا در ارتباط با سرور. لطفاً مجدداً تلاش نمایید.', 'error');
                    this.renewLoading = false;
                }
            },

            openSuspendModalByElement(el) {
                if (!el) return;
                this.openSuspendModal({
                    id: parseInt(el.getAttribute('data-id'), 10),
                    name: el.getAttribute('data-name') || '',
                    subdomain: el.getAttribute('data-subdomain') || '',
                    owner_name: el.getAttribute('data-owner') || '—',
                    suspended: el.getAttribute('data-suspended') === 'true',
                    toggle_url: el.getAttribute('data-url') || ''
                });
            },

            openSuspendModal(tenant) {
                if (!tenant) return;
                this.selectedTenant = {
                    id: tenant.id,
                    name: tenant.name || '',
                    subdomain: tenant.subdomain || '',
                    owner_name: tenant.owner_name || '',
                    suspended: Boolean(tenant.suspended),
                    toggle_url: tenant.toggle_url || ''
                };
                this.loading = false;
                this.suspendModalOpen = true;
            },

            closeModal() {
                this.suspendModalOpen = false;
                this.loading = false;
            },

            async confirmSuspend() {
                if (!this.selectedTenant || this.loading) return;
                this.loading = true;

                try {
                    const res = await fetch(this.selectedTenant.toggle_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        const tenantId = this.selectedTenant.id;
                        const isNowSuspended = (data.status === 'suspended');

                        // Update Table Row Badge
                        const statusCell = document.getElementById(`tenant-status-${tenantId}`);
                        if (statusCell) {
                            if (isNowSuspended) {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border border-amber-200/60 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        مسدود / تعلیق
                                    </span>
                                `;
                            } else {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border border-emerald-200/60 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        فعال
                                    </span>
                                `;
                            }
                        }

                        // Update Action Button inside 3-Dot Dropdown
                        const btn = document.getElementById(`btn-toggle-${tenantId}`);
                        if (btn) {
                            btn.setAttribute('title', isNowSuspended ? 'رفع مسدودیت و فعال‌سازی' : 'مسدودسازی موقت');
                            btn.setAttribute('data-suspended', isNowSuspended ? 'true' : 'false');
                            if (isNowSuspended) {
                                btn.className = 'w-full flex items-center gap-2 px-3.5 py-2 text-start transition-colors cursor-pointer text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40';
                                btn.innerHTML = `
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                    <span>رفع مسدودیت</span>
                                `;
                            } else {
                                btn.className = 'w-full flex items-center gap-2 px-3.5 py-2 text-start transition-colors cursor-pointer text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40';
                                btn.innerHTML = `
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                                    </svg>
                                    <span>مسدودسازی موقت</span>
                                `;
                            }
                        }

                        // Reactive synchronization of Alpine counters with animation
                        if (data.active_count !== undefined) {
                            this.animateNumber(Number(data.active_count), 'activeTenants', false, 400);
                        } else if (isNowSuspended) {
                            this.animateNumber(Math.max(0, this.activeTenants - 1), 'activeTenants', false, 400);
                            this.animateNumber(this.suspendedTenants + 1, 'suspendedTenants', false, 400);
                        } else {
                            this.animateNumber(this.activeTenants + 1, 'activeTenants', false, 400);
                            this.animateNumber(Math.max(0, this.suspendedTenants - 1), 'suspendedTenants', false, 400);
                        }

                        // Synchronize status data for donut chart
                        if (window.currentResellerStatusData) {
                            window.currentResellerStatusData.active = this.activeTenants;
                            window.currentResellerStatusData.suspended = this.suspendedTenants;
                        }
                        if (typeof window.updateResellerStatusChart === 'function') {
                            window.updateResellerStatusChart();
                        }

                        this.showToast(data.message || 'وضعیت سامانه با موفقیت تغییر کرد.', 'success');
                        this.closeModal();
                    } else {
                        this.showToast(data.message || 'خطا در تغییر وضعیت سامانه.', 'error');
                        this.loading = false;
                    }
                } catch (e) {
                    this.showToast('خطا در ارتباط با سرور. لطفاً مجدداً تلاش نمایید.', 'error');
                    this.loading = false;
                }
            }
        };
    }

    // Expose component function and global helpers to window
    window.resellerDashboard = resellerDashboard;
    window.openRenewModalByElement = function (el) {
        if (window.resellerDashboardInstance) {
            window.resellerDashboardInstance.openRenewModalByElement(el);
        }
    };
    window.openSuspendModalByElement = function (el) {
        if (window.resellerDashboardInstance) {
            window.resellerDashboardInstance.openSuspendModalByElement(el);
        }
    };
    window.openRenewModal = function (tenant) {
        if (window.resellerDashboardInstance) {
            window.resellerDashboardInstance.openRenewModal(tenant);
        }
    };
    window.openSuspendModal = function (tenant) {
        if (window.resellerDashboardInstance) {
            window.resellerDashboardInstance.openSuspendModal(tenant);
        }
    };

    // Global Event Listeners
    window.addEventListener('open-renew-modal', function (e) {
        if (window.resellerDashboardInstance) {
            window.resellerDashboardInstance.openRenewModal(e.detail);
        }
    });
    window.addEventListener('open-suspend-modal', function (e) {
        if (window.resellerDashboardInstance) {
            window.resellerDashboardInstance.openSuspendModal(e.detail);
        }
    });

    if (window.Alpine) {
        window.Alpine.data('resellerDashboard', resellerDashboard);
    }
    document.addEventListener('alpine:init', function () {
        window.Alpine.data('resellerDashboard', resellerDashboard);
    });
</script>

@push('scripts')
    <script>
        window.resellerGrowthChartInstance = null;
        window.resellerStatusChartInstance = null;
        window.resellerPipelineChartInstance = null;
        window.resellerRevenueCompChartInstance = null;
        window.currentResellerGrowthLabels = {!! json_encode($chartLabels) !!};
        window.currentResellerTenantsSeries = {!! json_encode($tenantsCreatedSeries->values()) !!};
        window.currentResellerRevenueSeries = {!! json_encode($revenueSeries->values()) !!};
        window.currentResellerStatusData = {
            active: {{ (int)$activeTenants }},
            suspended: {{ (int)$suspendedTenants }},
            other: {{ (int)max(0, $totalTenants - $activeTenants - $suspendedTenants) }}
        };
        window.currentResellerOrderSourceData = {
            online: {{ (int)$onlineOrdersCount }},
            manual: {{ (int)$manualOrdersCount }},
            onlineRevenue: {{ (int)$onlineOrdersRevenue }},
            manualRevenue: {{ (int)$manualOrdersRevenue }},
            total: {{ (int)$totalOrdersCount }}
        };
        window.currentResellerPipelineData = {!! json_encode($pipelineData) !!};
        window.currentResellerRevenueComposition = {!! json_encode($revenueComposition) !!};
        window.currentResellerTotalFinancialVolume = {{ (int)$totalFinancialVolume }};
        window.resellerCapabilitiesChartInstance = null;
        window.resellerPackagesChartInstance = null;
        window.resellerRetentionChartInstance = null;
        window.currentResellerFeaturesStats = {!! json_encode($featuresStats) !!};
        window.currentResellerLimitsStats = {!! json_encode($limitsStats) !!};
        window.currentResellerPackageDistribution = {!! json_encode($packageDistribution) !!};
        window.currentResellerRetentionCohorts = {!! json_encode($retentionCohorts) !!};
        window.currentResellerTotalTenants = {{ (int)$totalTenants }};

        function initResellerCharts() {
            if (!window.Chart) {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9ca3af' : '#4b5563';
            const textHeadColor = isDark ? '#e5e7eb' : '#1f2937';
            const gridColor = isDark ? '#374151' : '#f3f4f6';
            const chartFontFamily = "'IRANYekanX', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

            window.Chart.defaults.font.family = chartFontFamily;
            if (window.Chart.defaults.plugins && window.Chart.defaults.plugins.tooltip) {
                window.Chart.defaults.plugins.tooltip.rtl = true;
                window.Chart.defaults.plugins.tooltip.textDirection = 'rtl';
                window.Chart.defaults.plugins.tooltip.titleAlign = 'left';
                window.Chart.defaults.plugins.tooltip.bodyAlign = 'right';
                window.Chart.defaults.plugins.tooltip.footerAlign = 'left';
            }

            const ctxGrowth = document.getElementById('resellerGrowthChart');
            if (ctxGrowth && window.currentResellerGrowthLabels && window.currentResellerGrowthLabels.length > 0) {
                if (window.resellerGrowthChartInstance) {
                    window.resellerGrowthChartInstance.destroy();
                }

                const ctx = ctxGrowth.getContext('2d');
                const revenueGradient = ctx.createLinearGradient(0, 0, 0, 300);
                revenueGradient.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
                revenueGradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

                window.resellerGrowthChartInstance = new window.Chart(ctxGrowth, {
                    data: {
                        labels: window.currentResellerGrowthLabels,
                        datasets: [
                            {
                                type: 'line',
                                label: 'درآمد وصولی',
                                data: window.currentResellerRevenueSeries,
                                borderColor: '#10b981',
                                backgroundColor: revenueGradient,
                                borderWidth: 2.5,
                                fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: isDark ? '#064e3b' : '#ffffff',
                            pointBorderWidth: isDark ? 1.5 : 2,
                            pointHoverBackgroundColor: isDark ? '#34d399' : '#ffffff',
                            pointHoverBorderColor: '#10b981',
                            yAxisID: 'yRevenue',
                            order: 0,
                        },
                        {
                            type: 'bar',
                            label: 'سامانه جدید',
                            data: window.currentResellerTenantsSeries,
                            backgroundColor: 'rgba(99, 102, 241, 0.85)',
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 6,
                            borderSkipped: false,
                            maxBarThickness: 28,
                            yAxisID: 'yTenants',
                            order: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {mode: 'index', intersect: false},
                    animation: {duration: 500, easing: 'easeOutQuart'},
                    plugins: {
                        legend: {display: false},
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            titleAlign: 'left',
                            bodyAlign: 'right',
                            footerAlign: 'left',
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: isDark ? '#f9fafb' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: true,
                            boxPadding: 4,
                            callbacks: {
                                label: function (c) {
                                    if (c.dataset.yAxisID === 'yRevenue') {
                                        return ' درآمد وصولی: ' + window.toFaDigits(Number(c.parsed.y).toLocaleString('en-US')) + ' {{ $currencyLabel }}';
                                    }
                                    return ' سامانه جدید: ' + window.toFaDigits(c.parsed.y) + ' CRM';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            reverse: true,
                            grid: {display: false},
                            border: {display: false},
                            ticks: {
                                font: {family: chartFontFamily, size: 11},
                                color: textColor
                            }
                        },
                        yTenants: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                precision: 0,
                                font: {family: chartFontFamily, size: 11},
                                callback: function (v) {
                                    return window.toFaDigits(v);
                                }
                            },
                            grid: {color: isDark ? 'rgba(255, 255, 255, 0.04)' : gridColor},
                            border: {display: false}
                        },
                        yRevenue: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                font: {family: chartFontFamily, size: 11},
                                callback: function (v) {
                                    if (v === 0) return '۰';
                                    return window.toFaDigits(Math.round(v).toLocaleString('en-US')) + ' {{ $currencyLabel }}';
                                }
                            },
                            grid: {display: false},
                            border: {display: false}
                        }
                    }
                }
            });
        }

        const ctxOrderSource = document.getElementById('resellerStatusChart');
        if (ctxOrderSource && window.currentResellerOrderSourceData) {
            if (window.resellerStatusChartInstance) {
                window.resellerStatusChartInstance.destroy();
            }

            const oData = window.currentResellerOrderSourceData;
            const sourceLabels = [];
            const sourceCounts = [];
            const sourceColors = [];
            const sourceRevenues = [];

            if ((oData.online || 0) > 0) {
                sourceLabels.push('خرید آنلاین از سایت');
                sourceCounts.push(oData.online);
                sourceColors.push('#8b5cf6');
                sourceRevenues.push(oData.onlineRevenue || 0);
            }
            if ((oData.manual || 0) > 0) {
                sourceLabels.push('ثبت دستی نماینده');
                sourceCounts.push(oData.manual);
                sourceColors.push('#3b82f6');
                sourceRevenues.push(oData.manualRevenue || 0);
            }

            if (sourceCounts.length > 0) {
                window.resellerStatusChartInstance = new window.Chart(ctxOrderSource, {
                    type: 'doughnut',
                    data: {
                        labels: sourceLabels,
                        datasets: [{
                            data: sourceCounts,
                            backgroundColor: sourceColors,
                            hoverOffset: 6,
                            borderWidth: isDark ? 0 : 2,
                            borderColor: isDark ? 'transparent' : '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '74%',
                        animation: {duration: 500, easing: 'easeOutQuart'},
                        plugins: {
                            legend: {display: false},
                            tooltip: {
                                rtl: true,
                                textDirection: 'rtl',
                                titleAlign: 'left',
                                bodyAlign: 'right',
                                backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                                titleColor: isDark ? '#f9fafb' : '#111827',
                                bodyColor: isDark ? '#d1d5db' : '#374151',
                                borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function (ctx) {
                                        const count = ctx.parsed;
                                        const rev = sourceRevenues[ctx.dataIndex];
                                        const revStr = rev ? ' (' + window.toFaDigits(Number(rev).toLocaleString('en-US')) + ' {{ $currencyLabel }})' : '';
                                        return ' ' + ctx.label + ': ' + window.toFaDigits(count) + ' سفارش' + revStr;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        const ctxPipeline = document.getElementById('resellerPipelineChart');
        if (ctxPipeline && window.currentResellerPipelineData) {
            if (window.resellerPipelineChartInstance) {
                window.resellerPipelineChartInstance.destroy();
            }

            const pData = window.currentResellerPipelineData;
            const pLabels = [
                '۳۰ روز آینده',
                '۳۱ تا ۶۰ روز',
                '۶۱ تا ۹۰ روز'
            ];
            const pRevenues = [
                (pData['30d'] && pData['30d'].revenue) ? pData['30d'].revenue : 0,
                (pData['60d'] && pData['60d'].revenue) ? pData['60d'].revenue : 0,
                (pData['90d'] && pData['90d'].revenue) ? pData['90d'].revenue : 0
            ];
            const pCounts = [
                (pData['30d'] && pData['30d'].count) ? pData['30d'].count : 0,
                (pData['60d'] && pData['60d'].count) ? pData['60d'].count : 0,
                (pData['90d'] && pData['90d'].count) ? pData['90d'].count : 0
            ];

            const pCtx = ctxPipeline.getContext('2d');
            const revenueGradient = pCtx.createLinearGradient(0, 0, 0, 240);
            revenueGradient.addColorStop(0, 'rgba(6, 182, 212, 0.95)');
            revenueGradient.addColorStop(1, 'rgba(8, 145, 178, 0.25)');

            const countGradient = pCtx.createLinearGradient(0, 0, 0, 240);
            countGradient.addColorStop(0, 'rgba(99, 102, 241, 0.92)');
            countGradient.addColorStop(1, 'rgba(79, 70, 229, 0.28)');

            window.resellerPipelineChartInstance = new window.Chart(ctxPipeline, {
                type: 'bar',
                data: {
                    labels: pLabels,
                    datasets: [
                        {
                            label: 'گردش پیش‌بینی‌شده',
                            data: pRevenues,
                            backgroundColor: revenueGradient,
                            hoverBackgroundColor: '#0891b2',
                            borderColor: 'transparent',
                            borderWidth: 0,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 32,
                            categoryPercentage: 0.65,
                            barPercentage: 0.85,
                            yAxisID: 'yRevenue',
                            order: 1
                        },
                        {
                            label: 'سامانه‌های سررسید',
                            data: pCounts,
                            backgroundColor: countGradient,
                            hoverBackgroundColor: '#4f46e5',
                            borderColor: 'transparent',
                            borderWidth: 0,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 32,
                            categoryPercentage: 0.65,
                            barPercentage: 0.85,
                            yAxisID: 'yCount',
                            order: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {mode: 'index', intersect: false},
                    animation: {duration: 550, easing: 'easeOutQuart'},
                    layout: {
                        padding: {
                            top: 14,
                            right: 16,
                            bottom: 8,
                            left: 16
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'center',
                            rtl: true,
                            textDirection: 'rtl',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                borderRadius: 4,
                                useBorderRadius: true,
                                padding: 16,
                                color: textColor,
                                font: {family: chartFontFamily, size: 11, weight: 'bold'}
                            }
                        },
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            titleAlign: 'right',
                            bodyAlign: 'right',
                            footerAlign: 'right',
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: isDark ? '#f9fafb' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            boxPadding: 4,
                            callbacks: {
                                label: function (c) {
                                    if (c.dataset.yAxisID === 'yRevenue') {
                                        return ' گردش پیش‌بینی‌شده: ' + window.toFaDigits(Number(c.parsed.y).toLocaleString('en-US')) + ' {{ $currencyLabel }}';
                                    }
                                    return ' سامانه‌های سررسید: ' + window.toFaDigits(c.parsed.y) + ' سامانه';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            reverse: true,
                            grid: {display: false},
                            border: {display: false},
                            ticks: {
                                font: {family: chartFontFamily, size: 11, weight: 'bold'},
                                color: textHeadColor,
                                padding: 6
                            }
                        },
                        yCount: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                precision: 0,
                                font: {family: chartFontFamily, size: 11},
                                padding: 8,
                                callback: function (v) {
                                    return window.toFaDigits(v) + ' CRM';
                                }
                            },
                            grid: {color: isDark ? 'rgba(255, 255, 255, 0.04)' : gridColor},
                            border: {display: false}
                        },
                        yRevenue: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                font: {family: chartFontFamily, size: 11},
                                padding: 8,
                                callback: function (v) {
                                    if (v === 0) return '۰';
                                    return window.toFaDigits(Math.round(v).toLocaleString('en-US')) + ' {{ $currencyLabel }}';
                                }
                            },
                            grid: {display: false},
                            border: {display: false}
                        }
                    }
                }
            });
        }

        const ctxComp = document.getElementById('resellerRevenueCompChart');
        if (ctxComp && window.currentResellerRevenueComposition && window.currentResellerTotalFinancialVolume > 0) {
            if (window.resellerRevenueCompChartInstance) {
                window.resellerRevenueCompChartInstance.destroy();
            }

            const comp = window.currentResellerRevenueComposition;
            const compLabels = [
                comp.collected?.label || 'درآمد وصول‌شده',
                comp.receivables?.label || 'مطالبات معوق و جاری',
                comp.forecasted?.label || 'پیش‌بینی تمدیدها'
            ];
            const compData = [
                Number(comp.collected?.amount || 0),
                Number(comp.receivables?.amount || 0),
                Number(comp.forecasted?.amount || 0)
            ];
            const compColors = [
                comp.collected?.color || '#10b981',
                comp.receivables?.color || '#f59e0b',
                comp.forecasted?.color || '#6366f1'
            ];
            const compCounts = [
                comp.collected?.count || 0,
                comp.receivables?.count || 0,
                comp.forecasted?.count || 0
            ];

            window.resellerRevenueCompChartInstance = new window.Chart(ctxComp, {
                type: 'doughnut',
                data: {
                    labels: compLabels,
                    datasets: [{
                        data: compData,
                        backgroundColor: compColors,
                        hoverOffset: 6,
                        borderWidth: isDark ? 0 : 2,
                        borderColor: isDark ? 'transparent' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    animation: {duration: 500, easing: 'easeOutQuart'},
                    plugins: {
                        legend: {display: false},
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            titleAlign: 'right',
                            bodyAlign: 'right',
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: isDark ? '#f9fafb' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function (ctx) {
                                    const idx = ctx.dataIndex;
                                    const count = compCounts[idx];
                                    const countStr = count ? ' (' + window.toFaDigits(count) + ' مورد)' : '';
                                    const total = window.currentResellerTotalFinancialVolume || 1;
                                    const pct = ((Number(ctx.parsed) / total) * 100).toFixed(1);
                                    return ' ' + ctx.label + ': ' + window.toFaDigits(Number(ctx.parsed).toLocaleString('en-US')) + ' {{ $currencyLabel }} (' + window.toFaDigits(pct) + '٪)' + countStr;
                                }
                            }
                        }
                    }
                }
            });
        }
        // Initialize Phase 4 Intelligence charts as well if canvas elements are present
        initResellerIntelligenceCharts();
    }

    function updateResellerStatusChart() {
        const ctxStatus = document.getElementById('resellerStatusChart');
        if (!ctxStatus || !window.currentResellerOrderSourceData || !window.Chart) return;
        const isDark = document.documentElement.classList.contains('dark');
        const oData = window.currentResellerOrderSourceData;
        const sourceLabels = [];
        const sourceCounts = [];
        const sourceColors = [];

        if ((oData.online || 0) > 0) {
            sourceLabels.push('خرید آنلاین از سایت');
            sourceCounts.push(oData.online);
            sourceColors.push('#8b5cf6');
        }
        if ((oData.manual || 0) > 0) {
            sourceLabels.push('ثبت دستی نماینده');
            sourceCounts.push(oData.manual);
            sourceColors.push('#3b82f6');
        }

        if (window.resellerStatusChartInstance) {
            window.resellerStatusChartInstance.data.labels = sourceLabels;
            window.resellerStatusChartInstance.data.datasets[0].data = sourceCounts;
            window.resellerStatusChartInstance.data.datasets[0].backgroundColor = sourceColors;
            window.resellerStatusChartInstance.data.datasets[0].borderColor = isDark ? 'transparent' : '#ffffff';
            window.resellerStatusChartInstance.data.datasets[0].borderWidth = isDark ? 0 : 2;
            window.resellerStatusChartInstance.update();
        } else {
            initResellerCharts();
        }
    }

    window.updateResellerStatusChart = updateResellerStatusChart;

    function initResellerIntelligenceCharts() {
        if (!window.Chart) return;

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#4b5563';
        const textHeadColor = isDark ? '#e5e7eb' : '#1f2937';
        const gridColor = isDark ? '#374151' : '#f3f4f6';
        const radarGridColor = isDark ? 'rgba(75, 85, 99, 0.45)' : 'rgba(229, 231, 235, 0.85)';
        const chartFontFamily = "'IRANYekanX', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";

        window.currentCapabilitiesMode = window.currentCapabilitiesMode || 'features';

        function renderCapabilitiesChart(mode) {
            window.currentCapabilitiesMode = mode;
            const ctxCap = document.getElementById('resellerCapabilitiesChart');
            if (!ctxCap || !window.Chart) return;

            if (window.resellerCapabilitiesChartInstance) {
                window.resellerCapabilitiesChartInstance.destroy();
            }

            const totalT = window.currentResellerTotalTenants || 1;
            let chartLabels = [];
            let chartDatasets = [];

            if (mode === 'features') {
                const feats = window.currentResellerFeaturesStats || [];
                chartLabels = feats.map(f => f.name);
                const ratios = feats.map(f => Number(f.ratio) || 0);
                const counts = feats.map(f => Number(f.count) || 0);
                const colors = feats.map(f => f.color || '#6366f1');

                chartDatasets = [{
                    label: 'ضریب نفوذ در مشترکین (٪)',
                    data: ratios,
                    backgroundColor: colors,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 18,
                    _counts: counts,
                }];
            } else {
                const limits = window.currentResellerLimitsStats || [];
                chartLabels = limits.map(l => l.name);
                const limitedRatios = limits.map(l => Number(l.limitedRatio) || 0);
                const unlimitedRatios = limits.map(l => Number(l.unlimitedRatio) || 0);
                const limitedCounts = limits.map(l => Number(l.limitedCount) || 0);
                const unlimitedCounts = limits.map(l => Number(l.unlimitedCount) || 0);

                chartDatasets = [
                    {
                        label: 'دارای سقف مشخص (محدود)',
                        data: limitedRatios,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 15,
                        _counts: limitedCounts,
                    },
                    {
                        label: 'لایسنس نامحدود',
                        data: unlimitedRatios,
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 15,
                        _counts: unlimitedCounts,
                    }
                ];
            }

            window.resellerCapabilitiesChartInstance = new window.Chart(ctxCap, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {duration: 500, easing: 'easeOutQuart'},
                    plugins: {
                        legend: {
                            display: mode === 'limits',
                            position: 'top',
                            align: 'center',
                            rtl: true,
                            textDirection: 'rtl',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                borderRadius: 3,
                                useBorderRadius: true,
                                padding: 12,
                                color: textColor,
                                font: {family: chartFontFamily, size: 11, weight: 'bold'}
                            }
                        },
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            titleAlign: 'right',
                            bodyAlign: 'right',
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: isDark ? '#f9fafb' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function (ctx) {
                                    const val = Number(ctx.parsed.x).toFixed(1);
                                    const count = ctx.dataset._counts ? ctx.dataset._counts[ctx.dataIndex] : 0;
                                    return ' ' + ctx.dataset.label + ': ' + window.toFaDigits(val) + '٪ (' + window.toFaDigits(count) + ' سامانه)';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            grid: {color: isDark ? 'rgba(255, 255, 255, 0.04)' : gridColor},
                            ticks: {
                                color: textColor,
                                font: {family: chartFontFamily, size: 11},
                                callback: function (v) {
                                    return window.toFaDigits(v) + '٪';
                                }
                            }
                        },
                        y: {
                            grid: {display: false},
                            ticks: {
                                color: textHeadColor,
                                font: {family: chartFontFamily, size: 11, weight: 'bold'}
                            }
                        }
                    }
                }
            });
        }

        window.renderCapabilitiesChart = renderCapabilitiesChart;
        window.switchCapabilitiesView = function (mode) {
            renderCapabilitiesChart(mode);
        };

        renderCapabilitiesChart(window.currentCapabilitiesMode || 'features');


        const ctxPkg = document.getElementById('resellerPackagesChart');
        if (ctxPkg && window.currentResellerPackageDistribution && window.currentResellerPackageDistribution.length > 0) {
            if (window.resellerPackagesChartInstance) {
                window.resellerPackagesChartInstance.destroy();
            }

            const pkgs = window.currentResellerPackageDistribution;
            const pLabels = pkgs.map(p => p.name);
            const pCounts = pkgs.map(p => Number(p.count) || 0);
            const pColors = pkgs.map(p => p.color);
            const totalT = window.currentResellerTotalTenants || 1;

            window.resellerPackagesChartInstance = new window.Chart(ctxPkg, {
                type: 'doughnut',
                data: {
                    labels: pLabels,
                    datasets: [{
                        data: pCounts,
                        backgroundColor: pColors,
                        hoverOffset: 6,
                        borderWidth: isDark ? 0 : 2,
                        borderColor: isDark ? 'transparent' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    animation: {duration: 550, easing: 'easeOutQuart'},
                    plugins: {
                        legend: {display: false},
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            titleAlign: 'right',
                            bodyAlign: 'right',
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: isDark ? '#f9fafb' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function (ctx) {
                                    const val = Number(ctx.parsed);
                                    const pct = ((val / totalT) * 100).toFixed(1);
                                    return ' ' + ctx.label + ': ' + window.toFaDigits(val) + ' سامانه (' + window.toFaDigits(pct) + '٪)';
                                }
                            }
                        }
                    }
                }
            });
        }

        const ctxRetention = document.getElementById('resellerRetentionChart');
        if (ctxRetention && window.currentResellerRetentionCohorts) {
            if (window.resellerRetentionChartInstance) {
                window.resellerRetentionChartInstance.destroy();
            }

            const cohortEntries = Object.values(window.currentResellerRetentionCohorts);
            const cLabels = cohortEntries.map(c => c.label);
            const cCounts = cohortEntries.map(c => Number(c.count) || 0);
            const cColors = cohortEntries.map(c => c.color);
            const totalT = window.currentResellerTotalTenants || 1;

            window.resellerRetentionChartInstance = new window.Chart(ctxRetention, {
                type: 'bar',
                data: {
                    labels: cLabels,
                    datasets: [{
                        label: 'سامانه‌های مستقر',
                        data: cCounts,
                        backgroundColor: cColors,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 24,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {duration: 600, easing: 'easeOutQuart'},
                    plugins: {
                        legend: {display: false},
                        tooltip: {
                            rtl: true,
                            textDirection: 'rtl',
                            titleAlign: 'right',
                            bodyAlign: 'right',
                            backgroundColor: isDark ? 'rgba(17, 24, 39, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                            titleColor: isDark ? '#f9fafb' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#374151',
                            borderColor: isDark ? '#374151' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function (ctx) {
                                    const val = Number(ctx.parsed.x);
                                    const pct = ((val / totalT) * 100).toFixed(1);
                                    return ' ' + ctx.dataset.label + ': ' + window.toFaDigits(val) + ' سامانه (' + window.toFaDigits(pct) + '٪)';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {color: gridColor},
                            ticks: {
                                color: textColor,
                                font: {family: chartFontFamily, size: 11},
                                precision: 0,
                                callback: function (v) {
                                    return window.toFaDigits(v) + ' سامانه';
                                }
                            }
                        },
                        y: {
                            grid: {display: false},
                            ticks: {
                                color: textHeadColor,
                                font: {family: chartFontFamily, size: 11, weight: 'bold'}
                            }
                        }
                    }
                }
            });
        }
    }

    window.initResellerIntelligenceCharts = initResellerIntelligenceCharts;

    // Lifecycle triggers identical to Modules/Projects
    document.addEventListener('DOMContentLoaded', function () {
        initResellerCharts();
    });
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initResellerCharts, 100);
    }
    // Safety retry in case deferred Vite module script loads slightly after HTML parse
    setTimeout(initResellerCharts, 400);
    setTimeout(initResellerCharts, 1200);

    // Dynamic Dark/Light Theme Reactivity: Re-render charts when class on <html> changes
    let lastThemeIsDark = document.documentElement.classList.contains('dark');
    const themeObserver = new MutationObserver(function () {
        const currentIsDark = document.documentElement.classList.contains('dark');
        if (currentIsDark !== lastThemeIsDark) {
            lastThemeIsDark = currentIsDark;
            initResellerCharts();
        }
    });
    themeObserver.observe(document.documentElement, {attributes: true, attributeFilter: ['class']});

    // Ambient Spotlight Mouse Tracker (Linear Style)
    document.addEventListener('mousemove', function (e) {
        const cards = document.querySelectorAll('.spotlight-card');
        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            if (x >= -50 && x <= rect.width + 50 && y >= -50 && y <= rect.height + 50) {
                card.style.setProperty('--mouse-x', x + 'px');
                card.style.setProperty('--mouse-y', y + 'px');
            }
        }
    }, {passive: true});
</script>
@endpush
