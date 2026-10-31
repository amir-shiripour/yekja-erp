@php
    use Modules\DomainManager\Entities\DomainRecord;
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;

    $baseDomainsQuery = \Illuminate\Support\Facades\Schema::hasColumn('domain_records', 'client_id')
        ? DomainRecord::where('client_id', $client->id)
        : null;

    $totalDomainsCount   = $baseDomainsQuery ? (clone $baseDomainsQuery)->count() : 0;
    $activeDomainsCount  = $baseDomainsQuery ? (clone $baseDomainsQuery)->where('status', 'active')->count() : 0;
    $expiredDomainsCount = $baseDomainsQuery ? (clone $baseDomainsQuery)->where('status', 'expired')->count() : 0;
    $pendingDomainsCount = $baseDomainsQuery ? (clone $baseDomainsQuery)->where('status', 'pending')->count() : 0;

    $clientDomains = $baseDomainsQuery
        ? (clone $baseDomainsQuery)->with('reseller')->paginate(10, ['*'], 'domains_page')->withQueryString()->fragment('domains')
        : collect();

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $statCardClass = "rounded-3xl border p-5 flex items-center gap-5 overflow-hidden transition-all duration-300 hover:shadow-md";
@endphp

<div class="p-6 sm:p-8 space-y-6" x-data="{ domainSearch: '', domainStatus: '' }">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </span>
            <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span>دامنه‌های ثبت شده مشتری</span>
                    @if($totalDomainsCount > 0)
                        <span
                            class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-xs font-black">
                            {{ $faNum($totalDomainsCount) }} دامنه
                        </span>
                    @endif
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    مدیریت و مشاهده دامنه‌های فعال، وضعیت انقضا، دی‌ان‌اس‌ها و کارگزار متصل به این مشتری
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @can('domainmanager.domains.register')
                <a href="{{ route('user.domainmanager.search.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت یا انتقال دامنه</span>
                </a>
            @endcan
            @can('domainmanager.view')
                <a href="{{ route('user.domainmanager.domains.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>مدیریت کل دامنه‌ها</span>
                </a>
            @endcan
        </div>
    </div>

    @if($totalDomainsCount > 0)
        {{-- Domains Summary Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">کل دامنه‌ها</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum($totalDomainsCount) }}</span>
                        <span class="text-[11px] font-medium text-gray-400 ms-1">دامنه</span>
                    </div>
                </div>
            </div>
            <div class="{{ $statCardClass }} bg-emerald-50/60 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20 shadow-sm">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">دامنه‌های فعال</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $faNum($activeDomainsCount) }}</span>
                        <span class="text-[11px] font-medium text-emerald-500/80 ms-1">دامنه</span>
                    </div>
                </div>
            </div>
            <div class="{{ $statCardClass }} {{ $expiredDomainsCount > 0 ? 'bg-rose-50/60 dark:bg-rose-500/5 border-rose-100 dark:border-rose-500/20' : 'bg-amber-50/60 dark:bg-amber-500/5 border-amber-100 dark:border-amber-500/20' }} shadow-sm">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl {{ $expiredDomainsCount > 0 ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400' }} shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold {{ $expiredDomainsCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }} uppercase tracking-wide block mb-1 truncate">
                        {{ $expiredDomainsCount > 0 ? 'منقضی شده' : 'در انتظار / غیرفعال' }}
                    </span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black {{ $expiredDomainsCount > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }} tabular-nums">
                            {{ $faNum($expiredDomainsCount > 0 ? $expiredDomainsCount : $pendingDomainsCount) }}
                        </span>
                        <span class="text-[11px] font-medium {{ $expiredDomainsCount > 0 ? 'text-rose-500/80' : 'text-amber-500/80' }} ms-1">دامنه</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-gray-800/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="domainSearch" placeholder="جستجوی نام دامنه یا کارگزار..." class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <select x-model="domainStatus" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active">فعال (Active)</option>
                    <option value="expired">منقضی شده (Expired)</option>
                    <option value="pending">در انتظار (Pending)</option>
                </select>
            </div>
        </div>

        {{-- Domains Table --}}
        <div class="overflow-hidden rounded-3xl border border-gray-100 dark:border-gray-700/50 bg-white dark:bg-gray-800/60 shadow-sm backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider select-none">
                    <tr>
                        <th class="px-6 py-5 text-start">نام دامنه</th>
                        <th class="px-6 py-5 text-start">کارگزار متصل</th>
                        <th class="px-6 py-5 text-center">تاریخ انقضا</th>
                        <th class="px-6 py-5 text-center">وضعیت</th>
                        <th class="px-6 py-5 text-end">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($clientDomains as $domain)
                        @php
                            $st = strtolower($domain->status ?? '');
                            $domainNameLower = strtolower($domain->domain_name);
                            $resellerNameLower = strtolower($domain->reseller->name ?? '');
                            $searchKey = $domainNameLower . ' ' . $resellerNameLower;
                            $jalaliExpiry = $domain->expiry_date ? Jalalian::fromCarbon($domain->expiry_date) : null;
                        @endphp
                        <tr x-show="(!domainSearch || '{{ addslashes($searchKey) }}'.includes(domainSearch.toLowerCase())) && (!domainStatus || '{{ $st }}' === domainStatus)"
                            class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            {{-- Domain Name --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100/50 dark:border-indigo-500/20 shadow-xs">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="2" y1="12" x2="22" y2="12"></line>
                                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('user.domainmanager.domains.show', $domain->domain_name) }}"
                                               class="font-bold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-base select-all tracking-wide"
                                               dir="ltr">
                                                {{ $domain->domain_name }}
                                            </a>
                                            <a href="http://{{ $domain->domain_name }}" target="_blank" rel="noopener"
                                               class="p-1 rounded-lg text-gray-300 hover:text-indigo-500 transition-colors"
                                               title="مشاهده وب‌سایت در تب جدید">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        </div>
                                        @if($domain->privacy_protected)
                                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-0.5">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                مخفی‌سازی WHOIS فعال
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Reseller --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span class="font-bold text-sm">{{ $domain->reseller->name ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Expiry Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($jalaliExpiry)
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 tabular-nums">
                                        {{ $faNum($jalaliExpiry->format('Y/m/d')) }}
                                    </span>
                                    @php
                                        $diffDays = Carbon::now()->diffInDays($domain->expiry_date, false);
                                    @endphp
                                    @if($diffDays < 0)
                                        <span class="block text-[11px] font-bold text-rose-500 mt-0.5">منقضی شده</span>
                                    @elseif($diffDays <= 30)
                                        <span class="block text-[11px] font-bold text-amber-500 mt-0.5">{{ $faNum($diffDays) }} روز تا انقضا</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 font-medium">—</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($st === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        فعال (Active)
                                    </span>
                                @elseif($st === 'expired')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                        منقضی شده
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        در انتظار (Pending)
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <a href="{{ route('user.domainmanager.domains.show', $domain->domain_name) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-50 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-all text-xs font-bold shadow-xs hover:scale-105 active:scale-95">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>مشاهده و مدیریت</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($clientDomains instanceof \Illuminate\Pagination\AbstractPaginator && $clientDomains->total() > 0)
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            نمایش
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($clientDomains->firstItem() ?? 0) }}</strong>
                            تا
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($clientDomains->lastItem() ?? 0) }}</strong>
                            از
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($clientDomains->total()) }}</strong>
                            دامنه
                        </span>
                    </div>

                    @if($clientDomains->hasPages())
                        <div>
                            {{ $clientDomains->withQueryString()->fragment('domains')->links('domainmanager::partials.pagination') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="p-12 rounded-3xl bg-gray-50/60 dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-base font-black text-gray-800 dark:text-gray-200">هیچ دامنه‌ای به این مشتری متصل نشده است</h4>
                <p class="text-xs text-gray-400 mt-1">جهت اتصال دامنه، از صفحه مشخصات دامنه، مشتری مورد نظر را انتخاب و ذخیره کنید.</p>
            </div>
            @can('domainmanager.domains.register')
                <a href="{{ route('user.domainmanager.search.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت دامنه جدید</span>
                </a>
            @endcan
        </div>
    @endif
</div>
