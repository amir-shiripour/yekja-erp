<div role="tabpanel" id="tabpanel-kyc" aria-labelledby="tab-kyc" x-show="activeTab === 'kyc'" x-cloak class="space-y-6">
    <x-resellerhub::card class="p-6 sm:p-8 space-y-6">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center gap-3">
                <span class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">احراز هویت و اطلاعات زیرمجموعه‌ها
                        (KYC)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        تعیین فیلدها و مدارک الزامی برای مدیران زیرمجموعه در اولین ورود به سامانه
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.custom-fields.index', ['tab' => 'sub_account']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-all cursor-pointer">
                    <span>مدیریت پیشرفته فیلدها</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>

                <a href="{{ route('admin.custom-fields.create', ['entity_type' => 'sub_account']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md shadow-indigo-500/20 transition-all text-xs font-bold cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>افزودن فیلد احراز هویت</span>
                </a>
            </div>
        </div>
        <div
            class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-800/40 flex items-start gap-3">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs text-indigo-950 dark:text-indigo-200 leading-relaxed">
                <strong>فرآیند تکمیل خودکار اطلاعات (Onboarding):</strong>
                تمامی فیلدهایی که در این بخش تعریف می‌شوند، در اولین ورود مدیران زیرمجموعه (چه خریداران آنلاین و چه ثبت
                دستی) از آن‌ها درخواست خواهد شد. فیلدهای دارای علامت «اجباری» شرط عبور به داشبورد سامانه می‌باشند.
            </div>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300">
                    فیلدهای پیکربندی‌شده برای مدیران زیرمجموعه
                </h3>
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $subAccountCustomFields->count() }} فیلد فعال
                </span>
            </div>

            @if($subAccountCustomFields->isEmpty())
                <div
                    class="p-8 rounded-2xl bg-gray-50/70 dark:bg-gray-900/30 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-3">
                    <div
                        class="w-12 h-12 mx-auto rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">هنوز فیلدی برای احراز
                            هویت زیرمجموعه‌ها ثبت نشده است</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            می‌توانید کدملی، شماره پروانه، نام تجاری یا مدارک احراز هویت را تعریف نمایید.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('admin.custom-fields.create', ['entity_type' => 'sub_account']) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>تعریف فیلد جدید</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-right text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300 font-bold">
                        <tr>
                            <th scope="col" class="px-4 py-3.5">عنوان فیلد</th>
                            <th scope="col" class="px-4 py-3.5">نام سیستمی</th>
                            <th scope="col" class="px-4 py-3.5 text-center">نوع ورودی</th>
                            <th scope="col" class="px-4 py-3.5 text-center">وضعیت الزام</th>
                            <th scope="col" class="px-4 py-3.5 text-left">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900/40">
                        @foreach($subAccountCustomFields as $field)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                    {{ $field->label }}
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400 dir-ltr text-right">
                                    {{ $field->field_name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $typeBadge = match(strtolower($field->field_type)) {
                                            'file', 'image' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300',
                                            'select', 'radio' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300',
                                            'number', 'tel' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-md text-xs font-mono font-bold border {{ $typeBadge }}">
                                            {{ $field->field_type }}
                                        </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($field->is_required)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/40">
                                                اجباری
                                            </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                اختیاری
                                            </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-left">
                                    <a href="{{ route('admin.custom-fields.edit', $field) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-xs">
                                        <span>ویرایش</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </x-resellerhub::card>
</div>
