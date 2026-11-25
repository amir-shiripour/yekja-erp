@extends('layouts.user')

@php
    use Modules\Sms\Entities\SmsMessage;
    use Morilog\Jalali\Jalalian;

    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };

    $totalCount   = $totalCount ?? $messages->total();
    $sentCount    = $sentCount ?? $messages->getCollection()->where('status', SmsMessage::STATUS_SENT)->count();
    $failedCount  = $failedCount ?? $messages->getCollection()->where('status', SmsMessage::STATUS_FAILED)->count();
    $pendingCount = $pendingCount ?? $messages->getCollection()->where('status', SmsMessage::STATUS_PENDING)->count();

    $typeOptionsList = [
        ['value' => '',          'label' => 'همه انواع ارسال'],
        ['value' => 'system',    'label' => 'سیستمی'],
        ['value' => 'scheduled', 'label' => 'زمان‌بندی شده'],
        ['value' => 'manual',    'label' => 'دستی'],
        ['value' => 'otp',       'label' => 'کد تایید (OTP)'],
    ];

    $statusOptionsList = [
        ['value' => '',        'label' => 'همه وضعیت‌ها'],
        ['value' => 'sent',    'label' => 'ارسال موفق'],
        ['value' => 'failed',  'label' => 'ناموفق'],
        ['value' => 'pending', 'label' => 'در صف ارسال'],
    ];

    $periodOptionsList = [
        ['value' => 'all',   'label' => 'بدون فیلتر زمانی'],
        ['value' => 'today', 'label' => 'امروز'],
        ['value' => 'week',  'label' => 'این هفته'],
        ['value' => 'month', 'label' => 'این ماه'],
        ['value' => 'year',  'label' => 'امسال'],
    ];

    $typeBadgeMap = [
        'otp'       => ['label' => 'کد تایید (OTP)', 'variant' => 'sky'],
        'system'    => ['label' => 'سیستمی',          'variant' => 'gray'],
        'manual'    => ['label' => 'دستی',            'variant' => 'indigo'],
        'scheduled' => ['label' => 'زمان‌بندی شده',   'variant' => 'amber'],
    ];
@endphp

@section('title', 'گزارش پیامک‌ها')

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
                <span class="text-indigo-600 dark:text-indigo-400 font-black">گزارش پیامک‌ها</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            گزارش و مدیریت پیامک‌ها
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @can('sms.settings.view')
                        <a href="{{ route('user.sms.settings.index') }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="hidden sm:inline">تنظیمات پیامک</span>
                            <span class="sm:hidden">تنظیمات</span>
                        </a>
                    @endcan

                    @can('sms.messages.send')
                        <a href="{{ route('user.sms.send.create') }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline">ارسال پیامک جدید</span>
                            <span class="sm:hidden">ارسال</span>
                        </a>
                    @endcan
                </div>
            </div>
        </header>

        @if(session('status'))
            <x-ui.alert variant="success" title="پیام سیستم" :dismissible="true">{{ session('status') }}</x-ui.alert>
        @endif
        @if(session('success'))
            <x-ui.alert variant="success" title="عملیات موفقیت‌آمیز"
                        :dismissible="true">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" title="بروز خطا" :dismissible="true">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">کل پیامک‌ها</span>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ $faNum($totalCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">ارسال موفق</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $faNum($sentCount) }}</p>
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
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">ناموفق</span>
                    <p class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-1.5">{{ $faNum($failedCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-5 flex items-center justify-between gap-3 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-400 block truncate">در صف ارسال</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1.5">{{ $faNum($pendingCount) }}</p>
                </div>
                <div
                    class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('user.sms.logs.index') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-5 transition-all relative z-20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                <div class="sm:col-span-1 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">جستجو (موبایل / متن /
                        الگو)</label>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="شماره موبایل یا متن..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden">
                </div>

                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">نوع ارسال</label>
                    <x-ui.select
                        name="type"
                        :options="$typeOptionsList"
                        :value="request('type', '')"
                        placeholder="همه انواع..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">وضعیت</label>
                    <x-ui.select
                        name="status"
                        :options="$statusOptionsList"
                        :value="request('status', '')"
                        placeholder="همه وضعیت‌ها..."
                        :searchable="false"
                        :clearable="false"
                    />
                </div>

                <div class="sm:col-span-1 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">بازه زمانی</label>
                    <x-ui.select
                        name="period"
                        :options="$periodOptionsList"
                        :value="request('period', 'all')"
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

                    @if(request()->anyFilled(['q', 'type', 'status', 'period']) && (request('q') || request('type') || request('status') || request('period', 'all') !== 'all'))
                        <a href="{{ route('user.sms.logs.index') }}"
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
                        <th class="py-3.5 px-5 text-start">موبایل گیرنده</th>
                        <th class="py-3.5 px-4 text-center">نوع</th>
                        <th class="py-3.5 px-4 text-center">وضعیت</th>
                        <th class="py-3.5 px-4 text-center">درایور</th>
                        <th class="py-3.5 px-4 text-start w-1/3">پیام / الگو</th>
                        <th class="py-3.5 px-4 text-center">فرستنده</th>
                        <th class="py-3.5 px-4 text-center">تاریخ</th>
                        <th class="py-3.5 px-4 text-center w-28">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                    @forelse($messages as $msg)
                        @php
                            $typeMeta = $typeBadgeMap[$msg->type] ?? ['label' => $msg->type, 'variant' => 'gray'];
                            $sender = $msg->getSenderDetails();
                            $jalaliDate = $msg->created_at ? Jalalian::fromCarbon($msg->created_at)->format('Y/m/d H:i') : '—';
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3.5 px-5 whitespace-nowrap">
                                <span
                                    class="font-bold text-gray-900 dark:text-white dir-ltr inline-block">{{ $faNum($msg->to) }}</span>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <x-ui.badge :variant="$typeMeta['variant']"
                                            size="sm">{{ $typeMeta['label'] }}</x-ui.badge>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($msg->status === 'sent')
                                    <x-ui.badge variant="emerald" :dot="true" size="sm">ارسال شد</x-ui.badge>
                                @elseif($msg->status === 'failed')
                                    <x-ui.badge variant="danger" :dot="true" size="sm"
                                                title="{{ $msg->error_message ?? '' }}">ناموفق
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="amber" :dot="true" size="sm">در صف ارسال</x-ui.badge>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400 font-bold">
                                {{ $msg->driver ?? '—' }}
                            </td>

                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="space-y-1">
                                    @if($msg->template_key)
                                        <x-ui.badge variant="gray" size="sm">الگو: {{ $msg->template_key }}</x-ui.badge>
                                    @endif
                                    @if($msg->final_message)
                                        <p class="text-[11px] text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed"
                                           title="{{ $msg->final_message }}">
                                            {{ $msg->final_message }}
                                        </p>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">بدون متن</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($sender['type'] === 'workflow')
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] text-gray-400">گردش کار</span>
                                        @can('workflows.view')
                                            @if(Route::has('user.workflows.edit'))
                                                <a href="{{ route('user.workflows.edit', $sender['id']) }}"
                                                   class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">
                                                    {{ $sender['label'] }}
                                                </a>
                                            @else
                                                <span
                                                    class="font-bold text-gray-700 dark:text-gray-300">{{ $sender['label'] }}</span>
                                            @endif
                                        @else
                                            <span
                                                class="font-bold text-gray-700 dark:text-gray-300">{{ $sender['label'] }}</span>
                                        @endcan
                                    </div>
                                @elseif($sender['type'] === 'user')
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] text-gray-400">کاربر</span>
                                        <span
                                            class="font-bold text-gray-700 dark:text-gray-300">{{ $sender['label'] }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ $sender['label'] }}</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div
                                    class="font-bold text-gray-700 dark:text-gray-200">{{ $msg->created_at ? $faNum($msg->created_at->diffForHumans()) : '—' }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5"
                                     dir="ltr">{{ $faNum($jalaliDate) }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($msg->status !== 'sent')
                                    @can('sms.messages.send')
                                        <form action="{{ route('user.sms.logs.resend', $msg->id) }}" method="POST"
                                              class="inline-flex">
                                            @csrf
                                            <button type="submit"
                                                    title="ارسال مجدد"
                                                    aria-label="ارسال مجدد"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:border-indigo-200 dark:hover:border-indigo-700 shadow-2xs transition-all cursor-pointer active:scale-95">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endcan
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6">
                                <x-ui.empty-state
                                    title="هیچ پیامکی یافت نشد"
                                    description="با توجه به فیلترهای اعمال‌شده، موردی برای نمایش وجود ندارد."
                                    :actionLabel="request()->anyFilled(['q', 'type', 'status', 'period']) ? 'پاک‌سازی فیلترها' : null"
                                    :actionUrl="route('user.sms.logs.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @include('themes.admin-pro.partials.pagination-bar', ['paginator' => $messages, 'itemLabel' => 'پیامک ثبت‌شده'])
        </div>

        <div class="md:hidden space-y-3">
            @forelse($messages as $msg)
                @php
                    $typeMeta = $typeBadgeMap[$msg->type] ?? ['label' => $msg->type, 'variant' => 'gray'];
                    $jalaliDate = $msg->created_at ? Jalalian::fromCarbon($msg->created_at)->format('Y/m/d H:i') : '—';
                @endphp
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            class="text-xs font-black text-gray-900 dark:text-white dir-ltr">{{ $faNum($msg->to) }}</span>
                        <div class="flex items-center gap-1.5">
                            <x-ui.badge :variant="$typeMeta['variant']" size="sm">{{ $typeMeta['label'] }}</x-ui.badge>
                            @if($msg->status === 'sent')
                                <x-ui.badge variant="emerald" :dot="true" size="sm">ارسال شد</x-ui.badge>
                            @elseif($msg->status === 'failed')
                                <x-ui.badge variant="danger" :dot="true" size="sm">ناموفق</x-ui.badge>
                            @else
                                <x-ui.badge variant="amber" :dot="true" size="sm">در صف</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    @if($msg->final_message)
                        <p class="text-[11px] text-gray-600 dark:text-gray-300 leading-relaxed">{{ $msg->final_message }}</p>
                    @endif

                    <div
                        class="flex items-center justify-between gap-2 pt-2.5 border-t border-gray-100 dark:border-gray-700/50 text-[10px] text-gray-400">
                        <div class="flex items-center gap-2">
                            <span dir="ltr">{{ $faNum($jalaliDate) }}</span>
                            <span>•</span>
                            <span
                                class="font-bold text-gray-500 dark:text-gray-400">درایور: {{ $msg->driver ?? '—' }}</span>
                        </div>
                        @if($msg->status !== 'sent' && auth()->user()?->can('sms.messages.send'))
                            <form action="{{ route('user.sms.logs.resend', $msg->id) }}" method="POST"
                                  class="inline-flex">
                                @csrf
                                <button type="submit"
                                        title="ارسال مجدد"
                                        aria-label="ارسال مجدد"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 shadow-2xs transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <x-ui.empty-state
                    title="هیچ پیامکی یافت نشد"
                    description="با توجه به فیلترهای اعمال‌شده، موردی برای نمایش وجود ندارد."
                    :actionLabel="request()->anyFilled(['q', 'type', 'status', 'period']) ? 'پاک‌سازی فیلترها' : null"
                    :actionUrl="route('user.sms.logs.index')"
                />
            @endforelse

            @if($messages->total() > 0)
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
                    @include('themes.admin-pro.partials.pagination-bar', ['paginator' => $messages, 'itemLabel' => 'پیامک ثبت‌شده'])
                </div>
            @endif
        </div>

    </div>
@endsection
