<div id="tenant-delete-modal"
     x-data="tenantDeleteModalComponent()"
     x-show="open"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="tenant-delete-title"
     aria-describedby="tenant-delete-description"
     dir="rtl"
     @keydown.escape.window="closeModal()"
     @keydown.tab="trapFocus($event)">

    <div class="flex min-h-screen w-full items-center justify-center p-4 sm:p-6">
        {{-- Backdrop --}}
        <div class="fixed inset-0 h-full w-full bg-black/50 backdrop-blur-sm transition-opacity"
             @click="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Dialog Panel --}}
        <div @click.stop
             class="relative z-10 my-auto w-full max-w-md origin-center space-y-5 rounded-2xl border border-gray-200/80 bg-white p-6 text-start shadow-xl transition-all dark:border-gray-800 dark:bg-gray-900 sm:p-7"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- HEADER: subtle icon + title + one-line description --}}
            <div class="flex items-start gap-3.5">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400"
                    aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1 space-y-1">
                    <h2 id="tenant-delete-title"
                        class="text-base font-semibold leading-snug text-gray-900 dark:text-gray-100 sm:text-lg">
                        {{ __('resellerhub::messages.tenants.delete_title') }}
                    </h2>
                    <p id="tenant-delete-description"
                       class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ __('resellerhub::messages.tenants.delete_description') }}
                    </p>
                </div>
            </div>

            {{-- Tenant Summary: minimal neutral card --}}
            <div
                class="rounded-xl border border-gray-200/80 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-gray-500 dark:text-gray-400">
                            {{ __('resellerhub::messages.tenants.system') }}
                        </dt>
                        <dd class="min-w-0 truncate font-medium text-gray-900 dark:text-gray-100"
                            x-text="tenant?.name || ''"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-gray-500 dark:text-gray-400">
                            {{ __('resellerhub::messages.tenants.subdomain') }}
                        </dt>
                        <dd class="min-w-0">
                            <code dir="ltr"
                                  class="block truncate rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:text-sm"
                                  x-text="tenant?.subdomain || ''"></code>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Destructive Warning: slim, subtle bar --}}
            <div
                class="flex items-start gap-2.5 rounded-lg border border-rose-200/70 bg-rose-50/70 px-3.5 py-3 text-[13px] leading-relaxed text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/25 dark:text-rose-200">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-500 dark:text-rose-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p>{{ __('resellerhub::messages.tenants.delete_warning_body') }}</p>
            </div>

            {{-- ACTION AREA --}}
            <div class="space-y-3">

                {{-- STAGE IDLE: Cancel + Hold-To-Confirm --}}
                <template x-if="stage === 'idle'">
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Cancel Button --}}
                        <button type="button"
                                id="btn-tenant-cancel"
                                @click="closeModal()"
                                class="inline-flex h-11 cursor-pointer items-center justify-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 outline-none transition-colors hover:bg-gray-50 active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-gray-400/60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700/70">
                            {{ __('resellerhub::messages.common.cancel') }}
                        </button>

                        {{-- Hold-To-Confirm Button (dual-layer clip-path fill, right-to-left in RTL) --}}
                        <button type="button"
                                id="btn-tenant-hold-delete"
                                @pointerdown="startHold($event)"
                                @pointermove="handlePointerMove($event)"
                                @pointerup="cancelHold()"
                                @pointercancel="cancelHold()"
                                @contextmenu.prevent
                                class="relative isolate inline-flex h-11 w-full touch-none cursor-pointer select-none items-center justify-center overflow-hidden rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-semibold text-rose-700 transition-colors outline-none focus-visible:ring-2 focus-visible:ring-rose-500/60 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300"
                                :class="{ 'border-rose-600 bg-rose-600 text-white dark:border-rose-600 dark:bg-rose-600 dark:text-white': isHolding && holdProgress >= 100 }">

                            {{-- Base Layer (visible text) --}}
                            <span class="relative flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span x-text="holdingProgressText()"></span>
                            </span>

                            {{-- Fill Layer (revealed via dynamic clip-path from right in RTL) --}}
                            <span aria-hidden="true"
                                  class="hold-progress-bar pointer-events-none absolute inset-0 flex items-center justify-center gap-2 bg-rose-600 px-4 text-sm font-semibold text-white"
                                  :style="overlayClipStyle">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span x-text="holdingProgressText()"></span>
                            </span>
                        </button>
                    </div>
                </template>

                {{-- STAGE UNDO: slim bar + subtle countdown drain --}}
                <template x-if="stage === 'undo'">
                    <div
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/60"
                        role="status" aria-live="polite">
                        <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
                            <p class="truncate text-xs font-medium text-gray-700 dark:text-gray-300 sm:text-sm"
                               x-text="undoNoticeText()"></p>

                            {{-- Undo Button --}}
                            <button type="button"
                                    id="btn-tenant-undo"
                                    @click="triggerUndo()"
                                    class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white outline-none transition-colors hover:bg-gray-800 active:scale-[0.97] focus-visible:ring-2 focus-visible:ring-gray-400/60 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-200">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h10a5 5 0 0 1 5 5v2m0 0l-3-3m3 3l3-3"/>
                                </svg>
                                <span>{{ __('resellerhub::messages.tenants.undo') }}</span>
                            </button>
                        </div>

                        {{-- Countdown drain (remaining seconds, clip-path) --}}
                        <div aria-hidden="true"
                             class="absolute inset-x-3 bottom-0 h-0.5 rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="absolute inset-0 rounded-full bg-gray-400 dark:bg-gray-500"
                                 :style="undoClipStyle"></div>
                        </div>
                    </div>
                </template>

                {{-- STAGE EXECUTING: hold button turned into a loading state --}}
                <template x-if="stage === 'executing'">
                    <button type="button"
                            disabled
                            aria-busy="true"
                            class="inline-flex h-11 w-full cursor-wait items-center justify-center gap-2.5 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white dark:bg-rose-600">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ __('resellerhub::messages.tenants.deleting_in_progress') }}</span>
                    </button>
                </template>

                {{-- Inline error message (no alert popups) --}}
                <p x-cloak
                   x-show="errorMessage"
                   role="alert"
                   class="text-center text-xs leading-relaxed text-rose-600 dark:text-rose-400 sm:text-[13px]"
                   x-text="errorMessage"></p>
            </div>
        </div>
    </div>
</div>

<script>
    function tenantDeleteModalComponent() {
        return {
            open: false,
            stage: 'idle', // 'idle' | 'undo' | 'executing'
            tenant: null,
            holdProgress: 0,
            isHolding: false,
            holdDuration: 1600, // 1.6 seconds
            holdStartTime: null,
            animFrame: null,

            // Undo countdown state
            undoDuration: 5000, // 5 seconds
            undoStartTime: null,
            undoProgress: 100, // 100% -> 0%
            undoTimer: null,
            undoAnimFrame: null,

            // Inline error message (shown inside the modal)
            errorMessage: '',

            i18n: {
                holdText: @json(__('resellerhub::messages.tenants.hold_to_confirm')),
                undoNotice: @json(__('resellerhub::messages.tenants.undo_notice', ['seconds' => ':seconds'])),
                undoCancelled: @json(__('resellerhub::messages.tenants.undo_cancelled')),
                serverError: @json(__('resellerhub::messages.common.server_connection_error')),
                defaultError: @json(__('resellerhub::messages.common.error')),
            },

            init() {
                window.openTenantDeleteModal = (data) => {
                    this.tenant = data;
                    this.resetAll();
                    this.open = true;
                    // Move initial focus to the cancel button once the template renders.
                    setTimeout(() => {
                        document.getElementById('btn-tenant-cancel')?.focus();
                    }, 120);
                };
            },

            resetAll() {
                this.stage = 'idle';
                this.isHolding = false;
                this.holdProgress = 0;
                this.undoProgress = 100;
                this.errorMessage = '';
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

            // --- ACCESSIBILITY: keep Tab focus trapped inside the dialog ---
            trapFocus(event) {
                if (!this.open) return;
                const root = document.getElementById('tenant-delete-modal');
                if (!root) return;

                const focusable = Array.from(
                    root.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')
                ).filter((el) => !el.disabled && el.offsetParent !== null);

                if (!focusable.length) return;

                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },

            // --- HOLD TO CONFIRM LOGIC (unchanged behavior) ---
            startHold(event) {
                if (this.stage !== 'idle' || this.isHolding) return;
                this.errorMessage = '';
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

            // --- UNDO STAGE LOGIC (unchanged behavior) ---
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
                        this.executeFinalDelete();
                    } else {
                        this.undoAnimFrame = requestAnimationFrame(loopUndo);
                    }
                };

                this.undoAnimFrame = requestAnimationFrame(loopUndo);
            },

            triggerUndo() {
                this.resetAll();
                this.open = false;
                if (window.showToast) {
                    window.showToast(this.i18n.undoCancelled, 'info');
                }
            },

            executeFinalDelete() {
                this.stage = 'executing';

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
                            }
                            setTimeout(() => {
                                if (window.location.pathname.includes('/tenants/')) {
                                    window.location.href = '{{ route('reseller.tenants.index') }}';
                                } else {
                                    window.location.reload();
                                }
                            }, 500);
                        } else {
                            this.stage = 'idle';
                            this.holdProgress = 0;
                            this.errorMessage = data.message || this.i18n.defaultError;
                        }
                    })
                    .catch(() => {
                        this.stage = 'idle';
                        this.holdProgress = 0;
                        this.errorMessage = this.i18n.serverError;
                    });
            },

            // --- COMPUTED STYLES & TEXTS ---
            get overlayClipStyle() {
                const insetStart = 100 - this.holdProgress;
                return `clip-path: inset(0 0 0 ${insetStart}%);`;
            },

            get undoClipStyle() {
                return `clip-path: inset(0 0 0 ${100 - this.undoProgress}%);`;
            },

            toPersianDigits(num) {
                if (num === null || num === undefined) return '';
                return String(num).replace(/[0-9]/g, (w) => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'][+w]);
            },

            holdingProgressText() {
                // Fixed label while holding (no percentage).
                return this.i18n.holdText;
            },

            undoNoticeText() {
                const secondsLeft = Math.ceil((this.undoProgress / 100) * (this.undoDuration / 1000));
                return this.i18n.undoNotice.replace(':seconds', this.toPersianDigits(secondsLeft));
            }
        };
    }
</script>
