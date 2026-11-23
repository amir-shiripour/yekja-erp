@extends('layouts.user')

@php
    $title = 'ویرایش فیلد کاربران: ' . $field->label;
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden transition-all";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs font-bold text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 sm:py-8 space-y-6 pb-16 md:pb-8"
         x-data="{
            isSubmitting: false,
            fieldType: '{{ old('field_type', $field->field_type) }}'
         }"
    >

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        ویرایش فیلد: {{ $field->label }}
                    </h1>
                    <x-ui.badge variant="amber" size="sm" dot>ویرایش تنظیمات</x-ui.badge>
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

        <form method="POST" action="{{ route('admin.custom-fields.update', $field) }}" @submit="isSubmitting = true"
              class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="entity_type" value="user">

            <div class="{{ $cardClass }}">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        مشخصات فیلد کاربران
                    </h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 font-bold dir-ltr">#{{ $field->field_name }}</span>
                        <x-ui.copy-button :text="$field->field_name" size="sm" title="کپی شناسه"/>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نقش مرتبط <span class="text-rose-500">*</span></label>
                        <div class="relative max-w-md">
                            <select name="role_name"
                                    class="{{ $selectClass }} !pr-4 !pl-10 font-bold text-indigo-700 dark:text-indigo-400"
                                    required>
                                @foreach($roles as $k => $v)
                                    <option
                                        value="{{ $k }}" @selected(old('role_name', $field->role_name) === $k)>{{ $v }}</option>
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
                        <label class="{{ $labelClass }}">عنوان فیلد (Label) <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="label"
                            value="{{ old('label', $field->label) }}"
                            class="{{ $inputClass }}"
                            required
                        />
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">نوع ورودی (Input Type) <span
                                class="text-rose-500">*</span></label>
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
                                    value="{{ $t }}" @selected(old('field_type', $field->field_type) === $t)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input
                                type="checkbox"
                                name="is_required"
                                value="1"
                                @checked(old('is_required', $field->is_required))
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
                        >{{ old('options', is_array($field->options) ? implode("\n", $field->options) : $field->options) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">متن راهنما برای کاربر (اختیاری)</label>
                        <input
                            type="text"
                            name="help_text"
                            value="{{ old('help_text', $field->help_text ?? '') }}"
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
                    <span x-show="!isSubmitting">ذخیره تغییرات فیلد</span>
                    <span x-show="isSubmitting">در حال ذخیره...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        <x-ui.toast position="bottom-left"/>
    </div>
@endsection
