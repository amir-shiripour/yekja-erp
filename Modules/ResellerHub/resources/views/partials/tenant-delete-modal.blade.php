<div id="tenant-delete-modal"
     x-data="tenantDeleteModalComponent()"
     x-show="open"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     role="dialog"
     aria-modal="true"
     dir="rtl"
     @keydown.escape.window="closeModal()">

    <div class="flex items-center justify-center min-h-screen w-full px-4 py-8 text-center sm:p-0">
        {{-- Fullscreen Backdrop with Blur --}}
        <div class="fixed inset-0 w-full h-full bg-gray-950/80 dark:bg-black/90 backdrop-blur-md transition-opacity"
             @click="closeModal()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Dialog Panel --}}
        <div @click.stop
             class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 text-center space-y-5 my-8"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

            <div class="space-y-5 text-start">
                <div class="flex items-start gap-4">
                    <div class="w-13 h-13 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200/60 dark:border-rose-900/40 shadow-inner">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                            <span>{{ __('resellerhub::messages.tenants.delete_badge') }}</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white leading-tight">
                            {{ __('resellerhub::messages.tenants.delete_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-bold text-gray-900 dark:text-white" x-text="tenantLabel()"></span>
                        </p>
                    </div>
                </div>

                {{-- Tenant Details Box --}}
                <div class="p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('resellerhub::messages.common.status') }}:</span>
                        <span class="font-bold font-mono" x-text="tenant?.status || ''"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('resellerhub::messages.tenants.subdomain') }}:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr font-mono" x-text="tenant?.subdomain || ''"></span>
                    </div>
                </div>

                {{-- Destructive Warning --}}
                <div class="p-3.5 rounded-2xl bg-rose-50/90 dark:bg-rose-950/50 border border-rose-200/80 dark:border-rose-900/60 text-xs text-rose-900 dark:text-rose-200 leading-relaxed font-medium flex items-start gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ __('resellerhub::messages.tenants.delete_warning_body') }}</span>
                </div>

                {{-- HOLD TO CONFIRM BUTTON --}}
                <div class="space-y-2 pt-2">
                    <div class="relative w-full h-14 select-none touch-none rounded-2xl overflow-hidden bg-rose-950/40 border-2 border-rose-600/60 dark:border-rose-500/60 shadow-inner group transition-all"
                         :class="{ 'scale-[0.98] ring-2 ring-rose-500/70': isHolding }">

                        {{-- Smooth Dynamic Progress Fill Layer --}}
                        <div class="absolute inset-y-0 start-0 bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 pointer-events-none hold-progress-bar"
                             :style="'width: ' + holdProgress + '%;' + (isHolding ? 'transition: none;' : 'transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);')"></div>

                        {{-- Shimmer Effect during Hold --}}
                        <template x-if="isHolding">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse pointer-events-none"></div>
                        </template>

                        {{-- Interactive Touch / Pointer Area --}}
                        <button type="button"
                                id="btn-tenant-hold-delete"
                                @pointerdown="startHold()"
                                @pointerup="cancelHold()"
                                @pointerleave="cancelHold()"
                                @contextmenu.prevent
                                class="relative z-10 w-full h-full flex items-center justify-center gap-2.5 px-4 font-black text-xs sm:text-sm text-white cursor-pointer focus:outline-none">
                            <template x-if="!loading && !confirmed && !isHolding">
                                <span class="flex items-center gap-2 drop-shadow-sm">
                                    <svg class="w-5 h-5 text-rose-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>{{ __('resellerhub::messages.tenants.hold_to_confirm') }}</span>
                                </span>
                            </template>

                            <template x-if="!loading && isHolding && !confirmed">
                                <span class="flex items-center gap-2 font-black text-white drop-shadow">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                                    <span x-text="holdingProgressText()"></span>
                                </span>
                            </template>

                            <template x-if="loading || confirmed">
                                <span class="flex items-center gap-2 font-bold text-white drop-shadow">
                                    <svg class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>{{ __('resellerhub::messages.tenants.deleting_in_progress') }}</span>
                                </span>
                            </template>
                        </button>
                    </div>

                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium text-center">
                        {{ __('resellerhub::messages.tenants.hold_instruction') }}
                    </p>
                </div>

                {{-- Cancel Action --}}
                <div class="pt-1">
                    <button type="button"
                            @click="closeModal()"
                            :disabled="loading"
                            class="w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                        {{ __('resellerhub::messages.common.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function tenantDeleteModalComponent() {
        return {
            open: false,
            loading: false,
            tenant: null,
            holdProgress: 0,
            isHolding: false,
            holdDuration: 2000, // 2 seconds
            holdStartTime: null,
            confirmed: false,
            animFrame: null,

            i18n: {
                systemLabel: @json(__('resellerhub::messages.tenants.delete_system_label', ['name' => ':name', 'subdomain' => ':subdomain'])),
                holdingProgress: @json(__('resellerhub::messages.tenants.holding_progress', ['percent' => ':percent'])),
                serverError: @json(__('resellerhub::messages.common.server_connection_error')),
                defaultError: @json(__('resellerhub::messages.common.error')),
            },

            init() {
                window.openTenantDeleteModal = (data) => {
                    this.tenant = data;
                    this.holdProgress = 0;
                    this.isHolding = false;
                    this.confirmed = false;
                    this.loading = false;
                    this.open = true;
                };
            },

            closeModal() {
                if (this.loading) return;
                this.cancelHold();
                this.open = false;
            },

            startHold() {
                if (this.loading || this.confirmed || this.isHolding) return;
                this.isHolding = true;
                this.holdStartTime = Date.now();

                const loop = () => {
                    if (!this.isHolding) return;
                    const elapsed = Date.now() - this.holdStartTime;
                    this.holdProgress = Math.min(100, (elapsed / this.holdDuration) * 100);

                    if (this.holdProgress >= 100) {
                        this.confirmed = true;
                        this.isHolding = false;
                        if (navigator.vibrate) {
                            try { navigator.vibrate([40, 80, 40]); } catch(e) {}
                        }
                        this.executeDelete();
                    } else {
                        this.animFrame = requestAnimationFrame(loop);
                    }
                };

                this.animFrame = requestAnimationFrame(loop);
            },

            cancelHold() {
                if (this.confirmed || this.loading) return;
                this.isHolding = false;
                if (this.animFrame) {
                    cancelAnimationFrame(this.animFrame);
                    this.animFrame = null;
                }
                this.holdProgress = 0;
            },

            executeDelete() {
                if (!this.tenant || !this.tenant.delete_url) return;
                this.loading = true;

                fetch(this.tenant.delete_url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success) {
                        this.closeModal();
                        if (window.showToast) {
                            window.showToast(data.message, 'success');
                        } else {
                            alert(data.message);
                        }
                        setTimeout(() => {
                            if (window.location.pathname.includes('/tenants/')) {
                                window.location.href = '{{ route('reseller.tenants.index') }}';
                            } else {
                                window.location.reload();
                            }
                        }, 600);
                    } else {
                        this.loading = false;
                        this.confirmed = false;
                        this.holdProgress = 0;
                        alert(data.message || this.i18n.defaultError);
                    }
                })
                .catch((err) => {
                    this.loading = false;
                    this.confirmed = false;
                    this.holdProgress = 0;
                    alert(this.i18n.serverError + ' (' + err.message + ')');
                });
            },

            toPersianDigits(num) {
                if (num === null || num === undefined) return '';
                return String(num).replace(/[0-9]/g, (w) => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'][+w]);
            },

            tenantLabel() {
                const name = this.tenant?.name || '';
                const sub = this.tenant?.subdomain || '';
                return this.i18n.systemLabel.replace(':name', name).replace(':subdomain', sub);
            },

            holdingProgressText() {
                const pct = this.toPersianDigits(Math.round(this.holdProgress));
                return this.i18n.holdingProgress.replace(':percent', pct);
            }
        };
    }
</script>
