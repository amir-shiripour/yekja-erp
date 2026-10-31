@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
@endphp
{{-- White-Label Branding Customization Card --}}
<div
    class="mx-auto max-w-5xl sm:max-w-6xl rounded-3xl border border-gray-200/90 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 sm:p-8 shadow-xl shadow-gray-200/30 dark:shadow-none">
    {{-- Header --}}
    <div class="flex items-start gap-3.5 mb-6">
        <div
            class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 21a4 4 0 01-4-4 5 5 0 015-5 4 4 0 014 4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">برندینگ و ظاهر
                اختصاصی سامانه (وایت‌لیبل)</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشخص کنید پورتال با برند پیش‌فرض
                سامانه راه‌اندازی شود یا دارای لوگو، نام تجاری و تم کاملاً اختصاصی شما باشد.</p>
        </div>
    </div>

    {{-- Mode Toggle Tabs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        {{-- Option 1: Default Branding --}}
        <div wire:click="setWhiteLabelType('default')"
             class="relative p-4 sm:p-5 rounded-2xl border cursor-pointer transition-all duration-200 select-none {{ $whiteLabelType === 'default' ? 'border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 ring-2 ring-indigo-600/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/60 hover:border-gray-300 dark:hover:border-gray-600' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $whiteLabelType === 'default' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                برندینگ پیش‌فرض سامانه</h4>
                            <span
                                class="text-[9px] px-1.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 font-bold border border-blue-200/50">پیش‌فرض</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            سامانه با عنوان "{{ $defaultBrandTitle }}" و ظاهر سازمانی استاندارد
                            راه‌اندازی می‌گردد.
                        </p>
                    </div>
                </div>
                <div
                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 {{ $whiteLabelType === 'default' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-600' }}">
                    @if($whiteLabelType === 'default')
                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Option 2: Custom White-Label --}}
        <div wire:click="setWhiteLabelType('custom')"
             class="relative p-4 sm:p-5 rounded-2xl border cursor-pointer transition-all duration-200 select-none {{ $whiteLabelType === 'custom' ? 'border-purple-600 bg-purple-50/30 dark:bg-purple-950/20 ring-2 ring-purple-600/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/60 hover:border-gray-300 dark:hover:border-gray-600' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $whiteLabelType === 'custom' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white">
                                برندینگ وایت‌لیبل اختصاصی</h4>
                            @if(($whiteLabelFee['total'] ?? 0) > 0)
                                <span
                                    class="text-[9px] px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-200/50">+{{ ResellerHelper::formatPrice($whiteLabelFee['total'], $currencyLabel) }}</span>
                            @else
                                <span
                                    class="text-[9px] px-1.5 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 font-bold border border-purple-200/50">شخصی‌سازی کامل</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            لوگو، فاوآیکون، رنگ سازمانی، کپی‌رایت و پشتیبانی ۱۰۰٪ با نام و برند شما.
                        </p>
                    </div>
                </div>
                <div
                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 {{ $whiteLabelType === 'custom' ? 'border-purple-600 bg-purple-600' : 'border-gray-300 dark:border-gray-600' }}">
                    @if($whiteLabelType === 'custom')
                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($whiteLabelType === 'custom')
        {{-- 2-Column Desktop Grid: Form Inputs on Right, Live CRM Mockup Window on Left --}}
        <div
            class="pt-6 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Right Column: Form Controls (7 Columns on Large Screens) --}}
            <div class="lg:col-span-7 space-y-5">
                {{-- Brand Title & Footer Copyright --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            عنوان برند یا نام تجاری سامانه: <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.live.debounce.300ms="brandTitle"
                               class="block w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 py-2.5 px-3.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:text-white"
                               placeholder="مثال: سامانه اختصاصی کلینیک پارسا">
                        <p class="mt-1 text-xs text-gray-400">این نام در بالای منو، تب مرورگر و
                            مکاتبات درج می‌شود.</p>
                        @error('brandTitle')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            متن کپی‌رایت پاورقی (Footer):
                        </label>
                        <input type="text"
                               wire:model.live.debounce.300ms="brandFooterText"
                               class="block w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 py-2.5 px-3.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:text-white"
                               placeholder="مثال: تمامی حقوق این سامانه متعلق به کلینیک پارسا است.">
                        <p class="mt-1 text-xs text-gray-400">متن درج‌شده در انتهای صفحات پورتال
                            کاربری.</p>
                        @error('brandFooterText')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Support Phone & Email --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            تلفن پشتیبانی مشتریان:
                        </label>
                        <input type="text"
                               dir="ltr"
                               wire:model.live.debounce.300ms="supportPhone"
                               class="block w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 py-2.5 px-3.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:text-white text-left"
                               placeholder="۰۲۱-۸۸۸۸۸۸۸۸">
                        @error('supportPhone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            ایمیل پشتیبانی مشتریان:
                        </label>
                        <input type="email"
                               dir="ltr"
                               wire:model.live.debounce.300ms="supportEmail"
                               class="block w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 py-2.5 px-3.5 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 dark:text-white text-left"
                               placeholder="info@example.com">
                        @error('supportEmail')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Logo & Favicon Uploads --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Logo Upload --}}
                    @php
                        $logoPreview = null;
                        if ($brandLogo) {
                            try {
                                if (method_exists($brandLogo, 'temporaryUrl')) {
                                    $logoPreview = $brandLogo->temporaryUrl();
                                }
                            } catch (Throwable $e) {}
                        }
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                لوگوی اختصاصی برند:
                            </label>
                            <span class="text-xs text-gray-400">PNG, JPG, SVG تا ۲MB</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <label
                                class="relative flex-1 flex items-center justify-center px-3.5 py-2.5 border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl cursor-pointer transition-colors text-xs text-gray-500 dark:text-gray-400 select-none">
                                <input type="file" wire:model="brandLogo" accept="image/*"
                                       class="hidden">
                                <span wire:loading.remove wire:target="brandLogo"
                                      class="flex items-center gap-1.5 truncate">
                                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span
                                                        class="truncate">{{ $brandLogo ? 'تغییر لوگو...' : 'انتخاب لوگو...' }}</span>
                                                </span>
                                <span wire:loading.flex wire:target="brandLogo"
                                      class="items-center gap-1.5 text-purple-600">
                                                    <svg class="animate-spin h-3.5 w-3.5" fill="none"
                                                         viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12"
                                                                                     r="10" stroke="currentColor"
                                                                                     stroke-width="4"></circle><path
                                                            class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    <span>بارگذاری...</span>
                                                </span>
                            </label>

                            <div class="relative shrink-0">
                                <div
                                    class="w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1 flex items-center justify-center overflow-hidden shadow-xs">
                                    @if($logoPreview)
                                        <img src="{{ $logoPreview }}" alt="پیش‌نمایش لوگو"
                                             class="max-w-full max-h-full object-contain">
                                    @elseif($defaultBrandLogo)
                                        <img src="{{ asset('storage/' . $defaultBrandLogo) }}"
                                             alt="لوگوی پیش‌فرض"
                                             class="max-w-full max-h-full object-contain opacity-40">
                                    @else
                                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="1.5"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>
                                @if($brandLogo)
                                    <button type="button" wire:click="removeBrandLogo" title="حذف لوگو"
                                            class="absolute -top-1.5 -left-1.5 w-4 h-4 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @error('brandLogo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Favicon Upload --}}
                    @php
                        $faviconPreview = null;
                        if ($brandFavicon) {
                            try {
                                if (method_exists($brandFavicon, 'temporaryUrl')) {
                                    $faviconPreview = $brandFavicon->temporaryUrl();
                                }
                            } catch (Throwable $e) {}
                        }
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                فاوآیکون تب مرورگر:
                            </label>
                            <span class="text-xs text-gray-400">ICO, PNG تا ۱MB</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <label
                                class="relative flex-1 flex items-center justify-center px-3.5 py-2.5 border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl cursor-pointer transition-colors text-xs text-gray-500 dark:text-gray-400 select-none">
                                <input type="file" wire:model="brandFavicon"
                                       accept="image/x-icon,image/png,image/svg+xml,.ico"
                                       class="hidden">
                                <span wire:loading.remove wire:target="brandFavicon"
                                      class="flex items-center gap-1.5 truncate">
                                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    <span
                                                        class="truncate">{{ $brandFavicon ? 'تغییر فاوآیکون...' : 'انتخاب فاوآیکون...' }}</span>
                                                </span>
                                <span wire:loading.flex wire:target="brandFavicon"
                                      class="items-center gap-1.5 text-purple-600">
                                                    <svg class="animate-spin h-3.5 w-3.5" fill="none"
                                                         viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12"
                                                                                     r="10" stroke="currentColor"
                                                                                     stroke-width="4"></circle><path
                                                            class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    <span>بارگذاری...</span>
                                                </span>
                            </label>

                            <div class="relative shrink-0">
                                <div
                                    class="w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1 flex items-center justify-center overflow-hidden shadow-xs">
                                    @if($faviconPreview)
                                        <img src="{{ $faviconPreview }}" alt="پیش‌نمایش فاوآیکون"
                                             class="w-5 h-5 object-contain">
                                    @elseif($defaultBrandFavicon)
                                        <img src="{{ asset('storage/' . $defaultBrandFavicon) }}"
                                             alt="فاوآیکون پیش‌فرض"
                                             class="w-5 h-5 object-contain opacity-40">
                                    @else
                                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="1.5"
                                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    @endif
                                </div>
                                @if($brandFavicon)
                                    <button type="button" wire:click="removeBrandFavicon"
                                            title="حذف فاوآیکون"
                                            class="absolute -top-1.5 -left-1.5 w-4 h-4 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-xs cursor-pointer">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @error('brandFavicon')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Color Palette Selection Box --}}
                <div
                    class="p-4 sm:p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                رنگ سازمانی و تم اصلی سامانه:
                            </label>
                            <p class="text-xs text-gray-400 mt-0.5">تمامی دکمه‌ها، منوها و شاخص‌های
                                پورتال با این رنگ مزین می‌شوند.</p>
                        </div>

                        {{-- Color Input Box --}}
                        <div class="flex items-center gap-2">
                            <div
                                class="relative w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer shadow-xs">
                                <input type="color" wire:model.live="brandPrimaryColor"
                                       class="absolute -inset-2 w-14 h-14 cursor-pointer border-0 p-0">
                            </div>
                            <input type="text"
                                   dir="ltr"
                                   wire:model.live.debounce.300ms="brandPrimaryColor"
                                   class="w-24 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1.5 px-2 text-xs font-black text-center uppercase text-gray-900 dark:text-white"
                                   placeholder="{{ $defaultBrandColor }}">
                        </div>
                    </div>

                    {{-- Color Presets --}}
                    <div class="space-y-1.5 pt-1 border-t border-gray-200/60 dark:border-gray-700/60">
                        <span class="text-xs text-gray-400 font-bold block">پالت‌های محبوب و استاندارد:</span>
                        <div class="flex items-center gap-2 flex-wrap">
                            @foreach($colorPresets as $preset)
                                <button type="button"
                                        wire:click="setPrimaryColor('{{ $preset['hex'] }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border text-xs font-bold transition-all cursor-pointer {{ strtolower($brandPrimaryColor) === strtolower($preset['hex']) ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 ring-2 ring-purple-600/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800' }}">
                                                    <span class="w-3 h-3 rounded-full shrink-0 shadow-2xs"
                                                          style="background-color: {{ $preset['hex'] }}"></span>
                                    <span>{{ $preset['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Left Column: Live Realistic CRM Dashboard Mockup Window (5 Columns, Sticky) --}}
            <div class="lg:col-span-5 sticky top-6 self-start space-y-2.5">
                <div class="flex items-center justify-between px-1">
                                    <span
                                        class="text-xs font-black text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        پیش‌نمایش زنده ظاهر سامانه CRM:
                                    </span>
                    <span
                        class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/50">
                                        واکنش لحظه‌ای
                                    </span>
                </div>

                {{-- High-Fidelity Mockup Window Frame --}}
                <div
                    class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-900 text-white shadow-xl overflow-hidden">
                    {{-- macOS Style Browser Chrome Bar --}}
                    <div
                        class="px-3.5 py-2.5 bg-gray-800/90 border-b border-gray-700/80 flex items-center justify-between gap-2.5">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        </div>
                        <div
                            class="flex-1 max-w-[210px] bg-gray-900/90 rounded-lg px-2.5 py-1 text-xs text-gray-400 text-center truncate border border-gray-700/60 dir-ltr flex items-center justify-center gap-1.5">
                            @if($faviconPreview)
                                <img src="{{ $faviconPreview }}"
                                     class="w-3 h-3 object-contain shrink-0">
                            @elseif($defaultBrandFavicon)
                                <img src="{{ asset('storage/' . $defaultBrandFavicon) }}"
                                     class="w-3 h-3 object-contain shrink-0 opacity-60">
                            @else
                                <span class="w-2 h-2 rounded-full inline-block"
                                      style="background-color: {{ $brandPrimaryColor }}"></span>
                            @endif
                            <span class="truncate">
                                                @if($domainType === 'custom_domain' && !empty($customDomain))
                                    {{ $urlScheme }}{{ $customDomain }}
                                @else
                                    {{ $urlScheme }}{{ $baseDomain }}/{{ $subdomain ?: 'portal' }}
                                @endif
                                            </span>
                        </div>
                        <span class="text-[9px] font-bold text-gray-400 shrink-0">CRM Live</span>
                    </div>

                    {{-- Realistic RTL CRM Portal Dashboard Interface --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 p-3 sm:p-3.5 flex gap-2.5 text-xs select-none min-h-[320px]">
                        {{-- Sidebar (Right Column in RTL) --}}
                        <div
                            class="w-28 sm:w-32 bg-white dark:bg-gray-900 rounded-xl p-2 border border-gray-200/80 dark:border-gray-800 space-y-2.5 shrink-0 flex flex-col justify-between shadow-2xs">
                            <div class="space-y-2.5">
                                {{-- Brand Logo & Brand Title --}}
                                <div
                                    class="flex items-center gap-1.5 pb-2 border-b border-gray-100 dark:border-gray-800">
                                    <div
                                        class="w-6 h-6 rounded-lg flex items-center justify-center overflow-hidden shrink-0 border border-gray-100 dark:border-gray-800"
                                        style="background-color: {{ $brandPrimaryColor }}15">
                                        @if($logoPreview)
                                            <img src="{{ $logoPreview }}"
                                                 class="max-w-full max-h-full object-contain">
                                        @elseif($defaultBrandLogo)
                                            <img src="{{ asset('storage/' . $defaultBrandLogo) }}"
                                                 class="max-w-full max-h-full object-contain opacity-50">
                                        @else
                                            <span class="font-black text-xs"
                                                  style="color: {{ $brandPrimaryColor }}">
                                                                {{ mb_substr($brandTitle ?: ($user->name ?? 'C'), 0, 1) }}
                                                            </span>
                                        @endif
                                    </div>
                                    <span
                                        class="font-black text-xs text-gray-900 dark:text-white truncate"
                                        title="{{ $brandTitle ?: ($user->name ?? 'سامانه اختصاصی') }}">
                                                        {{ $brandTitle ?: ($user->name ?? 'سامانه اختصاصی') }}
                                                    </span>
                                </div>

                                {{-- Nav Items --}}
                                <div class="space-y-1">
                                    {{-- Active Item in Brand Color --}}
                                    <div
                                        class="px-2 py-1.5 rounded-lg font-bold text-xs text-white flex items-center gap-1.5 shadow-2xs transition-colors"
                                        style="background-color: {{ $brandPrimaryColor }}">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        <span>پیشخوان</span>
                                    </div>
                                    <div
                                        class="px-2 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>مشتریان</span>
                                    </div>
                                    <div
                                        class="px-2 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>فاکتورها</span>
                                    </div>
                                    <div
                                        class="px-2 py-1.5 rounded-lg text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <span>گزارش‌ها</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Support Phone Badge --}}
                            <div
                                class="p-1 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-[8px] text-gray-400 truncate">
                                پشتیبانی: <span class="font-bold text-gray-700 dark:text-gray-200"
                                                dir="ltr">{{ $supportPhone ? reseller_fa_num($supportPhone) : '۰۲۱...' }}</span>
                            </div>
                        </div>

                        {{-- Main Content Area (Left Column in RTL) --}}
                        <div class="flex-1 space-y-2 min-w-0 flex flex-col justify-between">
                            <div class="space-y-2">
                                {{-- Topbar --}}
                                <div
                                    class="bg-white dark:bg-gray-900 rounded-xl p-2 border border-gray-200/80 dark:border-gray-800 flex items-center justify-between gap-1.5 shadow-2xs">
                                                    <span
                                                        class="font-black text-gray-900 dark:text-white text-xs truncate">
                                                        {{ $brandTitle ?: 'پنل مدیریت CRM' }}
                                                    </span>
                                    <div class="flex items-center gap-1 shrink-0">
                                                        <span
                                                            class="inline-flex items-center gap-0.5 text-[8px] text-white px-2 py-0.5 rounded-md font-bold shadow-2xs"
                                                            style="background-color: {{ $brandPrimaryColor }}">
                                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            <span>عملیات جدید</span>
                                                        </span>
                                        <div
                                            class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                    </div>
                                </div>

                                {{-- Metric Stat Cards --}}
                                <div class="grid grid-cols-2 gap-1.5">
                                    <div
                                        class="p-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 space-y-0.5 shadow-2xs">
                                        <span class="text-[8px] text-gray-400 block">مشتریان فعال</span>
                                        <div class="flex items-center justify-between">
                                                            <span
                                                                class="font-black text-gray-900 dark:text-white text-xs">۱۲۸ نفر</span>
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                  style="background-color: {{ $brandPrimaryColor }}"></span>
                                        </div>
                                    </div>
                                    <div
                                        class="p-2 rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 space-y-0.5 shadow-2xs">
                                        <span class="text-[8px] text-gray-400 block">فروش دوره</span>
                                        <div class="flex items-center justify-between">
                                                            <span
                                                                class="font-black text-gray-900 dark:text-white text-xs">۲۴ سفارش</span>
                                            <span
                                                class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Mini Table Mockup --}}
                                <div
                                    class="bg-white dark:bg-gray-900 rounded-xl p-2 border border-gray-200/80 dark:border-gray-800 space-y-1 shadow-2xs">
                                    <div
                                        class="flex items-center justify-between pb-1 border-b border-gray-100 dark:border-gray-800 text-[8px] font-bold text-gray-400">
                                        <span>آخرین فعالیت‌ها</span>
                                        <span>وضعیت</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[9px] py-0.5">
                                        <span class="text-gray-700 dark:text-gray-300 truncate">سفارش #۱۰۴۲</span>
                                        <span
                                            class="px-1.5 py-0.2 rounded text-[7px] font-bold text-white shrink-0"
                                            style="background-color: {{ $brandPrimaryColor }}">تکمیل شد</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[9px] py-0.5">
                                        <span class="text-gray-700 dark:text-gray-300 truncate">ثبت پرونده جدید</span>
                                        <span
                                            class="px-1.5 py-0.2 rounded text-[7px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 shrink-0">فعال</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Mock Footer Copyright --}}
                            <div
                                class="pt-1.5 text-center text-[8px] text-gray-400 border-t border-gray-200/60 dark:border-gray-800 truncate"
                                title="{{ $brandFooterText ?: ('تمامی حقوق برای ' . ($brandTitle ?: 'سامانه') . ' محفوظ است.') }}">
                                {{ $brandFooterText ?: ('تمامی حقوق برای ' . ($brandTitle ?: 'سامانه') . ' محفوظ است.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Validation Errors --}}
@error('package')
<div
    class="mx-auto max-w-5xl sm:max-w-6xl rounded-xl bg-red-50 p-3 text-xs text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-100">{{ $message }}</div>
@enderror

{{-- Submit Button --}}
<div class="flex justify-center">
    <button wire:click="nextStep"
            type="button"
            class="w-full sm:w-auto min-w-[240px] flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
            wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="nextStep">مرحله بعد: فاکتور و پرداخت</span>
        <svg wire:loading.remove wire:target="nextStep" class="h-4 w-4 rtl:rotate-180" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
        <span wire:loading.flex wire:target="nextStep" class="items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle
                                    class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle><path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            در حال پردازش...
                        </span>
    </button>
</div>
