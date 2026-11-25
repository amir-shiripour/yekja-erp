@extends('layouts.user')

@php
    $driverOptions = [
        ['value' => 'null',    'label' => 'Null (فقط لاگ - محیط تست)'],
        ['value' => 'limosms', 'label' => 'Limo SMS (لیمو اس‌ام‌اس)'],
    ];
    $currentDriver = old('driver', optional($setting)->driver ?? 'null');
@endphp

@section('title', 'تنظیمات پیامک')

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
                <a href="{{ route('user.sms.logs.index') }}"
                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">گزارش پیامک‌ها</a>
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-gray-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-black">تنظیمات پیامک</span>
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
                            تنظیمات درگاه پیامک
                        </h1>
                    </div>
                </div>

                <a href="{{ route('user.sms.logs.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="hidden sm:inline">مشاهده لاگ‌ها</span>
                    <span class="sm:hidden">لاگ‌ها</span>
                </a>
            </div>
        </header>

        @if(session('status'))
            <x-ui.alert variant="success" title="عملیات موفقیت‌آمیز"
                        :dismissible="true">{{ session('status') }}</x-ui.alert>
        @endif

        @if(isset($errors) && $errors->any())
            <x-ui.alert variant="error" title="خطا در اعتبارسنجی" :dismissible="true">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST"
              action="{{ route('user.sms.settings.update') }}"
              x-data="{ submitting: false }"
              @submit="submitting = true"
              class="space-y-6">
            @csrf
            @method('PUT')

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-20">
                <div
                    class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">تنظیمات درگاه و احراز هویت</h2>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                        <div class="space-y-1.5 relative z-30">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">درایور
                                (سرویس‌دهنده)</label>
                            <x-ui.select
                                name="driver"
                                :options="$driverOptions"
                                :value="$currentDriver"
                                :searchable="false"
                                :clearable="false"
                            />
                        </div>

                        <div>
                            <x-ui.phone-input
                                id="sender"
                                name="sender"
                                label="شماره ارسال‌کننده (Sender)"
                                mode="line"
                                placeholder="مثلاً: 3000xxxx"
                                :value="old('sender', optional($setting)->sender)"
                            />
                        </div>

                        <div>
                            <x-ui.password-input
                                id="api_key"
                                name="api_key"
                                label="کلید وب‌سرویس (API Key)"
                                :value="old('api_key', data_get($setting, 'config.api_key'))"
                                placeholder="کلید دسترسی به وب‌سرویس"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label for="base_url" class="block text-xs font-bold text-gray-700 dark:text-gray-300">آدرس
                                پایه (Base URL - اختیاری)</label>
                            <input type="text"
                                   id="base_url"
                                   name="base_url"
                                   value="{{ old('base_url', data_get($setting, 'config.base_url')) }}"
                                   placeholder="https://api.limosms.com/v1/"
                                   dir="ltr"
                                   class="w-full h-11 px-4 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 text-gray-900 dark:text-gray-100 placeholder-gray-400 text-left focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden shadow-2xs">
                        </div>
                    </div>

                    @if($clientsModuleInstalled)
                        <div class="pt-5 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">ورود دو
                                        مرحله‌ای مشتریان (OTP)</h3>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-end">
                                <div class="space-y-1.5">
                                    <label for="client_otp_pattern"
                                           class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        شناسه پترن (Pattern ID / OtpId)
                                    </label>
                                    <input type="text"
                                           id="client_otp_pattern"
                                           name="client_otp_pattern"
                                           value="{{ old('client_otp_pattern', $clientOtpPattern) }}"
                                           placeholder="مثلاً: 38291"
                                           dir="ltr"
                                           class="w-full h-11 px-4 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 text-gray-900 dark:text-gray-100 placeholder-gray-400 text-left focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden shadow-2xs">
                                </div>

                                <div
                                    class="min-h-[44px] px-4 py-2.5 rounded-xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-[11px] text-gray-600 dark:text-gray-300 leading-relaxed">
                                        در پنل پیامک الگویی مانند «کد ورود شما: <span
                                            class="font-black text-indigo-600 dark:text-indigo-400">{0}</span>» بسازید و
                                        شناسه آن را وارد کنید.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div
                class="sticky bottom-4 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-lg flex flex-wrap items-center justify-between gap-3">
                @if($balance)
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-bold text-gray-500 dark:text-gray-400">اعتبار پنل:</span>
                        <x-ui.badge variant="emerald" size="md">
                            {{ number_format((float) ($balance['balance'] ?? 0)) }} {{ $balance['currency'] ?? 'ریال' }}
                        </x-ui.badge>
                    </div>
                @else
                    <a href="{{ route('user.sms.logs.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-colors cursor-pointer">
                        <span>بازگشت</span>
                    </a>
                @endif

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
                    <span x-text="submitting ? 'در حال ذخیره...' : 'ذخیره تنظیمات پیامک'">ذخیره تنظیمات پیامک</span>
                </button>
            </div>

        </form>
    </div>
@endsection
