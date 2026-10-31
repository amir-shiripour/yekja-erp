@php
    use Modules\DirectAdmin\Entities\DaAccount;

    $baseAccountsQuery = \Illuminate\Support\Facades\Schema::hasColumn('da_accounts', 'client_id')
        ? DaAccount::where('client_id', $client->id)
        : null;

    $totalAccountsCount     = $baseAccountsQuery ? (clone $baseAccountsQuery)->count() : 0;
    $activeAccountsCount    = $baseAccountsQuery ? (clone $baseAccountsQuery)->where('suspended', false)->count() : 0;
    $suspendedAccountsCount = $baseAccountsQuery ? (clone $baseAccountsQuery)->where('suspended', true)->count() : 0;
    $totalDiskUsage         = $baseAccountsQuery ? (clone $baseAccountsQuery)->sum('disk_usage_bytes') : 0;

    $clientAccounts = $baseAccountsQuery
        ? (clone $baseAccountsQuery)->with('server')->paginate(10, ['*'], 'hosting_page')->withQueryString()->fragment('directadmin')
        : collect();

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    if (!function_exists('formatClientHostingBytes')) {
        function formatClientHostingBytes($bytes, $precision = 1) {
            if ($bytes == -1 || $bytes === 'unlimited' || strtolower((string)$bytes) === 'unlimited') return 'نامحدود';
            if ($bytes <= 0) return '۰ MB';
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $val = $bytes / pow(1024, $pow);
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $formatted = str_replace(range(0, 9), $persian, (string)round($val, $precision));
            return $formatted . ' ' . $units[$pow];
        }
    }

    $statCardClass = "rounded-3xl border p-5 flex items-center gap-5 overflow-hidden transition-all duration-300 hover:shadow-md";
@endphp

<div class="p-6 sm:p-8 space-y-6" x-data="{ accountSearch: '', accountStatus: '' }">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
            </span>
            <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span>اکانت‌های میزبانی و هاستینگ</span>
                    @if($totalAccountsCount > 0)
                        <span
                            class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-xs font-black">
                            {{ $faNum($totalAccountsCount) }} اکانت
                        </span>
                    @endif
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    مدیریت حساب‌های میزبانی وب، سهمیه دیسک، وضعیت تعلیق و سرورهای متصل به این مشتری
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @can('directadmin.accounts.create')
                <a href="{{ route('user.directadmin.accounts.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-xs font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ایجاد اکانت جدید</span>
                </a>
            @endcan
            @can('directadmin.accounts.view')
                <a href="{{ route('user.directadmin.accounts.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>مدیریت کل اکانت‌ها</span>
                </a>
            @endcan
        </div>
    </div>

    @if($totalAccountsCount > 0)
        {{-- Hosting Summary Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">کل اکانت‌ها</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum($totalAccountsCount) }}</span>
                        <span class="text-[11px] font-medium text-gray-400 ms-1">اکانت</span>
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
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">اکانت‌های فعال</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $faNum($activeAccountsCount) }}</span>
                        <span class="text-[11px] font-medium text-emerald-500/80 ms-1">اکانت</span>
                    </div>
                </div>
            </div>
            <div class="{{ $statCardClass }} bg-purple-50/60 dark:bg-purple-500/5 border-purple-100 dark:border-purple-500/20 shadow-sm">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wide block mb-1 truncate">فضای مصرفی دیسک</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span class="text-xl xl:text-2xl font-black text-purple-700 dark:text-purple-400 tabular-nums">{{ formatClientHostingBytes($totalDiskUsage) }}</span>
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
                    <input type="text" x-model="accountSearch" placeholder="جستجوی نام کاربری، دامنه، پکیج یا سرور..." class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <select x-model="accountStatus" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active">فعال (Active)</option>
                    <option value="suspended">معلق (Suspended)</option>
                </select>
            </div>
        </div>

        {{-- Accounts Table --}}
        <div class="overflow-hidden rounded-3xl border border-gray-100 dark:border-gray-700/50 bg-white dark:bg-gray-800/60 shadow-sm backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider select-none">
                    <tr>
                        <th class="px-6 py-5 text-start">کاربر و دامنه اصلی</th>
                        <th class="px-6 py-5 text-start">سرور میزبانی</th>
                        <th class="px-6 py-5 text-center">پکیج</th>
                        <th class="px-6 py-5 text-center">مصرف دیسک</th>
                        <th class="px-6 py-5 text-center">وضعیت</th>
                        <th class="px-6 py-5 text-end">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($clientAccounts as $acc)
                        @php
                            $accStatus = $acc->suspended ? 'suspended' : 'active';
                            $diskUsed = $acc->disk_usage_bytes;
                            $diskLimit = $acc->disk_limit_bytes;
                            $diskUnlimited = ($diskLimit == -1 || $diskLimit === 'unlimited' || strtolower((string)$diskLimit) === 'unlimited');
                            $diskPercent = (!$diskUnlimited && $diskLimit > 0) ? min(round(($diskUsed / $diskLimit) * 100, 1), 100) : 0;
                            $serverName = $acc->server->name ?? '—';
                            $searchKey = strtolower($acc->username . ' ' . $acc->domain . ' ' . $acc->package . ' ' . $serverName);
                        @endphp
                        <tr x-show="(!accountSearch || '{{ addslashes($searchKey) }}'.includes(accountSearch.toLowerCase())) && (!accountStatus || '{{ $accStatus }}' === accountStatus)"
                            class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            {{-- User / Domain --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100/50 dark:border-indigo-500/20 uppercase shadow-xs">
                                        {{ mb_substr($acc->username, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('user.directadmin.accounts.show', $acc->username) }}"
                                           class="font-bold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-base block tracking-wide"
                                           dir="ltr">
                                            {{ $acc->username }}
                                        </a>
                                        <div class="flex items-center gap-1.5 mt-0.5" dir="ltr">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate block">
                                                {{ $acc->domain }}
                                            </span>
                                            <a href="http://{{ $acc->domain }}" target="_blank" rel="noopener"
                                               class="p-0.5 rounded text-gray-300 hover:text-indigo-500 transition-colors shrink-0"
                                               title="مشاهده وب‌سایت در تب جدید">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Server --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                    <span class="font-bold text-sm">{{ $serverName }}</span>
                                </div>
                            </td>

                            {{-- Package --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl border border-gray-200/60 dark:border-gray-600/50">
                                    {{ $acc->package ?? 'پیش‌فرض' }}
                                </span>
                            </td>

                            {{-- Disk Usage with Progress Bar --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1.5 w-44 mx-auto">
                                    <div class="flex justify-between text-xs font-bold text-gray-600 dark:text-gray-400 tabular-nums">
                                        <span class="text-gray-900 dark:text-gray-200">{{ formatClientHostingBytes($diskUsed) }}</span>
                                        <span class="text-gray-400">/ {{ $diskUnlimited ? 'نامحدود' : formatClientHostingBytes($diskLimit) }}</span>
                                    </div>
                                    @if(!$diskUnlimited)
                                        <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 {{ $diskPercent > 90 ? 'bg-rose-500' : ($diskPercent > 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ $diskPercent }}%"></div>
                                        </div>
                                    @else
                                        <div class="w-full h-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: 100%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($acc->suspended)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        معلق (Suspended)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        فعال (Active)
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <a href="{{ route('user.directadmin.accounts.show', $acc->username) }}"
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
            @if($clientAccounts instanceof \Illuminate\Pagination\AbstractPaginator && $clientAccounts->total() > 0)
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            نمایش
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($clientAccounts->firstItem() ?? 0) }}</strong>
                            تا
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($clientAccounts->lastItem() ?? 0) }}</strong>
                            از
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($clientAccounts->total()) }}</strong>
                            اکانت
                        </span>
                    </div>

                    @if($clientAccounts->hasPages())
                        <div>
                            {{ $clientAccounts->withQueryString()->fragment('directadmin')->links('directadmin::partials.pagination') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="p-12 rounded-3xl bg-gray-50/60 dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
            </div>
            <div>
                <h4 class="text-base font-black text-gray-800 dark:text-gray-200">هیچ اکانت هاستینگی برای این مشتری ثبت نشده است</h4>
                <p class="text-xs text-gray-400 mt-1">جهت اتصال اکانت، از بخش مدیریت اکانت‌ها هاست را به این مشتری متصل کنید.</p>
            </div>
            @can('directadmin.accounts.create')
                <a href="{{ route('user.directadmin.accounts.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ایجاد اکانت جدید</span>
                </a>
            @endcan
        </div>
    @endif
</div>
