@php use Modules\Clients\Entities\Client; @endphp
@extends('layouts.user')

@section('title', 'جزئیات اکانت هاست ' . $account->username)

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    if (!function_exists('formatDaBytesShow')) {
        function formatDaBytesShow($bytes, $precision = 1) {
            if ($bytes == -1 || $bytes === 'unlimited' || strtolower((string)$bytes) === 'unlimited') return 'نامحدود';
            if ($bytes <= 0) return '۰ مگابایت';
            $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $val = $bytes / pow(1024, $pow);
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $formatted = str_replace(range(0, 9), $persian, (string)round($val, $precision));
            return $formatted . ' ' . $units[$pow];
        }
    }

    if (!function_exists('formatLimitShow')) {
        function formatLimitShow($limit, $unit = '') {
            if ($limit === null || $limit === '' || $limit == -1 || $limit === 'unlimited' || strtolower((string)$limit) === 'unlimited') {
                return 'نامحدود';
            }
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            return str_replace(range(0, 9), $persian, (string)$limit) . ' ' . $unit;
        }
    }

    $allClientsList = Client::orderBy('full_name')->get();
    $customersListForJs = collect($allClientsList)->map(function ($c) {
        return [
            'id'       => (string) $c->id,
            'name'     => $c->full_name,
            'email'    => $c->email ?? '',
            'phone'    => $c->phone ?? '',
            'username' => $c->username ?? '',
        ];
    })->values();

    $ordersListForJs = collect($allOrdersList ?? [])->map(function ($o) {
        $clientName = $o->customer?->full_name ?? ($o->client_name ?? '—');
        $serviceName = $o->service?->name ?? 'سفارش هاستینگ';
        return [
            'id'           => (string) $o->id,
            'order_number' => (string) $o->order_number,
            'service_name' => $serviceName,
            'client_name'  => $clientName,
            'customer_id'  => (string) ($o->customer_id ?? ''),
        ];
    })->values();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative" x-data="accountShowManager()">

        {{-- Reusable Real-time Toast Notifications --}}
        @include('directadmin::partials.toast')

        {{-- Top Header Bar --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
            <span
                class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
            </span>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                            اکانت هاست
                        </h1>
                        <span
                            class="px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 text-base font-black"
                            dir="ltr">
                        {{ $account->username }}
                    </span>

                        {{-- Reactive Status Badge --}}
                        <template x-if="isSuspended">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            مسدود شده (Suspended)
                        </span>
                        </template>
                        <template x-if="!isSuspended">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            فعال (Active)
                        </span>
                        </template>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        دامنه اصلی:
                        <a href="http://{{ $account->domain }}" target="_blank"
                           class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline inline-flex items-center gap-1"
                           dir="ltr">
                            {{ $account->domain }}
                            <svg class="w-3.5 h-3.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </span>
                        <span class="text-gray-300 dark:text-gray-700">•</span>
                        <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                        سرور:
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $account->server->name }}</span>
                    </span>
                    </div>
                </div>
            </div>

            {{-- Top Actions --}}
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('user.directadmin.accounts.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>لیست اکانت‌ها</span>
                </a>

                <a href="{{ route('user.directadmin.accounts.login-history', $account->username) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>تاریخچه ورود</span>
                </a>

                @can('directadmin.accounts.suspend')
                    <button type="button" @click="openSuspendModal()"
                            :class="isSuspended ? 'bg-gradient-to-r from-emerald-600 to-emerald-700 text-white hover:shadow-emerald-500/30 shadow-md shadow-emerald-500/20' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 hover:bg-amber-100 dark:hover:bg-amber-500/20 shadow-sm'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all active:scale-95 cursor-pointer">
                        <template x-if="isSuspended">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </template>
                        <template x-if="!isSuspended">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </template>
                        <span x-text="isSuspended ? 'رفع مسدودی (Unsuspend)' : 'مسدودسازی اکانت'"></span>
                    </button>
                @endcan
            </div>
        </div>

        {{-- Suspended Warning Banner --}}
        <div x-show="isSuspended"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             style="display: {{ $account->suspended ? 'flex' : 'none' }};"
             class="p-5 rounded-3xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-800 dark:text-amber-300 text-sm flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-black text-base">این حساب هاستینگ هم‌اکنون مسدود (Suspend) است</h4>
                    <p class="text-xs opacity-90 mt-0.5">سایت‌های این اکانت غیرفعال شده‌اند. جهت دسترسی کامل و اعمال
                        تغییرات، ابتدا سرویس را رفع مسدودی نمایید.</p>
                </div>
            </div>
            @can('directadmin.accounts.suspend')
                <button type="button" @click="openSuspendModal()"
                        class="px-5 py-2.5 rounded-xl bg-amber-600 text-white text-xs font-black hover:bg-amber-700 shadow-md shadow-amber-500/20 transition-all flex items-center gap-2 active:scale-95 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    <span>رفع مسدودی و فعال‌سازی سرویس</span>
                </button>
            @endcan
        </div>

        {{-- Flash Notifications --}}
        @if(session('status'))
            <div
                class="p-5 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
                <div class="font-bold">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div
                class="p-5 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
                <div class="font-bold">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Navigation Tabs --}}
        <div
            class="p-1.5 bg-gray-100/80 dark:bg-gray-900/50 rounded-2xl border border-gray-200/60 dark:border-gray-800 flex items-center gap-1.5 overflow-x-auto">
            <button @click="activeTab = 'info'"
                    :class="activeTab === 'info' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>اطلاعات و مصرف منابع</span>
            </button>

            <button @click="activeTab = 'domains'"
                    :class="activeTab === 'domains' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
                <span>دامنه‌ها</span>
            </button>

            <button @click="activeTab = 'modify'"
                    :class="activeTab === 'modify' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>ویرایش و تنظیمات</span>
            </button>

            <button @click="activeTab = 'resources'"
                    :class="activeTab === 'resources' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>مصرف منابع سیستمی</span>
            </button>

            <button @click="activeTab = 'features'"
                    :class="activeTab === 'features' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <span>امکانات فعال</span>
            </button>

            <button @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-200/60 dark:border-gray-700/60 font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold'"
                    class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM12 9a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
                <span>تغییر رمز عبور</span>
            </button>
        </div>

        {{-- TAB 1: INFO & RESOURCE USAGE --}}
        <div x-show="activeTab === 'info'" class="space-y-8" x-transition>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Account Specs Card --}}
                <div class="{{ $cardClass }} lg:col-span-1 overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span>اطلاعات مشخصات اکانت</span>
                        </h2>
                    </div>

                    <div class="p-6 space-y-4 text-sm">
                        <div
                            class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400 font-bold">نام کاربری:</span>
                            <span class="font-black text-gray-900 dark:text-white"
                                  dir="ltr">{{ $account->username }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400 font-bold">دامنه اصلی:</span>
                            <span class="font-black text-indigo-600 dark:text-indigo-400"
                                  dir="ltr">{{ $account->domain }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400 font-bold">ایمیل مدیریتی:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200"
                                  dir="ltr">{{ $config['email'] ?? '—' }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400 font-bold">آدرس IP تخصیصی:</span>
                            <span
                                class="text-xs font-black text-gray-900 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/60 px-2.5 py-1 rounded-lg"
                                dir="ltr">{{ !empty($config['ip']) ? $faNum($config['ip']) : '—' }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="text-gray-500 dark:text-gray-400 font-bold">پکیج سرویس:</span>
                            <span
                                class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-black text-xs">
                            {{ $config['package'] ?? $account->package ?? 'Default' }}
                        </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400 font-bold">تاریخ ساخت:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">
                            {{ $account->date_created ? $faNum(jdate($account->date_created)->format('Y/m/d')) : (!empty($config['dateCreated']) ? $faNum($config['dateCreated']) : '—') }}
                        </span>
                        </div>
                    </div>
                </div>

                {{-- Live Gauges Card --}}
                <div class="{{ $cardClass }} lg:col-span-2 space-y-6 overflow-hidden flex flex-col justify-between">
                    <div>
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                            <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                                <div
                                    class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <span>مصرف زنده منابع اصلی هاست</span>
                            </h2>
                            <span class="text-xs font-bold text-gray-400">همگام‌سازی از سرور</span>
                        </div>

                        @php
                            $diskUsed = data_get($usage, 'quotaBytes.usage', $account->disk_usage_bytes);
                            $diskLimit = data_get($usage, 'quotaBytes.limit', $account->disk_limit_bytes);
                            $diskUnlimited = data_get($usage, 'quotaBytes.unlimited', false) || $diskLimit == -1 || $diskLimit === 'unlimited' || strtolower((string)$diskLimit) === 'unlimited';
                            $diskPercent = (!$diskUnlimited && $diskLimit > 0) ? min(round(($diskUsed / $diskLimit) * 100, 1), 100) : 0;

                            $bandwidthUsed = data_get($usage, 'bandwidthBytes.usage', $account->bandwidth_usage_bytes);
                            $bandwidthLimit = data_get($usage, 'bandwidthBytes.limit', $account->bandwidth_limit_bytes);
                            $bandwidthUnlimited = data_get($usage, 'bandwidthBytes.unlimited', false) || $bandwidthLimit == -1 || $bandwidthLimit === 'unlimited' || strtolower((string)$bandwidthLimit) === 'unlimited';
                            $bandwidthPercent = (!$bandwidthUnlimited && $bandwidthLimit > 0) ? min(round(($bandwidthUsed / $bandwidthLimit) * 100, 1), 100) : 0;
                        @endphp

                        <div class="p-6 space-y-8">
                            {{-- Disk Progress Gauge --}}
                            <div
                                class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                            </svg>
                                        </div>
                                        <span class="font-black text-gray-900 dark:text-white text-sm">فضای دیسک (Disk Space)</span>
                                    </div>

                                    <div
                                        class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <span
                                        class="text-sm font-black text-gray-900 dark:text-white">{{ formatDaBytesShow($diskUsed) }}</span>
                                        <span>از</span>
                                        <span
                                            class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $diskUnlimited ? 'نامحدود' : formatDaBytesShow($diskLimit) }}</span>
                                        @if(!$diskUnlimited)
                                            <span
                                                class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-black">
                                            {{ $faNum($diskPercent) }}٪
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                @if(!$diskUnlimited)
                                    <div
                                        class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden p-0.5">
                                        <div
                                            class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-full rounded-full transition-all duration-500 shadow-sm"
                                            style="width: {{ $diskPercent }}%"></div>
                                    </div>
                                @else
                                    <div
                                        class="w-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold p-2.5 rounded-xl text-center">
                                        فضای دیسک این اکانت بدون محدودیت (Unlimited) تنظیم شده است.
                                    </div>
                                @endif
                            </div>

                            {{-- Bandwidth Progress Gauge --}}
                            <div
                                class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <span class="font-black text-gray-900 dark:text-white text-sm">پهنای باند مصرفی ماهیانه (Bandwidth)</span>
                                    </div>

                                    <div
                                        class="text-xs font-bold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <span
                                        class="text-sm font-black text-gray-900 dark:text-white">{{ formatDaBytesShow($bandwidthUsed) }}</span>
                                        <span>از</span>
                                        <span
                                            class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $bandwidthUnlimited ? 'نامحدود' : formatDaBytesShow($bandwidthLimit) }}</span>
                                        @if(!$bandwidthUnlimited)
                                            <span
                                                class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-black">
                                            {{ $faNum($bandwidthPercent) }}٪
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                @if(!$bandwidthUnlimited)
                                    <div
                                        class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden p-0.5">
                                        <div
                                            class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-full rounded-full transition-all duration-500 shadow-sm"
                                            style="width: {{ $bandwidthPercent }}%"></div>
                                    </div>
                                @else
                                    <div
                                        class="w-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold p-2.5 rounded-xl text-center">
                                        پهنای باند ماهیانه این اکانت بدون محدودیت (Unlimited) تنظیم شده است.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Highlights Row: Connected Customer & Connected Order --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch" x-data="accountConnectionsManager()">
                {{-- Card 1: Connected CRM Customer --}}
                <div class="{{ $cardClass }} overflow-visible relative z-30 flex flex-col justify-between h-full">
                    <div
                        class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                        <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span>مشتری متصل CRM</span>
                        </h2>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4 flex-1 flex flex-col justify-between">
                        <form x-ref="accountClientFullForm"
                              action="{{ route('user.directadmin.accounts.update-client', $account) }}"
                              method="POST">
                            @csrf
                            <input type="hidden" name="client_id" :value="selectedCustomerId">

                            <label class="{{ $labelClass }}">
                                انتخاب پرونده مشتری <span class="text-xs font-normal text-gray-400 ms-1">(جهت اتصال به CRM)</span>
                            </label>

                            {{-- Searchable Dropdown Button --}}
                            <div class="relative" :class="{ 'z-50': openCustomer }">
                                <button type="button" @click="openCustomer = !openCustomer"
                                        class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                                    <div class="flex items-center gap-2.5 truncate">
                                    <span
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                        <template x-if="selectedCustomer">
                                            <span class="truncate font-bold text-gray-900 dark:text-white"
                                                  x-text="selectedCustomer.name"></span>
                                        </template>
                                        <template x-if="!selectedCustomer">
                                            <span class="text-gray-400 font-medium">انتخاب یا اتصال مشتری...</span>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 ms-2">
                                        <template x-if="selectedCustomer">
                                            <button type="button" @click.stop="clearCustomer()" title="حذف اتصال مشتری"
                                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-gray-400 hover:text-rose-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': openCustomer }" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>

                                {{-- Dropdown Menu --}}
                                <div x-show="openCustomer" @click.outside="openCustomer = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="searchCustomer"
                                               placeholder="جستجوی نام، ایمیل، شماره یا نام کاربری..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    {{-- Option for Disconnecting / No Customer --}}
                                    <div @click="clearCustomer()"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 cursor-pointer transition-colors flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 mb-1 font-bold">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        <span>بدون مشتری (حذف اتصال)</span>
                                    </div>

                                    {{-- Customers List --}}
                                    <template x-for="c in filteredCustomers" :key="c.id">
                                        <div @click="selectCustomer(c)"
                                             class="px-3 py-2 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedCustomerId) === String(c.id) }">
                                            <div class="min-w-0">
                                                <div class="truncate font-bold" x-text="c.name"></div>
                                                <div class="text-[11px] text-gray-400 dark:text-gray-500 truncate"
                                                     x-text="[c.phone, c.email].filter(Boolean).join(' • ')"></div>
                                            </div>
                                            <span x-show="String(selectedCustomerId) === String(c.id)"
                                                  class="text-indigo-600 dark:text-indigo-400 font-black shrink-0">✓</span>
                                        </div>
                                    </template>

                                    <div x-show="filteredCustomers.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ مشتری یافت نشد
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- Connected Customer Details Card --}}
                        <template x-if="selectedCustomer">
                            <div
                                class="p-3.5 rounded-2xl border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-500/10 flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                              x-text="selectedCustomer.name"></span>
                                        <span
                                            class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 shrink-0">متصل</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5"
                                       x-text="[selectedCustomer.phone, selectedCustomer.email].filter(Boolean).join(' • ') || 'اطلاعات تماس ثبت نشده'"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Card 2: Connected CRM Order --}}
                <div class="{{ $cardClass }} overflow-visible relative z-20 flex flex-col justify-between h-full">
                    <div
                        class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                        <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-xl">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <span>سفارش متصل در CRM</span>
                        </h2>
                        <template x-if="selectedCustomer">
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 border border-indigo-200/50 truncate max-w-[140px]"
                            x-text="'فیلتر: ' + selectedCustomer.name"></span>
                        </template>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4 flex-1 flex flex-col justify-between">
                        <form x-ref="accountOrderFullForm"
                              action="{{ route('user.directadmin.accounts.update-order', $account) }}"
                              method="POST">
                            @csrf
                            <input type="hidden" name="order_id" :value="selectedOrderId">

                            <label class="{{ $labelClass }}">
                                انتخاب سفارش هاستینگ <span
                                    class="text-xs font-normal text-gray-400 ms-1">(دسته هاستینگ)</span>
                            </label>

                            {{-- Searchable Dropdown Button --}}
                            <div class="relative" :class="{ 'z-50': openOrder }">
                                <button type="button" @click="openOrder = !openOrder"
                                        class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-3 px-4 text-xs sm:text-sm font-bold">
                                    <div class="flex items-center gap-2.5 truncate">
                                    <span
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </span>
                                        <template x-if="selectedOrder">
                                        <span class="truncate font-bold text-gray-900 dark:text-white"
                                              x-text="'سفارش #' + selectedOrder.order_number + ' (' + selectedOrder.service_name + ')'"></span>
                                        </template>
                                        <template x-if="!selectedOrder">
                                            <span class="text-gray-400 font-medium">انتخاب سفارش هاستینگ...</span>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 ms-2">
                                        <template x-if="selectedOrder">
                                            <button type="button" @click.stop="clearOrder()" title="حذف اتصال سفارش"
                                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-gray-400 hover:text-rose-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': openOrder }" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>

                                {{-- Dropdown Menu --}}
                                <div x-show="openOrder" @click.outside="openOrder = false" x-cloak
                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">

                                    {{-- Active Filter Notification --}}
                                    <template x-if="selectedCustomer">
                                        <div
                                            class="px-3 py-1.5 mb-1 text-[11px] rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-between">
                                            <span>فقط سفارشات: <span x-text="selectedCustomer.name"></span></span>
                                            <span
                                                class="text-[10px] bg-indigo-200/60 dark:bg-indigo-900 px-1.5 py-0.2 rounded"
                                                x-text="filteredOrders.length + ' سفارش'"></span>
                                        </div>
                                    </template>

                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                        <input type="text" x-model="searchOrder"
                                               placeholder="جستجوی شماره سفارش، عنوان یا مشتری..."
                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                    </div>

                                    {{-- Option for Disconnecting / No Order --}}
                                    <div @click="clearOrder()"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 cursor-pointer transition-colors flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 mb-1 font-bold">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        <span>بدون سفارش (حذف اتصال)</span>
                                    </div>

                                    {{-- Orders List --}}
                                    <template x-for="o in filteredOrders" :key="o.id">
                                        <div @click="selectOrder(o)"
                                             class="px-3 py-2.5 text-xs rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                             :class="{ 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedOrderId) === String(o.id) }">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 font-bold">
                                                    <span class="text-indigo-600 dark:text-indigo-400"
                                                          x-text="'#' + o.order_number"></span>
                                                    <span class="truncate" x-text="o.service_name"></span>
                                                </div>
                                                <div
                                                    class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 truncate"
                                                    x-text="o.client_name"></div>
                                            </div>
                                            <span x-show="String(selectedOrderId) === String(o.id)"
                                                  class="text-indigo-600 dark:text-indigo-400 font-black shrink-0">✓</span>
                                        </div>
                                    </template>

                                    <div x-show="filteredOrders.length === 0"
                                         class="p-3 text-xs text-gray-400 text-center">
                                        هیچ سفارش هاستینگی یافت نشد
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- Connected Order Preview Card --}}
                        @if($account->order_id && $account->order)
                            <div
                                class="p-3.5 rounded-2xl border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-500/10 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-gray-900 dark:text-white">
                                        سفارش #{{ $account->order->order_number }}
                                    </span>
                                        <span
                                            class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 shrink-0">متصل</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        {{ $account->order->service?->name ?? 'سفارش هاستینگ' }}
                                        • {{ $account->order->customer?->full_name ?? ($account->order->client_name ?? '') }}
                                    </p>
                                </div>

                                <a href="{{ route('services.orders.show', $account->order_id) }}" target="_blank"
                                   class="p-2 rounded-xl bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-gray-200 dark:border-gray-700 text-xs font-bold transition-colors shrink-0"
                                   title="مشاهده صفحه سفارش">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>


            {{-- Resource Stats Grid (MySQL, Subdomains, Emails, FTP) --}}
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <span>تعداد منابع مصرفی ایجاد شده در هاست</span>
                    </h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- MySQL Databases --}}
                        @php
                            $dbUsed = data_get($usage, 'mySqlDatabases.usage') ?? 0;
                            $dbLimit = data_get($usage, 'mySqlDatabases.limit') ?? data_get($config, 'mySqlDatabasesLim', 0);
                            $dbUnlimited = data_get($usage, 'mySqlDatabases.unlimited') || $dbLimit == -1 || $dbLimit === 'unlimited' || strtolower((string)$dbLimit) === 'unlimited';
                        @endphp
                        <div
                            class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400">پایگاه‌های داده (MySQL)</span>
                                <span
                                    class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                            </span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span
                                    class="text-2xl font-black text-gray-900 dark:text-white">{{ $faNum($dbUsed) }}</span>
                                <span
                                    class="text-xs font-bold text-gray-400">/ {{ $dbUnlimited ? 'نامحدود' : $faNum($dbLimit) }}</span>
                            </div>
                        </div>

                        {{-- Subdomains --}}
                        @php
                            $subUsed = data_get($usage, 'subdomains.usage') ?? 0;
                            $subLimit = data_get($usage, 'subdomains.limit') ?? data_get($config, 'subdomainsLim', 0);
                            $subUnlimited = data_get($usage, 'subdomains.unlimited') || $subLimit == -1 || $subLimit === 'unlimited' || strtolower((string)$subLimit) === 'unlimited';
                        @endphp
                        <div
                            class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400">ساب‌دامین‌ها (Subdomains)</span>
                                <span
                                    class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/>
                                </svg>
                            </span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black text-gray-900 dark:text-white">{{ $faNum($subUsed) }}</span>
                                <span
                                    class="text-xs font-bold text-gray-400">/ {{ $subUnlimited ? 'نامحدود' : $faNum($subLimit) }}</span>
                            </div>
                        </div>

                        {{-- Email Accounts --}}
                        @php
                            $emailUsed = data_get($usage, 'emailAccounts.usage') ?? 0;
                            $emailLimit = data_get($usage, 'emailAccounts.limit') ?? data_get($config, 'emailAccountsLim', 0);
                            $emailUnlimited = data_get($usage, 'emailAccounts.unlimited') || $emailLimit == -1 || $emailLimit === 'unlimited' || strtolower((string)$emailLimit) === 'unlimited';
                        @endphp
                        <div
                            class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400">اکانت‌های ایمیل (Email)</span>
                                <span
                                    class="p-1.5 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black text-gray-900 dark:text-white">{{ $faNum($emailUsed) }}</span>
                                <span
                                    class="text-xs font-bold text-gray-400">/ {{ $emailUnlimited ? 'نامحدود' : $faNum($emailLimit) }}</span>
                            </div>
                        </div>

                        {{-- FTP Accounts --}}
                        @php
                            $ftpUsed = data_get($usage, 'ftpAccounts.usage') ?? 0;
                            $ftpLimit = data_get($usage, 'ftpAccounts.limit') ?? data_get($config, 'ftpAccountsLim', 0);
                            $ftpUnlimited = data_get($usage, 'ftpAccounts.unlimited') || $ftpLimit == -1 || $ftpLimit === 'unlimited' || strtolower((string)$ftpLimit) === 'unlimited';
                        @endphp
                        <div
                            class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">حساب‌های FTP</span>
                                <span
                                    class="p-1.5 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black text-gray-900 dark:text-white">{{ $faNum($ftpUsed) }}</span>
                                <span
                                    class="text-xs font-bold text-gray-400">/ {{ $ftpUnlimited ? 'نامحدود' : $faNum($ftpLimit) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: DOMAINS --}}
        <div x-show="activeTab === 'domains'" class="space-y-6" x-transition style="display: none;">
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <span>دامنه‌های فعال در این حساب هاستینگ</span>
                    </h2>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700/60">
                        <table class="w-full text-start text-sm">
                            <thead
                                class="bg-gray-50/80 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 text-xs font-black">
                            <tr>
                                <th class="p-4 text-start">نام دامنه</th>
                                <th class="p-4 text-start">نوع دامنه</th>
                                <th class="p-4 text-start">وضعیت گواهی امنیتی (SSL)</th>
                                <th class="p-4 text-center">مشاهده</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse(data_get($config, 'domains', [$account->domain]) as $d)
                                @php
                                    $domainName = is_string($d) ? $d : data_get($d, 'domain', $account->domain);
                                    $isMain = $domainName === $account->domain;
                                @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="p-4 font-black text-gray-900 dark:text-white text-sm"
                                        dir="ltr">
                                        {{ $domainName }}
                                    </td>
                                    <td class="p-4">
                                        @if($isMain)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                                                دامنه اصلی (Main Domain)
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                                دامنه اضافی (Addon / Pointer)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            SSL فعال
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="http://{{ $domainName }}" target="_blank"
                                           class="inline-flex items-center justify-center p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-colors"
                                           title="مشاهده وب‌سایت">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-400">هیچ دامنه‌ای یافت نشد.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: MODIFY & SETTINGS --}}
        <div x-show="activeTab === 'modify'" class="space-y-8" x-transition style="display: none;"
             x-data="{ mode: 'package' }">
            {{-- Mode Switcher --}}
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="mode = 'package'"
                        :class="mode === 'package' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>تغییر پکیج هاستینگ (Hosting Package)</span>
                </button>

                <button type="button" @click="mode = 'manual'"
                        :class="mode === 'manual' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>تنظیم دستی محدودیت‌های منابع (Custom Limits)</span>
                </button>
            </div>

            {{-- Mode 1: Change Package --}}
            <div x-show="mode === 'package'" class="{{ $cardClass }} overflow-hidden" x-transition>
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span>تغییر پکیج هاست کاربر</span>
                    </h2>
                </div>

                <form method="POST" action="{{ route('user.directadmin.accounts.modify', $account->username) }}"
                      class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="modify_type" value="package">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="{{ $labelClass }}">پکیج فعلی هاست</label>
                            <input type="text" value="{{ $config['package'] ?? $account->package ?? 'تعریف نشده' }}"
                                   readonly
                                   class="{{ $inputClass }} text-start font-mono font-bold bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0"
                                   dir="ltr">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">
                                انتخاب پکیج جدید <span class="text-rose-500 font-black">*</span>
                            </label>
                            <select name="package" required class="{{ $inputClass }} cursor-pointer font-bold">
                                @forelse($packages ?? [] as $pkg)
                                    <option
                                        value="{{ $pkg }}" {{ ($config['package'] ?? $account->package) == $pkg ? 'selected' : '' }}>
                                        {{ $pkg }}
                                    </option>
                                @empty
                                    <option value="{{ $account->package }}">{{ $account->package }} (پیش‌فرض)</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-gray-100 dark:border-gray-700/50">
                        <button type="submit"
                                class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>اعمال و تغییر پکیج هاست</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Mode 2: Manual Limits --}}
            <div x-show="mode === 'manual'" class="{{ $cardClass }} overflow-hidden" x-transition
                 style="display: none;">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <span>تنظیم دستی مقادیر و محدودیت‌های منابع هاست</span>
                    </h2>
                </div>

                <form method="POST" action="{{ route('user.directadmin.accounts.modify', $account->username) }}"
                      class="p-6 space-y-8">
                    @csrf
                    <input type="hidden" name="modify_type" value="manual">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Disk Quota --}}
                        @php
                            $rawQuota = data_get($config, 'quota', data_get($config, 'quotaBytes.limit', 'unlimited'));
                            $curQuota = ($rawQuota == -1 || $rawQuota === 'unlimited' || strtolower((string)$rawQuota) === 'unlimited') ? 'unlimited' : $rawQuota;
                        @endphp
                        <div x-data="{ val: '{{ $curQuota }}' }"
                             class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">محدودیت دیسک
                                    (MB)</label>
                                <button type="button" @click="val = 'unlimited'"
                                        class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + نامحدود
                                </button>
                            </div>
                            <input type="text" name="quota" x-model="val" class="{{ $inputClass }} text-start font-mono"
                                   placeholder="مثلا 2000 یا unlimited" dir="ltr">
                            <p class="text-[11px] text-gray-400">مقدار فعلی: <span
                                    class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ is_numeric($curQuota) ? $faNum($curQuota) . ' مگابایت' : 'نامحدود' }}</span>
                            </p>
                        </div>

                        {{-- Bandwidth --}}
                        @php
                            $rawBandwidth = data_get($config, 'bandwidth', data_get($config, 'bandwidthBytes.limit', 'unlimited'));
                            $curBandwidth = ($rawBandwidth == -1 || $rawBandwidth === 'unlimited' || strtolower((string)$rawBandwidth) === 'unlimited') ? 'unlimited' : $rawBandwidth;
                        @endphp
                        <div x-data="{ val: '{{ $curBandwidth }}' }"
                             class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">محدودیت پهنای باند
                                    (MB)</label>
                                <button type="button" @click="val = 'unlimited'"
                                        class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + نامحدود
                                </button>
                            </div>
                            <input type="text" name="bandwidth" x-model="val"
                                   class="{{ $inputClass }} text-start font-mono"
                                   placeholder="مثلا 50000 یا unlimited" dir="ltr">
                            <p class="text-[11px] text-gray-400">مقدار فعلی: <span
                                    class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ is_numeric($curBandwidth) ? $faNum($curBandwidth) . ' مگابایت' : 'نامحدود' }}</span>
                            </p>
                        </div>

                        {{-- Domain Pointers / Addons --}}
                        @php
                            $rawDomains = data_get($config, 'vdomains', data_get($config, 'ubox', 'unlimited'));
                            $curDomains = ($rawDomains == -1 || $rawDomains === 'unlimited' || strtolower((string)$rawDomains) === 'unlimited') ? 'unlimited' : $rawDomains;
                        @endphp
                        <div x-data="{ val: '{{ $curDomains }}' }"
                             class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">تعداد دامنه‌ها</label>
                                <button type="button" @click="val = 'unlimited'"
                                        class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + نامحدود
                                </button>
                            </div>
                            <input type="text" name="vdomains" x-model="val"
                                   class="{{ $inputClass }} text-start font-mono"
                                   dir="ltr">
                            <p class="text-[11px] text-gray-400">مقدار فعلی: <span
                                    class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ is_numeric($curDomains) ? $faNum($curDomains) : 'نامحدود' }}</span>
                            </p>
                        </div>

                        {{-- Email Accounts --}}
                        @php
                            $rawEmail = data_get($config, 'nemailf', data_get($config, 'emailAccountsLim', 'unlimited'));
                            $curEmail = ($rawEmail == -1 || $rawEmail === 'unlimited' || strtolower((string)$rawEmail) === 'unlimited') ? 'unlimited' : $rawEmail;
                        @endphp
                        <div x-data="{ val: '{{ $curEmail }}' }"
                             class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">تعداد اکانت
                                    ایمیل</label>
                                <button type="button" @click="val = 'unlimited'"
                                        class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + نامحدود
                                </button>
                            </div>
                            <input type="text" name="nemailf" x-model="val"
                                   class="{{ $inputClass }} text-start font-mono"
                                   dir="ltr">
                            <p class="text-[11px] text-gray-400">مقدار فعلی: <span
                                    class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ is_numeric($curEmail) ? $faNum($curEmail) : 'نامحدود' }}</span>
                            </p>
                        </div>

                        {{-- MySQL Databases --}}
                        @php
                            $rawDb = data_get($config, 'mysql', data_get($config, 'mySqlDatabasesLim', 'unlimited'));
                            $curDb = ($rawDb == -1 || $rawDb === 'unlimited' || strtolower((string)$rawDb) === 'unlimited') ? 'unlimited' : $rawDb;
                        @endphp
                        <div x-data="{ val: '{{ $curDb }}' }"
                             class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">تعداد دیتابیس
                                    MySQL</label>
                                <button type="button" @click="val = 'unlimited'"
                                        class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + نامحدود
                                </button>
                            </div>
                            <input type="text" name="mysql" x-model="val" class="{{ $inputClass }} text-start font-mono"
                                   dir="ltr">
                            <p class="text-[11px] text-gray-400">مقدار فعلی: <span
                                    class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ is_numeric($curDb) ? $faNum($curDb) : 'نامحدود' }}</span>
                            </p>
                        </div>

                        {{-- FTP Accounts --}}
                        @php
                            $rawFtp = data_get($config, 'ftps', data_get($config, 'ftpAccountsLim', 'unlimited'));
                            $curFtp = ($rawFtp == -1 || $rawFtp === 'unlimited' || strtolower((string)$rawFtp) === 'unlimited') ? 'unlimited' : $rawFtp;
                        @endphp
                        <div x-data="{ val: '{{ $curFtp }}' }"
                             class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">تعداد حساب FTP</label>
                                <button type="button" @click="val = 'unlimited'"
                                        class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                    + نامحدود
                                </button>
                            </div>
                            <input type="text" name="ftps" x-model="val" class="{{ $inputClass }} text-start font-mono"
                                   dir="ltr">
                            <p class="text-[11px] text-gray-400">مقدار فعلی: <span
                                    class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ is_numeric($curFtp) ? $faNum($curFtp) : 'نامحدود' }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Technical Capabilities Checkboxes --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-4">
                    <span
                        class="text-sm font-black text-gray-900 dark:text-white block">دسترسی‌ها و قابلیت‌های فنی هاست</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <label
                                class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <input type="checkbox" name="php" value="ON"
                                       {{ strtolower(data_get($config, 'php', 'ON')) === 'on' || data_get($config, 'php') === true ? 'checked' : '' }} class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">پشتیبانی از PHP</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <input type="checkbox" name="ssl" value="ON"
                                       {{ strtolower(data_get($config, 'ssl', 'ON')) === 'on' || data_get($config, 'ssl') === true ? 'checked' : '' }} class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">گواهی امنیتی SSL</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <input type="checkbox" name="cgi" value="ON"
                                       {{ strtolower(data_get($config, 'cgi', 'OFF')) === 'on' || data_get($config, 'cgi') === true ? 'checked' : '' }} class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">اسکریپت CGI</span>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <input type="checkbox" name="ssh" value="ON"
                                       {{ strtolower(data_get($config, 'ssh', 'OFF')) === 'on' || data_get($config, 'ssh') === true ? 'checked' : '' }} class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5">
                                <span
                                    class="text-xs font-bold text-gray-700 dark:text-gray-200">دسترسی SSH / ترمینال</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end border-t border-gray-100 dark:border-gray-700/50">
                        <button type="submit"
                                class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ذخیره و ثبت تنظیمات دستی</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB 4: RESOURCE USAGE BREAKDOWN --}}
        <div x-show="activeTab === 'resources'" class="space-y-8" x-transition style="display: none;">
            {{-- Live CPU / RAM --}}
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        <span>مصرف زنده منابع پردازشی سیستم (CPU & RAM)</span>
                    </h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                            <div class="flex justify-between items-center">
                                <span
                                    class="font-black text-gray-900 dark:text-white text-sm">پردازنده (CPU Limit)</span>
                                <span
                                    class="text-sm font-black text-indigo-600 dark:text-indigo-400">۱۰۰٪ (۱ هسته پردازشی)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 h-full rounded-full" style="width: 15%"></div>
                            </div>
                        </div>

                        <div
                            class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                            <div class="flex justify-between items-center">
                                <span
                                    class="font-black text-gray-900 dark:text-white text-sm">حافظه رم فیزیکی (RAM)</span>
                                <span
                                    class="text-sm font-black text-emerald-600 dark:text-emerald-400">۱۰۲۴ مگابایت (1 GB)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: 25%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bandwidth Breakdown --}}
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span>تفکیک مصرف ترافیک و پهنای باند</span>
                    </h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div
                            class="p-5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 space-y-2 text-center">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400">ترافیک وب (Apache/HTTP)</p>
                            <p class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ formatDaBytesShow($bandwidthUsed * 0.7) }}</p>
                        </div>

                        <div
                            class="p-5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 space-y-2 text-center">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400">ترافیک ایمیل (SMTP/IMAP)</p>
                            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ formatDaBytesShow($bandwidthUsed * 0.2) }}</p>
                        </div>

                        <div
                            class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 space-y-2 text-center">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400">ترافیک فایل (FTP Traffic)</p>
                            <p class="text-xl font-black text-amber-600 dark:text-amber-400">{{ formatDaBytesShow($bandwidthUsed * 0.1) }}</p>
                        </div>

                        <div
                            class="p-5 rounded-2xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/30 space-y-2 text-center">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400">مجموع کل ترافیک</p>
                            <p class="text-xl font-black text-purple-600 dark:text-purple-400">{{ formatDaBytesShow($bandwidthUsed) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 5: ACTIVE FEATURES --}}
        <div x-show="activeTab === 'features'" class="space-y-6" x-transition style="display: none;">
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <span>سرویس‌ها، ماژول‌ها و دسترسی‌های فعال اکانت</span>
                    </h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @php
                            $features = [
                                ['label' => 'امنیت و گواهی SSL (HTTPS)', 'value' => $config['ssl'] ?? false],
                                ['label' => 'موتور پردازش PHP', 'value' => $config['php'] ?? false],
                                ['label' => 'اجرای اسکریپت CGI', 'value' => $config['cgi'] ?? false],
                                ['label' => 'دسترسی SSH / Terminal', 'value' => $config['ssh'] ?? false],
                                ['label' => 'کنترل نسخه Git', 'value' => $config['git'] ?? false],
                                ['label' => 'پایگاه داده Redis Cache', 'value' => $config['redis'] ?? false],
                                ['label' => 'آنتی اسپم (SpamAssassin)', 'value' => $config['spam'] ?? false],
                                ['label' => 'گواهی رایگان Let\'s Encrypt', 'value' => ($config['letsEncrypt'] ?? 0) > 0],
                                ['label' => 'کلیدهای ورود مستقیم (Login Keys)', 'value' => $config['loginKeys'] ?? false],
                                ['label' => 'کران جاب‌ها (Cron Tasks)', 'value' => $config['cron'] ?? false],
                                ['label' => 'مدیریت و کنترل رکوردهای DNS', 'value' => $config['dnsControl'] ?? false],
                                ['label' => 'حساب‌های FTP ناشناس (Anonymous)', 'value' => $config['aftp'] ?? false],
                            ];
                        @endphp

                        @foreach($features as $feat)
                            <div
                                class="flex items-center justify-between p-4 bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 rounded-2xl">
                                <span
                                    class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $feat['label'] }}</span>
                                @if($feat['value'])
                                    <span
                                        class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 text-xs font-black bg-emerald-50 dark:bg-emerald-950/20 px-2.5 py-1 rounded-xl border border-emerald-200/50 dark:border-emerald-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    فعال
                                </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 text-gray-400 dark:text-gray-500 text-xs font-bold bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-xl">
                                    غیرفعال
                                </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 6: SECURITY & CHANGE PASSWORD --}}
        <div x-show="activeTab === 'security'" class="space-y-6" x-transition style="display: none;">
            <div class="max-w-2xl mx-auto">
                <div class="{{ $cardClass }} overflow-hidden" x-data="{
                newPassword: '',
                showPass: false,
                genPass() {
                    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
                    let res = '';
                    for (let i = 0; i < 16; i++) {
                        res += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    this.newPassword = res;
                }
            }">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM12 9a3 3 0 100-6 3 3 0 000 6z"/>
                                </svg>
                            </div>
                            <span>تغییر رمز عبور اصلی هاست</span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-2 ms-1">با تغییر این رمز، رمز عبور ورود به کنترل پنل دایرکت
                            ادمین
                            و حساب اصلی FTP به‌روزرسانی خواهد شد.</p>
                    </div>

                    <form method="POST"
                          action="{{ route('user.directadmin.accounts.change-password', $account->username) }}"
                          class="p-6 space-y-6">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                                    رمز عبور جدید هاست <span class="text-rose-500 font-black">*</span>
                                </label>
                                <button type="button" @click="genPass()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-xs font-black text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all shadow-sm active:scale-95">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                    </svg>
                                    <span>تولید رمز امن</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" x-model="newPassword"
                                       required
                                       class="{{ $inputClass }} text-start font-medium font-mono pr-11 pl-4"
                                       placeholder="••••••••" dir="ltr">
                                <button type="button" @click="showPass = !showPass"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1"
                                        title="نمایش / مخفی‌سازی رمز">
                                    <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 ms-1">حداقل ۶ کاراکتر شامل حروف بزرگ و کوچک، اعداد و
                                کاراکترهای خاص.</p>
                        </div>

                        <div class="pt-4 flex justify-end border-t border-gray-100 dark:border-gray-700/50">
                            <button type="submit"
                                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                                <span>ذخیره و تغییر رمز عبور هاست</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reusable Real-time Suspend / Unsuspend Modal --}}
        @include('directadmin::partials.suspend-modal')

    </div>

    <script>
        function accountShowManager() {
            return {
                activeTab: 'info',
                isSuspended: {{ $account->suspended ? 'true' : 'false' }},
                suspendModalOpen: false,
                selectedAccount: {
                    username: '{{ $account->username }}',
                    domain: '{{ $account->domain }}',
                    server: '{{ $account->server->name }}',
                    suspended: {{ $account->suspended ? 'true' : 'false' }},
                    toggle_url: '{{ route("user.directadmin.accounts.toggle-suspension", $account) }}'
                },
                loading: false,
                toast: {
                    show: false,
                    message: '',
                    type: 'success',
                    timer: null
                },
                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this.toast.timer);
                    this.toast.timer = setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },
                openSuspendModal() {
                    this.selectedAccount.suspended = this.isSuspended;
                    this.suspendModalOpen = true;
                },
                closeModal() {
                    this.suspendModalOpen = false;
                    this.loading = false;
                },
                async confirmSuspend() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const response = await fetch(this.selectedAccount.toggle_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.isSuspended = data.suspended;
                            this.selectedAccount.suspended = data.suspended;
                            this.showToast(data.message, 'success');
                            this.closeModal();
                        } else {
                            this.showToast(data.message || 'خطا در تغییر وضعیت اکانت.', 'error');
                            this.loading = false;
                        }
                    } catch (err) {
                        this.showToast('خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error');
                        this.loading = false;
                    }
                }
            };
        }

        function accountConnectionsManager() {
            return {
                openCustomer: false,
                searchCustomer: '',
                customers: {!! json_encode($customersListForJs, JSON_UNESCAPED_UNICODE) !!},
                selectedCustomerId: '{{ ($account->client_id && $account->client) ? (string)$account->client_id : '' }}',

                openOrder: false,
                searchOrder: '',
                orders: {!! json_encode($ordersListForJs, JSON_UNESCAPED_UNICODE) !!},
                selectedOrderId: '{{ $account->order_id ? (string)$account->order_id : '' }}',

                get selectedCustomer() {
                    if (!this.selectedCustomerId) return null;
                    return this.customers.find(c => String(c.id) === String(this.selectedCustomerId)) || null;
                },

                get filteredCustomers() {
                    if (!this.searchCustomer.trim()) return this.customers;
                    const q = this.searchCustomer.toLowerCase().trim();
                    return this.customers.filter(c =>
                        (c.name && c.name.toLowerCase().includes(q)) ||
                        (c.email && c.email.toLowerCase().includes(q)) ||
                        (c.phone && c.phone.includes(q)) ||
                        (c.username && c.username.toLowerCase().includes(q))
                    );
                },

                get selectedOrder() {
                    if (!this.selectedOrderId) return null;
                    return this.orders.find(o => String(o.id) === String(this.selectedOrderId)) || null;
                },

                get filteredOrders() {
                    let list = this.orders;
                    // If a customer is selected, strictly filter to that customer's orders
                    if (this.selectedCustomer) {
                        list = list.filter(o => String(o.customer_id) === String(this.selectedCustomer.id));
                    }
                    if (!this.searchOrder.trim()) return list;
                    const q = this.searchOrder.toLowerCase().trim();
                    return list.filter(o =>
                        (o.order_number && o.order_number.toLowerCase().includes(q)) ||
                        (o.service_name && o.service_name.toLowerCase().includes(q)) ||
                        (o.client_name && o.client_name.toLowerCase().includes(q))
                    );
                },

                selectCustomer(c) {
                    this.selectedCustomerId = c ? String(c.id) : '';
                    this.openCustomer = false;
                    this.searchCustomer = '';
                    this.$nextTick(() => this.$refs.accountClientFullForm.submit());
                },

                clearCustomer() {
                    this.selectedCustomerId = '';
                    this.openCustomer = false;
                    this.searchCustomer = '';
                    this.$nextTick(() => this.$refs.accountClientFullForm.submit());
                },

                selectOrder(o) {
                    this.selectedOrderId = o ? String(o.id) : '';
                    if (o && o.customer_id && !this.selectedCustomerId) {
                        this.selectedCustomerId = String(o.customer_id);
                    }
                    this.openOrder = false;
                    this.searchOrder = '';
                    this.$nextTick(() => this.$refs.accountOrderFullForm.submit());
                },

                clearOrder() {
                    this.selectedOrderId = '';
                    this.openOrder = false;
                    this.searchOrder = '';
                    this.$nextTick(() => this.$refs.accountOrderFullForm.submit());
                }
            };
        }
    </script>
@endsection
