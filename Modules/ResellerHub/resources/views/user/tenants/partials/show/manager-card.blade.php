<div
    class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between backdrop-blur-xl h-full">
    <h2 class="text-base font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700/60 min-h-[44px] flex items-center justify-between">
        <span>مشخصات مدیر و حساب زیرمجموعه</span>
        <span
            class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">خریدار سامانه</span>
    </h2>

    <div class="space-y-4 text-sm flex-1 pt-4">
        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">نام مدیر سامانه:</span>
            <span class="font-bold text-gray-900 dark:text-white">{{ $managerName }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">شماره موبایل:</span>
            <span class="font-medium dir-ltr">{{ $managerMobile ? reseller_fa_num($managerMobile) : '—' }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">نشانی ایمیل (نام کاربری):</span>
            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 dir-ltr">{{ $managerEmail }}</span>
        </div>

        @if($parentReseller)
            <div
                class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-700/40 text-xs">
                <span class="text-gray-500 dark:text-gray-400">نماینده فروش ارائه‌دهنده:</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $parentReseller->company_name ?: ($parentReseller->user?->name ?? 'نماینده #' . $parentReseller->id) }}
                </span>
            </div>
        @endif
    </div>
    <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 mt-auto">
        <template x-if="status === 'pending_payment'">
            <div
                class="w-full h-12 px-5 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs font-bold rounded-xl flex items-center justify-center gap-2">
                <span>تغییر وضعیت برای رزروهای در انتظار پرداخت غیرفعال است</span>
            </div>
        </template>
        <template x-if="status !== 'pending_payment'">
            <button type="button"
                    @click="openSuspendModal()"
                    class="w-full h-12 px-5 text-xs font-black rounded-xl transition-all flex items-center justify-center gap-2 border shadow-xs cursor-pointer box-border active:scale-95"
                    :class="status === 'active' ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-emerald-600 hover:bg-emerald-700 text-white border-transparent shadow-md shadow-emerald-600/20'">
                <template x-if="status === 'suspended'">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </template>
                <template x-if="status !== 'suspended'">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                    </svg>
                </template>
                <span
                    x-text="status === 'active' ? 'مسدودسازی موقت سامانه (Suspend)' : 'فعال‌سازی مجدد سامانه (Reactivate)'"></span>
            </button>
        </template>
    </div>
</div>
