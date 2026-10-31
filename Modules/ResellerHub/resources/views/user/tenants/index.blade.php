@extends('layouts.user')

@section('title', 'مدیریت مشتریان و سامانه‌های CRM')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">
                سامانه‌های CRM مشتریان
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                مشاهده، جستجو و پایش سامانه‌های واگذار شده به مشتریان
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reseller.tenants.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>افزودن سامانه جدید</span>
            </a>
        </div>
    </div>

    @include('resellerhub::user.dashboard.partials.tenants-table')
</div>
@endsection
