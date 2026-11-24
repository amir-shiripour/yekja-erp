@extends('layouts.user')

@php
    use Modules\Reminders\Entities\Reminder;
    use Modules\Tasks\Entities\Task;
    use Modules\FollowUps\Entities\FollowUp;
    use Morilog\Jalali\Jalalian;

    $status = $status ?? 'open';
    $period = $period ?? 'today';

    // تابع کمکی تبدیل ارقام به فارسی
    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $taskCount = $taskReminders->count();
    $followUpCount = $followUpReminders->count();
    $totalRemindersCount = $taskCount + $followUpCount;
    $openCount = $taskReminders->whereIn('status', [Reminder::STATUS_OPEN, Reminder::STATUS_ESCALATED])->count()
               + $followUpReminders->whereIn('status', [Reminder::STATUS_OPEN, Reminder::STATUS_ESCALATED])->count();

    // گزینه‌های وضعیت فیلتر
    $statusFilterOptionsList = collect($statusFilterOptions ?? [
        'open'     => 'باز (در انتظار)',
        'done'     => 'انجام‌شده',
        'canceled' => 'لغو شده',
        'all'      => 'همه',
    ])->map(fn($label, $key) => [
        'value' => (string) $key,
        'label' => $label,
    ])->values()->all();

    // گزینه‌های بازه زمانی فیلتر
    $periodOptionsList = collect($periodOptions ?? [
        'today'  => 'امروز',
        'week'   => 'این هفته',
        'month'  => 'این ماه',
        'year'   => 'امسال',
        'custom' => 'بازه دلخواه',
        'all'    => 'بدون فیلتر زمانی',
    ])->map(fn($label, $key) => [
        'value' => (string) $key,
        'label' => $label,
    ])->values()->all();

    // گزینه‌های کاربر در صورت دسترسی
    $userFilterOptionsList = collect($users ?? [])->map(fn($u) => [
        'value'   => (string) $u->id,
        'label'   => $u->name,
        'initial' => mb_substr($u->name, 0, 1),
    ])->all();

    // گزینه‌های عملیات گروهی تغییر وضعیت
    $bulkStatusOptions = [
        ['value' => Reminder::STATUS_DONE,     'label' => 'انجام شد'],
        ['value' => Reminder::STATUS_CANCELED, 'label' => 'لغو شد'],
        ['value' => Reminder::STATUS_OPEN,     'label' => 'باز (در انتظار)'],
    ];

    // ترکیب و مرتب‌سازی کل یادآوری‌ها برای نمایش یکپارچه در تب‌ها
    $priorityWeight = [
        Task::PRIORITY_CRITICAL => 4,
        Task::PRIORITY_HIGH     => 3,
        Task::PRIORITY_MEDIUM   => 2,
        Task::PRIORITY_LOW      => 1,
    ];

    $allReminders = $taskReminders->map(function ($r) {
        $r->reminder_scope = 'tasks';
        return $r;
    })->concat($followUpReminders->map(function ($r) {
        $r->reminder_scope = 'followups';
        return $r;
    }))->sort(function ($a, $b) use ($priorityWeight) {
        $taskA = $a->task ?? $a->followUp;
        $taskB = $b->task ?? $b->followUp;
        $prioA = $priorityWeight[$taskA->priority ?? Task::PRIORITY_MEDIUM] ?? 2;
        $prioB = $priorityWeight[$taskB->priority ?? Task::PRIORITY_MEDIUM] ?? 2;
        if ($prioA !== $prioB) {
            return $prioB <=> $prioA;
        }
        return ($a->remind_at ?? now())->timestamp <=> ($b->remind_at ?? now())->timestamp;
    });

    $allRemindersIds = $allReminders->pluck('id')->map(fn($id) => (string)$id)->values()->all();
    $taskRemindersIds = $taskReminders->pluck('id')->map(fn($id) => (string)$id)->values()->all();
    $followUpRemindersIds = $followUpReminders->pluck('id')->map(fn($id) => (string)$id)->values()->all();
    $remindersIdsMap = [
        'all' => $allRemindersIds,
        'tasks' => $taskRemindersIds,
        'followups' => $followUpRemindersIds,
    ];

    // تنظیمات استایل اولویت
    $priorityConfig = [
        Task::PRIORITY_CRITICAL => ['label' => 'بحرانی', 'variant' => 'rose'],
        Task::PRIORITY_HIGH     => ['label' => 'زیاد',    'variant' => 'amber'],
        Task::PRIORITY_MEDIUM   => ['label' => 'معمولی',  'variant' => 'blue'],
        Task::PRIORITY_LOW      => ['label' => 'کم',      'variant' => 'secondary'],
    ];

    // تنظیمات استایل وضعیت
    $statusBadgeConfig = [
        Reminder::STATUS_OPEN      => ['label' => 'باز',         'variant' => 'primary'],
        Reminder::STATUS_DONE      => ['label' => 'انجام‌شده',    'variant' => 'emerald'],
        Reminder::STATUS_CANCELED  => ['label' => 'لغو شده',      'variant' => 'secondary'],
        Reminder::STATUS_ESCALATED => ['label' => 'ارجاع شده',   'variant' => 'rose'],
    ];
@endphp

@section('title', 'مدیریت یادآوری‌ها')

@section('content')
    <script>
        function remindersManager(allIds) {
            return {
                selectedIds: [],
                bulkStatus: '',
                bulkAction: 'status',
                activeTab: 'all',
                allIds: allIds,
                toFa(n) {
                    return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                },
                getCurrentIds() {
                    return (this.allIds[this.activeTab] || this.allIds.all || []).map(String);
                },
                get isAllChecked() {
                    const ids = this.getCurrentIds();
                    return ids.length > 0 && ids.every(id => this.selectedIds.map(String).includes(String(id)));
                },
                toggleAll() {
                    const ids = this.getCurrentIds();
                    if (this.isAllChecked) {
                        this.selectedIds = this.selectedIds.map(String).filter(id => !ids.includes(String(id)));
                    } else {
                        this.selectedIds = [...new Set([...this.selectedIds.map(String), ...ids])];
                    }
                }
            };
        }
    </script>

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data='remindersManager(@json($remindersIdsMap))'>

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
                <span class="text-indigo-600 dark:text-indigo-400 font-black">مدیریت یادآوری‌ها</span>
            </nav>

            {{-- ردیف هدر: آیکون، عنوان و دکمه‌های عملیاتی در یک راستا هم روی موبایل و هم روی دسکتاپ --}}
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
                            مدیریت و پیگیری یادآوری‌ها
                        </h1>
                    </div>
                </div>

                {{-- دکمه‌های عملیاتی هدر (تنظیمات یادآوری‌ها) --}}
                <div class="flex items-center gap-2 shrink-0">
                    @can('reminders.settings.view')
                        <a href="{{ route('user.reminders.settings.index') }}"
                           class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="hidden sm:inline">تنظیمات یادآوری‌ها</span>
                            <span class="sm:hidden">تنظیمات</span>
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

        {{-- کارت‌های آماری شاخص‌ها (4 KPI Cards) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- کارت ۱: کل یادآوری‌ها --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل یادآوری‌ها</span>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ $faNum($totalRemindersCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>

            {{-- کارت ۲: یادآوری‌های وظایف --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">یادآوری‌های وظایف</span>
                    <p class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-1.5">{{ $faNum($taskCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>

            {{-- کارت ۳: یادآوری‌های پیگیری --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">یادآوری‌های پیگیری</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">{{ $faNum($followUpCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
            </div>

            {{-- کارت ۴: در انتظار انجام --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span
                        class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">در انتظار انجام</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $faNum($openCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- فرم فیلتر و جستجو --}}
        <form method="GET" action="{{ route('user.reminders.index') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 transition-all">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                {{-- فیلتر وضعیت --}}
                <div
                    class="{{ (auth()->user()->can('reminders.manage') || auth()->user()->can('reminders.view')) && !empty($users) ? 'sm:col-span-1 lg:col-span-2' : 'sm:col-span-1 lg:col-span-3' }}">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">وضعیت یادآوری</label>
                    <x-ui.select
                        name="status"
                        :options="$statusFilterOptionsList"
                        :value="$status"
                        placeholder="وضعیت..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                {{-- فیلتر بازه زمانی --}}
                <div
                    class="{{ (auth()->user()->can('reminders.manage') || auth()->user()->can('reminders.view')) && !empty($users) ? 'sm:col-span-1 lg:col-span-2' : 'sm:col-span-1 lg:col-span-3' }}">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">بازه زمانی</label>
                    <x-ui.select
                        name="period"
                        :options="$periodOptionsList"
                        :value="$period"
                        placeholder="بازه زمانی..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                {{-- فیلتر کاربر --}}
                @if((auth()->user()->can('reminders.manage') || auth()->user()->can('reminders.view')) && !empty($users))
                    <div class="sm:col-span-1 lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">کاربر</label>
                        <x-ui.select
                            name="user_id"
                            :options="$userFilterOptionsList"
                            :value="$selectedUserId"
                            placeholder="انتخاب کاربر..."
                            searchPlaceholder="جستجوی کاربر..."
                            :clearable="false"
                        />
                    </div>
                @endif

                {{-- فیلتر بازه تاریخ جلالی --}}
                <div
                    class="{{ (auth()->user()->can('reminders.manage') || auth()->user()->can('reminders.view')) && !empty($users) ? 'sm:col-span-2 lg:col-span-3' : 'sm:col-span-2 lg:col-span-4' }}">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">بازه دلخواه تاریخ
                        سررسید</label>
                    <div class="grid grid-cols-2 gap-2" x-data>
                        <div class="relative">
                            <input type="text"
                                   name="from_jalali"
                                   data-jdp-only-date
                                   value="{{ request('from_jalali') }}"
                                   placeholder="از: ۱۴۰۳/۰۱/۰۱"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden text-center">
                        </div>
                        <div class="relative">
                            <input type="text"
                                   name="to_jalali"
                                   data-jdp-only-date
                                   value="{{ request('to_jalali') }}"
                                   placeholder="تا: ۱۴۰۳/۱۲/۲۹"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden text-center">
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های اعمال و بازنشانی فیلتر --}}
                <div class="sm:col-span-2 lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>اعمال فیلتر</span>
                    </button>

                    @if(request()->anyFilled(['status', 'period', 'from_jalali', 'to_jalali', 'user_id']))
                        <a href="{{ route('user.reminders.index') }}"
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

        {{-- نوار سگمنت سوئیچر تب‌ها (همه / وظایف / پیگیری‌ها) --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div
                class="inline-flex p-1 bg-gray-100/80 dark:bg-gray-900/60 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 backdrop-blur-md">
                <button type="button"
                        @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer">
                    <span>همه</span>
                    <span
                        class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] leading-none"
                        :class="activeTab === 'all' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        {{ $faNum($totalRemindersCount) }}
                    </span>
                </button>

                <button type="button"
                        @click="activeTab = 'tasks'"
                        :class="activeTab === 'tasks' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer">
                    <span>وظایف</span>
                    <span
                        class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] leading-none"
                        :class="activeTab === 'tasks' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        {{ $faNum($taskCount) }}
                    </span>
                </button>

                <button type="button"
                        @click="activeTab = 'followups'"
                        :class="activeTab === 'followups' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer">
                    <span>پیگیری‌ها</span>
                    <span
                        class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] leading-none"
                        :class="activeTab === 'followups' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">
                        {{ $faNum($followUpCount) }}
                    </span>
                </button>
            </div>

            <div class="text-xs font-bold text-gray-400 dark:text-gray-500">
                <span>نمایش {{ $faNum($totalRemindersCount) }} یادآوری</span>
            </div>
        </div>

        {{-- جدول یادآوری‌ها در دسکتاپ --}}
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
                        <th class="py-3 px-4 text-start">شرح و پیام یادآوری</th>
                        <th class="py-3 px-4 text-start">مرجع مرتبط</th>
                        <th class="py-3 px-4 text-start">مشتری / کاربر</th>
                        <th class="py-3 px-4 text-center">اولویت</th>
                        <th class="py-3 px-4 text-center">زمان سررسید</th>
                        <th class="py-3 px-4 text-center">وضعیت</th>
                        <th class="py-3 px-4 text-center w-28">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                    @forelse($allReminders as $reminder)
                        @php
                            $task = $reminder->task;
                            $followUp = $reminder->followUp ?? $task;
                            $relatedItem = $reminder->reminder_scope === 'tasks' ? $task : $followUp;
                            $remindJalali = $reminder->remind_at ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i') : '—';
                            $isOverdue = $reminder->remind_at && $reminder->remind_at->isPast() && in_array($reminder->status, [Reminder::STATUS_OPEN, Reminder::STATUS_ESCALATED]);

                            $prioVal = $relatedItem->priority ?? Task::PRIORITY_MEDIUM;
                            $prio = $priorityConfig[$prioVal] ?? ['label' => 'معمولی', 'variant' => 'blue'];
                            $stat = $statusBadgeConfig[$reminder->status] ?? ['label' => $reminder->status, 'variant' => 'secondary'];

                            // اطلاعات مشتری یا کاربر مرتبط
                            $relatedModel = null;
                            $relatedUrl = null;
                            $isClient = false;
                            if ($relatedItem) {
                                if ($relatedItem->related_type === Task::RELATED_TYPE_CLIENT && $relatedItem->relatedClient) {
                                    if (auth()->user()->can('clients.view') && $relatedItem->relatedClient->isVisibleFor(auth()->user())) {
                                        $relatedModel = $relatedItem->relatedClient;
                                        $relatedUrl = route('user.clients.show', $relatedItem->relatedClient->id);
                                        $isClient = true;
                                    }
                                } elseif ($relatedItem->related_type === Task::RELATED_TYPE_USER && $relatedItem->relatedUser) {
                                    if (auth()->user()->can('users.view')) {
                                        $relatedModel = $relatedItem->relatedUser;
                                    }
                                }
                            }
                        @endphp
                        <tr id="reminder-row-{{ $reminder->id }}"
                            data-scope="{{ $reminder->reminder_scope }}"
                            x-show="activeTab === 'all' || activeTab === '{{ $reminder->reminder_scope }}'"
                            class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition-colors group">
                            {{-- چک‌باکس انتخاب --}}
                            <td class="py-3.5 px-4 text-center align-middle">
                                <input type="checkbox"
                                       value="{{ $reminder->id }}"
                                       x-model="selectedIds"
                                       class="reminder-cb rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            {{-- شرح یادآوری --}}
                            <td class="py-3.5 px-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 shrink-0">
                                        #{{ $faNum($reminder->id) }}
                                    </span>
                                    <div class="font-bold text-gray-900 dark:text-white line-clamp-1 min-w-0"
                                         title="{{ $reminder->message }}">
                                        {{ $reminder->message ?? ($reminder->reminder_scope === 'tasks' ? 'یادآوری انجام وظیفه' : 'یادآوری پیگیری') }}
                                    </div>
                                    @if($reminder->snooze_count > 0)
                                        <a href="{{ route('user.reminders.snooze-history', ['reminder' => $reminder->id]) }}"
                                           class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-100 transition-colors shrink-0"
                                           title="مشاهده تاریخچه تعویق">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $faNum($reminder->snooze_count) }} تعویق</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            {{-- مرجع مرتبط --}}
                            <td class="py-3.5 px-4 align-middle">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $reminder->reminder_scope === 'tasks' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50' }} shrink-0">
                                        {{ $reminder->reminder_scope === 'tasks' ? 'وظیفه' : 'پیگیری' }}
                                    </span>
                                    @if($relatedItem)
                                        @php $rUrl = $reminder->relatedUrl(); @endphp
                                        @if($rUrl)
                                            <a href="{{ $rUrl }}"
                                               class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 line-clamp-1 transition-colors">
                                                {{ $relatedItem->title ?? ('#' . $relatedItem->id) }}
                                            </a>
                                        @else
                                            <span
                                                class="font-bold text-gray-700 dark:text-gray-300 line-clamp-1">{{ $relatedItem->title ?? ('#' . $relatedItem->id) }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- مشتری / کاربر --}}
                            <td class="py-3.5 px-4 align-middle">
                                @if($relatedModel)
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[10px] font-black shrink-0 border border-emerald-100 dark:border-emerald-800/50">
                                            {{ mb_substr($isClient ? $relatedModel->full_name : $relatedModel->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            @if($relatedUrl)
                                                <a href="{{ $relatedUrl }}"
                                                   class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block transition-colors">
                                                    {{ $isClient ? $relatedModel->full_name : $relatedModel->name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-gray-900 dark:text-white truncate block">
                                                    {{ $isClient ? $relatedModel->full_name : $relatedModel->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- اولویت --}}
                            <td class="py-3.5 px-4 text-center align-middle">
                                <x-ui.badge :variant="$prio['variant']" size="xs">
                                    {{ $prio['label'] }}
                                </x-ui.badge>
                            </td>

                            {{-- زمان سررسید --}}
                            <td class="py-3.5 px-4 text-center align-middle">
                                <div
                                    class="inline-flex items-center gap-1.5 text-xs {{ $isOverdue ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-gray-600 dark:text-gray-300' }}">
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $faNum($remindJalali) }}</span>
                                </div>
                            </td>

                            {{-- وضعیت --}}
                            <td class="py-3.5 px-4 text-center align-middle">
                                <x-ui.badge :variant="$stat['variant']" size="xs">
                                    {{ $stat['label'] }}
                                </x-ui.badge>
                            </td>

                            {{-- عملیات تکی --}}
                            <td class="py-3.5 px-4 text-center align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- دکمه تغییر وضعیت به انجام شده با کامپوننت اختصاصی complete-button --}}
                                    @if($reminder->status !== Reminder::STATUS_DONE)
                                        <form method="POST"
                                              action="{{ route('user.reminders.update-status', ['reminder' => $reminder->id]) }}"
                                              class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ Reminder::STATUS_DONE }}">
                                            <x-ui.complete-button
                                                type="submit"
                                                label=""
                                                confirm-label="انجام شد؟"
                                                size="sm"
                                                title="علامت‌گذاری به عنوان انجام شده"
                                            />
                                        </form>
                                    @endif

                                    {{-- دکمه حذف تکی با کامپوننت دیلیت --}}
                                    @can('reminders.delete')
                                        <form method="POST"
                                              action="{{ route('user.reminders.destroy', ['reminder' => $reminder->id]) }}"
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.delete-button
                                                type="submit"
                                                label=""
                                                confirm-label="حذف؟"
                                                size="sm"
                                                title="حذف یادآوری"
                                            />
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @if($allReminders->isNotEmpty())
                            @if($taskCount === 0)
                                <tr x-show="activeTab === 'tasks'" x-cloak>
                                    <td colspan="8" class="p-6">
                                        <x-ui.empty-state
                                            title="هیچ یادآوری وظیفه‌ای یافت نشد"
                                            description="در این بازه زمانی هیچ یادآوری برای وظایف ثبت نشده است."
                                        >
                                            <x-slot:icon>
                                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </x-slot:icon>
                                        </x-ui.empty-state>
                                    </td>
                                </tr>
                            @endif

                            @if($followUpCount === 0)
                                <tr x-show="activeTab === 'followups'" x-cloak>
                                    <td colspan="8" class="p-6">
                                        <x-ui.empty-state
                                            title="هیچ یادآوری پیگیری‌ای یافت نشد"
                                            description="در این بازه زمانی هیچ یادآوری برای پیگیری‌ها ثبت نشده است."
                                        >
                                            <x-slot:icon>
                                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                            </x-slot:icon>
                                        </x-ui.empty-state>
                                    </td>
                                </tr>
                            @endif
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="p-6">
                                <x-ui.empty-state
                                    title="هیچ یادآوری‌ای در این بازه یافت نشد"
                                    description="در حال حاضر هیچ یادآوری وظیفه یا پیگیری با وضعیت و فیلترهای انتخابی شما ثبت نشده است."
                                >
                                    <x-slot:icon>
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </x-slot:icon>
                                    @if(request()->anyFilled(['status', 'period', 'from_jalali', 'to_jalali', 'user_id']))
                                        <x-slot:actions>
                                            <a href="{{ route('user.reminders.index') }}"
                                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                <span>بازنشانی فیلترها</span>
                                            </a>
                                        </x-slot:actions>
                                    @endif
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- نمایش کارتی در موبایل بدون اسکرول افقی --}}
        <div class="md:hidden space-y-3">
            @forelse($allReminders as $reminder)
                @php
                    $task = $reminder->task;
                    $followUp = $reminder->followUp ?? $task;
                    $relatedItem = $reminder->reminder_scope === 'tasks' ? $task : $followUp;
                    $remindJalali = $reminder->remind_at ? Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i') : '—';
                    $isOverdue = $reminder->remind_at && $reminder->remind_at->isPast() && in_array($reminder->status, [Reminder::STATUS_OPEN, Reminder::STATUS_ESCALATED]);

                    $prioVal = $relatedItem->priority ?? Task::PRIORITY_MEDIUM;
                    $prio = $priorityConfig[$prioVal] ?? ['label' => 'معمولی', 'variant' => 'blue'];
                    $stat = $statusBadgeConfig[$reminder->status] ?? ['label' => $reminder->status, 'variant' => 'secondary'];

                    $relatedModel = null;
                    $relatedUrl = null;
                    $isClient = false;
                    if ($relatedItem) {
                        if ($relatedItem->related_type === Task::RELATED_TYPE_CLIENT && $relatedItem->relatedClient) {
                            if (auth()->user()->can('clients.view') && $relatedItem->relatedClient->isVisibleFor(auth()->user())) {
                                $relatedModel = $relatedItem->relatedClient;
                                $relatedUrl = route('user.clients.show', $relatedItem->relatedClient->id);
                                $isClient = true;
                            }
                        } elseif ($relatedItem->related_type === Task::RELATED_TYPE_USER && $relatedItem->relatedUser) {
                            if (auth()->user()->can('users.view')) {
                                $relatedModel = $relatedItem->relatedUser;
                            }
                        }
                    }
                @endphp
                <div id="reminder-card-{{ $reminder->id }}"
                     data-scope="{{ $reminder->reminder_scope }}"
                     x-show="activeTab === 'all' || activeTab === '{{ $reminder->reminder_scope }}'"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 shadow-xs space-y-3">
                    {{-- هدر کارت: چک‌باکس، شناسه و بج‌ها --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   value="{{ $reminder->id }}"
                                   x-model="selectedIds"
                                   class="reminder-cb rounded-md border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400">#{{ $faNum($reminder->id) }}</span>
                            <span
                                class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $reminder->reminder_scope === 'tasks' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                {{ $reminder->reminder_scope === 'tasks' ? 'وظیفه' : 'پیگیری' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-ui.badge :variant="$prio['variant']" size="xs">{{ $prio['label'] }}</x-ui.badge>
                            <x-ui.badge :variant="$stat['variant']" size="xs">{{ $stat['label'] }}</x-ui.badge>
                        </div>
                    </div>

                    {{-- متن پیام یادآوری --}}
                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-relaxed">
                        {{ $reminder->message ?? ($reminder->reminder_scope === 'tasks' ? 'یادآوری انجام وظیفه' : 'یادآوری پیگیری') }}
                    </div>

                    {{-- اطلاعات مرتبط (مرجع، مخاطب و تاریخ سررسید) --}}
                    <div class="bg-gray-50/75 dark:bg-gray-900/40 rounded-xl p-3 space-y-2 text-xs">
                        @if($relatedItem)
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-400">مرجع:</span>
                                @php $rUrl = $reminder->relatedUrl(); @endphp
                                @if($rUrl)
                                    <a href="{{ $rUrl }}"
                                       class="font-bold text-indigo-600 dark:text-indigo-400 truncate hover:underline">
                                        {{ $relatedItem->title ?? ('#' . $relatedItem->id) }}
                                    </a>
                                @else
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $relatedItem->title ?? ('#' . $relatedItem->id) }}</span>
                                @endif
                            </div>
                        @endif

                        @if($relatedModel)
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-400">مخاطب:</span>
                                @if($relatedUrl)
                                    <a href="{{ $relatedUrl }}"
                                       class="font-bold text-gray-800 dark:text-gray-200 hover:text-indigo-600 truncate">
                                        {{ $isClient ? $relatedModel->full_name : $relatedModel->name }}
                                    </a>
                                @else
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $isClient ? $relatedModel->full_name : $relatedModel->name }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-400">زمان سررسید:</span>
                            <span
                                class="font-bold {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $faNum($remindJalali) }}
                            </span>
                        </div>
                    </div>

                    {{-- اکشن‌های فوتر کارت --}}
                    <div
                        class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-700/50">
                        <div class="flex items-center gap-2">
                            @if($reminder->snooze_count > 0)
                                <a href="{{ route('user.reminders.snooze-history', ['reminder' => $reminder->id]) }}"
                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $faNum($reminder->snooze_count) }} بار تعویق</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($reminder->status !== Reminder::STATUS_DONE)
                                <form method="POST"
                                      action="{{ route('user.reminders.update-status', ['reminder' => $reminder->id]) }}"
                                      class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ Reminder::STATUS_DONE }}">
                                    <x-ui.complete-button
                                        type="submit"
                                        label=""
                                        confirm-label="انجام شد؟"
                                        size="sm"
                                        title="علامت‌گذاری به عنوان انجام شده"
                                    />
                                </form>
                            @endif

                            @can('reminders.delete')
                                <form method="POST"
                                      action="{{ route('user.reminders.destroy', ['reminder' => $reminder->id]) }}"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.delete-button
                                        type="submit"
                                        label=""
                                        confirm-label="حذف؟"
                                        size="sm"
                                        title="حذف یادآوری"
                                    />
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
                @if($allReminders->isNotEmpty())
                    @if($taskCount === 0)
                        <div x-show="activeTab === 'tasks'" x-cloak>
                            <x-ui.empty-state
                                title="هیچ یادآوری وظیفه‌ای یافت نشد"
                                description="در این بازه زمانی هیچ یادآوری برای وظایف ثبت نشده است."
                            >
                                <x-slot:icon>
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </x-slot:icon>
                            </x-ui.empty-state>
                        </div>
                    @endif

                    @if($followUpCount === 0)
                        <div x-show="activeTab === 'followups'" x-cloak>
                            <x-ui.empty-state
                                title="هیچ یادآوری پیگیری‌ای یافت نشد"
                                description="در این بازه زمانی هیچ یادآوری برای پیگیری‌ها ثبت نشده است."
                            >
                                <x-slot:icon>
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </x-slot:icon>
                            </x-ui.empty-state>
                        </div>
                    @endif
                @endif
            @empty
                <x-ui.empty-state
                    title="هیچ یادآوری‌ای در این بازه یافت نشد"
                    description="در حال حاضر هیچ یادآوری وظیفه یا پیگیری با وضعیت و فیلترهای انتخابی شما ثبت نشده است."
                >
                    <x-slot:icon>
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </x-slot:icon>
                    @if(request()->anyFilled(['status', 'period', 'from_jalali', 'to_jalali', 'user_id']))
                        <x-slot:actions>
                            <a href="{{ route('user.reminders.index') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>بازنشانی فیلترها</span>
                            </a>
                        </x-slot:actions>
                    @endif
                </x-ui.empty-state>
            @endforelse
        </div>

        {{-- نوار شناور عملیات گروهی (Bulk Actions Floating Bar) کاملاً منطبق بر دیزاین تک‌خطی و بدون بیرون‌زدگی در موبایل --}}
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
                <form method="POST" action="{{ route('user.reminders.bulk-status') }}"
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
                            <span class="hidden sm:inline">یادآوری </span>انتخاب
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

                    {{-- خط جداکننده عمودی در دسکتاپ --}}
                    <div class="hidden sm:block h-6 w-px bg-gray-200/80 dark:bg-gray-700/80 shrink-0"></div>

                    {{-- بخش تغییر وضعیت: دراپ‌داون وضعیت و دکمه ثبت --}}
                    @canany(['reminders.edit', 'reminders.manage'])
                        <div class="flex items-center gap-1.5 flex-1 sm:flex-initial min-w-0"
                             @status-changed.window="bulkStatus = $event.detail">
                            <div class="flex-1 sm:flex-initial sm:w-44 min-w-0">
                                <x-ui.select
                                    name="status"
                                    :options="$bulkStatusOptions"
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
                    @can('reminders.delete')
                        <div @click="bulkAction = 'delete'" class="shrink-0 flex items-center">
                            <x-ui.delete-button
                                type="submit"
                                label=""
                                confirm-label="حذف؟"
                                cancel-label="انصراف"
                                size="sm"
                                title="حذف گروهی یادآوری‌های انتخاب شده"
                            />
                        </div>
                    @endcan
                </form>
            </div>
        </div>

        {{-- فاصله تضمین‌شده انتهای صفحه در موبایل برای جلوگیری از هرگونه تداخل با داک ناوبری --}}
        <div class="h-28 md:hidden" style="height: 7rem;" aria-hidden="true"></div>
    </div>

    {{-- اسکریپت تغییر وضعیت تکی بدون رفرش ناگهانی --}}
    <script>
        function updateSingleStatus(reminderId, url, status) {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PATCH',
                    status: status
                })
            })
                .then(response => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById('reminder-row-' + reminderId);
                        const card = document.getElementById('reminder-card-' + reminderId);
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentStatusFilter = urlParams.get('status') || 'open';

                        if (currentStatusFilter === 'open') {
                            if (row) {
                                row.classList.add('transition-all', 'duration-300', 'opacity-0', '-translate-x-4');
                                setTimeout(() => row.remove(), 300);
                            }
                            if (card) {
                                card.classList.add('transition-all', 'duration-300', 'opacity-0', '-translate-x-4');
                                setTimeout(() => card.remove(), 300);
                            }
                        } else {
                            window.location.reload();
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('خطا در تغییر وضعیت. لطفاً مجدداً تلاش کنید.');
                });
        }
    </script>
@endsection

@includeIf('partials.jalali-date-picker')
