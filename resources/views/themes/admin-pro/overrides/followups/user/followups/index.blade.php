@extends('layouts.user')

@php
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    $statuses   = $statuses   ?? Task::statusOptions();
    $priorities = $priorities ?? Task::priorityOptions();
    $types      = $types      ?? Task::typeOptions();
    $title      = 'مدیریت و پیگیری مشتریان';

    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $totalCount = $followups->total();
    $doneCount = $followups->where('status', Task::STATUS_DONE)->count();
    $inProgressCount = $followups->where('status', Task::STATUS_IN_PROGRESS)->count();
    $criticalCount = $followups->whereIn('priority', [Task::PRIORITY_HIGH, Task::PRIORITY_CRITICAL])->count();
@endphp

@section('title', 'مدیریت پیگیری‌ها')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8">
        <header
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">مدیریت پیگیری‌ها</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            مدیریت و پیگیری مشتریان
                        </h1>

                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @can('followups.create')
                        <a href="{{ route('user.followups.create') }}"
                           class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline">ثبت پیگیری جدید</span>
                            <span class="sm:hidden">پیگیری جدید</span>
                        </a>
                    @endcan
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
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل پیگیری‌ها</span>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ $faNum($totalCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>

            {{-- کارت ۲: در جریان / فعال --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">در جریان / فعال</span>
                    <p class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-1.5">{{ $faNum($inProgressCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">تکمیل‌شده</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $faNum($doneCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">اولویت فوری</span>
                    <p class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5">{{ $faNum($criticalCount) }}</p>
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

        <form method="GET" action="{{ route('user.followups.index') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 transition-all">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                <div
                    class="relative {{ (auth()->user()->can('followups.view.all') && !empty($users)) ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="جستجو در عنوان، شرح یا یادداشت..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 ps-10 pe-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden">
                </div>

                @if(auth()->user()->can('followups.view.all'))
                    <div class="lg:col-span-2">
                        <select name="assignee_id"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden cursor-pointer">
                            <option value="">همه مسئولین</option>
                            @foreach($users ?? [] as $u)
                                <option
                                    value="{{ $u->id }}" @selected(request('assignee_id') == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="lg:col-span-2">
                    <select name="status"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <select name="task_type"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden cursor-pointer">
                        <option value="">همه انواع پیگیری</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(request('task_type') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <select name="priority"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-2 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden cursor-pointer">
                        <option value="">اولویت</option>
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5 lg:col-span-2 justify-end">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->hasAny(['q', 'status', 'task_type', 'priority', 'assignee_id']))
                        <a href="{{ route('user.followups.index') }}"
                           class="inline-flex items-center justify-center p-2.5 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors shrink-0"
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

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/40 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">لیست پیگیری‌ها</h3>
                </div>
            </div>

            @if($followups->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="هیچ پیگیری‌ای یافت نشد"
                        description="در حال حاضر پیگیری‌ای با مشخصات فیلتر شده یا ثبت‌شده در سامانه وجود ندارد."
                        action-label="ثبت اولین پیگیری"
                        :action-url="auth()->user()->can('followups.create') ? route('user.followups.create') : null"
                    />
                </div>
            @else
                <div
                    class="hidden md:block overflow-x-auto [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">
                    <table class="w-full text-right text-sm">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700/40 text-xs font-black text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5 w-14 text-center">#</th>
                            <th class="px-5 py-3.5">عنوان پیگیری</th>
                            <th class="px-5 py-3.5">مشتری مرتبط</th>
                            <th class="px-5 py-3.5 w-28">نوع</th>
                            <th class="px-5 py-3.5 w-32">مسئول</th>
                            <th class="px-5 py-3.5 w-28">وضعیت</th>
                            <th class="px-5 py-3.5 w-24">اولویت</th>
                            <th class="px-5 py-3.5 w-36">سررسید</th>
                            <th class="px-5 py-3.5 text-left pl-6 w-36">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                        @foreach($followups as $fu)
                            @php
                                $statusVariant = match($fu->status) {
                                    Task::STATUS_DONE => 'emerald',
                                    Task::STATUS_CANCELED => 'rose',
                                    Task::STATUS_IN_PROGRESS => 'blue',
                                    default => 'slate',
                                };

                                $priorityVariant = match($fu->priority) {
                                    Task::PRIORITY_CRITICAL => 'rose',
                                    Task::PRIORITY_HIGH => 'rose',
                                    Task::PRIORITY_MEDIUM => 'amber',
                                    default => 'slate',
                                };
                            @endphp
                            <tr class="group hover:bg-indigo-50/30 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-4 text-center text-gray-400 dark:text-gray-500 text-xs font-bold">
                                    #{{ $faNum($fu->id) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-col min-w-0">
                                        <a href="{{ route('user.followups.show', $fu) }}"
                                           class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-1">
                                            {{ $fu->title }}
                                        </a>
                                        @if($fu->description)
                                            <span class="text-xs text-gray-400 dark:text-gray-500 line-clamp-1 mt-0.5">
                                                {{ $fu->description }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if($fu->client)
                                        <div class="flex flex-col min-w-0">
                                            <a href="{{ route('user.clients.show', $fu->client) }}"
                                               class="font-bold text-xs text-gray-800 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">
                                                {{ $fu->client->full_name }}
                                            </a>
                                            @if($fu->client->phone)
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 dir-ltr">
                                                        {{ $faNum($fu->client->phone) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.badge variant="slate" size="sm">
                                        {{ $types[$fu->task_type] ?? $fu->task_type }}
                                    </x-ui.badge>
                                </td>

                                <td class="px-5 py-4 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    @if($fu->assignee)
                                        <div class="flex items-center gap-2">
                                            <span class="truncate font-bold">{{ $fu->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.badge :variant="$statusVariant" size="sm">
                                        {{ $statuses[$fu->status] ?? $fu->status }}
                                    </x-ui.badge>
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.badge :variant="$priorityVariant" size="sm">
                                        {{ $priorities[$fu->priority] ?? $fu->priority }}
                                    </x-ui.badge>
                                </td>

                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                    @if($fu->due_at)
                                        <div class="flex flex-col">
                                            <span class="dir-ltr text-right font-bold text-gray-800 dark:text-gray-200">
                                                {{ $faNum(Jalalian::fromCarbon($fu->due_at)->format('Y/m/d')) }}
                                            </span>
                                            <span
                                                class="text-[10px] text-gray-400 dark:text-gray-500 dir-ltr text-right">
                                                {{ $faNum(Jalalian::fromCarbon($fu->due_at)->format('H:i')) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-left pl-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('user.followups.show', $fu) }}"
                                           class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 text-gray-600 dark:text-gray-300 transition-colors cursor-pointer shadow-2xs"
                                           title="مشاهده جزئیات">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        @canany(['followups.edit', 'followups.manage'])
                                            <a href="{{ route('user.followups.edit', $fu) }}"
                                               class="p-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer shadow-2xs"
                                               title="ویرایش پیگیری">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcanany

                                        @can('followups.delete')
                                            <form method="POST" action="{{ route('user.followups.destroy', $fu) }}"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.delete-button
                                                    type="submit"
                                                    label=""
                                                    confirm-label="حذف؟"
                                                    size="sm"
                                                    title="حذف پیگیری"
                                                />
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700/40">
                    @foreach($followups as $fu)
                        @php
                            $statusVariant = match($fu->status) {
                                Task::STATUS_DONE => 'emerald',
                                Task::STATUS_CANCELED => 'rose',
                                Task::STATUS_IN_PROGRESS => 'blue',
                                default => 'slate',
                            };

                            $priorityVariant = match($fu->priority) {
                                Task::PRIORITY_CRITICAL => 'rose',
                                Task::PRIORITY_HIGH => 'rose',
                                Task::PRIORITY_MEDIUM => 'amber',
                                default => 'slate',
                            };
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1">
                                        <span class="font-bold">#{{ $faNum($fu->id) }}</span>
                                        <span>{{ $types[$fu->task_type] ?? $fu->task_type }}</span>
                                    </div>
                                    <a href="{{ route('user.followups.show', $fu) }}"
                                       class="font-black text-sm text-gray-900 dark:text-white hover:text-indigo-600 line-clamp-1">
                                        {{ $fu->title }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <x-ui.badge :variant="$statusVariant" size="sm">
                                        {{ $statuses[$fu->status] ?? $fu->status }}
                                    </x-ui.badge>
                                    <x-ui.badge :variant="$priorityVariant" size="sm">
                                        {{ $priorities[$fu->priority] ?? $fu->priority }}
                                    </x-ui.badge>
                                </div>
                            </div>

                            @if($fu->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $fu->description }}
                                </p>
                            @endif

                            <div
                                class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-gray-100 dark:border-gray-700/40">
                                <div>
                                    <span class="text-gray-400 text-[11px] block">مشتری:</span>
                                    @if($fu->client)
                                        <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">
                                            {{ $fu->client->full_name }}
                                        </span>
                                        @if($fu->client->phone)
                                            <span
                                                class="text-[11px] text-gray-500 dark:text-gray-400 dir-ltr block mt-0.5">
                                                {{ $faNum($fu->client->phone) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-gray-400 text-[11px] block">مسئول / سررسید:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 block truncate">
                                        {{ $fu->assignee->name ?? '—' }}
                                    </span>
                                    @if($fu->due_at)
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 dir-ltr block mt-0.5">
                                            {{ $faNum(Jalalian::fromCarbon($fu->due_at)->format('Y/m/d H:i')) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/40">
                                <a href="{{ route('user.followups.show', $fu) }}"
                                   class="px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200">
                                    مشاهده
                                </a>
                                @canany(['followups.edit', 'followups.manage'])
                                    <a href="{{ route('user.followups.edit', $fu) }}"
                                       class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100">
                                        ویرایش
                                    </a>
                                @endcanany
                                @can('followups.delete')
                                    <form method="POST" action="{{ route('user.followups.destroy', $fu) }}"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.delete-button type="submit" label="" confirm-label="حذف؟" size="sm"/>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($followups->total() > 0)
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40 rounded-b-2xl">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <div>
                                نمایش
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ $faNum($followups->firstItem() ?? 0) }}</span>
                                تا
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ $faNum($followups->lastItem() ?? 0) }}</span>
                                از مجموع
                                <span
                                    class="font-black text-indigo-600 dark:text-indigo-400">{{ $faNum($followups->total()) }}</span>
                                پیگیری ثبت‌شده
                            </div>

                            <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">•</span>

                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400 text-xs">تعداد در هر صفحه:</span>
                                <select name="per_page" aria-label="تعداد در هر صفحه"
                                        onchange="window.location.href = this.value"
                                        class="py-1 px-2.5 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer shadow-2xs">
                                    @foreach([10, 15, 25, 50, 100] as $size)
                                        <option
                                            value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                                            {{ (int)$followups->perPage() === $size ? 'selected' : '' }}>
                                            {{ $faNum($size) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            @if($followups->hasPages())
                                {{ $followups->appends(request()->query())->links('themes.admin-pro.partials.pagination') }}
                            @else
                                <nav role="navigation" aria-label="Pagination Navigation"
                                     class="inline-flex items-center gap-1.5">
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-md shadow-indigo-500/25 select-none">
                                        {{ $faNum(1) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </span>
                                </nav>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <x-ui.dock/>

        <x-ui.toast position="bottom-left"/>
    </div>
@endsection
