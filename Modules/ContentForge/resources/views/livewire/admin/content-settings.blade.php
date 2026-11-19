<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">تنظیمات ماژول محتوا</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت رفتار عمومی، سئو، ادیتور و لینک‌های کوتاه
                ماژول</p>
        </div>
        <div>
            <button wire:click="save"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/10 transition-all">
                ذخیره تنظیمات
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session()->has('success'))
        <div
            class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('warning'))
        <div
            class="p-4 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50 rounded-xl text-sm font-semibold">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Form sections --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Unified Theme Studio Redirect Card (Single Source of Truth) --}}
        <div
            class="p-6 bg-white dark:bg-gray-800 border-2 border-indigo-200 dark:border-indigo-900/60 rounded-2xl shadow-sm md:col-span-2 bg-gradient-to-br from-white to-indigo-50/20 dark:from-gray-800 dark:to-indigo-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-xl bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">مدیریت پوسته‌ها و ظاهر CRM</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        انتخاب، ساخت پوسته و بازنویسی تمام صفحات سامانه به‌صورت متمرکز فقط از طریق <strong>استودیو
                            پوسته‌ها</strong> انجام می‌شود.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px]">
                        <span
                            class="px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-100 dark:border-indigo-800">
                            پوسته فعال CRM: <span
                                class="font-mono">{{ $settings['crm']['theme_key'] ?? 'default' }}</span>
                        </span>
                        @if(!empty($currentThemeDetails['crm_overrides_count']))
                            <span
                                class="px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-100 dark:border-emerald-800">
                                {{ $currentThemeDetails['crm_overrides_count'] }} فایل اورراید فعال
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('user.content.themes') }}"
               class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition-all shrink-0 text-center">
                ورود به استودیو پوسته‌ها ←
            </a>
        </div>

        {{-- General Configuration --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-5">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                تنظیمات عمومی وبلاگ
            </h3>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">تعداد مقالات در هر صفحه آرشیو</label>
                <input type="number" wire:model.live="settings.general.posts_per_page"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">نام کلید قالب پیش‌فرض فرانت</label>
                <input type="text" wire:model.live="settings.general.default_theme_key"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">سرعت تخمینی مطالعه (کلمه در دقیقه)</label>
                    <input type="number" wire:model.live="settings.general.reading_time_wpm"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">سیستم نظرات وبلاگ</label>
                    <select wire:model.live="settings.general.enable_comments"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                        <option value="true">فعال</option>
                        <option value="false">غیرفعال</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- SEO Configuration --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-5">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                تنظیمات سئو (SEO)
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">تولید خودکار دسکریپشن</label>
                    <select wire:model.live="settings.seo.auto_generate_description"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                        <option value="true">بله</option>
                        <option value="false">خیر</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">تعداد کاراکتر دسکریپشن</label>
                    <input type="number" wire:model.live="settings.seo.description_length"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">تولید خودکار نشانه‌گذاری ساختاریافته
                    (Schema.org)</label>
                <select wire:model.live="settings.seo.auto_schema_markup"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                    <option value="true">فعال</option>
                    <option value="false">غیرفعال</option>
                </select>
            </div>
        </div>

        {{-- Short Link Configuration --}}
        <div class="p-6 bg-white dark:bg-gray-800 border rounded-2xl shadow-sm space-y-5">
            <h3 class="text-md font-bold text-gray-900 dark:text-white border-b pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                تنظیمات لینک‌های کوتاه
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">سیستم لینک کوتاه</label>
                    <select wire:model.live="settings.short_link.enabled"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                        <option value="true">فعال</option>
                        <option value="false">غیرفعال</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-gray-500 dark:text-gray-400">طول کد لینک کوتاه (کاراکتر)</label>
                    <input type="number" wire:model.live="settings.short_link.code_length"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs focus:outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-gray-500 dark:text-gray-400">پیشوند آدرس لینک کوتاه (URL Prefix)</label>
                <input type="text" wire:model.live="settings.short_link.prefix"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-mono text-left focus:outline-none">
                <span class="text-[10px] text-gray-400 block mt-1">مثال: انتخاب s آدرس لینک کوتاه را به شکل domain.com/s/code درمی‌آورد.</span>
            </div>
        </div>
    </div>

    {{-- Modal for Creating New Theme --}}
    @if($showCreateThemeModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-5 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">ایجاد پوسته جدید برای
                                CRM</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ساخت دایرکتوری و آماده‌سازی
                                فایل‌های اورراید</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeCreateThemeModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    {{-- Theme Key (English) --}}
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">شناسه انگلیسی پوسته (Theme Key) <span
                                class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="newThemeKey" placeholder="مثال: galaxy یا modern-crm"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl font-mono text-left focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @error('newThemeKey') <span
                            class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        <span class="text-[10px] text-gray-400 block">نام پوشه پوسته در resources/views/themes/{key} خواهد بود.</span>
                    </div>

                    {{-- Theme Name (Persian) --}}
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">نام نمایشی پوسته <span
                                class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="newThemeName" placeholder="مثال: پوسته کهکشان"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @error('newThemeName') <span
                            class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Theme Description --}}
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">توضیحات مختصر</label>
                        <input type="text" wire:model.defer="newThemeDescription"
                               placeholder="مثال: طراحی اختصاصی برای فرم‌های ایجاد کاربر و پنل"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    {{-- Initial Override View --}}
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">کپی خودکار صفحه برای شروع
                            اورراید</label>
                        <select wire:model.defer="initialOverrideView"
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="admin.users.edit">صفحه ایجاد و ویرایش کاربر (admin.users.edit) [پیشنهادی]
                            </option>
                            <option value="user.partials.sidebar">سایدبار منوهای اصلی CRM (user.partials.sidebar)
                            </option>
                            <option value="user.partials.topbar">تاپ‌بار و هدر بالای پنل (user.partials.topbar)</option>
                            <option value="user.dashboard">داشبورد اصلی کاربری (user.dashboard)</option>
                            <option value="layouts.user">اسکلت و لایوت کلی پنل CRM (layouts.user)</option>
                            <option value="resellerhub::user.dashboard">داشبورد ماژول نمایندگی
                                (resellerhub::user.dashboard)
                            </option>
                            <option value="accounting::index">داشبورد حسابداری (accounting::index)</option>
                            <option value="clients::index">لیست مشتریان (clients::index)</option>
                            <option value="none">پوسته خام (بدون کپی اولیه فایل)</option>
                        </select>
                        <span class="text-[10px] text-gray-400 block">فایل انتخابی بلافاصله در پوشه overrides پوسته جدید کپی می‌شود تا آماده ویرایش باشد.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    <button type="button" wire:click="closeCreateThemeModal"
                            class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-bold transition-all">
                        انصراف
                    </button>
                    <button type="button" wire:click="createTheme"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition-all">
                        ایجاد پوسته و فعال‌سازی فوری
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
