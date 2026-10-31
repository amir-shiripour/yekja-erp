{{-- Reusable Toast Notifications for ResellerHub --}}
<div x-data="{
        toast: { show: false, message: '', type: 'success' },
        _timer: null,
        showToast(message, type = 'success', duration = 4000) {
            this.toast = {
                show: true,
                message: String(message || ''),
                type: type || 'success'
            };
            if (this._timer) clearTimeout(this._timer);
            this._timer = setTimeout(() => {
                this.toast.show = false;
            }, duration);
        },
        init() {
            window._rhToastComponent = this;
            window.showToast = (msgOrType, maybeMsg) => {
                let t = 'success';
                let m = msgOrType;
                if (maybeMsg !== undefined) {
                    t = msgOrType;
                    m = maybeMsg;
                }
                this.showToast(m, t);
            };
        }
     }"
     x-on:toast.window="showToast($event.detail?.message || $event.detail?.text || $event.detail, $event.detail?.type || 'success')"
     x-on:notify.window="showToast($event.detail?.text || $event.detail?.message || $event.detail, $event.detail?.type || 'info')"
     x-show="toast.show"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="-translate-y-4 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="-translate-y-4 opacity-0 scale-95"
     class="fixed top-6 start-1/2 -translate-x-1/2 z-[9999] max-w-md w-full px-4"
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
        <button @click="toast.show = false" type="button"
                class="p-1 rounded-lg hover:bg-white/20 transition-colors text-white/80 hover:text-white cursor-pointer"
                aria-label="بستن اعلان">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<script>
    if (typeof window.faNum !== 'function') {
        window.faNum = function (str) {
            if (str === null || str === undefined) return '';
            const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return String(str).replace(/[0-9]/g, function (w) {
                return persian[+w];
            });
        };
    }
</script>

@include('resellerhub::partials.prevent-double-submit')
@include('resellerhub::partials.notice-modal')
