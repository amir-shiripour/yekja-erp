@extends('layouts.user')

@php
    $isEdit = $user && $user->exists;
    $indexRoute = $indexRoute ?? (request()->routeIs('user.users.*') ? 'user.users.index' : 'admin.users.index');
    $storeRoute = $storeRoute ?? (request()->routeIs('user.users.*') ? 'user.users.store' : 'admin.users.store');
    $updateRoute = $updateRoute ?? (request()->routeIs('user.users.*') ? 'user.users.update' : 'admin.users.update');
    $destroyRoute = $destroyRoute ?? (request()->routeIs('user.users.*') ? 'user.users.destroy' : 'admin.users.destroy');
    $title = $pageTitle ?? ($isEdit ? 'ویرایش کاربر: ' . $user->name : 'ایجاد کاربر جدید');

    $selectedRolesOld = old('roles');
    $selectedRoles = is_array($selectedRolesOld) ? $selectedRolesOld : (isset($selectedRoles) ? (is_array($selectedRoles) ? $selectedRoles : [$selectedRoles]) : (optional($user)->roles ? $user->roles->pluck('name')->toArray() : []));

    $selectedSuperiorsOld = old('superior_ids');
    $selectedSuperiors = is_array($selectedSuperiorsOld) ? $selectedSuperiorsOld : (optional($user)->superiors ? $user->superiors->pluck('id')->toArray() : []);

    $customValues = collect(optional($user)->customValues ?? [])->keyBy('field_name');

    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs transition-all duration-200 relative";
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";

    $roleOptionsForSelect = [];
    foreach($roles as $k => $v) {
        $roleOptionsForSelect[] = [
            'value' => is_object($v) ? ($v->name ?? $k) : $k,
            'label' => is_object($v) ? ($v->display_name ?? $v->name ?? $k) : $v
        ];
    }

    $userOptionsForSelect = [];
    if(isset($allUsers)) {
        foreach($allUsers as $u) {
            $userOptionsForSelect[] = [
                'value' => $u->id,
                'label' => $u->name
            ];
        }
    }
@endphp

@section('content')
    <script>
        function setupRoleMultiSelect(config) {
            return {
                name: config.name,
                options: config.options || [],
                selectedValues: [],
                search: '',
                open: false,
                position: 'bottom',

                init() {
                    const oldValues = Array.isArray(config.oldValues) ? config.oldValues : [];
                    this.selectedValues = oldValues.map(String);
                },

                toggleDropdown() {
                    if (this.open) {
                        this.open = false;
                        return;
                    }
                    this.open = true;
                    this.$nextTick(() => {
                        const rect = this.$el.getBoundingClientRect();
                        const dropdownHeight = 250;
                        const spaceBelow = window.innerHeight - rect.bottom;
                        const spaceAbove = rect.top;
                        if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
                            this.position = 'top';
                        } else {
                            this.position = 'bottom';
                        }
                        this.$refs.roleSearchInput.focus();
                    });
                },

                isSelected(value) {
                    value = String(value);
                    return this.selectedValues.includes(value);
                },

                toggle(value) {
                    value = String(value);
                    if (this.isSelected(value)) {
                        this.selectedValues = this.selectedValues.filter(v => v !== value);
                    } else {
                        this.selectedValues.push(value);
                    }
                },

                clearValue(value) {
                    value = String(value);
                    this.selectedValues = this.selectedValues.filter(v => v !== value);
                },

                filteredOptions() {
                    const term = (this.search || '').toLowerCase();
                    return (this.options || []).filter(o => (o.label || '').toLowerCase().includes(term));
                }
            };
        }
    </script>

    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6 pb-16 md:pb-8" x-data="{ isSubmitting: false }">

        {{-- هدر صفحه با استایل مدرن Admin Pro --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-gradient-to-r from-white via-indigo-50/30 to-white dark:from-gray-800 dark:via-indigo-950/20 dark:to-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-2xl {{ $isEdit ? 'bg-amber-500 shadow-amber-500/30' : 'bg-indigo-600 shadow-indigo-600/30' }} text-white shadow-md">
                        @if($isEdit)
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        @endif
                    </span>
                    <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ $pageTitle ?? ($isEdit ? 'ویرایش کاربر: ' . $user->name : 'ایجاد کاربر جدید') }}
                    </h1>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mr-13 leading-relaxed">
                    {{ $isEdit ? 'به‌روزرسانی اطلاعات حساب کاربری، بازنشانی رمز عبور و تنظیم نقش‌ها و دسترسی‌های سیستمی.' : 'ثبت حساب کاربری جدید، تنظیم مشخصات ورود، نقش‌ها و روابط سازمانی.' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route($indexRoute) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    بازگشت به لیست
                </a>

                @if($isEdit)
                    @can('users.delete')
                        @if(auth()->id() !== $user->id)
                            <form method="POST" action="{{ route($destroyRoute, $user) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <x-ui.delete-button
                                    type="submit"
                                    label="حذف حساب"
                                    confirm-label="تایید حذف نهایی؟"
                                    size="md"
                                />
                            </form>
                        @endif
                    @endcan
                @endif
            </div>
        </div>

        {{-- هشدارهای صفحه با کامپوننت اصیل وایب‌فارسی --}}
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

        {{-- نمایش خطاها با کامپوننت Alert --}}
        @if($errors->any())
            <x-ui.alert variant="error" title="خطا در اعتبارسنجی اطلاعات فرم" :dismissible="true">
                <ul class="list-disc list-inside space-y-1 mt-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ $isEdit ? route($updateRoute, $user) : route($storeRoute) }}" enctype="multipart/form-data" autocomplete="off" @submit="isSubmitting = true" class="space-y-6 pb-12">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- بخش اول: اطلاعات فردی --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        اطلاعات فردی و شناسایی
                    </h2>
                    <x-ui.badge variant="indigo" size="sm">مرحله ۱</x-ui.badge>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="{{ $inputClass }}" required placeholder="مثلاً: علی احمدی">
                    </div>

                    <div x-data="{
                        email: '{{ old('email', $user->email ?? '') }}',
                        init() {
                            @if(!$isEdit && !old('email'))
                                this.generateEmail();
                            @endif
                        },
                        generateEmail() {
                            let host = window.location.hostname;
                            let domain = host.startsWith('panel.') ? host.substring(6) : host;
                            if (domain === 'localhost' || domain === '127.0.0.1' || !domain) {
                                domain = 'domain.com';
                            }
                            let randomString = Math.random().toString(36).substring(2, 8);
                            this.email = 'user_' + randomString + '@' + domain;
                            window.toast?.success('آدرس ایمیل تصادفی تولید شد.');
                        },
                        copyEmail() {
                            if (!this.email) return;
                            window.copyToClipboard(this.email, 'آدرس ایمیل در کلیپ‌بورد کپی شد.');
                        }
                    }">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">ایمیل سازمانی <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="generateEmail()" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors cursor-pointer" title="تولید خودکار آدرس ایمیل">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    تولید خودکار
                                </button>
                                <button type="button" x-show="email" @click="copyEmail()" class="text-[11px] font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white flex items-center gap-1 transition-colors cursor-pointer" title="کپی ایمیل">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    کپی
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <input type="email" name="email" x-model="email" class="{{ $inputClass }} dir-ltr text-left !pl-11" required placeholder="user@domain.com">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-ui.phone-input
                            label="شماره موبایل"
                            name="mobile"
                            :value="old('mobile', $user->mobile ?? '')"
                            placeholder="912 345 6789"
                        />
                    </div>

                    {{-- کد ملی با کامپوننت هوشمند National ID Input --}}
                    <div>
                        <x-ui.national-id-input
                            label="کد ملی کاربر"
                            name="national_id"
                            :value="old('national_id', $user->national_id ?? '')"
                            placeholder="کد ملی ۱۰ رقمی"
                        />
                    </div>
                </div>
            </div>

            {{-- بخش دوم: امنیت و کلمه عبور با کامپوننت Password Input --}}
            <div class="{{ $cardClass }}" x-data="{
                generatedPass: '',
                genPass() {
                    const length = 12;
                    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
                    let retVal = '';
                    for (let i = 0, n = charset.length; i < length; ++i) {
                        retVal += charset.charAt(Math.floor(Math.random() * n));
                    }
                    this.generatedPass = retVal;
                    
                    window.dispatchEvent(new CustomEvent('set-password', { detail: { value: retVal } }));

                    const passInput = document.getElementById('admin_user_pass');
                    const confInput = document.getElementById('admin_user_pass_conf');
                    if (passInput) {
                        passInput.value = retVal;
                        passInput.dispatchEvent(new Event('input', { bubbles: true }));
                        passInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (confInput) {
                        confInput.value = retVal;
                        confInput.dispatchEvent(new Event('input', { bubbles: true }));
                        confInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    window.toast?.success('رمز عبور قوی تولید و در فیلدها درج گردید.');
                },
                copyPass() {
                    if (!this.generatedPass) return;
                    window.copyToClipboard(this.generatedPass, 'کلمه عبور در کلیپ‌بورد کپی شد.');
                }
            }">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        تنظیمات امنیتی و رمز عبور
                    </h2>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="genPass()" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors cursor-pointer" title="تولید خودکار کلمه عبور ایمن">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            تولید رمز قوی
                        </button>
                        <button type="button" x-show="generatedPass" @click="copyPass()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 flex items-center gap-1 transition-colors cursor-pointer" title="کپی رمز تولید شده">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            کپی رمز
                        </button>
                        <x-ui.badge variant="rose" size="sm">مرحله ۲</x-ui.badge>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    @if($isEdit)
                        <x-ui.alert type="info">
                            فقط در صورتی که تمایل به تغییر رمز عبور کاربر دارید این فیلدها را پر کنید؛ در غیر این صورت رمز عبور فعلی محفوظ می‌ماند.
                        </x-ui.alert>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-ui.password-input
                                name="password"
                                id="admin_user_pass"
                                label="رمز عبور {{ !$isEdit ? '*' : '' }}"
                                :show-strength="true"
                                placeholder="••••••••"
                                autocomplete="new-password"
                                :required="!$isEdit"
                            />
                        </div>

                        <div>
                            <x-ui.password-input
                                name="password_confirmation"
                                id="admin_user_pass_conf"
                                label="تأیید رمز عبور {{ !$isEdit ? '*' : '' }}"
                                :show-strength="false"
                                placeholder="••••••••"
                                autocomplete="new-password"
                                :required="!$isEdit"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- بخش سوم: نقش‌ها و دسترسی‌ها --}}
            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        نقش‌ها و اختیارات کاربری
                    </h2>
                    <x-ui.badge variant="emerald" size="sm">مرحله ۳</x-ui.badge>
                </div>
                <div class="p-6 space-y-6">

                    {{-- انتخاب نقش --}}
                    <div class="max-w-full" x-data="setupRoleMultiSelect({
                        name: 'roles',
                        options: {{ json_encode($roleOptionsForSelect, JSON_UNESCAPED_UNICODE) }},
                        oldValues: {{ json_encode($selectedRoles, JSON_UNESCAPED_UNICODE) }}
                    })" @click.away="open = false">
                        <label class="{{ $labelClass }}">انتخاب نقش‌های کاربر در سیستم <span class="text-rose-500">*</span></label>

                        <div class="relative group mt-2" :class="open ? 'z-50' : 'z-20'">
                            <div
                                class="flex flex-wrap gap-1.5 min-h-[46px] items-center rounded-2xl border border-gray-200 bg-gray-50/80 px-3 py-2 transition-all duration-200
                                       focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:bg-white
                                       dark:bg-gray-900/80 dark:border-gray-700 dark:focus-within:bg-gray-800 cursor-text shadow-xs"
                                @click="toggleDropdown()"
                            >
                                <template x-if="selectedValues.length === 0">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 px-1 select-none pointer-events-none">
                                        برای انتخاب نقش کلیک کنید...
                                    </span>
                                </template>

                                <template x-for="value in selectedValues" :key="value">
                                    <div class="inline-flex items-center gap-1.5 pl-2.5 pr-2 py-0.5 text-xs font-bold rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/60">
                                        <span x-text="options.find(o => String(o.value) === String(value))?.label ?? value"></span>
                                        <button type="button" class="p-0.5 rounded-full hover:bg-indigo-200/50 dark:hover:bg-indigo-800/50 text-indigo-600 dark:text-indigo-400 transition-colors" @click.stop="clearValue(value)">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                </template>

                                <input
                                    x-ref="roleSearchInput"
                                    type="text"
                                    x-model="search"
                                    class="flex-1 min-w-[80px] border-0 bg-transparent p-0 text-xs text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600"
                                    placeholder=""
                                    @keydown.backspace="if(search === '' && selectedValues.length > 0) selectedValues.pop()"
                                >

                                <div class="absolute left-3 text-gray-400 pointer-events-none">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            <div
                                x-ref="dropdown"
                                x-show="open"
                                x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                                x-cloak
                                class="absolute z-50 w-full overflow-hidden rounded-2xl border border-gray-200/90 bg-white/95 backdrop-blur-xl shadow-2xl dark:border-gray-700/80 dark:bg-gray-800/95"
                                :class="{ 'bottom-full mb-2': position === 'top', 'top-full mt-2': position === 'bottom' }"
                                style="box-shadow: 0 16px 36px -4px rgba(0,0,0,0.18);"
                            >
                                <div class="max-h-60 overflow-y-auto py-1 scrollbar-thin">
                                    <template x-for="opt in filteredOptions()" :key="opt.value">
                                        <button
                                            type="button"
                                            class="relative w-full cursor-pointer select-none py-2.5 pl-3 pr-9 text-right text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="isSelected(opt.value) ? 'font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' : 'text-gray-700 dark:text-gray-200'"
                                            @click.prevent="toggle(opt.value); $refs.roleSearchInput.focus()"
                                        >
                                            <span x-text="opt.label"></span>
                                            <span x-show="isSelected(opt.value)" class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600 dark:text-indigo-400">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>
                                    </template>
                                    <template x-if="filteredOptions().length === 0">
                                        <div class="px-4 py-3 text-xs text-gray-500 text-center italic">نقشی یافت نشد.</div>
                                    </template>
                                </div>
                            </div>

                            <template x-for="value in selectedValues" :key="'hidden-roles-' + value">
                                <input type="hidden" name="roles[]" :value="value">
                            </template>
                            <template x-if="selectedValues.length === 0">
                                <input type="hidden" name="roles[]" value="">
                            </template>
                        </div>

                        {{-- فیلدهای سفارشی وابسته به نقش --}}
                        <div class="space-y-6 mt-6">
                            @isset($customFieldsByRole)
                                @foreach($customFieldsByRole as $roleName => $fields)
                                    <div x-show="selectedValues.includes('{{ $roleName }}')" x-collapse x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/70 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/60 dark:border-gray-700/50">
                                        <div class="md:col-span-2">
                                            <h3 class="text-xs font-black text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">
                                                فیلدهای اختصاصی نقش: {{ $roles[$roleName]->display_name ?? $roles[$roleName] ?? $roleName }}
                                            </h3>
                                        </div>

                                        @foreach($fields as $field)
                                            @php
                                                $type = strtolower($field->field_type ?? 'text');
                                                $cv = $customValues->get($field->field_name);
                                                $existing = $cv ? $cv->value : '';
                                                $value = old('custom.' . $field->field_name, $existing);
                                                $isFullWidth = in_array($type, ['textarea']);
                                                $checkedVals = [];
                                                if ($type === 'checkbox') {
                                                    $oldArr = old('custom.' . $field->field_name);
                                                    if (is_array($oldArr)) {
                                                        $checkedVals = $oldArr;
                                                    } else {
                                                        $checkedVals = is_string($existing) ? (json_decode($existing, true) ?: []) : (is_array($existing) ? $existing : []);
                                                    }
                                                }
                                                $opts = $field->meta['options'] ?? null;
                                                $isLtrField = in_array($type, ['number', 'email', 'url', 'tel', 'color']);
                                            @endphp

                                            <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                                <label class="{{ $labelClass }}">{{ $field->label }}</label>

                                                @if($type === 'textarea')
                                                    <textarea name="custom[{{ $field->field_name }}]" rows="3" class="{{ $inputClass }} resize-none leading-relaxed">{{ $value }}</textarea>
                                                @elseif($type === 'file')
                                                    <x-ui.file-upload
                                                        name="custom[{{ $field->field_name }}]"
                                                        hint="انتخاب فایل پیوست برای {{ $field->label }}"
                                                    />
                                                @elseif($type === 'color')
                                                    <x-ui.color-picker
                                                        name="custom[{{ $field->field_name }}]"
                                                        :value="$value ?: '#4f46e5'"
                                                    />
                                                @elseif($type === 'price' || str_contains($field->field_name, 'price') || str_contains($field->field_name, 'amount'))
                                                    <x-ui.price-input
                                                        name="custom[{{ $field->field_name }}]"
                                                        :value="$value"
                                                    />
                                                @elseif($type === 'select' && is_array($opts))
                                                    <div class="relative">
                                                        <select name="custom[{{ $field->field_name }}]" class="{{ $inputClass }} appearance-none cursor-pointer">
                                                            @foreach($opts as $opt)
                                                                <option value="{{ $opt }}" {{ (string)$value === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @else
                                                    <input type="{{ $type }}" name="custom[{{ $field->field_name }}]" value="{{ $value }}" class="{{ $inputClass }} {{ $isLtrField ? 'dir-ltr text-left' : '' }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endisset
                        </div>
                    </div>

                    <div class="border-t border-gray-200/80 dark:border-gray-700/80 my-6"></div>

                    {{-- اتصال به سایر کاربران --}}
                    <div class="max-w-full" x-data="setupRoleMultiSelect({
                        name: 'superiors',
                        options: {{ json_encode($userOptionsForSelect, JSON_UNESCAPED_UNICODE) }},
                        oldValues: {{ json_encode($selectedSuperiors, JSON_UNESCAPED_UNICODE) }}
                    })" @click.away="open = false">
                        <label class="{{ $labelClass }}">اتصال به سایر کاربران (سرپرست / پزشک / ارجاع‌دهنده)</label>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-2">تعیین ناظر، پزشک معالج یا سرپرست مستقیم این کاربر در چارت سازمانی.</p>

                        <div class="relative group mt-2" :class="open ? 'z-50' : 'z-20'">
                            <div
                                class="flex flex-wrap gap-1.5 min-h-[46px] items-center rounded-2xl border border-gray-200 bg-gray-50/80 px-3 py-2 transition-all duration-200
                                       focus-within:border-cyan-500 focus-within:ring-2 focus-within:ring-cyan-500/20 focus-within:bg-white
                                       dark:bg-gray-900/80 dark:border-gray-700 dark:focus-within:bg-gray-800 cursor-text shadow-xs"
                                @click="toggleDropdown()"
                            >
                                <template x-if="selectedValues.length === 0">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 px-1 select-none pointer-events-none">
                                        انتخاب کاربران سرپرست...
                                    </span>
                                </template>

                                <template x-for="value in selectedValues" :key="value">
                                    <div class="inline-flex items-center gap-1.5 pl-2.5 pr-2 py-0.5 text-xs font-bold rounded-xl bg-cyan-50 text-cyan-700 border border-cyan-200/80 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800/60">
                                        <span x-text="options.find(o => String(o.value) === String(value))?.label ?? value"></span>
                                        <button type="button" class="p-0.5 rounded-full hover:bg-cyan-200/50 dark:hover:bg-cyan-800/50 text-cyan-600 dark:text-cyan-400 transition-colors" @click.stop="clearValue(value)">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                </template>

                                <input
                                    x-ref="roleSearchInput"
                                    type="text"
                                    x-model="search"
                                    class="flex-1 min-w-[80px] border-0 bg-transparent p-0 text-xs text-gray-900 focus:ring-0 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600"
                                    placeholder=""
                                    @keydown.backspace="if(search === '' && selectedValues.length > 0) selectedValues.pop()"
                                >

                                <div class="absolute left-3 text-gray-400 pointer-events-none">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            <div
                                x-ref="dropdown"
                                x-show="open"
                                x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                                x-cloak
                                class="absolute z-50 w-full overflow-hidden rounded-2xl border border-gray-200/90 bg-white/95 backdrop-blur-xl shadow-2xl dark:border-gray-700/80 dark:bg-gray-800/95"
                                :class="{ 'bottom-full mb-2': position === 'top', 'top-full mt-2': position === 'bottom' }"
                                style="box-shadow: 0 16px 36px -4px rgba(0,0,0,0.18);"
                            >
                                <div class="max-h-60 overflow-y-auto py-1 scrollbar-thin">
                                    <template x-for="opt in filteredOptions()" :key="opt.value">
                                        <button
                                            type="button"
                                            class="relative w-full cursor-pointer select-none py-2.5 pl-3 pr-9 text-right text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            :class="isSelected(opt.value) ? 'font-black text-cyan-600 dark:text-cyan-400 bg-cyan-50/50 dark:bg-cyan-950/30' : 'text-gray-700 dark:text-gray-200'"
                                            @click.prevent="toggle(opt.value); $refs.roleSearchInput.focus()"
                                        >
                                            <span x-text="opt.label"></span>
                                            <span x-show="isSelected(opt.value)" class="absolute inset-y-0 left-0 flex items-center pl-3 text-cyan-600 dark:text-cyan-400">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <template x-for="value in selectedValues" :key="'hidden-superiors-' + value">
                                <input type="hidden" name="superior_ids[]" :value="value">
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            {{-- نوار ثبت فرم (بهینه‌سازی‌شده برای عدم تداخل با داک در موبایل) --}}
            <div class="flex items-center justify-between relative md:sticky md:bottom-6 z-30 mb-6 md:mb-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md p-4 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xl">
                <a href="{{ route($indexRoute) }}" class="px-5 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>

                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">{{ $isEdit ? 'ذخیره تغییرات کاربر' : 'ثبت نهایی کاربر جدید' }}</span>
                    <span x-show="isSubmitting">در حال پردازش...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>

        </form>

        {{-- فاصله تضمین‌شده انتهای فرم در موبایل برای جلوگیری از هرگونه تداخل با داک شناور --}}
        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        {{-- کانتینر اعلان‌ها و توست‌های سیستمی --}}
        <x-ui.toast position="bottom-left" />
    </div>
@endsection
