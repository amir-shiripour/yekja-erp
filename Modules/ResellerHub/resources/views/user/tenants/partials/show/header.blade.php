<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
    <div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ $tenant->name }}</h1>

            <template x-if="status === 'active'">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-bold border border-emerald-200/50 dark:border-emerald-800/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    فعال و آماده به کار
                </span>
            </template>
            <template x-if="status === 'pending_payment'">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 rounded-full text-xs font-bold border border-blue-200/50 dark:border-blue-800/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    در انتظار پرداخت فاکتور
                </span>
            </template>
            <template x-if="status === 'suspended'">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 rounded-full text-xs font-bold border border-amber-200/50 dark:border-amber-800/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    معلق / مسدود
                </span>
            </template>
            <template x-if="status === 'expired'">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 rounded-full text-xs font-bold border border-red-200/50 dark:border-red-800/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    منقضی شده
                </span>
            </template>

            @if($isCustomBranding)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 rounded-full text-xs font-bold border border-purple-200/50 dark:border-purple-800/40">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $brandPrimaryColor }};"></span>
                    وایت‌لیبل اختصاصی
                </span>
            @endif
        </div>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1 dir-ltr text-right font-medium">
            {{ $displayUrl }}
        </p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('reseller.dashboard') }}"
           class="px-5 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-xl transition-colors">
            بازگشت به لیست
        </a>

        <a href="{{ route('reseller.tenants.export-sql', $tenant->id) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 text-sm font-bold rounded-xl transition-colors border border-emerald-200 dark:border-emerald-800"
           title="استخراج دیتابیس اختصاصی این سامانه">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>دانلود SQL Dump</span>
        </a>

        <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
           class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 text-sm font-bold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>ویرایش سامانه</span>
        </a>

        <template x-if="status === 'pending_payment'">
            <span
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-sm font-bold rounded-xl border border-amber-200/60"
                title="ورود به سامانه پس از پرداخت و تسویه فاکتور امکان‌پذیر خواهد بود">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span>غیرقابل ورود (در انتظار تسویه)</span>
            </span>
        </template>
        <template x-if="status !== 'pending_payment'">
            <form method="POST" action="{{ route('reseller.tenants.login-as', $tenant->id) }}" target="_blank"
                  class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-xl transition shadow-md shadow-indigo-600/20 active:scale-95">
                    <span>ورود به CRM</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </button>
            </form>
        </template>
    </div>
</div>
