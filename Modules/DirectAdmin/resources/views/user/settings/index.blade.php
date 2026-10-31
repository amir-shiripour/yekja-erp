@extends('layouts.user')

@section('title', 'تنظیمات ماژول دایرکت ادمین')

@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-xs font-bold text-gray-600 dark:text-gray-300 mb-2";
    $cardClass  = "bg-white dark:bg-gray-800/40 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden";

    $tabs = [
        'general'      => ['label' => 'عمومی و سرورها', 'icon' => 'server'],
        'automation'   => ['label' => 'اتوماسیون سفارشات', 'icon' => 'bolt'],
        'provisioning' => ['label' => 'دسته‌بندی سیستمی', 'icon' => 'tag'],
    ];
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="directadminSettingsForm()">

        {{-- Toast Notifications Partial --}}
        @include('directadmin::partials.toast')

        {{-- Hero Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">تنظیمات ماژول
                        دایرکت ادمین</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">پیکربندی سرور پیش‌فرض، اتوماسیون همگام‌سازی
                        وضعیت سفارشات و راه‌اندازی دسته‌بندی سیستمی.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.directadmin.dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>پیشخوان دایرکت ادمین</span>
                </a>
            </div>
        </div>

        {{-- Flash Status Alert --}}
        @if(session('status'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 animate-fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Main Settings Form --}}
        <form method="POST" action="{{ route('user.directadmin.settings.update') }}">
            @csrf

            {{-- Sticky Tab Navigation Bar --}}
            <input type="hidden" name="active_tab" x-model="activeTab">
            <div class="sticky top-4 z-30 mb-6">
                <div
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-2 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm flex flex-wrap items-center gap-1.5">
                    @foreach($tabs as $tabKey => $tab)
                        <button type="button" @click="activeTab = '{{ $tabKey }}'"
                                :class="activeTab === '{{ $tabKey }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-700 dark:hover:text-gray-200'"
                                class="flex-1 min-w-36 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 cursor-pointer">
                            @switch($tab['icon'])
                                @case('server')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                                    </svg>
                                    @break
                                @case('bolt')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    @break
                                @case('tag')
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    @break
                            @endswitch
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab 1: General & Servers --}}
            <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/40 to-transparent dark:from-indigo-900/10">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                                </svg>
                            </span>
                            سرور پیش‌فرض ایجاد هاستینگ
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">
                            سرور اصلی برای ساخت خودکار و دستی اکانت‌های هاستینگ در سفارشات را تعیین کنید.
                        </p>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        {{-- Mode Selector: Select vs Type --}}
                        <div class="inline-flex p-1.5 rounded-2xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700">
                            <button type="button" 
                                    @click="serverSelectionMode = 'select'"
                                    :class="serverSelectionMode === 'select' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <span>انتخاب از سرورهای موجود</span>
                            </button>

                            <button type="button" 
                                    @click="serverSelectionMode = 'type'"
                                    :class="serverSelectionMode === 'type' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>تایپ مشخصات سرور پیش‌فرض</span>
                            </button>
                        </div>
                        <input type="hidden" name="server_selection_mode" :value="serverSelectionMode">

                        {{-- Mode 1: Select Existing Server --}}
                        <div x-show="serverSelectionMode === 'select'" class="max-w-xl space-y-3">
                            <label class="{{ $labelClass }}">انتخاب سرور فعال دایرکت ادمین</label>
                            <select name="default_server_id" class="{{ $inputClass }} font-bold cursor-pointer">
                                @forelse($servers as $srv)
                                    <option value="{{ $srv->id }}" @selected($defaultServerId == $srv->id)>
                                        {{ $srv->name }} ({{ $srv->host }}:{{ $srv->port }}) {{ $srv->isManual() ? '— [دستی]' : '' }}
                                    </option>
                                @empty
                                    <option value="">هیچ سروری ثبت نشده است (می‌توانید از دکمه «تایپ مشخصات» سرور پیش‌فرض بسازید)</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-400 mt-2.5 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                در صفحه جزئیات هر سفارش، این سرور به صورت پیش‌فرض جهت ساخت سریع هاست انتخاب خواهد شد.
                            </p>
                        </div>

                        {{-- Mode 2: Type Manual Default Server --}}
                        <div x-show="serverSelectionMode === 'type'" x-cloak class="max-w-xl p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-4">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>ثبت مستقیم مشخصات سرور پیش‌فرض دستی (بدون نیاز به رمز عبور API):</span>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">نام نمایشی سرور پیش‌فرض <span class="text-rose-500">*</span></label>
                                <input type="text" name="manual_server_name" value="{{ old('manual_server_name', 'سرور پیش فرض') }}"
                                       placeholder="مثلاً: سرور پیش فرض" class="{{ $inputClass }} font-bold">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="{{ $labelClass }}">آدرس هاست / آی‌پی</label>
                                    <input type="text" name="manual_server_host" value="{{ old('manual_server_host', 'localhost') }}"
                                           dir="ltr" placeholder="localhost" class="{{ $inputClass }} text-start font-mono">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">پورت</label>
                                    <input type="number" name="manual_server_port" value="{{ old('manual_server_port', '2222') }}"
                                           dir="ltr" class="{{ $inputClass }} text-start font-mono">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="{{ $labelClass }}">نام کاربری</label>
                                    <input type="text" name="manual_server_username" value="{{ old('manual_server_username', 'manual') }}"
                                           dir="ltr" class="{{ $inputClass }} text-start font-mono">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">توضیحات</label>
                                    <input type="text" name="manual_server_description" value="{{ old('manual_server_description', 'سرور پیش فرض برای سفارشات دستی') }}"
                                           class="{{ $inputClass }} text-xs">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Automation & Status Sync --}}
            <div x-show="activeTab === 'automation'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-purple-50/40 to-transparent dark:from-purple-900/10">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </span>
                            قوانین همگام‌سازی و اتوماسیون وضعیت سفارشات
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-13">
                            واکنش هوشمند و بلادرنگ بین وضعیت سفارش در CRM و مسدودسازی هاست در وب‌سرور دایرکت ادمین.
                        </p>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        {{-- Exact Toggle Button Matching Services Settings --}}
                        <label for="auto_sync_order_status"
                               class="flex items-center justify-between gap-4 cursor-pointer group p-6 rounded-2xl border-2 border-transparent bg-gray-50 dark:bg-gray-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                            <div class="flex-1">
                                <span class="text-base font-black text-gray-800 dark:text-gray-200 block">همگام‌سازی دوطرفه وضعیت سفارش و هاستینگ (Two-Way Sync)</span>
                                <span
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400 block mt-1 leading-relaxed">
                                    با فعال بودن این گزینه، با <strong>غیرفعال، لغو یا منقضی شدن سفارش</strong> در CRM، اکانت هاستینگ در DirectAdmin به طور خودکار <strong>مسدود (Suspend)</strong> می‌شود؛ و در صورت مسدود/فعال شدن اکانت در پنل سرور، وضعیت سفارش متناظر نیز به‌روزرسانی خواهد شد.
                                </span>
                            </div>
                            <div class="relative shrink-0">
                                <input type="hidden" name="auto_sync_order_status" value="0">
                                <input type="checkbox" id="auto_sync_order_status"
                                       name="auto_sync_order_status" value="1"
                                       @checked($autoSyncStatus) class="sr-only peer">
                                <div
                                    class="w-14 h-8 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-indigo-600 transition-colors duration-300 shadow-inner"></div>
                                <div
                                    class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                    <svg
                                        class="w-3 h-3 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Tab 3: System Category Provisioning --}}
            <div x-show="activeTab === 'provisioning'" x-cloak class="space-y-6">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-emerald-50/40 to-transparent dark:from-emerald-900/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-inner">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-black text-gray-900 dark:text-white">دسته‌بندی قفل‌شده سیستمی در
                                    ماژول خدمات</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ایجاد خودکار دسته‌بندی
                                    استاندارد و غیرقابل حذف جهت تفکیک و اختصاص سرویس‌های هاستینگ</p>
                            </div>
                        </div>

                        {{-- Dynamic Status Badge --}}
                        <div class="shrink-0">
                            <template x-if="categoryCreated">
                                <span
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 shadow-sm">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    <span>دسته‌بندی سیستمی فعال و قفل‌شده</span>
                                </span>
                            </template>
                            <template x-if="!categoryCreated">
                                <span
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>نیاز به مقداردهی اولیه</span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                            <div
                                class="md:col-span-2 space-y-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                <p>
                                    برای اینکه سیستم بتواند سفارشات هاستینگ را به صورت خودکار تشخیص دهد و امکان <strong>«ساخت
                                        مستقیم هاست از صفحه سفارش»</strong> فراهم گردد، دسته‌بندی سیستمی زیر با ویژگی
                                    <code
                                        class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 font-mono font-bold"
                                        dir="ltr">is_locked = true</code> در ماژول خدمات ایجاد می‌شود:
                                </p>
                                <div class="pt-1">
                                    <div
                                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 flex items-center gap-3.5 max-w-sm">
                                        <span
                                            class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200/60 dark:border-emerald-500/20 shadow-xs">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white block">میزبانی وب و هاستینگ</span>
                                            <span class="font-mono text-xs text-gray-400" dir="ltr">slug: hosting</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-stretch md:items-end justify-center gap-3">
                                <button type="button" @click="seedCategory()" :disabled="seeding"
                                        class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2.5 disabled:opacity-50 cursor-pointer">
                                    <template x-if="seeding">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!seeding">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <span
                                        x-text="seeding ? 'در حال ثبت دسته‌بندی...' : (categoryCreated ? 'به‌روزرسانی دسته‌بندی سیستمی' : '🚀 مقداردهی و ایجاد دسته‌بندی قفل‌شده')"></span>
                                </button>

                                <a href="{{ route('services.categories.index') }}" target="_blank"
                                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    <span>مشاهده لیست دسته‌بندی‌های خدمات</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky Action Bar Matching Services Settings --}}
            <div class="sticky bottom-4 z-40 mt-8">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-linear-to-r from-indigo-600 to-indigo-700 text-white font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-500 hover:to-indigo-600 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تنظیمات
                    </button>
                    <a href="{{ route('user.directadmin.dashboard') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function directadminSettingsForm() {
                return {
                    activeTab: '{{ session('active_tab', old('active_tab')) }}' || localStorage.getItem('directadminSettingsTab') || 'general',
                    serverSelectionMode: '{{ $servers->isNotEmpty() ? "select" : "type" }}',
                    categoryCreated: {{ $hostingCategory ? 'true' : 'false' }},
                    seeding: false,
                    toast: {
                        show: false,
                        message: '',
                        type: 'success',
                        timer: null
                    },
                    init() {
                        this.$watch('activeTab', value => localStorage.setItem('directadminSettingsTab', value));
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
                    async seedCategory() {
                        if (this.seeding) return;
                        this.seeding = true;

                        try {
                            const response = await fetch('{{ route("user.directadmin.settings.seed-category") }}', {
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
                                this.categoryCreated = true;
                                this.showToast(data.message, 'success');
                            } else {
                                this.showToast(data.message || 'خطا در ثبت دسته‌بندی سیستمی.', 'error');
                            }
                        } catch (err) {
                            this.showToast('خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error');
                        } finally {
                            this.seeding = false;
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
