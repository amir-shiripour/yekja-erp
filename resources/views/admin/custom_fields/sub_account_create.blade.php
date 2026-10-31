{{-- resources/views/admin/custom_fields/sub_account_create.blade.php --}}
@extends('layouts.user')

@php
    $title = 'ایجاد فیلد احراز هویت زیرمجموعه‌ها (KYC)';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه اختصاصی زیرمجموعه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    تعریف فیلد احراز هویت زیرمجموعه‌ها (KYC)
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">تعیین فیلدهای هویتی و پروانه فعالیت برای مدیران سامانه‌های زیرمجموعه و خریداران لایسنس</p>
            </div>

            <a href="{{ route('admin.custom-fields.index', ['tab' => 'sub_account']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست فیلدهای زیرمجموعه
            </a>
        </div>

        {{-- بنر راهنمای کاربردی --}}
        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 flex items-start gap-3">
            <span class="flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-600 text-white shrink-0 mt-0.5 text-xs font-bold">i</span>
            <div class="text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
                این فیلدها در مرحله اولیه راه‌اندازی (Onboarding) یا صفحه تکمیل اطلاعات پروفایل مدیران زیرمجموعه‌ها نمایش داده می‌شوند و کاملاً مستقل از پرسنل داخلی سیستم هستند.
            </div>
        </div>

        {{-- نمایش خطاها در صورت وجود --}}
        @if($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800/50 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-400 font-bold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    لطفاً خطاهای زیر را بررسی کنید:
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.custom-fields.store') }}" class="space-y-6 pb-20"
              x-data="{ isSubmitting: false, showAdvanced: false }"
              @submit="isSubmitting = true">
            @csrf
            {{-- ارسال امن و قطعی entity_type و role_name برای زیرمجموعه --}}
            <input type="hidden" name="entity_type" value="sub_account">
            <input type="hidden" name="role_name" value="sub_account">

            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        مشخصات فیلد زیرمجموعه‌ها
                    </h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- برچسب فیلد --}}
                    <div>
                        <label class="{{ $labelClass }}">عنوان فیلد (Label) <span class="text-red-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}" class="{{ $inputClass }}"
                               placeholder="مثلاً: شماره پروانه نظام پزشکی، آدرس مرکز، کدملی مدیر..." required autofocus>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">این عنوان در فرم احراز هویت زیرمجموعه به مدیر نمایش داده می‌شود.</p>
                    </div>

                    {{-- نوع فیلد --}}
                    <div>
                        <label for="field_type" class="{{ $labelClass }}">نوع ورودی (HTML Type) <span class="text-red-500">*</span></label>
                        <select id="field_type" name="field_type" class="{{ $selectClass }}" required>
                            @foreach([
                                'text' => 'متن کوتاه (Text)',
                                'number' => 'عدد (Number)',
                                'file' => 'تصویر یا مدرک (File)',
                                'textarea' => 'متن چند خطی / آدرس (Textarea)',
                                'date' => 'تاریخ (Date)',
                                'email' => 'ایمیل (Email)',
                                'select' => 'لیست کشویی (Select)',
                                'checkbox' => 'چک‌باکس (Checkbox)',
                                'radio' => 'گزینه رادیویی (Radio)',
                            ] as $type => $label)
                                <option value="{{ $type }}" @selected(old('field_type', 'text') === $type)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">نوع کنترلی که برای ورود داده در صفحه زیرمجموعه قرار می‌گیرد.</p>
                    </div>

                </div>

                {{-- تنظیمات پیشرفته (آکاردئون) --}}
                <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                    <button type="button" @click="showAdvanced = !showAdvanced"
                            class="w-full flex items-center justify-between p-6 outline-none group">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">تنظیمات پیشرفته (اختیاری)</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-transform duration-300"
                             :class="showAdvanced ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="showAdvanced" x-collapse x-cloak>
                        <div class="p-6 pt-0 border-t border-gray-100 dark:border-gray-700/50 mt-2 space-y-6">

                            <div>
                                <label class="{{ $labelClass }}">کلید سیستمی فیلد (field_name)</label>
                                <input type="text" name="field_name" value="{{ old('field_name') }}"
                                       class="{{ $inputClass }} dir-ltr text-left font-mono max-w-md"
                                       placeholder="مثلاً: clinic_license_number">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    در صورت خالی گذاشتن، کلید به صورت خودکار به زبان انگلیسی از روی عنوان تولید می‌شود.
                                </p>
                            </div>

                            <div>
                                <label class="inline-flex items-center gap-3 cursor-pointer group p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 transition-colors w-fit">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="is_required" value="1"
                                               @checked(old('is_required')) class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 cursor-pointer">
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        تکمیل این فیلد برای مدیر زیرمجموعه اجباری (Required) باشد
                                    </span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- دکمه ذخیره و انصراف --}}
            <div class="flex items-center justify-between sticky bottom-6 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.custom-fields.index', ['tab' => 'sub_account']) }}"
                   class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ایجاد فیلد احراز هویت زیرمجموعه</span>
                    <span x-show="isSubmitting">در حال ذخیره...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>

        </form>
    </div>
@endsection
