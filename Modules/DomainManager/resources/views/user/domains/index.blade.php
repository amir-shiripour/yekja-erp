@php use Modules\DomainManager\Entities\DomainRecord; @endphp
@extends('layouts.user')

@section('title', 'مدیریت و لیست دامنه‌ها')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    if (!function_exists('domainSortUrl')) {
        function domainSortUrl($col) {
            $currentCol = request('sort_by');
            $currentOrder = strtolower(request('sort_order', 'desc'));
            $newOrder = ($currentCol === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort_by' => $col, 'sort_order' => $newOrder]);
        }
    }
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative"
         x-data="{
        renewModal: {
            open: false,
            domainName: '',
            resellerName: '',
            expiryDateFa: '',
            expiryDateRaw: null,
            years: '1',
            actionUrl: '',
            submitting: false
        },
        openRenewModal(dom) {
            this.renewModal.domainName = dom.domain_name;
            this.renewModal.resellerName = dom.reseller_name || 'کارگزار متصل';
            this.renewModal.expiryDateFa = dom.expiry_fa || 'نامشخص';
            this.renewModal.expiryDateRaw = dom.expiry_raw || null;
            this.renewModal.years = '1';
            this.renewModal.actionUrl = dom.renew_url;
            this.renewModal.submitting = false;
            this.renewModal.open = true;
        },
        closeRenewModal() {
            if (this.renewModal.submitting) return;
            this.renewModal.open = false;
        }
     }">

        {{-- Reusable Real-time Toast Notifications --}}
        @include('domainmanager::partials.toast')

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path
                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </span>
                    <span>مدیریت و لیست دامنه‌ها</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 ms-1">
                    تغییر دی‌ان‌اس‌ها، مخفی‌سازی مشخصات WHOIS، دریافت کد انتقال دامنه (EPP) و تمدید آنلاین
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.domainmanager.dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                    </svg>
                    <span>پیشخوان دامنه</span>
                </a>

                @can('domainmanager.domains.register')
                    <a href="{{ route('user.domainmanager.search.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>ثبت یا انتقال دامنه جدید</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- Flash Session Messages --}}
        @if(session('status'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3 shadow-2xs animate-fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Quick Stats Cards --}}
        @php
            $totalDomCount = DomainRecord::count();
            $activeDomCount = DomainRecord::where('status', 'active')->count();
            $expiredDomCount = DomainRecord::where('status', 'expired')->count();
            $pendingDomCount = DomainRecord::where('status', 'pending')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">کل دامنه‌ها</span>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $faNum($totalDomCount) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path
                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">دامنه‌های فعال</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $faNum($activeDomCount) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">دامنه‌های منقضی</span>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $faNum($expiredDomCount) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">در حال انتقال</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $faNum($pendingDomCount) }}</p>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Filters Form --}}
        <form method="GET"
              class="bg-white dark:bg-gray-800/60 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                {{-- Search Input --}}
                <div class="relative lg:col-span-4">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="جستجوی نام دامنه یا مشتری..."
                           class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white text-start font-medium"
                           dir="ltr">
                </div>

                {{-- Reseller Filter --}}
                <div class="lg:col-span-3">
                    <select name="reseller_id"
                            class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                        <option value="">همه کارگزاران</option>
                        @foreach($resellers as $res)
                            <option value="{{ $res->id }}" @selected(request('reseller_id') == $res->id)>
                                {{ $res->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="lg:col-span-3">
                    <select name="status"
                            class="w-full rounded-xl border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer font-bold text-xs">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active" @selected(request('status') === 'active')>فعال (Active)</option>
                        <option value="expired" @selected(request('status') === 'expired')>منقضی شده (Expired)</option>
                        <option value="pending" @selected(request('status') === 'pending')>در حال انتقال (Pending)
                        </option>
                    </select>
                </div>

                {{-- Filter Action Buttons --}}
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all cursor-pointer">
                        اعمال فیلتر
                    </button>
                    @if(request()->anyFilled(['q', 'reseller_id', 'status', 'sort_by']))
                        <a href="{{ route('user.domainmanager.domains.index') }}"
                           class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all shrink-0 cursor-pointer"
                           title="پاکسازی فیلترها">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Domains Table Card --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
            @if($domains->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                        <tr>
                            <th class="px-6 py-4 text-start">
                                <a href="{{ domainSortUrl('domain') }}"
                                   class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                    <span>نام دامنه</span>
                                    <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-start">
                                <a href="{{ domainSortUrl('client') }}"
                                   class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                    <span>مشتری CRM</span>
                                    <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-start">کارگزار متصل</th>
                            <th class="px-6 py-4 text-center">شناسه سفارش</th>
                            <th class="px-6 py-4 text-center">
                                <a href="{{ domainSortUrl('status') }}"
                                   class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                    <span>وضعیت</span>
                                    <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-start">
                                <a href="{{ domainSortUrl('created') }}"
                                   class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                    <span>تاریخ ثبت</span>
                                    <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-start">
                                <a href="{{ domainSortUrl('expiry') }}"
                                   class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                    <span>تاریخ انقضا</span>
                                    <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-start">
                                <a href="{{ domainSortUrl('synced') }}"
                                   class="inline-flex items-center gap-1.5 hover:text-indigo-600 transition-colors group">
                                    <span>آخرین همگام‌سازی</span>
                                    <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-4 text-end">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach($domains as $dom)
                            @php
                                $domJsData = [
                                    'domain_name'   => $dom->domain_name,
                                    'reseller_name' => $dom->reseller->name ?? '—',
                                    'expiry_fa'     => $dom->expiry_date ? $faNum(jdate($dom->expiry_date)->format('Y/m/d')) : 'نامشخص',
                                    'expiry_raw'    => $dom->expiry_date ? $dom->expiry_date->format('Y-m-d') : null,
                                    'renew_url'     => route('user.domainmanager.domains.renew', $dom->domain_name)
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                                {{-- Domain Name --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ route('user.domainmanager.domains.show', $dom->domain_name) }}"
                                               class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block text-sm select-all"
                                               dir="ltr">
                                                {{ $dom->domain_name }}
                                            </a>
                                            @if($dom->privacy_protected)
                                                <span
                                                    class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-0.5">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    مخفی‌سازی WHOIS
                                                </span>
                                            @endif
                                        </div>
                                        <a href="http://{{ $dom->domain_name }}" target="_blank" rel="noopener"
                                           class="p-1 rounded-lg text-gray-300 hover:text-indigo-500 transition-colors"
                                           title="مشاهده وب‌سایت در تب جدید">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>

                                {{-- Client / Customer --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($dom->client)
                                        <a href="{{ route('user.clients.show', $dom->client->id) }}"
                                           class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-2">
                                            <span
                                                class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                                {{ mb_substr($dom->client->full_name, 0, 1) }}
                                            </span>
                                            <span>{{ $dom->client->full_name }}</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 font-medium">—</span>
                                    @endif
                                </td>

                                {{-- Reseller --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                        <span class="font-bold">{{ $dom->reseller->name ?? '—' }}</span>
                                    </div>
                                </td>

                                {{-- Order ID --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-lg border border-gray-200/60 dark:border-gray-600/50"
                                        dir="ltr">
                                        {{ $dom->order_id ? $faNum($dom->order_id) : '—' }}
                                    </span>
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $st = strtolower($dom->status ?? '');
                                    @endphp
                                    @if($st === 'active')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            فعال (Active)
                                        </span>
                                    @elseif($st === 'expired')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            منقضی شده
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            در انتظار (Pending)
                                        </span>
                                    @endif
                                </td>

                                {{-- Creation Date --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-600 dark:text-gray-300">
                                    {{ $dom->creation_date ? $faNum(jdate($dom->creation_date)->format('Y/m/d')) : '—' }}
                                </td>

                                {{-- Expiry Date --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($dom->expiry_date)
                                        @php
                                            $isNearExpiry = now()->diffInDays($dom->expiry_date, false) <= 30;
                                        @endphp
                                        <span
                                            class="{{ $isNearExpiry ? 'font-black text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300 font-bold' }}">
                                            {{ $faNum(jdate($dom->expiry_date)->format('Y/m/d')) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 font-bold">—</span>
                                    @endif
                                </td>

                                {{-- Synced At --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-bold">
                                    {{ $dom->synced_at ? $faNum(jdate($dom->synced_at)->format('Y/m/d H:i')) : '—' }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Show details --}}
                                        <a href="{{ route('user.domainmanager.domains.show', $dom->domain_name) }}"
                                           class="p-2.5 rounded-xl bg-gray-50 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors shadow-2xs"
                                           title="جزئیات و تنظیمات دامنه">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        {{-- Quick Renew Button (Opens Beautiful Modal) --}}
                                        @can('domainmanager.domains.manage')
                                            <button type="button"
                                                    @click="openRenewModal({{ json_encode($domJsData) }})"
                                                    class="p-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 transition-colors shadow-2xs cursor-pointer"
                                                    title="تمدید آنلاین دامنه">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                @if($domains->total() > 0)
                    <div
                        class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            نمایش
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($domains->firstItem() ?? 0) }}</strong>
                            تا
                            <strong
                                class="text-gray-800 dark:text-gray-200">{{ $faNum($domains->lastItem() ?? 0) }}</strong>
                            از
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($domains->total()) }}</strong>
                            دامنه
                        </span>
                        </div>

                        @if($domains->hasPages())
                            <div>
                                {{ $domains->withQueryString()->links('domainmanager::partials.pagination') }}
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                    <div
                        class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path
                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ دامنه‌ای یافت نشد</h3>
                        <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                            دامنه‌ای مطابق با فیلترهای انتخابی پیدا نشد. می‌توانید فیلترها را تغییر داده یا از دکمه «ثبت
                            یا
                            انتقال دامنه جدید» استفاده کنید.
                        </p>
                    </div>
                    @if(request()->anyFilled(['q', 'reseller_id', 'status']))
                        <a href="{{ route('user.domainmanager.domains.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            پاک‌سازی تمام فیلترها
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Premium Domain Renewal Modal --}}
        <div x-show="renewModal.open" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                {{-- Backdrop --}}
                <div x-show="renewModal.open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="closeRenewModal()"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                {{-- Modal Dialog Panel --}}
                <div x-show="renewModal.open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-start shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700/60">

                    {{-- Modal Header --}}
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-r from-emerald-50/70 to-indigo-50/50 dark:from-emerald-950/30 dark:to-indigo-950/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white" id="modal-title">تمدید
                                    آنلاین
                                    و آنی دامنه</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">افزایش دوره اعتبار دامنه در
                                    وب‌سرویس کارگزار</p>
                            </div>
                        </div>
                        <button type="button" @click="closeRenewModal()"
                                class="rounded-xl p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body Form --}}
                    <form :action="renewModal.actionUrl" method="POST" @submit="renewModal.submitting = true"
                          class="p-6 space-y-5">
                        @csrf

                        {{-- Domain Summary Card --}}
                        <div
                            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">نام دامنه:</span>
                                <span
                                    class="font-bold text-sm text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-3 py-1 rounded-xl border border-indigo-200/60 dark:border-indigo-500/20"
                                    dir="ltr" x-text="renewModal.domainName"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">کارگزار متصل:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200"
                                      x-text="renewModal.resellerName"></span>
                            </div>
                            <div
                                class="flex items-center justify-between text-xs pt-2 border-t border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">تاریخ انقضای فعلی:</span>
                                <span class="font-bold text-gray-900 dark:text-white"
                                      x-text="renewModal.expiryDateFa"></span>
                            </div>
                        </div>

                        {{-- Renewal Duration Selector --}}
                        <div class="space-y-1.5"
                             x-data="{
                            openYears: false,
                            searchYears: '',
                            yearOptions: [
                                { value: '1', title: '۱ سال تمدید دامنه', subtitle: 'دوره پایه' },
                                { value: '2', title: '۲ سال تمدید دامنه', subtitle: 'پیشنهادی' },
                                { value: '3', title: '۳ سال تمدید دامنه', subtitle: '۳۶ ماهه' },
                                { value: '4', title: '۴ سال تمدید دامنه', subtitle: '۴۸ ماهه' },
                                { value: '5', title: '۵ سال تمدید دامنه', subtitle: 'اقتصادی ۵ ساله' },
                                { value: '6', title: '۶ سال تمدید دامنه', subtitle: '۷۲ ماهه' },
                                { value: '7', title: '۷ سال تمدید دامنه', subtitle: '۸۴ ماهه' },
                                { value: '8', title: '۸ سال تمدید دامنه', subtitle: '۹۶ ماهه' },
                                { value: '9', title: '۹ سال تمدید دامنه', subtitle: '۱۰۸ ماهه' },
                                { value: '10', title: '۱۰ سال تمدید دامنه', subtitle: 'حداکثر بازه' }
                            ],
                            get filteredYears() {
                                if (!this.searchYears.trim()) return this.yearOptions;
                                const q = this.searchYears.toLowerCase();
                                return this.yearOptions.filter(o => o.title.toLowerCase().includes(q) || (o.subtitle && o.subtitle.toLowerCase().includes(q)));
                            },
                            selectYear(opt) {
                                renewModal.years = opt ? String(opt.value) : '1';
                                this.openYears = false;
                                this.searchYears = '';
                            },
                            getSelectedYearTitle() {
                                let found = this.yearOptions.find(o => String(o.value) === String(renewModal.years));
                                if (!found) return 'انتخاب مدت تمدید...';
                                return found.title + (found.subtitle ? ' (' + found.subtitle + ')' : '');
                            }
                         }">
                            <label class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">
                                مدت زمان دوره تمدید <span class="text-rose-500 font-black">*</span>
                            </label>
                            <input type="hidden" name="years" :value="renewModal.years" required>

                            <div class="relative" :class="{ 'z-50': openYears }">
                                <button type="button" @click="openYears = !openYears"
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs sm:text-sm font-bold text-gray-900 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 transition-all shadow-2xs dark:border-gray-700 dark:bg-gray-900/60 dark:text-white flex items-center justify-between cursor-pointer">
                                    <span x-text="getSelectedYearTitle()" class="truncate font-medium"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                         :class="{ 'rotate-180': openYears }" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="openYears" @click.outside="openYears = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="searchYears" placeholder="جستجوی دوره تمدید..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                    </div>

                                    <template x-for="opt in filteredYears" :key="opt.value">
                                        <div @click="selectYear(opt)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-600 dark:hover:text-emerald-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-300 font-bold': String(renewModal.years) === String(opt.value) }">
                                            <span x-text="opt.title" class="truncate font-medium"></span>
                                            <span x-show="opt.subtitle"
                                                  class="text-[10px] px-1.5 py-0.5 rounded border border-gray-200/80 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shrink-0 font-medium"
                                                  x-text="opt.subtitle"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Notice Alert --}}
                        <div
                            class="p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5">
                            <svg class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="leading-relaxed">
                                درخواست تمدید بلافاصله به وب‌سرویس کارگزار ارسال گردیده و هزینه آن از موجودی حساب
                                نمایندگی
                                شما کسر خواهد شد.
                            </p>
                        </div>

                        {{-- Modal Actions --}}
                        <div
                            class="pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-end gap-3">
                            <button type="button" @click="closeRenewModal()"
                                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                انصراف
                            </button>

                            <button type="submit" :disabled="renewModal.submitting"
                                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all active:scale-95 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <svg x-show="!renewModal.submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg x-show="renewModal.submitting" class="animate-spin w-4 h-4 text-white" fill="none"
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span
                                    x-text="renewModal.submitting ? 'در حال ارسال درخواست تمدید...' : 'تایید و تمدید آنلاین دامنه'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
