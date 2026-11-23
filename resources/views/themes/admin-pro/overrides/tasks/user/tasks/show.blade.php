@extends('layouts.user')

@php
    use Modules\Tasks\Entities\Task;
    use Morilog\Jalali\Jalalian;
    use Modules\Reminders\Entities\Reminder;
    use Illuminate\Support\Str;

    // تبدیل ارقام به فارسی
    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $statuses   = $statuses   ?? Task::statusOptions();
    $priorities = $priorities ?? Task::priorityOptions();
    $types      = $types      ?? Task::typeOptions();

    $statusLabel   = $statuses[$task->status]   ?? $task->status;
    $priorityLabel = $priorities[$task->priority] ?? $task->priority;
    $typeLabel     = $types[$task->task_type]   ?? $task->task_type;

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

    // دریافت یادآوری‌ها
    $taskReminders = class_exists(Reminder::class)
        ? Reminder::where('related_type', 'TASK')
            ->where('related_id', $task->id)
            ->orderBy('remind_at')
            ->get()
        : collect();

    // استخراج اطلاعات مشتری مرتبط
    $client = $relatedClient ?? ($task->relatedClient ?? null);
    $clientPhone = $client?->phone;
    $clientPhoneLabel = 'شماره تماس';
    if ($clientPhone) {
        if (str_starts_with($clientPhone, '09') || str_starts_with($clientPhone, '9') || str_starts_with($clientPhone, '+989')) {
            $clientPhoneLabel = 'شماره همراه';
        } else {
            $clientPhoneLabel = 'تلفن ثابت / دفتر';
        }
    }
    $clientMeta = is_array($client?->meta) ? $client->meta : (json_decode($client?->meta ?? '', true) ?: []);
    $clientOfficePhone = $clientMeta['office_phone'] ?? ($clientMeta['tell'] ?? ($clientMeta['telephone'] ?? null));
    $clientCompany = $clientMeta['company'] ?? ($clientMeta['company_name'] ?? ($clientMeta['organization'] ?? null));
    $clientStatusName = optional($client?->status)->name;

    // استخراج کاربر مرتبط
    $user = $relatedUser ?? ($task->relatedUser ?? null);
    $assignee = $task->assignee;
@endphp

@section('title', 'وظیفه: ' . $task->title)

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8">

        {{-- هدر صفحه و مسیر راهنما (Breadcrumbs) --}}
        <header class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            {{-- بردکرامب ناوبری --}}
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500" aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('user.tasks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مدیریت وظایف</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black truncate max-w-xs sm:max-w-md inline-block">{{ $task->title }}</span>
            </nav>

            {{-- ردیف هدر: آیکون، عنوان و دکمه‌های عملیاتی در یک راستا هم روی موبایل و هم روی دسکتاپ --}}
            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span class="flex items-center justify-center w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-4.5 h-4.5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                            <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal" title="{{ $task->title }}">
                                {{ $task->title }}
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @canany(['tasks.edit', 'tasks.manage'])
                        <a href="{{ route('user.tasks.edit', $task) }}"
                           class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span class="hidden sm:inline">ویرایش وظیفه</span>
                            <span class="sm:hidden">ویرایش</span>
                        </a>
                    @endcanany

                    <a href="{{ route('user.tasks.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/80 text-xs sm:text-sm font-bold shadow-2xs transition-colors shrink-0">
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="hidden sm:inline">بازگشت به لیست</span>
                        <span class="sm:hidden">بازگشت</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- پیام‌های سیستم --}}
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

        {{-- چیدمان دو ستونه دسکتاپ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            {{-- ستون اصلی (۲ سوم صفحه): اطلاعات پایه، موجودیت مرتبط، یادآوری‌ها --}}
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">

                {{-- کارت جزئیات و شرح وظیفه --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-7 space-y-5">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-700/40">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                        <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">مشخصات و شرح وظیفه</h2>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-base sm:text-xl font-black text-gray-900 dark:text-white leading-relaxed">
                            {{ $task->title }}
                        </h3>

                        {{-- باکس شرح و توضیحات --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500">
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                <span>توضیحات و شرح وظیفه:</span>
                            </div>
                            @if(trim((string) $task->description))
                                <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed font-normal">{!! nl2br(e(trim($task->description))) !!}</div>
                            @else
                                <p class="text-xs text-gray-400 italic">توضیحات بیشتری برای این وظیفه درج نشده است.</p>
                            @endif
                        </div>
                    </div>

                    {{-- کارت‌های مرتبط: مشتری پرونده یا کاربر همکار --}}
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/40">
                        {{-- مشتری مرتبط --}}
                        @if($client)
                            <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50/30 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 space-y-3.5">
                                <div class="flex items-center justify-between gap-3 border-b border-emerald-100/70 dark:border-emerald-800/30 pb-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/20 shrink-0">
                                            {{ mb_substr($client->full_name ?? 'م', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <a href="{{ route('user.clients.show', $client) }}"
                                                   class="font-black text-xs sm:text-sm text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 truncate transition-colors">
                                                    {{ $client->full_name }}
                                                </a>
                                                <x-ui.copy-button :text="$client->full_name" size="sm" iconOnly="true" title="کپی نام مشتری" />
                                                @if($clientStatusName)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                                        {{ $clientStatusName }}
                                                    </span>
                                                @endif
                                                @if($client->case_number)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                                        پرونده: {{ $faNum($client->case_number) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-gray-400 font-bold block mt-0.5">مشتری و مخاطب مرتبط</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('user.clients.show', $client) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-all shadow-2xs shrink-0">
                                        <span>مشاهده پرونده</span>
                                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    @if($clientPhone)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400 font-bold">{{ $clientPhoneLabel }}:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right">{{ $faNum($clientPhone) }}</span>
                                            </div>
                                            <x-ui.copy-button :text="$clientPhone" size="sm" iconOnly="true" title="کپی {{ $clientPhoneLabel }}" />
                                        </div>
                                    @endif

                                    @if($clientOfficePhone)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400 font-bold">تلفن دفتر:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right">{{ $faNum($clientOfficePhone) }}</span>
                                            </div>
                                            <x-ui.copy-button :text="$clientOfficePhone" size="sm" iconOnly="true" title="کپی تلفن دفتر" />
                                        </div>
                                    @endif

                                    @if($client->email)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400 font-bold">ایمیل:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr truncate text-right">{{ $client->email }}</span>
                                            </div>
                                            <x-ui.copy-button :text="$client->email" size="sm" iconOnly="true" title="کپی ایمیل مشتری" />
                                        </div>
                                    @endif

                                    @if($client->national_code)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400 font-bold">کد ملی:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $faNum($client->national_code) }}</span>
                                            </div>
                                            <x-ui.copy-button :text="$client->national_code" size="sm" iconOnly="true" title="کپی کد ملی" />
                                        </div>
                                    @endif

                                    @if($clientCompany)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400 font-bold">شرکت/سازمان:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $clientCompany }}</span>
                                            </div>
                                            <x-ui.copy-button :text="$clientCompany" size="sm" iconOnly="true" title="کپی نام شرکت" />
                                        </div>
                                    @endif

                                    @if($client->case_number)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-emerald-100 dark:border-emerald-900/40">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400 font-bold">شماره پرونده:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $faNum($client->case_number) }}</span>
                                            </div>
                                            <x-ui.copy-button :text="$client->case_number" size="sm" iconOnly="true" title="کپی شماره پرونده" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($user)
                            <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/30 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-800/40 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-sm shrink-0">
                                        {{ mb_substr($user->name ?? 'ک', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-sm text-gray-900 dark:text-white">{{ $user->name }}</span>
                                            <x-ui.copy-button :text="$user->name" size="sm" iconOnly="true" title="کپی نام کاربر" />
                                        </div>
                                        <span class="text-[11px] text-gray-400 font-bold">کاربر همکار مرتبط</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    @if($user->email)
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-blue-100 dark:border-blue-900/40">
                                            <span class="text-gray-400 font-bold">ایمیل: {{ $user->email }}</span>
                                            <x-ui.copy-button :text="$user->email" size="sm" iconOnly="true" title="کپی ایمیل" />
                                        </div>
                                    @endif
                                    @if($user->mobile)
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-blue-100 dark:border-blue-900/40">
                                            <span class="text-gray-400 font-bold">موبایل: {{ $faNum($user->mobile) }}</span>
                                            <x-ui.copy-button :text="$user->mobile" size="sm" iconOnly="true" title="کپی موبایل" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40 text-xs text-gray-400 italic">
                                این وظیفه به مشتری یا کاربری متصل نیست و به صورت مستقل تعریف شده است.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- بخش یادآوری‌ها --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-7 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/40 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                            <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                هشدارهای یادآوری وظیفه
                            </h3>
                        </div>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-black border border-indigo-100 dark:border-indigo-800/40">
                            {{ $faNum($taskReminders->count()) }} یادآور
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($taskReminders as $reminder)
                            @php
                                $remStatusVariant = match($reminder->status) {
                                    Reminder::STATUS_DONE => 'success',
                                    Reminder::STATUS_CANCELED => 'danger',
                                    default => 'neutral'
                                };
                            @endphp
                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-gray-100 dark:border-gray-700/40 bg-gray-50/50 dark:bg-gray-900/30">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-900 dark:text-white">
                                            {{ $reminder->remind_at ? $faNum(Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i')) : '—' }}
                                        </div>
                                        <span class="text-[10px] text-gray-400 font-medium">کانال: درون‌برنامه‌ای</span>
                                    </div>
                                </div>

                                <x-ui.badge :variant="$remStatusVariant" size="sm" dot>
                                    {{ Reminder::statusOptions()[$reminder->status] ?? $reminder->status }}
                                </x-ui.badge>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-gray-400 italic">
                                هنوز یادآوری برای این وظیفه ثبت نشده است.
                            </div>
                        @endforelse
                    </div>

                    {{-- فرم افزودن یادآوری سریع --}}
                    @can('reminders.create')
                        <div class="pt-5 border-t border-gray-100 dark:border-gray-700/40 space-y-3">
                            <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ثبت یادآور جدید</span>
                            </h4>

                            <form method="POST" action="{{ route('user.reminders.store') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                @csrf
                                <input type="hidden" name="related_type" value="TASK">
                                <input type="hidden" name="related_id" value="{{ $task->id }}">

                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1">کانال یادآوری</label>
                                    <x-ui.select
                                        name="channel"
                                        :options="[
                                            'IN_APP' => 'اعلان درون‌برنامه‌ای',
                                            'SMS'    => 'پیامک (SMS)',
                                        ]"
                                        value="IN_APP"
                                        :searchable="false"
                                        :clearable="false"
                                    />
                                </div>

                                <div class="sm:col-span-5">
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1">تاریخ و ساعت یادآوری</label>
                                    <input type="text"
                                           name="remind_at_view"
                                           data-jdp
                                           data-jdp-min-date="today"
                                           placeholder="انتخاب زمان..."
                                           required
                                           class="w-full text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 py-2 px-3 text-gray-800 dark:text-gray-200 text-center">
                                </div>

                                <div class="sm:col-span-3">
                                    <button type="submit"
                                            class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-xs transition-colors">
                                        افزودن یادآور
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>

            {{-- ستون فرعی (۱ سوم صفحه): کاربر مسئول، وضعیت و متادیتا --}}
            <div class="space-y-6 sm:space-y-8">

                {{-- کارت کاربر مسئول --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">کاربر مسئول انجام</h3>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-black text-base shadow-md shadow-purple-500/20 shrink-0">
                            {{ mb_substr($task->assignee_name ?? 'م', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-black text-gray-900 dark:text-white block truncate">
                                {{ $task->assignee_name }}
                            </span>
                            <span class="text-xs font-bold text-gray-400 block mt-0.5">مسئول وظیفه</span>
                        </div>
                    </div>

                    @if($assignee)
                        <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700/40 text-xs">
                            @if($assignee->email)
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-gray-400 font-bold">ایمیل:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr truncate text-right">{{ $assignee->email }}</span>
                                    </div>
                                    <x-ui.copy-button :text="$assignee->email" size="sm" iconOnly="true" title="کپی ایمیل" />
                                </div>
                            @endif
                            @if($assignee->mobile)
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-gray-400 font-bold">موبایل:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right">{{ $faNum($assignee->mobile) }}</span>
                                    </div>
                                    <x-ui.copy-button :text="$assignee->mobile" size="sm" iconOnly="true" title="کپی موبایل" />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- کارت وضعیت و زمان‌بندی --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">وضعیت و اولویت</h3>
                    </div>

                    <div class="space-y-3 text-xs font-bold">
                        <div class="flex items-center justify-between py-1">
                            <span class="text-gray-400">وضعیت فعلی:</span>
                            <x-ui.badge :variant="$statusVariant" size="sm" dot>
                                {{ $statusLabel }}
                            </x-ui.badge>
                        </div>

                        <div class="flex items-center justify-between py-1 border-t border-gray-50 dark:border-gray-700/30">
                            <span class="text-gray-400">سطح اولویت:</span>
                            <x-ui.badge :variant="$priorityVariant" size="sm">
                                {{ $priorityLabel }}
                            </x-ui.badge>
                        </div>

                        <div class="flex items-center justify-between py-1 border-t border-gray-50 dark:border-gray-700/30">
                            <span class="text-gray-400">نوع وظیفه:</span>
                            <span class="px-2 py-0.5 rounded-lg text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $typeLabel }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-1 border-t border-gray-50 dark:border-gray-700/30">
                            <span class="text-gray-400">موعد سررسید:</span>
                            @if($task->due_at)
                                <span class="{{ $isOverdue ? 'text-rose-600 font-black' : 'text-gray-800 dark:text-gray-200' }}">
                                    {{ $faNum(Jalalian::fromCarbon($task->due_at)->format('Y/m/d H:i')) }}
                                    @if($isOverdue)
                                        <span class="text-[10px] block text-rose-500 font-bold text-left">(منقضی شده)</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- کارت متادیتای سیستم --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-6 space-y-3.5">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 dark:border-gray-700/40 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-700/70 text-gray-600 dark:text-gray-300 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                            شناسه و متادیتای سامانه
                        </h3>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-gray-900/40">
                            <span class="text-gray-400 font-bold">شناسه یکتا:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">#{{ $faNum($task->id) }}</span>
                        </div>

                        <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-gray-900/40">
                            <span class="text-gray-400 font-bold">ثبت‌کننده:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $task->creator_name ?? 'سیستم' }}</span>
                        </div>

                        <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-gray-900/40">
                            <span class="text-gray-400 font-bold">زمان ثبت:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $task->created_at ? $faNum(Jalalian::fromCarbon($task->created_at)->format('Y/m/d H:i')) : '—' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-gray-900/40">
                            <span class="text-gray-400 font-bold">آخرین ویرایش:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $task->updated_at ? $faNum(Jalalian::fromCarbon($task->updated_at)->format('Y/m/d H:i')) : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@includeIf('partials.jalali-date-picker')
