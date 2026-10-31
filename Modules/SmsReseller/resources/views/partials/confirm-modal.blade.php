{{-- Reusable Confirmation / Delete Modal Component for SmsReseller --}}
<div x-data="{
        isOpen: false,
        title: 'تأیید عملیات',
        message: 'آیا از انجام این عملیات اطمینان دارید؟',
        targetName: '',
        actionUrl: '',
        method: 'POST',
        confirmText: 'تأیید و ادامه',
        cancelText: 'انصراف',
        isLoading: false,
        type: 'danger',

        openModal(detail) {
            this.title = detail.title || 'تأیید عملیات';
            this.message = detail.message || 'آیا از انجام این عملیات اطمینان دارید؟';
            this.targetName = detail.targetName || '';
            this.actionUrl = detail.actionUrl || detail.action || '';
            this.method = detail.method || 'POST';
            this.confirmText = detail.confirmText || 'تأیید و ادامه';
            this.cancelText = detail.cancelText || 'انصراف';
            this.type = detail.type || 'danger';
            this.isLoading = false;
            this.isOpen = true;
        },

        closeModal() {
            if (this.isLoading) return;
            this.isOpen = false;
        },

        submitForm() {
            this.isLoading = true;
            this.$refs.confirmForm.submit();
        }
     }"
     @open-confirm-modal.window="openModal($event.detail)"
     @keydown.escape.window="closeModal()"
     x-show="isOpen"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
        {{-- Backdrop blur --}}
        <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
             @click="closeModal()"></div>

        {{-- Modal Card --}}
        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all sm:my-8 max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-center"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- Icon Badge --}}
            <div
                :class="type === 'danger' ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 border-red-100 dark:border-red-500/20' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-100 dark:border-indigo-500/20'"
                class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center shadow-inner border">
                <template x-if="type === 'danger'">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </template>
                <template x-if="type !== 'danger'">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </div>

            {{-- Title & Message --}}
            <div class="space-y-2">
                <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white" x-text="title"></h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal"
                   x-text="message"></p>

                {{-- Target item display badge --}}
                <template x-if="targetName">
                    <div
                        class="mt-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold truncate">
                        <span x-text="targetName"></span>
                    </div>
                </template>
            </div>

            {{-- Hidden Submit Form --}}
            <form x-ref="confirmForm" :action="actionUrl" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="_method" :value="method">
            </form>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="button"
                        @click="closeModal()"
                        :disabled="isLoading"
                        class="flex-1 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs sm:text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors cursor-pointer disabled:opacity-50">
                    <span x-text="cancelText"></span>
                </button>

                <button type="button"
                        @click="submitForm()"
                        :disabled="isLoading"
                        :class="type === 'danger' ? 'bg-red-600 hover:bg-red-700 shadow-red-600/30 hover:shadow-red-600/50' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/30 hover:shadow-indigo-600/50'"
                        class="flex-1 px-5 py-3 rounded-xl text-white text-xs sm:text-sm font-black shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                    <svg x-show="isLoading" class="animate-spin h-4 w-4 text-white shrink-0" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isLoading ? 'در حال ارسال...' : confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
