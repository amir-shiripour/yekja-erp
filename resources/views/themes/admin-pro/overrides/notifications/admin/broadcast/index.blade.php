@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;

    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $totalBroadcasts = $totalBroadcasts ?? $broadcasts->total();
    $totalRecipients = $totalRecipients ?? $broadcasts->getCollection()->sum('recipients_count');
    $urgentBroadcasts = $urgentBroadcasts ?? $broadcasts->getCollection()->whereIn('priority', ['urgent', 'high'])->count();
    $allUsersBroadcasts = $allUsersBroadcasts ?? $broadcasts->getCollection()->where('target_type', 'all')->count();

    $priorityFilter = $priorityFilter ?? 'all';
    $targetFilter = $targetFilter ?? 'any';
    $periodFilter = $periodFilter ?? 'all';
    $search = $search ?? '';

    $priorityOptionsList = collect(['all' => 'همه اولویت‌ها'])
        ->merge(collect($priorities ?? config('notifications.priorities', []))->map(fn($p) => $p['label']))
        ->map(fn($label, $key) => ['value' => (string) $key, 'label' => $label])
        ->values()
        ->all();

    $targetOptionsList = [
        ['value' => 'any',   'label' => 'همه انواع مخاطب'],
        ['value' => 'all',   'label' => 'همه کاربران'],
        ['value' => 'role',  'label' => 'نقش‌های سازمانی'],
        ['value' => 'users', 'label' => 'کاربران انتخابی'],
    ];

    $periodOptionsList = [
        ['value' => 'all',   'label' => 'بدون فیلتر زمانی'],
        ['value' => 'today', 'label' => 'امروز'],
        ['value' => 'week',  'label' => 'این هفته'],
        ['value' => 'month', 'label' => 'این ماه'],
        ['value' => 'year',  'label' => 'امسال'],
    ];

    $priorityBadgeMap = [
        'urgent' => ['label' => 'فوری',  'variant' => 'danger'],
        'high'   => ['label' => 'مهم',   'variant' => 'warning'],
        'normal' => ['label' => 'عادی',  'variant' => 'sky'],
        'low'    => ['label' => 'پایین', 'variant' => 'gray'],
    ];
@endphp

@section('title', 'تاریخچه اطلاعیه‌های همگانی')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8">
        <header class="space-y-2.5 sm:space-y-3">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">اطلاعیه‌های همگانی</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            اطلاعیه‌ها و پیام‌های همگانی
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.notifications.broadcast.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="hidden sm:inline">ارسال اطلاعیه جدید</span>
                        <span class="sm:hidden">ارسال جدید</span>
                    </a>
                </div>
            </div>
        </header>

        @if(session('success'))
            <x-ui.alert variant="success" title="عملیات موفقیت‌آمیز" :dismissible="true">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" title="بروز خطا" :dismissible="true">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل اطلاعیه‌ها</span>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ $faNum($totalBroadcasts) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">مجموع گیرندگان</span>
                    <p class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-1.5">{{ $faNum($totalRecipients) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">ارسال‌های مهم و فوری</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">{{ $faNum($urgentBroadcasts) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">همگانی (کل کاربران)</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $faNum($allUsersBroadcasts) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                    </svg>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.notifications.broadcast.index') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 transition-all relative z-20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">اولویت</label>
                    <x-ui.select
                        name="priority"
                        :options="$priorityOptionsList"
                        :value="$priorityFilter"
                        placeholder="اولویت..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                <div class="sm:col-span-1 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">مخاطبان هدف</label>
                    <x-ui.select
                        name="target_type"
                        :options="$targetOptionsList"
                        :value="$targetFilter"
                        placeholder="مخاطبان..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">بازه زمانی</label>
                    <x-ui.select
                        name="period"
                        :options="$periodOptionsList"
                        :value="$periodFilter"
                        placeholder="بازه زمانی..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                <div class="sm:col-span-1 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">بازه دلخواه تاریخ
                        ارسال</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text"
                               name="from_jalali"
                               data-jdp-only-date
                               value="{{ request('from_jalali') }}"
                               placeholder="از: ۱۴۰۳/۰۱/۰۱"
                               autocomplete="off"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden text-center">
                        <input type="text"
                               name="to_jalali"
                               data-jdp-only-date
                               value="{{ request('to_jalali') }}"
                               placeholder="تا: ۱۴۰۳/۱۲/۲۹"
                               autocomplete="off"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden text-center">
                    </div>
                </div>

                <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->anyFilled(['priority', 'target_type', 'period', 'from_jalali', 'to_jalali']) && ($priorityFilter !== 'all' || $targetFilter !== 'any' || $periodFilter !== 'all' || request()->filled('from_jalali') || request()->filled('to_jalali')))
                        <a href="{{ route('admin.notifications.broadcast.index') }}"
                           class="inline-flex items-center justify-center p-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 hover:text-rose-600 hover:border-rose-200 transition-colors cursor-pointer shrink-0"
                           title="حذف فیلترها">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div
            class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start border-collapse">
                    <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/75 dark:bg-gray-900/50 text-[11px] font-bold text-gray-400 dark:text-gray-400">
                        <th class="py-3.5 px-5 text-start">عنوان و پیام اطلاعیه</th>
                        <th class="py-3.5 px-4 text-center">وضعیت / اولویت</th>
                        <th class="py-3.5 px-4 text-center">مخاطبان هدف</th>
                        <th class="py-3.5 px-4 text-center">کانال‌ها</th>
                        <th class="py-3.5 px-4 text-center">گیرندگان</th>
                        <th class="py-3.5 px-4 text-center">فرستنده</th>
                        <th class="py-3.5 px-4 text-center">تاریخ ارسال</th>
                        <th class="py-3.5 px-4 text-center w-24">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                    @forelse($broadcasts as $item)
                        @php
                            $prioMeta = $priorityBadgeMap[$item->priority] ?? $priorityBadgeMap['normal'];
                            $sentJalali = $item->sent_at ? Jalalian::fromCarbon($item->sent_at)->format('Y/m/d H:i') : '—';
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3.5 px-5 max-w-sm">
                                <div class="font-bold text-gray-900 dark:text-white mb-1">{{ $item->title }}</div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $item->message }}
                                </p>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <x-ui.badge :variant="$prioMeta['variant']" :dot="true" size="sm">
                                    {{ $prioMeta['label'] }}
                                </x-ui.badge>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($item->target_type === 'all')
                                    <x-ui.badge variant="indigo" size="sm">همه کاربران</x-ui.badge>
                                @elseif($item->target_type === 'role')
                                    <x-ui.badge variant="purple" size="sm">نقش‌های سازمانی</x-ui.badge>
                                @else
                                    <x-ui.badge variant="sky" size="sm">کاربران انتخابی</x-ui.badge>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    @foreach($item->channels ?? [] as $ch)
                                        <x-ui.badge variant="gray" size="sm">
                                            {{ $ch === 'database' ? 'پنل' : ($ch === 'sms' ? 'پیامک' : 'ایمیل') }}
                                        </x-ui.badge>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap font-black text-gray-800 dark:text-gray-200">
                                {{ $faNum($item->recipients_count) }} نفر
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap text-gray-600 dark:text-gray-300 font-bold">
                                {{ $item->creator?->name ?? 'سیستم' }}
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div
                                    class="font-bold text-gray-700 dark:text-gray-200">{{ $item->sent_at ? $faNum($item->sent_at->diffForHumans()) : '—' }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5"
                                     dir="ltr">{{ $faNum($sentJalali) }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <form action="{{ route('admin.notifications.broadcast.destroy', $item->id) }}"
                                      method="POST" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.delete-button size="sm"/>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6">
                                <x-ui.empty-state
                                    title="هیچ اطلاعیه همگانی ارسال نشده است"
                                    description="می‌توانید اولین اطلاعیه گروهی را برای کاربران یا نقش‌های سازمانی ارسال کنید."
                                    actionLabel="ارسال اطلاعیه جدید"
                                    :actionUrl="route('admin.notifications.broadcast.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @include('themes.admin-pro.partials.pagination-bar', ['paginator' => $broadcasts, 'itemLabel' => 'اطلاعیه ثبت‌شده'])
        </div>

        <div class="md:hidden space-y-3">
            @forelse($broadcasts as $item)
                @php
                    $prioMeta = $priorityBadgeMap[$item->priority] ?? $priorityBadgeMap['normal'];
                    $sentJalali = $item->sent_at ? Jalalian::fromCarbon($item->sent_at)->format('Y/m/d H:i') : '—';
                @endphp
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="space-y-1 min-w-0">
                            <h3 class="text-xs font-black text-gray-900 dark:text-white">{{ $item->title }}</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">{{ $item->message }}</p>
                        </div>
                        <x-ui.badge :variant="$prioMeta['variant']" :dot="true" size="sm">
                            {{ $prioMeta['label'] }}
                        </x-ui.badge>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap">
                        <x-ui.badge variant="indigo" size="sm">
                            {{ $item->target_type === 'all' ? 'همه کاربران' : ($item->target_type === 'role' ? 'نقش‌های سازمانی' : 'کاربران انتخابی') }}
                        </x-ui.badge>
                        @foreach($item->channels ?? [] as $ch)
                            <x-ui.badge variant="gray" size="sm">
                                {{ $ch === 'database' ? 'پنل' : ($ch === 'sms' ? 'پیامک' : 'ایمیل') }}
                            </x-ui.badge>
                        @endforeach
                        <span class="text-[11px] font-black text-gray-600 dark:text-gray-300 mr-auto">
                            {{ $faNum($item->recipients_count) }} نفر
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-2 pt-2.5 border-t border-gray-100 dark:border-gray-700/50 text-[11px] text-gray-500 dark:text-gray-400">
                        <div>
                            <span class="font-bold">{{ $item->creator?->name ?? 'سیستم' }}</span>
                            <span class="mx-1">•</span>
                            <span dir="ltr">{{ $faNum($sentJalali) }}</span>
                        </div>
                        <form action="{{ route('admin.notifications.broadcast.destroy', $item->id) }}" method="POST"
                              class="inline-flex">
                            @csrf
                            @method('DELETE')
                            <x-ui.delete-button size="sm"/>
                        </form>
                    </div>
                </div>
            @empty
                <x-ui.empty-state
                    title="هیچ اطلاعیه همگانی ارسال نشده است"
                    description="می‌توانید اولین اطلاعیه گروهی را برای کاربران یا نقش‌های سازمانی ارسال کنید."
                    actionLabel="ارسال اطلاعیه جدید"
                    :actionUrl="route('admin.notifications.broadcast.create')"
                />
            @endforelse

            @if($broadcasts->total() > 0)
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
                    @include('themes.admin-pro.partials.pagination-bar', ['paginator' => $broadcasts, 'itemLabel' => 'اطلاعیه ثبت‌شده'])
                </div>
            @endif
        </div>

    </div>
@endsection
