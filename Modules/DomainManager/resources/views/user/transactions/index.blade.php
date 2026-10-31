@extends('layouts.user')

@section('title', 'تراکنش‌های کارگزاری دامنه')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
    $dataItems = is_array($transactions) ? ($transactions['data'] ?? $transactions) : [];
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span>تراکنش‌های مالی کارگزاری</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                مشاهده لیست عملیات مالی، شارژها و پرداخت‌های ثبت و تمدید در کارگزار
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.domainmanager.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>پیشخوان کارگزاری</span>
            </a>
        </div>
    </div>

    {{-- Error Banner --}}
    @if($error)
        <div
            class="p-5 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 flex items-center gap-4 shadow-sm">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ $error }}
            </div>
        </div>
    @endif

    {{-- Transactions Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl overflow-hidden">
        @if(!empty($dataItems) && is_array($dataItems))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/50 text-start text-sm">
                    <thead
                        class="bg-gray-50/80 dark:bg-gray-900/50 text-xs font-black text-gray-600 dark:text-gray-400 select-none">
                    <tr>
                        <th class="px-6 py-4 text-start">شناسه پیگیری (Track ID)</th>
                        <th class="px-6 py-4 text-start">نوع عملیات</th>
                        <th class="px-6 py-4 text-start">دامنه مرتبط</th>
                        <th class="px-6 py-4 text-center">وضعیت اجرا</th>
                        <th class="px-6 py-4 text-center">وضعیت مالی</th>
                        <th class="px-6 py-4 text-start">تاریخ و زمان</th>
                        <th class="px-6 py-4 text-start">توضیحات کارگزار</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($dataItems as $tx)
                        @php
                            $trackId = $tx['trackId'] ?? $tx['id'] ?? '—';
                            $type = $tx['type'] ?? $tx['action'] ?? $tx['jobType'] ?? '—';
                            $dom = $tx['domain'] ?? $tx['entity'] ?? '—';
                            $status = strtolower($tx['status'] ?? 'success');
                            $payStatus = strtolower($tx['paymentStatus'] ?? $tx['payment_status'] ?? 'paid');
                            $timeStr = $tx['createdAt'] ?? $tx['date'] ?? null;
                            $msg = $tx['description'] ?? $tx['message'] ?? $tx['error'] ?? '—';
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors duration-200">
                            {{-- Track ID --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700/60 text-gray-800 dark:text-gray-200 font-mono text-xs rounded-lg font-bold select-all"
                                        dir="ltr">
                                        {{ $trackId }}
                                    </span>
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700 dark:text-gray-300">
                                {{ $type }}
                            </td>

                            {{-- Domain --}}
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-900 dark:text-white"
                                dir="ltr">
                                {{ $dom }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(in_array($status, ['success', 'done', 'completed', 'active']))
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            موفق
                                        </span>
                                @elseif(in_array($status, ['failed', 'error', 'rejected']))
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            ناموفق
                                        </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            {{ $status }}
                                        </span>
                                @endif
                            </td>

                            {{-- Payment Status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($payStatus === 'paid' || $payStatus === 'success')
                                    <span
                                        class="text-xs font-bold text-emerald-600 dark:text-emerald-400">تسویه‌شده</span>
                                @else
                                    <span class="text-xs font-bold text-gray-500">{{ $payStatus }}</span>
                                @endif
                            </td>

                            {{-- Time --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $timeStr ? (is_numeric($timeStr) ? $faNum(jdate((int)$timeStr)->format('Y/m/d H:i')) : $timeStr) : '—' }}
                            </td>

                            {{-- Message --}}
                            <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                {{ $msg }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Simple Pagination Nav --}}
            <div
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                <div class="text-xs font-bold text-gray-500 dark:text-gray-400">
                    صفحه فعلی: <span class="text-gray-900 dark:text-white font-mono">{{ $faNum($page) }}</span>
                </div>

                <div class="flex items-center gap-2">
                    @if($page > 1)
                        <a href="{{ route('user.domainmanager.transactions', ['page' => $page - 1]) }}"
                           class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            صفحه قبل
                        </a>
                    @endif
                    <a href="{{ route('user.domainmanager.transactions', ['page' => $page + 1]) }}"
                       class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        صفحه بعد
                    </a>
                </div>
            </div>
        @else
            <div class="p-16 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ تراکنشی ثبت نشده است</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                        تراکنش‌ها و عملیات ارسال‌شده به کارگزار در این صفحه لیست خواهند شد.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
