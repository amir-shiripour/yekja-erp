@php
    $centralDomain = reseller_clean_domain(config('tenancy.central_domains')[0] ?? request()->getHost());
@endphp

<div class="space-y-6">
    {{-- Filter and Search Card --}}
    <form method="GET" action="{{ route('reseller.tenants.index') }}"
          class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl transition-all duration-300 p-5 sm:p-6">
        <input type="hidden" name="tab" value="subaccounts">
        @if(request('period'))
            <input type="hidden" name="period" value="{{ request('period') }}">
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
            {{-- Search input --}}
            <div class="relative lg:col-span-4 sm:col-span-2">
                <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       name="sub_search"
                       value="{{ request('sub_search', (request('tab') === 'subaccounts' ? request('search') : '')) }}"
                       placeholder="جستجو در نام کسب‌وکار، نام خریدار، ایمیل، موبایل..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 ps-10 pe-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all dark:text-white">
            </div>

            {{-- Status Filter --}}
            <div class="lg:col-span-3 sm:col-span-1">
                <select name="sub_status"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2.5 px-3 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    <option value="">همه وضعیت‌های حساب</option>
                    <option
                        value="active" {{ request('sub_status', (request('tab') === 'subaccounts' ? request('status') : '')) === 'active' ? 'selected' : '' }}>
                        فقط فعال‌ها
                    </option>
                    <option
                        value="suspended" {{ request('sub_status', (request('tab') === 'subaccounts' ? request('status') : '')) === 'suspended' ? 'selected' : '' }}>
                        فقط معلق‌ها
                    </option>
                </select>
            </div>

            {{-- Super Admin Reseller Filter --}}
            @if(!empty($isSuperAdmin) && !empty($allResellers) && $allResellers->count() > 0)
                <div class="lg:col-span-3 sm:col-span-1">
                    <select name="reseller_id"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2.5 px-3 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        <option value="all">همه نمایندگان</option>
                        @foreach($allResellers as $r)
                            <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->company_name ?: ($r->user?->name ?? 'نماینده #' . $r->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Submit and Reset Buttons --}}
            <div class="flex items-center gap-2 lg:col-span-2 justify-end">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-sm shadow-indigo-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>اعمال فیلتر</span>
                </button>

                @if(request()->hasAny(['sub_search', 'sub_status', 'search', 'status']))
                    <a href="{{ route('reseller.tenants.index', ['tab' => 'subaccounts']) }}"
                       class="inline-flex items-center justify-center p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 transition-colors shrink-0"
                       title="پاکسازی فیلترها">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Sub-Accounts Data Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm backdrop-blur-xl overflow-hidden transition-all">
        <div
            class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span
                        class="w-9 h-9 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800/50 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">
                        فهرست خریداران و حساب‌های زیرمجموعه
                    </h2>
                    <span
                        class="px-2.5 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-800/50 text-xs font-black tabular-nums">
                        {{ reseller_fa_num(isset($subAccounts) ? $subAccounts->total() : 0) }} خریدار
                    </span>
                </div>
                <p class="text-xs text-gray-400 font-medium ms-11">
                    مدیریت پرونده خریداران، پزشکان، کلینیک‌ها و سامانه‌های CRM تحت مالکیت آن‌ها
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                @can('resellerhub.sub-accounts.create')
                    <a href="{{ route('reseller.sub-accounts.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span>ایجاد خریدار جدید</span>
                    </a>
                @endcan
            </div>
        </div>

        @if(empty($subAccounts) || $subAccounts->isEmpty())
            <div class="p-12 sm:p-16 text-center space-y-4">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700/60 text-gray-400 rounded-2xl flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    @if(request()->hasAny(['sub_search', 'sub_status', 'search', 'status']))
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ خریداری با فیلترهای انتخابی
                            یافت نشد</h3>
                        <p class="text-xs text-gray-400">عبارت جستجو یا وضعیت را تغییر دهید تا نتایج نمایش داده
                            شوند.</p>
                        <div class="pt-2">
                            <a href="{{ route('reseller.tenants.index', ['tab' => 'subaccounts']) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition">
                                پاک کردن فیلترها
                            </a>
                        </div>
                    @else
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">هنوز هیچ خریدار یا زیرمجموعه‌ای
                            ثبت نشده است</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            با ثبت خریداران می‌توانید سامانه‌های ابری CRM را مستقیماً به آن‌ها اختصاص داده و مدیریت
                            فرمایید.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('reseller.sub-accounts.create') }}"
                               class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ثبت اولین خریدار زیرمجموعه</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="overflow-x-auto min-h-[260px]">
                <table class="w-full text-right text-sm">
                    <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/75 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs">
                        <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                            کسب‌وکار / زیرمجموعه
                        </th>
                        <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                            سامانه CRM متصل
                        </th>
                        <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-start">
                            تاریخ ثبت
                        </th>
                        <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-center">
                            وضعیت حساب
                        </th>
                        <th scope="col" class="py-3.5 px-4 sm:px-5 font-bold text-end">
                            عملیات مدیریت
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-300">
                    @foreach($subAccounts as $sub)
                        @php
                            $subDisplayName = $sub->company_name ?: ($sub->tenant?->name ?: $sub->name);
                            $subInitial = mb_substr($subDisplayName ?: '—', 0, 1, 'UTF-8');
                            $connectedTenantsList = $sub->getConnectedTenants();
                        @endphp
                        <tr class="group hover:bg-indigo-50/25 dark:hover:bg-indigo-950/20 transition-all duration-200"
                            id="row-subaccount-{{ $sub->id }}">
                            {{-- Col 1: Name and Profile --}}
                            <td class="py-4 px-4 sm:px-5 relative">
                                <div
                                    class="absolute right-0 top-2 bottom-2 w-1 bg-transparent group-hover:bg-purple-500 rounded-l transition-all duration-200"></div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-xs flex items-center justify-center shrink-0">
                                        {{ $subInitial }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('reseller.sub-accounts.show', $sub->id) }}"
                                           class="font-black text-xs sm:text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block">
                                            {{ $subDisplayName }}
                                        </a>
                                        <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                            @if($sub->mobile)
                                                <span
                                                    class="dir-ltr text-start font-mono">{{ reseller_fa_num($sub->mobile) }}</span>
                                            @endif
                                            @if($sub->email)
                                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                                <span
                                                    class="dir-ltr text-start truncate max-w-[140px]">{{ $sub->email }}</span>
                                            @endif
                                            <span
                                                class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $sub->source_badge }}">
                                                {{ $sub->source_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Col 2: Connected CRM Systems --}}
                            <td class="py-4 px-4 sm:px-5">
                                @if($connectedTenantsList->isNotEmpty())
                                    <div class="flex flex-col gap-1 max-w-[240px]">
                                        @foreach($connectedTenantsList->take(2) as $t)
                                            <div class="flex items-center gap-1.5 text-xs truncate">
                                                <a href="{{ route('reseller.tenants.show', $t->id) }}"
                                                   class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline truncate"
                                                   title="{{ $t->name ?: $t->subdomain }}">
                                                    {{ $t->name ?: $t->subdomain }}
                                                </a>
                                                <span class="text-[10px] font-mono text-gray-400 dir-ltr">({{ $t->subdomain }})</span>
                                            </div>
                                        @endforeach
                                        @if($connectedTenantsList->count() > 2)
                                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                                + {{ reseller_fa_num($connectedTenantsList->count() - 2) }} سامانه دیگر
                                            </span>
                                        @endif
                                    </div>
                                @elseif($sub->tenant)
                                    <div class="flex flex-col gap-1">
                                        <a href="{{ route('reseller.tenants.show', $sub->tenant->id) }}"
                                           class="font-bold text-xs text-indigo-600 dark:text-indigo-400 hover:underline truncate">
                                            {{ $sub->tenant->name }}
                                        </a>
                                        <span class="text-[10px] font-mono text-gray-400 dir-ltr">{{ $sub->tenant->subdomain }}.{{ $centralDomain }}</span>
                                    </div>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                        فاقد سامانه متصل
                                    </span>
                                @endif
                            </td>

                            {{-- Col 3: Registration Date --}}
                            <td class="py-4 px-4 sm:px-5 text-gray-500 dark:text-gray-400">
                                <div class="font-bold text-xs text-gray-800 dark:text-gray-200">
                                    {{ reseller_format_jalali($sub->created_at, 'Y/m/d') }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5">{{ $sub->time_ago }}</div>
                            </td>

                            {{-- Col 4: Status --}}
                            <td class="py-4 px-4 sm:px-5 text-center">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $sub->status_badge }}"
                                    id="badge-sub-{{ $sub->id }}">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full {{ $sub->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>{{ $sub->status_label }}</span>
                                </span>
                            </td>

                            {{-- Col 5: Actions --}}
                            <td class="py-4 px-4 sm:px-5 text-end">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- View Details --}}
                                    <a href="{{ route('reseller.sub-accounts.show', $sub->id) }}"
                                       class="p-2 rounded-xl bg-gray-100 hover:bg-indigo-50 dark:bg-gray-700 dark:hover:bg-indigo-950/60 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition cursor-pointer"
                                       title="مشاهده جزئیات کامل خریدار">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('reseller.sub-accounts.edit', $sub->id) }}"
                                       class="p-2 rounded-xl bg-gray-100 hover:bg-amber-50 dark:bg-gray-700 dark:hover:bg-amber-950/60 text-gray-600 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition cursor-pointer"
                                       title="ویرایش حساب خریدار">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Toggle Status --}}
                                    <button type="button"
                                            @click="promptToggleStatus({{ $sub->id }}, '{{ addslashes($sub->name) }}', '{{ $sub->status }}')"
                                            class="p-2 rounded-xl transition cursor-pointer {{ $sub->status === 'active' ? 'bg-amber-50 hover:bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 dark:text-amber-300' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-300' }}"
                                            title="{{ $sub->status === 'active' ? 'تعلیق حساب خریدار' : 'فعال‌سازی مجدد حساب' }}">
                                        @if($sub->status === 'active')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Delete Button (dqnamo modal) --}}
                                    @can('resellerhub.sub-accounts.delete')
                                        <button type="button"
                                                @click="openSubAccountDeleteModal({
                                                    id: {{ $sub->id }},
                                                    name: '{{ addslashes($sub->name) }}',
                                                    company: '{{ addslashes($sub->company_name ?? '') }}',
                                                    tenants_count: {{ $connectedTenantsList->count() }},
                                                    tenants: {{ json_encode($connectedTenantsList->map(fn($t) => [
                                                        'id' => $t->id,
                                                        'name' => $t->name ?: $t->subdomain,
                                                        'subdomain' => $t->subdomain,
                                                        'status' => $t->status,
                                                    ])->values()) }},
                                                    delete_url: '{{ route('reseller.sub-accounts.destroy', $sub->id) }}'
                                                })"
                                                class="p-2 rounded-xl bg-gray-100 hover:bg-rose-50 dark:bg-gray-700 dark:hover:bg-rose-950/60 text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 transition cursor-pointer"
                                                title="{{ __('resellerhub::messages.subaccounts.delete_tooltip') }}">
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
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if(isset($subAccounts) && $subAccounts->total() > 0)
                <div
                    class="p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40 rounded-b-3xl">
                    {{ $subAccounts->appends(request()->query())->links('resellerhub::partials.pagination') }}
                </div>
            @endif
        @endif
    </div>
</div>
