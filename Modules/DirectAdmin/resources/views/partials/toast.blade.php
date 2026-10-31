{{-- Reusable Real-time Toast Notifications Component --}}
<div x-show="toast.show"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="-translate-y-4 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="-translate-y-4 opacity-0 scale-95"
     class="fixed top-6 start-1/2 -translate-x-1/2 z-50 max-w-md w-full px-4"
     style="display: none;">
    <div
        :class="toast.type === 'success' ? 'bg-emerald-600 text-white shadow-emerald-500/30' : 'bg-rose-600 text-white shadow-rose-500/30'"
        class="p-4 rounded-2xl shadow-2xl flex items-center justify-between gap-3 border border-white/20 backdrop-blur-xl">
        <div class="flex items-center gap-3">
            <template x-if="toast.type === 'success'">
                <span class="p-1.5 rounded-xl bg-white/20 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </template>
            <template x-if="toast.type === 'error'">
                <span class="p-1.5 rounded-xl bg-white/20 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
            </template>
            <span class="text-xs sm:text-sm font-bold" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false"
                class="p-1 rounded-lg hover:bg-white/20 transition-colors text-white/80 hover:text-white cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
