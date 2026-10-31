@extends('layouts.user')

@section('title', 'تاریخچه ورود اکانت ' . $account->username)

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden";
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    تاریخچه ورود اکانت <span
                        class="font-mono text-indigo-600 dark:text-indigo-400 font-black">{{ $account->username }}</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                    دامنه: <span class="font-mono font-bold text-gray-700 dark:text-gray-300"
                                 dir="ltr">{{ $account->domain }}</span> • مشاهده آخرین نشست‌ها و تلاش‌های ورود به هاست
                </p>
            </div>
        </div>

        <a href="{{ route('user.directadmin.accounts.show', $account->username) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>بازگشت به جزئیات اکانت</span>
        </a>
    </div>

    @if(!$connection['success'])
        <div
            class="rounded-2xl bg-amber-50 p-5 border border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/30 text-amber-700 dark:text-amber-400 text-sm flex gap-3 items-center shadow-sm">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>ارتباط با سرور برقرار نشد؛ نمایش اطلاعات لاگ به صورت زنده غیرفعال است. خطا: {{ $connection['error'] }}</span>
        </div>
    @endif

    {{-- Log table --}}
    <div class="{{ $cardClass }}">
        <div
            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
            <h2 class="text-base font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span>آخرین نشست‌ها و تلاش‌های ورود</span>
            </h2>
        </div>

        @if(count($history) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-start">آدرس IP / میزبان</th>
                        <th class="px-6 py-4 text-start">تاریخ و زمان ورود</th>
                        <th class="px-6 py-4 text-center">تعداد تلاش‌ها</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($history as $log)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/60 font-mono font-bold text-xs text-gray-900 dark:text-gray-100"
                                        dir="ltr">
                                        {{ $log['host'] ?? '—' }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-bold">
                                {{ !empty($log['timestamp']) ? $faNum(jdate(\Carbon\Carbon::parse($log['timestamp']))->format('Y/m/d H:i:s')) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold">
                                @if(($log['attempts'] ?? 1) > 3)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-black bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/20 dark:text-rose-400">
                                            {{ $faNum($log['attempts']) }} تلاش
                                        </span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs font-black text-gray-700 dark:text-gray-300">
                                            {{ $faNum($log['attempts'] ?? 1) }}
                                        </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center text-gray-400 dark:text-gray-500">
                <svg class="w-14 h-14 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-base font-black mb-1 text-gray-700 dark:text-gray-300">لاگی ثبت نشده است</h3>
                <p class="text-xs">هیچ تاریخچه ورودی برای این اکانت یافت نشد.</p>
            </div>
        @endif
    </div>
</div>
@endsection
