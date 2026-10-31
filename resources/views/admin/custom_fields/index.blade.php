{{-- resources/views/admin/custom_fields/index.blade.php --}}
@extends('layouts.user')

@php
    $title = 'مدیریت فیلدهای سفارشی';
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                </span>
                    فیلدهای سفارشی سیستم و زیرمجموعه‌ها
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">مدیریت فیلدهای اختصاصی کاربران سامانه و احراز هویت مدیران زیرمجموعه (KYC)</p>
            </div>

            @can('custom-fields.create')
                @if($tab === 'sub_account')
                    <a href="{{ route('admin.custom-fields.sub-account.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        افزودن فیلد احراز هویت زیرمجموعه
                    </a>
                @else
                    <a href="{{ route('admin.custom-fields.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        افزودن فیلد پرسنل و کاربران
                    </a>
                @endif
            @endcan
        </div>

        {{-- تب‌های تفکیک موجودیت: کاربران داخلی در برابر زیرمجموعه‌های نمایندگی --}}
        @php
            $isResellerHubEnabled = \Nwidart\Modules\Facades\Module::has('ResellerHub') && \Nwidart\Modules\Facades\Module::isEnabled('ResellerHub');
            $canSeeSubAccountTab = $isResellerHubEnabled && (auth()->user()->can('custom-fields.sub-accounts.view') || auth()->user()->hasRole('super-admin'));
        @endphp

        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-2">
            <a href="{{ route('admin.custom-fields.index', ['tab' => 'user']) }}"
               class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'user' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>فیلدهای کاربران و پرسنل داخلی</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'user' ? 'bg-indigo-800 text-indigo-100' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ $userFieldsCount }}
                </span>
            </a>

            @if($canSeeSubAccountTab)
                <a href="{{ route('admin.custom-fields.index', ['tab' => 'sub_account']) }}"
                   class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ $tab === 'sub_account' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>احراز هویت و اطلاعات زیرمجموعه‌ها (KYC)</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'sub_account' ? 'bg-indigo-800 text-indigo-100' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ $subAccountFieldsCount }}
                    </span>
                </a>
            @endif
        </div>

        @if($tab === 'sub_account')
            <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 flex items-start gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-600 text-white shrink-0 mt-0.5 text-xs font-bold">i</span>
                <div class="text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
                    این فیلدها در اولین ورود مدیران زیرمجموعه‌ها (چه خریداران آنلاین سایت و چه سامانه‌های ایجاد شده توسط نمایندگان) به عنوان اطلاعات هویتی و پروانه فعالیت درخواست می‌شوند و کاملاً مستقل از پرسنل داخلی سیستم مرکزی شما هستند.
                </div>
            </div>
        @endif

        {{-- جدول فیلدها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 w-16">#</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300">عنوان فیلد و نام سیستمی</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">{{ $tab === 'sub_account' ? 'دامنه اعمال' : 'نقش مرتبط' }}</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">نوع فیلد (Type)</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-center">اجباری</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($fields as $f)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">

                            {{-- شناسه --}}
                            <td class="px-6 py-4 text-gray-400 dark:text-gray-500 font-mono text-xs font-semibold">
                                {{ $f->id }}
                            </td>

                            {{-- عنوان و نام سیستمی --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 group-hover:bg-indigo-100 group-hover:text-indigo-600 dark:group-hover:bg-indigo-900/30 dark:group-hover:text-indigo-400 transition-colors shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $f->label }}</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono dir-ltr text-right mt-0.5">
                                            {{ $f->field_name }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- نقش مرتبط یا دامنه --}}
                            <td class="px-6 py-4 text-center">
                                @if($f->entity_type === 'sub_account')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-extrabold rounded-md border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300">
                                        مدیران زیرمجموعه (KYC)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-bold rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-700 dark:text-gray-200">
                                        {{ $f->role->display_name ?? $f->role_name }}
                                    </span>
                                @endif
                            </td>

                            {{-- نوع فیلد --}}
                            <td class="px-6 py-4 text-center">
                                @php
                                    $typeColor = match(strtolower($f->field_type)) {
                                        'select', 'radio', 'checkbox' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800',
                                        'file', 'image' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800',
                                        'number', 'tel' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold border {{ $typeColor }} dir-ltr">
                                    {{ $f->field_type }}
                                </span>
                            </td>

                            {{-- وضعیت اجباری --}}
                            <td class="px-6 py-4 text-center">
                                @if($f->is_required)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        بله
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-gray-50 text-gray-500 border border-gray-100 dark:bg-gray-800/50 dark:text-gray-400 dark:border-gray-700/50">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        خیر
                                    </span>
                                @endif
                            </td>

                            {{-- عملیات --}}
                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @can('custom-fields.update')
                                        <a href="{{ route('admin.custom-fields.edit', $f) }}"
                                           class="p-2 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:text-indigo-400 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 transition-colors"
                                           title="ویرایش فیلد">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endcan

                                    @can('custom-fields.delete')
                                        <form method="POST" action="{{ route('admin.custom-fields.destroy', $f) }}" class="inline-block" onsubmit="return confirm('آیا از حذف این فیلد اطمینان دارید؟ اطلاعات ذخیره شده کاربران مرتبط با این فیلد ممکن است تحت تاثیر قرار گیرد.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors"
                                                    title="حذف فیلد">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    <p class="text-base font-medium text-gray-900 dark:text-white">هیچ فیلد سفارشی یافت نشد.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            @if($fields->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    {{ $fields->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
