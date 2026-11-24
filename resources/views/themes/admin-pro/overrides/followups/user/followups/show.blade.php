@extends('layouts.user')

@section('title', 'جزئیات پیگیری #' . ($followUp->id ?? ''))

@section('content')
    @php
        use Modules\Tasks\Entities\Task;
        use Morilog\Jalali\Jalalian;
        use Modules\Reminders\Entities\Reminder;
        use Illuminate\Support\Str;

        $faNum = function ($str) {
            $en = ['0','1','2','3','4','5','6','7','8','9'];
            $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            return str_replace($en, $fa, (string) $str);
        };

        $statuses      = $statuses   ?? Task::statusOptions();
        $priorities    = $priorities ?? Task::priorityOptions();
        $types         = $types      ?? Task::typeOptions();

        $statusLabel   = $statuses[$followUp->status]     ?? $followUp->status;
        $priorityLabel = $priorities[$followUp->priority] ?? $followUp->priority;
        $typeLabel     = $types[$followUp->task_type]     ?? $followUp->task_type;

        $taskReminders = class_exists(Reminder::class)
            ? Reminder::where('related_type', 'TASK')
                ->where('related_id', $followUp->id)
                ->orderBy('remind_at')
                ->get()
            : collect();

        $statusVariant = match($followUp->status) {
            Task::STATUS_DONE => 'emerald',
            Task::STATUS_CANCELED => 'rose',
            Task::STATUS_IN_PROGRESS => 'blue',
            default => 'slate',
        };

        $priorityVariant = match($followUp->priority) {
            Task::PRIORITY_CRITICAL => 'rose',
            Task::PRIORITY_HIGH => 'rose',
            Task::PRIORITY_MEDIUM => 'indigo',
            default => 'slate',
        };

        $client = $followUp->client;
        $assignee = $followUp->assignee;

        $clientPhone = $client->phone ?? '';
        $clientPhoneLabel = 'شماره تماس';
        if ($clientPhone) {
            if (str_starts_with($clientPhone, '09') || str_starts_with($clientPhone, '9') || str_starts_with($clientPhone, '+989')) {
                $clientPhoneLabel = 'شماره همراه';
            } else {
                $clientPhoneLabel = 'تلفن ثابت / دفتر';
            }
        }

        $clientMeta = $client ? (is_array($client->meta) ? $client->meta : (json_decode($client->meta ?? '', true) ?: [])) : [];
        $clientOfficePhone = $clientMeta['office_phone'] ?? ($clientMeta['tell'] ?? ($clientMeta['telephone'] ?? null));
        $clientCompany = $clientMeta['company'] ?? ($clientMeta['company_name'] ?? ($clientMeta['organization'] ?? null));
        $clientStatusName = optional($client?->status)->name;
    @endphp

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8">
        <header class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500" aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('user.followups.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مدیریت پیگیری‌ها</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black truncate max-w-xs sm:max-w-md inline-block">{{ $followUp->title }}</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span class="flex items-center justify-center w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-4.5 h-4.5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                            <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal" title="{{ $followUp->title }}">
                                {{ $followUp->title }}
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0 flex-wrap">
                    @canany(['followups.edit', 'followups.manage'])
                        @if($followUp->status !== Task::STATUS_DONE)
                            <form method="POST" action="{{ route('user.tasks.bulk-update') }}" class="inline-block shrink-0">
                                @csrf
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="status" value="{{ Task::STATUS_DONE }}">
                                <input type="hidden" name="ids[]" value="{{ $followUp->id }}">
                                <x-ui.complete-button
                                    type="submit"
                                    label="تکمیل پیگیری"
                                    confirm-label="انجام شد؟"
                                    size="md"
                                    title="علامت‌گذاری این پیگیری به عنوان انجام شده"
                                />
                            </form>
                        @endif

                        <a href="{{ route('user.followups.edit', $followUp) }}"
                           class="inline-flex items-center justify-center gap-1 sm:gap-2 px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-md shadow-indigo-500/25 transition-all cursor-pointer active:scale-95 shrink-0">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="hidden sm:inline">ویرایش پیگیری</span>
                            <span class="sm:hidden">ویرایش</span>
                        </a>
                    @endcanany

                    @can('followups.delete')
                        <form method="POST" action="{{ route('user.followups.destroy', $followUp) }}" class="inline-block shrink-0">
                            @csrf
                            @method('DELETE')
                            <x-ui.delete-button
                                type="submit"
                                label=""
                                confirm-label="حذف؟"
                                cancel-label="انصراف"
                                size="md"
                                title="حذف پیگیری"
                            />
                        </form>
                    @endcan

                    <a href="{{ route('user.followups.index') }}"
                       class="inline-flex items-center gap-1 sm:gap-2 px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="hidden sm:inline">بازگشت به فهرست</span>
                        <span class="sm:hidden">بازگشت</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-7 space-y-6">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700/40 pb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                            <span class="text-xs font-bold text-gray-400 dark:text-gray-500">نوع اقدام:</span>
                            <x-ui.badge variant="indigo" size="sm">
                                {{ $typeLabel }}
                            </x-ui.badge>
                        </div>
                        <div class="text-xs text-gray-400 dir-ltr">
                            {{ $followUp->created_at ? $faNum(Jalalian::fromCarbon($followUp->created_at)->format('Y/m/d H:i')) : '' }}
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h2 class="text-base sm:text-xl font-black text-gray-900 dark:text-white leading-relaxed">
                            {{ $followUp->title }}
                        </h2>
                        <div class="p-4 sm:p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500">
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                <span>توضیحات و شرح پیگیری:</span>
                            </div>
                            @if(trim((string) $followUp->description))
                                <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed font-normal">{!! nl2br(e(trim($followUp->description))) !!}</div>
                            @else
                                <p class="text-xs text-gray-400 italic">توضیحات بیشتری برای این پیگیری درج نشده است.</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/40">
                        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50/30 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/40 space-y-3.5">
                            <div class="flex items-center justify-between gap-3 border-b border-emerald-100/70 dark:border-emerald-800/30 pb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/20 shrink-0">
                                        {{ mb_substr($client->full_name ?? 'م', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($client)
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
                                            @else
                                                <span class="font-bold text-xs sm:text-sm text-gray-500 dark:text-gray-400">بدون مشتری مرتبط</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-gray-400 font-bold block mt-0.5">مشتری و مخاطب پرونده</span>
                                    </div>
                                </div>

                                @if($client)
                                    <a href="{{ route('user.clients.show', $client) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-all shadow-2xs shrink-0">
                                        <span>مشاهده پرونده</span>
                                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>

                            @if($client)
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
                            @else
                                <p class="text-xs text-gray-400 italic py-2">هیچ پرونده مشتری به این پیگیری متصل نشده است.</p>
                            @endif
                        </div>

                        <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/30 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-800/40 space-y-3.5">
                            <div class="flex items-center justify-between gap-3 border-b border-indigo-100/70 dark:border-indigo-800/30 pb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-indigo-500/20 shrink-0">
                                        {{ mb_substr($assignee->name ?? 'م', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-black text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                                                {{ $assignee->name ?? 'تعیین‌نشده' }}
                                            </span>
                                            @if($assignee?->name)
                                                <x-ui.copy-button :text="$assignee->name" size="sm" iconOnly="true" title="کپی نام مسئول" />
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-gray-400 font-bold block mt-0.5">کاربر مسئول انجام پیگیری</span>
                                    </div>
                                </div>

                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200 shrink-0">
                                    مسئول پیگیری
                                </span>
                            </div>

                            @if($assignee)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-indigo-100 dark:border-indigo-900/40">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="text-gray-400 font-bold">ایمیل:</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr truncate text-right">{{ $assignee->email ?? '—' }}</span>
                                        </div>
                                        @if($assignee->email)
                                            <x-ui.copy-button :text="$assignee->email" size="sm" iconOnly="true" title="کپی ایمیل مسئول" />
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-indigo-100 dark:border-indigo-900/40">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="text-gray-400 font-bold">شماره همراه:</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right">{{ $assignee->mobile ? $faNum($assignee->mobile) : '—' }}</span>
                                        </div>
                                        @if($assignee->mobile)
                                            <x-ui.copy-button :text="$assignee->mobile" size="sm" iconOnly="true" title="کپی شماره همراه مسئول" />
                                        @endif
                                    </div>
                                </div>
                            @else
                                <p class="text-xs text-gray-400 italic py-2">هیچ کاربری به عنوان مسئول این اقدام تعیین نشده است.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-7 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/40 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                            <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                هشدارهای یادآور پیگیری
                            </h3>
                        </div>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-black border border-indigo-100 dark:border-indigo-800/40">
                            {{ $faNum($taskReminders->count()) }} یادآور
                        </span>
                    </div>
                    <div class="space-y-3">
                        @forelse($taskReminders as $reminder)
                            @php
                                $dateJal = $reminder->remind_at
                                    ? $faNum(Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d H:i'))
                                    : '—';
                                $remStatusLabel = Reminder::statusOptions()[$reminder->status] ?? $reminder->status;
                                $isDone = $reminder->status === Reminder::STATUS_DONE;
                            @endphp
                            <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700/40 {{ $isDone ? 'bg-emerald-50/40 dark:bg-emerald-950/15 border-emerald-200/50' : 'bg-gray-50/50 dark:bg-gray-900/30' }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-black text-gray-800 dark:text-gray-200 truncate">
                                            {{ $reminder->message ?? 'یادآوری موعد پیگیری' }}
                                        </span>
                                        @if($isDone)
                                            <span class="inline-flex items-center text-[10px] text-emerald-600 dark:text-emerald-400 font-black">
                                                ✓ انجام شد
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-gray-400 dir-ltr text-right">
                                        <span>{{ $dateJal }}</span>
                                        <span>•</span>
                                        <span>{{ $remStatusLabel }}</span>
                                    </div>
                                </div>

                                @can('reminders.edit')
                                    @if(!$isDone && $reminder->status !== Reminder::STATUS_CANCELED)
                                        <form method="POST" action="{{ route('user.reminders.update-status', $reminder) }}" class="shrink-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ Reminder::STATUS_DONE }}">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/60 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-all shadow-2xs active:scale-95 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>انجام شد</span>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400 text-xs">
                                در حال حاضر هیچ یادآوری فعالی برای این پیگیری تعریف نشده است.
                            </div>
                        @endforelse
                    </div>

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
                                <input type="hidden" name="related_id" value="{{ $followUp->id }}">

                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">تاریخ هشدار</label>
                                    <input type="text"
                                           name="remind_date_jalali"
                                           data-jdp
                                           data-jdp-only-date
                                           value="{{ Jalalian::fromCarbon(now())->format('Y/m/d') }}"
                                           class="w-full rounded-xl border border-gray-200/70 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-3 py-2 text-xs text-center font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 outline-hidden dir-ltr">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">ساعت</label>
                                    <input type="text"
                                           name="remind_time"
                                           data-jdp
                                           data-jdp-only-time
                                           placeholder="10:00"
                                           class="w-full rounded-xl border border-gray-200/70 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-3 py-2 text-xs text-center font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 outline-hidden dir-ltr">
                                </div>

                                <div class="sm:col-span-5">
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">متن یادآوری</label>
                                    <input type="text"
                                           name="message"
                                           value="یادآوری پیگیری: {{ Str::limit($followUp->title, 25) }}"
                                           class="w-full rounded-xl border border-gray-200/70 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 outline-hidden">
                                </div>

                                <div class="sm:col-span-2">
                                    <button type="submit"
                                            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-black shadow-md shadow-indigo-600/25 transition-all cursor-pointer active:scale-95">
                                        ثبت یادآور
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                </div>

            </div>

            <div class="space-y-6">

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 dark:border-gray-700/40 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                            وضعیت سررسید و زمان‌بندی
                        </h3>
                    </div>

                    <div class="space-y-3.5 text-xs">
                        <div class="p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-800/40">
                            <span class="block text-[11px] font-bold text-indigo-700 dark:text-indigo-300 mb-1">موعد انجام (سررسید):</span>
                            <span class="font-black text-sm text-gray-900 dark:text-white dir-ltr text-right block">
                                {{ $followUp->due_at ? $faNum(Jalalian::fromCarbon($followUp->due_at)->format('Y/m/d H:i')) : 'تعیین‌نشده' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-700/40">
                            <span class="text-gray-400 font-bold">تاریخ ایجاد:</span>
                            <span class="text-gray-700 dark:text-gray-300 dir-ltr">
                                {{ $followUp->created_at ? $faNum(Jalalian::fromCarbon($followUp->created_at)->format('Y/m/d H:i')) : '—' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-400 font-bold">تاریخ اتمام:</span>
                            <span class="text-gray-700 dark:text-gray-300 dir-ltr">
                                {{ $followUp->completed_at ? $faNum(Jalalian::fromCarbon($followUp->completed_at)->format('Y/m/d H:i')) : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
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

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-50 dark:border-gray-700/40">
                            <span class="text-gray-400 font-bold">شناسه پیگیری:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">#{{ $faNum($followUp->id) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-50 dark:border-gray-700/40">
                            <span class="text-gray-400 font-bold">ثبت‌کننده:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ optional($followUp->creator)->name ?? 'سیستم' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-gray-400 font-bold">آخرین ویرایش:</span>
                            <span class="text-gray-600 dark:text-gray-300 dir-ltr">{{ $followUp->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <x-ui.toast position="bottom-left" />

    </div>
@endsection

@includeIf('partials.jalali-date-picker')
