@extends('layouts.user')

@section('title', 'ایجاد اکانت هاستینگ جدید')

@php
    $customersListForJs = collect($customers ?? [])->map(function ($c) {
        return [
            'id'       => $c->id,
            'name'     => $c->full_name,
            'email'    => $c->email ?? '',
            'phone'    => $c->phone ?? '',
            'username' => $c->username ?? '',
        ];
    })->values();

    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="accountCreator()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </span>
                ایجاد اکانت هاستینگ جدید
            </h1>
            <a href="{{ route('user.directadmin.accounts.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div
                class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Flash Error Message --}}
        @if(session('error'))
            <div
                class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                <div>
                    <p class="font-black text-base">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Main Form --}}
        <form id="accountForm" method="POST" action="{{ route('user.directadmin.accounts.store') }}" class="space-y-8">
            @csrf

            {{-- Card 1: Server & Customer Information --}}
            <div class="{{ $cardClass }} overflow-visible relative z-30">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                            </svg>
                        </div>
                        اطلاعات سرور و مشتری
                    </h2>
                </div>

                <div class="p-6 space-y-8">
                    {{-- Row 1: Server & Package --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="{{ $labelClass }}">
                                انتخاب سرور دایرکت ادمین <span class="text-rose-500 font-black">*</span>
                            </label>
                            <div class="relative">
                                <select name="da_server_id" required
                                        x-model="serverId"
                                        @change="window.location = '{{ route('user.directadmin.accounts.create') }}?server_id=' + serverId"
                                        class="{{ $inputClass }} cursor-pointer">
                                    @foreach($servers as $srv)
                                        <option value="{{ $srv->id }}" @selected($srv->id === $server->id)>
                                            {{ $srv->name }} ({{ $srv->host }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">
                                پکیج میزبانی (Hosting Plan) <span class="text-rose-500 font-black">*</span>
                            </label>
                            <div class="relative">
                                <select name="package" required class="{{ $inputClass }} cursor-pointer">

                                    <option value="">انتخاب پکیج...</option>
                                    @forelse($packages as $pkg)
                                        <option
                                            value="{{ $pkg }}" @selected(old('package') == $pkg)>{{ $pkg }}</option>
                                    @empty
                                        <option value="Default" @selected(old('package') == 'Default')>Default
                                            (پیش‌فرض)
                                        </option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Customer selection (Type-to-Search, matching invoice create) --}}
                    <div class="pt-8 border-t border-dashed border-gray-200 dark:border-gray-700">
                        <label class="{{ $labelClass }}">
                            انتخاب مشتری <span class="text-xs font-normal text-gray-400 ms-1">(اختیاری - جهت اتصال اکانت به پرونده CRM)</span>
                        </label>
                        <input type="hidden" name="client_id" :value="selectedCustomer">

                        {{-- Search Input (Dropdown only opens when typing) --}}
                        <div x-show="!selectedCustomer" class="max-w-xl relative"
                             @click.outside="customerDropdownOpen = false">
                            <div class="relative">
                                <svg
                                    class="absolute start-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                </svg>
                                <input type="text" x-model="customerQuery"
                                       @input="customerDropdownOpen = customerQuery.trim().length > 0"
                                       class="{{ $inputClass }} ps-11 cursor-text focus:outline-none outline-none"
                                       :class="customerDropdownOpen && filteredCustomers.length > 0 ? 'rounded-b-none border-b-0' : ''"
                                       autocomplete="off"
                                       placeholder="جستجو با نام، ایمیل، موبایل یا نام کاربری مشتری...">
                            </div>

                            {{-- Results dropdown --}}
                            <div
                                x-show="customerDropdownOpen && customerQuery.trim().length > 0 && filteredCustomers.length > 0"
                                x-transition
                                class="absolute z-[100] w-full max-h-64 overflow-y-auto bg-white dark:bg-gray-900 border border-t-0 border-gray-200 dark:border-gray-700 rounded-xl rounded-t-none shadow-xl">
                                <template x-for="c in filteredCustomers" :key="c.id">
                                    <button type="button" @click="selectCustomer(c)"
                                            class="w-full text-start px-4 py-3 text-sm hover:bg-indigo-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-800 last:border-0 flex items-center gap-3">
                                        <span
                                            class="flex items-center justify-center w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 font-black text-xs shrink-0"
                                            x-text="(c.name || '؟').trim().charAt(0)"></span>
                                        <span class="min-w-0">
                                            <span class="font-bold text-gray-900 dark:text-white block truncate"
                                                  x-text="c.name"></span>
                                            <span class="text-xs text-gray-400 block truncate"
                                                  x-text="[c.phone, c.email].filter(Boolean).join(' • ')"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="customerDropdownOpen && customerQuery.trim().length > 0 && filteredCustomers.length === 0"
                               class="mt-2 text-xs text-gray-400 px-1">مشتری‌ای با این مشخصات یافت نشد.</p>
                        </div>

                        {{-- Selected Customer View --}}
                        <div x-show="selectedCustomer" x-cloak x-transition class="max-w-xl space-y-3">
                            <div
                                class="flex items-center gap-4 p-4 rounded-2xl border-2 border-indigo-200 dark:border-indigo-500/30 bg-indigo-50/60 dark:bg-indigo-500/10">
                                <span
                                    class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-600 text-white font-black text-base shrink-0"
                                    x-text="(selectedCustomerData?.name || '؟').trim().charAt(0)"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-gray-900 dark:text-white truncate"
                                       x-text="selectedCustomerData?.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                       x-text="[selectedCustomerData?.phone, selectedCustomerData?.email].filter(Boolean).join(' • ')"></p>
                                </div>
                                <button type="button" @click="clearCustomer()"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 text-xs font-bold text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 16M20 4L4 20"/>
                                    </svg>
                                    تغییر
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Account Credentials & Domain --}}
            <div class="{{ $cardClass }} overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        مشخصات حساب کاربری و دامنه هاست
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Domain --}}
                        <div>
                            <label class="{{ $labelClass }}">
                                دامنه اصلی هاست (Domain) <span class="text-rose-500 font-black">*</span>
                            </label>
                            <input type="text" name="domain" x-model="domain" value="{{ old('domain') }}" required
                                   class="{{ $inputClass }} text-start font-medium tabular-nums"
                                   placeholder="example.com" dir="ltr">
                            <p class="text-xs text-gray-400 mt-2 ms-1">آدرس دامنه بدون http:// یا www وارد شود.</p>
                        </div>

                        {{-- Username --}}
                        <div>
                            <label class="{{ $labelClass }}">
                                نام کاربری هاست (Username) <span class="text-rose-500 font-black">*</span>
                            </label>
                            <input type="text" name="username" value="{{ old('username') }}" required
                                   class="{{ $inputClass }} text-start font-medium tabular-nums"
                                   placeholder="clienthost" dir="ltr">
                            <p class="text-xs text-gray-400 mt-2 ms-1">حروف کوچک و اعداد انگلیسی مجاز است (۴ الی ۱۶
                                کاراکتر).</p>
                        </div>

                        {{-- Password with Generator & Visibility Toggle --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 ms-1">
                                    رمز عبور هاست <span class="text-rose-500 font-black">*</span>
                                </label>
                                <button type="button" @click="genPass()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-xs font-black text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all shadow-sm active:scale-95">
                                    <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                    </svg>
                                    <span>تولید رمز امن</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" x-model="pass" required
                                       class="{{ $inputClass }} text-start font-medium tabular-nums pr-11 pl-4"
                                       placeholder="••••••••" dir="ltr">
                                <button type="button" @click="showPass = !showPass"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1"
                                        title="نمایش / مخفی‌سازی رمز">
                                    <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 ms-1">حداقل ۶ کاراکتر شامل حروف و اعداد انگلیسی.</p>
                        </div>

                        {{-- Auto Email Display --}}
                        <div>
                            <label class="{{ $labelClass }}">ایمیل مدیریتی هاست</label>
                            <input type="text" :value="autoEmail" readonly
                                   class="{{ $inputClass }} text-start font-medium tabular-nums bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed shadow-inner dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0 focus:border-gray-300 dark:focus:border-gray-700"
                                   dir="ltr">
                            <p class="text-xs text-gray-400 mt-2 ms-1">تنظیم خودکار جهت دریافت گزارش‌ها و لاگ‌های
                                سرور.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Active Server Communication Specifications --}}
            <div class="{{ $cardClass }} relative overflow-hidden">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        مشخصات ارتباطی سرور فعال
                    </h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div
                            class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">نام سرور</span>
                            <span
                                class="text-sm font-black text-gray-900 dark:text-white block truncate">{{ $server->name }}</span>
                        </div>

                        <div
                            class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400 block">آدرس سرور (Host)</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white block truncate"
                                  dir="ltr">{{ $server->host }}</span>
                        </div>

                        <div
                            class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400 block">IP تخصیصی اکانت</span>
                            <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 block truncate"
                                  dir="ltr">{{ $serverIp ?? $server->host }}</span>
                        </div>

                        <div
                            class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-1">
                            <span
                                class="text-xs font-bold text-gray-500 dark:text-gray-400 block">وضعیت پروتکل و پورت</span>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-black {{ $server->ssl ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ $server->ssl ? 'HTTPS' : 'HTTP' }}
                                </span>
                                <span class="text-xs font-bold text-gray-400">| پورت {{ $server->port }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky Bottom Submit Bar (Exact Invoice Create Pattern) --}}
            <div class="sticky bottom-4 z-40">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-lg flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ایجاد اکانت در سرور
                    </button>
                    <a href="{{ route('user.directadmin.accounts.index') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function accountCreator() {
                return {
                    serverId: {{ $server->id }},
                    pass: '{{ old('password') }}',
                    showPass: false,
                    domain: '{{ old('domain') }}',
                    customers: @json($customersListForJs),
                    selectedCustomer: '{{ old('client_id') }}',
                    customerQuery: '',
                    customerDropdownOpen: false,

                    genPass() {
                        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
                        let res = '';
                        for (let i = 0; i < 16; i++) {
                            res += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        this.pass = res;
                    },

                    get autoEmail() {
                        if (!this.domain) return 'info@domain.com';
                        let clean = this.domain.replace(/^https?:\/\//i, '').replace(/^www\./i, '').trim();
                        return clean ? 'info@' + clean : 'info@domain.com';
                    },

                    get selectedCustomerData() {
                        return this.customers.find(c => c.id == this.selectedCustomer);
                    },

                    get filteredCustomers() {
                        if (!this.customerQuery || this.customerQuery.trim().length === 0) {
                            return [];
                        }
                        const q = this.customerQuery.toLowerCase().trim();
                        return this.customers.filter(c =>
                            (c.name && c.name.toLowerCase().includes(q)) ||
                            (c.email && c.email.toLowerCase().includes(q)) ||
                            (c.phone && c.phone.includes(q)) ||
                            (c.username && c.username.toLowerCase().includes(q))
                        ).slice(0, 20);
                    },

                    selectCustomer(c) {
                        this.selectedCustomer = c.id;
                        this.customerDropdownOpen = false;
                        this.customerQuery = '';
                    },

                    clearCustomer() {
                        this.selectedCustomer = '';
                        this.customerQuery = '';
                    }
                };
            }
        </script>
    @endpush
@endsection
