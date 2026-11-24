@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;
    use Modules\Reminders\Entities\Reminder;
    use Modules\Tasks\Entities\Task;

    $task = $reminder->task;
    $followUp = $reminder->followUp ?? $task;
    $relatedItem = $reminder->reminder_scope === 'tasks' ? $task : $followUp;

    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $formatDuration = function ($minutes) use ($faNum) {
        if (!$minutes) return '—';
        if ($minutes < 60) {
            return $faNum($minutes) . ' دقیقه';
        }
        $hours = floor($minutes / 60);
        $remMinutes = $minutes % 60;
        if ($remMinutes == 0) {
            return $faNum($hours) . ' ساعت';
        }
        return $faNum($hours) . ' ساعت و ' . $faNum($remMinutes) . ' دقیقه';
    };

    $statusVariant = match($reminder->status) {
        Reminder::STATUS_DONE => 'emerald',
        Reminder::STATUS_ESCALATED => 'rose',
        Reminder::STATUS_CANCELED => 'zinc',
        default => 'amber',
    };

    $statusLabel = match($reminder->status) {
        Reminder::STATUS_DONE => 'انجام شده',
        Reminder::STATUS_ESCALATED => 'ارجاع شده',
        Reminder::STATUS_CANCELED => 'لغو شده',
        default => 'در انتظار',
    };
@endphp

@section('title', 'تاریخچه تعویق یادآوری #' . $reminder->id)

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8">

        {{-- هدر صفحه با بردکرامب و دیزاین کارت منسجم --}}
        <header class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            {{-- بردکرامب ناوبری --}}
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500" aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('user.reminders.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مدیریت یادآوری‌ها</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">تاریخچه تعویق #{{ $faNum($reminder->id) }}</span>
            </nav>

            {{-- ردیف هدر: آیکون، عنوان و دکمه بازگشت --}}
            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                            <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                                تاریخچه تعویق یادآوری #{{ $faNum($reminder->id) }}
                            </h1>
                        </div>
                    </div>
                </div>

                {{-- دکمه بازگشت به لیست یادآوری‌ها --}}
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('user.reminders.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="hidden sm:inline">بازگشت به یادآوری‌ها</span>
                        <span class="sm:hidden">بازگشت</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- کارت خلاصه اطلاعات یادآوری --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/40">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                <h2 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">مشخصات یادآوری مرتبط</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- متن پیام --}}
                <div class="sm:col-span-2 p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40 space-y-1">
                    <span class="block text-[11px] text-gray-400 font-bold">متن یادآوری</span>
                    <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white leading-relaxed">
                        {{ $reminder->message ?: 'یادآوری بدون عنوان' }}
                    </p>
                </div>

                {{-- وضعیت و اولویت --}}
                <div class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40 space-y-1">
                    <span class="block text-[11px] text-gray-400 font-bold">وضعیت فعلی</span>
                    <div class="flex items-center gap-2 pt-0.5">
                        <x-ui.badge :variant="$statusVariant" size="sm" dot>
                            {{ $statusLabel }}
                        </x-ui.badge>
                        <span class="text-xs font-bold text-gray-500">
                            ({{ $reminder->reminder_scope === 'tasks' ? 'وظیفه' : 'پیگیری' }})
                        </span>
                    </div>
                </div>

                {{-- موعد سررسید فعلی --}}
                <div class="p-3.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/40 space-y-1">
                    <span class="block text-[11px] text-gray-400 font-bold">سررسید جاری</span>
                    <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white dir-ltr block text-right">
                        {{ $reminder->remind_at ? $faNum(Jalalian::fromCarbon($reminder->remind_at)->format('Y/m/d - H:i')) : '—' }}
                    </span>
                </div>
            </div>

            @if($relatedItem)
                <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/70 dark:border-indigo-900/40 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-gray-400 font-bold">موجودیت مرتبط:</span>
                        @php $rUrl = $reminder->relatedUrl(); @endphp
                        @if($rUrl)
                            <a href="{{ $rUrl }}" class="font-black text-indigo-700 dark:text-indigo-300 hover:underline truncate">
                                {{ $relatedItem->title ?? ('#' . $relatedItem->id) }}
                            </a>
                        @else
                            <span class="font-black text-gray-800 dark:text-gray-200 truncate">
                                {{ $relatedItem->title ?? ('#' . $relatedItem->id) }}
                            </span>
                        @endif
                    </div>
                    @if($reminder->snooze_limit > 0)
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 shrink-0">
                            سقف مجاز تعویق: {{ $faNum($reminder->snooze_limit) }} بار
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- لاگ‌های تاریخچه تعویق --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <h2 class="text-sm font-black text-gray-900 dark:text-white">رویدادهای ثبت‌شده تعویق</h2>
                </div>
                <span class="text-xs font-bold text-gray-400">
                    مجموع: {{ $faNum($logs->count()) }} مورد
                </span>
            </div>

            @if($logs->isEmpty())
                <div class="p-6">
                    <x-ui.empty-state
                        title="هیچ تعویقی ثبت نشده است"
                        description="این یادآوری تاکنون به تعویق نیفتاده و در موعد اولیه خود قرار دارد."
                    >
                        <x-slot:icon>
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </x-slot:icon>
                    </x-ui.empty-state>
                </div>
            @else
                {{-- جدول دسکتاپ --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 text-[11px] font-bold text-gray-500 dark:text-gray-400">
                                <th class="py-3 px-4">نوبت</th>
                                <th class="py-3 px-4">کاربر اقدام‌کننده</th>
                                <th class="py-3 px-4">موعد اولیه</th>
                                <th class="py-3 px-4">موکول‌شده به</th>
                                <th class="py-3 px-4">مدت تعویق</th>
                                <th class="py-3 px-4">دلیل تعویق</th>
                                <th class="py-3 px-4">زمان ثبت تعویق</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                            @foreach($logs as $log)
                                <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-colors">
                                    {{-- نوبت --}}
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg text-xs font-black bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50">
                                            #{{ $faNum($log->snooze_sequence) }}
                                        </span>
                                    </td>

                                    {{-- کاربر --}}
                                    <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] font-black shrink-0">
                                                {{ mb_substr(optional($log->user)->name ?? 'ک', 0, 1) }}
                                            </div>
                                            <span>{{ optional($log->user)->name ?? 'سیستم / ناشناس' }}</span>
                                        </div>
                                    </td>

                                    {{-- موعد اولیه --}}
                                    <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 dir-ltr text-right">
                                        {{ $log->original_remind_at ? $faNum(Jalalian::fromCarbon($log->original_remind_at)->format('Y/m/d H:i')) : '—' }}
                                    </td>

                                    {{-- موکول شده به --}}
                                    <td class="py-3.5 px-4 font-bold text-indigo-600 dark:text-indigo-400 dir-ltr text-right">
                                        {{ $log->snoozed_to ? $faNum(Jalalian::fromCarbon($log->snoozed_to)->format('Y/m/d H:i')) : '—' }}
                                    </td>

                                    {{-- مدت تعویق --}}
                                    <td class="py-3.5 px-4 font-bold text-gray-700 dark:text-gray-300">
                                        {{ $formatDuration($log->duration_minutes) }}
                                    </td>

                                    {{-- دلیل تعویق --}}
                                    <td class="py-3.5 px-4 max-w-xs">
                                        @if($log->reason)
                                            <span class="inline-block p-1.5 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                                {{ $log->reason }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-[11px]">—</span>
                                        @endif
                                    </td>

                                    {{-- تاریخ ثبت --}}
                                    <td class="py-3.5 px-4 text-gray-400 text-[11px] dir-ltr text-right">
                                        {{ $log->created_at ? $faNum(Jalalian::fromCarbon($log->created_at)->format('Y/m/d H:i:s')) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- کارت‌های موبایل --}}
                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700/50 p-4 space-y-4">
                    @foreach($logs as $log)
                        <div class="pt-4 first:pt-0 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg text-xs font-black bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50">
                                        تعویق #{{ $faNum($log->snooze_sequence) }}
                                    </span>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        {{ optional($log->user)->name ?? 'سیستم / ناشناس' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-gray-400 dir-ltr">
                                    {{ $log->created_at ? $faNum(Jalalian::fromCarbon($log->created_at)->format('Y/m/d H:i')) : '—' }}
                                </span>
                            </div>

                            <div class="bg-gray-50/70 dark:bg-gray-900/30 rounded-xl p-3 space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400">مدت زمان تعویق:</span>
                                    <span class="font-bold text-amber-700 dark:text-amber-400">
                                        {{ $formatDuration($log->duration_minutes) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400">موعد جدید سررسید:</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr text-right">
                                        {{ $log->snoozed_to ? $faNum(Jalalian::fromCarbon($log->snoozed_to)->format('Y/m/d H:i')) : '—' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400">موعد اولیه:</span>
                                    <span class="text-gray-500 dark:text-gray-400 dir-ltr text-right">
                                        {{ $log->original_remind_at ? $faNum(Jalalian::fromCarbon($log->original_remind_at)->format('Y/m/d H:i')) : '—' }}
                                    </span>
                                </div>

                                @if($log->reason)
                                    <div class="pt-2 border-t border-gray-200/60 dark:border-gray-800 space-y-1">
                                        <span class="text-gray-400 text-[10.5px] block font-bold">دلیل تعویق:</span>
                                        <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                            {{ $log->reason }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- فاصله تضمین‌شده انتهای صفحه در موبایل برای جلوگیری از هرگونه تداخل با داک ناوبری --}}
        <div class="h-28 md:hidden" style="height: 7rem;" aria-hidden="true"></div>
    </div>
@endsection
