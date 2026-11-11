@extends('layouts.user')

@section('title', 'ایجاد کاربر زیرمجموعه جدید')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5 sm:space-y-8" x-data="subAccountCreator()">
        {{-- Header & Title --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-5 pb-4 sm:pb-6 border-b border-gray-200/60 dark:border-gray-800">
            <div class="flex items-start justify-between gap-3 w-full sm:w-auto">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 sm:gap-4">
                        <span
                            class="flex items-center justify-center w-8 h-8 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white shadow-sm shrink-0">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-base sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                                    {{ __('افزودن کاربر زیرمجموعه جدید') }}
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile Back Button (Top-Left) --}}
                <a href="{{ route('reseller.sub-accounts.index') }}"
                   class="sm:hidden inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden xs:inline">بازگشت به </span>
                    <span>لیست</span>
                </a>
            </div>

            {{-- Desktop Back Button --}}
            <a href="{{ route('reseller.sub-accounts.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('بازگشت به لیست سامانه‌ها و کاربران') }}</span>
            </a>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            {{-- Form Section --}}
            <div class="lg:col-span-2">
                <x-resellerhub::card class="overflow-hidden p-4 sm:p-8">
                    <form @submit.prevent="submitForm()" action="{{ route('reseller.sub-accounts.store') }}"
                          method="POST"
                          class="space-y-6">
                        @csrf

                        <div>
                            <div
                                class="flex items-center gap-2.5 pb-3 border-b border-gray-100 dark:border-gray-700/80 mb-5">
                                <span
                                    class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                                <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">{{ __('مشخصات هویتی کاربر زیرمجموعه') }}</h3>
                            </div>

                            <div class="space-y-5">
                                {{-- Name --}}
                                <div>
                                    <x-resellerhub::label>
                                        {{ __('نام و نام خانوادگی') }} <span class="text-rose-500 font-bold">*</span>
                                    </x-resellerhub::label>
                                    <div class="relative">
                                        <x-resellerhub::input class="pe-10" type="text"
                                                              name="name"
                                                              x-model="form.name"
                                                              placeholder="{{ __('مثلاً: دکتر علی محمدی') }}"
                                                              required
                                                              autofocus/>
                                        <div
                                            class="absolute inset-y-0 end-0 pe-3.5 flex items-center pointer-events-none text-gray-400">
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
                                        <x-resellerhub::input class="pl-10 pr-4 text-left tracking-wider" type="text"
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
                            </div>
                        </div>
                        <div
                            class="pt-4 sm:pt-5 border-t border-gray-100 dark:border-gray-800 flex flex-row items-center justify-between gap-2 sm:gap-3 sticky bottom-0 sm:static bg-white/95 dark:bg-gray-800/95 backdrop-blur-md -mx-4 sm:mx-0 px-4 sm:px-0 py-3 sm:py-0 z-20">
                            <a href="{{ route('reseller.sub-accounts.index') }}"
                               class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all cursor-pointer text-center shrink-0">
                                {{ __('انصراف') }}
                            </a>
                            <button type="submit"
                                    :disabled="submitting"
                                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-md sm:shadow-lg shadow-indigo-600/25 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
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
                                <span x-text="submitting ? 'در حال ثبت...' : 'ثبت و ایجاد کاربر زیرمجموعه'"></span>
                            </button>
                        </div>
                    </form>
                </x-resellerhub::card>
            </div>

            {{-- Sidebar Info --}}
            <div class="space-y-6">
                <div
                    class="rounded-2xl sm:rounded-3xl bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/60 p-4 sm:p-6 shadow-xs space-y-3.5 sm:space-y-4">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">{{ __('نکات مهم درباره کاربران زیرمجموعه') }}</h3>
                    </div>

                    <ul class="space-y-3 sm:space-y-3.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        <li class="flex items-start gap-2 sm:gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-1.5 shrink-0"></span>
                            <span><strong>یک کاربر زیرمجموعه، چند سامانه:</strong> هر حساب کاربری می‌تواند چندین سامانه مستقل تحت مالکیت خود داشته باشد.</span>
                        </li>
                        <li class="flex items-start gap-2 sm:gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 mt-1.5 shrink-0"></span>
                            <span><strong>انتخاب سریع در ساخت سامانه:</strong> پس از ثبت، در فرم ساخت سامانه تنها با انتخاب نام این خریدار، کلیه مشخصات متصل خواهند شد.</span>
                        </li>
                    </ul>
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
             class="fixed bottom-6 start-4 end-4 sm:start-auto sm:end-6 z-[100] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl text-xs sm:text-sm font-bold border backdrop-blur-md"
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
        function subAccountCreator() {
            return {
                form: {
                    name: '{{ old('name', '') }}',
                    mobile: '{{ old('mobile', '') }}'
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
                        const res = await fetch("{{ route('reseller.sub-accounts.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.form)
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
                            this.showToast(data.message || 'خطا در ثبت اطلاعات زیرمجموعه.', 'error');
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
