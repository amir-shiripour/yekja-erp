@if ($showKycModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             wire:click="closeKycModal"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-200/80 dark:border-gray-700">

                {{-- Modal Header --}}
                <div
                    class="p-6 bg-gray-50/80 dark:bg-gray-850 border-b border-gray-200/80 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <span
                            class="flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/25 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">
                                احراز هویت و اطلاعات مدیر سامانه
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                اطلاعات تکمیلی هویتی جهت راه‌اندازی و اعتبارسنجی اولیه سامانه
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeKycModal"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Form --}}
                <form wire:submit="saveKyc">
                    <div class="p-6 space-y-4">
                        @if ($errors->has('kyc'))
                            <div
                                class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-bold rounded-xl">
                                {{ $errors->first('kyc') }}
                            </div>
                        @endif

                        @if ($subAccountFields->isEmpty())
                            <div class="text-center py-6">
                                <div
                                    class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">فیلد احراز هویت اختصاصی
                                    تعیین نشده است</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    می‌توانید با فشردن دکمه زیر، مستقیماً این گام را تایید و تکمیل نمایید.
                                </p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($subAccountFields as $field)
                                    @php
                                        $fieldName = $field->field_name;
                                        $fieldType = strtolower($field->field_type ?? 'text');
                                        $fieldErrorKey = 'kycForm.' . $fieldName;
                                    @endphp
                                    <div wire:key="kyc-field-{{ $field->id }}" class="space-y-1.5">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                            {{ $field->label }}
                                            @if ($field->is_required)
                                                <span class="text-red-500 font-extrabold ms-0.5">*</span>
                                            @else
                                                <span
                                                    class="text-xs text-gray-400 font-normal ms-1">(اختیاری)</span>
                                            @endif
                                        </label>

                                        @if ($fieldType === 'textarea')
                                            <textarea
                                                wire:model="kycForm.{{ $fieldName }}"
                                                rows="3"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all placeholder:text-gray-400"
                                                placeholder="ورود {{ $field->label }}"
                                            ></textarea>
                                        @elseif ($fieldType === 'select')
                                            @php
                                                $meta = [];
                                                if (is_array($field->meta)) {
                                                    $meta = $field->meta;
                                                } elseif (!empty($field->meta)) {
                                                    $decodedMeta = json_decode((string)$field->meta, true);
                                                    $meta = is_array($decodedMeta) ? $decodedMeta : [];
                                                }
                                                $options = $meta['options'] ?? [];
                                            @endphp
                                            <select
                                                wire:model="kycForm.{{ $fieldName }}"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                            >
                                                <option value="">انتخاب کنید...</option>
                                                @foreach ($options as $optVal => $optLabel)
                                                    <option value="{{ $optVal }}">{{ $optLabel }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input
                                                type="{{ in_array($fieldType, ['number', 'date', 'email', 'tel']) ? $fieldType : 'text' }}"
                                                wire:model="kycForm.{{ $fieldName }}"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all placeholder:text-gray-400"
                                                placeholder="ورود {{ $field->label }}"
                                            />
                                        @endif

                                        @error($fieldErrorKey)
                                        <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            wire:click="closeKycModal"
                            class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                        >
                            انصراف
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
                        >
                            <span>ثبت و تایید احراز هویت</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
