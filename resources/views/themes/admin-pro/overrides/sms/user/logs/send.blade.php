@extends('layouts.user')

@php
    $users            = $users ?? collect();
    $roles            = $roles ?? collect();
    $clients          = $clients ?? collect();
    $clientStatuses   = $clientStatuses ?? collect();
    $canTargetUsers   = $canTargetUsers ?? true;
    $canTargetClients = $canTargetClients ?? false;

    $userSelectOptions = $users->map(function ($u) {
        $subtitle = $u->phone ?: ($u->email ?: null);
        return [
            'value'    => (string) $u->id,
            'label'    => $u->name,
            'subtitle' => $subtitle,
            'initial'  => mb_substr($u->name, 0, 1),
        ];
    })->values()->all();

    $roleOptions = $roles->map(fn($r) => [
        'value'    => (string) $r->id,
        'label'    => $r->display_name ?: $r->name,
        'subtitle' => $r->display_name ? $r->name : null,
    ])->values()->all();

    $clientSelectOptions = $clients->map(function ($c) {
        return [
            'value'    => (string) $c->id,
            'label'    => $c->full_name,
            'subtitle' => $c->phone ?: null,
            'initial'  => mb_substr($c->full_name, 0, 1),
        ];
    })->values()->all();

    $clientStatusOptions = $clientStatuses->map(fn($st) => [
        'value' => (string) $st->id,
        'label' => $st->label ?? $st->key,
    ])->values()->all();

    $defaultTargetType = old('target_type', $canTargetUsers ? 'users' : ($canTargetClients ? 'clients' : 'users'));
    $defaultSmsType    = old('type', 'manual');
    $defaultScope      = old('recipient_scope', 'selected');
@endphp

@section('title', 'ارسال پیامک جدید')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 pb-32 md:pb-8"
         x-data="{
            targetType: @js($defaultTargetType),
            smsType: @js($defaultSmsType),
            recipientScope: @js($defaultScope),
            bodyText: @js((string) old('body', '')),
            submitting: false,
            insertVar(v) {
                const el = this.$refs.bodyInput;
                if (!el) { this.bodyText += v; return; }
                const start = el.selectionStart ?? this.bodyText.length;
                const end = el.selectionEnd ?? this.bodyText.length;
                this.bodyText = this.bodyText.slice(0, start) + v + this.bodyText.slice(end);
                this.$nextTick(() => {
                    el.focus();
                    el.setSelectionRange(start + v.length, start + v.length);
                });
            },
            get isPersian() {
                return /[^\x00-\x7F]/.test(this.bodyText);
            },
            get smsParts() {
                const len = this.bodyText.length;
                if (len === 0) return 0;
                const single = this.isPersian ? 70 : 160;
                const multi = this.isPersian ? 67 : 153;
                return len <= single ? 1 : Math.ceil(len / multi);
            },
            toFa(n) {
                return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
            }
         }">

        {{-- مسیر راهنما و هدر صفحه --}}
        <header class="space-y-2.5 sm:space-y-3">
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
                <span class="text-indigo-600 dark:text-indigo-400 font-black">ارسال پیامک جدید</span>
            </nav>

            <div class="flex items-center justify-between gap-2.5 sm:gap-6">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <span
                        class="flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 shadow-2xs shrink-0">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight truncate sm:whitespace-normal">
                            ارسال پیامک جدید
                        </h1>
                    </div>
                </div>

                <a href="{{ route('user.sms.logs.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold shadow-2xs transition-colors cursor-pointer shrink-0">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden sm:inline">تاریخچه پیامک‌ها</span>
                    <span class="sm:hidden">تاریخچه</span>
                </a>
            </div>
        </header>

        @if(isset($errors) && $errors->any())
            <x-ui.alert variant="error" title="خطا در ثبت فرم" :dismissible="true">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if(session('status'))
            <x-ui.alert variant="success" title="پیام سیستم" :dismissible="true">{{ session('status') }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('user.sms.send.store') }}" @submit="submitting = true" class="space-y-6">
            @csrf

            <section
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-30">
                <div
                    class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">گیرندگان پیام</h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">جامعه هدف و روش انتخاب
                            دریافت‌کنندگان را تعیین کنید</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @if($canTargetUsers || $canTargetClients)
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جامعه
                                    هدف</label>
                                @php
                                    $targetOptions = [];
                                    if ($canTargetUsers) $targetOptions[] = ['value' => 'users', 'label' => 'کاربران سیستم'];
                                    if ($canTargetClients) $targetOptions[] = ['value' => 'clients', 'label' => 'مشتریان'];
                                @endphp
                                <x-ui.segmented-button
                                    name="target_type"
                                    model="targetType"
                                    :fullWidth="true"
                                    size="md"
                                    :options="$targetOptions"
                                />
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">روش
                                انتخاب</label>
                            <x-ui.segmented-button
                                name="recipient_scope"
                                model="recipientScope"
                                :fullWidth="true"
                                size="md"
                                :options="[
                                    ['value' => 'selected', 'label' => 'انتخاب دستی از لیست'],
                                    ['value' => 'filters',  'label' => 'ارسال گروهی با فیلتر'],
                                ]"
                            />
                        </div>
                    </div>

                    {{-- انتخابگرهای چندتایی با کامپوننت رسمی select --}}
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50">
                        <div x-show="targetType === 'users' && recipientScope === 'filters'" x-cloak
                             class="relative z-40">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                نقش‌های کاربری <span class="text-rose-500">*</span>
                            </label>
                            <x-ui.select
                                name="user_role_ids"
                                :options="$roleOptions"
                                :value="old('user_role_ids', [])"
                                :multiple="true"
                                placeholder="انتخاب نقش‌های کاربری..."
                                searchPlaceholder="جستجوی نقش..."
                            />
                        </div>

                        <div x-show="targetType === 'users' && recipientScope === 'selected'" x-cloak
                             class="relative z-40">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                انتخاب کاربران <span class="text-rose-500">*</span>
                            </label>
                            <x-ui.select
                                name="user_ids"
                                :options="$userSelectOptions"
                                :value="old('user_ids', [])"
                                :multiple="true"
                                placeholder="انتخاب یک یا چند کاربر..."
                                searchPlaceholder="جستجوی نام یا شماره..."
                            />
                        </div>

                        <div x-show="targetType === 'clients' && recipientScope === 'filters'" x-cloak
                             class="relative z-40">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                وضعیت‌های مشتری <span class="text-rose-500">*</span>
                            </label>
                            <x-ui.select
                                name="client_status_ids"
                                :options="$clientStatusOptions"
                                :value="old('client_status_ids', [])"
                                :multiple="true"
                                placeholder="انتخاب وضعیت مشتری..."
                                searchPlaceholder="جستجوی وضعیت..."
                            />
                        </div>

                        <div x-show="targetType === 'clients' && recipientScope === 'selected'" x-cloak
                             class="relative z-40">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                انتخاب مشتریان <span class="text-rose-500">*</span>
                            </label>
                            <x-ui.select
                                name="client_ids"
                                :options="$clientSelectOptions"
                                :value="old('client_ids', [])"
                                :multiple="true"
                                placeholder="انتخاب یک یا چند مشتری..."
                                searchPlaceholder="جستجوی نام یا موبایل..."
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs relative z-20">
                <div
                    class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-gray-900 dark:text-white">محتوای پیام، الگو و زمان‌بندی
                                ارسال</h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">زمان‌بندی، کد پترن و متن
                                پیامک را در یک بخش یکپارچه تنظیم کنید</p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 text-[11px] font-bold">
                        <span
                            class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/70 text-gray-600 dark:text-gray-300">
                            <span x-text="toFa(bodyText.length)"></span> کاراکتر
                        </span>
                        <span
                            class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                            <span x-text="toFa(smsParts)"></span> صفحه پیامک
                        </span>
                    </div>
                </div>

                <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <div class="lg:col-span-2 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-end">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع
                                    زمان‌بندی</label>
                                <x-ui.segmented-button
                                    name="type"
                                    model="smsType"
                                    :fullWidth="true"
                                    size="md"
                                    :options="[
                                        ['value' => 'manual',    'label' => 'ارسال آنی (همین الان)'],
                                        ['value' => 'scheduled', 'label' => 'زمان‌بندی شده (آینده)'],
                                    ]"
                                />
                            </div>

                            <div>
                                <label for="pattern"
                                       class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">کد پترن
                                    (اختیاری)</label>
                                <input type="text"
                                       id="pattern"
                                       name="pattern"
                                       value="{{ old('pattern') }}"
                                       placeholder="مثلاً: 18459"
                                       dir="ltr"
                                       class="w-full h-[42px] text-xs sm:text-sm font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white px-3.5 text-left focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden">
                            </div>
                        </div>

                        <div x-show="smsType === 'scheduled'"
                             x-cloak
                             x-transition
                             class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/15 border border-amber-100 dark:border-amber-800/40">
                            <div>
                                <label for="scheduled_date_jalali"
                                       class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    تاریخ اجرا (شمسی)
                                </label>
                                <input type="text"
                                       id="scheduled_date_jalali"
                                       name="scheduled_date_jalali"
                                       data-jdp-only-date
                                       autocomplete="off"
                                       value="{{ old('scheduled_date_jalali') }}"
                                       placeholder="مثلاً: ۱۴۰۵/۰۹/۰۵"
                                       class="w-full h-10 text-xs sm:text-sm font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 text-center focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden shadow-2xs">
                            </div>

                            <div>
                                <x-ui.time-picker
                                    id="scheduled_time"
                                    name="scheduled_time"
                                    label="ساعت اجرا"
                                    :value="old('scheduled_time', '09:00')"
                                    :step="5"
                                />
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="body" class="block text-xs font-bold text-gray-700 dark:text-gray-300">متن
                                    پیامک</label>
                                <span class="sm:hidden text-[10px] font-bold text-gray-400">
                                    <span x-text="toFa(bodyText.length)"></span> کاراکتر • <span
                                        x-text="toFa(smsParts)"></span> صفحه
                                </span>
                            </div>
                            <textarea id="body"
                                      name="body"
                                      x-ref="bodyInput"
                                      x-model="bodyText"
                                      rows="5"
                                      placeholder="متن پیام خود را اینجا بنویسید یا از متغیرهای سمت چپ استفاده کنید..."
                                      class="w-full text-xs sm:text-sm rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white p-4 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-hidden leading-relaxed"></textarea>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/60 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-black text-gray-800 dark:text-gray-100">
                            <span
                                class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </span>
                            <span>متغیرهای قابل درج</span>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                            برای درج سریع در متن، روی هر متغیر کلیک کنید:
                        </p>
                        <div class="grid grid-cols-2 lg:grid-cols-1 gap-2">
                            @foreach([
                                '{0}' => 'نام و نام خانوادگی',
                                '{1}' => 'نام کاربری',
                                '{2}' => 'کد ملی',
                                '{3}' => 'شماره موبایل',
                                '{4}' => 'ایمیل',
                                '{5}' => 'نقش / وضعیت',
                            ] as $key => $val)
                                <button type="button"
                                        @click="insertVar('{{ $key }}')"
                                        class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-indigo-50/40 dark:hover:bg-indigo-950/30 transition-all text-right cursor-pointer group">
                                    <span
                                        class="text-[11px] font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 truncate">{{ $val }}</span>
                                    <x-ui.badge variant="indigo" size="sm">{{ $key }}</x-ui.badge>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <div
                class="sticky bottom-4 z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 shadow-lg flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('user.sms.logs.index') }}"
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
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <svg x-cloak x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'در حال ارسال...' : 'ارسال پیامک'">ارسال پیامک</span>
                </button>
            </div>

        </form>
    </div>
@endsection
