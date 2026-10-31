@extends('layouts.user')

@section('title', 'تنظیمات ماژول دامنه')

@php
    $cardClass = "bg-white dark:bg-gray-800/40 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden";
@endphp

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="domainSettingsForm()">

    {{-- Reusable Real-time Toast Notifications --}}
    @include('domainmanager::partials.toast')

    {{-- Hero Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">تنظیمات و یکپارچه‌سازی ماژول دامنه</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    راه‌اندازی دسته‌بندی قفل‌شده سیستمی و یکپارچه‌سازی با ماژول سفارشات و خدمات CRM.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.domainmanager.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>پیشخوان دامنه</span>
            </a>
        </div>
    </div>

    {{-- Flash Status Alert --}}
    @if(session('status'))
        <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- System Category Provisioning Card --}}
    <div class="{{ $cardClass }}">
        <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700/60 bg-gradient-to-r from-indigo-50/50 to-transparent dark:from-indigo-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 shadow-inner">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">دسته‌بندی قفل‌شده سیستمی در ماژول خدمات</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ایجاد خودکار دسته‌بندی استاندارد و غیرقابل حذف جهت تشخیص و نمایش سفارشات دامنه‌ها</p>
                </div>
            </div>

            {{-- Dynamic Status Badge --}}
            <div class="shrink-0">
                <template x-if="categoryCreated">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 shadow-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>دسته‌بندی سیستمی فعال و قفل‌شده</span>
                    </span>
                </template>
                <template x-if="!categoryCreated">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>نیاز به مقداردهی اولیه</span>
                    </span>
                </template>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="md:col-span-2 space-y-3 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    <p>
                        برای اینکه سیستم بتواند سفارشات دامنه را به صورت خودکار تشخیص دهد و کارت اختصاصی <strong>«مشخصات دامنه در سفارش»</strong> را در صفحه سفارشات نمایش دهد، دسته‌بندی سیستمی زیر با ویژگی <code class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 font-mono font-bold" dir="ltr">is_locked = true</code> در ماژول خدمات ایجاد می‌شود:
                    </p>
                    <div class="pt-1">
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 flex items-center gap-3.5 max-w-sm">
                            <span class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-200/60 dark:border-indigo-500/20 shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                            </span>
                            <div>
                                <span class="font-bold text-sm text-gray-900 dark:text-white block">ثبت و تمدید دامنه</span>
                                <span class="font-mono text-xs text-gray-400" dir="ltr">slug: domain</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-stretch md:items-end justify-center gap-3">
                    <button type="button" @click="seedCategory()" :disabled="seeding"
                            class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black text-xs sm:text-sm shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2.5 disabled:opacity-50 cursor-pointer">
                        <template x-if="seeding">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <template x-if="!seeding">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <span x-text="seeding ? 'در حال ثبت دسته‌بندی...' : (categoryCreated ? 'به‌روزرسانی دسته‌بندی سیستمی' : '🚀 مقداردهی و ایجاد دسته‌بندی قفل‌شده')"></span>
                    </button>

                    <a href="{{ route('services.categories.index') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <span>مشاهده لیست دسته‌بندی‌های خدمات</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function domainSettingsForm() {
        return {
            categoryCreated: {{ $domainCategory ? 'true' : 'false' }},
            seeding: false,
            toast: {
                show: false,
                message: '',
                type: 'success',
                timer: null
            },
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                clearTimeout(this.toast.timer);
                this.toast.timer = setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },
            async seedCategory() {
                if (this.seeding) return;
                this.seeding = true;

                try {
                    const response = await fetch('{{ route("user.domainmanager.settings.seed-category") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.categoryCreated = true;
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || 'خطا در ثبت دسته‌بندی سیستمی.', 'error');
                    }
                } catch (err) {
                    this.showToast('خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error');
                } finally {
                    this.seeding = false;
                }
            }
        };
    }
</script>
@endpush
@endsection
