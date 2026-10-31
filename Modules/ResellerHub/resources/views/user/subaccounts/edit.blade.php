@extends('layouts.user')

@section('title', 'ویرایش حساب زیرمجموعه: ' . $subAccount->name)

@php
    $tenantsCount = $subAccount->tenants()->count();
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="subAccountEditor()">
        {{-- Header --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200/60 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-indigo-700 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 ring-4 ring-indigo-50 dark:ring-indigo-950/40">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ __('ویرایش حساب زیرمجموعه') }}</h1>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60">
                        {{ $subAccount->name }}
                    </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('بروزرسانی مشخصات خریدار و اطلاعات تماس') }}</p>
                </div>
            </div>
            <a href="{{ route('reseller.sub-accounts.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all border border-gray-200 dark:border-gray-700 shadow-2xs self-start sm:self-auto cursor-pointer">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('بازگشت به لیست زیرمجموعه‌ها') }}</span>
            </a>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Form Section --}}
            <div class="lg:col-span-2">
                <x-resellerhub::card class="overflow-hidden p-6 sm:p-8">
                    <form @submit.prevent="submitForm()"
                          action="{{ route('reseller.sub-accounts.update', $subAccount->id) }}" method="POST"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <div
                                class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/80 mb-5">
                            <span
                                class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ __('اطلاعات هویتی و تماس') }}</h3>
                            </div>

                            <div class="space-y-5">
                                {{-- Name --}}
                                <div>
                                    <x-resellerhub::label>
                                        {{ __('نام و نام خانوادگی خریدار') }} <span
                                            class="text-rose-500 font-bold">*</span>
                                    </x-resellerhub::label>
                                    <div class="relative">
                                        <x-resellerhub::input class="pl-10" type="text"
                                                              name="name"
                                                              x-model="form.name"

                                                              placeholder="{{ __('مثلاً: دکتر علی محمدی') }}"
                                                              required/>
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <template x-if="errors.name">
                                    <span class="text-xs text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="errors.name[0]"></span>
                                    </span>
                                    </template>
                                    @error('name')
                                    <span x-show="!errors.name"
                                          class="text-xs text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </div>

                                {{-- Mobile --}}
                                <div>
                                    <x-resellerhub::label>
                                        {{ __('شماره تلفن همراه') }} <span class="text-rose-500 font-bold">*</span>
                                    </x-resellerhub::label>
                                    <div class="relative">
                                        <x-resellerhub::input class="pl-10 text-left tracking-wider" type="text"
                                                              name="mobile"
                                                              x-model="form.mobile"
                                                              dir="ltr"

                                                              placeholder="09123456789"
                                                              required/>
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1.5">{{ __('شماره تماس اصلی مشتری جهت پیامک‌های سیستمی و هماهنگی.') }}</p>
                                    <template x-if="errors.mobile">
                                    <span class="text-xs text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="errors.mobile[0]"></span>
                                    </span>
                                    </template>
                                    @error('mobile')
                                    <span x-show="!errors.mobile"
                                          class="text-xs text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </span>
                                    @enderror
                                </div>

                                {{-- Company / Brand Name (Optional) --}}
                                <div>
                                    <x-resellerhub::label>
                                        {{ __('نام کلینیک، مرکز یا نام تجاری') }} <span
                                            class="text-xs text-gray-400 font-normal">({{ __('اختیاری') }})</span>
                                    </x-resellerhub::label>
                                    <div class="relative">
                                        <x-resellerhub::input class="pl-10" type="text"
                                                              name="company_name"
                                                              x-model="form.company_name"

                                                              placeholder="{{ __('مثلاً: کلینیک تخصصی دندانپزشکی رازی') }}"/>
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <template x-if="errors.company_name">
                                    <span class="text-xs text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                                       stroke-width="2"
                                                                       d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="errors.company_name[0]"></span>
                                    </span>
                                    </template>
                                    @error('company_name')
                                    <span x-show="!errors.company_name"
                                          class="text-xs text-rose-500 font-bold mt-1.5 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Module Usage Limits & Quotas Section --}}
                        <div
                            class="rounded-2xl border border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/30 p-5 space-y-4">
                            <div
                                class="flex items-center justify-between pb-3 border-b border-gray-200/60 dark:border-gray-700/80">
                                <div class="flex items-center gap-2">
                                <span
                                    class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </span>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">{{ __('سقف مصرف و لیمیت‌های ماژول‌ها (اختیاری)') }}</h4>
                                        <p class="text-xs text-gray-400">{{ __('حداکثر سقف مجاز برای این زیرمجموعه را تعیین کنید (خالی گذاشتن فیلدها به معنی نامحدود است).') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Invoices Limit --}}
                                <div
                                    class="p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/70 space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        {{ __('حداکثر تعداد فاکتور مجاز') }}
                                    </label>
                                    <input type="number"
                                           min="1"
                                           name="limits[invoices]"
                                           value="{{ old('limits.invoices', $subAccount->getLimit('invoices', '')) }}"
                                           placeholder="{{ __('مثلاً: ۲۰ (خالی = نامحدود)') }}"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 text-center font-bold">
                                    <p class="text-xs text-gray-400">{{ __('سقف صدور فاکتور توسط یا برای این زیرمجموعه') }}</p>
                                </div>
                                @if(!empty($availableModules))
                                    @foreach($availableModules as $mod)
                                        @if(!in_array($mod->slug, ['invoices', 'resellerhub', 'settings', 'admin', 'core']))
                                            <div
                                                class="p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/70 space-y-1.5">
                                                <label
                                                    class="block text-xs font-bold text-gray-700 dark:text-gray-300 truncate">
                                                    {{ __('سقف ماژول') }} {{ $mod->title }}
                                                </label>
                                                <input type="number"
                                                       min="1"
                                                       name="limits[{{ $mod->slug }}]"
                                                       value="{{ old('limits.' . $mod->slug, $subAccount->getLimit($mod->slug, '')) }}"
                                                       placeholder="{{ __('نامحدود') }}"
                                                       class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-2xs dark:text-white dark:placeholder-gray-500 text-center font-bold">
                                                <p class="text-xs text-gray-400 truncate">{{ __('محدودیت تعداد رکورد یا سقف استفاده') }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- System Credentials Read-Only Card --}}
                        <div
                            class="rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/70 dark:border-gray-700/60 p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span>{{ __('اطلاعات دسترسی سیستمی (مدیریت خودکار)') }}</span>
                                </h4>
                                <span
                                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md">
                                {{ __('فعال') }}
                            </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div
                                    class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                <span
                                    class="text-gray-400 text-xs block">{{ __('شناسه یکتای کاربری / ایمیل سیستمی:') }}</span>
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-left block mt-1 select-all truncate">{{ $subAccount->email }}</span>
                                </div>
                                <div
                                    class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-400 text-xs block">{{ __('رمز عبور:') }}</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 block mt-1">
                                    •••••••• (محفوظ در سامانه)
                                </span>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div
                            class="pt-5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                            <a href="{{ route('reseller.sub-accounts.index') }}"
                               class="px-5 py-2.5 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer">
                                {{ __('انصراف') }}
                            </a>
                            <button type="submit"
                                    :disabled="submitting"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-600/25 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="submitting">
                                    <svg class="animate-spin w-4 h-4 text-white shrink-0" fill="none"
                                         viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!submitting">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <span x-text="submitting ? 'در حال ذخیره...' : 'ذخیره تغییرات'"></span>
                            </button>
                        </div>
                    </form>
                </x-resellerhub::card>
            </div>

            {{-- Sidebar Info --}}
            <div class="space-y-6">
                {{-- Connected Tenants Card --}}
                <div
                    class="rounded-3xl bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ __('سامانه‌های CRM متصل') }}</h3>
                        </div>
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                        {{ $tenantsCount }} {{ __('سامانه') }}
                    </span>
                    </div>

                    @if($tenantsCount > 0)
                        <div class="space-y-2">
                            @foreach($subAccount->tenants()->take(5)->get() as $t)
                                <div
                                    class="p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                    <div class="truncate">
                                        <div
                                            class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $t->name }}</div>
                                        <div class="text-xs text-gray-400 dir-ltr text-left">{{ $t->subdomain }}</div>
                                    </div>
                                    <a href="{{ route('reseller.tenants.show', $t->id) }}"
                                       class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline shrink-0">
                                        {{ __('مشاهده') }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 leading-relaxed">
                            {{ __('هنوز هیچ سامانه CRM به این حساب متصل نشده است. می‌توانید در بخش ساخت CRM جدید، این زیرمجموعه را انتخاب فرمایید.') }}
                        </p>
                    @endif
                </div>

                <div
                    class="rounded-3xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 p-6 space-y-3">
                    <h4 class="text-xs font-black text-indigo-950 dark:text-indigo-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('قابلیت اتصال چند سامانه') }}</span>
                    </h4>
                    <p class="text-xs text-indigo-700 dark:text-indigo-300/90 leading-relaxed">
                        {{ __('یک مشتری یا زیرمجموعه می‌تواند مالک چندین شعبه یا سیستم CRM باشد. تمامی فاکتورها و لایسنس‌های این سامانه‌ها در پنل اختصاصی مشتری تجمیع می‌گردند.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div x-show="toast.show" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 end-6 z-[100] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl text-xs sm:text-sm font-bold border backdrop-blur-md"
             :class="toast.type === 'success' ? 'bg-emerald-600/95 border-emerald-500 text-white' : 'bg-rose-600/95 border-rose-500 text-white'">
            <svg x-show="toast.type === 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="toast.type !== 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span x-text="toast.message"></span>
        </div>
    </div>

    <script>
        function subAccountEditor() {
            return {
                form: {
                    name: @json(old('name', $subAccount->name)),
                    mobile: @json(old('mobile', $subAccount->mobile)),
                    company_name: @json(old('company_name', $subAccount->company_name ?? ''))
                },
                submitting: false,
                errors: {},
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 4000);
                },
                async submitForm() {
                    if (this.submitting) return;
                    this.errors = {};
                    this.submitting = true;

                    try {
                        const res = await fetch("{{ route('reseller.sub-accounts.update', $subAccount->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-HTTP-Method-Override': 'PUT'
                            },
                            body: JSON.stringify({
                                ...this.form,
                                _method: 'PUT'
                            })
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            this.showToast(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect_url || "{{ route('reseller.sub-accounts.index') }}";
                            }, 700);
                        } else if (res.status === 422 && data.errors) {
                            this.errors = data.errors;
                            const firstErr = Object.values(data.errors)[0];
                            this.showToast(Array.isArray(firstErr) ? firstErr[0] : firstErr, 'error');
                        } else {
                            this.showToast(data.message || 'خطا در بروزرسانی اطلاعات.', 'error');
                        }
                    } catch (e) {
                        this.showToast('خطای ارتباط با سرور. لطفاً دوباره تلاش کنید.', 'error');
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>

    @include('resellerhub::partials.prevent-double-submit')
@endsection
