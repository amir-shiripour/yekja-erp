@extends('layouts.user')

@php
    $createRoute = $createRoute ?? (request()->routeIs('user.users.*') ? 'user.users.create' : 'admin.users.create');
    $editRoute = $editRoute ?? (request()->routeIs('user.users.*') ? 'user.users.edit' : 'admin.users.edit');
    $destroyRoute = $destroyRoute ?? (request()->routeIs('user.users.*') ? 'user.users.destroy' : 'admin.users.destroy');
    $title = $pageTitle ?? 'مدیریت کاربران';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 space-y-6 pb-32 md:pb-8">

        {{-- هدر صفحه با سبک اصیل وایب‌فارسی --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">
                        {{ $pageTitle ?? 'مدیریت کاربران' }}
                    </h1>
                    <x-ui.badge variant="indigo" size="sm" dot>
                        {{ number_format($users->total()) }} کاربر فعال
                    </x-ui.badge>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mr-12 leading-relaxed">
                    {{ $pageSubtitle ?? 'مشاهده، ویرایش دسترسی‌ها و مدیریت حساب‌های کاربری فعال در سامانه.' }}
                </p>
            </div>

            {{-- دکمه افزودن کاربر جدید --}}
            @can('users.create')
                @if (Route::has($createRoute))
                    <a href="{{ route($createRoute) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                        </svg>
                        افزودن کاربر جدید
                    </a>
                @endif
            @endcan
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

        {{-- کانتینر اصلی محتوا و داده‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden">
            @if($users->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="هیچ کاربری یافت نشد"
                        description="در حال حاضر کاربری با مشخصات جستجو شده یا ثبت شده در سامانه وجود ندارد."
                        action-label="افزودن اولین کاربر"
                        :action-url="Route::has($createRoute) ? route($createRoute) : null"
                    />
                </div>
            @else
                {{-- نسخه دسکتاپ: جدول استاندارد با چیدمان کاملاً منظم و ساختار صحیح HTML --}}
                <div class="hidden md:block overflow-x-auto [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-200/80 dark:border-gray-700/80 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5 w-16">#</th>
                            <th class="px-5 py-3.5">مشخصات کاربر</th>
                            <th class="px-5 py-3.5">شماره تماس</th>
                            <th class="px-5 py-3.5">نقش‌ها و سطح دسترسی</th>
                            <th class="px-5 py-3.5 text-left pl-6 w-32">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($users as $user)
                            <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- ستون شناسه --}}
                                <td class="px-5 py-4 text-gray-400 dark:text-gray-500 text-xs font-bold">
                                    #{{ $user->id }}
                                </td>

                                {{-- ستون نام و ایمیل کاربر با دکمه کپی --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100 dark:border-indigo-800">
                                            {{ mb_substr($user->name ?? 'ک', 0, 1) }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-gray-900 dark:text-white truncate">
                                                {{ $user->name }}
                                            </span>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-xs text-gray-500 dark:text-gray-400 dir-ltr text-right truncate">
                                                    {{ $user->email }}
                                                </span>
                                                @if(!empty($user->email))
                                                    <x-ui.copy-button :text="$user->email" size="sm" title="کپی ایمیل" />
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- ستون شماره موبایل با دکمه کپی --}}
                                <td class="px-5 py-4">
                                    @if(!empty($user->mobile))
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-xs text-gray-700 dark:text-gray-200 dir-ltr">
                                                {{ $user->mobile }}
                                            </span>
                                            <x-ui.copy-button :text="$user->mobile" size="sm" title="کپی شماره تماس" />
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>

                                {{-- ستون نقش‌ها با کامپوننت Badge --}}
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5 max-w-[280px]">
                                        @forelse($user->roles as $role)
                                            @php
                                                $roleName = $role->name ?? '';
                                                $badgeVariant = match(true) {
                                                    str_contains($roleName, 'admin') || str_contains($roleName, 'مدیر') => 'indigo',
                                                    str_contains($roleName, 'sales') || str_contains($roleName, 'فروش') => 'emerald',
                                                    str_contains($roleName, 'support') || str_contains($roleName, 'پشتیبان') => 'sky',
                                                    default => 'slate',
                                                };
                                            @endphp
                                            <x-ui.badge :variant="$badgeVariant" size="sm">
                                                {{ $role->display_name ?? $role->name }}
                                            </x-ui.badge>
                                        @empty
                                            <span class="text-xs text-gray-400 dark:text-gray-500">بدون نقش انتسابی</span>
                                        @endforelse
                                    </div>
                                </td>

                                {{-- ستون عملیات --}}
                                <td class="px-5 py-4 text-left pl-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @can('users.update')
                                            <a href="{{ route($editRoute, $user) }}"
                                               class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 transition-colors"
                                               title="ویرایش کاربر">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('users.delete')
                                            @if(auth()->id() !== $user->id)
                                                <form method="POST" action="{{ route($destroyRoute, $user) }}" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.delete-button
                                                        type="submit"
                                                        label=""
                                                        confirm-label="حذف؟"
                                                        size="sm"
                                                        title="حذف کاربر"
                                                    />
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- نسخه موبایل (کارت‌های بهینه‌شده به جای جدول اسکرول‌شونده و نامرتب) --}}
                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($users as $user)
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ mb_substr($user->name ?? 'ک', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-sm text-gray-900 dark:text-white truncate">
                                            {{ $user->name }}
                                        </h3>
                                        <span class="text-[11px] text-gray-400">#{{ $user->id }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    @can('users.update')
                                        <a href="{{ route($editRoute, $user) }}"
                                           class="p-2 rounded-lg text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-900/30 text-xs font-bold"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endcan
                                    @can('users.delete')
                                        @if(auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route($destroyRoute, $user) }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.delete-button type="submit" label="" confirm-label="حذف؟" size="sm" />
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>

                            <div class="space-y-1.5 text-xs bg-gray-50/70 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">ایمیل:</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-700 dark:text-gray-300 dir-ltr text-right truncate max-w-[200px]">{{ $user->email }}</span>
                                        @if(!empty($user->email))
                                            <x-ui.copy-button :text="$user->email" size="sm" />
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">موبایل:</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-700 dark:text-gray-300 dir-ltr">{{ $user->mobile ?? '—' }}</span>
                                        @if(!empty($user->mobile))
                                            <x-ui.copy-button :text="$user->mobile" size="sm" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($user->roles->isNotEmpty())
                                <div class="flex flex-wrap gap-1 pt-1">
                                    @foreach($user->roles as $role)
                                        <x-ui.badge variant="slate" size="sm">
                                            {{ $role->display_name ?? $role->name }}
                                        </x-ui.badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- صفحه‌بندی --}}
                @if($users->hasPages())
                    <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/30">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- کانتینر اعلان‌ها و توست‌های سیستمی --}}
        <x-ui.toast position="bottom-left" />
    </div>
@endsection
