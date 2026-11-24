@extends('layouts.user')

@php
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;

    $statuses   = $statuses   ?? Task::statusOptions();
    $priorities = $priorities ?? Task::priorityOptions();
    $types      = $types      ?? Task::typeOptions();
    $title      = 'مدیریت و پیگیری وظایف';

    // تبدیل ارقام به فارسی
    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $totalCount = $tasks->total();
    $doneCount = $tasks->where('status', Task::STATUS_DONE)->count();
    $inProgressCount = $tasks->where('status', Task::STATUS_IN_PROGRESS)->count();
    $criticalCount = $tasks->whereIn('priority', [Task::PRIORITY_HIGH, Task::PRIORITY_CRITICAL])->count();

    $userFilterOptions = collect($users ?? [])->map(fn($u) => [
        'value'   => (string) $u->id,
        'label'   => $u->name,
        'initial' => mb_substr($u->name, 0, 1),
    ])->all();

    $sortOptions = [
        'newest'   => 'جدیدترین',
        'oldest'   => 'قدیمی‌ترین',
        'due_asc'  => 'سررسید (نزدیک‌ترین)',
        'due_desc' => 'سررسید (دورترین)',
    ];
@endphp

@section('title', 'مدیریت وظایف')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data="{ selectedIds: [], allChecked: false, bulkStatus: '', bulkAction: 'status', toFa(n) { return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]); } }">
        {{-- هدر صفحه با بردکرامب و دیزاین کارت منسجم --}}
        <header
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            {{-- بردکرامب ناوبری --}}
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">مدیریت وظایف</span>
            </nav>

            {{-- ردیف هدر: آیکون، عنوان و دکمه‌های عملیاتی در یک راستا هم روی موبایل و هم روی دسکتاپ --}}
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
                            مدیریت و پیگیری وظایف
                        </h1>
                    </div>
                </div>

                {{-- دکمه ایجاد وظیفه جدید --}}
                <div class="flex items-center gap-2 shrink-0">
                    @can('tasks.create')
                        <a href="{{ route('user.tasks.create') }}"
                           class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline">ایجاد وظیفه جدید</span>
                            <span class="sm:hidden">وظیفه جدید</span>
                        </a>
                    @endcan
                </div>
            </div>
        </header>

        {{-- هشدارهای سیستمی --}}
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

        {{-- کارت‌های آماری شاخص‌ها --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- کارت ۱: کل وظایف --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل وظایف</span>
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
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">در حال انجام</span>
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

            {{-- کارت ۳: تکمیل‌شده --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">انجام‌شده</span>
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

            {{-- کارت ۴: اولویت فوری --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">اولویت بالا و بحرانی</span>
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

        {{-- فرم فیلتر و جستجو --}}
        <form method="GET" action="{{ route('user.tasks.index') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 transition-all">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                {{-- فیلد جستجوی متنی --}}
                <div
                    class="relative {{ (auth()->user()->can('tasks.view.all') && !empty($users)) ? 'sm:col-span-2 lg:col-span-4' : 'sm:col-span-1 lg:col-span-3' }}">
                    <div class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="جستجو در عنوان یا توضیحات وظیفه..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 ps-10 pe-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden">
                </div>

                {{-- فیلتر کاربر مسئول --}}
                @if(auth()->user()->can('tasks.view.all') && !empty($users))
                    <div class="sm:col-span-1 lg:col-span-4">
                        <x-ui.select
                            name="assignee_id"
                            :options="$userFilterOptions"
                            :value="request('assignee_id')"
                            placeholder="همه مسئولین"
                            searchPlaceholder="جستجوی مسئول..."
                            :clearable="true"
                        />
                    </div>
                @endif

                {{-- فیلتر وضعیت --}}
                <div
                    class="{{ (auth()->user()->can('tasks.view.all') && !empty($users)) ? 'sm:col-span-1 lg:col-span-4' : 'sm:col-span-1 lg:col-span-2' }}">
                    <x-ui.select
                        name="status"
                        :options="$statuses"
                        :value="request('status')"
                        placeholder="همه وضعیت‌ها"
                        :searchable="false"
                        :clearable="true"
                    />
                </div>

                {{-- فیلتر نوع وظیفه --}}
                <div
                    class="{{ (auth()->user()->can('tasks.view.all') && !empty($users)) ? 'sm:col-span-1 lg:col-span-3' : 'sm:col-span-1 lg:col-span-2' }}">
                    <x-ui.select
                        name="task_type"
                        :options="$types"
                        :value="request('task_type')"
                        placeholder="همه انواع وظیفه"
                        :searchable="false"
                        :clearable="true"
                    />
                </div>

                {{-- فیلتر اولویت --}}
                <div
                    class="{{ (auth()->user()->can('tasks.view.all') && !empty($users)) ? 'sm:col-span-1 lg:col-span-3' : 'sm:col-span-1 lg:col-span-1' }}">
                    <x-ui.select
                        name="priority"
                        :options="$priorities"
                        :value="request('priority')"
                        placeholder="همه اولویت‌ها"
                        :searchable="false"
                        :clearable="true"
                    />
                </div>

                {{-- فیلتر مرتب‌سازی --}}
                <div
                    class="{{ (auth()->user()->can('tasks.view.all') && !empty($users)) ? 'sm:col-span-1 lg:col-span-3' : 'sm:col-span-1 lg:col-span-2' }}">
                    <x-ui.select
                        name="sort"
                        :options="$sortOptions"
                        :value="request('sort', 'newest')"
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                {{-- دکمه‌های ثبت و بازنشانی فیلتر --}}
                <div
                    class="flex items-center gap-1.5 {{ (auth()->user()->can('tasks.view.all') && !empty($users)) ? 'sm:col-span-1 lg:col-span-3' : 'sm:col-span-1 lg:col-span-2' }} justify-end">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-black shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->hasAny(['q', 'status', 'task_type', 'priority', 'assignee_id', 'sort', 'per_page']))
                        <a href="{{ route('user.tasks.index') }}"
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

        {{-- جدول و کارت داده‌ها --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
            {{-- هدر بالای جدول با شمارنده فارسی --}}
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/40 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">لیست وظایف</h3>
                </div>
            </div>

            @if($tasks->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <div
                        class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">هیچ وظیفه‌ای یافت نشد</h4>
                    <p class="text-xs text-gray-400">هنوز وظیفه‌ای با این مشخصات ثبت نشده یا فیلترهای جستجو موردی
                        نیافتند.</p>
                </div>
            @else
                {{-- نسخه دسکتاپ: جدول منظم با فونت و پدینگ استاندارد --}}
                <div
                    class="hidden md:block overflow-x-auto [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">
                    <table class="w-full text-right text-sm">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700/40 text-xs font-black text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="w-12 px-4 py-3.5 text-center">
                                <input type="checkbox"
                                       x-model="allChecked"
                                       @change="selectedIds = allChecked ? [{{ implode(',', $tasks->pluck('id')->toArray()) }}] : []"
                                       class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </th>
                            <th class="px-4 py-3.5 w-14 text-center">#</th>
                            <th class="px-5 py-3.5">عنوان وظیفه</th>
                            <th class="px-5 py-3.5">مخاطب / موجودیت</th>
                            <th class="px-4 py-3.5 w-28">نوع</th>
                            <th class="px-5 py-3.5 w-36">مسئول</th>
                            <th class="px-4 py-3.5 w-28">وضعیت</th>
                            <th class="px-4 py-3.5 w-24">اولویت</th>
                            <th class="px-5 py-3.5 w-36">سررسید</th>
                            <th class="px-5 py-3.5 w-28 text-left pl-6">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40 font-medium">
                        @foreach($tasks as $task)
                            @php
                                $statusVariant = match($task->status) {
                                    Task::STATUS_DONE => 'success',
                                    Task::STATUS_IN_PROGRESS => 'info',
                                    Task::STATUS_CANCELED => 'danger',
                                    default => 'neutral'
                                };

                                $priorityVariant = match($task->priority) {
                                    Task::PRIORITY_CRITICAL => 'danger',
                                    Task::PRIORITY_HIGH => 'warning',
                                    Task::PRIORITY_MEDIUM => 'primary',
                                    default => 'neutral'
                                };

                                $isOverdue = $task->due_at && $task->due_at->isPast() && !in_array($task->status, [Task::STATUS_DONE, Task::STATUS_CANCELED]);
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/40 transition-colors group">
                                {{-- چک‌باکس --}}
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox"
                                           :value="{{ $task->id }}"
                                           x-model="selectedIds"
                                           @change="allChecked = (selectedIds.length === {{ $tasks->count() }})"
                                           class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                </td>

                                {{-- شناسه --}}
                                <td class="px-4 py-4 text-center text-xs font-bold text-gray-400">
                                    #{{ $faNum($task->id) }}
                                </td>

                                {{-- عنوان --}}
                                <td class="px-5 py-4">
                                    <div class="space-y-1">
                                        <a href="{{ route('user.tasks.show', $task) }}"
                                           class="font-black text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-1">
                                            {{ $task->title }}
                                        </a>
                                        @if($task->description)
                                            <p class="text-xs text-gray-400 line-clamp-1 font-normal">
                                                {{ Str::limit($task->description, 80) }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                {{-- موجودیت مرتبط --}}
                                <td class="px-5 py-4">
                                    @if($task->related_type === Task::RELATED_TYPE_CLIENT && $task->relatedClient)
                                        <div class="flex items-center gap-2">
                                            <div class="min-w-0">
                                                <a href="{{ route('user.clients.show', $task->relatedClient) }}"
                                                   class="font-bold text-xs text-gray-800 dark:text-gray-200 hover:text-emerald-600 dark:hover:text-emerald-400 truncate block">
                                                    {{ $task->relatedClient->full_name }}
                                                </a>
                                                @if(optional($task->relatedClient->status)->name)
                                                    <span
                                                        class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        {{ $task->relatedClient->status->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($task->related_type === Task::RELATED_TYPE_USER && $task->relatedUser)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-black shrink-0">
                                                {{ mb_substr($task->relatedUser->name, 0, 1) }}
                                            </div>
                                            <span class="font-bold text-xs text-gray-800 dark:text-gray-200 truncate">
                                                {{ $task->relatedUser->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- نوع --}}
                                <td class="px-4 py-4">
                                    <span
                                        class="px-2 py-0.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700/70 dark:text-gray-300">
                                        {{ $types[$task->task_type] ?? $task->task_type }}
                                    </span>
                                </td>

                                {{-- مسئول --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">
                                            {{ $task->assignee_name }}
                                        </span>
                                    </div>
                                </td>

                                {{-- وضعیت --}}
                                <td class="px-4 py-4">
                                    <x-ui.badge :variant="$statusVariant" size="sm" dot>
                                        {{ $statuses[$task->status] ?? $task->status }}
                                    </x-ui.badge>
                                </td>

                                {{-- اولویت --}}
                                <td class="px-4 py-4">
                                    <x-ui.badge :variant="$priorityVariant" size="sm">
                                        {{ $priorities[$task->priority] ?? $task->priority }}
                                    </x-ui.badge>
                                </td>

                                {{-- سررسید --}}
                                <td class="px-5 py-4">
                                    @if($task->due_at)
                                        <div class="space-y-0.5">
                                            <span
                                                class="text-xs font-bold block {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $faNum(Jalalian::fromCarbon($task->due_at)->format('Y/m/d')) }}
                                            </span>
                                            <span
                                                class="text-[10px] block {{ $isOverdue ? 'text-rose-500 font-bold' : 'text-gray-400' }}">
                                                {{ $isOverdue ? 'منقضی شده' : $faNum(Jalalian::fromCarbon($task->due_at)->format('H:i')) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- ستون عملیات --}}
                                <td class="px-5 py-4 text-left pl-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- مشاهده --}}
                                        <a href="{{ route('user.tasks.show', $task) }}"
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

                                        {{-- ویرایش --}}
                                        @canany(['tasks.edit', 'tasks.manage'])
                                            <a href="{{ route('user.tasks.edit', $task) }}"
                                               class="p-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer shadow-2xs"
                                               title="ویرایش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcanany

                                        {{-- علامت‌گذاری به عنوان انجام شده --}}
                                        @canany(['tasks.edit', 'tasks.manage'])
                                            @if($task->status !== Task::STATUS_DONE)
                                                <form method="POST" action="{{ route('user.tasks.bulk-update') }}" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="action" value="status">
                                                    <input type="hidden" name="status" value="{{ Task::STATUS_DONE }}">
                                                    <input type="hidden" name="ids[]" value="{{ $task->id }}">
                                                    <x-ui.complete-button
                                                        type="submit"
                                                        label=""
                                                        confirm-label="انجام شد؟"
                                                        size="sm"
                                                        title="علامت‌گذاری وظیفه به عنوان انجام شده"
                                                    />
                                                </form>
                                            @endif
                                        @endcanany

                                        {{-- حذف --}}
                                        @can('tasks.delete')
                                            <form method="POST" action="{{ route('user.tasks.destroy', $task) }}"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.delete-button
                                                    type="submit"
                                                    label=""
                                                    confirm-label="حذف؟"
                                                    size="sm"
                                                    title="حذف وظیفه"
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

                {{-- نسخه موبایل: کارت‌های منظم و شکیل مستقل --}}
                <div class="md:hidden p-3.5 sm:p-4 space-y-3.5 bg-gray-50/50 dark:bg-gray-900/30">
                    @foreach($tasks as $task)
                        @php
                            $statusVariant = match($task->status) {
                                Task::STATUS_DONE => 'emerald',
                                Task::STATUS_IN_PROGRESS => 'blue',
                                Task::STATUS_CANCELED => 'rose',
                                default => 'slate'
                            };

                            $priorityVariant = match($task->priority) {
                                Task::PRIORITY_CRITICAL => 'rose',
                                Task::PRIORITY_HIGH => 'rose',
                                Task::PRIORITY_MEDIUM => 'indigo',
                                default => 'slate'
                            };
                        @endphp
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/60 shadow-xs space-y-3.5 transition-all hover:border-indigo-100 dark:hover:border-indigo-900/40">
                            {{-- ردیف چک‌باکس، عنوان وظیفه و شناسه --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <input type="checkbox"
                                           :value="{{ $task->id }}"
                                           x-model="selectedIds"
                                           class="mt-1 rounded-md border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                    <div class="min-w-0">
                                        <a href="{{ route('user.tasks.show', $task) }}"
                                           class="font-black text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2 transition-colors">
                                            {{ $task->title }}
                                        </a>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40 shrink-0">
                                    #{{ $faNum($task->id) }}
                                </span>
                            </div>

                            {{-- بج‌های وضعیت، اولویت و نوع وظیفه --}}
                            <div class="flex flex-wrap items-center gap-1.5">
                                <x-ui.badge :variant="$statusVariant" size="sm" dot>
                                    {{ $statuses[$task->status] ?? $task->status }}
                                </x-ui.badge>
                                <x-ui.badge :variant="$priorityVariant" size="sm">
                                    {{ $priorities[$task->priority] ?? $task->priority }}
                                </x-ui.badge>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700/70 dark:text-gray-300">
                                    {{ $types[$task->task_type] ?? $task->task_type }}
                                </span>
                            </div>

                            {{-- توضیحات خلاصه --}}
                            @if($task->description)
                                <div
                                    class="p-2.5 rounded-xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $task->description }}
                                </div>
                            @endif

                            {{-- شبکه اطلاعات: مسئول، سررسید و موجودیت مرتبط --}}
                            <div
                                class="grid grid-cols-2 gap-2 text-xs pt-2.5 border-t border-gray-100 dark:border-gray-700/40">
                                <div class="p-2 rounded-xl bg-gray-50/60 dark:bg-gray-900/40">
                                    <span class="text-gray-400 text-[10.5px] block font-bold mb-0.5">مسئول انجام:</span>
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $task->assignee_name }}</span>
                                </div>
                                <div class="p-2 rounded-xl bg-gray-50/60 dark:bg-gray-900/40">
                                    <span class="text-gray-400 text-[10.5px] block font-bold mb-0.5">موعد سررسید:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 block dir-ltr text-right">
                                        {{ $task->due_at ? $faNum(Jalalian::fromCarbon($task->due_at)->format('Y/m/d')) : '—' }}
                                    </span>
                                </div>
                                {{-- موجودیت یا مخاطب مرتبط --}}
                                @if($task->related_type === Task::RELATED_TYPE_CLIENT && $task->relatedClient)
                                    <div
                                        class="col-span-2 p-2.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100/70 dark:border-emerald-900/40 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div
                                                class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-[10px] shrink-0">
                                                {{ mb_substr($task->relatedClient->full_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-gray-400 text-[10px] font-bold">مخاطب:</span>
                                                    <a href="{{ route('user.clients.show', $task->relatedClient) }}"
                                                       class="font-black text-xs text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 truncate">
                                                        {{ $task->relatedClient->full_name }}
                                                    </a>
                                                    @if(optional($task->relatedClient->status)->name)
                                                        <span
                                                            class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                                            {{ $task->relatedClient->status->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($task->relatedClient->phone)
                                                    <div
                                                        class="text-[10px] text-gray-500 dark:text-gray-400 dir-ltr text-right font-medium">
                                                        {{ $faNum($task->relatedClient->phone) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if($task->relatedClient->case_number)
                                            <span
                                                class="text-[10px] font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/40 px-2 py-0.5 rounded-md shrink-0">
                                                پرونده: {{ $faNum($task->relatedClient->case_number) }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($task->related_type === Task::RELATED_TYPE_USER && $task->relatedUser)
                                    <div
                                        class="col-span-2 p-2.5 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100/70 dark:border-blue-900/40 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div
                                                class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-[10px] shrink-0">
                                                {{ mb_substr($task->relatedUser->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0 flex items-center gap-1.5">
                                                <span class="text-gray-400 text-[10px] font-bold">همکار مرتبط:</span>
                                                <span class="font-black text-xs text-gray-900 dark:text-white truncate">
                                                    {{ $task->relatedUser->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- دکمه‌های عملیاتی --}}
                            <div
                                class="flex flex-wrap items-center justify-end gap-2 pt-2.5 border-t border-gray-100 dark:border-gray-700/40">
                                <a href="{{ route('user.tasks.show', $task) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>مشاهده</span>
                                </a>
                                @canany(['tasks.edit', 'tasks.manage'])
                                    <a href="{{ route('user.tasks.edit', $task) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>ویرایش</span>
                                    </a>
                                @endcanany
                                @canany(['tasks.edit', 'tasks.manage'])
                                    @if($task->status !== Task::STATUS_DONE)
                                        <form method="POST" action="{{ route('user.tasks.bulk-update') }}" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="action" value="status">
                                            <input type="hidden" name="status" value="{{ Task::STATUS_DONE }}">
                                            <input type="hidden" name="ids[]" value="{{ $task->id }}">
                                            <x-ui.complete-button
                                                type="submit"
                                                label=""
                                                confirm-label="انجام شد؟"
                                                size="sm"
                                                title="علامت‌گذاری وظیفه به عنوان انجام شده"
                                            />
                                        </form>
                                    @endif
                                @endcanany
                                @can('tasks.delete')
                                    <form method="POST" action="{{ route('user.tasks.destroy', $task) }}"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.delete-button type="submit" label="" confirm-label="حذف؟" size="sm"
                                                            title="حذف وظیفه"/>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- پیجینیشن غنی با اعداد فارسی و دراپ‌داون تعداد --}}
                @if($tasks->total() > 0)
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40 rounded-b-2xl">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <div>
                                نمایش
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ $faNum($tasks->firstItem() ?? 0) }}</span>
                                تا
                                <span
                                    class="font-black text-gray-900 dark:text-white">{{ $faNum($tasks->lastItem() ?? 0) }}</span>
                                از مجموع
                                <span
                                    class="font-black text-indigo-600 dark:text-indigo-400">{{ $faNum($tasks->total()) }}</span>
                                وظیفه ثبت‌شده
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
                                            {{ (int)$tasks->perPage() === $size ? 'selected' : '' }}>
                                            {{ $faNum($size) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            @if($tasks->hasPages())
                                {{ $tasks->appends(request()->query())->links('themes.admin-pro.partials.pagination') }}
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

        {{-- نوار شناور و مدرن عملیات گروهی در فوتر (Floating Action Island) --}}
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
                <form method="POST" action="{{ route('user.tasks.bulk-update') }}"
                      class="flex items-center gap-1.5 sm:gap-3 w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="action" x-model="bulkAction">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>

                    {{-- چیپ یکپارچه شمارنده با دکمه لغو سریع --}}
                    <div
                        class="inline-flex items-center gap-1.5 sm:gap-2 h-9 px-2 sm:px-3 rounded-full bg-indigo-50/90 dark:bg-indigo-950/50 border border-indigo-100/90 dark:border-indigo-800/60 text-indigo-950 dark:text-indigo-100 shadow-2xs shrink-0 select-none">
                        <span
                            class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black leading-none shadow-xs"
                            x-text="toFa(selectedIds.length)"></span>
                        <span class="text-xs font-black whitespace-nowrap">
                            <span class="hidden sm:inline">وظیفه </span>انتخاب
                        </span>
                        <button type="button"
                                @click="selectedIds = []; allChecked = false"
                                class="inline-flex items-center justify-center w-5 h-5 -me-1 rounded-full text-indigo-400 hover:text-indigo-700 dark:hover:text-white hover:bg-indigo-200/50 dark:hover:bg-indigo-800/60 transition-colors cursor-pointer"
                                title="لغو انتخاب همه">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- خط جداکننده عمودی در دسکتاپ --}}
                    <div class="hidden sm:block h-6 w-px bg-gray-200/80 dark:bg-gray-700/80 shrink-0"></div>

                    {{-- بخش تغییر وضعیت: دراپ‌داون وضعیت و دکمه ثبت --}}
                    @canany(['tasks.edit', 'tasks.manage'])
                        <div class="flex items-center gap-1.5 flex-1 sm:flex-initial min-w-0"
                             @status-changed.window="bulkStatus = $event.detail">
                            <div class="flex-1 sm:flex-initial sm:w-44 min-w-0">
                                <x-ui.select
                                    name="status"
                                    :options="$statuses"
                                    placeholder="وضعیت..."
                                    placement="top"
                                    size="sm"
                                    rounded="full"
                                    :searchable="false"
                                    :clearable="false"
                                />
                            </div>

                            <button type="submit"
                                    @click="bulkAction = 'status'"
                                    name="action"
                                    value="status"
                                    :disabled="!bulkStatus"
                                    class="h-9 px-2.5 sm:px-4 rounded-full text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-indigo-600 transition-all cursor-pointer shadow-xs shadow-indigo-600/20 active:scale-95 inline-flex items-center justify-center gap-1 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="whitespace-nowrap">ثبت</span>
                            </button>
                        </div>
                    @endcanany

                    {{-- خط جداکننده عمودی --}}
                    <div class="h-5 sm:h-6 w-px bg-gray-200/80 dark:bg-gray-700/80 shrink-0"></div>

                    {{-- دکمه حذف گروهی --}}
                    @can('tasks.delete')
                        <div @click="bulkAction = 'delete'" class="shrink-0 flex items-center">
                            <x-ui.delete-button
                                type="submit"
                                label=""
                                confirm-label="حذف؟"
                                cancel-label="انصراف"
                                size="sm"
                                title="حذف گروهی وظایف انتخاب شده"
                            />
                        </div>
                    @endcan
                </form>
            </div>
        </div>

        {{-- فاصله تضمین‌شده انتهای صفحه در موبایل برای جلوگیری از هرگونه تداخل با داک ناوبری --}}
        <div class="h-28 md:hidden" style="height: 7rem;" aria-hidden="true"></div>
    </div>
@endsection
