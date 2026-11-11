@if ($showBrandingModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             wire:click="closeBrandingModal"></div>
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
                                      d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">
                                شخصی‌سازی برند و وایت‌لیبل (White-Label)
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                هویت بصری، عنوان و مشخصات ارتباطی برند اختصاصی خود را تنظیم کنید.
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeBrandingModal"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="saveBranding">
                    <div class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                عنوان برند / نام نمایشی سامانه <span class="text-red-500 font-extrabold">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="brandTitle"
                                placeholder="مثال: کلینیک تخصصی دکتر رحیمی"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                            />
                            @error('brandTitle')
                            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                رنگ سازمانی و تم اصلی <span class="text-red-500 font-extrabold">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input
                                    type="color"
                                    wire:model.live="brandPrimaryColor"
                                    class="w-12 h-10 p-1 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800"
                                />
                                <input
                                    type="text"
                                    wire:model="brandPrimaryColor"
                                    dir="ltr"
                                    class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                />
                                <div class="w-8 h-8 rounded-xl shrink-0 shadow-inner"
                                     style="background-color: {{ $brandPrimaryColor }}"></div>
                            </div>
                            @error('brandPrimaryColor')
                            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ready-made / default brand color presets --}}
                        <div class="space-y-1.5 pt-1">
                            <span class="block text-xs font-bold text-gray-400">
                                پالت رنگ‌های آماده و پیش‌فرض
                            </span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach($colorPresets as $preset)
                                    <button
                                        type="button"
                                        wire:key="brand-color-preset-{{ $loop->index }}"
                                        wire:click="selectBrandPrimaryColor('{{ $preset['hex'] }}')"
                                        title="{{ $preset['name'] }}"
                                        class="flex items-center gap-1.5 p-2 rounded-xl border transition-all cursor-pointer text-start {{ strtolower((string) $brandPrimaryColor) === strtolower($preset['hex']) ? 'border-indigo-500 ring-2 ring-indigo-500/25 bg-indigo-50/60 dark:bg-indigo-950/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}"
                                    >
                                        <span class="w-4 h-4 rounded-full shrink-0 shadow-2xs"
                                              style="background-color: {{ $preset['hex'] }}"></span>
                                        <span
                                            class="text-xs font-bold text-gray-600 dark:text-gray-300 truncate">
                                            {{ $preset['name'] }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    شماره تلفن پشتیبانی
                                </label>
                                <input
                                    type="tel"
                                    wire:model="supportPhone"
                                    placeholder="۰۲۱-۸۸۸۸۸۸۸۸"
                                    dir="ltr"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs text-left focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                />
                                <p class="text-xs text-gray-400 leading-relaxed">
                                    ورود ارقام فارسی یا انگلیسی مجاز است؛ شماره به صورت فارسی نمایش داده می‌شود.
                                </p>
                                @error('supportPhone')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    ایمیل پشتیبانی
                                </label>
                                <input
                                    type="email"
                                    wire:model="supportEmail"
                                    placeholder="support@brand.ir"
                                    dir="ltr"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all font-mono"
                                />
                                @error('supportEmail')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                متن کپی‌رایت فوتر
                            </label>
                            <input
                                type="text"
                                wire:model="brandFooterText"
                                placeholder="تمامی حقوق محفوظ است."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                            />
                            @error('brandFooterText')
                            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            wire:click="closeBrandingModal"
                            class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                        >
                            انصراف
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
                        >
                            <span>ذخیره وایت‌لیبل</span>
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
