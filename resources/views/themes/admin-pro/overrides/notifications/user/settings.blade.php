@extends('layouts.user')

@php
    $categoryBadgeMap = [
        'system'    => 'indigo',
        'tasks'     => 'emerald',
        'reminders' => 'amber',
        'clients'   => 'sky',
        'sales'     => 'success',
        'workflows' => 'purple',
        'broadcast' => 'rose',
    ];
@endphp

@section('title', 'تنظیمات و ترجیحات اعلان‌ها')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8">
        <header
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-4 sm:p-6 shadow-xs transition-all space-y-3 sm:space-y-4">
            <nav class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"
                 aria-label="مسیر راهنما">
                <a href="{{ route('user.dashboard') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">پیشخوان</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('user.notifications.index') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">مرکز اعلان‌ها</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">تنظیمات و ترجیحات اعلان‌ها</span>
            </nav>

            <div class="flex items-center justify-between gap-3 sm:gap-6">
                <div class="flex items-center gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-base sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            تنظیمات و ترجیحات اعلان‌ها
                        </h1>
                    </div>
                </div>

                <a href="{{ route('user.notifications.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden sm:inline">بازگشت به اعلان‌ها</span>
                    <span class="sm:hidden">بازگشت</span>
                </a>
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

        <form action="{{ route('user.notifications.settings.update') }}"
              method="POST"
              x-data="{ submitting: false }"
              @submit="submitting = true"
              class="space-y-6">
            @csrf

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-20">
                <div
                    class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">تنظیمات عمومی و ساعات سکوت</h2>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-5">
                    <div
                        class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 transition-all hover:bg-gray-50 dark:hover:bg-gray-900/60">
                        <div class="space-y-1 min-w-0">
                            <label for="sound_enabled"
                                   class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block cursor-pointer">
                                پخش صدای اعلان در مرورگر
                            </label>
                        </div>
                        <x-ui.toggle
                            id="sound_enabled"
                            name="sound_enabled"
                            :checked="$userSetting->sound_enabled"
                        />
                    </div>

                    <div x-data="{ enabled: {{ $userSetting->quiet_hours_enabled ? 'true' : 'false' }} }"
                         class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="space-y-1 min-w-0">
                                <label for="quiet_hours_enabled"
                                       class="text-xs sm:text-sm font-black text-gray-900 dark:text-white block cursor-pointer">
                                    فعال‌سازی ساعات سکوت شبانه
                                </label>
                            </div>
                            <x-ui.toggle
                                id="quiet_hours_enabled"
                                name="quiet_hours_enabled"
                                x-model="enabled"
                            />
                        </div>

                        <div x-show="enabled"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-200/70 dark:border-gray-700/60">
                            <div>
                                <x-ui.time-picker
                                    id="quiet_hours_start"
                                    name="quiet_hours_start"
                                    label="ساعت شروع سکوت"
                                    :value="substr($userSetting->quiet_hours_start ?? '22:00', 0, 5)"
                                    :step="5"
                                />
                            </div>
                            <div>
                                <x-ui.time-picker
                                    id="quiet_hours_end"
                                    name="quiet_hours_end"
                                    label="ساعت پایان سکوت"
                                    :value="substr($userSetting->quiet_hours_end ?? '08:00', 0, 5)"
                                    :step="5"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs overflow-hidden">
                <div
                    class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">کانال‌های دریافت به تفکیک موضوع</h2>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-900/20 text-[11px] font-black text-gray-500 dark:text-gray-400">
                            <th class="py-3.5 px-5">موضوع / دسته‌بندی</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">اعلان پنل</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">پیامک</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">ایمیل</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs sm:text-sm">
                        @foreach($categories as $catKey => $cat)
                            @php
                                $userPref = $userPreferences[$catKey] ?? null;
                                $dbChecked = $userPref ? $userPref->channel_database : in_array('database', $defaultChannels[$catKey] ?? ['database']);
                                $smsChecked = $userPref ? $userPref->channel_sms : in_array('sms', $defaultChannels[$catKey] ?? []);
                                $mailChecked = $userPref ? $userPref->channel_mail : in_array('mail', $defaultChannels[$catKey] ?? []);
                                $badgeVariant = $categoryBadgeMap[$catKey] ?? 'indigo';
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-ui.badge :variant="$badgeVariant" :dot="true" size="sm">
                                            {{ $cat['label'] }}
                                        </x-ui.badge>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <x-ui.toggle
                                        name="preferences[{{ $catKey }}][database]"
                                        :checked="$dbChecked"
                                        size="sm"
                                    />
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <x-ui.toggle
                                        name="preferences[{{ $catKey }}][sms]"
                                        :checked="$smsChecked"
                                        size="sm"
                                    />
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <x-ui.toggle
                                        name="preferences[{{ $catKey }}][mail]"
                                        :checked="$mailChecked"
                                        size="sm"
                                    />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                class="sticky bottom-4 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-lg flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('user.notifications.index') }}"
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
                         stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-cloak x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span
                        x-text="submitting ? 'در حال ذخیره...' : 'ذخیره تنظیمات اعلان‌ها'">ذخیره تنظیمات اعلان‌ها</span>
                </button>
            </div>

        </form>

    </div>
@endsection
