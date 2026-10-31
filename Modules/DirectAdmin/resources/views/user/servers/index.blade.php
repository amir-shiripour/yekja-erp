@extends('layouts.user')

@section('title', 'مدیریت سرورهای دایرکت ادمین')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ openAddModal: @json($autoOpenAdd ?? false), activeEditServer: null }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </span>
                <span>تنظیمات سرورهای دایرکت ادمین</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 ms-1">
                مدیریت سرورها و اطلاعات احراز هویت جهت اتصال زنده، ساخت خودکار اکانت و همگام‌سازی سرویس‌ها
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.directadmin.accounts.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>لیست اکانت‌ها</span>
            </a>

            <button @click="openAddModal = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>افزودن سرور جدید</span>
            </button>
        </div>
    </div>

    {{-- Status Alert --}}
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

    {{-- Info Alert --}}
    @if(session('info'))
        <div
            class="p-5 text-sm text-indigo-800 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center gap-4 shadow-sm animate-in fade-in">
            <span class="bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 p-2 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="font-bold">
                {{ session('info') }}
            </div>
        </div>
    @endif

    {{-- Server list Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @forelse($servers as $srv)
            <div class="{{ $cardClass }} flex flex-col justify-between overflow-hidden"
                 x-data="{ testing: false, testResult: null, testSuccess: null }">
                <div>
                    {{-- Card Header --}}
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="relative flex h-3.5 w-3.5">
                                @if($srv->is_active)
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                                @else
                                    <span
                                        class="relative inline-flex rounded-full h-3.5 w-3.5 bg-gray-400 dark:bg-gray-600"></span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white flex flex-wrap items-center gap-2">
                                    <span>{{ $srv->name }}</span>
                                    @if($srv->isDefault())
                                        <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            سرور پیش‌فرض
                                        </span>
                                    @endif
                                    @if($srv->isManual())
                                        <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                            دستی (آفلاین)
                                        </span>
                                    @endif
                                    @if($srv->is_active)
                                        <span
                                            class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                            فعال
                                        </span>
                                    @else
                                        <span
                                            class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                            غیرفعال
                                        </span>
                                    @endif
                                </h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(!$srv->isDefault() && $srv->is_active)
                                <form action="{{ route('user.directadmin.servers.set-default', $srv) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-all cursor-pointer" title="انتخاب به عنوان سرور پیش‌فرض سفارشات">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </button>
                                </form>
                            @endif

                            <button @click="activeEditServer = {{ $srv->toJson() }}"
                                    class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-all"
                                    title="ویرایش سرور">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            <form action="{{ route('user.directadmin.servers.destroy', $srv) }}" method="POST"
                                  onsubmit="return confirm('آیا از حذف این سرور اطمینان دارید؟');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 dark:hover:text-rose-400 transition-all"
                                        title="حذف سرور">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span
                                    class="text-xs font-bold text-gray-500 dark:text-gray-400 block">آدرس و پورت سرور</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white block font-mono truncate"
                                      dir="ltr">
                                    {{ $srv->host }}:{{ $srv->port }}
                                </span>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">نام کاربری نماینده (Reseller)</span>
                                <span
                                    class="text-sm font-black text-indigo-600 dark:text-indigo-400 block font-mono truncate"
                                    dir="ltr">
                                    {{ $srv->username }}
                                </span>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">پروتکل ارتباطی (SSL)</span>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-black {{ $srv->ssl ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $srv->ssl ? 'HTTPS (فعال)' : 'HTTP (عادی)' }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                                <span
                                    class="text-xs font-bold text-gray-500 dark:text-gray-400 block">آخرین اتصال موفق</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block truncate">
                                    {{ $srv->last_connected_at ? jdate($srv->last_connected_at)->format('Y/m/d H:i') : 'هنوز ثبت نشده' }}
                                </span>
                            </div>
                        </div>

                        {{-- Connection status & live test box --}}
                        <div class="pt-2">
                            <div x-show="testing"
                                 class="p-3.5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center gap-3 text-indigo-700 dark:text-indigo-400 text-xs font-bold">
                                <svg class="animate-spin h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                     fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>در حال برقراری ارتباط و سنجش پینگ سرور...</span>
                            </div>

                            <div x-show="testResult && testSuccess === true" x-cloak
                                 class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>ارتباط با سرور با موفقیت برقرار شد!</span>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 font-mono text-[11px] border border-emerald-200 dark:border-emerald-500/30">
                                    پینگ: <span x-text="testResult.latency"></span> ms
                                </span>
                            </div>

                            <div x-show="testResult && testSuccess === false" x-cloak
                                 class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs space-y-1.5">
                                <div class="font-bold flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>خطا در برقراری ارتباط:</span>
                                </div>
                                <div
                                    class="font-mono text-[11px] bg-white/80 dark:bg-gray-900/60 p-2.5 rounded-xl border border-rose-200/60 dark:border-rose-500/20 break-all"
                                    x-text="testResult.error"></div>
                            </div>

                            @if($srv->last_error && !old('host'))
                                <div x-show="!testResult"
                                     class="p-3.5 rounded-2xl bg-rose-50/70 dark:bg-rose-500/10 border border-rose-200/70 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs space-y-1.5">
                                    <div class="font-bold flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>آخرین خطای ثبت شده:</span>
                                    </div>
                                    <div
                                        class="font-mono text-[11px] bg-white/80 dark:bg-gray-900/60 p-2.5 rounded-xl border border-rose-200/60 dark:border-rose-500/20 break-all">
                                        {{ $srv->last_error }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div
                    class="p-4 sm:px-6 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-b-3xl flex items-center justify-between gap-3">
                    <button type="button"
                            @click="testing = true; testResult = null;
                                    axios.post('{{ route('user.directadmin.servers.test') }}', { server_id: {{ $srv->id }} })
                                        .then(res => {
                                            testSuccess = res.data.success;
                                            testResult = res.data;
                                        })
                                        .catch(err => {
                                            testSuccess = false;
                                            testResult = { error: err.message };
                                        })
                                        .finally(() => testing = false)"
                            class="px-4 py-2.5 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>تست اتصال زنده</span>
                    </button>

                    <form action="{{ route('user.directadmin.accounts.sync') }}" method="POST">
                        @csrf
                        <input type="hidden" name="server_id" value="{{ $srv->id }}">
                        <button type="submit"
                                class="px-4 py-2.5 text-xs font-bold rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                            <span>همگام‌سازی اکانت‌ها</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full {{ $cardClass }} p-12 text-center text-gray-500 dark:text-gray-400 space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-800 dark:text-gray-200">هیچ سروری ثبت نشده است</h3>
                    <p class="text-xs text-gray-400 mt-1">جهت شروع ساخت اکانت و اتصال به دایرکت ادمین، لطفاً سرور میزبان
                        خود را ثبت کنید.</p>
                </div>
                <button @click="openAddModal = true"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>افزودن اولین سرور</span>
                </button>
            </div>
        @endforelse
    </div>

    {{-- Add Server Modal (State-of-the-Art Redesign) --}}
    <div x-show="openAddModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            {{-- Backdrop blur --}}
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                 @click="openAddModal = false"></div>

            {{-- Modal Card --}}
            <div
                class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800"
                x-data="{ 
                    showAddPass: false,
                    serverType: 'api',
                    serverName: '',
                    serverHost: '',
                    serverPort: '2222',
                    serverUsername: '',
                    setAsDefault: false,
                    selectType(type) {
                        this.serverType = type;
                        if (type === 'manual') {
                            if (!this.serverName) this.serverName = 'سرور پیش فرض';
                            if (!this.serverHost) this.serverHost = 'localhost';
                            if (!this.serverUsername) this.serverUsername = 'manual';
                            this.setAsDefault = true;
                        }
                    }
                }">

                {{-- Modal Header --}}
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-gray-50/90 via-white to-gray-50/90 dark:from-gray-900/90 dark:via-gray-800/80 dark:to-gray-900/90 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">ثبت سرور دایرکت ادمین جدید</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">افزودن مشخصات سرور جهت مدیریت آنلاین یا سفارشات دستی</p>
                        </div>
                    </div>
                    <button @click="openAddModal = false"
                            class="p-2.5 rounded-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Mode Switcher: Online API vs Manual Offline Server --}}
                <div class="px-6 sm:px-8 pt-6">
                    <div class="grid grid-cols-2 gap-3 p-1.5 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <button type="button" @click="selectType('api')"
                                :class="serverType === 'api' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                            </svg>
                            <span>سرور آنلاین دایرکت ادمین (API)</span>
                        </button>
                        <button type="button" @click="selectType('manual')"
                                :class="serverType === 'manual' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                class="py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>سرور پیش‌فرض / دستی (آفلاین)</span>
                        </button>
                    </div>
                </div>

                {{-- Modal Body Form --}}
                <form method="POST" action="{{ route('user.directadmin.servers.store') }}" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    <input type="hidden" name="server_type" :value="serverType">

                    {{-- Group 1: Server Display Name --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 space-y-2">
                        <label class="{{ $labelClass }}">
                            نام نمایشی سرور <span class="text-rose-500 font-black">*</span>
                        </label>
                        <input type="text" name="name" x-model="serverName" required class="{{ $inputClass }} font-bold"
                               placeholder="مثلاً: سرور پیش فرض یا سرور اصلی دایرکت ادمین">
                        <p class="text-[11px] text-gray-400">یک نام مشخص و خوانا جهت نمایش در لیست‌ها و فرم ساخت هاست انتخاب کنید.</p>
                    </div>

                    {{-- Group 2: Host and Port --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 space-y-4">
                        <span class="text-xs font-black text-gray-700 dark:text-gray-300 block">مشخصات شبکه و آدرس اتصال سرور</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="{{ $labelClass }}">
                                    IP سرور یا نام دامنه هاست <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text" name="host" x-model="serverHost" required class="{{ $inputClass }} text-start font-mono"
                                       placeholder="localhost یا 185.10.x.x" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">
                                    پورت پنل <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="number" name="port" x-model="serverPort" value="2222" required
                                       class="{{ $inputClass }} text-start font-mono font-bold" dir="ltr">
                            </div>
                        </div>
                    </div>

                    {{-- Group 3: Credentials --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 space-y-4">
                        <span class="text-xs font-black text-gray-700 dark:text-gray-300 block">اطلاعات احراز هویت نماینده (Reseller Credentials)</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    نام کاربری نماینده (Username) <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text" name="username" x-model="serverUsername" required
                                       class="{{ $inputClass }} text-start font-mono font-bold" placeholder="admin یا manual"
                                       dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">
                                    <span>رمز عبور یا کلید ورود (Login Key)</span>
                                    <span x-show="serverType === 'api'" class="text-rose-500 font-black">*</span>
                                    <span x-show="serverType === 'manual'" class="text-xs text-gray-400 font-normal ms-1">(اختیاری در حالت دستی)</span>
                                </label>
                                <div class="relative">
                                    <input :type="showAddPass ? 'text' : 'password'" name="password" :required="serverType === 'api'"
                                           class="{{ $inputClass }} text-start font-mono pr-10" placeholder="••••••••"
                                           dir="ltr">
                                    <button type="button" @click="showAddPass = !showAddPass"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                                        <svg x-show="!showAddPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showAddPass" x-cloak class="w-4 h-4" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Group 4: Options --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 cursor-pointer hover:bg-gray-100/70 dark:hover:bg-gray-800/80 transition-all">
                            <input type="checkbox" name="ssl" value="1" checked
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                            <div>
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 block">پروتکل امن SSL (HTTPS)</span>
                                <span
                                    class="text-[11px] text-gray-400 block mt-0.5">اتصال رمزنگاری‌شده با پورت امن</span>
                            </div>
                        </label>

                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 cursor-pointer hover:bg-gray-100/70 dark:hover:bg-gray-800/80 transition-all">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                            <div>
                                <span
                                    class="text-xs font-black text-gray-800 dark:text-gray-200 block">سرور فعال باشد</span>
                                <span
                                    class="text-[11px] text-gray-400 block mt-0.5">نمایش در فرم ساخت اکانت و داشبورد</span>
                            </div>
                        </label>

                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 cursor-pointer hover:bg-indigo-100/70 dark:hover:bg-indigo-900/30 transition-all sm:col-span-2">
                            <input type="checkbox" name="set_as_default" value="1" x-model="setAsDefault"
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                            <div>
                                <span
                                    class="text-xs font-black text-indigo-900 dark:text-indigo-200 block">تنظیم به عنوان سرور پیش‌فرض سفارشات</span>
                                <span
                                    class="text-[11px] text-indigo-700/80 dark:text-indigo-400 block mt-0.5">در سفارشات هاستینگ جدید این سرور به عنوان گزینه پیش‌فرض انتخاب خواهد شد.</span>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openAddModal = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            انصراف
                        </button>
                        <button type="submit"
                                class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-black hover:shadow-indigo-500/40 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ثبت و ذخیره سرور</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Server Modal (State-of-the-Art Redesign) --}}
    <div x-show="activeEditServer !== null" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                 @click="activeEditServer = null"></div>

            <div
                class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full z-10 border border-gray-100 dark:border-gray-800"
                x-data="{ showEditPass: false }">

                {{-- Modal Header --}}
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-gray-50/90 via-white to-gray-50/90 dark:from-gray-900/90 dark:via-gray-800/80 dark:to-gray-900/90 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">ویرایش تنظیمات سرور</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">به‌روزرسانی آدرس، پورت یا اطلاعات
                                احراز هویت سرور</p>
                        </div>
                    </div>
                    <button @click="activeEditServer = null"
                            class="p-2.5 rounded-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Form --}}
                <form method="POST"
                      :action="`{{ url('user/directadmin/servers') }}/${activeEditServer ? activeEditServer.id : ''}`"
                      class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Group 1: Server Name --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 space-y-2">
                        <label class="{{ $labelClass }}">
                            نام نمایشی سرور <span class="text-rose-500 font-black">*</span>
                        </label>
                        <input type="text" name="name" required :value="activeEditServer ? activeEditServer.name : ''"
                               class="{{ $inputClass }} font-bold">
                    </div>

                    {{-- Group 2: Host and Port --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 space-y-4">
                        <span class="text-xs font-black text-gray-700 dark:text-gray-300 block">مشخصات شبکه و آدرس اتصال سرور</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="{{ $labelClass }}">
                                    IP سرور یا نام دامنه هاست <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text" name="host" required
                                       :value="activeEditServer ? activeEditServer.host : ''"
                                       class="{{ $inputClass }} text-start font-mono" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">
                                    پورت <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="number" name="port" required
                                       :value="activeEditServer ? activeEditServer.port : '2222'"
                                       class="{{ $inputClass }} text-start font-mono font-bold" dir="ltr">
                            </div>
                        </div>
                    </div>

                    {{-- Group 3: Credentials --}}
                    <div
                        class="p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 space-y-4">
                        <span class="text-xs font-black text-gray-700 dark:text-gray-300 block">اطلاعات احراز هویت نماینده (Reseller Credentials)</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    نام کاربری نماینده <span class="text-rose-500 font-black">*</span>
                                </label>
                                <input type="text" name="username" required
                                       :value="activeEditServer ? activeEditServer.username : ''"
                                       class="{{ $inputClass }} text-start font-mono font-bold" dir="ltr">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">
                                    رمز عبور جدید <span class="text-xs font-normal text-gray-400 ms-1">(تنها در صورت تغییر وارد کنید)</span>
                                </label>
                                <div class="relative">
                                    <input :type="showEditPass ? 'text' : 'password'" name="password"
                                           class="{{ $inputClass }} text-start font-mono pr-10" placeholder="••••••••"
                                           dir="ltr">
                                    <button type="button" @click="showEditPass = !showEditPass"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                                        <svg x-show="!showEditPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showEditPass" x-cloak class="w-4 h-4" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Group 4: Options --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 cursor-pointer hover:bg-gray-100/70 dark:hover:bg-gray-800/80 transition-all">
                            <input type="checkbox" name="ssl" value="1"
                                   :checked="activeEditServer && activeEditServer.ssl"
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                            <div>
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200 block">پروتکل امن SSL (HTTPS)</span>
                                <span
                                    class="text-[11px] text-gray-400 block mt-0.5">اتصال رمزنگاری‌شده با پورت امن</span>
                            </div>
                        </label>

                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 cursor-pointer hover:bg-gray-100/70 dark:hover:bg-gray-800/80 transition-all">
                            <input type="checkbox" name="is_active" value="1"
                                   :checked="activeEditServer && activeEditServer.is_active"
                                   class="rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                            <div>
                                <span
                                    class="text-xs font-black text-gray-800 dark:text-gray-200 block">سرور فعال باشد</span>
                                <span
                                    class="text-[11px] text-gray-400 block mt-0.5">نمایش در فرم ساخت اکانت و داشبورد</span>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="activeEditServer = null"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            انصراف
                        </button>
                        <button type="submit"
                                class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-black hover:shadow-indigo-500/40 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ذخیره تغییرات سرور</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
