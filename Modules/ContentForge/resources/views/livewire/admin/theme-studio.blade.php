<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                <span
                    class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </span>
                استودیو پوسته‌ها و ظاهر سامانه (Theme Studio)
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مدیریت یکپارچه ظاهر پنل CRM، وب‌سایت عمومی، ساخت
                پوسته و بازنویسی صفحات تمام ماژول‌ها</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ url('/settings#themes') }}"
               class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl text-xs font-bold transition-all">
                تنظیم و اعمال پوسته‌ها در تنظیمات سیستم ←
            </a>
            <button type="button" wire:click="openCreateThemeModal"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                ایجاد پوسته جدید
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session()->has('success'))
        <div
            class="p-4 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl text-xs font-bold">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div
            class="p-4 bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 rounded-2xl text-xs font-bold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Summary Banner: Clear Separation of CRM vs Public Website --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Active CRM Panel Theme --}}
        <div
            class="p-5 bg-white dark:bg-gray-800 border-2 border-indigo-100 dark:border-indigo-900/50 rounded-2xl shadow-xs flex flex-col justify-between gap-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 uppercase">پنل مدیریت CRM (داشبورد و ماژول‌ها)</span>
                        <h3 class="text-base font-black text-gray-900 dark:text-white mt-0.5">
                            پوسته فعال: <span
                                class="font-mono text-indigo-600 dark:text-indigo-400">{{ $availableThemes[$activeCrmTheme]['name'] ?? $activeCrmTheme }}</span>
                        </h3>
                    </div>
                </div>
                <span
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $crmOverridesEnabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400' }}">
                    {{ $crmOverridesEnabled ? 'بازنویسی فعال' : 'حالت پیش‌فرض هسته' }}
                </span>
            </div>
            <div
                class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700/80 text-xs">
                <span class="text-gray-500 dark:text-gray-400">مخصوص مسیرهای داخلی پنل کاربری</span>
                <a href="{{ url('/settings#themes') }}"
                   class="px-3 py-1.5 rounded-lg font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 transition-colors">
                    تغییر و اعمال در تنظیمات سیستم ←
                </a>
            </div>
        </div>

        {{-- Active Public Website Theme --}}
        <div
            class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs flex flex-col justify-between gap-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-sky-600 dark:text-sky-400 uppercase">وب‌سایت عمومی (صفحه اول و لندینگ)</span>
                        <h3 class="text-base font-black text-gray-900 dark:text-white mt-0.5">
                            پوسته فعال: <span
                                class="font-mono text-sky-600 dark:text-sky-400">{{ $availableThemes[$activeWebTheme]['name'] ?? $activeWebTheme }}</span>
                        </h3>
                    </div>
                </div>
                <a href="{{ url('/') }}" target="_blank"
                   class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-[11px] font-bold transition-colors">
                    مشاهده سایت ↗
                </a>
            </div>
            <div class="pt-3 border-t border-gray-100 dark:border-gray-700/80 text-xs text-gray-500 dark:text-gray-400">
                فقط پوسته‌هایی که فایل صفحه اول (`index.blade.php`) دارند برای وب‌سایت عمومی قابل انتخاب هستند تا تداخلی
                پیش نیاید.
            </div>
        </div>
    </div>

    {{-- Themes Visual Grid --}}
    <div class="space-y-4">
        <h2 class="text-sm font-black text-gray-800 dark:text-gray-200">پوسته‌های نصب‌شده در سامانه
            ({{ count($availableThemes) }})</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($availableThemes as $tKey => $theme)
                @php
                    $isCrmActive = ($activeCrmTheme === $tKey);
                    $isWebActive = ($activeWebTheme === $tKey);
                @endphp
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border {{ $isCrmActive ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700' }} p-5 shadow-xs flex flex-col justify-between gap-5 transition-all hover:shadow-md">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ $theme['name'] }}</h3>
                                <span
                                    class="text-[11px] font-mono text-gray-400">{{ $tKey }} • v{{ $theme['version'] }}</span>
                            </div>
                            <div class="flex flex-wrap gap-1 justify-end">
                                @if($isCrmActive)
                                    <span
                                        class="px-2 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg text-[10px] font-bold">
                                        فعال در CRM
                                    </span>
                                @endif
                                @if($isWebActive)
                                    <span
                                        class="px-2 py-0.5 bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-lg text-[10px] font-bold">
                                        فعال در سایت
                                    </span>
                                @endif
                                @if(!$theme['has_web_index'])
                                    <span
                                        class="px-2 py-0.5 bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 rounded-lg text-[10px] font-bold">
                                        مخصوص CRM
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 min-h-[2rem]">
                            {{ $theme['description'] }}
                        </p>

                        <div
                            class="flex items-center justify-between text-[11px] pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400">فایل‌های اورراید CRM:</span>
                            <span
                                class="font-bold {{ $theme['total_overrides'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                {{ $theme['total_overrides'] }} فایل
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                    wire:click="openEditThemeModal('{{ $tKey }}')"
                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-[11px] font-bold transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                ویرایش مشخصات
                            </button>

                            <button type="button"
                                    wire:click="openOverridesModal('{{ $tKey }}')"
                                    class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[11px] font-bold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                                فایل‌های اورراید ({{ $theme['total_overrides'] }})
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Modal: Create New Theme --}}
    @if($showCreateThemeModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">ایجاد پوسته جدید برای CRM</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پوسته با علامت مخصوص CRM ساخته می‌شود
                            تا هیچ تداخلی با سایت عمومی نداشته باشد</p>
                    </div>
                    <button type="button" wire:click="closeCreateThemeModal"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">شناسه انگلیسی پوسته (Theme Key) <span
                                class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="newThemeKey" placeholder="مثال: galaxy"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl font-mono text-left focus:outline-none">
                        @error('newThemeKey') <span
                            class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">نام نمایشی پوسته <span
                                class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="newThemeName" placeholder="مثال: پوسته کهکشان"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none">
                        @error('newThemeName') <span
                            class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">توضیحات</label>
                        <input type="text" wire:model.defer="newThemeDescription" placeholder="توضیح مختصر..."
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">کپی خودکار صفحه اولیه</label>
                        <select wire:model.defer="initialOverrideView"
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none">
                            @foreach($commonTargets as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach($items as $vKey => $vLabel)
                                        <option value="{{ $vKey }}">{{ $vLabel }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                            <option value="none">پوسته خام (بدون فایل اولیه)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    <button type="button" wire:click="closeCreateThemeModal"
                            class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-bold">
                        انصراف
                    </button>
                    <button type="button" wire:click="createTheme"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20">
                        ایجاد و فعال‌سازی
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Manage & Add Overrides for a Theme --}}
    @if($showOverridesModal && $inspectingTheme)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">
                            فایل‌های اورراید پوسته: <span
                                class="text-indigo-600 dark:text-indigo-400">{{ $inspectingTheme['name'] }}</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-mono">
                            resources/views/themes/{{ $inspectingThemeKey }}/overrides/</p>
                    </div>
                    <button type="button" wire:click="closeOverridesModal"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if(session()->has('override_success'))
                    <div
                        class="p-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-bold">
                        {{ session('override_success') }}
                    </div>
                @endif
                @if(session()->has('override_error'))
                    <div
                        class="p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 rounded-xl text-xs font-bold">
                        {{ session('override_error') }}
                    </div>
                @endif

                {{-- Add new override section --}}
                <div
                    class="p-4 bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 rounded-2xl space-y-3">
                    <h4 class="text-xs font-black text-gray-800 dark:text-gray-200">+ افزودن صفحه جدید جهت
                        شخصی‌سازی</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-1">انتخاب از صفحات پرکاربرد CRM و
                                ماژول‌ها:</label>
                            <select wire:model.defer="selectedOverrideTarget"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none">
                                @foreach($commonTargets as $group => $items)
                                    <optgroup label="{{ $group }}">
                                        @foreach($items as $vKey => $vLabel)
                                            <option value="{{ $vKey }}">{{ $vLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-1">یا نام ویوی دلخواه (اختیاری - مثال:
                                tasks::index):</label>
                            <input type="text" wire:model.defer="customOverrideView"
                                   placeholder="مثال: resellerhub::user.dashboard"
                                   class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl font-mono text-left focus:outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" wire:click="addOverrideFile"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all">
                            کپی و آماده‌سازی فایل برای ویرایش
                        </button>
                    </div>
                </div>

                {{-- Existing overrides list --}}
                <div class="space-y-2">
                    <h4 class="text-xs font-black text-gray-800 dark:text-gray-200">فایل‌های موجود در این پوسته
                        ({{ count($inspectingTheme['overridden_files']) }}):</h4>
                    @if(empty($inspectingTheme['overridden_files']))
                        <div
                            class="p-6 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                            هنوز هیچ فایلی در پوشه overrides این پوسته کپی نشده است. از بخش بالا صفحه مدنظر خود را اضافه
                            کنید.
                        </div>
                    @else
                        <div
                            class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl">
                            @foreach($inspectingTheme['overridden_files'] as $file)
                                <div
                                    class="p-3 flex items-center justify-between gap-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/40 text-xs">
                                    <span class="font-mono text-gray-700 dark:text-gray-200 truncate" dir="ltr">overrides/{{ $file }}</span>
                                    <button type="button"
                                            wire:click="deleteOverrideFile('{{ $file }}')"
                                            wire:confirm="آیا از حذف این فایل اورراید مطمئن هستید؟"
                                            class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 rounded-lg text-[11px] font-bold shrink-0">
                                        حذف
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-3 border-t border-gray-100 dark:border-gray-700/80">
                    <button type="button" wire:click="closeOverridesModal"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Edit Theme Metadata --}}
    @if($showEditThemeModal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="relative bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">ویرایش مشخصات پوسته (<span
                                class="font-mono text-indigo-600 dark:text-indigo-400">{{ $editingThemeKey }}</span>)
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">به‌روزرسانی اطلاعات فایل
                            theme.json</p>
                    </div>
                    <button type="button" wire:click="closeEditThemeModal"
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">نام نمایشی پوسته <span
                                class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="editThemeName"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none">
                        @error('editThemeName') <span
                            class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">توضیحات</label>
                        <input type="text" wire:model.defer="editThemeDescription"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 dark:text-gray-300">نسخه (Version)</label>
                        <input type="text" wire:model.defer="editThemeVersion"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl font-mono text-left focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                    <button type="button" wire:click="closeEditThemeModal"
                            class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-bold">
                        انصراف
                    </button>
                    <button type="button" wire:click="updateTheme"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20">
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
