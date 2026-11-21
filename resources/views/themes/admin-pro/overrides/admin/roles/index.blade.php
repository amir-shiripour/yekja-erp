@extends('layouts.user')

@php
    $title = 'مدیریت نقش‌ها و دسترسی‌ها';
    $createRoute = isset($createRoute) ? route($createRoute) : (Route::has('admin.roles.create') ? route('admin.roles.create') : '#');
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 space-y-6 pb-32 md:pb-8">

        {{-- هدر صفحه با سبک اصیل وایب‌فارسی --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">
                        مدیریت نقش‌ها و دسترسی‌ها
                    </h1>
                    <x-ui.badge variant="indigo" size="sm" dot>
                        {{ count($roles) }} نقش تعریف‌شده
                    </x-ui.badge>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mr-12 leading-relaxed">
                    تعریف سطوح مختلف سازمانی، تعیین ویجت‌های پیشخوان و تخصیص مجوزهای دسترسی به هر نقش.
                </p>
            </div>

            @if(!empty($isTenantContext) || auth()->user()->can('roles.create'))
                <a href="{{ $createRoute }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    افزودن نقش جدید
                </a>
            @endif
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

        {{-- جدول لیست نقش‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden">
            @if(count($roles) === 0)
                <div class="p-8">
                    <x-ui.empty-state
                        title="هیچ نقشی در سیستم ثبت نشده است"
                        description="برای دسته‌بندی کاربران و اعطای دسترسی‌های لازم، ابتدا یک نقش ایجاد نمایید."
                        action-label="ایجاد اولین نقش"
                        :action-url="$createRoute"
                    />
                </div>
            @else
                {{-- نسخه دسکتاپ: جدول منظم و ساختاریافته --}}
                <div class="hidden md:block overflow-x-auto [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-200/80 dark:border-gray-700/80 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5">عنوان و نوع نقش</th>
                            <th class="px-5 py-3.5 text-center">شناسه لاتین (Slug)</th>
                            <th class="px-5 py-3.5 text-center">کاربران متصل</th>
                            <th class="px-5 py-3.5 text-left pl-6 w-32">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($roles as $role)
                            @php
                                $isCustom = !empty($role->tenant_id);
                                $canEditRole = !empty($isTenantContext) ? $isCustom : auth()->user()->can('roles.update');
                                $canDeleteRole = !empty($isTenantContext) ? $isCustom : auth()->user()->can('roles.delete');
                                $editLink = isset($editRoute) ? route($editRoute, $role) : route('admin.roles.edit', $role);
                                $destroyLink = isset($destroyRoute) ? route($destroyRoute, $role) : route('admin.roles.destroy', $role);
                                $count = $roleUserCounts[$role->name] ?? 0;
                                $isSuperAdmin = in_array($role->name, ['super-admin', 'Super Admin']);
                            @endphp

                            <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- ستون نام و نوع نقش --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                            </svg>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ $role->display_name ?? $role->name }}
                                            </span>
                                            @if(!empty($isTenantContext))
                                                @if($isCustom)
                                                    <x-ui.badge variant="emerald" size="sm" dot>سازمان</x-ui.badge>
                                                @else
                                                    <x-ui.badge variant="neutral" size="sm">عمومی</x-ui.badge>
                                                @endif
                                            @elseif($isSuperAdmin)
                                                <x-ui.badge variant="amber" size="sm" dot>ارشد</x-ui.badge>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- ستون شناسه لاتین با دکمه کپی --}}
                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900/60 px-2.5 py-1 rounded-lg border border-gray-200/60 dark:border-gray-700/60">
                                        <span class="text-xs text-gray-600 dark:text-gray-300 dir-ltr font-bold">
                                            {{ $role->name }}
                                        </span>
                                        <x-ui.copy-button :text="$role->name" size="sm" title="کپی شناسه" />
                                    </div>
                                </td>

                                {{-- ستون تعداد کاربران --}}
                                <td class="px-5 py-4 text-center">
                                    <x-ui.badge :variant="$count > 0 ? 'sky' : 'neutral'" size="sm">
                                        {{ number_format($count) }} کاربر
                                    </x-ui.badge>
                                </td>

                                {{-- ستون عملیات --}}
                                <td class="px-5 py-4 text-left pl-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($canEditRole)
                                            <a href="{{ $editLink }}"
                                               class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 transition-colors"
                                               title="ویرایش نقش">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @elseif(!empty($isTenantContext))
                                            <span class="text-xs text-gray-400">قفل</span>
                                        @endif

                                        @if($canDeleteRole && !$isSuperAdmin)
                                            <form method="POST" action="{{ $destroyLink }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.delete-button
                                                    type="submit"
                                                    label=""
                                                    confirm-label="حذف؟"
                                                    size="sm"
                                                    title="حذف نقش"
                                                />
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- نسخه موبایل: کارت‌های تمیز بدون اسکرول شکسته --}}
                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($roles as $role)
                        @php
                            $isCustom = !empty($role->tenant_id);
                            $canEditRole = !empty($isTenantContext) ? $isCustom : auth()->user()->can('roles.update');
                            $canDeleteRole = !empty($isTenantContext) ? $isCustom : auth()->user()->can('roles.delete');
                            $editLink = isset($editRoute) ? route($editRoute, $role) : route('admin.roles.edit', $role);
                            $destroyLink = isset($destroyRoute) ? route($destroyRoute, $role) : route('admin.roles.destroy', $role);
                            $count = $roleUserCounts[$role->name] ?? 0;
                            $isSuperAdmin = in_array($role->name, ['super-admin', 'Super Admin']);
                        @endphp
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white">
                                        {{ $role->display_name ?? $role->name }}
                                    </span>
                                    @if($isCustom)
                                        <x-ui.badge variant="emerald" size="sm">سازمان</x-ui.badge>
                                    @elseif($isSuperAdmin)
                                        <x-ui.badge variant="amber" size="sm">ارشد</x-ui.badge>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($canEditRole)
                                        <a href="{{ $editLink }}" class="p-2 text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($canDeleteRole && !$isSuperAdmin)
                                        <form method="POST" action="{{ $destroyLink }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.delete-button type="submit" label="" confirm-label="حذف؟" size="sm" />
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs bg-gray-50/70 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-gray-400">شناسه:</span>
                                    <span class="dir-ltr text-gray-700 dark:text-gray-300 font-bold">{{ $role->name }}</span>
                                    <x-ui.copy-button :text="$role->name" size="sm" />
                                </div>
                                <div>
                                    <x-ui.badge variant="sky" size="sm">{{ number_format($count) }} کاربر</x-ui.badge>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- نوار ابزار متحرک موبایل (Mobile Dock) --}}
        <x-ui.dock />

        {{-- کانتینر اعلان‌ها و توست‌های سیستمی --}}
        <x-ui.toast position="bottom-left" />
    </div>
@endsection
