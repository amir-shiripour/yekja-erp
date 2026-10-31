<script>
    function resellerOrderToFa(val) {
        if (val === null || val === undefined || val === '') return '';
        return typeof window.faNum === 'function' ? window.faNum(val) : String(val);
    }

    function resellerOrderFormatNumber(val) {
        if (!val && val !== 0) return '0';
        const num = typeof val === 'number' ? val : Number(String(val).replace(/\D/g, ''));
        return isNaN(num) ? '0' : num.toLocaleString('en-US');
    }

    function resellerOrderCleanNumber(val) {
        if (!val) return 0;
        const clean = String(val).replace(/\D/g, '');
        return clean ? parseInt(clean, 10) : 0;
    }

    function resellerOrderCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('orderManager', () => ({
            activeTab: (window.location.hash ? window.location.hash.substring(1) : '') || 'details',
            editMode: false,
            statusDropdownOpen: false,
            openStatusModal: false,
            statusSubmitting: false,
            pendingStatusId: '{{ $order->status }}',
            pendingStatusName: '{{ $statusLabel }}',
            pendingStatusColor: '{{ $statusColor }}',

            openRenewalModal: false,
            renewalSubmitting: false,
            renewalCycle: '{{ $order->billing_cycle ?? "yearly" }}',
            renewalPriceType: '{{ $order->renewal_price_type ?? "auto" }}',
            renewalPrice: '{{ number_format($isRenewalManual ? ($order->renewal_price ?? 0) : ($order->renewal_price ?: $order->total_amount)) }}',

            openUpgradeModal: false,
            upgradeSubmitting: false,
            selectedUpgradePackageId: null,
            upgradeCalcMode: 'prorated',
            customUpgradePrice: '',
            upgradeNotes: '',
            upgradeIssueInvoice: true,
            packagesData: @json($packagesData),
            currentPackageId: {{ $currentPackageId }},
            remainingDaysCount: {{ (int)$remainingDaysCount }},
            totalCycleDaysCount: {{ (int)$totalCycleDaysCount }},
            currentPackageCyclePrice: {{ (int)$currentPackageCyclePrice }},
            orderTotalAmount: {{ (int)$order->total_amount }},

            get selectedNewPackage() {
                return this.packagesData.find(p => p.id == this.selectedUpgradePackageId) || null;
            },
            get newPackagePrice() {
                return this.selectedNewPackage ? (this.selectedNewPackage.price_for_order_cycle || 0) : 0;
            },
            get oldPackagePrice() {
                return this.currentPackageCyclePrice || this.orderTotalAmount || 0;
            },
            get fullDiff() {
                return Math.max(0, this.newPackagePrice - this.oldPackagePrice);
            },
            get proratedDiff() {
                if (this.totalCycleDaysCount > 0 && this.remainingDaysCount > 0 && this.remainingDaysCount < this.totalCycleDaysCount) {
                    return Math.max(0, Math.round(this.fullDiff * (this.remainingDaysCount / this.totalCycleDaysCount)));
                }
                return this.fullDiff;
            },
            get finalUpgradeAmount() {
                if (this.upgradeCalcMode === 'full') {
                    return this.fullDiff;
                }
                if (this.upgradeCalcMode === 'custom') {
                    return resellerOrderCleanNumber(this.customUpgradePrice);
                }
                return this.remainingDaysCount > 0 ? this.proratedDiff : this.fullDiff;
            },
            get finalUpgradeAmountFormatted() {
                return this.toFa(this.formatNumber(this.finalUpgradeAmount));
            },
            moduleTitlesMap: @json($moduleTitlesMap ?? []),
            get newModulesAdded() {
                if (!this.selectedNewPackage) return [];
                const currentPkg = this.packagesData.find(p => p.id == this.currentPackageId);
                const currentMods = (currentPkg && Array.isArray(currentPkg.modules)) ? currentPkg.modules : [];
                const targetMods = Array.isArray(this.selectedNewPackage.modules) ? this.selectedNewPackage.modules : [];
                const diff = targetMods.filter(m => !currentMods.includes(m));
                return diff.map(slug => this.moduleTitlesMap[slug] || slug);
            },
            formatCustomPrice(e) {
                let num = resellerOrderCleanNumber(e.target.value);
                this.customUpgradePrice = num ? resellerOrderFormatNumber(num) : '';
            },
            isPackageCheaper(pkg) {
                if (!pkg) return false;
                return (Number(pkg.price_for_order_cycle) || 0) < this.oldPackagePrice;
            },
            canSelectPackage(pkg) {
                if (!pkg) return false;
                if (pkg.id == this.currentPackageId) return false;
                if (this.isPackageCheaper(pkg)) return false;
                return true;
            },
            selectUpgradePackage(id) {
                const pkg = this.packagesData.find(p => p.id == id);
                if (!this.canSelectPackage(pkg)) return;
                this.selectedUpgradePackageId = id;
            },

            cyclePrices: @json($cyclePrices),
            cycleNextDates: @json($cycleNextDates),

            formatNumber(val) {
                return resellerOrderFormatNumber(val);
            },
            toFa(val) {
                return resellerOrderToFa(val);
            },
            get renewalPriceFormatted() {
                let p = this.cyclePrices[this.renewalCycle] ?? {{ $calculatedRenewalPrice }};
                return this.toFa(this.formatNumber(p));
            },
            get nextRenewalDateDisplay() {
                let d = this.cycleNextDates[this.renewalCycle] ?? '{{ $nextRenewalDateCalculated }}';
                return this.toFa(d);
            },

            promptStatusChange(id, name, color, isCurrent) {
                if (isCurrent) return;
                this.pendingStatusId = id;
                this.pendingStatusName = name;
                this.pendingStatusColor = color;
                this.openStatusModal = true;
            },

            init() {
                this.$watch('editMode', value => {
                    if (value) {
                        this.$nextTick(() => {
                            if (typeof jalaliDatepicker !== 'undefined') {
                                jalaliDatepicker.startWatch();
                            }
                        });
                    }
                });

                        this.$watch('renewalPriceType', value => {
                            if (value === 'auto') {
                                this.renewalPrice = '{{ number_format($order->total_amount ?? 0) }}';
                            } else {
                                this.renewalPrice = '{{ number_format($order->renewal_price ?: $order->total_amount) }}';
                            }
                        });

                        this.$watch('activeTab', tab => {
                            if (window.history && window.history.replaceState) {
                                window.history.replaceState(null, null, '#' + tab);
                            }
                        });
                    },
            formatRenewalPrice(e) {
                if (this.renewalPriceType === 'auto') return;
                let num = resellerOrderCleanNumber(e.target.value);
                this.renewalPrice = num ? resellerOrderFormatNumber(num) : '';
            }
        }));
    });

    function addItemsManager() {
        return {
            selectedItems: [],
            issueInvoice: true,
            selectedForRemoval: [],
            itemsToRemove: [],
            removeModalOpen: false,
            removing: false,

            isSelected(slug) {
                return this.selectedItems.some(i => i.slug === slug);
            },

            isSelectedForRemoval(slug) {
                return this.selectedForRemoval.some(i => i.slug === slug);
            },

            toggleRemoveSelection(type, slug, title) {
                const idx = this.selectedForRemoval.findIndex(i => i.slug === slug);
                if (idx !== -1) {
                    this.selectedForRemoval.splice(idx, 1);
                } else {
                    this.selectedForRemoval.push({type, slug, title});
                }
            },

            clearRemovalSelection() {
                this.selectedForRemoval = [];
            },

            promptBulkRemove() {
                if (!this.selectedForRemoval.length) return;
                this.itemsToRemove = [...this.selectedForRemoval];
                this.removeModalOpen = true;
            },

            toggleModule(mod) {
                if (mod.already_in_order) return;
                const idx = this.selectedItems.findIndex(i => i.slug === mod.slug);
                if (idx !== -1) {
                    // deselect module and any of its features
                    this.selectedItems = this.selectedItems.filter(i => i.slug !== mod.slug && i.parentSlug !== mod.slug);
                } else {
                    this.selectedItems.push({
                        slug: mod.slug,
                        title: mod.title,
                        type: 'module',
                        price: mod.cycle_price || 0,
                        parentSlug: null,
                    });
                }
            },

            toggleFeature(feat, parentSlug) {
                if (feat.already_in_order) return;
                const idx = this.selectedItems.findIndex(i => i.slug === feat.key);
                if (idx !== -1) {
                    this.selectedItems.splice(idx, 1);
                } else {
                    this.selectedItems.push({
                        slug: feat.key,
                        title: feat.title,
                        type: 'feature',
                        price: feat.cycle_price || 0,
                        parentSlug: parentSlug,
                    });
                }
            },

            removeItem(slug) {
                this.selectedItems = this.selectedItems.filter(i => i.slug !== slug && i.parentSlug !== slug);
            },

            promptRemoveActiveItem(type, slug, title) {
                this.itemsToRemove = [{type, slug, title}];
                this.removeModalOpen = true;
            },

            async confirmRemoveActiveItems(removeUrl) {
                if (!this.itemsToRemove.length || this.removing) return;
                this.removing = true;
                try {
                    const csrfToken = resellerOrderCsrfToken();
                    const {data} = await axios.post(removeUrl, {
                        items: this.itemsToRemove.map(i => ({
                            type: i.type,
                            slug: i.slug,
                            title: i.title || null,
                        })),
                        type: this.itemsToRemove[0]?.type,
                        slug: this.itemsToRemove[0]?.slug,
                        title: this.itemsToRemove[0]?.title || null,
                    }, {
                        headers: {'X-CSRF-TOKEN': csrfToken}
                    });
                    if (data.success) {
                        window.location.href = data.redirect_url;
                    }
                } catch (e) {
                    const msg = e.response?.data?.message || 'خطا در حذف اقلام. لطفاً دوباره تلاش کنید.';
                    if (typeof window.showResellerNotice === 'function') {
                        window.showResellerNotice({ title: 'خطا', message: msg, type: 'error' });
                    } else if (typeof window.showToast === 'function') {
                        window.showToast('error', msg);
                    }
                    this.removing = false;
                }
            },

                    get total() {
                        return this.selectedItems.reduce((s, i) => s + (i.price || 0), 0);
                    },

                    formatTotal() {
                        return resellerOrderToFa(resellerOrderFormatNumber(this.total));
                    },

                    toFa(val) {
                        return resellerOrderToFa(val);
                    },

                    submitting: false,

                    async submitItems(url) {
                        if (!this.selectedItems.length || this.submitting) return;
                        this.submitting = true;
                        try {
                            const csrfToken = resellerOrderCsrfToken();
                            const {data} = await axios.post(url, {
                                items: this.selectedItems.map(i => ({
                                    type: i.type,
                                    slug: i.slug,
                                    title: i.title,
                                    price: i.price,
                                })),
                                issue_invoice: this.issueInvoice,
                            }, {
                                headers: {'X-CSRF-TOKEN': csrfToken}
                            });
                            if (data.success) {
                                window.location.href = data.redirect_url;
                            }
                        } catch (e) {
                            const msg = e.response?.data?.message || 'خطا در ثبت اطلاعات. لطفاً دوباره تلاش کنید.';
                            if (typeof window.showResellerNotice === 'function') {
                                window.showResellerNotice({ title: 'خطا', message: msg, type: 'error' });
                            } else if (typeof window.showToast === 'function') {
                                window.showToast('error', msg);
                            }
                            this.submitting = false;
                        }
                    }
                };
            }
</script>
