<div
    class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 flex flex-col justify-between backdrop-blur-xl h-full">
    <h2 class="text-base font-bold text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700/60 min-h-[44px] flex items-center justify-between">
        <span>مشخصات کاربری و حساب زیرمجموعه</span>
        <span
            class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">خریدار سامانه</span>
    </h2>

    <div class="space-y-4 text-sm flex-1 pt-4">
        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">نام و نام خانوادگی:</span>
            <span class="font-bold text-gray-900 dark:text-white">{{ $managerName }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">شماره موبایل:</span>
            <span class="font-medium dir-ltr">{{ $managerMobile ? reseller_fa_num($managerMobile) : '—' }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-gray-500 dark:text-gray-400">نشانی ایمیل:</span>
            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 dir-ltr">{{ $managerEmail }}</span>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                    <span>فیلدهای سفارشی احراز هویت زیرمجموعه</span>
                </span>
                @if(isset($customFieldItems) && $customFieldItems->isNotEmpty())
                    <span
                        class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/80">
                        {{ reseller_fa_num($customFieldItems->count()) }} فیلد
                    </span>
                @endif
            </div>

            @if(isset($customFieldItems) && $customFieldItems->isNotEmpty())
                <div class="space-y-2">
                    @foreach($customFieldItems as $field)
                        @php
                            $fVal = $field['value'] ?? null;
                            $fHasVal = ($fVal !== null && $fVal !== '');
                            $isLtr = in_array($field['field_type'] ?? 'text', ['number', 'email', 'url', 'tel'])
                                || in_array($field['name'] ?? '', ['kdmly', 'national_code', 'national_id', 'melli_code', 'test_fax', 'test_postal', 'postal_code']);
                            $categoryBadge = match($field['category_group'] ?? 'general') {
                                'identity' => 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 border-indigo-200/80 dark:border-indigo-800/80',
                                'contact' => 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 border-blue-200/80 dark:border-blue-800/80',
                                'business' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200/80 dark:border-emerald-800/80',
                                default => 'text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800',
                            };
                        @endphp
                        <div
                            class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800/80 text-xs">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span
                                    class="font-bold text-gray-700 dark:text-gray-300 truncate">{{ $field['label'] }}:</span>
                                @if(!empty($field['category_label']))
                                    <span
                                        class="text-[9px] font-bold px-1.5 py-0.2 rounded border {{ $categoryBadge }}">
                                        {{ $field['category_label'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                @if($fHasVal)
                                    <span
                                        class="font-black text-gray-900 dark:text-white {{ $isLtr ? 'dir-ltr text-left' : '' }}">
                                        {{ $isLtr && is_numeric($fVal) ? reseller_fa_num($fVal) : $fVal }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 font-medium">ثبت نشده</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="p-3 rounded-xl bg-gray-50/60 dark:bg-gray-900/30 border border-dashed border-gray-200 dark:border-gray-800 text-center text-xs text-gray-400 dark:text-gray-500">
                    هیچ فیلد سفارشی برای احراز هویت این کاربر زیرمجموعه ثبت نشده است.
                </div>
            @endif

            @if(isset($subAccount) && $subAccount)
                <div class="pt-1 flex items-center justify-end">
                    <a href="{{ route('reseller.sub-accounts.show', $subAccount->id) }}"
                       class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                        <span>مشاهده پرونده کامل کاربر زیرمجموعه</span>
                        <svg class="w-3 h-3 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
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
