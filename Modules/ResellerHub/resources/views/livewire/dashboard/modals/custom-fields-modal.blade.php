@if ($showCustomFieldsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="custom-fields-modal-title" role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                 wire:click="closeCustomFieldsModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200/80 dark:border-gray-700">

                <div
                    class="p-6 bg-gray-50/80 dark:bg-gray-850 border-b border-gray-200/80 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-indigo-600 text-white rounded-2xl shadow-md shadow-indigo-600/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white"
                                id="custom-fields-modal-title">
                                {{ $activeCustomStepTitle }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $activeCustomStepDescription ?: 'لطفاً فیلدهای زیر را برای تکمیل راه‌اندازی با دقت تکمیل نمایید.' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeCustomFieldsModal"
                            class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveCustomFields">
                    <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                        @if(empty($activeCustomStepFields))
                            <div
                                class="p-6 text-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                فیلد سفارشی برای این مرحله پیکربندی نشده است. می‌توانید دکمه ثبت را جهت تأیید گام
                                بزنید.
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($activeCustomStepFields as $field)
                                    @php
                                        $fName = $field['name'] ?? $field['field_name'] ?? '';
                                        $fLabel = $field['label'] ?? $fName;
                                        $fType = strtolower($field['type'] ?? $field['field_type'] ?? 'text');
                                        $fRequired = !empty($field['required']) || !empty($field['is_required']);
                                        $fPlaceholder = $field['placeholder'] ?? '';
                                        $isFull = ($fType === 'textarea');
                                    @endphp
                                    <div class="{{ $isFull ? 'sm:col-span-2' : 'sm:col-span-1' }} space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <label class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                {{ $fLabel }}
                                                @if($fRequired)
                                                    <span class="text-rose-500 font-black">*</span>
                                                @else
                                                    <span
                                                        class="text-[10px] text-gray-400 font-normal">(اختیاری)</span>
                                                @endif
                                            </label>
                                        </div>

                                        @if($fType === 'textarea')
                                            <textarea wire:model.defer="customFieldsForm.{{ $fName }}"
                                                      rows="3"
                                                      placeholder="{{ $fPlaceholder }}"
                                                      class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                                        @elseif($fType === 'select')
                                            @php
                                                $selectOpts = [];
                                                if (is_array($field['options'] ?? null)) {
                                                    $selectOpts = $field['options'];
                                                } elseif (!empty($field['options'])) {
                                                    $selectOpts = array_map('trim', explode(',', $field['options']));
                                                }
                                            @endphp
                                            <select wire:model.defer="customFieldsForm.{{ $fName }}"
                                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                                <option value="">انتخاب کنید...</option>
                                                @foreach($selectOpts as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($fType === 'checkbox')
                                            <div
                                                class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900">
                                                <span
                                                    class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{ $fPlaceholder ?: 'فعال‌سازی / تأیید' }}</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox"
                                                           wire:model.defer="customFieldsForm.{{ $fName }}"
                                                           value="1" class="sr-only peer">
                                                    <div
                                                        class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                </label>
                                            </div>
                                        @elseif($fType === 'currency')
                                            <div class="relative">
                                                <input type="number"
                                                       wire:model.defer="customFieldsForm.{{ $fName }}"
                                                       placeholder="{{ $fPlaceholder ?: 'مبلغ به تومان' }}"
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 pl-14 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                                <span
                                                    class="absolute inset-y-0 left-0 flex items-center px-3 text-[11px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 rounded-l-xl">تومان</span>
                                            </div>
                                        @elseif($fType === 'url')
                                            <input type="url" wire:model.defer="customFieldsForm.{{ $fName }}"
                                                   placeholder="{{ $fPlaceholder ?: 'https://...' }}"
                                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                        @elseif($fType === 'time')
                                            <input type="time" wire:model.defer="customFieldsForm.{{ $fName }}"
                                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-mono text-center text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                        @elseif($fType === 'mobile')
                                            <input type="tel" wire:model.defer="customFieldsForm.{{ $fName }}"
                                                   placeholder="{{ $fPlaceholder ?: '۰۹xxxxxxxxx' }}"
                                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                        @else
                                            <input
                                                type="{{ in_array($fType, ['number', 'email', 'date', 'tel']) ? $fType : 'text' }}"
                                                wire:model.defer="customFieldsForm.{{ $fName }}"
                                                placeholder="{{ $fPlaceholder }}"
                                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 {{ in_array($fType, ['number', 'tel', 'email']) ? 'dir-ltr text-left' : '' }}">
                                        @endif

                                        @error('customFieldsForm.' . $fName)
                                        <p class="text-[11px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div
                            class="p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 flex items-center gap-2.5 text-xs text-indigo-900 dark:text-indigo-300">
                            <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>اطلاعات ثبت‌شده در این گام به شکل پایدار در بخش پروفایل و اطلاعات کاربری شما ذخیره می‌شوند و بعد از پایان تور نیز همواره قابل مشاهده و ویرایش هستند.</span>
                        </div>
                    </div>

                    <div
                        class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <button
                            type="button"
                            wire:click="closeCustomFieldsModal"
                            class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer">
                            انصراف
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>ذخیره اطلاعات و تأیید گام</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
