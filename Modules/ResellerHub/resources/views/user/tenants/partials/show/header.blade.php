<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $tenant->name }}</h1>
            </div>
        </div>

        {{-- Mobile/Tablet Back Button (Top corner next to title) --}}
        <a href="{{ route('reseller.dashboard') }}"
           class="lg:hidden h-10 inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 text-xs sm:text-sm font-bold rounded-xl transition-colors border border-slate-200/70 dark:border-gray-700 whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 text-slate-500 shrink-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="sm:inline hidden">بازگشت به لیست</span>
            <span class="sm:hidden inline">بازگشت</span>
        </a>
    </div>

    {{-- Action Bar: Single horizontal row with unified h-10 height (Zero 2-tier stacking!) --}}
    <div class="flex items-center gap-2 w-full lg:w-auto">
        {{-- Primary Action: Login to Tenant CRM --}}
        <template x-if="status === 'pending_payment'">
            <span
                class="h-10 inline-flex items-center justify-center gap-2 flex-1 lg:flex-initial px-4 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-xs sm:text-sm font-bold rounded-xl border border-amber-200/70 whitespace-nowrap"
                title="ورود به سامانه پس از پرداخت و تسویه فاکتور امکان‌پذیر خواهد بود">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>در انتظار تسویه</span>
            </span>
        </template>
        <template x-if="status !== 'pending_payment'">
            <form method="POST" action="{{ route('reseller.tenants.login-as', $tenant->id) }}" target="_blank"
                  class="flex-1 lg:flex-initial">
                @csrf
                <button type="submit"
                        class="w-full h-10 inline-flex items-center justify-center gap-2 px-4 sm:px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black rounded-xl transition shadow-xs hover:shadow-sm active:scale-95 cursor-pointer whitespace-nowrap">
                    <span>ورود به سامانه</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </button>
            </form>
        </template>

        {{-- Unified Amber Edit Button --}}
        <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
           class="h-10 inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-xs sm:text-sm font-bold rounded-xl transition-colors border border-amber-200/80 dark:border-amber-800/60 whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span class="sm:inline hidden">ویرایش سامانه</span>
            <span class="sm:hidden inline">ویرایش</span>
        </a>

        {{-- SQL Dump (Emerald) --}}
        <a href="{{ route('reseller.tenants.export-sql', $tenant->id) }}"
           class="h-10 w-10 sm:w-auto inline-flex items-center justify-center gap-1.5 sm:px-3.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-bold rounded-xl transition-colors border border-emerald-200/80 dark:border-emerald-800/60 whitespace-nowrap shrink-0"
           title="استخراج دیتابیس اختصاصی این سامانه">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span class="sm:inline hidden">دانلود SQL Dump</span>
        </a>

        {{-- Hard Delete Tenant (Rose) --}}
        <button type="button"
                @click="openTenantDeleteModal({
                    id: {{ $tenant->id }},
                    name: '{{ addslashes($tenant->name ?: $tenant->subdomain) }}',
                    subdomain: '{{ addslashes($displayUrl) }}',
                    delete_url: '{{ route('reseller.tenants.destroy', $tenant->id) }}'
                })"
                class="h-10 w-10 sm:w-auto inline-flex items-center justify-center gap-1.5 sm:px-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-xs sm:text-sm font-bold rounded-xl transition-colors border border-rose-200/80 dark:border-rose-800/60 cursor-pointer whitespace-nowrap shrink-0"
                title="{{ __('resellerhub::messages.tenants.delete_tooltip') }}">
            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <span class="sm:inline hidden">{{ __('resellerhub::messages.tenants.delete_action') }}</span>
        </button>

        {{-- Desktop Back Button (End of Action Bar) --}}
        <a href="{{ route('reseller.dashboard') }}"
           class="hidden lg:inline-flex h-10 items-center justify-center gap-1.5 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 text-sm font-bold rounded-xl transition-colors border border-slate-200/70 dark:border-gray-700 whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 text-slate-500 shrink-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>بازگشت به لیست</span>
        </a>
    </div>
</div>
