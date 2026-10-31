@php
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    $discoveredList = $discoveredModels ?? [];
    $healthList = $tenancyHealth ?? [];
@endphp

{{-- Tab: Tenancy & Zero-Touch Dynamic Models --}}
<div x-show="activeTab === 'tenancy'"
     x-cloak
     x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     id="tabpanel-tenancy"
     role="tabpanel"
     aria-labelledby="tab-tenancy"
     x-data="tenancyTabManager({
        items: {{ Js::from($healthList) }},
        discovered: {{ Js::from($discoveredList) }},
        perPage: 8
     })"
     class="space-y-6">

    {{-- Bento Hero Header & Live Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Stat Card 1: Engine Health --}}
        <div
            class="bg-gradient-to-br from-indigo-900/10 via-purple-900/5 to-transparent dark:from-indigo-950/40 dark:via-purple-950/20 p-5 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span
                    class="p-2.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-400/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>۱۰۰٪ فعال و پایدار</span>
                </span>
            </div>
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400">موتور ایزولاسیون داده‌ها</h4>
            <div class="text-base font-black text-gray-900 dark:text-white mt-1">Zero-Touch Tenancy</div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">اسکوپ‌گذاری پویا بدون دستکاری سورس</p>
        </div>

        {{-- Stat Card 2: Total Guarded Entities --}}
        <div
            class="bg-white dark:bg-gray-800/80 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span
                    class="p-2.5 rounded-xl bg-purple-500/10 dark:bg-purple-400/10 text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
                <span class="text-xs font-bold text-gray-400">Guarded</span>
            </div>
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400">موجودیت‌های محافظت‌شده</h4>
            <div class="text-2xl font-black text-gray-900 dark:text-white mt-1"
                 x-text="toPersianDigits(items.length)"></div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">مدل تجاری دارای مالکیت مستاجر</p>
        </div>

        {{-- Stat Card 3: Connected Modules --}}
        <div
            class="bg-white dark:bg-gray-800/80 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="p-2.5 rounded-xl bg-blue-500/10 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </span>
                <span class="text-xs font-bold text-gray-400">Modules</span>
            </div>
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400">دامنه‌های ماژولار متصل</h4>
            <div class="text-2xl font-black text-gray-900 dark:text-white mt-1"
                 x-text="toPersianDigits(availableModules.length) + ' ماژول'"></div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 truncate"
               x-text="availableModules.map(m => m.label).slice(0, 5).join('، ')"></p>
        </div>

        {{-- Stat Card 4: Anti-Tampering Security Guard --}}
        <div
            class="bg-white dark:bg-gray-800/80 p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span
                    class="p-2.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-400/10 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Guarded</span>
            </div>
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400">سپر امنیتی ضد دستکاری</h4>
            <div class="text-sm font-black text-emerald-700 dark:text-emerald-300 mt-1">Anti-Tampering Active</div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">مسدودسازی بلادرنگ تغییر شناسه مستاجر</p>
        </div>
    </div>

    {{-- Controls & Actions Bar --}}
    <div
        class="bg-white dark:bg-gray-800/80 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1 max-w-md">
                <input type="text"
                       x-model="searchQuery"
                       @input="currentPage = 1"
                       placeholder="جستجوی موجودیت، ماژول یا عنوان..."
                       class="w-full ps-10 pe-4 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                <span class="absolute inset-y-0 start-0 ps-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                        @click="showAddForm = !showAddForm"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>افزودن مدل جدید</span>
                </button>

                {{-- Open Sync Modal Button --}}
                <button type="button"
                        @click="openSyncModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs sm:text-sm font-bold shadow-sm shadow-indigo-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>همگام‌سازی سلامت</span>
                </button>
            </div>
        </div>

        {{-- Module Filter Pills --}}
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs">
            <span class="text-gray-400 font-bold me-1">فیلتر دامنه:</span>
            <button type="button" @click="selectedModule = 'all'; currentPage = 1"
                    :class="selectedModule === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-colors cursor-pointer">
                همه (<span x-text="toPersianDigits(items.length)"></span>)
            </button>
            <template x-for="mod in availableModules" :key="mod.key">
                <button type="button" @click="selectedModule = mod.key; currentPage = 1"
                        :class="selectedModule === mod.key ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-colors cursor-pointer">
                    <span x-text="mod.label"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- Expandable Add Model Card (With Auto-Discovery Dropdown & Manual Input) --}}
    <div x-show="showAddForm"
         x-cloak
         x-transition:enter="transition ease-out duration-200 transform opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150 transform opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/90 rounded-2xl p-5 border border-indigo-200 dark:border-indigo-800/60 shadow-md space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                <span class="p-1 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </span>
                <span>ثبت موجودیت جدید در سامانه چندمستاجری</span>
            </h4>
            <button type="button" @click="showAddForm = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Mode Switch Tabs: Quick Select vs Manual Input --}}
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
            <button type="button"
                    @click="addMode = 'select'"
                    :class="addMode === 'select' ? 'text-indigo-600 dark:text-indigo-400 font-bold border-b-2 border-indigo-600 pb-1 -mb-3.5' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="text-xs cursor-pointer flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span>انتخاب از ماژول‌های سیستم (بدون نیاز به کد)</span>
            </button>
            <button type="button"
                    @click="addMode = 'manual'"
                    :class="addMode === 'manual' ? 'text-indigo-600 dark:text-indigo-400 font-bold border-b-2 border-indigo-600 pb-1 -mb-3.5' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="text-xs cursor-pointer flex items-center gap-1.5 ms-4">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                <span>ورود دستی کلاس مدل (توسعه‌دهندگان)</span>
            </button>
        </div>

        <form method="POST" action="{{ route('reseller.settings.tenancy.add-model') }}" class="space-y-3">
            @csrf

            {{-- Hidden input containing the chosen class for submission --}}
            <input type="hidden" name="model_class"
                   :value="addMode === 'select' ? selectedDiscoveredClass : manualModelClass" required>

            {{-- MODE 1: Quick Select from Discovered Models (Accounting-style Custom Searchable Dropdown) --}}
            <div x-show="addMode === 'select'" class="space-y-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    موجودیت مورد نظر را انتخاب کنید:
                </label>
                <div class="flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center">
                    {{-- Searchable Dropdown Matching Accounting Expenses Create UX --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        get filteredOptions() {
                            if (!this.search.trim()) return discovered;
                            const q = this.search.toLowerCase();
                            return discovered.filter(o =>
                                (o.model && o.model.toLowerCase().includes(q)) ||
                                (o.module && o.module.toLowerCase().includes(q)) ||
                                (o.module_label && o.module_label.toLowerCase().includes(q)) ||
                                (o.label && o.label.toLowerCase().includes(q)) ||
                                (o.class && o.class.toLowerCase().includes(q))
                            );
                        },
                        select(opt) {
                            selectedDiscoveredClass = opt ? opt.class : '';
                            manualModelClass = selectedDiscoveredClass;
                            this.open = false;
                            this.search = '';
                        },
                        getSelectedTitle() {
                            let found = discovered.find(o => o.class === selectedDiscoveredClass);
                            if (!found) return 'یک مدل از ماژول‌های موجود را انتخاب کنید...';
                            return (found.module_label || found.module) + ' ← ' + found.model;
                        },
                        getSelectedModel() {
                            return discovered.find(o => o.class === selectedDiscoveredClass) || null;
                        }
                    }" class="relative flex-1" :class="{ 'z-50': open }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between cursor-pointer text-start py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm">
                            <div class="flex items-center gap-2 truncate">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </span>
                                <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                <template x-if="getSelectedModel()">
                                    <span
                                        class="text-[10px] text-gray-400 dark:text-gray-500 hidden md:inline truncate max-w-xs"
                                        x-text="'(' + getSelectedModel().class + ')'"></span>
                                </template>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200"
                                 :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak
                             class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[420px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                            <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                <input type="text" x-model="search" placeholder="جستجوی نام مدل، ماژول یا کلاس..."
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            <template x-for="opt in filteredOptions" :key="opt.class">
                                <div @click="select(opt)"
                                     class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                     :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': selectedDiscoveredClass === opt.class }">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="text-gray-800 dark:text-gray-100 font-semibold"
                                              x-text="opt.model"></span>
                                        <span class="text-[10px] text-gray-400 truncate hidden sm:inline"
                                              x-text="opt.class"></span>
                                    </div>
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded-full border shrink-0 font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50"
                                        x-text="opt.module_label || opt.module"></span>
                                </div>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                هیچ مدلی یافت نشد
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            :disabled="!selectedDiscoveredClass"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs sm:text-sm transition-all active:scale-95 shrink-0 cursor-pointer w-full sm:w-auto">
                        اتصال و ایزوله‌سازی مدل
                    </button>
                </div>
                <p class="text-[11px] text-gray-400">
                    سیستم به صورت خودکار تمام مدل‌های موجود در ماژول‌های نصب‌شده را شناسایی کرده و نیازی به دانش کدنویسی
                    ندارد.
                </p>
            </div>

            {{-- MODE 2: Manual Input --}}
            <div x-show="addMode === 'manual'" class="space-y-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    نام کامل کلاس PHP مدل:
                </label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="flex-1">
                        <input type="text"
                               name="model_class_manual"
                               x-model="manualModelClass"
                               placeholder="مثال: Modules\Booking\Entities\CustomAppointment"
                               dir="ltr"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    </div>
                    <button type="submit"
                            :disabled="!manualModelClass"
                            class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black dark:bg-indigo-600 dark:hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs sm:text-sm transition-all active:scale-95 shrink-0 cursor-pointer">
                        ثبت و فعال‌سازی
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Entities Table with Secure Presentation --}}
    <div
        class="bg-white dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
            <div class="space-y-0.5">
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">
                    موجودیت‌های تجاری تحت پایش چندمستاجری
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    ایزوله‌سازی خودکار داده‌های هر مستاجر در لایه کوئری‌های الکوئنت با تفکیک مدل‌های سیستمی و سفارشی
                </p>
            </div>
            <span
                class="px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold">
                <span x-text="toPersianDigits(filteredItems.length)"></span> مورد
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                <tr class="bg-gray-50/70 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs font-bold border-b border-gray-100 dark:border-gray-700/60">
                    <th scope="col" class="px-5 py-3.5">موجودیت تجاری</th>
                    <th scope="col" class="px-5 py-3.5">دامنه ماژول</th>
                    <th scope="col" class="px-5 py-3.5">سطح حفاظت و گارد امنیتی</th>
                    <th scope="col" class="px-5 py-3.5 text-center">نوع موجودیت</th>
                    <th scope="col" class="px-5 py-3.5 text-center">وضعیت پایش</th>
                    <th scope="col" class="px-5 py-3.5 text-center">مدیریت</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs sm:text-sm">
                <template x-for="item in paginatedItems" :key="item.raw_class">
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        {{-- Entity & Friendly Title --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0"
                                        :class="getModuleAvatarClass(item.module)">
                                        <span
                                            x-text="item.entity_label ? item.entity_label.substring(0, 1) : item.model.substring(0, 1)"></span>
                                    </span>
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white"
                                         x-text="item.entity_label || item.model"></div>
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 tracking-tight"
                                         dir="ltr" x-text="item.raw_class"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Module Domain --}}
                        <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold"
                                      :class="getModuleBadgeClass(item.module)"
                                      x-text="item.module_label || item.module"></span>
                        </td>

                        {{-- Protection Mechanism & Guards (No DB Column Leaks) --}}
                        <td class="px-5 py-3.5">
                            <div
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/40">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>اسکوپ خودکار + گارد ضد دستکاری</span>
                            </div>
                        </td>

                        {{-- Entity Type (Core System vs Custom) --}}
                        <td class="px-5 py-3.5 text-center">
                            <template x-if="item.is_system">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300"
                                        title="موجودیت الزامی و پیش‌فرض هسته سامانه CRM">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span>پیش‌فرض هسته</span>
                                    </span>
                            </template>
                            <template x-if="!item.is_system">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50">
                                        <span>سفارشی</span>
                                    </span>
                            </template>
                        </td>

                        {{-- Monitoring Status --}}
                        <td class="px-5 py-3.5 text-center">
                            <template x-if="item.status === 'active'">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>ایزوله و فعال</span>
                                    </span>
                            </template>
                            <template x-if="item.status !== 'active'">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>نیاز به بررسی</span>
                                    </span>
                            </template>
                        </td>

                        {{-- Actions (Protected Core vs Deletable Custom) --}}
                        <td class="px-5 py-3.5 text-center">
                            <template x-if="item.is_system">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-bold select-none"
                                          title="این موجودیت پیش‌فرض هسته CRM است و برای حفظ یکپارچگی داده‌ها قابل حذف نیست">
                                        غیرقابل حذف
                                    </span>
                            </template>
                            <template x-if="!item.is_system">
                                <button type="button"
                                        @click="openDeleteModal(item)"
                                        title="حذف از کاتالوگ"
                                        class="p-1.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </template>
                        </td>
                    </tr>
                </template>

                <template x-if="filteredItems.length === 0">
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 text-sm">
                            هیچ موجودیتی مطابق با فیلتر یا جستجوی شما یافت نشد.
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>

        {{-- Custom Pagination matching ResellerHub Component --}}
        <div x-show="totalPages > 1"
             class="p-4 sm:p-5 border-t border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                نمایش
                <span class="font-bold text-gray-800 dark:text-gray-200"
                      x-text="toPersianDigits((currentPage - 1) * perPage + 1)"></span>
                تا
                <span class="font-bold text-gray-800 dark:text-gray-200"
                      x-text="toPersianDigits(Math.min(currentPage * perPage, filteredItems.length))"></span>
                از مجموع
                <span class="font-bold text-gray-800 dark:text-gray-200"
                      x-text="toPersianDigits(filteredItems.length)"></span>
                موجودیت
            </div>

            <nav role="navigation" aria-label="Pagination Navigation" class="inline-flex items-center gap-1.5">
                {{-- Previous Page (RTL) --}}
                <button type="button"
                        @click="prevPage()"
                        :disabled="currentPage === 1"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all shadow-2xs"
                        :class="currentPage === 1 ? 'bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:text-indigo-600 cursor-pointer active:scale-95'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Page Numbers --}}
                <template x-for="p in totalPages" :key="p">
                    <button type="button"
                            @click="currentPage = p"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl font-bold text-xs transition-all shadow-2xs cursor-pointer select-none"
                            :class="currentPage === p ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black shadow-md shadow-indigo-500/25' : 'bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-700 active:scale-95'">
                        <span x-text="toPersianDigits(p)"></span>
                    </button>
                </template>

                {{-- Next Page (RTL) --}}
                <button type="button"
                        @click="nextPage()"
                        :disabled="currentPage === totalPages"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all shadow-2xs"
                        :class="currentPage === totalPages ? 'bg-gray-100 dark:bg-gray-800/50 text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:text-indigo-600 cursor-pointer active:scale-95'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </nav>
        </div>
    </div>

    {{-- MODAL 1: Chic Delete Confirmation Modal (Matching ResellerHub Standard) --}}
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="closeDeleteModal()"
         role="dialog"
         aria-modal="true"
         dir="rtl">

        <div class="flex items-center justify-center min-h-screen w-full px-4 py-8 text-center sm:p-0">
            <div class="fixed inset-0 w-full h-full bg-gray-950/75 dark:bg-black/85 backdrop-blur-sm transition-opacity"
                 @click="closeDeleteModal()"
                 aria-hidden="true"></div>

            <div @click.stop="$event.stopPropagation()"
                 class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 text-center space-y-5 my-8">

                {{-- Danger Icon Badge --}}
                <div
                    class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-inner border border-rose-100 dark:border-rose-900/40">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">تأیید حذف موجودیت از
                        چندمستاجری</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                        آیا از حذف موجودیت <span class="font-bold text-gray-900 dark:text-white"
                                                 x-text="'«' + (entityToDelete?.entity_label || entityToDelete?.model || '') + '»'"></span>
                        از رجیستری ایزولاسیون اطمینان دارید؟
                    </p>
                </div>

                {{-- Target Card --}}
                <div
                    class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700/80 text-start space-y-1">
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200"
                         x-text="entityToDelete?.entity_label || entityToDelete?.model"></div>
                    <div class="text-[11px] text-gray-400 truncate" dir="ltr" x-text="entityToDelete?.raw_class"></div>
                </div>

                <p class="text-xs text-rose-600 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/30 p-3 rounded-xl border border-rose-100 dark:border-rose-900/40">
                    با حذف این کلاس، اسکوپ چندمستاجری و گاردهای امنیتی از روی این مدل برداشته خواهد شد.
                </p>

                <form method="POST" action="{{ route('reseller.settings.tenancy.remove-model') }}"
                      class="space-y-2.5 pt-2">
                    @csrf
                    <input type="hidden" name="model_class" :value="entityToDelete?.raw_class || ''">
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" @click="closeDeleteModal()"
                                class="py-2.5 px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                class="py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/20 transition cursor-pointer">
                            بله، حذف شود
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 2: Chic Health Sync & Diagnostic Modal --}}
    <div x-show="syncModalOpen"
         x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="closeSyncModal()"
         role="dialog"
         aria-modal="true"
         dir="rtl">

        <div class="flex items-center justify-center min-h-screen w-full px-4 py-8 text-center sm:p-0">
            <div class="fixed inset-0 w-full h-full bg-gray-950/75 dark:bg-black/85 backdrop-blur-sm transition-opacity"
                 @click="closeSyncModal()"
                 aria-hidden="true"></div>

            <div @click.stop="$event.stopPropagation()"
                 class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 text-center space-y-5 my-8">

                {{-- Shield Icon Badge --}}
                <div
                    class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner border border-indigo-100 dark:border-indigo-900/40">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">تحلیل و همگام‌سازی سلامت
                        چندمستاجری</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                        این فرآیند سلامت اتصال تمام موجودیت‌های ماژول‌ها به موتور چندمستاجری را به طور کامل بازبینی و
                        بهینه‌سازی می‌کند.
                    </p>
                </div>

                {{-- Checklist Box --}}
                <div
                    class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 text-xs text-indigo-900 dark:text-indigo-200 text-start space-y-2">
                    <div class="font-bold flex items-center gap-1.5 text-indigo-700 dark:text-indigo-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>عملیات‌هایی که در این گام انجام می‌شوند:</span>
                    </div>
                    <ul class="space-y-1.5 list-disc list-inside ps-1 text-[11px] leading-relaxed">
                        <li>بررسی اتصال پویای اسکوپ TenantScope به تمام مدل‌های الکوئنت</li>
                        <li>بازخوانی و اعتبارسنجی کاتالوگ فایل کانفیگ <code>config/tenancy.php</code></li>
                        <li>تست عملکرد هوک‌های خودکار <code>creating</code> و گارد ضد دستکاری</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('reseller.settings.tenancy.sync') }}"
                      @submit="isSyncingInProgress = true" class="space-y-2.5 pt-2">
                    @csrf
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" @click="closeSyncModal()"
                                class="py-2.5 px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                            انصراف
                        </button>
                        <button type="submit"
                                :disabled="isSyncingInProgress"
                                class="py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition cursor-pointer flex items-center justify-center gap-2">
                            <template x-if="isSyncingInProgress">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span x-text="isSyncingInProgress ? 'در حال همگام‌سازی...' : 'شروع همگام‌سازی'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Alpine Manager Script for Tenancy Tab --}}
<script>
    function tenancyTabManager(config) {
        return {
            items: config.items || [],
            discovered: config.discovered || [],
            searchQuery: '',
            selectedModule: 'all',
            showAddForm: false,
            addMode: 'select',
            manualModelClass: '',
            selectedDiscoveredClass: '',
            currentPage: 1,
            perPage: config.perPage || 8,
            deleteModalOpen: false,
            entityToDelete: null,
            syncModalOpen: false,
            isSyncingInProgress: false,

            get availableModules() {
                const map = {};
                this.items.forEach(item => {
                    if (item.module && !map[item.module]) {
                        map[item.module] = {
                            key: item.module,
                            label: item.module_label || item.module
                        };
                    }
                });
                return Object.values(map);
            },

            get filteredItems() {
                return this.items.filter(item => {
                    const q = this.searchQuery.trim().toLowerCase();
                    const matchesSearch = !q ||
                        (item.model && item.model.toLowerCase().includes(q)) ||
                        (item.entity_label && item.entity_label.toLowerCase().includes(q)) ||
                        (item.module_label && item.module_label.toLowerCase().includes(q)) ||
                        (item.raw_class && item.raw_class.toLowerCase().includes(q));
                    const matchesMod = this.selectedModule === 'all' || item.module === this.selectedModule;
                    return matchesSearch && matchesMod;
                });
            },

            get totalPages() {
                const len = this.filteredItems.length;
                return Math.max(1, Math.ceil(len / this.perPage));
            },

            get paginatedItems() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredItems.slice(start, start + this.perPage);
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },

            openDeleteModal(item) {
                this.entityToDelete = item;
                this.deleteModalOpen = true;
            },

            closeDeleteModal() {
                this.deleteModalOpen = false;
                this.entityToDelete = null;
            },

            openSyncModal() {
                this.syncModalOpen = true;
                this.isSyncingInProgress = false;
            },

            closeSyncModal() {
                this.syncModalOpen = false;
                this.isSyncingInProgress = false;
            },

            getModuleBadgeClass(module) {
                switch (module) {
                    case 'Booking':
                        return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300';
                    case 'Clients':
                        return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                    case 'Services':
                        return 'bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300';
                    case 'Market':
                        return 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300';
                    case 'Accounting':
                        return 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
                    default:
                        return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                }
            },

            getModuleAvatarClass(module) {
                switch (module) {
                    case 'Booking':
                        return 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400';
                    case 'Clients':
                        return 'bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400';
                    case 'Services':
                        return 'bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400';
                    case 'Market':
                        return 'bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400';
                    case 'Accounting':
                        return 'bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400';
                    default:
                        return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300';
                }
            },

            toPersianDigits(num) {
                const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                return String(num).replace(/[0-9]/g, function (w) {
                    return faDigits[+w];
                });
            }
        };
    }
</script>
