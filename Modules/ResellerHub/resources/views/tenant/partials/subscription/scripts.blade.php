<script>
        function tenantSubscriptionHandler() {
            const pkgs = @json($availableUpgradePackages);
            return {
                upgradeModal: false,
                pendingInvoiceModal: false,
                pendingInvoiceData: null,
                cancelInvoiceModal: false,
                cancellingInvoiceId: null,
                cancellingInvoiceNumber: '',
                cancelLoading: false,
                availablePackages: pkgs,
                selectedPackageId: (pkgs && pkgs.length > 0) ? pkgs[0].id : null,
                selectedDurationMonths: (pkgs && pkgs.length > 0 && Array.isArray(pkgs[0].available_periods) && pkgs[0].available_periods.length > 0)
                    ? ((pkgs[0].available_periods.find(p => p.months === 12) || pkgs[0].available_periods[0]).months)
                    : 12,
                upgradeMessage: '',
                submitting: false,

                // Collapsible sections state (persisted in localStorage)
                sectionQuotasOpen: localStorage.getItem('tenant_section_quotas_open') !== 'false',
                sectionFeaturesOpen: localStorage.getItem('tenant_section_features_open') !== 'false',
                sectionInvoicesOpen: localStorage.getItem('tenant_section_invoices_open') !== 'false',

                toggleSection(sectionKey) {
                    if (sectionKey === 'quotas') {
                        this.sectionQuotasOpen = !this.sectionQuotasOpen;
                        localStorage.setItem('tenant_section_quotas_open', this.sectionQuotasOpen);
                    } else if (sectionKey === 'features') {
                        this.sectionFeaturesOpen = !this.sectionFeaturesOpen;
                        localStorage.setItem('tenant_section_features_open', this.sectionFeaturesOpen);
                    } else if (sectionKey === 'invoices') {
                        this.sectionInvoicesOpen = !this.sectionInvoicesOpen;
                        localStorage.setItem('tenant_section_invoices_open', this.sectionInvoicesOpen);
                    }
                },

                get selectedPackage() {
                    if (!this.availablePackages || !this.selectedPackageId) return null;
                    return this.availablePackages.find(p => p.id === this.selectedPackageId) || null;
                },
                get selectedPackageName() {
                    return this.selectedPackage ? this.selectedPackage.name : '';
                },
                get packageAvailablePeriods() {
                    if (!this.selectedPackage || !Array.isArray(this.selectedPackage.available_periods)) {
                        return [];
                    }
                    return this.selectedPackage.available_periods;
                },
                selectPackage(id) {
                    this.selectedPackageId = id;
                    // Reset selected duration to first available or 12
                    const periods = this.packageAvailablePeriods;
                    if (periods.length > 0) {
                        const hasCurrent = periods.find(p => p.months === this.selectedDurationMonths);
                        if (!hasCurrent) {
                            const has12 = periods.find(p => p.months === 12);
                            this.selectedDurationMonths = has12 ? 12 : periods[0].months;
                        }
                    }
                },
                getSelectedPeriodData() {
                    const periods = this.packageAvailablePeriods;
                    return periods.find(p => p.months === this.selectedDurationMonths) || null;
                },
                renderPkgIcon(icon, sizeClass) {
                    if (window.renderIndustryIcon) {
                        return window.renderIndustryIcon(icon || 'cube', sizeClass || 'w-4 h-4');
                    }
                    const svgMap = window.industryIconSvgs || {};
                    const path = svgMap[icon] || svgMap['cube'] || '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>';
                    return '<svg class="' + (sizeClass || 'w-4 h-4') + '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">' + path + '</svg>';
                },
                proceedToPayPendingInvoice() {
                    if (!this.pendingInvoiceData) return;
                    const inv = this.pendingInvoiceData;
                    this.pendingInvoiceModal = false;
                    this.upgradeModal = false;

                    setTimeout(() => {
                        this.openPaymentModalForInvoice(
                            inv.id,
                            inv.invoice_number,
                            inv.package_name,
                            inv.total,
                            inv.total_formatted
                        );
                    }, 50);
                },
                async sendUpgradeRequest(e) {
                    // Pre-check: If selected package already has an unpaid invoice, trigger the blocking modal immediately!
                    if (this.selectedPackage && this.selectedPackage.pending_invoice) {
                        this.pendingInvoiceData = {
                            id: this.selectedPackage.pending_invoice.id,
                            invoice_number: this.selectedPackage.pending_invoice.invoice_number,
                            package_name: this.selectedPackage.name,
                            total: this.selectedPackage.pending_invoice.total,
                            total_formatted: this.selectedPackage.pending_invoice.total_formatted,
                        };
                        this.pendingInvoiceModal = true;
                        return;
                    }

                    if (this.submitting) return;
                    this.submitting = true;
                    try {
                        const formData = new FormData(e.target);
                        const res = await fetch("{{ route('tenant.subscription.request') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        const data = await res.json();
                        this.submitting = false;

                        if (res.ok && data.success) {
                            this.upgradeModal = false;
                            this.upgradeMessage = '';

                            if (data.is_paid) {
                                if (window.showToast) {
                                    window.showToast('success', data.message);
                                }
                                setTimeout(() => window.location.reload(), 1000);
                                return;
                            }

                            // Seamless in-place gateway selection modal
                            const pkgName = data.package_name || this.selectedPackageName;
                            setTimeout(() => {
                                if (typeof window.openInvoicePaymentModal === 'function' && data.invoice_id) {
                                    window.openInvoicePaymentModal({
                                        invoice_id: data.invoice_id,
                                        invoice_number: data.invoice_number,
                                        package_name: pkgName,
                                        invoice_due: data.invoice_due || data.total,
                                        invoice_due_formatted: data.invoice_due_formatted || null
                                    });
                                } else if (data.pay_url) {
                                    window.location.href = data.pay_url;
                                }
                            }, 50);
                        } else if (data.renewal_limit_exceeded) {
                            this.upgradeModal = false;
                            if (typeof window.openRenewalLimitModal === 'function') {
                                window.openRenewalLimitModal(data);
                            } else if (window.showToast) {
                                window.showToast('error', data.message);
                            }
                        } else if (data.has_pending_invoice) {
                            // Server-side guard: An unpaid invoice already exists for this package
                            this.pendingInvoiceData = {
                                id: data.invoice_id,
                                invoice_number: data.invoice_number,
                                package_name: data.package_name || this.selectedPackageName,
                                total: data.total || data.invoice_due,
                                total_formatted: data.invoice_due_formatted,
                            };
                            this.pendingInvoiceModal = true;
                        } else {
                            const errMsg = data.message || 'خطایی در ثبت ارتقا رخ داد.';
                            if (typeof window.showResellerNotice === 'function') {
                                window.showResellerNotice({title: 'خطا در ارتقا', message: errMsg, type: 'error'});
                            } else if (window.showToast) {
                                window.showToast('error', errMsg);
                            }
                        }
                    } catch (err) {
                        this.submitting = false;
                        const netErr = 'خطا در برقراری ارتباط با سرور.';
                        if (typeof window.showResellerNotice === 'function') {
                            window.showResellerNotice({title: 'خطا', message: netErr, type: 'error'});
                        } else if (window.showToast) {
                            window.showToast('error', netErr);
                        }
                    }
                },
                openPaymentModalForInvoice(invId, invNumber, pkgName, dueAmount, dueFormatted) {
                    if (typeof window.openInvoicePaymentModal === 'function' && invId) {
                        window.openInvoicePaymentModal({
                            invoice_id: invId,
                            invoice_number: invNumber,
                            package_name: pkgName,
                            invoice_due: dueAmount,
                            invoice_due_formatted: dueFormatted
                        });
                    } else {
                        window.location.href = "{{ url('/get-started/pay') }}/" + invId;
                    }
                },
                confirmCancelInvoice(id, number, pkg, amount) {
                    if (window.openCancelInvoiceModal) {
                        window.openCancelInvoiceModal({
                            id: id,
                            invoice_number: number,
                            package_name: pkg || '',
                            invoice_due_formatted: amount || ''
                        });
                        return;
                    }
                    this.cancellingInvoiceId = id;
                    this.cancellingInvoiceNumber = number;
                    this.cancelInvoiceModal = true;
                },
                async submitCancelInvoice() {
                    if (!this.cancellingInvoiceId || this.cancelLoading) return;
                    this.cancelLoading = true;
                    try {
                        const cancelUrl = "{{ route('tenant.subscription.invoices.cancel', ':id') }}".replace(':id', this.cancellingInvoiceId);
                        const res = await fetch(cancelUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await res.json();
                        this.cancelLoading = false;
                        if (res.ok && data.success) {
                            this.cancelInvoiceModal = false;
                            if (window.showToast) {
                                window.showToast('success', data.message);
                            }
                            setTimeout(() => window.location.reload(), 700);
                        } else {
                            const err = data.message || 'خطا در لغو فاکتور.';
                            if (window.showToast) {
                                window.showToast('error', err);
                            }
                        }
                    } catch (err) {
                        this.cancelLoading = false;
                        if (window.showToast) {
                            window.showToast('error', 'خطا در برقراری ارتباط با سرور.');
                        }
                    }
                }
            };
        }
    </script>
