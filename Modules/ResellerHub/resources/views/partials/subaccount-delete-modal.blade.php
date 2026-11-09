{{-- SubAccount Deletion Confirmation Modal --}}
<div id="subaccount-delete-modal"
     x-data="subAccountDeleteModalComponent()"
     x-show="open"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="subaccount-delete-title"
     dir="rtl"
     @keydown.escape.window="closeModal()">

    <div class="flex items-center justify-center min-h-screen w-full px-4 py-8 text-center sm:p-6">
        {{-- Backdrop --}}
        <div class="fixed inset-0 w-full h-full bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-opacity"
             @click="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Dialog Panel (dqnamo style enhanced) --}}
        <div @click.stop
             class="relative w-full max-w-lg origin-center rounded-2xl border border-gray-200/90 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 sm:p-7 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.06),0_20px_25px_-5px_rgba(0,0,0,0.1),0_10px_10px_-5px_rgba(0,0,0,0.04)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.4),0_25px_50px_-12px_rgba(0,0,0,0.7)] my-8 text-start space-y-5 z-10 transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- HEADER: Icon + Title & Warning Badge --}}
            <div class="flex items-start gap-4">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="subaccount-delete-title" class="font-bold text-gray-900 dark:text-gray-100 text-base sm:text-lg leading-snug">
                            <template x-if="subAccount && subAccount.tenants_count > 0">
                                <span>{{ __('resellerhub::messages.subaccounts.delete_title_with_tenants') }}</span>
                            </template>
                            <template x-if="!subAccount || subAccount.tenants_count === 0">
                                <span>{{ __('resellerhub::messages.subaccounts.delete_title') }}</span>
                            </template>
                        </h2>
                        <template x-if="subAccount && subAccount.tenants_count > 0">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-900/80 shrink-0">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <span x-text="toPersianDigits(subAccount?.tenants_count) + ' سامانه'"></span>
                            </span>
                        </template>
                    </div>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-normal"
                       x-text="subAccountLabel()"></p>
                </div>
            </div>

            {{-- CASE 1: CONNECTED TENANTS DETAILS --}}
            <template x-if="subAccount && subAccount.tenants_count > 0">
                <div class="space-y-3.5 pt-1">
                    {{-- Recommendation Callout --}}
                    <div
                        class="p-4 rounded-xl bg-amber-50/90 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/40 text-xs sm:text-[13px] text-amber-900 dark:text-amber-200 leading-relaxed flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="space-y-1">
                            <span
                                class="font-bold block text-amber-950 dark:text-amber-100 text-xs sm:text-sm">{{ __('resellerhub::messages.subaccounts.delete_recommendation_title') }}</span>
                            <div class="text-amber-800 dark:text-amber-300 leading-relaxed"
                                 x-html="recommendationText()"></div>
                        </div>
                    </div>

                    {{-- Connected Tenants Compact List --}}
                    <div class="space-y-2">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 block"
                              x-text="connectedTenantsTitle()"></span>
                        <div
                            class="max-h-36 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 divide-y divide-gray-100 dark:divide-gray-700/50 p-1.5 text-xs">
                            <template x-for="t in (subAccount?.tenants || [])" :key="t.id">
                                <div
                                    class="px-3 py-2 rounded-lg flex items-center justify-between gap-3 hover:bg-white dark:hover:bg-gray-700/60 transition-colors">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200 truncate"
                                          x-text="t.name || t.subdomain"></span>
                                    <span
                                        class="font-mono text-xs text-gray-500 dark:text-gray-400 dir-ltr shrink-0 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md"
                                        x-text="t.subdomain"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Cascade Hard-Delete Alert --}}
                    <div
                        class="p-4 rounded-xl bg-rose-50/90 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/40 text-xs sm:text-[13px] text-rose-900 dark:text-rose-200 leading-relaxed font-medium flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="leading-relaxed">
                            <span>{{ __('resellerhub::messages.subaccounts.delete_warning_body') }}</span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- CASE 2: NO CONNECTED TENANTS --}}
            <template x-if="subAccount && subAccount.tenants_count === 0">
                <div
                    class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed space-y-1.5">
                    <p class="font-medium text-gray-900 dark:text-gray-100" x-html="withoutTenantsDesc()"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-normal">{{ __('resellerhub::messages.subaccounts.delete_without_tenants_sub') }}</p>
                </div>
            </template>

            {{-- ACTION AREA: STAGE IDLE (CANCEL + HOLD TO CONFIRM BUTTONS) --}}
            <template x-if="stage === 'idle'">
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 grid h-11 grid-cols-2 gap-3">
                    {{-- Cancel Button --}}
                    <button type="button"
                            id="btn-subaccount-cancel"
                            @click="closeModal()"
                            class="h-11 cursor-pointer rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 font-semibold text-gray-700 dark:text-gray-300 text-xs sm:text-sm transition-all hover:bg-gray-50 dark:hover:bg-gray-700 active:scale-[0.98] flex items-center justify-center">
                        {{ __('resellerhub::messages.common.cancel') }}
                    </button>

                    {{-- Hold-To-Confirm Button (dqnamo dual-layer clip-path) --}}
                    <button type="button"
                            id="btn-subaccount-hold-delete"
                            @pointerdown="startHold($event)"
                            @pointermove="handlePointerMove($event)"
                            @pointerup="cancelHold()"
                            @pointercancel="cancelHold()"
                            @contextmenu.prevent
                            class="relative isolate inline-flex h-11 touch-none cursor-pointer select-none items-center justify-center overflow-hidden rounded-xl border border-rose-300 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 px-4 font-semibold text-rose-700 dark:text-rose-300 text-xs sm:text-sm outline-none transition-all active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-rose-500 w-full"
                            :class="{ 'border-rose-600 bg-rose-600 text-white': isHolding && holdProgress >= 100 }">

                        {{-- Base Layer (Visible Text) --}}
                        <span class="relative flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span
                                x-text="isHolding ? holdingProgressText() : '{{ __('resellerhub::messages.subaccounts.hold_to_confirm') }}'"></span>
                        </span>

                        {{-- Fill Layer (revealed via dynamic clip-path from right in RTL) --}}
                        <span aria-hidden="true"
                              class="absolute inset-0 flex items-center justify-center gap-2 bg-rose-600 px-4 text-white font-semibold text-xs sm:text-sm pointer-events-none hold-progress-bar"
                              :style="overlayClipStyle">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span x-text="holdingProgressText()"></span>
                        </span>
                    </button>
                </div>
            </template>

            {{-- ACTION AREA: STAGE UNDO (dqnamo UndoNotice with linear draining clip-path) --}}
            <template x-if="stage === 'undo'">
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div
                        class="flex h-12 items-center justify-between gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/90 dark:bg-gray-800 p-2 px-3.5"
                        role="status">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p class="truncate font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm"
                               x-text="undoNoticeText()"></p>
                        </div>

                        {{-- Undo Button with animated clip-path wipe countdown --}}
                        <button type="button"
                                id="btn-subaccount-undo"
                                @click="triggerUndo()"
                                class="relative isolate flex h-8.5 min-w-24 cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 font-semibold text-gray-800 dark:text-gray-100 text-xs sm:text-sm outline-none transition-transform duration-150 active:scale-[0.97] shrink-0 shadow-xs">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h10a5 5 0 0 1 5 5v2m0 0l-3-3m3 3l3-3"/>
                                </svg>
                                <span>{{ __('resellerhub::messages.subaccounts.undo') }}</span>
                            </span>

                            {{-- Draining overlay layer showing remaining countdown visually --}}
                            <span aria-hidden="true"
                                  class="absolute inset-0 flex items-center justify-center gap-1.5 bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 px-3 text-xs sm:text-sm font-semibold pointer-events-none"
                                  :style="undoClipStyle">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h10a5 5 0 0 1 5 5v2m0 0l-3-3m3 3l3-3"/>
                                </svg>
                                <span>{{ __('resellerhub::messages.subaccounts.undo') }}</span>
                            </span>
                        </button>
                    </div>
                </div>
            </template>

            {{-- ACTION AREA: STAGE EXECUTING (SERVER IN PROGRESS) --}}
            <template x-if="stage === 'executing'">
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div
                        class="flex h-11 items-center justify-center gap-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs sm:text-sm font-semibold">
                        <svg class="w-4 h-4 animate-spin text-rose-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ __('resellerhub::messages.subaccounts.deleting_in_progress') }}</span>
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
            stage: 'idle', // 'idle' | 'undo' | 'executing'
            subAccount: null,
            holdProgress: 0,
            isHolding: false,
            holdDuration: 1800, // 1.8 seconds (dqnamo style)
            holdStartTime: null,
            animFrame: null,

            // Undo countdown state
            undoDuration: 5000, // 5 seconds
            undoStartTime: null,
            undoProgress: 100, // 100% -> 0%
            undoTimer: null,
            undoAnimFrame: null,

            i18n: {
                subAccountLabel: @json(__('resellerhub::messages.subaccounts.delete_subaccount_label', ['name' => ':name'])),
                recommendationBody: @json(__('resellerhub::messages.subaccounts.delete_recommendation_body', ['count' => ':count'])),
                connectedTenantsTitle: @json(__('resellerhub::messages.subaccounts.connected_tenants_list_title', ['count' => ':count'])),
                withoutTenantsDesc: @json(__('resellerhub::messages.subaccounts.delete_without_tenants_desc', ['name' => ':name'])),
                holdingProgress: @json(__('resellerhub::messages.subaccounts.holding_progress', ['percent' => ':percent'])),
                undoNotice: @json(__('resellerhub::messages.subaccounts.undo_notice', ['seconds' => ':seconds'])),
                serverError: @json(__('resellerhub::messages.common.server_connection_error')),
                defaultError: @json(__('resellerhub::messages.common.error')),
            },

            init() {
                window.openSubAccountDeleteModal = (data) => {
                    this.subAccount = data;
                    this.resetAll();
                    this.open = true;
                };
            },

            resetAll() {
                this.stage = 'idle';
                this.isHolding = false;
                this.holdProgress = 0;
                this.undoProgress = 100;
                if (this.animFrame) cancelAnimationFrame(this.animFrame);
                if (this.undoAnimFrame) cancelAnimationFrame(this.undoAnimFrame);
                if (this.undoTimer) clearTimeout(this.undoTimer);
                this.animFrame = null;
                this.undoAnimFrame = null;
                this.undoTimer = null;
            },

            closeModal() {
                if (this.stage === 'executing') return;
                this.resetAll();
                this.open = false;
            },

            // --- HOLD TO CONFIRM LOGIC ---
            startHold(event) {
                if (this.stage !== 'idle' || this.isHolding) return;
                this.isHolding = true;
                this.holdStartTime = Date.now();

                const loop = () => {
                    if (!this.isHolding) return;
                    const elapsed = Date.now() - this.holdStartTime;
                    this.holdProgress = Math.min(100, (elapsed / this.holdDuration) * 100);

                    if (this.holdProgress >= 100) {
                        this.isHolding = false;
                        if (navigator.vibrate) {
                            try {
                                navigator.vibrate([30, 50, 30]);
                            } catch (e) {
                            }
                        }
                        // Transition to UNDO stage!
                        this.enterUndoStage();
                    } else {
                        this.animFrame = requestAnimationFrame(loop);
                    }
                };

                this.animFrame = requestAnimationFrame(loop);
            },

            cancelHold() {
                if (this.stage !== 'idle') return;
                this.isHolding = false;
                if (this.animFrame) {
                    cancelAnimationFrame(this.animFrame);
                    this.animFrame = null;
                }
                this.holdProgress = 0;
            },

            handlePointerMove(event) {
                if (!this.isHolding) return;
                const rect = event.currentTarget.getBoundingClientRect();
                const pad = 10;
                if (event.clientX < rect.left - pad || event.clientX > rect.right + pad ||
                    event.clientY < rect.top - pad || event.clientY > rect.bottom + pad) {
                    this.cancelHold();
                }
            },

            // --- UNDO STAGE LOGIC ---
            enterUndoStage() {
                this.stage = 'undo';
                this.undoStartTime = Date.now();
                this.undoProgress = 100;

                const loopUndo = () => {
                    if (this.stage !== 'undo') return;
                    const elapsed = Date.now() - this.undoStartTime;
                    const remaining = Math.max(0, this.undoDuration - elapsed);
                    this.undoProgress = (remaining / this.undoDuration) * 100;

                    if (remaining <= 0) {
                        // Undo expired -> execute real deletion!
                        this.executeFinalDelete();
                    } else {
                        this.undoAnimFrame = requestAnimationFrame(loopUndo);
                    }
                };

                this.undoAnimFrame = requestAnimationFrame(loopUndo);
            },

            triggerUndo() {
                // User clicked UNDO! Revert immediately back to idle!
                this.resetAll();
                if (window.showToast) {
                    window.showToast(@json(__('resellerhub::messages.subaccounts.undo_cancelled')), 'info');
                }
            },

            executeFinalDelete() {
                this.stage = 'executing';
                const cascade = (this.subAccount?.tenants_count > 0);

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
                            setTimeout(() => window.location.reload(), 500);
                        } else if (res.status === 409 && data.has_connected_tenants) {
                            this.subAccount.tenants_count = data.connected_tenants_count;
                            this.subAccount.tenants = data.connected_tenants;
                            this.stage = 'idle';
                            this.holdProgress = 0;
                        } else {
                            this.stage = 'idle';
                            this.holdProgress = 0;
                            alert(data.message || this.i18n.defaultError);
                        }
                    })
                    .catch((err) => {
                        this.stage = 'idle';
                        this.holdProgress = 0;
                        alert(this.i18n.serverError + ' (' + err.message + ')');
                    });
            },

            // --- COMPUTED STYLES & TEXTS ---
            get overlayClipStyle() {
                // In RTL (Persian): fill from right to left
                const insetStart = 100 - this.holdProgress;
                return `clip-path: inset(0 0 0 ${insetStart}%);`;
            },

            get undoClipStyle() {
                // Draining countdown from 100% to 0%
                return `clip-path: inset(0 0 0 ${100 - this.undoProgress}%);`;
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
                return this.i18n.recommendationBody.replace(':count', '<strong class="text-rose-600 dark:text-rose-400 font-bold">' + count + '</strong>');
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
            },

            undoNoticeText() {
                const secondsLeft = Math.ceil((this.undoProgress / 100) * (this.undoDuration / 1000));
                return this.i18n.undoNotice.replace(':seconds', this.toPersianDigits(secondsLeft));
            }
        };
    }
</script>
