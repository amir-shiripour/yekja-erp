<div id="subaccount-delete-modal"
     x-data="subAccountDeleteModalComponent()"
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
             class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 text-center space-y-6 my-8"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

            {{-- CASE 1: SUB-ACCOUNT HAS CONNECTED TENANTS (HIGH RISK WARNING + HOLD TO CONFIRM) --}}
            <template x-if="subAccount && subAccount.tenants_count > 0">
                <div class="space-y-5 text-start">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-13 h-13 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200/60 dark:border-rose-900/40 shadow-inner">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="space-y-1 min-w-0 flex-1">
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                <span>{{ __('resellerhub::messages.subaccounts.delete_warning_with_tenants_badge') }}</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white leading-tight">
                                {{ __('resellerhub::messages.subaccounts.delete_title_with_tenants') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-bold text-gray-900 dark:text-white" x-text="subAccountLabel()"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Recommendation Callout: Advise deleting tenants first --}}
                    <div
                        class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 text-xs text-amber-900 dark:text-amber-200 leading-relaxed space-y-2">
                        <div class="font-black flex items-center gap-1.5 text-amber-800 dark:text-amber-300">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ __('resellerhub::messages.subaccounts.delete_recommendation_title') }}</span>
                        </div>
                        <p class="leading-relaxed" x-html="recommendationText()"></p>
                    </div>

                    {{-- Connected Tenants List --}}
                    <div class="space-y-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300"
                              x-text="connectedTenantsTitle()"></span>
                        <div
                            class="max-h-36 overflow-y-auto rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/50 divide-y divide-gray-100 dark:divide-gray-700/60 p-1.5 text-xs">
                            <template x-for="t in (subAccount?.tenants || [])" :key="t.id">
                                <div class="px-3 py-2 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-white truncate"
                                             x-text="t.name || t.subdomain"></div>
                                        <div class="text-[11px] text-gray-400 dir-ltr text-start font-mono truncate"
                                             x-text="t.subdomain"></div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40 shrink-0">
                                        {{ __('resellerhub::messages.common.delete') }}
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Destructive Cascade Warning Callout --}}
                    <div class="p-3.5 rounded-2xl bg-rose-50/90 dark:bg-rose-950/50 border border-rose-200/80 dark:border-rose-900/60 text-xs text-rose-900 dark:text-rose-200 leading-relaxed font-medium flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ __('resellerhub::messages.subaccounts.delete_warning_body') }}</span>
                    </div>

                    {{-- HOLD TO CONFIRM BUTTON (inspired by dqnamo) --}}
                    <div class="space-y-2 pt-2">
                        <div
                            class="relative w-full h-14 select-none touch-none rounded-2xl overflow-hidden bg-rose-950/40 border-2 border-rose-600/60 dark:border-rose-500/60 shadow-inner group transition-all"
                            :class="{ 'scale-[0.98] ring-2 ring-rose-500/70': isHolding }">

                            {{-- Smooth Dynamic Progress Fill Layer --}}
                            <div
                                class="absolute inset-y-0 start-0 bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 pointer-events-none hold-progress-bar"
                                :style="'width: ' + holdProgress + '%;' + (isHolding ? 'transition: none;' : 'transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);')"></div>

                            {{-- Shimmer Effect during Hold --}}
                            <template x-if="isHolding">
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse pointer-events-none"></div>
                            </template>

                            {{-- Interactive Touch / Pointer Area --}}
                            <button type="button"
                                    id="btn-subaccount-hold-delete"
                                    @pointerdown="startHold()"
                                    @pointerup="cancelHold()"
                                    @pointerleave="cancelHold()"
                                    @contextmenu.prevent
                                    class="relative z-10 w-full h-full flex items-center justify-center gap-2.5 px-4 font-black text-xs sm:text-sm text-white cursor-pointer focus:outline-none">
                                <template x-if="!loading && !confirmed && !isHolding">
                                    <span class="flex items-center gap-2 drop-shadow-sm">
                                        <svg class="w-5 h-5 text-rose-300 group-hover:scale-110 transition-transform"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>{{ __('resellerhub::messages.subaccounts.hold_to_confirm') }}</span>
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
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>{{ __('resellerhub::messages.subaccounts.deleting_in_progress') }}</span>
                                    </span>
                                </template>
                            </button>
                        </div>

                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium text-center">
                            {{ __('resellerhub::messages.subaccounts.hold_instruction') }}
                        </p>
                    </div>

                    {{-- Cancel Action --}}
                    <div class="pt-1">
                        <button type="button"
                                @click="closeModal()"
                                :disabled="loading"
                                class="w-full py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                            {{ __('resellerhub::messages.subaccounts.cancel_btn') }}
                        </button>
                    </div>
                </div>
            </template>

            {{-- CASE 2: SUB-ACCOUNT HAS NO CONNECTED TENANTS (SAFE DIRECT CONFIRMATION) --}}
            <template x-if="subAccount && subAccount.tenants_count === 0">
                <div class="space-y-5">
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-inner border border-rose-100 dark:border-rose-900/40">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>

                    <div class="space-y-2 text-center">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                            {{ __('resellerhub::messages.subaccounts.delete_title') }}
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal"
                           x-html="withoutTenantsDesc()"></p>
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium">
                            {{ __('resellerhub::messages.subaccounts.delete_without_tenants_sub') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button"
                                @click="closeModal()"
                                :disabled="loading"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                            {{ __('resellerhub::messages.common.cancel') }}
                        </button>
                        <button type="button"
                                @click="executeDelete(false)"
                                :disabled="loading"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-lg shadow-rose-600/20 transition cursor-pointer disabled:opacity-50">
                            <template x-if="!loading">
                                <span>{{ __('resellerhub::messages.subaccounts.delete_action_btn') }}</span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>...</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function subAccountDeleteModalComponent() {
        return {
            open: false,
            loading: false,
            subAccount: null,
            holdProgress: 0,
            isHolding: false,
            holdDuration: 2500, // 2.5 seconds
            holdStartTime: null,
            confirmed: false,
            animFrame: null,

            i18n: {
                subAccountLabel: @json(__('resellerhub::messages.subaccounts.delete_subaccount_label', ['name' => ':name'])),
                recommendationBody: @json(__('resellerhub::messages.subaccounts.delete_recommendation_body', ['count' => ':count'])),
                connectedTenantsTitle: @json(__('resellerhub::messages.subaccounts.connected_tenants_list_title', ['count' => ':count'])),
                withoutTenantsDesc: @json(__('resellerhub::messages.subaccounts.delete_without_tenants_desc', ['name' => ':name'])),
                holdingProgress: @json(__('resellerhub::messages.subaccounts.holding_progress', ['percent' => ':percent'])),
                serverError: @json(__('resellerhub::messages.common.server_connection_error')),
                defaultError: @json(__('resellerhub::messages.common.error')),
            },

            init() {
                window.openSubAccountDeleteModal = (data) => {
                    this.subAccount = data;
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
                            try {
                                navigator.vibrate([40, 80, 40]);
                            } catch (e) {
                            }
                        }
                        this.executeDelete(true);
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

            executeDelete(cascade = false) {
                if (!this.subAccount || !this.subAccount.delete_url) return;
                this.loading = true;

                fetch(this.subAccount.delete_url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        force_delete_tenants: cascade ? 1 : 0,
                        cascade_tenants: cascade ? 1 : 0
                    })
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
                            setTimeout(() => window.location.reload(), 600);
                        } else if (res.status === 409 && data.has_connected_tenants) {
                            this.subAccount.tenants_count = data.connected_tenants_count;
                            this.subAccount.tenants = data.connected_tenants;
                            this.loading = false;
                            this.confirmed = false;
                            this.holdProgress = 0;
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

            subAccountLabel() {
                const name = this.subAccount?.name || '';
                return this.i18n.subAccountLabel.replace(':name', name);
            },

            recommendationText() {
                const count = this.toPersianDigits(this.subAccount?.tenants_count || 0);
                return this.i18n.recommendationBody.replace(':count', '<strong class="text-rose-600 dark:text-rose-400 font-black tabular-nums">' + count + '</strong>');
            },

            connectedTenantsTitle() {
                const count = this.toPersianDigits(this.subAccount?.tenants_count || (this.subAccount?.tenants ? this.subAccount.tenants.length : 0));
                return this.i18n.connectedTenantsTitle.replace(':count', count);
            },

            withoutTenantsDesc() {
                const name = this.subAccount?.name || '';
                return this.i18n.withoutTenantsDesc.replace(':name', name);
            },

            holdingProgressText() {
                const pct = this.toPersianDigits(Math.round(this.holdProgress));
                return this.i18n.holdingProgress.replace(':percent', pct);
            }
        };
    }
</script>
