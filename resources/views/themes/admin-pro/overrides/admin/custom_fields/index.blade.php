@extends('layouts.user')

@php
    $title = 'مدیریت فیلدهای سفارشی';
    $isResellerHubEnabled = \Nwidart\Modules\Facades\Module::has('ResellerHub') && \Nwidart\Modules\Facades\Module::isEnabled('ResellerHub');
    $canSeeSubAccountTab = $isResellerHubEnabled && auth()->check() && (
        (method_exists(auth()->user(), 'can') && auth()->user()->can('custom-fields.sub-accounts.view'))
        || (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super-admin'))
    );
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 space-y-6 pb-32 md:pb-8">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </span>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        فیلدهای سفارشی و احراز هویت
                    </h1>
                </div>
            </div>

            @can('custom-fields.create')
                @if($tab === 'sub_account')
                    <a href="{{ route('admin.custom-fields.sub-account.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                        </svg>
                        افزودن فیلد احراز هویت زیرمجموعه
                    </a>
                @else
                    <a href="{{ route('admin.custom-fields.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                        </svg>
                        افزودن فیلد کاربران و پرسنل
                    </a>
                @endif
            @endcan
        </div>

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

        <div
            class="flex items-center gap-2 p-1.5 bg-gray-100/80 dark:bg-gray-800/80 rounded-2xl w-fit border border-gray-200/60 dark:border-gray-700/60">
            <a href="{{ route('admin.custom-fields.index', ['tab' => 'user']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $tab === 'user' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>فیلدهای کاربران و پرسنل</span>
                <span
                    class="px-2 py-0.5 rounded-lg text-[10px] font-black {{ $tab === 'user' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ number_format($userFieldsCount) }}
                </span>
            </a>

            @if($canSeeSubAccountTab)
                <a href="{{ route('admin.custom-fields.index', ['tab' => 'sub_account']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $tab === 'sub_account' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>فیلدهای زیرمجموعه‌ها</span>
                    <span
                        class="px-2 py-0.5 rounded-lg text-[10px] font-black {{ $tab === 'sub_account' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ number_format($subAccountFieldsCount) }}
                    </span>
                </a>
            @endif
        </div>
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden">
            @if($fields->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="هیچ فیلد سفارشی یافت نشد"
                        description="در حال حاضر فیلدی برای این بخش تعریف نشده است. می‌توانید با کلیک بر روی دکمه زیر اولین فیلد را ایجاد کنید."
                        action-label="{{ $tab === 'sub_account' ? 'افزودن فیلد KYC' : 'افزودن فیلد پرسنل' }}"
                        :action-url="$tab === 'sub_account' ? route('admin.custom-fields.sub-account.create') : route('admin.custom-fields.create')"
                    />
                </div>
            @else
                <div
                    class="hidden md:block overflow-x-auto [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">
                    <table class="w-full text-right text-xs">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-200/80 dark:border-gray-700/80 font-bold text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5 w-16">#</th>
                            <th class="px-5 py-3.5">عنوان فیلد و نام سیستمی</th>
                            <th class="px-5 py-3.5 text-center">{{ $tab === 'sub_account' ? 'دامنه اعمال' : 'نقش مرتبط' }}</th>
                            <th class="px-5 py-3.5 text-center">نوع ورودی</th>
                            <th class="px-5 py-3.5 text-center">اجباری</th>
                            <th class="px-5 py-3.5 text-left pl-6 w-32">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($fields as $f)
                            <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-5 py-4 text-gray-400 dark:text-gray-500 font-bold text-xs">
                                    #{{ $f->id }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-800">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M4 6h16M4 12h16M4 18h7"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-gray-900 dark:text-white truncate">
                                                {{ $f->label }}
                                            </span>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span
                                                    class="text-[11px] text-gray-500 dark:text-gray-400 dir-ltr font-bold">
                                                    {{ $f->field_name }}
                                                </span>
                                                <x-ui.copy-button :text="$f->field_name" size="sm"
                                                                  title="کپی نام فیلد"/>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if($f->entity_type === 'sub_account')
                                        <x-ui.badge variant="indigo" size="sm" dot>مدیران زیرمجموعه (KYC)</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="slate" size="sm">
                                            {{ $f->role->display_name ?? $f->role_name }}
                                        </x-ui.badge>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @php
                                        $typeVariant = match(strtolower($f->field_type)) {
                                            'select', 'radio', 'checkbox' => 'indigo',
                                            'file', 'image' => 'amber',
                                            'number', 'tel' => 'sky',
                                            'date' => 'emerald',
                                            default => 'neutral'
                                        };
                                    @endphp
                                    <x-ui.badge :variant="$typeVariant" size="sm">
                                        {{ $f->field_type }}
                                    </x-ui.badge>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if($f->is_required)
                                        <x-ui.badge variant="emerald" size="sm" dot>الزامی</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral" size="sm">اختیاری</x-ui.badge>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-left pl-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @can('custom-fields.update')
                                            <a href="{{ route('admin.custom-fields.edit', $f) }}"
                                               class="p-2 rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 transition-colors"
                                               title="ویرایش فیلد">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('custom-fields.delete')
                                            <form method="POST" action="{{ route('admin.custom-fields.destroy', $f) }}"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.delete-button
                                                    type="submit"
                                                    label=""
                                                    confirm-label="حذف؟"
                                                    size="sm"
                                                    title="حذف فیلد"
                                                />
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($fields as $f)
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span
                                        class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4 6h16M4 12h16M4 18h7"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-xs text-gray-900 dark:text-white truncate">
                                            {{ $f->label }}
                                        </h3>
                                        <span class="text-[10px] text-gray-400">#{{ $f->id }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    @can('custom-fields.update')
                                        <a href="{{ route('admin.custom-fields.edit', $f) }}"
                                           class="p-2 rounded-xl text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-900/30 text-xs font-bold"
                                           title="ویرایش">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('custom-fields.delete')
                                        <form method="POST" action="{{ route('admin.custom-fields.destroy', $f) }}"
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.delete-button
                                                type="submit"
                                                label=""
                                                confirm-label="حذف؟"
                                                size="sm"
                                            />
                                        </form>
                                    @endcan
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between text-xs bg-gray-50/70 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-gray-400 text-[11px]">شناسه:</span>
                                    <span
                                        class="dir-ltr text-gray-700 dark:text-gray-300 font-bold text-[11px]">{{ $f->field_name }}</span>
                                    <x-ui.copy-button :text="$f->field_name" size="sm"/>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-ui.badge variant="slate" size="sm">
                                        {{ $f->entity_type === 'sub_account' ? 'KYC' : ($f->role->display_name ?? $f->role_name) }}
                                    </x-ui.badge>
                                    <x-ui.badge :variant="$f->is_required ? 'emerald' : 'neutral'" size="sm">
                                        {{ $f->is_required ? 'الزامی' : 'اختیاری' }}
                                    </x-ui.badge>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($fields->hasPages())
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30">
                    {{ $fields->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
