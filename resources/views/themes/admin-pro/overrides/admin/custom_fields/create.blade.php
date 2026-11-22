@extends('layouts.user')

@php
    $title = 'ایجاد فیلد سفارشی پرسنل و کاربران';
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs font-bold text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 sm:py-8 space-y-6 pb-16 md:pb-8"
         x-data="{
            isSubmitting: false,
            fieldType: '{{ old('field_type', 'text') }}',
            labelVal: '{{ old('label', '') }}',
            nameVal: '{{ old('field_name', '') }}',

            autoGenerateSlug() {
                if (!this.nameVal && this.labelVal) {
                    this.nameVal = this.labelVal
                        .trim()
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '_')
                        .replace(/^-+|-+$/g, '');
                    if (!this.nameVal) this.nameVal = 'field_' + Math.random().toString(36).substring(2, 7);
                }
            }
         }"
    >

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        ایجاد فیلد کاربران و پرسنل
                    </h1>
                </div>
            </div>

            <a href="{{ route('admin.custom-fields.index', ['tab' => 'user']) }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold rounded-2xl border border-gray-200/90 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 shadow-2xs transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست فیلدها
            </a>
        </div>

        @if($errors->any())
            <x-ui.alert variant="error" title="خطای اعتبارسنجی فیلد" :dismissible="true">
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.custom-fields.store') }}" @submit="isSubmitting = true"
              class="space-y-6">
            @csrf
            <input type="hidden" name="entity_type" value="user">

            <div class="{{ $cardClass }}">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        مشخصات اصلی فیلد کاربران
                    </h2>
                    <x-ui.badge variant="blue" size="sm">مرحله ۱</x-ui.badge>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نقش سازمانی مرتبط <span class="text-rose-500">*</span></label>
                        <div class="relative max-w-md">
                            <select name="role_name"
                                    class="{{ $selectClass }} !pr-4 !pl-10 font-bold text-indigo-700 dark:text-indigo-400"
                                    required>
                                <option value="" disabled selected>انتخاب نقش مرتبط...</option>
                                @foreach($roles as $k => $v)
                                    <option value="{{ $k }}" @selected(old('role_name') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">عنوان فیلد <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="label"
                            x-model="labelVal"
                            @blur="autoGenerateSlug()"
                            value="{{ old('label') }}"
                            class="{{ $inputClass }}"
                            placeholder="مثلاً: شماره نظام پزشکی، سابقه کار..."
                            required
                            autofocus
                        />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="{{ $labelClass }} !mb-0">شناسه سیستمی <span
                                    class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="nameVal = 'field_' + Math.random().toString(36).substring(2, 8)"
                                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 cursor-pointer"
                                >
                                    تولید رندوم
                                </button>
                                <button
                                    type="button"
                                    x-show="nameVal"
                                    @click="window.copyToClipboard(nameVal, 'شناسه فیلد در کلیپ‌بورد کپی شد')"
                                    class="text-[11px] font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white flex items-center gap-1 cursor-pointer transition-colors"
                                    title="کپی شناسه فیلد"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                    </svg>
                                    کپی
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <input
                                id="user_field_name_input"
                                type="text"
                                name="field_name"
                                x-model="nameVal"
                                value="{{ old('field_name') }}"
                                class="{{ $inputClass }} dir-ltr text-left !pl-10 font-bold"
                                placeholder="company_license_number"
                                required
                            />
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نوع ورودی <span class="text-rose-500">*</span></label>
                        <select name="field_type" x-model="fieldType" class="{{ $selectClass }}" required>
                            @foreach([
                                'text' => 'متن کوتاه (Text)',
                                'number' => 'عدد (Number)',
                                'file' => 'تصویر یا مدرک (File)',
                                'textarea' => 'متن چندخطی (Textarea)',
                                'date' => 'تاریخ (Date)',
                                'select' => 'منوی کشویی تک‌انتخابی (Select)',
                                'checkbox' => 'چک‌باکس چند انتخابی (Checkbox)',
                                'radio' => 'دکمه رادیویی تک‌انتخابی (Radio)',
                                'email' => 'پست الکترونیکی (Email)',
                                'tel' => 'تلفن / شماره تماس (Tel)',
                                'url' => 'آدرس اینترنتی (URL)',
                                'color' => 'انتخاب رنگ (Color)'
                            ] as $t => $label)
                                <option
                                    value="{{ $t }}" @selected(old('field_type', 'text') === $t)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input
                                type="checkbox"
                                name="is_required"
                                value="1"
                                @checked(old('is_required'))
                                class="w-4 h-4 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600"
                            />
                            <span
                                class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 transition-colors">
                                این فیلد اجباری است
                            </span>
                        </label>
                    </div>

                    <div class="md:col-span-2" x-show="['select', 'radio', 'checkbox'].includes(fieldType)" x-collapse>
                        <label class="{{ $labelClass }}">گزینه‌های انتخاب (هر گزینه در یک سطر یا با ویرگول
                            لاتین)</label>
                        <textarea
                            name="options"
                            rows="3"
                            class="{{ $inputClass }} resize-none leading-relaxed"
                            placeholder="گزینه ۱&#10;گزینه ۲&#10;گزینه ۳"
                        >{{ old('options') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">متن راهنما برای کاربر (اختیاری)</label>
                        <input
                            type="text"
                            name="help_text"
                            value="{{ old('help_text') }}"
                            class="{{ $inputClass }}"
                            placeholder="توضیح مختصری که زیر فیلد به کاربر نمایش داده می‌شود"
                        />
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between relative md:sticky md:bottom-6 z-30 mb-6 md:mb-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md p-4 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xl">
                <a href="{{ route('admin.custom-fields.index', ['tab' => 'user']) }}"
                   class="px-5 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>

                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
                >
                    <span x-show="isSubmitting"
                          class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ذخیره فیلد کاربران</span>
                    <span x-show="isSubmitting">در حال ذخیره...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        <x-ui.dock/>

        <x-ui.toast position="bottom-left"/>
    </div>
@endsection
