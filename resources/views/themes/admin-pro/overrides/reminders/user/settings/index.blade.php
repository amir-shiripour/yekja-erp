@extends('layouts.user')

@php
    use Morilog\Jalali\Jalalian;

    $inProgressEnabled = isset($settings['reminders_in_progress_enabled']) ? $settings['reminders_in_progress_enabled'] == '1' : true;
    $snoozeEnabled = isset($settings['reminders_snooze_enabled']) ? $settings['reminders_snooze_enabled'] == '1' : true;
    $snoozeLimit = $settings['reminders_snooze_limit'] ?? 5;
    $reasonRequired = $settings['reminders_snooze_reason_required'] ?? 'optional';

    $reasonOptions = [
        ['value' => 'optional', 'label' => 'اختیاری (در صورت تمایل کاربر دلیل بنویسد)'],
        ['value' => 'required', 'label' => 'اجباری (کاربر حتماً باید دلیلی بنویسد)'],
        ['value' => 'disabled', 'label' => 'غیرفعال (امکان ثبت دلیل وجود نداشته باشد)'],
    ];

    $roleOptions = $roles->map(function($r) {
        $displayName = !empty($r->display_name) ? $r->display_name : $r->name;
        return [
            'value'    => $r->name,
            'label'    => $displayName,
            'subtitle' => $displayName !== $r->name ? $r->name : null,
            'initial'  => mb_substr($displayName, 0, 1),
        ];
    })->values()->all();

    $userOptions = $users->map(fn($u) => [
        'value'    => (string) $u->id,
        'label'    => $u->name,
        'initial'  => mb_substr($u->name, 0, 1),
        'subtitle' => $u->email,
        'email'    => $u->email,
    ])->values()->all();

    $faNum = function ($str) {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace($en, $fa, (string) $str);
    };
@endphp

@section('title', 'تنظیمات یادآوری و تعویق')

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
                <span class="text-indigo-600 dark:text-indigo-400 font-black">تنظیمات یادآوری و تعویق</span>
            </nav>

            {{-- ردیف هدر: آیکون، عنوان و دکمه بازگشت --}}
            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            تنظیمات یادآوری و تعویق
                        </h1>
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

        {{-- هشدارهای سیستمی --}}
        @if(session('success'))
            <x-ui.alert variant="success" title="عملیات موفقیت‌آمیز" :dismissible="true">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('status'))
            <x-ui.alert variant="success" title="پیام سیستم" :dismissible="true">
                {{ session('status') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" title="بروز خطا" :dismissible="true">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <form action="{{ route('user.reminders.settings.update') }}" method="POST"
              x-data="{ submitting: false }"
              @submit="submitting = true"
              class="space-y-6 sm:space-y-8">
            @csrf
            @method('PUT')

            {{-- بخش ۱: قوانین و رفتارهای تعویق (Snooze Policies) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-20"
                 x-data="{ snoozeEnabled: @json($snoozeEnabled), inProgressEnabled: @json($inProgressEnabled) }">

                {{-- هدر کارت --}}
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">قوانین و رفتارهای تعویق</h2>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 transition-all hover:bg-gray-50 dark:hover:bg-gray-900/60">
                        <div class="space-y-1 min-w-0">
                            <label for="reminders_in_progress_enabled" class="text-xs font-black text-gray-900 dark:text-white block cursor-pointer">
                                امکان تغییر وضعیت به «درحال انجام»
                            </label>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" id="reminders_in_progress_enabled" name="reminders_in_progress_enabled" value="1" x-model="inProgressEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600 transition-colors shadow-2xs"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 transition-all hover:bg-gray-50 dark:hover:bg-gray-900/60">
                        <div class="space-y-1 min-w-0">
                            <label for="reminders_snooze_enabled" class="text-xs font-black text-gray-900 dark:text-white block cursor-pointer">
                                فعال بودن قابلیت تعویق (Snooze)
                            </label>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                امکان موکول کردن یادآوری به آینده با گزینه‌های زمانی از پیش‌تعریف‌شده یا زمان سفارشی.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" id="reminders_snooze_enabled" name="reminders_snooze_enabled" value="1" x-model="snoozeEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600 transition-colors shadow-2xs"></div>
                        </label>
                    </div>

                    <div x-show="snoozeEnabled"
                         x-cloak
                         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250 transform"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition duration-150 transform"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="space-y-5 pt-2 border-t border-gray-100 dark:border-gray-700/50">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="reminders_snooze_limit" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    حداکثر تعداد مجاز تعویق
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           id="reminders_snooze_limit"
                                           name="reminders_snooze_limit"
                                           value="{{ $snoozeLimit }}"
                                           min="1"
                                           max="100"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-hidden">
                                </div>
                            </div>

                            <div class="relative focus-within:z-50">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    وضعیت ثبت دلیل تعویق
                                </label>
                                <x-ui.select
                                    name="reminders_snooze_reason_required"
                                    :options="$reasonOptions"
                                    :value="$reasonRequired"
                                    placeholder="انتخاب وضعیت..."
                                    placement="top"
                                    :searchable="false"
                                    :clearable="false"
                                />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-30">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">تنظیمات ارجاع خودکار (Escalation)</h2>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5 relative focus-within:z-50">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                ارجاع خودکار به نقش‌های مدیریتی زیر
                            </label>
                            <x-ui.select
                                name="reminders_escalation_roles"
                                :options="$roleOptions"
                                :value="$escalationRoles"
                                :multiple="true"
                                placeholder="انتخاب نقش‌های مسئول..."
                                searchPlaceholder="جستجوی نقش..."
                                placement="top"
                                color="indigo"
                            />
                        </div>

                        <div class="space-y-1.5 relative focus-within:z-50">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                ارجاع خودکار به کاربران اختصاصی زیر
                            </label>
                            <x-ui.select
                                name="reminders_escalation_users"
                                :options="$userOptions"
                                :value="array_map('strval', $escalationUsers)"
                                :multiple="true"
                                placeholder="انتخاب کاربران مسئول..."
                                searchPlaceholder="جستجوی نام یا ایمیل..."
                                placement="top"
                                color="indigo"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative md:sticky md:bottom-6 z-10 mb-6 md:mb-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xl shadow-gray-200/50 dark:shadow-black/30 flex items-center justify-between gap-4 transition-all">
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.reminders.index') }}"
                       class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700 text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300 transition-all shadow-2xs active:scale-95">
                        <svg class="w-4 h-4 rtl:rotate-180 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>انصراف و بازگشت</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2.5 px-6 sm:px-8 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black text-xs sm:text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 active:scale-95 transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-show="submitting"
                              class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin shrink-0"></span>
                        <svg x-show="!submitting" class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-show="!submitting">ذخیره تنظیمات یادآوری</span>
                        <span x-show="submitting">در حال ذخیره‌سازی...</span>
                    </button>
                </div>
            </div>
        </form>
        <div class="h-28 md:hidden" style="height: 7rem;" aria-hidden="true"></div>
    </div>
@endsection
