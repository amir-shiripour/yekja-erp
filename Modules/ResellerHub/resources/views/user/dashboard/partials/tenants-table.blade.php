@php
    use App\Models\Tenant;
    use App\Tenancy\TenantManager;
    use Modules\ResellerHub\App\Enums\PackageColor;
    use Modules\ResellerHub\App\Enums\PackageIcon;
    use Modules\ResellerHub\App\Models\ResellerPackage;

    $tenantManager = app(TenantManager::class);
    $totalTenants = $totalTenants ?? (isset($tenants) ? $tenants->total() : 0);
    $activeTenants = $activeTenants ?? 0;
    $pendingPaymentTenants = $pendingPaymentTenants ?? 0;
    $suspendedTenants = $suspendedTenants ?? 0;
    $expiredTenants = $expiredTenants ?? 0;
@endphp

<div
    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all">
    {{-- Card Header --}}
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
                    سامانه‌ها و کاربران
                </h2>
            </div>
        </div>
    </div>

    {{-- Filter Section (No Resellers Section!) --}}
    <div class="px-5 sm:px-6 py-4 bg-gray-50/75 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700/60">
        <form method="GET"
              action="{{ (request()->routeIs('reseller.tenants.*') && \Illuminate\Support\Facades\Route::has('reseller.tenants.index')) ? route('reseller.tenants.index') : route('reseller.dashboard') }}"
              class="m-0">
            @if(request('period') && !request()->routeIs('reseller.tenants.*'))
                <input type="hidden" name="period" value="{{ request('period') }}">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                {{-- Search Box--}}
                <div class="relative lg:col-span-6 sm:col-span-2">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search', request('q')) }}"
                           placeholder="جستجو بر اساس نام سامانه، کاربر، سابدامین، شماره تماس..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 ps-10 pe-18 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
                    <div class="absolute inset-y-0 end-0 pe-1.5 flex items-center gap-1">
                        @if(request()->hasAny(['search', 'q', 'status', 'sort_by', 'sort_order']))
                            <a href="{{ route('reseller.tenants.index') }}"
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

                {{-- Status Filter--}}
                <div class="lg:col-span-3 sm:col-span-1">
                    <select name="status" aria-label="فیلتر وضعیت"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                        <option value="" @selected(!request('status'))>
                            همه وضعیت‌ها
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

                {{-- Sort Filter--}}
                <div class="lg:col-span-3 sm:col-span-1">
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
            </div>
        </form>
    </div>

    @if($tenants->isEmpty() && (empty($unassignedSubAccounts) || $unassignedSubAccounts->isEmpty()))
        <div class="p-12 sm:p-16 text-center space-y-4">
            <div
                class="w-16 h-16 bg-gray-100 dark:bg-gray-700/60 text-gray-400 rounded-2xl flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="max-w-md mx-auto space-y-1">
                @if(request()->hasAny(['search', 'q', 'status', 'sort_by']))
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ سامانه‌ای با فیلترهای انتخابی یافت
                        نشد</h3>
                    <p class="text-xs text-gray-400">عبارت جستجو یا وضعیت را تغییر دهید تا نتایج نمایش داده شوند.</p>
                    <div class="pt-2">
                        <a href="{{ route('reseller.tenants.index') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition">
                            پاک کردن فیلترها
                        </a>
                    </div>
                @else
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هنوز هیچ سامانه ثبت نشده است</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        با زدن دکمه زیر می‌توانید در عرض چند ثانیه، اولین سامانه اختصاصی مشتری خود را راه‌اندازی و
                        واگذار نمایید.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('reseller.tenants.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>راه‌اندازی اولین سامانه</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Desktop Table View (>= md) --}}
        <div class="hidden md:block overflow-x-auto min-h-[260px]">
            <table class="w-full text-right text-sm">
                <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/75 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs">
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="name" label="نام CRM"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="status" label="وضعیت دسترسی"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="expires_at" label="اعتبار لایسنس"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        طرح و پکیج
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                        <x-resellerhub::sort-header col="subdomain" label="آدرس دسترسی"/>
                    </th>
                    <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-end">
                        عملیات
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-300">
                @foreach($tenants as $tenant)
                    @php
                        $tenantUrl = route('reseller.tenants.login-as', $tenant->id);
                        $displayUrl = $tenantManager->getTenantDisplayUrl($tenant);
                        $fullTenantUrl = $tenantManager->getTenantUrl($tenant);
                        $tPackage = $tenant->package;
                        $tPriceType = $tPackage?->getPriceTypeEnum();
                        $canRenew = $tPackage ? $tPriceType->hasRenewal() : true;
                        $tPeriods = $canRenew ? reseller_tenant_renewal_periods($tenant) : [];
                        $daysLeft = reseller_days_remaining($tenant->plan_expires_at);
                        $totalSubscriptionDays = 365;
                        if ($tenant->created_at && $tenant->plan_expires_at) {
                            $totalSubscriptionDays = max(1, $tenant->created_at->diffInDays($tenant->plan_expires_at));
                        }
                        $percentDaysRemaining = ($daysLeft !== null && $daysLeft > 0)
                            ? min(100, max(2, round(($daysLeft / max(1, $totalSubscriptionDays)) * 100)))
                            : 0;

                        $barColorClass = 'bg-emerald-500';
                        $daysTextClass = 'text-emerald-600 dark:text-emerald-400';
                        $badgeClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/60';

                        if ($daysLeft !== null && $daysLeft <= 7) {
                            $barColorClass = 'bg-rose-500';
                            $daysTextClass = 'text-rose-600 dark:text-rose-400';
                            $badgeClass = 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border-rose-100 dark:border-rose-800/60';
                        } elseif ($daysLeft !== null && $daysLeft <= 30) {
                            $barColorClass = 'bg-amber-500';
                            $daysTextClass = 'text-amber-600 dark:text-amber-400';
                            $badgeClass = 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 border-amber-100 dark:border-amber-800/60';
                        }
                    @endphp
                    <tr class="group hover:bg-indigo-50/25 dark:hover:bg-indigo-950/20 transition-all duration-200"
                        id="row-tenant-{{ $tenant->id }}">
                        <td class="py-4 px-4 sm:px-5 relative">
                            <div
                                class="absolute right-0 top-2 bottom-2 w-1 bg-transparent group-hover:bg-indigo-500 rounded-l transition-all duration-200"></div>
                            <div class="min-w-0">
                                <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                                   class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block">
                                    {{ $tenant->name ?: $tenant->subdomain }}
                                </a>
                            </div>
                        </td>
                        <td class="py-4 px-4 sm:px-5" id="tenant-status-{{ $tenant->id }}">
                            @if($tenant->status === 'active')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 ring-1 ring-inset ring-emerald-600/20 shadow-2xs">
                                    <span>فعال</span>
                                </span>
                            @elseif($tenant->status === 'suspended')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 ring-1 ring-inset ring-rose-600/20 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                    <span>معلق / مسدود</span>
                                </span>
                            @elseif($tenant->status === 'pending_payment')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 ring-1 ring-inset ring-amber-600/20 shadow-2xs">
                                    <span>در انتظار پرداخت</span>
                                </span>
                            @elseif($tenant->status === 'expired')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 ring-1 ring-inset ring-gray-400/20 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                                    <span>منقضی شده</span>
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap ring-1 ring-inset ring-gray-400/20 shadow-2xs {{ $tenant->status_badge }}">
                                    <span class="w-2 h-2 rounded-full {{ $tenant->status_dot_class }}"></span>
                                    <span>{{ $tenant->status_label }}</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-5" id="tenant-expires-{{ $tenant->id }}">
                            @if($tPackage && !$canRenew)
                                <div class="flex items-center gap-2.5 whitespace-nowrap">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800/60 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span
                                            class="text-xs font-bold text-gray-800 dark:text-gray-200 block truncate">{{ $tPriceType->licenseBadgeLabel() }}</span>
                                        <span class="text-[11px] text-gray-400 block">بدون محدودیت زمانی</span>
                                    </div>
                                </div>
                            @elseif($tenant->plan_expires_at)
                                <div class="flex items-center gap-2.5 whitespace-nowrap">
                                    <span
                                        class="w-8 h-8 rounded-xl {{ $badgeClass }} flex items-center justify-center shrink-0 border shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0 space-y-0.5">
                                        <div class="flex items-center gap-1.5">
                                            @if($daysLeft !== null && $daysLeft <= 0)
                                                <span class="text-xs font-black text-rose-600 dark:text-rose-400">منقضی شده</span>
                                            @elseif($daysLeft !== null)
                                                <span
                                                    class="text-xs font-black tabular-nums {{ $daysTextClass }}">
                                                    {{ reseller_fa_num($daysLeft) }} روز باقی‌مانده
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 tabular-nums">
                                            {{ reseller_format_jalali($tenant->plan_expires_at) }}
                                        </div>
                                        @if($tenant->status === Tenant::STATUS_ACTIVE && $daysLeft !== null && $daysLeft > 0)
                                            <div
                                                class="w-20 bg-gray-100 dark:bg-gray-700/60 rounded-full h-1 overflow-hidden"
                                                title="اعتبار باقی‌مانده: {{ $percentDaysRemaining }}٪">
                                                <div
                                                    class="h-full rounded-full {{ $barColorClass }} transition-all duration-700 ease-out"
                                                    style="width: {{ $percentDaysRemaining }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-2.5 whitespace-nowrap">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800/60 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block truncate">دائمی / مادام‌العمر</span>
                                        <span
                                            class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium block">بدون انقضا</span>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="py-4 px-4 sm:px-5">
                            @if($tPackage)
                                @php
                                    $pkgColor = PackageColor::fromOrDefault($tPackage->color ?? null);
                                    $pkgIcon = PackageIcon::fromOrDefault($tPackage->icon ?? null);
                                @endphp
                                <div class="flex items-center gap-2.5 whitespace-nowrap">
                                    <span
                                        class="w-8 h-8 rounded-xl {{ $pkgColor->lightClass() }} flex items-center justify-center shrink-0 border shadow-2xs">
                                        {!! $pkgIcon->svg('w-4 h-4') !!}
                                    </span>
                                    <div class="min-w-0">
                                        <span
                                            class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white block truncate">
                                            {{ $tPackage->name }}
                                        </span>
                                        <span
                                            class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400 block">
                                            {{ $tPriceType->label() }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-2.5 whitespace-nowrap">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-50 to-indigo-50 dark:from-violet-950/60 dark:to-indigo-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center shrink-0 border border-violet-200/70 dark:border-violet-800/60 shadow-2xs">
                                        <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span
                                            class="font-black text-xs sm:text-sm text-violet-900 dark:text-violet-200 block truncate">
                                            پکیج سفارشی
                                        </span>
                                        <span class="text-[11px] font-bold text-violet-600 dark:text-violet-400 block">
                                            منابع و ماژول‌های اختصاصی
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </td>

                        <td class="py-4 px-4 sm:px-5">
                            <div x-data="{ copied: false }" class="inline-flex items-center gap-1.5">
                                <a href="//{{ $displayUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline dir-ltr text-start font-bold truncate max-w-[170px]"
                                   title="{{ $displayUrl }}">
                                    {{ $displayUrl }}
                                </a>
                                <button type="button"
                                        @click="
                                            copyText('{{ $fullTenantUrl }}', 'آدرس اختصاصی سامانه با موفقیت کپی شد');
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="p-1 rounded-lg transition-all duration-200 cursor-pointer relative overflow-hidden active:scale-90"
                                        :class="copied ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-300 ring-2 ring-emerald-500/40 shadow-xs scale-105' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'"
                                        :title="copied ? 'کپی شد!' : 'کپی آدرس اختصاصی سامانه'">
                                    <span x-show="copied" x-cloak
                                          class="absolute inset-0 rounded-lg bg-emerald-500/25 animate-ping pointer-events-none"></span>
                                    <svg x-show="!copied"
                                         class="w-3.5 h-3.5 transition-transform duration-200"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak
                                         class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 transition-transform duration-300 transform scale-110"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="py-4 px-4 sm:px-5 text-end whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                @if(!$tenant->isPendingPayment())
                                    <form method="POST" action="{{ $tenantUrl }}" target="_blank"
                                          class="inline-flex items-center m-0 p-0 shrink-0">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white dark:bg-indigo-950/60 dark:hover:bg-indigo-600 dark:text-indigo-300 dark:hover:text-white text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer shrink-0"
                                                title="ورود مستقیم به پنل مدیریت سامانه (SSO)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>ورود</span>
                                        </button>
                                    </form>
                                @endif
                                @if($canRenew && !$tenant->isPendingPayment() && auth()->user()->can('resellerhub.tenants.renew'))
                                    <button type="button"
                                            id="btn-renew-{{ $tenant->id }}"
                                            @click="openRenewModalByElement($el)"
                                            data-id="{{ $tenant->id }}"
                                            data-name="{{ e($tenant->name) }}"
                                            data-subdomain="{{ e($displayUrl) }}"
                                            data-package="{{ e($tPackage?->name ?? '') }}"
                                            data-expires="{{ e($tenant->jalali_plan_expires_at ?? 'نامحدود') }}"
                                            data-periods="{{ e(json_encode($tPeriods, JSON_THROW_ON_ERROR)) }}"
                                            data-url="{{ route('reseller.tenants.renew', $tenant->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white dark:bg-emerald-950/60 dark:hover:bg-emerald-600 dark:text-emerald-300 dark:hover:text-white text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95 shrink-0"
                                            title="تمدید اشتراک">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>تمدید</span>
                                    </button>
                                @endif
                                <div class="relative inline-flex items-center text-start shrink-0"
                                     x-data="{
                                         open: false,
                                         coords: { top: 0, left: 0 },
                                         toggle(el) {
                                             if (this.open) { this.open = false; return; }
                                             const rect = el.getBoundingClientRect();
                                             const menuWidth = 205;
                                             const menuHeight = 180;
                                             const margin = 6;
                                             const spaceBelow = window.innerHeight - rect.bottom;
                                             const spaceAbove = rect.top;

                                             let top = (spaceBelow >= menuHeight + margin || spaceBelow >= spaceAbove)
                                                 ? (rect.bottom + margin)
                                                 : (rect.top - menuHeight - margin);

                                             let left = rect.left;
                                             if (left + menuWidth > window.innerWidth - 12) {
                                                 left = window.innerWidth - menuWidth - 12;
                                             }
                                             if (left < 12) { left = 12; }

                                             this.coords = { top: Math.round(top), left: Math.round(left) };
                                             this.open = true;
                                         }
                                     }">
                                    <button type="button"
                                            @click.stop="toggle($el)"
                                            class="p-1.5 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors cursor-pointer shrink-0"
                                            title="سایر اقدامات">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>

                                    <template x-teleport="body">
                                        <div x-show="open"
                                             data-tenant-dropdown="true"
                                             @click.outside="open = false"
                                             @scroll.window.capture="open = false"
                                             @resize.window="open = false"
                                             @keydown.escape.window="open = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             :style="'top: ' + coords.top + 'px; left: ' + coords.left + 'px;'"
                                             style="display: none;"
                                             dir="rtl"
                                             class="fixed w-52 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/90 dark:border-gray-700/80 shadow-2xl py-1.5 z-[99999] text-xs font-bold divide-y divide-gray-100 dark:divide-gray-700/60">

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
                                                    <span>مشاهده جزئیات سامانه</span>
                                                </a>
                                                <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
                                                   class="flex items-center gap-2 px-3.5 py-2 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors">
                                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>ویرایش اطلاعات سامانه</span>
                                                </a>
                                                @if($tenant->subAccount)
                                                    <a href="{{ route('reseller.sub-accounts.show', $tenant->subAccount->id) }}"
                                                       class="flex items-center gap-2 px-3.5 py-2 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors">
                                                        <svg class="w-4 h-4 text-purple-400" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        <span>مشاهده پرونده کاربر زیرمجموعه</span>
                                                    </a>
                                                @endif
                                            </div>

                                            {{-- Suspend / Unsuspend --}}
                                            @if(!$tenant->isPendingPayment() && auth()->user()->can('resellerhub.tenants.toggle-status'))
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
                                                            class="w-full flex items-center gap-2 px-3.5 py-2 text-start transition-colors cursor-pointer {{ $tenant->status === 'suspended' ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                                                        @if($tenant->status === 'suspended')
                                                            <svg class="w-4 h-4 shrink-0" fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                            </svg>
                                                            <span>رفع مسدودیت سامانه</span>
                                                        @else
                                                            <svg class="w-4 h-4 shrink-0" fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                                                            </svg>
                                                            <span>مسدودسازی موقت سامانه</span>
                                                        @endif
                                                    </button>
                                                </div>
                                            @endif

                                            {{-- Delete Actions Section --}}
                                            <div class="py-1">
                                                {{-- 1. Delete Tenant --}}
                                                @can('resellerhub.tenants.delete')
                                                    <button type="button"
                                                            id="btn-tenant-delete-{{ $tenant->id }}"
                                                            @click="open = false; openTenantDeleteModal({
                                                                id: {{ $tenant->id }},
                                                                name: '{{ addslashes($tenant->name ?: $tenant->subdomain) }}',
                                                                subdomain: '{{ addslashes($displayUrl) }}',
                                                                delete_url: '{{ route('reseller.tenants.destroy', $tenant->id) }}'
                                                            })"
                                                            class="w-full flex items-center gap-2 px-3.5 py-2 text-start text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        <span>حذف این سامانه</span>
                                                    </button>
                                                @endcan

                                                {{-- 2. Delete SubAccount (If attached to buyer) --}}
                                                @if($tenant->subAccount && auth()->user()->can('resellerhub.sub-accounts.delete'))
                                                    @php
                                                        $subAcc = $tenant->subAccount;
                                                        $connTenants = $subAcc->getConnectedTenants();
                                                    @endphp
                                                    <button type="button"
                                                            id="btn-subaccount-delete-tenant-{{ $tenant->id }}"
                                                            @click="open = false; openSubAccountDeleteModal({
                                                                id: {{ $subAcc->id }},
                                                                name: '{{ addslashes($subAcc->name) }}',
                                                                company: '{{ addslashes($subAcc->company_name ?? '') }}',
                                                                tenants_count: {{ $connTenants->count() }},
                                                                tenants: {{ json_encode($connTenants->map(fn($t) => [
                                                                    'id' => $t->id,
                                                                    'name' => $t->name ?: $t->subdomain,
                                                                    'subdomain' => $t->subdomain,
                                                                    'status' => $t->status,
                                                                ])->values(), JSON_THROW_ON_ERROR) }},
                                                                delete_url: '{{ route('reseller.sub-accounts.destroy', $subAcc->id) }}'
                                                            })"
                                                            class="w-full flex items-center gap-2 px-3.5 py-2 text-start text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors cursor-pointer border-t border-gray-100 dark:border-gray-700/60">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM16 11h6m-3-3v6"/>
                                                        </svg>
                                                        <span>حذف کاربر زیرمجموعه</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if(isset($unassignedSubAccounts) && $unassignedSubAccounts->isNotEmpty())
                    @foreach($unassignedSubAccounts as $unassigned)
                        <tr class="bg-amber-50/15 dark:bg-amber-950/10 hover:bg-amber-50/30 transition-all duration-200"
                            id="row-unassigned-{{ $unassigned->id }}">
                            <td class="py-4 px-4 sm:px-5 relative">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('reseller.sub-accounts.show', $unassigned->id) }}"
                                           class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition-colors truncate">
                                            {{ $unassigned->company_name ?: $unassigned->name }}
                                        </a>
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20">
                                            کاربر زیرمجموعه
                                        </span>
                                    </div>
                                    <div
                                        class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>در انتظار تعریف یا تخصیص سامانه</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 sm:px-5">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 ring-1 ring-inset ring-emerald-600/20 shadow-2xs">
                                    <span>فعال</span>
                                </span>
                            </td>
                            <td class="py-4 px-4 sm:px-5">
                                <div class="flex items-center gap-2.5 whitespace-nowrap text-gray-400">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-gray-50/60 dark:bg-gray-800/30 text-gray-400 flex items-center justify-center shrink-0 border border-dashed border-gray-200 dark:border-gray-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-gray-400 block">—</span>
                                        <span class="text-[11px] text-gray-400 block">فاقد اعتبار</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 sm:px-5">
                                <div class="flex items-center gap-2.5 whitespace-nowrap text-gray-400">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-gray-50/60 dark:bg-gray-800/30 text-gray-400 flex items-center justify-center shrink-0 border border-dashed border-gray-200 dark:border-gray-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-gray-400 block">تخصیص‌ نیافته</span>
                                        <span class="text-[11px] text-gray-400 block">بدون اشتراک</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 sm:px-5">
                                <span class="text-xs text-gray-400">—</span>
                            </td>
                            <td class="py-4 px-4 sm:px-5 text-end whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2 shrink-0">
                                    @can('resellerhub.tenants.create')
                                        <a href="{{ route('reseller.tenants.create', ['sub_account_id' => $unassigned->id]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs"
                                           title="ساخت سامانه برای این کاربر">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <span>تخصیص سامانه</span>
                                        </a>
                                    @endcan

                                    @can('resellerhub.sub-accounts.delete')
                                        <button type="button"
                                                id="btn-subaccount-delete-unassigned-{{ $unassigned->id }}"
                                                @click="openSubAccountDeleteModal({
                                                    id: {{ $unassigned->id }},
                                                    name: '{{ addslashes($unassigned->name) }}',
                                                    company: '{{ addslashes($unassigned->company_name ?? '') }}',
                                                    tenants_count: 0,
                                                    tenants: [],
                                                    delete_url: '{{ route('reseller.sub-accounts.destroy', $unassigned->id) }}'
                                                })"
                                                class="p-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-600 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 dark:text-purple-400 transition cursor-pointer"
                                                title="حذف این کاربر زیرمجموعه">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (< md) --}}
        <div class="md:hidden p-3.5 sm:p-4 space-y-3.5 bg-slate-50/60 dark:bg-gray-900/40 border-t border-slate-200/70 dark:border-gray-700/60">
            @foreach($tenants as $tenant)
                @php
                    $tenantUrl = route('reseller.tenants.login-as', $tenant->id);
                    $displayUrl = $tenantManager->getTenantDisplayUrl($tenant);
                    $fullTenantUrl = $tenantManager->getTenantUrl($tenant);
                    $tPackage = $tenant->package;
                    $tPriceType = $tPackage?->getPriceTypeEnum();
                    $canRenew = $tPackage ? $tPriceType->hasRenewal() : true;
                    $tPeriods = $canRenew ? reseller_tenant_renewal_periods($tenant) : [];
                    $daysLeft = reseller_days_remaining($tenant->plan_expires_at);

                    $mBarColorClass = 'bg-emerald-500';
                    $mDaysTextColor = 'text-gray-700 dark:text-gray-300';
                    if ($daysLeft !== null && $daysLeft <= 7) {
                        $mBarColorClass = 'bg-rose-500';
                        $mDaysTextColor = 'text-rose-600 dark:text-rose-400';
                    } elseif ($daysLeft !== null && $daysLeft <= 30) {
                        $mBarColorClass = 'bg-amber-500';
                        $mDaysTextColor = 'text-amber-600 dark:text-amber-400';
                    }

                    $mDaysText = '—';
                    if ($daysLeft !== null) {
                        $mDaysText = $daysLeft > 0 ? reseller_fa_num($daysLeft) . ' روز مانده' : 'منقضی شده';
                    }

                    $mPkgColor = $tPackage ? PackageColor::fromOrDefault($tPackage->color) : null;
                    $mPkgIcon = $tPackage ? PackageIcon::fromOrDefault($tPackage->icon) : null;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200/80 dark:border-gray-700/80 shadow-xs hover:border-indigo-200 dark:hover:border-indigo-800/60 transition-all overflow-hidden" id="card-tenant-{{ $tenant->id }}">
                    {{-- Header Partition: Title & Status --}}
                    <div class="p-3.5 sm:p-4 bg-slate-50/50 dark:bg-gray-800/50 border-b border-slate-100 dark:border-gray-700/50 flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                               class="font-bold text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block">
                                {{ $tenant->name ?: $tenant->subdomain }}
                            </a>
                            <div x-data="{ copied: false }" class="flex items-center gap-1.5 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <span class="truncate">{{ $displayUrl }}</span>
                                <button type="button"
                                        @click="
                                            copyText('{{ $fullTenantUrl }}', 'آدرس اختصاصی سامانه با موفقیت کپی شد');
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="p-1 rounded-lg transition-all duration-200 cursor-pointer relative overflow-hidden active:scale-90 shrink-0"
                                        :class="copied ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-300 ring-2 ring-emerald-500/40 shadow-xs scale-105' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'"
                                        :title="copied ? 'کپی شد!' : 'کپی آدرس اختصاصی سامانه'">
                                    <span x-show="copied" x-cloak
                                          class="absolute inset-0 rounded-lg bg-emerald-500/25 animate-ping pointer-events-none"></span>
                                    <svg x-show="!copied"
                                         class="w-3.5 h-3.5 transition-transform duration-200"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak
                                         class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 transition-transform duration-300 transform scale-110"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div class="shrink-0">
                            @if($tenant->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 ring-1 ring-inset ring-emerald-600/20">
                                    <span>فعال</span>
                                </span>
                            @elseif($tenant->status === 'suspended')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 ring-1 ring-inset ring-rose-600/20">
                                    <span>معلق</span>
                                </span>
                            @elseif($tenant->status === 'pending_payment')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 ring-1 ring-inset ring-amber-600/20">
                                    <span>در انتظار پرداخت</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    <span>منقضی</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Body Partition: Package & Expiration --}}
                    <div class="p-3.5 sm:p-4 flex items-center justify-between text-xs gap-3">
                        <div class="flex items-center gap-1.5">
                            @if($tPackage && $mPkgColor && $mPkgIcon)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $mPkgColor->lightClass() }}">
                                    {!! $mPkgIcon->svg('w-3.5 h-3.5') !!}
                                    <span>{{ $tPackage->name }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-gradient-to-r from-violet-50 to-indigo-50 dark:from-violet-950/50 dark:to-indigo-950/50 text-violet-700 dark:text-violet-300 border border-violet-200/80 dark:border-violet-800/60 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-violet-600 dark:text-violet-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>پکیج سفارشی</span>
                                </span>
                            @endif
                        </div>

                        <div class="text-start">
                            @if($tenant->plan_expires_at)
                                <span class="font-bold tabular-nums {{ $mDaysTextColor }}">
                                    {{ $mDaysText }}
                                </span>
                            @else
                                <span class="text-gray-400 font-medium">نامحدود</span>
                            @endif
                        </div>
                    </div>

                    {{-- Footer Partition: Complete 8-Action Operations on Mobile --}}
                    <div class="p-3 sm:p-3.5 bg-slate-50/70 dark:bg-gray-900/30 border-t border-slate-100 dark:border-gray-700/60 space-y-2">
                        {{-- Row 1: Primary SSO & Renewal Actions --}}
                        <div class="flex items-center gap-2">
                            @if(!$tenant->isPendingPayment())
                                <form method="POST" action="{{ $tenantUrl }}" target="_blank" class="flex-1 m-0 p-0">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white dark:bg-indigo-950/60 dark:hover:bg-indigo-600 dark:text-indigo-300 dark:hover:text-white border border-indigo-100 dark:border-indigo-900/40 text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer whitespace-nowrap"
                                            title="ورود مستقیم به پنل مدیریت سامانه (SSO)">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>ورود سریع</span>
                                    </button>
                                </form>
                            @endif

                            @if($canRenew && !$tenant->isPendingPayment() && auth()->user()->can('resellerhub.tenants.renew'))
                                <button type="button"
                                        id="btn-renew-m-{{ $tenant->id }}"
                                        @click="openRenewModalByElement($el)"
                                        data-id="{{ $tenant->id }}"
                                        data-name="{{ e($tenant->name) }}"
                                        data-subdomain="{{ e($displayUrl) }}"
                                        data-package="{{ e($tPackage?->name ?? '') }}"
                                        data-expires="{{ e($tenant->jalali_plan_expires_at ?? 'نامحدود') }}"
                                        data-periods="{{ e(json_encode($tPeriods, JSON_THROW_ON_ERROR)) }}"
                                        data-url="{{ route('reseller.tenants.renew', $tenant->id) }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white dark:bg-emerald-950/60 dark:hover:bg-emerald-600 dark:text-emerald-300 dark:hover:text-white border border-emerald-100 dark:border-emerald-900/40 text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95 whitespace-nowrap"
                                        title="تمدید سفارش">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>تمدید سفارش</span>
                                </button>
                            @endif
                        </div>

                        {{-- Row 2: Secondary Details, Edit & More Operations Dropdown --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('reseller.tenants.show', $tenant->id) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 border border-slate-200/80 dark:border-slate-600 text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95 whitespace-nowrap"
                               title="مشاهده جزئیات سامانه">
                                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>جزئیات</span>
                            </a>

                            <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/40 text-xs font-bold transition-all shadow-2xs cursor-pointer active:scale-95 whitespace-nowrap"
                               title="ویرایش اطلاعات سامانه">
                                <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>ویرایش</span>
                            </a>

                            {{-- 3-Dots Menu Button --}}
                            <div class="relative inline-flex items-center text-start shrink-0"
                                 x-data="{
                                     open: false,
                                     coords: { top: 0, left: 0 },
                                     toggle(el) {
                                         if (this.open) { this.open = false; return; }
                                         const rect = el.getBoundingClientRect();
                                         const menuWidth = 220;
                                         const menuHeight = 220;
                                         const margin = 6;
                                         const spaceBelow = window.innerHeight - rect.bottom;
                                         const spaceAbove = rect.top;

                                         let top = (spaceBelow >= menuHeight + margin || spaceBelow >= spaceAbove)
                                             ? (rect.bottom + margin)
                                             : (rect.top - menuHeight - margin);

                                         let left = rect.left;
                                         if (left + menuWidth > window.innerWidth - 12) {
                                             left = window.innerWidth - menuWidth - 12;
                                         }
                                         if (left < 12) { left = 12; }

                                         this.coords = { top: Math.round(top), left: Math.round(left) };
                                         this.open = true;
                                     }
                                 }">
                                <button type="button"
                                        @click.stop="toggle($el)"
                                        class="p-2 rounded-xl border border-gray-200/80 dark:border-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-300 transition-colors cursor-pointer shrink-0 shadow-2xs active:scale-95"
                                        title="سایر اقدامات">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>

                            <template x-teleport="body">
                                <div x-show="open"
                                     data-tenant-dropdown="true"
                                     @click.outside="open = false"
                                     @scroll.window.capture="open = false"
                                     @resize.window="open = false"
                                     @keydown.escape.window="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     :style="'top: ' + coords.top + 'px; left: ' + coords.left + 'px;'"
                                     style="display: none;"
                                     dir="rtl"
                                     class="fixed w-56 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/90 dark:border-gray-700/80 shadow-2xl py-1.5 z-[99999] text-xs font-bold divide-y divide-gray-100 dark:divide-gray-700/60">

                                    {{-- SubAccount Profile Link --}}
                                    @if($tenant->subAccount)
                                        <div class="py-1">
                                            <a href="{{ route('reseller.sub-accounts.show', $tenant->subAccount->id) }}"
                                               class="flex items-center gap-2 px-3.5 py-2 text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors">
                                                <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <span>مشاهده پرونده کاربر خریدار</span>
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Suspend / Unsuspend --}}
                                    @if(!$tenant->isPendingPayment() && auth()->user()->can('resellerhub.tenants.toggle-status'))
                                        <div class="py-1">
                                            <button type="button"
                                                    id="btn-toggle-m-{{ $tenant->id }}"
                                                    @click="open = false; openSuspendModalByElement($el)"
                                                    data-id="{{ $tenant->id }}"
                                                    data-name="{{ e($tenant->name) }}"
                                                    data-subdomain="{{ e($displayUrl) }}"
                                                    data-owner="{{ e($tenant->subAccount?->name ?? '—') }}"
                                                    data-suspended="{{ $tenant->status === 'suspended' ? 'true' : 'false' }}"
                                                    data-url="{{ route('reseller.tenants.toggle-status', $tenant->id) }}"
                                                    class="w-full flex items-center gap-2 px-3.5 py-2 text-start transition-colors cursor-pointer {{ $tenant->status === 'suspended' ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                                                @if($tenant->status === 'suspended')
                                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>رفع مسدودیت سامانه</span>
                                                @else
                                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                                                    </svg>
                                                    <span>مسدودسازی موقت سامانه</span>
                                                @endif
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Delete Actions Section --}}
                                    <div class="py-1">
                                        {{-- 1. Delete Tenant --}}
                                        @can('resellerhub.tenants.delete')
                                            <button type="button"
                                                    id="btn-tenant-delete-m-{{ $tenant->id }}"
                                                    @click="open = false; openTenantDeleteModal({
                                                        id: {{ $tenant->id }},
                                                        name: '{{ addslashes($tenant->name ?: $tenant->subdomain) }}',
                                                        subdomain: '{{ addslashes($displayUrl) }}',
                                                        delete_url: '{{ route('reseller.tenants.destroy', $tenant->id) }}'
                                                    })"
                                                    class="w-full flex items-center gap-2 px-3.5 py-2 text-start text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>حذف این سامانه</span>
                                            </button>
                                        @endcan

                                        {{-- 2. Delete SubAccount (If attached to buyer) --}}
                                        @if($tenant->subAccount && auth()->user()->can('resellerhub.sub-accounts.delete'))
                                            @php
                                                $mSubAcc = $tenant->subAccount;
                                                $mConnTenants = $mSubAcc->getConnectedTenants();
                                            @endphp
                                            <button type="button"
                                                    id="btn-subaccount-delete-m-{{ $tenant->id }}"
                                                    @click="open = false; openSubAccountDeleteModal({
                                                        id: {{ $mSubAcc->id }},
                                                        name: '{{ addslashes($mSubAcc->name) }}',
                                                        company: '{{ addslashes($mSubAcc->company_name ?? '') }}',
                                                        tenants_count: {{ $mConnTenants->count() }},
                                                        tenants: {{ json_encode($mConnTenants->map(fn($t) => [
                                                            'id' => $t->id,
                                                            'name' => $t->name ?: $t->subdomain,
                                                            'subdomain' => $t->subdomain,
                                                            'status' => $t->status,
                                                        ])->values(), JSON_THROW_ON_ERROR) }},
                                                        delete_url: '{{ route('reseller.sub-accounts.destroy', $mSubAcc->id) }}'
                                                    })"
                                                    class="w-full flex items-center gap-2 px-3.5 py-2 text-start text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors cursor-pointer border-t border-gray-100 dark:border-gray-700/60">
                                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM16 11h6m-3-3v6"/>
                                                </svg>
                                                <span>حذف کاربر زیرمجموعه</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            @if(isset($unassignedSubAccounts) && $unassignedSubAccounts->isNotEmpty())
                @foreach($unassignedSubAccounts as $unassigned)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-amber-200/80 dark:border-amber-800/60 shadow-xs overflow-hidden" id="card-unassigned-{{ $unassigned->id }}">
                        <div class="p-3.5 sm:p-4 bg-amber-50/40 dark:bg-amber-950/20 border-b border-amber-100 dark:border-amber-900/40 flex items-center justify-between">
                            <a href="{{ route('reseller.sub-accounts.show', $unassigned->id) }}" class="font-bold text-sm text-gray-900 dark:text-white hover:text-purple-600 transition">
                                {{ $unassigned->company_name ?: $unassigned->name }}
                            </a>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20">
                                کاربر زیرمجموعه
                            </span>
                        </div>
                        <div class="p-3.5 sm:p-4 flex items-center justify-between gap-3">
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">در انتظار تعریف یا تخصیص سامانه</p>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('reseller.tenants.create', ['sub_account_id' => $unassigned->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs active:scale-95 whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>تخصیص سامانه</span>
                                </a>
                                @can('resellerhub.sub-accounts.delete')
                                    <button type="button"
                                            id="btn-subaccount-delete-unassigned-m-{{ $unassigned->id }}"
                                            @click="openSubAccountDeleteModal({
                                                id: {{ $unassigned->id }},
                                                name: '{{ addslashes($unassigned->name) }}',
                                                company: '{{ addslashes($unassigned->company_name ?? '') }}',
                                                tenants_count: 0,
                                                tenants: [],
                                                delete_url: '{{ route('reseller.sub-accounts.destroy', $unassigned->id) }}'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold transition border border-rose-200/60 dark:border-rose-900/40 active:scale-95 cursor-pointer whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>حذف</span>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Pagination Footer --}}
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
                        سامانه
                    </span>

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
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        <span>تعداد در صفحه:</span>
                        <select name="per_page" aria-label="تعداد در صفحه" onchange="this.form.submit()"
                                class="rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1 px-2 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                            @foreach([10, 15, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>
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
