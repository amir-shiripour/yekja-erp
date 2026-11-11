@php
    use Morilog\Jalali\Jalalian;

    $exportStatusLabels = [
        'active' => ['label' => 'فعال', 'class' => 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200/50 dark:border-emerald-800/40', 'dot' => 'bg-emerald-500'],
        'suspended' => ['label' => 'معلق', 'class' => 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200/50 dark:border-rose-800/40', 'dot' => 'bg-rose-500'],
        'expired' => ['label' => 'منقضی‌شده', 'class' => 'bg-amber-50/70 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200/50 dark:border-amber-800/40', 'dot' => 'bg-amber-500'],
    ];

    $hasActiveFilters = request()->hasAny(['search', 'status', 'reseller_id', 'sort_by', 'sort_order', 'per_page']);
@endphp

<div x-show="activeTab === 'database'"
     x-data="tenantExportTabManager()"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     role="tabpanel"
     id="tabpanel-database"
     aria-labelledby="tab-database"
     class="space-y-6"
     style="display: none;">

    {{-- Hero Banner --}}
    <x-resellerhub::card class="overflow-hidden">
        <div class="p-4 sm:p-6 md:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
            <div class="flex items-start sm:items-center gap-3.5 sm:gap-5 min-w-0">
                <div
                    class="w-11 h-11 sm:w-14 sm:h-14 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 flex items-center justify-center font-black shadow-xs shrink-0">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m0 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/>
                    </svg>
                </div>
                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-base sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                            استخراج و پشتیبان‌گیری دیتابیس مستاجران
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- 4 Metric Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        {{-- Total Tenants --}}
        <x-resellerhub::card class="p-4 sm:p-5 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block truncate">کل سامانه‌های در دسترس</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span
                        class="text-2xl font-black text-gray-900 dark:text-white">{{ reseller_fa_num($exportTotalTenants) }}</span>
                    <span class="text-xs text-gray-400">سامانه</span>
                </div>
            </div>
            <div
                class="w-11 h-11 rounded-xl bg-gray-100/90 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border border-gray-200/70 dark:border-gray-600/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </x-resellerhub::card>

        {{-- Active Tenants --}}
        <x-resellerhub::card class="p-4 sm:p-5 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block truncate">سامانه‌های فعال</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span
                        class="text-2xl font-black text-gray-900 dark:text-white">{{ reseller_fa_num($exportActiveTenants) }}</span>
                    <span class="text-xs text-gray-400">سرویس فعال</span>
                </div>
            </div>
            <div
                class="w-11 h-11 rounded-xl bg-gray-100/90 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border border-gray-200/70 dark:border-gray-600/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </x-resellerhub::card>

        {{-- Custom Domains --}}
        <x-resellerhub::card class="p-4 sm:p-5 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block truncate">دارای دامنه اختصاصی</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span
                        class="text-2xl font-black text-gray-900 dark:text-white">{{ reseller_fa_num($exportCustomDomainCount) }}</span>
                    <span class="text-xs text-gray-400">دامنه سفارشی</span>
                </div>
            </div>
            <div
                class="w-11 h-11 rounded-xl bg-gray-100/90 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 border border-gray-200/70 dark:border-gray-600/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
            </div>
        </x-resellerhub::card>

        {{-- Security Guarantee --}}
        <x-resellerhub::card class="p-4 sm:p-5 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <span class="text-xs font-bold text-gray-400 block truncate">ایزولاسیون کامل رکوردها</span>
                <p class="text-xs font-black text-gray-700 dark:text-gray-300 mt-1 leading-snug">
                    تفکیک ۱۰۰٪ بر اساس کلید شناسه هر مستاجر
                </p>
            </div>
            <div
                class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </x-resellerhub::card>
    </div>

    {{-- Search and Filter Bar --}}
    <form method="GET" action="{{ route('reseller.settings.index') }}"
          class="bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs p-4 sm:p-5 transition-all duration-300">
        <input type="hidden" name="tab" value="database">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
            {{-- Search input --}}
            <div
                class="relative col-span-1 sm:col-span-2 {{ (count($exportResellersList) > 0) ? 'lg:col-span-4' : 'lg:col-span-5' }}">
                <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <x-resellerhub::input class="ps-10" type="text"
                                      name="search"
                                      value="{{ request('search') }}"
                                      placeholder="جستجو در نام سامانه، زیردامنه، دامنه یا خریدار..."
                />
            </div>

            {{-- Status Filter --}}
            <div class="col-span-1 sm:col-span-1 lg:col-span-2">
                <x-resellerhub::select name="status">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فقط فعال‌ها</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>فقط معلق‌ها
                    </option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>فقط منقضی‌شده‌ها
                    </option>
                </x-resellerhub::select>
            </div>

            {{-- Reseller Filter for Super Admin --}}
            @if(count($exportResellersList) > 0)
                <div class="col-span-1 sm:col-span-1 lg:col-span-2">
                    <x-resellerhub::select name="reseller_id">
                        <option value="all">همه نمایندگان</option>
                        @foreach($exportResellersList as $r)
                            <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->company_name ?: ($r->user?->name ?? 'نماینده #' . $r->id) }}
                            </option>
                        @endforeach
                    </x-resellerhub::select>
                </div>
            @endif

            {{-- Sort Filter --}}
            <div class="col-span-1 sm:col-span-1 lg:col-span-2">
                <x-resellerhub::select name="sort_by">
                    <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>جدیدترین
                        (پیش‌فرض)
                    </option>
                    <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                    <option value="name_asc" @selected(request('sort_by') === 'name_asc')>نام (الفبا صعودی)</option>
                    <option value="name_desc" @selected(request('sort_by') === 'name_desc')>نام (الفبا نزولی)</option>
                    <option value="created_at_asc" @selected(request('sort_by') === 'created_at_asc')>تاریخ ساخت
                        (صعودی)
                    </option>
                </x-resellerhub::select>
            </div>

            {{-- Action Buttons --}}
            <div class="col-span-1 sm:col-span-2 lg:col-span-2 flex items-center gap-2 justify-end">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>فیلتر</span>
                </button>

                @if($hasActiveFilters)
                    <a href="{{ route('reseller.settings.index', ['tab' => 'database']) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all cursor-pointer shrink-0"
                       title="پاکسازی فیلترها">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>حذف</span>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tenants Table Card --}}
    <x-resellerhub::card class="overflow-hidden">
        <div
            class="px-4 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                    سامانه‌های آماده استخراج دیتابیس
                </h3>
            </div>
        </div>

        @if($exportTenants->count() > 0)
            @php
                $preparedExportRows = [];
                foreach ($exportTenants as $tenant) {
                    $tStats = $exportTenantsData[$tenant->id] ?? ['total_records' => 0, 'modules' => []];
                    $totalRec = $tStats['total_records'] ?? 0;
                    $st = $exportStatusLabels[$tenant->status] ?? ['label' => $tenant->status, 'class' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400'];

                    $tenantModalPayload = [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'custom_domain' => $tenant->custom_domain,
                        'status' => $tenant->status,
                        'status_label' => $st['label'],
                        'created_at_fa' => $tenant->jalali_created_at ?: '-',
                        'owner_name' => $tenant->subAccount ? ($tenant->subAccount->company_name ?: $tenant->subAccount->name) : 'نمایندگی اصلی',
                        'owner_phone' => $tenant->subAccount ? ($tenant->subAccount->mobile ?: ($tenant->subAccount->phone ?? '-')) : '-',
                        'export_sql_url' => route('reseller.tenants.export-sql', $tenant),
                        'export_json_url' => route('reseller.tenants.export-json', $tenant),
                        'show_url' => route('reseller.tenants.show', $tenant),
                    ];

                    $preparedExportRows[] = compact('tenant', 'tStats', 'totalRec', 'st', 'tenantModalPayload');
                }
            @endphp

            {{-- Mobile Stacked Cards --}}
            <div class="lg:hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach($preparedExportRows as $row)
                    @php
                        $tenant = $row['tenant'];
                        $tStats = $row['tStats'];
                        $totalRec = $row['totalRec'];
                        $st = $row['st'];
                        $tenantModalPayload = $row['tenantModalPayload'];
                    @endphp
                    <div class="p-4 space-y-3">
                        {{-- Row Header: Avatar + Name + Status --}}
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div
                                    class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 font-black text-xs flex items-center justify-center shrink-0">
                                    <span>#{{ $tenant->id }}</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-black text-sm text-gray-900 dark:text-white truncate">
                                        {{ $tenant->name }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        ثبت: {{ reseller_fa_num($tenant->jalali_created_at ?: '-') }}
                                    </div>
                                </div>
                            </div>

                            <span
                                class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $st['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>
                                <span>{{ $st['label'] }}</span>
                            </span>
                        </div>

                        {{-- Domain + Volume pills --}}
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[11px] font-bold bg-gray-100/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/70 dark:border-gray-700 dir-ltr">
                                /{{ $tenant->subdomain }}
                            </span>
                            @if($tenant->custom_domain)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 dark:bg-gray-800/70 text-gray-600 dark:text-gray-300 border border-gray-200/70 dark:border-gray-700 dir-ltr max-w-full truncate">
                                    {{ $tenant->custom_domain }}
                                </span>
                            @endif
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-50 dark:bg-gray-800/70 text-gray-600 dark:text-gray-300 font-bold text-[11px] border border-gray-200/70 dark:border-gray-700">
                                {{ reseller_fa_num(number_format($totalRec)) }} رکورد
                            </span>
                        </div>

                        {{-- Owner & Reseller Meta --}}
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                            <div class="min-w-0">
                                <span class="text-[11px] text-gray-400 block">خریدار / زیرمجموعه</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">
                                    {{ $tenant->subAccount ? ($tenant->subAccount->company_name ?: $tenant->subAccount->name) : 'حساب مستقیم' }}
                                </span>
                            </div>
                            <div class="min-w-0 text-end">
                                <span class="text-[11px] text-gray-400 block">نماینده ثبت‌کننده</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">
                                    {{ $tenant->reseller?->company_name ?: ($tenant->reseller?->user?->name ?? 'نماینده #' . $tenant->reseller_id) }}
                                </span>
                            </div>
                        </div>

                        {{-- Export Actions --}}
                        <div class="grid grid-cols-4 gap-2 pt-1">
                            <button type="button"
                                    @click="openModal(@js($tenantModalPayload), @js($tStats))"
                                    class="col-span-2 h-9 inline-flex items-center justify-center gap-1.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-all active:scale-95 whitespace-nowrap cursor-pointer">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>عملیات استخراج</span>
                            </button>

                            <a href="{{ route('reseller.tenants.export-sql', $tenant) }}"
                               title="دانلود فوری SQL Dump"
                               class="col-span-1 h-9 px-2 inline-flex items-center justify-center gap-1 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 transition-all active:scale-95 font-mono text-xs font-bold">
                                <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>SQL</span>
                            </a>

                            <a href="{{ route('reseller.tenants.export-json', $tenant) }}"
                               title="دانلود فوری خروجی JSON"
                               class="col-span-1 h-9 px-2 inline-flex items-center justify-center gap-1 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 transition-all active:scale-95 font-mono text-xs font-bold">
                                <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>JSON</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Table --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500 font-black">
                        <th scope="col" class="py-3.5 px-3 text-start whitespace-nowrap">سامانه</th>
                        <th scope="col" class="py-3.5 px-2.5 text-start whitespace-nowrap">مسیر</th>
                        <th scope="col" class="py-3.5 px-2.5 text-start whitespace-nowrap">خریدار</th>
                        <th scope="col" class="py-3.5 px-2.5 text-start whitespace-nowrap">نماینده</th>
                        <th scope="col" class="py-3.5 px-2 text-center whitespace-nowrap">وضعیت</th>
                        <th scope="col" class="py-3.5 px-2 text-center whitespace-nowrap">داده‌ها</th>
                        <th scope="col" class="py-3.5 px-3 text-end whitespace-nowrap">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                    @foreach($preparedExportRows as $row)
                        @php
                            $tenant = $row['tenant'];
                            $tStats = $row['tStats'];
                            $totalRec = $row['totalRec'];
                            $st = $row['st'];
                            $tenantModalPayload = $row['tenantModalPayload'];
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/60 transition-colors">
                            {{-- Tenant Title & ID --}}
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-700 font-black text-[11px] flex items-center justify-center shrink-0">
                                        <span>#{{ $tenant->id }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-black text-gray-900 dark:text-white truncate max-w-[130px]" title="{{ $tenant->name }}">
                                            {{ $tenant->name }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5 whitespace-nowrap">
                                            {{ reseller_fa_num($tenant->jalali_created_at ?: '-') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Subdomain / Custom Domain --}}
                            <td class="py-3.5 px-2.5">
                                <div class="space-y-1 max-w-[125px]">
                                    <span title="/{{ $tenant->subdomain }}"
                                        class="inline-block max-w-full truncate align-middle px-2 py-0.5 rounded-lg font-mono text-[11px] font-bold bg-gray-100/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/70 dark:border-gray-700 dir-ltr">
                                        /{{ $tenant->subdomain }}
                                    </span>
                                    @if($tenant->custom_domain)
                                        <div
                                            class="text-[11px] font-medium text-gray-600 dark:text-gray-300 flex items-center gap-1 dir-ltr max-w-full">
                                            <svg class="w-3 h-3 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                            <span class="truncate">{{ $tenant->custom_domain }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Owner / SubAccount --}}
                            <td class="py-3.5 px-2.5">
                                @if($tenant->subAccount)
                                    <div class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[115px]" title="{{ $tenant->subAccount->company_name ?: $tenant->subAccount->name }}">
                                        {{ $tenant->subAccount->company_name ?: $tenant->subAccount->name }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-0.5 dir-ltr text-right whitespace-nowrap">
                                        {{ $tenant->subAccount->mobile ?: ($tenant->subAccount->phone ?? '-') }}
                                    </div>
                                @else
                                    <span class="text-xs font-bold text-gray-400 whitespace-nowrap">حساب مستقیم</span>
                                @endif
                            </td>

                            {{-- Reseller Info --}}
                            <td class="py-3.5 px-2.5">
                                @php $resellerTitle = $tenant->reseller?->company_name ?: ($tenant->reseller?->user?->name ?? 'نماینده #' . $tenant->reseller_id); @endphp
                                <div class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[120px]" title="{{ $resellerTitle }}">
                                    {{ $resellerTitle }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5 whitespace-nowrap">
                                    #{{ $tenant->reseller_id }}
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-2 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $st['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>
                                    <span>{{ $st['label'] }}</span>
                                </span>
                            </td>

                            {{-- Total Records Count --}}
                            <td class="py-3.5 px-2 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-gray-50 dark:bg-gray-800/70 text-gray-700 dark:text-gray-300 font-bold text-[11px] border border-gray-200/70 dark:border-gray-700">
                                    <span>{{ reseller_fa_num(number_format($totalRec)) }} رکورد</span>
                                </span>
                            </td>

                            {{-- Export Operations Action Buttons --}}
                            <td class="py-3.5 px-3 text-end whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Open Detailed Modal Button --}}
                                    <button type="button"
                                            @click="openModal(@js($tenantModalPayload), @js($tStats))"
                                            title="عملیات استخراج و جزئیات"
                                            class="h-8 inline-flex items-center justify-center gap-1 px-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] shadow-xs transition-all active:scale-95 whitespace-nowrap cursor-pointer">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>استخراج</span>
                                    </button>

                                    {{-- Quick SQL Download Button --}}
                                    <a href="{{ route('reseller.tenants.export-sql', $tenant) }}"
                                       title="دانلود فوری SQL Dump"
                                       class="h-8 px-2 inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 transition-all active:scale-95 font-mono text-[11px] font-bold whitespace-nowrap">
                                        <span>SQL</span>
                                    </a>

                                    {{-- Quick JSON Download Button --}}
                                    <a href="{{ route('reseller.tenants.export-json', $tenant) }}"
                                       title="دانلود فوری خروجی JSON"
                                       class="h-8 px-2 inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 transition-all active:scale-95 font-mono text-[11px] font-bold whitespace-nowrap">
                                        <span>JSON</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($exportTenants->total() > 0)
                <div
                    class="px-4 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-gray-500 dark:text-gray-400 font-medium">
                        <span>
                            نمایش
                            <strong class="font-bold text-gray-900 dark:text-white">{{ reseller_fa_num($exportTenants->firstItem() ?? 1) }}</strong>
                            تا
                            <strong class="font-bold text-gray-900 dark:text-white">{{ reseller_fa_num($exportTenants->lastItem() ?? $exportTenants->count()) }}</strong>
                            از مجموع
                            <strong class="font-bold text-gray-900 dark:text-white">{{ reseller_fa_num($exportTenants->total()) }}</strong>
                            سامانه
                        </span>

                        <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>

                        <form method="GET" action="{{ route('reseller.settings.index') }}"
                              class="inline-flex items-center gap-1.5 m-0">
                            <input type="hidden" name="tab" value="database">
                            @foreach(request()->except(['per_page', 'export_page', 'page', 'tab']) as $k => $v)
                                @if(!is_array($v) && $v !== null && $v !== '')
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                            <span class="hidden sm:inline">تعداد در صفحه:</span>
                            <select name="per_page" aria-label="تعداد در صفحه" onchange="this.form.submit()"
                                    class="rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1.5 px-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer shadow-2xs">
                                @foreach([10, 15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ reseller_fa_num($size) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="flex items-center justify-center">
                        @if($exportTenants->hasPages())
                            {{ $exportTenants->appends(['tab' => 'database'])->withQueryString()->links('resellerhub::partials.pagination') }}
                        @else
                            <div class="inline-flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs shadow-sm select-none">
                                    {{ reseller_fa_num(1) }}
                                </span>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="py-16 text-center text-gray-400">
                <div
                    class="w-16 h-16 rounded-3xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <h4 class="font-black text-gray-700 dark:text-gray-300 text-base">هیچ سامانه‌ای برای استخراج دیتابیس
                    یافت نشد</h4>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    هیچ سامانه‌ای با فیلترهای مشخص‌شده همخوانی ندارد. می‌توانید فیلترها را پاکسازی کنید.
                </p>
            </div>
        @endif
    </x-resellerhub::card>

    {{-- Export Operations Modal (Self-Contained Alpine Modal) --}}
    <div x-show="modalOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">

        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @click.outside="closeModal()"
             @keydown.escape.window="closeModal()"
             class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header --}}
            <div
                class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/40">
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m0 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span>استخراج دیتابیس اختصاصی</span>
                            <span
                                class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800">
                                #<span x-text="currentTenant?.id"></span>
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            سامانه: <span class="font-bold text-gray-800 dark:text-gray-200"
                                          x-text="currentTenant?.name"></span>
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="closeModal()"
                        class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs sm:text-sm">
                {{-- Tenant Info Summary Bar --}}
                <div
                    class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <span class="text-xs text-gray-400 block font-bold">زیرمجموعه / خریدار:</span>
                        <span class="font-black text-gray-800 dark:text-gray-200 text-xs mt-0.5 block truncate"
                              x-text="currentTenant?.owner_name"></span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block font-bold">آدرس ساب‌دامین:</span>
                        <span
                            class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-0.5 block truncate dir-ltr">
                            /<span x-text="currentTenant?.subdomain"></span>
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block font-bold">کل رکوردهای دیتابیس:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs mt-0.5 block">
                            <span x-text="currentStats?.total_records ?? 0"></span> رکورد مجزا
                        </span>
                    </div>
                </div>

                {{-- Primary Action Cards --}}
                <div class="space-y-3">
                    <div class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                        <span>انتخاب نوع فایل خروجی:</span>
                        <span class="text-xs text-gray-400">دانلود با اعتبارسنجی امن و لینک موقت</span>
                    </div>

                    {{-- Option 1: Standalone SQL Dump (Recommended) --}}
                    <a :href="currentTenant?.export_sql_url"
                       class="group p-4 sm:p-5 rounded-2xl bg-indigo-50/40 hover:bg-indigo-50/80 dark:bg-indigo-950/20 dark:hover:bg-indigo-950/35 border border-indigo-200/80 dark:border-indigo-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-200 shadow-xs cursor-pointer">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div
                                class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-xs shrink-0">
                                SQL
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="font-black text-gray-900 dark:text-white text-sm flex flex-wrap items-center gap-2">
                                    <span>دانلود فایل کامل دیتابیس (SQL Dump)</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold">پیشنهادی</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                    شامل ساختار کامل جداول (<code class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">CREATE
                                        TABLE</code>) و درج رکوردهای تفکیک‌شده (<code
                                        class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">INSERT INTO</code>) با
                                    رعایت روابط خارجی.
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <span
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs shadow-xs group-hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>دانلود .sql</span>
                            </span>
                        </div>
                    </a>

                    {{-- Option 2: JSON Export --}}
                    <a :href="currentTenant?.export_json_url"
                       class="group p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800/80 hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-gray-200/80 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-200 shadow-xs cursor-pointer">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div
                                class="w-11 h-11 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/80 dark:border-gray-600 flex items-center justify-center font-black text-xs shrink-0">
                                JSON
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="font-black text-gray-900 dark:text-white text-sm">
                                    دانلود آرشیو ساختاریافته JSON
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                    مناسب برای یکپارچه‌سازی‌های برنامه‌نویسی، انتقال به سیستم‌های ثالث و تحلیل‌های
                                    آماری.
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <span
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs transition">
                                <span>دانلود .json</span>
                            </span>
                        </div>
                    </a>
                </div>

                {{-- Table Breakdown Details --}}
                <div class="space-y-3 pt-2">
                    <div class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                        <span>خلاصه تفکیک رکوردهای جداول دیتابیس در فایل خروجی:</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto p-1 custom-scrollbar">
                        <template x-for="(modInfo, modKey) in (currentStats?.modules || {})" :key="modKey">
                            <template x-for="tbl in (modInfo?.tables || [])" :key="tbl.table">
                                <div x-show="tbl.count > 0"
                                     class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-gray-800 dark:text-gray-200 truncate"
                                             x-text="tbl.label"></div>
                                        <div class="text-xs text-gray-400" x-text="tbl.table"></div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-xs border border-emerald-200/60 dark:border-emerald-800/50 shrink-0">
                                        <span x-text="tbl.count"></span> رکورد
                                    </span>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>

                {{-- Security & Isolation Notice --}}
                <div
                    class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 flex items-start gap-2.5 text-xs text-blue-900 dark:text-blue-200">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="leading-relaxed">
                        داده‌های استخراج‌شده در این بسته به صورت کاملاً ایزوله و مستقل فیلتر شده‌اند و به هیچ عنوان
                        اطلاعات سایر مستاجران یا داده‌های بالادستی پلتفرم در این فایل قرار ندارد.
                    </p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/40 flex items-center justify-between">
                <a :href="currentTenant?.show_url"
                   class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                    <span>مشاهده پنل و جزییات این CRM</span>
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                <button type="button"
                        @click="closeModal()"
                        class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition cursor-pointer">
                    بستن
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function tenantExportTabManager() {
        return {
            modalOpen: false,
            currentTenant: null,
            currentStats: null,
            openModal(tenant, stats) {
                this.currentTenant = tenant;
                this.currentStats = stats;
                this.modalOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            closeModal() {
                this.modalOpen = false;
                this.currentTenant = null;
                this.currentStats = null;
                document.body.classList.remove('overflow-hidden');
            }
        };
    }
</script>
