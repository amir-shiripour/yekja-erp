<div class="bg-white dark:bg-gray-800/80 p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-6 backdrop-blur-xl">
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
            <span
                class="p-2.5 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
            </span>
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white">برندینگ و ظاهر اختصاصی سامانه
                    (وایت‌ لیبل)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشخصات هویت بصری، لوگو، رنگ سازمانی و اطلاعات
                    تماس این CRM</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if($isCustomBranding)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 rounded-full text-xs font-bold border border-purple-200/60 dark:border-purple-800/40">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    وایت‌ لیبل کاملاً اختصاصی خریدار
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 rounded-full text-xs font-bold border border-blue-200/60 dark:border-blue-800/40">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    برندینگ پیش‌ فرض نماینده
                </span>
            @endif

            <a href="{{ route('reseller.tenants.edit', $tenant->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-xl transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>ویرایش برندینگ</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- 1. Logo & Favicon Visual Card --}}
        <div
            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-3">
            <span class="text-xs font-bold text-gray-400 block">تصاویر و نماد تجاری</span>
            <div class="flex items-center gap-4">
                {{-- Logo Box --}}
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="w-14 h-14 rounded-2xl border border-gray-200 dark:border-gray-700 p-1 bg-gray-50 dark:bg-gray-850 flex items-center justify-center overflow-hidden shadow-2xs">
                        @if($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="لوگو" class="max-w-full max-h-full object-contain">
                        @else
                            <span
                                class="w-8 h-8 rounded-xl text-white font-black text-xs flex items-center justify-center shadow-xs"
                                style="background-color: {{ $brandPrimaryColor }};">
                                {{ mb_substr($brandTitle ?: $tenant->name, 0, 1) }}
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 font-bold">لوگو</span>
                </div>

                {{-- Favicon Box --}}
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="w-14 h-14 rounded-2xl border border-gray-200 dark:border-gray-700 p-2.5 bg-gray-50 dark:bg-gray-850 flex items-center justify-center overflow-hidden shadow-2xs">
                        @if($brandFaviconUrl)
                            <img src="{{ $brandFaviconUrl }}" alt="فاوآیکون" class="w-6 h-6 object-contain">
                        @else
                            <span class="w-4 h-4 rounded-full"
                                  style="background-color: {{ $brandPrimaryColor }};"></span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 font-bold">فاوآیکون</span>
                </div>
            </div>
        </div>

        {{-- 2. Brand Title & Primary Color Card --}}
        <div
            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-3 flex flex-col justify-between">
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-gray-400 block">عنوان برند و رنگ سازمانی</span>
                <h4 class="text-sm font-black text-gray-900 dark:text-white truncate">
                    {{ $brandTitle ?: $tenant->name }}
                </h4>
            </div>
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                <span class="w-5 h-5 rounded-full shadow-2xs shrink-0"
                      style="background-color: {{ $brandPrimaryColor }};"></span>
                <span
                    class="text-xs font-bold text-gray-800 dark:text-gray-200 dir-ltr uppercase">{{ $brandPrimaryColor }}</span>
                <span class="text-xs text-gray-400">رنگ اصلی تم</span>
            </div>
        </div>

        {{-- 3. Support Contacts Card --}}
        <div
            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-2 flex flex-col justify-between">
            <span class="text-xs font-bold text-gray-400 block">پشتیبانی اختصاصی مشتریان</span>
            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">تلفن:</span>
                    <span
                        class="font-bold text-gray-800 dark:text-gray-200 dir-ltr">{{ $supportPhone ? reseller_fa_num($supportPhone) : 'پیش‌فرض نماینده' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">ایمیل:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200 dir-ltr truncate max-w-[130px]"
                          title="{{ $supportEmail ?: 'پیش‌فرض نماینده' }}">{{ $supportEmail ?: 'پیش‌فرض نماینده' }}</span>
                </div>
            </div>
        </div>

        {{-- 4. Footer Copyright Card --}}
        <div
            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 space-y-2 flex flex-col justify-between">
            <span class="text-xs font-bold text-gray-400 block">کپی‌رایت پاورقی (Footer)</span>
            <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed">
                {{ $brandFooterText ?: ('تمامی حقوق متعلق به ' . ($brandTitle ?: $tenant->name) . ' است.') }}
            </p>
        </div>
    </div>
</div>
