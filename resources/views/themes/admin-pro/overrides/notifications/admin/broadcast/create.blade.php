@extends('layouts.user')

@php
    $categoryOptionsList = collect($categories)->map(fn($c, $key) => [
        'value' => (string) $key,
        'label' => $c['label'],
    ])->values()->all();

    $priorityOptionsList = collect($priorities)->map(fn($p, $key) => [
        'value' => (string) $key,
        'label' => $p['label'],
    ])->values()->all();

    $severityOptionsList = collect($severities)->map(fn($s, $key) => [
        'value' => (string) $key,
        'label' => $s['label'],
    ])->values()->all();
@endphp

@section('title', 'ارسال اطلاعیه همگانی جدید')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data="broadcastForm()">
        <header class="space-y-2.5 sm:space-y-3">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('admin.notifications.broadcast.index') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">اطلاعیه‌های همگانی</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">ارسال اطلاعیه جدید</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            ارسال اطلاعیه همگانی جدید
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.notifications.broadcast.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="hidden sm:inline">بازگشت به تاریخچه</span>
                        <span class="sm:hidden">بازگشت</span>
                    </a>
                </div>
            </div>
        </header>

        @if($errors->any())
            <x-ui.alert variant="error" title="خطای اعتبارسنجی" :dismissible="true">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert variant="error" title="بروز خطا" :dismissible="true">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <form action="{{ route('admin.notifications.broadcast.store') }}"
              method="POST"
              @submit="submitting = true"
              class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
                        <div
                            class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                            <div
                                class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-gray-900 dark:text-white">محتوا و مخاطبان
                                    اطلاعیه</h2>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6 space-y-5">
                            <div>
                                <label for="title"
                                       class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    عنوان اطلاعیه <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       id="title"
                                       name="title"
                                       x-model="title"
                                       value="{{ old('title') }}"
                                       required
                                       placeholder="مثلاً: اطلاعیه جلسه عمومی یا به‌روزرسانی سامانه"
                                       class="w-full h-10 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white px-3.5 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden">
                            </div>

                            <div>
                                <label for="message"
                                       class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    متن پیام <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="message"
                                          name="message"
                                          x-model="message"
                                          rows="4"
                                          required
                                          placeholder="متن کامل اطلاعیه را اینجا بنویسید..."
                                          class="w-full text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white p-3.5 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden">{{ old('message') }}</textarea>
                            </div>

                            <div>
                                <label for="action_url"
                                       class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    لینک اقدام مرتبط
                                </label>
                                <input type="url"
                                       id="action_url"
                                       name="action_url"
                                       x-model="actionUrl"
                                       value="{{ old('action_url') }}"
                                       placeholder="https://..."
                                       dir="ltr"
                                       class="w-full h-10 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white px-3.5 text-left focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden">
                            </div>

                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-3">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    مخاطبان هدف <span class="text-rose-500">*</span>
                                </label>

                                <x-ui.segmented-button
                                    name="target_type"
                                    model="targetType"
                                    :fullWidth="true"
                                    size="md"
                                    :options="[
                                        ['value' => 'all',  'label' => 'تمام کاربران فعال سامانه'],
                                        ['value' => 'role', 'label' => 'بر اساس نقش‌های سازمانی'],
                                    ]"
                                />

                                <div x-show="targetType === 'role'"
                                     x-cloak
                                     x-transition
                                     class="p-3 sm:p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 space-y-2.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">انتخاب
                                        نقش‌های سازمانی:</label>
                                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-2 max-h-60 overflow-y-auto pe-1">
                                        @foreach($roles as $role)
                                            <label
                                                class="flex items-center justify-between gap-2 px-2.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 text-[11px] sm:text-xs font-bold text-gray-700 dark:text-gray-200 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors min-w-0">
                                                <span
                                                    class="truncate min-w-0">{{ $role->display_name ?: $role->name }}</span>
                                                <x-ui.toggle
                                                    name="target_roles[]"
                                                    value="{{ $role->name }}"
                                                    size="sm"
                                                />
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-20 p-5 sm:p-6 space-y-4">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-700/60">
                            ویژگی‌ها و کانال‌های ارسال
                        </h3>

                        {{-- کانال‌ها --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                کانال‌های تحویل <span class="text-rose-500">*</span>
                            </label>
                            <div class="space-y-2">
                                <div
                                    class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50">
                                    <label for="channel_database"
                                           class="text-xs font-bold text-gray-700 dark:text-gray-200 cursor-pointer">
                                        درون‌برنامه‌ای (پنل کاربری)
                                    </label>
                                    <x-ui.toggle
                                        id="channel_database"
                                        name="channels[]"
                                        value="database"
                                        :checked="true"
                                        size="sm"
                                    />
                                </div>
                                <div
                                    class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50">
                                    <label for="channel_sms"
                                           class="text-xs font-bold text-gray-700 dark:text-gray-200 cursor-pointer">
                                        پیامک (SMS)
                                    </label>
                                    <x-ui.toggle
                                        id="channel_sms"
                                        name="channels[]"
                                        value="sms"
                                        size="sm"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- دسته‌بندی --}}
                        <div class="relative z-30">
                            <label
                                class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی</label>
                            <x-ui.select
                                name="category"
                                :options="$categoryOptionsList"
                                value="broadcast"
                                :searchable="false"
                                :clearable="false"
                            />
                        </div>

                        {{-- اولویت --}}
                        <div class="relative z-20" @priority-changed.window="priority = $event.detail">
                            <label
                                class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">اولویت</label>
                            <x-ui.select
                                name="priority"
                                :options="$priorityOptionsList"
                                value="normal"
                                :searchable="false"
                                :clearable="false"
                            />
                        </div>

                        {{-- سطح اهمیت --}}
                        <div class="relative z-10" @severity-changed.window="severity = $event.detail">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نوع پیام (سطح
                                اهمیت)</label>
                            <x-ui.select
                                name="severity"
                                :options="$severityOptionsList"
                                value="info"
                                :searchable="false"
                                :clearable="false"
                            />
                        </div>
                    </div>

                    {{-- کارت پیش‌نمایش زنده --}}
                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-4 space-y-3">
                        <div class="text-[11px] font-black text-gray-400 dark:text-gray-500">پیش‌نمایش زنده در پنل
                            کاربر
                        </div>

                        <div
                            class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 shadow-xs flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border"
                                 :class="{
                                    'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 dark:border-indigo-900/40': severity === 'info',
                                    'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/40': severity === 'success',
                                    'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/40': severity === 'warning',
                                    'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/40': severity === 'danger'
                                 }">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-black text-gray-900 dark:text-white"
                                     x-text="title || 'عنوان اطلاعیه'"></div>
                                <div
                                    class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 leading-relaxed"
                                    x-text="message || 'متن پیام در اینجا نمایش داده خواهد شد...'"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- فوتر چسبان عملیات --}}
            <div
                class="sticky bottom-4 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-lg flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.notifications.broadcast.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-colors cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>انصراف و بازگشت</span>
                </a>

                <button type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-xs sm:text-sm font-black shadow-sm shadow-indigo-600/20 transition-all cursor-pointer">
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <svg x-cloak x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'در حال ارسال...' : 'ارسال فوری اطلاعیه'">ارسال فوری اطلاعیه</span>
                </button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function broadcastForm() {
            return {
                title: @js(old('title', '')),
                message: @js(old('message', '')),
                actionUrl: @js(old('action_url', '')),
                targetType: @js(old('target_type', 'all')),
                priority: @js(old('priority', 'normal')),
                severity: @js(old('severity', 'info')),
                submitting: false,
            };
        }
    </script>
@endpush
