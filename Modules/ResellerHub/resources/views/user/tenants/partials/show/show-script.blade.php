@php use Morilog\Jalali\Jalalian; @endphp
<script>
    window.tenantPageConfig = {
        tenantId: {{ (int)$tenant->id }},
        status: @json($tenant->status),
        expiresAt: @json($tenant->plan_expires_at ? reseller_format_jalali($tenant->plan_expires_at, 'Y/m/d') : 'نامحدود'),
        customDomain: @json($tenant->custom_domain ?? ''),
        cnameTarget: @json(app(\Modules\ResellerHub\App\Services\DomainVerificationService::class)->getTargetCname()),
        availablePeriods: @json($availablePeriods),
        currency: @json($currencyLabel),
        packageName: @json($tenantPackage?->name ?? 'طرح اختصاصی'),
        todayJalali: @json(Jalalian::now()->format('Y/m/d')),
        name: @json($tenant->name),
        subdomain: @json($displayUrl),
        ownerName: @json($managerName),
        renewUrl: @json(route('reseller.tenants.renew', $tenant->id)),
        toggleUrl: @json(route('reseller.tenants.toggle-status', $tenant->id)),
        updateDomainUrl: @json(route('reseller.tenants.update-domain', $tenant->id))
    };

    function tenantDetails(config) {
        config = config || window.tenantPageConfig || {};
        return {
            tenantId: config.tenantId,
            status: config.status,
            expiresAt: config.expiresAt,
            customDomain: config.customDomain || '',
            cnameTarget: config.cnameTarget || window.location.hostname,
            availablePeriods: config.availablePeriods || [],
            currency: config.currency || 'تومان',
            packageName: config.packageName || '',
            todayJalali: config.todayJalali || '{{ Jalalian::now()->format("Y/m/d") }}',
            name: config.name || '',
            subdomain: config.subdomain || '',
            ownerName: config.ownerName || '',
            renewUrl: config.renewUrl || '',
            toggleUrl: config.toggleUrl || '',
            updateDomainUrl: config.updateDomainUrl || '',
            dnsModalOpen: false,
            dnsChecking: false,
            dnsStatus: 'pending',
            dnsStatusLabel: 'در حال بررسی...',
            dnsMessage: '',
            dnsInfo: null,
            domainModalOpen: false,
            domainInput: config.customDomain || '',
            domainLoading: false,
            domainRemoving: false,
            domainError: '',
            suspendModalOpen: false,
            loading: false,
            selectedTenant: {
                id: null,
                name: '',
                subdomain: '',
                owner_name: '',
                suspended: false,
                toggle_url: ''
            },

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
                currency: 'تومان',
                renew_url: ''
            },

            toast: {show: false, message: '', type: 'success'},

            init() {
                if (this.customDomain) {
                    this.checkDns();
                }
            },

            async checkDns() {
                if (!this.customDomain || this.dnsChecking) return;
                this.dnsChecking = true;
                try {
                    const res = await fetch(`{{ route('reseller.tenants.check-domain') }}?domain=${encodeURIComponent(this.customDomain)}&tenant_id=${this.tenantId}`);
                    const data = await res.json();
                    if (data.dns) {
                        this.dnsStatus = data.dns.status;
                        this.dnsStatusLabel = data.dns.status_label;
                        this.dnsMessage = data.dns.message;
                        this.dnsInfo = data.dns;
                    } else {
                        this.dnsStatus = data.valid ? 'pending' : 'unresolved';
                        this.dnsMessage = data.message;
                    }
                } catch (e) {
                    this.dnsStatus = 'error';
                    this.dnsMessage = 'خطا در استعلام رکوردهای دامنه';
                } finally {
                    this.dnsChecking = false;
                }
            },

            copyText(text, msg = 'کپی شد') {
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

            faNum(str) {
                return window.faNum ? window.faNum(str) : (str !== null && str !== undefined ? String(str) : '');
            },

            showToast(msg, type = 'success') {
                this.toast.message = msg;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            openSuspendModal(tenant) {
                const t = tenant || {};
                this.selectedTenant = {
                    id: t.id || this.tenantId,
                    name: t.name || this.name,
                    subdomain: t.subdomain || this.subdomain,
                    owner_name: t.owner_name || this.ownerName,
                    suspended: this.status === 'suspended',
                    toggle_url: t.toggle_url || this.toggleUrl
                };
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
                        }
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        this.status = data.status;
                        this.showToast(data.message, 'success');
                        this.closeModal();
                    } else {
                        this.showToast(data.message || 'خطا در تغییر وضعیت سامانه.', 'error');
                        this.loading = false;
                    }
                } catch (e) {
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                    this.loading = false;
                }
            },

            openRenewModal(tenant) {
                const t = tenant || {};
                this.selectedRenewTenant = {
                    id: t.id || this.tenantId,
                    name: t.name || this.name,
                    subdomain: t.subdomain || this.subdomain,
                    package_name: t.package_name || this.packageName,
                    current_expires_at: t.current_expires_at || this.expiresAt,
                    periods: t.periods || this.availablePeriods || [],
                    currency: t.currency || this.currency || 'تومان',
                    renew_url: t.renew_url || this.renewUrl
                };
                const has12 = this.selectedRenewTenant.periods.find(p => p.months === 12);
                if (has12) {
                    this.selectedMonths = 12;
                } else if (this.selectedRenewTenant.periods.length > 0) {
                    this.selectedMonths = this.selectedRenewTenant.periods[0].months;
                } else {
                    this.selectedMonths = 12;
                }
                this.isCustomMonths = false;
                this.customMonthsInput = '';
                this.issueInvoice = false;
                this.renewNotes = '';
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

                const todayStr = this.todayJalali || '{{ Jalalian::now()->format("Y/m/d") }}';
                const todayParts = todayStr.split('/').map(Number);

                if (!y || !m || !d) {
                    y = todayParts[0];
                    m = todayParts[1];
                    d = todayParts[2];
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
                            this.showToast(data.message || 'فاکتور تمدید با موفقیت صادر شد. در حال انتقال...', 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1200);
                            return;
                        }

                        this.expiresAt = this.faNum(data.jalali_expires_at);
                        this.status = 'active';
                        this.showToast(data.message, 'success');
                        this.closeRenewModal();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        this.showToast(data.message || 'خطا در تمدید لایسنس.', 'error');
                        this.renewLoading = false;
                    }
                } catch (e) {
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                    this.renewLoading = false;
                }
            },

            openDomainModal() {
                this.domainInput = this.customDomain || '';
                this.domainError = '';
                this.domainRemoving = false;
                this.domainModalOpen = true;
            },

            closeDomainModal() {
                if (this.domainLoading) return;
                this.domainModalOpen = false;
                this.domainLoading = false;
                this.domainRemoving = false;
                this.domainError = '';
            },

            async saveCustomDomain() {
                if (this.domainLoading) return;
                const domain = (this.domainInput || '').trim().toLowerCase().replace(/^https?:\/\//, '').replace(/\/+$/, '');
                if (!domain) {
                    this.domainError = 'لطفاً آدرس دامنه را وارد نمایید.';
                    return;
                }

                this.domainLoading = true;
                this.domainError = '';

                try {
                    const res = await fetch(this.updateDomainUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            custom_domain: domain
                        })
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        this.customDomain = data.custom_domain;
                        if (data.dns) {
                            this.dnsStatus = data.dns.status;
                            this.dnsStatusLabel = data.dns.status_label;
                            this.dnsMessage = data.dns.message;
                            this.dnsInfo = data.dns;
                        }
                        this.showToast(data.message, 'success');
                        this.closeDomainModal();
                        this.checkDns();
                    } else {
                        this.domainError = data.message || 'خطا در ثبت دامنه اختصاصی.';
                        this.showToast(data.message || 'خطا در ثبت دامنه اختصاصی.', 'error');
                    }
                } catch (e) {
                    this.domainError = 'خطا در برقراری ارتباط با سرور.';
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                } finally {
                    this.domainLoading = false;
                }
            },

            async removeCustomDomain() {
                if (this.domainLoading) return;
                this.domainLoading = true;
                this.domainError = '';

                try {
                    const res = await fetch(this.updateDomainUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            custom_domain: ''
                        })
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        this.customDomain = '';
                        this.dnsStatus = 'pending';
                        this.dnsInfo = null;
                        this.showToast(data.message, 'success');
                        this.closeDomainModal();
                    } else {
                        this.domainError = data.message || 'خطا در حذف دامنه اختصاصی.';
                        this.showToast(data.message || 'خطا در حذف دامنه اختصاصی.', 'error');
                    }
                } catch (e) {
                    this.domainError = 'خطا در برقراری ارتباط با سرور.';
                    this.showToast('خطا در برقراری ارتباط با سرور.', 'error');
                } finally {
                    this.domainLoading = false;
                    this.domainRemoving = false;
                }
            }
        };
    }
</script>
