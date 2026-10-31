@once('reseller-notice-modal')
    @php
        $sessionError = session('error');
        $sessionWarning = session('warning');
        $sessionSuccess = session('success');
        $sessionInfo = session('info');
    @endphp

    <div x-data="resellerNoticeModalHandler({
        sessionError: @js($sessionError),
        sessionWarning: @js($sessionWarning),
        sessionSuccess: @js($sessionSuccess),
        sessionInfo: @js($sessionInfo)
     })"
         x-cloak
         x-show="isOpen"
         @keydown.escape.window="closeModal()"
         @open-reseller-notice-modal.window="openModal($event.detail)"
         class="fixed inset-0 z-[99999] overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         aria-labelledby="reseller-notice-modal-title"
         role="dialog"
         aria-modal="true"
         dir="rtl">

        <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-950/55 dark:bg-black/70 backdrop-blur-[2px]"
                 @click="closeModal()"
                 aria-hidden="true"></div>

            {{-- Modal Dialog --}}
            <div
                class="relative z-10 w-full max-w-[420px] overflow-hidden rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/90 dark:border-gray-800 shadow-[0_24px_70px_-16px_rgba(15,23,42,0.28)] dark:shadow-[0_24px_70px_-16px_rgba(0,0,0,0.75)]"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 sm:scale-[0.97]"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 sm:scale-[0.98]"
                @click.stop="$event.stopPropagation()">

                <div class="p-6 text-start">
                    {{-- Header: icon chip + title + close --}}
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                             :class="{
                            'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400': type === 'error',
                            'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400': type === 'warning',
                            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400': type === 'success',
                            'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400': type === 'info'
                         }">
                            <template x-if="type === 'error'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v3.75m0 3.75h.008M10.34 3.94l-8.4 14.55A1.75 1.75 0 003.46 21h17.08a1.75 1.75 0 001.52-2.51L13.66 3.94a1.75 1.75 0 00-3.32 0z"/>
                                </svg>
                            </template>
                            <template x-if="type === 'warning'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v3.75m0 3.75h.008M10.34 3.94l-8.4 14.55A1.75 1.75 0 003.46 21h17.08a1.75 1.75 0 001.52-2.51L13.66 3.94a1.75 1.75 0 00-3.32 0z"/>
                                </svg>
                            </template>
                            <template x-if="type === 'success'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="type === 'info'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                </svg>
                            </template>
                        </div>

                        <div class="min-w-0 flex-1 pt-0.5">
                            <h3 id="reseller-notice-modal-title"
                                class="text-[15px] font-bold leading-6 text-gray-900 dark:text-gray-50"
                                x-text="title"></h3>
                            <p class="mt-1.5 text-[13px] leading-6 text-gray-600 dark:text-gray-400 whitespace-pre-line"
                               x-text="message"></p>
                        </div>

                        <button type="button"
                                @click="closeModal()"
                                aria-label="بستن"
                                class="shrink-0 -mt-1 -me-1 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-500 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Action Button --}}
                    <button type="button"
                            @click="closeModal()"
                            class="mt-5 w-full h-10 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-[13px] font-bold transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                            x-text="confirmText">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resellerNoticeModalHandler(initialData) {
            return {
                isOpen: false,
                title: '',
                message: '',
                type: 'error',
                confirmText: 'متوجه شدم',

                init() {
                    if (initialData?.sessionError) {
                        this.openModal({
                            title: 'توجه / خطا',
                            message: initialData.sessionError,
                            type: 'error'
                        });
                    } else if (initialData?.sessionWarning) {
                        this.openModal({
                            title: 'هشدار',
                            message: initialData.sessionWarning,
                            type: 'warning'
                        });
                    }
                },

                openModal(data) {
                    if (!data) return;
                    this.title = data.title || (data.type === 'success' ? 'عملیات موفق' : (data.type === 'warning' ? 'هشدار' : 'پیام سیستم'));
                    this.message = data.message || data.text || '';
                    this.type = data.type || 'error';
                    this.confirmText = data.confirmText || 'متوجه شدم';
                    this.isOpen = true;
                },

                closeModal() {
                    this.isOpen = false;
                }
            };
        }

        // Global unified modal helper replacing browser alert
        window.showResellerNotice = function (opts) {
            const payload = typeof opts === 'string' ? {message: opts, type: 'warning'} : opts;
            window.dispatchEvent(new CustomEvent('open-reseller-notice-modal', {detail: payload}));
        };

        // Safely override raw browser alert across ResellerHub views
        if (typeof window !== 'undefined') {
            window.alert = function (msg) {
                window.showResellerNotice({
                    title: 'پیام سیستم',
                    message: String(msg || ''),
                    type: 'warning'
                });
            };
        }
    </script>
@endonce
