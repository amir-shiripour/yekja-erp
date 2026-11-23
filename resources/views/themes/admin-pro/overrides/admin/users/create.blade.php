{{-- resources/views/themes/admin-pro/overrides/admin/users/create.blade.php --}}
@extends('layouts.user')

@php
    $title = 'ایجاد کاربر جدید';
    $indexRoute = isset($indexRoute) ? $indexRoute : (Route::has('admin.users.index') ? 'admin.users.index' : 'user.users.index');
    $storeRoute = isset($storeRoute) ? $storeRoute : (Route::has('admin.users.store') ? 'admin.users.store' : 'user.users.store');

    $safeSelectedRole = old('role', $selectedRole ?? '');
    $grouped = $customFieldsByRole instanceof \Illuminate\Support\Collection
        ? ($customFieldsByRole->first() instanceof \App\Models\CustomUserField
            ? $customFieldsByRole->groupBy('role_name')
            : $customFieldsByRole)
        : collect();

    $inputTypes = ['text','number','email','date','datetime-local','time','month','week','url','tel','password','color','range','hidden'];
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden transition-all";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs font-bold text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 sm:py-8 space-y-6 pb-16 md:pb-8"
         x-data="{
            role: '{{ $safeSelectedRole }}',
            isSubmitting: false,
            nameVal: '{{ old('name', '') }}',
            emailVal: '{{ old('email', '') }}',

            generateEmail() {
                const rand = Math.random().toString(36).substring(2, 7);
                const prefix = this.nameVal ? this.nameVal.trim().toLowerCase().replace(/\s+/g, '.') : 'user';
                this.emailVal = prefix + '.' + rand + '@example.com';
                const el = document.getElementById('admin_user_email');
                if (el) {
                    el.value = this.emailVal;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (window.toast) window.toast.success('آدرس ایمیل نمونه تولید شد.');
            },

            copyEmail() {
                if (!this.emailVal) return;
                window.copyToClipboard(this.emailVal, 'آدرس ایمیل در کلیپ‌بورد کپی شد.');
            }
         }"
    >

        {{-- هدر صفحه با تم اختصاصی Admin Pro --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        ایجاد کاربر جدید
                    </h1>
                    <x-ui.badge variant="indigo" size="sm" dot>کاربر سامانه</x-ui.badge>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mr-13 leading-relaxed">
                    ثبت حساب کاربری جدید، تنظیم مشخصات هویتی و تماس، تعیین رمز عبور و تخصیص نقش سازمانی.
                </p>
            </div>

            @if(Route::has($indexRoute))
                <a href="{{ route($indexRoute) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold rounded-2xl border border-gray-200/90 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 shadow-2xs transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    بازگشت به لیست کاربران
                </a>
            @endif
        </div>

        {{-- نمایش خطاهای اعتبارسنجی فقط با الرت وایب‌فارسی (بدون توست همزمان) --}}
        @if($errors->any())
            <x-ui.alert variant="error" title="خطای اعتبارسنجی اطلاعات فرم" :dismissible="true">
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-6">
            @csrf

            {{-- بخش ۱: اطلاعات فردی و تماس --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        مشخصات هویتی و اطلاعات تماس
                    </h2>
                    <x-ui.badge variant="blue" size="sm">مرحله ۱</x-ui.badge>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- نام و نام خانوادگی --}}
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            name="name"
                            x-model="nameVal"
                            class="{{ $inputClass }}"
                            required
                            placeholder="مثلاً: علی احمدی"
                        />
                    </div>

                    {{-- ایمیل سازمانی با دکمه تولید خودکار و کپی --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="{{ $labelClass }} !mb-0">پست الکترونیکی <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="generateEmail()" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center gap-1 transition-colors cursor-pointer" title="تولید خودکار آدرس ایمیل">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    تولید خودکار
                                </button>
                                <button type="button" x-show="emailVal" @click="copyEmail()" class="text-[11px] font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white flex items-center gap-1 transition-colors cursor-pointer" title="کپی ایمیل">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    کپی
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <input
                                id="admin_user_email"
                                type="email"
                                name="email"
                                x-model="emailVal"
                                class="{{ $inputClass }} dir-ltr text-left !pl-11"
                                required
                                placeholder="user@domain.com"
                            />
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- شماره موبایل با کامپوننت وایب‌فارسی --}}
                    <div>
                        <x-ui.phone-input
                            label="شماره موبایل"
                            name="mobile"
                            :value="old('mobile', '')"
                            placeholder="912 345 6789"
                        />
                    </div>

                    {{-- کد ملی کاربر با کامپوننت هوشمند National ID Input --}}
                    <div class="md:col-span-2">
                        <x-ui.national-id-input
                            label="کد ملی کاربر"
                            name="national_id"
                            :value="old('national_id', '')"
                            placeholder="کد ملی ۱۰ رقمی"
                        />
                    </div>
                </div>
            </div>

            {{-- بخش ۲: امنیت و کلمه عبور --}}
            <div class="{{ $cardClass }}"
                 x-data="{
                    generatedPass: '',
                    genPass() {
                        const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
                        let pass = '';
                        for (let i = 0; i < 12; i++) {
                            pass += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        this.generatedPass = pass;
                        const p1 = document.getElementById('admin_create_pass');
                        const p2 = document.getElementById('admin_create_pass_confirm');
                        if (p1) {
                            p1.value = pass;
                            p1.dispatchEvent(new Event('input', { bubbles: true }));
                            p1.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        if (p2) {
                            p2.value = pass;
                            p2.dispatchEvent(new Event('input', { bubbles: true }));
                            p2.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        if (window.toast) window.toast.success('رمز عبور ایمن تولید و در هر دو فیلد درج شد.');
                    },
                    copyPass() {
                        if (!this.generatedPass) return;
                        window.copyToClipboard(this.generatedPass, 'کلمه عبور در کلیپ‌بورد کپی شد.');
                    }
                 }"
            >
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        امنیت حساب و کلمه عبور
                    </h2>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="genPass()" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center gap-1 transition-colors cursor-pointer" title="تولید خودکار رمز عبور">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            تولید رمز قوی
                        </button>
                        <button type="button" x-show="generatedPass" @click="copyPass()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 flex items-center gap-1 transition-colors cursor-pointer" title="کپی رمز عبور">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            کپی رمز
                        </button>
                        <x-ui.badge variant="rose" size="sm">مرحله ۲</x-ui.badge>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-ui.password-input
                            name="password"
                            id="admin_create_pass"
                            label="رمز عبور *"
                            :show-strength="true"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :required="true"
                        />
                    </div>

                    <div>
                        <x-ui.password-input
                            name="password_confirmation"
                            id="admin_create_pass_confirm"
                            label="تأیید رمز عبور *"
                            :show-strength="false"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            :required="true"
                        />
                    </div>
                </div>
            </div>

            {{-- بخش ۳: نقش و سطوح دسترسی --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        نقش سازمانی و فیلدهای اختصاصی
                    </h2>
                    <x-ui.badge variant="emerald" size="sm">مرحله ۳</x-ui.badge>
                </div>

                <div class="p-6 space-y-6">
                    {{-- انتخاب نقش --}}
                    <div class="max-w-md">
                        <label class="{{ $labelClass }}">نقش کاربر سیستم <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select
                                name="role"
                                x-model="role"
                                class="{{ $inputClass }} appearance-none cursor-pointer font-bold text-indigo-700 dark:text-indigo-400 !pr-4 !pl-10"
                                required
                            >
                                <option value="" disabled>انتخاب نقش سازمانی...</option>
                                @foreach($roles as $k => $v)
                                    @php
                                        $optionValue = is_object($v) ? ($v->name ?? $k) : $k;
                                        $optionLabel = is_object($v) ? ($v->display_name ?? $v->name ?? $k) : $v;
                                    @endphp
                                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- فیلدهای سفارشی داینامیک بر اساس نقش انتخابی --}}
                    @if($grouped->isNotEmpty())
                        <div class="space-y-6 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            @foreach($grouped as $roleName => $fields)
                                <div x-show="role === '{{ $roleName }}'" x-collapse x-cloak class="space-y-4 bg-gray-50/70 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700/80">
                                    <div class="flex items-center gap-2 border-b border-gray-200/80 dark:border-gray-700/80 pb-3">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <h3 class="text-xs font-black text-gray-800 dark:text-gray-200">
                                            فیلدهای اختصاصی نقش: {{ $roleName }}
                                        </h3>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($fields as $field)
                                            @php
                                                $type = strtolower($field->field_type ?? 'text');
                                                $valueOld = old('custom.' . $field->field_name);
                                                $isFullWidth = in_array($type, ['textarea']);
                                            @endphp
                                            <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                                <label class="{{ $labelClass }}">
                                                    {{ $field->label }}
                                                    @if($field->is_required)
                                                        <span class="text-rose-500">*</span>
                                                    @endif
                                                </label>

                                                @if($type === 'textarea')
                                                    <textarea
                                                        name="custom[{{ $field->field_name }}]"
                                                        rows="3"
                                                        class="{{ $inputClass }} resize-none leading-relaxed"
                                                        @if($field->is_required) required @endif
                                                    >{{ $valueOld }}</textarea>

                                                @elseif($type === 'file')
                                                    <x-ui.file-upload
                                                        name="custom[{{ $field->field_name }}]"
                                                        label="انتخاب فایل مدرک یا تصویر"
                                                    />

                                                @elseif(in_array($type, $inputTypes, true))
                                                    <input
                                                        type="{{ $type }}"
                                                        name="custom[{{ $field->field_name }}]"
                                                        value="{{ $valueOld }}"
                                                        class="{{ $inputClass }} {{ in_array($type, ['number', 'email', 'url', 'tel', 'color']) ? 'dir-ltr text-left' : '' }}"
                                                        @if($field->is_required) required @endif
                                                    />

                                                @else
                                                    <input
                                                        type="text"
                                                        name="custom[{{ $field->field_name }}]"
                                                        value="{{ $valueOld }}"
                                                        class="{{ $inputClass }}"
                                                        @if($field->is_required) required @endif
                                                    />
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- نوار ثبت فرم (کاملاً بهینه‌سازی‌شده برای عدم تداخل با داک موبایل) --}}
            <div class="flex items-center justify-between relative md:sticky md:bottom-6 z-30 mb-6 md:mb-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md p-4 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xl">
                @if(Route::has($indexRoute))
                    <a href="{{ route($indexRoute) }}"
                       class="px-5 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                        انصراف
                    </a>
                @else
                    <div></div>
                @endif

                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
                >
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ثبت و ایجاد حساب کاربر</span>
                    <span x-show="isSubmitting">در حال ثبت...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        </form>

        {{-- فاصله تضمین‌شده انتهای فرم در موبایل برای جلوگیری از هرگونه تداخل با داک شناور --}}
        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        {{-- سیستم نمایش اعلان‌ها و توست‌ها --}}
        <x-ui.toast position="bottom-left" />
    </div>
@endsection
