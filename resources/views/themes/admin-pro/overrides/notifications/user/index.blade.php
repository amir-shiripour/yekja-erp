@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;

    $statusFilter = $statusFilter ?? 'all';
    $categoryFilter = $categoryFilter ?? 'all';
    $priorityFilter = $priorityFilter ?? 'all';
    $periodFilter = $periodFilter ?? 'all';

    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $statusOptionsList = [
        ['value' => 'all',    'label' => 'همه اعلان‌ها'],
        ['value' => 'unread', 'label' => 'خوانده‌نشده'],
        ['value' => 'read',   'label' => 'خوانده‌شده'],
    ];

    $periodOptionsList = [
        ['value' => 'all',   'label' => 'بدون فیلتر زمانی'],
        ['value' => 'today', 'label' => 'امروز'],
        ['value' => 'week',  'label' => 'این هفته'],
        ['value' => 'month', 'label' => 'این ماه'],
        ['value' => 'year',  'label' => 'امسال'],
    ];

    $categoryOptionsList = collect(['all' => 'همه دسته‌بندی‌ها'])
        ->merge(collect($categories)->map(fn($c) => $c['label']))
        ->map(fn($label, $key) => ['value' => (string) $key, 'label' => $label])
        ->values()
        ->all();

    $priorityOptionsList = collect(['all' => 'همه اولویت‌ها'])
        ->merge(collect($priorities)->map(fn($p) => $p['label']))
        ->map(fn($label, $key) => ['value' => (string) $key, 'label' => $label])
        ->values()
        ->all();

    $categoryBadgeMap = [
        'system'    => 'indigo',
        'tasks'     => 'emerald',
        'reminders' => 'amber',
        'clients'   => 'sky',
        'sales'     => 'success',
        'workflows' => 'purple',
        'broadcast' => 'rose',
    ];

    $priorityBadgeMap = [
        'urgent' => ['label' => 'فوری',  'variant' => 'danger'],
        'high'   => ['label' => 'مهم',   'variant' => 'warning'],
        'normal' => ['label' => 'عادی',  'variant' => 'sky'],
        'low'    => ['label' => 'پایین', 'variant' => 'gray'],
    ];

    $urgentCount = $notifications->getCollection()->where('priority', 'urgent')->count();
    $pageIds = $notifications->pluck('id')->map(fn($id) => (string) $id)->values()->all();
@endphp

@section('title', 'مدیریت اعلان‌ها')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data="notificationsManager(@js($pageIds))">
        <header class="space-y-2.5 sm:space-y-3">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">مرکز اعلان‌ها</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            مدیریت و پیگیری اعلان‌ها
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if(Route::has('user.notifications.settings'))
                        <a href="{{ route('user.notifications.settings') }}"
                           class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="hidden sm:inline">تنظیمات اعلان‌ها</span>
                            <span class="sm:hidden">تنظیمات</span>
                        </a>
                    @endif

                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('user.notifications.mark-all-read') }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="hidden sm:inline">خواندن همه</span>
                                <span class="sm:hidden">خواندن همه</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </header>

        @if(session('status'))
            <x-ui.alert variant="success" title="پیام سیستم" :dismissible="true">
                {{ session('status') }}
            </x-ui.alert>
        @endif
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
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل اعلان‌ها</span>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ $faNum($totalCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">خوانده‌نشده</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">{{ $faNum($unreadCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">خوانده‌شده</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $faNum($readCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">فوری در این صفحه</span>
                    <p class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5">{{ $faNum($urgentCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('user.notifications.index') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 transition-all relative z-20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">وضعیت مطالعه</label>
                    <x-ui.select
                        name="filter"
                        :options="$statusOptionsList"
                        :value="$statusFilter"
                        placeholder="وضعیت..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                <div class="sm:col-span-1 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">دسته‌بندی</label>
                    <x-ui.select
                        name="category"
                        :options="$categoryOptionsList"
                        :value="$categoryFilter"
                        placeholder="دسته‌بندی..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

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

                <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->anyFilled(['filter', 'category', 'priority', 'period']) && ($statusFilter !== 'all' || $categoryFilter !== 'all' || $priorityFilter !== 'all' || $periodFilter !== 'all'))
                        <a href="{{ route('user.notifications.index') }}"
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
                        <th class="py-3 px-4 w-10 text-center">
                            <input type="checkbox"
                                   :checked="isAllChecked"
                                   @change="toggleAll()"
                                   class="rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="py-3 px-4 text-start">عنوان و شرح اعلان</th>
                        <th class="py-3 px-4 text-center">دسته‌بندی</th>
                        <th class="py-3 px-4 text-center">اولویت</th>
                        <th class="py-3 px-4 text-center">زمان ثبت</th>
                        <th class="py-3 px-4 text-center">وضعیت</th>
                        <th class="py-3 px-4 text-center w-28">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                    @forelse($notifications as $n)
                        @php
                            $isUnread = is_null($n->read_at);
                            $priority = $n->priority;
                            $severity = $n->severity;
                            $category = $n->category;
                            $catVariant = $categoryBadgeMap[$category] ?? 'gray';
                            $prioMeta = $priorityBadgeMap[$priority] ?? $priorityBadgeMap['normal'];
                            $jalaliTime = $n->created_at ? Jalalian::fromCarbon($n->created_at)->format('Y/m/d H:i') : '—';
                        @endphp
                        <tr class="group transition-colors {{ $isUnread ? 'bg-indigo-50/25 dark:bg-indigo-950/15 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30' : 'hover:bg-gray-50/80 dark:hover:bg-gray-700/30' }}">
                            <td class="py-3.5 px-4 text-center">
                                <input type="checkbox"
                                       value="{{ $n->id }}"
                                       x-model="selectedIds"
                                       class="rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            <td class="py-3.5 px-4 max-w-md">
                                <div class="flex items-start gap-2.5">
                                    @if($isUnread)
                                        <span
                                            class="mt-1.5 w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                                    @endif
                                    <div class="min-w-0 space-y-1">
                                        <div class="font-bold text-gray-900 dark:text-white leading-snug">
                                            {{ $n->title }}
                                        </div>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                            {{ $n->message }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <x-ui.badge :variant="$catVariant" size="sm">
                                    {{ $n->category_label }}
                                </x-ui.badge>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <x-ui.badge :variant="$prioMeta['variant']" :dot="true" size="sm">
                                    {{ $prioMeta['label'] }}
                                </x-ui.badge>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div
                                    class="font-bold text-gray-700 dark:text-gray-200">{{ $faNum($n->created_at->diffForHumans()) }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5"
                                     dir="ltr">{{ $faNum($jalaliTime) }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($isUnread)
                                    <x-ui.badge variant="amber" :dot="true" size="sm">خوانده‌نشده</x-ui.badge>
                                @else
                                    <x-ui.badge variant="emerald" size="sm">خوانده‌شده</x-ui.badge>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($isUnread)
                                        <form method="POST" action="{{ route('user.notifications.mark-read', $n->id) }}"
                                              class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.complete-button
                                                size="sm"
                                                confirmLabel="خوانده شد"
                                                pendingLabel="در حال ثبت..."
                                                doneLabel="ثبت شد"
                                            />
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('user.notifications.destroy', $n->id) }}"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.delete-button size="sm"/>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6">
                                <x-ui.empty-state
                                    title="هیچ اعلانی یافت نشد"
                                    description="با توجه به فیلترهای انتخابی، در حال حاضر هیچ پیامی برای نمایش وجود ندارد."
                                    :actionLabel="$statusFilter !== 'all' || $categoryFilter !== 'all' || $priorityFilter !== 'all' || $periodFilter !== 'all' ? 'بازنشانی فیلترها' : null"
                                    :actionUrl="route('user.notifications.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @include('themes.admin-pro.partials.pagination-bar', ['paginator' => $notifications, 'itemLabel' => 'اعلان ثبت‌شده'])
        </div>

        {{-- لیست کارت‌های اعلان در موبایل --}}
        <div class="md:hidden space-y-3">
            @if($notifications->count() > 0)
                <div class="flex items-center justify-between px-2 text-xs text-gray-500 dark:text-gray-400">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               :checked="isAllChecked"
                               @change="toggleAll()"
                               class="rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <span class="font-bold">انتخاب همه این صفحه</span>
                    </label>
                </div>
            @endif

            @forelse($notifications as $n)
                @php
                    $isUnread = is_null($n->read_at);
                    $priority = $n->priority;
                    $category = $n->category;
                    $catVariant = $categoryBadgeMap[$category] ?? 'gray';
                    $prioMeta = $priorityBadgeMap[$priority] ?? $priorityBadgeMap['normal'];
                    $jalaliTime = $n->created_at ? Jalalian::fromCarbon($n->created_at)->format('Y/m/d H:i') : '—';
                @endphp
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border {{ $isUnread ? 'border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/20 dark:bg-indigo-950/15' : 'border-gray-100 dark:border-gray-700/50' }} p-4 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <input type="checkbox"
                                   value="{{ $n->id }}"
                                   x-model="selectedIds"
                                   class="mt-1 rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer shrink-0">
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($isUnread)
                                        <span
                                            class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400 shrink-0"></span>
                                    @endif
                                    <h3 class="text-xs font-black text-gray-900 dark:text-white leading-snug">{{ $n->title }}</h3>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">{{ $n->message }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <x-ui.badge :variant="$catVariant" size="sm">{{ $n->category_label }}</x-ui.badge>
                            <x-ui.badge :variant="$prioMeta['variant']" :dot="true"
                                        size="sm">{{ $prioMeta['label'] }}</x-ui.badge>
                            @if($isUnread)
                                <x-ui.badge variant="amber" size="sm">خوانده‌نشده</x-ui.badge>
                            @else
                                <x-ui.badge variant="emerald" size="sm">خوانده‌شده</x-ui.badge>
                            @endif
                        </div>

                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500">
                            {{ $faNum($n->created_at->diffForHumans()) }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between gap-2 pt-2.5 border-t border-gray-100 dark:border-gray-700/50">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500" dir="ltr">
                            {{ $faNum($jalaliTime) }}
                        </span>

                        <div class="flex items-center gap-1.5">
                            @if($isUnread)
                                <form method="POST" action="{{ route('user.notifications.mark-read', $n->id) }}"
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.complete-button size="sm" confirmLabel="خوانده شد"/>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('user.notifications.destroy', $n->id) }}"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <x-ui.delete-button size="sm"/>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <x-ui.empty-state
                    title="هیچ اعلانی یافت نشد"
                    description="با توجه به فیلترهای انتخابی، در حال حاضر هیچ پیامی برای نمایش وجود ندارد."
                    :actionLabel="$statusFilter !== 'all' || $categoryFilter !== 'all' || $priorityFilter !== 'all' || $periodFilter !== 'all' ? 'بازنشانی فیلترها' : null"
                    :actionUrl="route('user.notifications.index')"
                />
            @endforelse

            @if($notifications->total() > 0)
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
                    @include('themes.admin-pro.partials.pagination-bar', ['paginator' => $notifications, 'itemLabel' => 'اعلان ثبت‌شده'])
                </div>
            @endif
        </div>

        <div x-show="selectedIds.length > 0"
             x-cloak
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="fixed bottom-24 md:bottom-6 z-40 inset-x-0 px-8 sm:px-12 md:px-0 pointer-events-none flex justify-center transition-all duration-300"
             :class="typeof sidebarCollapsed !== 'undefined' && sidebarCollapsed ? 'lg:pr-20' : 'lg:pr-72'">
            <div
                class="pointer-events-auto w-full sm:w-auto bg-white/95 dark:bg-gray-800/95 backdrop-blur-2xl border border-gray-200/90 dark:border-gray-700/80 rounded-full px-2 py-1.5 sm:px-4 sm:py-2 shadow-2xl shadow-indigo-950/20 dark:shadow-black/70 ring-1 ring-black/5 dark:ring-white/10">
                <div class="flex items-center gap-1.5 sm:gap-3 w-full sm:w-auto justify-between">
                    <div
                        class="inline-flex items-center gap-1.5 sm:gap-2 h-9 px-2 sm:px-3 rounded-full bg-indigo-50/90 dark:bg-indigo-950/50 border border-indigo-100/90 dark:border-indigo-800/60 text-indigo-950 dark:text-indigo-100 shadow-2xs shrink-0 select-none">
                        <span
                            class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black leading-none shadow-xs"
                            x-text="toFa(selectedIds.length)"></span>
                        <span class="text-xs font-black whitespace-nowrap">
                            <span class="hidden sm:inline">اعلان </span>انتخاب
                        </span>
                        <button type="button"
                                @click="selectedIds = []"
                                class="inline-flex items-center justify-center w-5 h-5 -me-1 rounded-full text-indigo-400 hover:text-indigo-700 dark:hover:text-white hover:bg-indigo-200/50 dark:hover:bg-indigo-800/60 transition-colors cursor-pointer"
                                title="لغو انتخاب همه">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="hidden sm:block h-6 w-px bg-gray-200/80 dark:bg-gray-700/80 shrink-0"></div>
                    <form method="POST" action="{{ route('user.notifications.bulk-read') }}"
                          class="inline-flex items-center shrink-0">
                        @csrf
                        @method('PATCH')
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <x-ui.complete-button
                            size="base"
                            confirmLabel="خوانده شد"
                            pendingLabel="در حال ثبت..."
                            doneLabel="ثبت شد"
                        />
                    </form>

                    <div class="h-5 sm:h-6 w-px bg-gray-200/80 dark:bg-gray-700/80 shrink-0"></div>
                    <form method="POST" action="{{ route('user.notifications.bulk-destroy') }}"
                          class="inline-flex items-center shrink-0">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <x-ui.delete-button size="base"/>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function notificationsManager(pageIds) {
            return {
                pageIds: pageIds || [],
                selectedIds: [],

                get isAllChecked() {
                    return this.pageIds.length > 0 && this.pageIds.every(id => this.selectedIds.includes(id));
                },

                toggleAll() {
                    if (this.isAllChecked) {
                        this.selectedIds = [];
                    } else {
                        this.selectedIds = [...this.pageIds];
                    }
                },

                toFa(val) {
                    const en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    return String(val).replace(/[0-9]/g, d => fa[en.indexOf(d)]);
                }
            };
        }
    </script>
@endpush
