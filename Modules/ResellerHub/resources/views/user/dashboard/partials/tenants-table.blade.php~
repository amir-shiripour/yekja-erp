@php
    use App\Models\Tenant;
    use App\Tenancy\TenantManager;
    use Modules\ResellerHub\App\Models\ResellerPackage;

    $tenantManager = app(TenantManager::class);
    $totalTenants = $totalTenants ?? (isset($tenants) ? $tenants->total() : 0);
    $activeTenants = $activeTenants ?? 0;
    $pendingPaymentTenants = $pendingPaymentTenants ?? 0;
    $suspendedTenants = $suspendedTenants ?? 0;
    $expiredTenants = $expiredTenants ?? 0;
@endphp

<div
    class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl overflow-hidden transition-all">
    <div
        class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <span
                    class="w-9 h-9 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </span>
                <h2 class="text-base font-black text-gray-900 dark:text-white">
                    سامانه‌های CRM واگذار شده
                </h2>
                <span
                    class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50 text-xs font-black tabular-nums">
                    {{ reseller_fa_num($tenants->total()) }} سامانه
                </span>
            </div>
            <p class="text-xs text-gray-400 font-medium ms-11">
                پایش لحظه‌ای لایسنس‌ها، وضعیت اتصال و ورود مستقیم به سامانه‌های مشتریان
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('reseller.tenants.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>افزودن سامانه جدید</span>
            </a>
        </div>
    </div>
    <div class="px-5 sm:px-6 py-4 bg-gray-50/75 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700/60">
        <form method="GET" action="{{ (request()->routeIs('reseller.tenants.*') && \Illuminate\Support\Facades\Route::has('reseller.tenants.index')) ? route('reseller.tenants.index') : route('reseller.dashboard') }}" class="m-0">
            @if(request('period'))
                <input type="hidden" name="period" value="{{ request('period') }}">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <div
                    class="relative {{ (!empty($isSuperAdmin) && !empty($allResellers) && $allResellers->count() > 1) ? 'lg:col-span-4 sm:col-span-2' : 'lg:col-span-6 sm:col-span-2' }}">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search', request('q')) }}"
                           placeholder="جستجو بر اساس نام کسب‌وکار، سابدامین، مالک، شماره تماس..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 ps-10 pe-18 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                    <div class="absolute inset-y-0 end-0 pe-1.5 flex items-center gap-1">
                        @if(request()->hasAny(['search', 'q', 'status', 'reseller_id', 'sort_by', 'sort_order']))
                            <a href="{{ route('reseller.dashboard', request('period') ? ['period' => request('period')] : []) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                               title="حذف فیلترها">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif
                        <button type="submit"
                                class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-2xs cursor-pointer">
                            <span>جستجو</span>
                        </button>
                    </div>
                </div>

                <div
                    class="{{ (!empty($isSuperAdmin) && !empty($allResellers) && $allResellers->count() > 1) ? 'lg:col-span-3 sm:col-span-1' : 'lg:col-span-3 sm:col-span-1' }}">
                    <select name="status" aria-label="فیلتر وضعیت"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                        <option value="" @selected(!request('status'))>
                            همه وضعیت‌ها ({{ reseller_fa_num($totalTenants) }})
                        </option>
                        <option value="active" @selected(request('status') === 'active')>
                            فعال ({{ reseller_fa_num($activeTenants) }})
                        </option>
                        <option value="pending_payment" @selected(request('status') === 'pending_payment')>
                            در انتظار پرداخت ({{ reseller_fa_num($pendingPaymentTenants ?? 0) }})
                        </option>
                        <option value="suspended" @selected(request('status') === 'suspended')>
                            مسدود / معلق ({{ reseller_fa_num($suspendedTenants) }})
                        </option>
                        <option value="expired" @selected(request('status') === 'expired')>
                            منقضی شده ({{ reseller_fa_num($expiredTenants) }})
                        </option>
                    </select>
                </div>

                <div
                    class="{{ (!empty($isSuperAdmin) && !empty($allResellers) && $allResellers->count() > 1) ? 'lg:col-span-2 sm:col-span-1' : 'lg:col-span-3 sm:col-span-1' }}">
                    <select name="sort_by" aria-label="مرتب‌سازی"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                        <option value="latest" @selected(request('sort_by') === 'latest' || !request('sort_by'))>
                            مرتب‌سازی: جدیدترین
                        </option>
                        <option value="oldest" @selected(request('sort_by') === 'oldest')>قدیمی‌ترین</option>
                        <option value="name_asc" @selected(request('sort_by') === 'name_asc')>نام (الف تا ی)</option>
                        <option value="name_desc" @selected(request('sort_by') === 'name_desc')>نام (ی تا الف)</option>
                        <option value="expires_at_asc" @selected(request('sort_by') === 'expires_at_asc')>انقضا
                            (نزدیک‌ترین)
                        </option>
                        <option value="expires_at_desc" @selected(request('sort_by') === 'expires_at_desc')>انقضا
                            (دورترین)
                        </option>
                    </select>
                </div>
                @if(!empty($isSuperAdmin) && !empty($allResellers) && $allResellers->count() > 1)
                    <div class="lg:col-span-3 sm:col-span-2">
                        <select name="reseller_id" aria-label="انتخاب نماینده"
                                onchange="this.form.submit()"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                            <option value="all" {{ ($selectedResellerId ?? 'all') === 'all' ? 'selected' : '' }}>همه
                                نمایندگان پلتفرم
                            </option>
                            @foreach($allResellers as $r)
                                <option
                                    value="{{ $r->id }}" {{ (string)($selectedResellerId ?? '') === (string)$r->id ? 'selected' : '' }}>
                                    {{ $r->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if($tenants->isEmpty())
        <div class="p-12 sm:p-16 text-center space-y-4">
            <div
                class="w-16 h-16 bg-gray-100 dark:bg-gray-700/60 text-gray-400 rounded-2xl flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="max-w-md mx-auto space-y-1">
                @if(request()->hasAny(['search', 'q', 'status', 'reseller_id']))
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ سامانه‌ای با فیلترهای انتخابی یافت
                        نشد</h3>
                    <p class="text-xs text-gray-400">عبارت جستجو یا فیلتر وضعیت انتخابی را تغییر دهید تا نتایج نمایش
                        داده شوند.</p>
                    <div class="pt-2">
                        <a href="{{ route('reseller.dashboard') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition">
                            پاک کردن فیلترها
                        </a>
                    </div>
                @else
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هنوز هیچ سامانه CRM ثبت نشده است</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        با زدن دکمه زیر می‌توانید در عرض چند ثانیه، اولین سامانه CRM اختصاصی مشتری خود را راه‌اندازی و
                        واگذار نمایید.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('reseller.tenants.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>راه‌اندازی اولین سامانه CRM</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/75 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs">
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="name" label="نام کسب‌وکار و سامانه"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="subdomain" label="آدرس دسترسی ابری"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold hidden lg:table-cell">طرح / پکیج</th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="status" label="وضعیت دسترسی"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="expires_at" label="اعتبار لایسنس"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-left">عملیات مدیریت</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-300">
                @foreach($tenants as $tenant)
                    @php
                        $tenantUrl = route('reseller.tenants.login-as', $tenant->id);
                        $displayUrl = $tenantManager->getTenantDisplayUrl($tenant);
                        $fullTenantUrl = $tenantManager->getTenantUrl($tenant);
                        $tPackage = $tenant->package;
                        $tPeriods = reseller_tenant_renewal_periods($tenant);
                        $daysLeft = reseller_days_remaining($tenant->plan_expires_at);
                        $totalSubscriptionDays = 365;
                        if ($tenant->created_at && $tenant->plan_expires_at) {
                            $totalSubscriptionDays = max(1, $tenant->created_at->diffInDays($tenant->plan_expires_at));
                        }
                        $percentDaysRemaining = ($daysLeft !== null && $daysLeft > 0)
                            ? min(100, max(2, round(($daysLeft / max(1, $totalSubscriptionDays)) * 100)))
                            : 0;
                        $barColorClass = ($daysLeft !== null && $daysLeft <= 7)
                            ? 'bg-rose-500'
                            : (($daysLeft !== null && $daysLeft <= 30) ? 'bg-amber-500' : 'bg-emerald-500');
                    @endphp
                    <tr class="group hover:bg-indigo-50/25 dark:hover:bg-indigo-950/20 transition-all duration-200 relative"
                        id="row-tenant-{{ $tenant->id }}">
                        <td class="py-4 px-4 sm:px-5 relative">
                            <div
                                class="absolute right-0 top-2 bottom-2 w-1 bg-transparent group-hover:bg-indigo-500 rounded-l transition-all duration-200"></div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 font-black text-xs flex items-center justify-center shrink-0">
                                    {{ mb_substr($tenant->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                                       class="font-black text-xs sm:text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block">
                                        {{ $tenant->name }}
                                    </a>
                                    <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        <span>ثبت: {{ reseller_format_jalali($tenant->created_at, 'Y/m/d') }}</span>
                                        @if($tenant->subAccount?->name)
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span
                                                class="text-gray-500 dark:text-gray-400 font-medium truncate max-w-[150px]"
                                                title="مالک: {{ $tenant->subAccount?->name }}">
                                                 {{ $tenant->subAccount?->name }}
                                             </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4 sm:px-5">
                            <div x-data="{ copied: false }" class="inline-flex items-center gap-1.5">
                                <form method="POST" action="{{ $tenantUrl }}" target="_blank" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline dir-ltr font-medium"
                                            title="ورود مستقیم به سامانه CRM">
                                        <span>{{ $displayUrl }}</span>
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </button>
                                </form>
                                <button type="button"
                                        @click="
                                            copyText('{{ $fullTenantUrl }}', 'آدرس اختصاصی سامانه کپی شد');
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="p-1 rounded-lg transition-all duration-200 cursor-pointer relative overflow-hidden active:scale-90"
                                        :class="copied ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-300 ring-2 ring-emerald-500/40 shadow-xs scale-105' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'"
                                        :title="copied ? 'کپی شد!' : 'کپی آدرس سامانه'">
                                    <span x-show="copied" style="display: none;"
                                          class="absolute inset-0 rounded-lg bg-emerald-500/25 animate-ping pointer-events-none"></span>
                                    <svg x-show="!copied"
                                         class="w-3.5 h-3.5 transition-transform duration-200 group-hover:scale-110"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" style="display: none;"
                                         class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 transition-transform duration-300 transform scale-110"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="py-4 px-4 sm:px-5 text-xs hidden lg:table-cell">
                            @if($tPackage)
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border transition-colors"
                                    style="{{ $tPackage->light_bg_style }}">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                          style="background-color: {{ $tPackage->color_hex }};"></span>
                                    <span>{{ $tPackage->name }}</span>
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/50">
                                    <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                                    <span>سفارشی</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-5" id="tenant-status-{{ $tenant->id }}">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $tenant->status_badge }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $tenant->status_dot_class }}"></span>
                                {{ $tenant->status_label }}
                            </span>
                        </td>
                        <td class="py-4 px-4 sm:px-5 text-xs" id="tenant-expires-{{ $tenant->id }}">
                            <div class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $tenant->jalali_plan_expires_at ?? 'نامحدود' }}
                            </div>
                            @if($tenant->plan_expires_at && $tenant->status === Tenant::STATUS_ACTIVE)
                                <div
                                    class="text-xs font-bold mt-1 flex items-center justify-between gap-1.5 {{ ($daysLeft !== null && $daysLeft <= 7) ? 'text-rose-600 dark:text-rose-400' : (($daysLeft !== null && $daysLeft <= 30) ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400') }}">
                                    <span>{{ reseller_human_remaining($tenant->plan_expires_at) }}</span>
                                    <span class="text-[9px] opacity-75 dir-ltr">{{ reseller_fa_num($percentDaysRemaining) }}٪</span>
                                </div>
                                {{-- Micro Progress Bar that smoothly expands and tracks lifecycle --}}
                                <div
                                    class="w-full bg-gray-100 dark:bg-gray-700/60 rounded-full h-1 mt-1.5 overflow-hidden"
                                    title="اعتبار باقیمانده: {{ $percentDaysRemaining }}٪">
                                    <div
                                        class="h-full rounded-full {{ $barColorClass }} transition-all duration-700 ease-out"
                                        style="width: {{ $percentDaysRemaining }}%"></div>
                                </div>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-5 text-left">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($tenant->isPendingPayment())
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60 text-xs font-bold"
                                        title="سامانه در انتظار پرداخت فاکتور بوده و پس از تسویه فعال می‌گردد">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>در انتظار تسویه</span>
                                    </span>
                                    <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95"
                                       title="مشاهده جزئیات سامانه">
                                        <span>جزئیات</span>
                                    </a>
                                @else
                                    <form method="POST" action="{{ $tenantUrl }}" target="_blank" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white dark:bg-indigo-950/60 dark:hover:bg-indigo-600 dark:text-indigo-300 dark:hover:text-white text-xs font-bold transition-all shadow-2xs active:scale-95"
                                                title="ورود مستقیم به پنل مدیریت سامانه (SSO)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>ورود</span>
                                        </button>
                                    </form>
                                    <button type="button"
                                            id="btn-renew-{{ $tenant->id }}"
                                            @click="openRenewModalByElement($el)"
                                            data-id="{{ $tenant->id }}"
                                            data-name="{{ e($tenant->name) }}"
                                            data-subdomain="{{ e($displayUrl) }}"
                                            data-package="{{ e($tPackage?->name ?? '') }}"
                                            data-expires="{{ e($tenant->jalali_plan_expires_at ?? 'نامحدود') }}"
                                            data-periods="{{ e(json_encode($tPeriods)) }}"
                                            data-url="{{ route('reseller.tenants.renew', $tenant->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white dark:bg-emerald-950/60 dark:hover:bg-emerald-600 dark:text-emerald-300 dark:hover:text-white text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95"
                                            title="تمدید لایسنس و اشتراک">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>تمدید</span>
                                    </button>
                                @endif

                                <div class="relative" x-data="{ open: false }">
                                    <button type="button"
                                            @click="open = !open"
                                            @click.outside="open = false"
                                            class="p-1.5 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors cursor-pointer"
                                            title="عملیات تکمیلی">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         style="display: none;"
                                         class="absolute left-0 mt-1.5 w-44 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 shadow-xl py-1.5 z-30 text-xs font-bold divide-y divide-gray-100 dark:divide-gray-700/60">

                                        <div class="py-1">
                                            <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                                               class="flex items-center gap-2 px-3.5 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span>مشاهده جزئیات</span>
                                            </a>
                                            <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
                                               class="flex items-center gap-2 px-3.5 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span>ویرایش اطلاعات</span>
                                            </a>
                                        </div>
                                        @if(!$tenant->isPendingPayment())
                                            <div class="py-1">
                                                <button type="button"
                                                        id="btn-toggle-{{ $tenant->id }}"
                                                        @click="open = false; openSuspendModalByElement($el)"
                                                        data-id="{{ $tenant->id }}"
                                                        data-name="{{ e($tenant->name) }}"
                                                        data-subdomain="{{ e($displayUrl) }}"
                                                        data-owner="{{ e($tenant->subAccount?->name ?? '—') }}"
                                                        data-suspended="{{ $tenant->status === 'suspended' ? 'true' : 'false' }}"
                                                        data-url="{{ route('reseller.tenants.toggle-status', $tenant->id) }}"
                                                        class="w-full flex items-center gap-2 px-3.5 py-2 text-start transition-colors cursor-pointer {{ $tenant->status === 'suspended' ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                                                    @if($tenant->status === 'suspended')
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span>رفع مسدودیت</span>
                                                    @else
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                                                        </svg>
                                                        <span>مسدودسازی موقت</span>
                                                    @endif
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($tenants->total() > 0)
            <div
                class="px-5 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        نمایش
                        <strong
                            class="text-gray-900 dark:text-gray-100 tabular-nums">{{ reseller_fa_num($tenants->firstItem() ?? 0) }}</strong>
                        تا
                        <strong
                            class="text-gray-900 dark:text-gray-100 tabular-nums">{{ reseller_fa_num($tenants->lastItem() ?? 0) }}</strong>
                        از
                        <strong
                            class="text-gray-900 dark:text-gray-100 tabular-nums">{{ reseller_fa_num($tenants->total()) }}</strong>
                        سامانه CRM
                    </span>

                    <span class="text-gray-300 dark:text-gray-600">•</span>

                    <form method="GET" class="inline-flex items-center gap-1.5 m-0">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('sort_by'))
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                        @endif
                        @if(request('reseller_id'))
                            <input type="hidden" name="reseller_id" value="{{ request('reseller_id') }}">
                        @endif
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        <span>تعداد در صفحه:</span>
                        <select name="per_page" aria-label="تعداد در صفحه" onchange="this.form.submit()"
                                class="rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1 px-2 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                            @foreach([10, 15, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected(request('per_page', 15) == $size)>
                                    {{ reseller_fa_num($size) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if($tenants->hasPages())
                    <div>
                        {{ $tenants->withQueryString()->links('resellerhub::partials.pagination') }}
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
