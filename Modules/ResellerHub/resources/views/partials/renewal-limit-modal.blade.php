@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    $initialRenewalLimit = session('renewal_limit_payload');
@endphp

<div x-data="resellerRenewalLimitModalHandler(@js($initialRenewalLimit))"
     x-cloak
     x-show="isOpen"
     @keydown.escape.window="closeModal()"
     @open-renewal-limit-modal.window="openModal($event.detail)"
     class="fixed inset-0 z-[9999] overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     aria-labelledby="renewal-limit-modal-title"
     role="dialog"
     aria-modal="true">

    <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-950/70 dark:bg-black/85 backdrop-blur-sm transition-opacity"
             @click="closeModal()"></div>
        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 space-y-5 text-start my-8"
            dir="rtl">
            <div
                class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>
            <div class="flex items-start justify-between gap-4 pt-1 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3.5">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200/80 dark:bg-indigo-950/60 dark:text-indigo-400 dark:border-indigo-800/80 flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>

                    <div class="space-y-1">
                        <div
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/70 dark:text-indigo-300 dark:border-indigo-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                            <span>سقف تمدید دوره‌های آتی</span>
                        </div>
                        <h3 id="renewal-limit-modal-title"
                            class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            اشتراک شما در حال حاضر فعال و معتبر است
                        </h3>
                    </div>
                </div>

                {{-- Close Button --}}
                <button type="button"
                        @click="closeModal()"
                        class="text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-100 dark:hover:bg-gray-800 p-1.5 rounded-xl transition cursor-pointer shrink-0"
                        title="بستن">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Explanatory Message Box --}}
            <div
                class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-2">
                <div class="flex items-center gap-2 text-indigo-900 dark:text-indigo-200 font-bold text-xs">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>امکان تمدید زودهنگام بیشتر وجود ندارد</span>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed"
                   x-text="payload?.message || 'طبق تنظیمات سامانه، امکان تمدید متوالی برای دوره‌های آتی بیشتر از سقف مجاز وجود ندارد. اشتراک شما همچنان دارای اعتبار معتبر است.'">
                </p>
            </div>

            {{-- Subscription Information Card --}}
            <div
                class="rounded-2xl p-4 bg-gray-50/80 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/70 space-y-2.5 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">سازمان / سامانه:</span>
                    <strong class="font-bold text-gray-900 dark:text-white"
                            x-text="payload?.tenant_name || 'سامانه ابری'"></strong>
                </div>

                <template x-if="payload?.package_name">
                    <div
                        class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">پلن اشتراکی فعال:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400"
                              x-text="payload.package_name"></span>
                    </div>
                </template>

                <template x-if="payload?.expires_at_jalali">
                    <div
                        class="flex items-center justify-between pb-2 border-b border-gray-200/60 dark:border-gray-700/60">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">اعتبار فعلی تا تاریخ:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 dir-ltr text-xs tabular-nums"
                              x-text="payload.expires_at_jalali"></span>
                    </div>
                </template>

                <div class="flex items-center justify-between">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">سقف مجاز تمدید پیشاپیش:</span>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        x-text="payload?.max_cycles_label || (faNum(payload?.max_cycles || 1) + ' دوره آتی')"></span>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                @if(Route::has('tenant.subscription'))
                    <a href="{{ route('tenant.subscription') }}"
                       class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 text-xs font-bold transition cursor-pointer shadow-xs">
                        مشاهده جزئیات اشتراک
                    </a>
                @elseif(Route::has('profile.show'))
                    <a href="{{ route('profile.show', ['active_tab' => 'subscription']) }}"
                       class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 text-xs font-bold transition cursor-pointer shadow-xs">
                        مشاهده جزئیات اشتراک
                    </a>
                @else
                    <span></span>
                @endif

                <button type="button"
                        @click="closeModal()"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition cursor-pointer shadow-md shadow-indigo-600/20">
                    متوجه شدم
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function resellerRenewalLimitModalHandler(initialPayload = null) {
        return {
            isOpen: false,
            payload: initialPayload || null,

            init() {
                if (this.payload) {
                    this.openModal(this.payload);
                }
            },

            openModal(data) {
                this.payload = data || {};
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
            },

            faNum(str) {
                if (window.faNum) return window.faNum(str);
                return (str !== null && str !== undefined)
                    ? String(str).replace(/\d/g, d => String.fromCharCode(d.charCodeAt(0) + 1728))
                    : '';
            }
        };
    }

    window.openRenewalLimitModal = function (payload) {
        window.dispatchEvent(new CustomEvent('open-renewal-limit-modal', {detail: payload}));
    };
</script>
